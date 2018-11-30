<?

/**
 *
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 */
class Core_Api_Core extends Core_Api_Abstract
{
    /* NOTE - при добавлении новых предметов необходимо изменять ENUM поле engine4_olympic.theme в БД*/
    public static $themes = array(
        'phys' => array('label' =>'Физика', 'bit' => 1, 'short_label' => 'Ф', 'mid_label' => 'Физика', 'prepositional' => 'физике'),
        'math' => array('label' =>'Математика', 'bit' => 2, 'short_label' => 'М', 'mid_label' => 'Матем', 'prepositional' => 'математике'),
        'inf' => array('label' =>'Информатика', 'bit' => 4, 'short_label' => 'И', 'mid_label' => 'Информ', 'prepositional' => 'информатике'),
        'chem' => array('label' =>'Химия', 'bit' => 8, 'short_label' => 'Х', 'mid_label' => 'Химия', 'prepositional' => 'химии'),
        'bio' => array('label' =>'Биология', 'bit' => 16, 'short_label' => 'Б', 'mid_label' => 'Биол', 'prepositional' => 'биологии'),
        'russ' => array('label' =>'Русский язык', 'bit' => 32, 'short_label' => 'Р', 'mid_label' => 'Русск', 'prepositional' => 'русскому языку')
    );

    public static function getThemeByBit($bit)
    {
        foreach (self::$themes as $key=>$theme){
            if ($theme['bit'] == $bit){
                $theme['key'] = $key;
                return $theme;
            }
        }
        foreach (self::$themes as $key=>$theme){
            if ((int)$bit & (int)$theme['bit']){
                $theme['key'] = $key;
                return $theme;
            }
        }
        return ['label' => '-', 'bit' => 0, 'short_label' => '-', 'key' => ''];
    }

    public static function getThemeMultiOptions($withVoid = true)
    {
        $MO = $withVoid ? ['' => '-не указан-'] : [];
        foreach (self::$themes as $key=>$theme){
            $MO[$key] = $theme['label'];
        }
        return $MO;
    }

    /**
     * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
     */
    protected $_subject;

    /**
     * Set the object that represents the subject of the page
     *
     * @param Core_Model_Item_Abstract|mixed $subject
     * @return Core_Api_Core
     */
    public function setSubject($subject)
    {
        if( null !== $this->_subject ) {
            throw new Core_Model_Exception("The subject may not be set twice");
        }

        if( !($subject instanceof Core_Model_Item_Abstract) ) {
            throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
        }

        $this->_subject = $subject;
        return $this;
    }

    /**
     * Get the previously set subject of the page
     *
     * @return Core_Model_Item_Abstract|null
     */
    public function getSubject($type = null)
    {
        if( null === $this->_subject ) {
            throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
        } else if( is_string($type) && $type !== $this->_subject->getType() ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        } else if( is_array($type) && !in_array($this->_subject->getType(), $type) ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        }

        return $this->_subject;
    }

    /**
     * Checks if a subject has been set
     *
     * @return bool
     */
    public function hasSubject($type = null)
    {
        if( null === $this->_subject ) {
            return false;
        } else if( null === $type ) {
            return true;
        } else {
            return ( $type === $this->_subject->getType() );
        }
    }

    public function clearSubject()
    {
        $this->_subject = null;
        return $this;
    }

    protected $_branchRoot = false;
    /*  @return  Core_Model_Branch|false */
    public function branchesUse($root = null)
    {
        if ($root === null){
            return $this->_branchRoot;
        }
        return $this->_branchRoot = $root;
    }

    public function logException($e, $error_code = 'cathed_exception')
    {
        if(
            Zend_Registry::isRegistered('Zend_Log') &&
            ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log ) {
            // Only log if in production or the exception is not an instance of Engine_Exception
            if ('production' === APPLICATION_ENV || !($e instanceof Engine_Exception)) {
                $output = PHP_EOL . 'Error Code: ' . $error_code . PHP_EOL;
                if (!empty($_SERVER['REQUEST_URI'])) {
                    $output .= $_SERVER['REQUEST_METHOD'] . ' URL: ' . (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . PHP_EOL;
                    if (!empty($_SERVER['HTTP_REFERER'])) $output .= 'REFERER: '. $_SERVER['HTTP_REFERER']. PHP_EOL;
                    if (!empty($_POST)) $output.= "POST:\n" . json_encode( Engine_Api::_()->string()->filterLongVarList($_POST), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
                }
                if (($viewer = Engine_Api::_()->user()->getViewer()) && $viewer->getIdentity()) {
                    $output .= 'VIEWER: ' . $viewer->getIdentity() . PHP_EOL;
                } else {
                    $output .= 'VIEWER: no' . PHP_EOL;
                }
                $output .= $e->__toString() . PHP_EOL;
                $log->log($output, Zend_Log::CRIT);
            }
            return $output;
        }
    }

    public function logItemTry(Core_Model_Item_Abstract $item, $action = '', $customLogCallback = null, $logAnyway = false, $addStacktrace = false)
    {
        $db = Engine_Db_Table::getDefaultAdapter();

        $checkEnableLog = function(Core_Model_Item_Abstract $subject) use($db, $action){
            return $db->fetchRow(
                $db->select()->from('engine4_core_log_items')
                    ->where('subject_type = "*" or subject_type = ?', $subject->getType())
                    ->where('subject_id = 0 or subject_id = ?', $subject->getIdentity())
                    ->where('active = 1')
                    ->where('action = "*" or action = ?', $action)
                    ->limit(1)
            );
        };


        $logItemsRec = $checkEnableLog($item);
        $parent = null;
        if (($parent = $item->getParent()) &&  !$logItemsRec && !($parent instanceof  User_Model_User)){
            $logItemsRec = $checkEnableLog($parent);
            if ($item instanceof Olympic_Model_Olympic && $item->original_olympic_id && ($cloneOrigin = Engine_Api::_()->getItem('olympic', $item->original_olympic_id))){
                $logItemsRec = $checkEnableLog($cloneOrigin);
                if (($clParent = $cloneOrigin->getParent()) &&  !$logItemsRec && !($clParent instanceof  User_Model_User)){
                    $logItemsRec = $checkEnableLog($clParent);
                }
            }
        }
        if (!$logAnyway && !$logItemsRec) return false;

        $logfile = APPLICATION_PATH_TMP . '/log/custom_items_log.log';

        $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
        $msg = 'CUSTOM LOG: '. $item->getType() .' '.$action.' logrow:'. ($logItemsRec ? $logItemsRec['id'] : '-') . "\n";
        $msg .=  'VIEWER: ' . $viewerId . ' ' . date('Y-m-d H:i:s') . "\n";
        $msg .= 'ITEM: ' . $item->getGuid() . ($parent ? ' (parent='. $parent->getGuid() . ')' : '') . "\n" . json_encode( Engine_Api::_()->string()->filterLongVarList($item->toArray()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)  . "\n";
        $msg .= $_SERVER['REQUEST_METHOD'] .' URL: ' . (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
        if (!empty($_SERVER['HTTP_REFERER'])) $msg .= 'REFERER: '. $_SERVER['HTTP_REFERER']. PHP_EOL;
        if (!empty($_POST)) $msg .= "POST:\n" . json_encode(Engine_Api::_()->string()->filterLongVarList($_POST), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        if (is_callable($customLogCallback)){
            $msg = $customLogCallback($msg);
        }
        if ($addStacktrace){
            try {throw new Exception();} catch(Exception $e){ $trace = $e->getTraceAsString(); }
            $msg .= 'TRACE: ' . $trace . "\n";
        }
        file_put_contents($logfile, $msg . "\n\n\n", FILE_APPEND);

        if ($logItemsRec){
            $db->update('engine4_core_log_items', ['last_record_date' => date('Y-m-d H:i:s')], 'id = '. $logItemsRec['id']);
        }
        return true;
    }

    public function getCaptchaOptions(array $params = array())
    {
        $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
        if( (empty($spamSettings['recaptchaenabled']) ||
            empty($spamSettings['recaptchapublic']) ||
            empty($spamSettings['recaptchaprivate'])) ) {
            // Image captcha
            return array_merge(array(
                'label' => 'Human Verification',
                'description' => 'Please type the characters you see in the image.',
                'captcha' => 'image',
                'required' => true,
                'captchaOptions' => array(
                    'wordLen' => 6,
                    'fontSize' => '30',
                    'timeout' => 300,
                    'imgDir' => APPLICATION_PATH . '/public/temporary/',
                    'imgUrl' => Zend_Registry::get('Zend_View')->baseUrl() . '/public/temporary',
                    'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf',
                ),
            ), $params);
        } else {
            // Recaptcha
            return array_merge(array(
                'label' => 'Human Verification',
                'description' => 'Please type the characters you see in the image.',
                'captcha' => 'reCaptcha',
                'required' => true,
                'captchaOptions' => array(
                    'privkey' => $spamSettings['recaptchaprivate'],
                    'pubkey' => $spamSettings['recaptchapublic'],
                    'theme' => 'white',
                    'lang' => Zend_Registry::get('Locale')->getLanguage(),
                    'tabindex' => (isset($params['tabindex']) ? $params['tabindex'] : null ),
                    'ssl' => constant('_ENGINE_SSL')   // Fixed Captcha does not work well when ssl is enabled on website
                ),
            ), $params);
        }
    }
    protected static $treeNodeSettings = null;
    const DEFAULT_DIVISION_TITLE = 'Разное';
    public function getTreeNodeSettings()
    {
        if (!self::$treeNodeSettings){
            self::$treeNodeSettings = include APPLICATION_PATH_SET. DS . 'tree-nodes.php';
        }
        return self::$treeNodeSettings;
    }


    protected static $userFieldsSettings = null;
    public function getUserFieldsSettings($domainConsidering = true)
    {
        if (!self::$userFieldsSettings){
            self::$userFieldsSettings = include APPLICATION_PATH_SET. DS . 'user-fields.php';
        }

        if($domainConsidering){
            $domainSettings = $this->getNowDomainSettings();
            if(isset($domainSettings['user_field_overrides'])){
                $overides = $domainSettings['user_field_overrides'];
                if(isset($overides['all'])){
                    foreach (self::$userFieldsSettings as $userTypeKey => $fieldSettings){
                        self::$userFieldsSettings[$userTypeKey] = array_replace_recursive($fieldSettings, $overides['all']);
                    }
                    unset($overides['all']);
                }
                self::$userFieldsSettings = array_replace_recursive(self::$userFieldsSettings, $domainSettings['user_field_overrides']);
            }
        }

        return self::$userFieldsSettings;
    }

    protected static $domainsSettings = null;
    public function getDomainsSettings()
    {
        if (!self::$domainsSettings){
            self::$domainsSettings = include APPLICATION_PATH_SET. DS . 'domains.php';
        }
        return self::$domainsSettings;
    }

    protected static $authSettings = null;
    public function getAuthSettings($subjectType = null) {
        if (!self::$authSettings){
            self::$authSettings = include APPLICATION_PATH_SET. DS . 'auth-settings.php';
        }
        $DS = $this->getNowDomainSettings();
        if (!empty($DS['auth'])){
            foreach($DS['auth'] as $itemType => $preset){
                self::$authSettings[$itemType]['actions'] = array_merge(self::$authSettings[$itemType]['actions'], $preset);
            }
        }
        if($subjectType === null){
            return self::$authSettings;
        } else {
            return isset(self::$authSettings[$subjectType]) ? self::$authSettings[$subjectType] : array();
        }
    }

    protected static $nowDomainSets = null;
    public function getNowDomainSettings()
    {
        if (self::$nowDomainSets){
            return self::$nowDomainSets;
        }
        $domains = Engine_Api::_()->core()->getDomainsSettings();
        if (!isset($_SERVER['HTTP_HOST'])){
            $domains['bitsa']['key'] = 'bitsa';
            return self::$nowDomainSets = $domains['bitsa'];
        }
        $activeDomain = null;
        foreach($domains as $key=>$domain){
            $checkHost = $_SERVER['HTTP_HOST'];
            //
            if ($checkHost==$domain['domain']){
                return self::$nowDomainSets = array_merge($domain, array('key'=>$key)) ;
            }
        }
        $domains['bitsa']['key'] = 'bitsa';

        return self::$nowDomainSets = $domains['bitsa'];
    }

    public function getDomainFilter()
    {
        $DS = $this->getNowDomainSettings();
        if (!isset($DS['domainDisplay'])){
            $DS['domainDisplay'] = 'both';
        }
        return ( $DS['domainDisplay'] != 'all')
            ? ( $DS['domainDisplay'] == 'both' ? array('',$DS['key']) : array($DS['key'])  )
            : false;
    }

    public function getAutoJoinAfterLoginItemTypes()
    {
        return ['group', 'event', 'course'];
    }

    public function getApiViewer()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer->getIdentity() && isset($_GET['access_token'])){
            // get viewer by token
            /* @var $tokens_table User_Model_DbTable_AccessTokens */
            $tokens_table = Engine_Api::_()->getDbTable('AccessTokens', 'user');
            $viewer = $tokens_table->getViewerByToken($_GET['access_token']);
        }
        return $viewer->getIdentity() ? $viewer : null;
    }

    /**
     * @param array $sourceParams
     * @param array|callable $domainParams
     * @return array
     */
    public function widgetSettingsOverload($sourceParams, $domainParams)
    {
        if (is_array($domainParams) && isset($domainParams['callback'])){
            return $domainParams['callback']($sourceParams, $domainParams);
        }else if (is_callable($domainParams)){
            return $domainParams($sourceParams);
        }else{
            return array_merge($sourceParams, $domainParams);
        }
    }

    /* @return Core_Api_String */
    public function string()
    {
        return $this->getApi('string', 'core');
    }
}