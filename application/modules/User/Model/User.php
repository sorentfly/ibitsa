<?

/**
 * @property string salt
 */
class User_Model_User extends Core_Model_Item_Abstract
{

    // max length of permission key - 16 symbols
    const PERMISSION_VIEW 						= 'view';
    const PERMISSION_EDIT 						= 'edit';
    const PERMISSION_VIEW_USERNAME 				= 'view_username';
    const PERMISSION_VIEW_OLYMPICS 				= 'view_olympics';
    const PERMISSION_VIEW_HOME_ADDRESS 			= 'view_address';
    const PERMISSION_VIEW_SECONDARY_EDUCATION 	= 'view_sec_edu';
    const PERMISSION_VIEW_HIGHER_EDUCATION 		= 'view_hi_edu';
    const PERMISSION_VIEW_CHILDS_INFO 			= 'view_child';
    const PERMISSION_VIEW_WORK_INFO 			= 'view_work';
    const PERMISSION_DELETE 					= 'delete';

    const DELETED_USERNAME = "Пользователь удалён";

    /* For NO users in `engine4_core_search` table*/
    protected $_searchTriggers = false;

    /*Multidomain hacks*/
    protected static $_domainSettings = null;

    protected static function getDS($property){
        if (static::$_domainSettings === null){
            static::$_domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        }
        return isset(static::$_domainSettings[$property]) ? static::$_domainSettings[$property] : null;
    }

    public function save()
    {
        $cleanData = $this->getModifiedFieldsCleanData();
        if (!empty($cleanData['birthdate'])){
            if ($this->birthdate!=$cleanData['birthdate']){
                // Логгирование вследствие непредсказуемого рассинхрона дат рождения
                $logfile = APPLICATION_PATH_TMP . '/log/user_bday_changes.log';
                try {throw new Exception();} catch(Exception $e){ $trace = $e->getTraceAsString(); }
                $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
                $msg =  'VIEWER: ' . $viewerId . ' ' . date('Y-m-d H:i:s') . "\n";
                $msg .= 'USER: ' . $this->getIdentity() . ' (' . $this->getFIO() . '). bDay ' .  $cleanData['birthdate'] . ' -> ' . $this->birthdate . "\n";
                $msg .= 'bDay-In-fields: '. Engine_Api::_()->fields()->getValByName($this, 'birthdate');
                $msg .= 'URL: ' . (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
                $msg .= 'TRACE: ' . $trace . "\n";
                file_put_contents($logfile, $msg . "\n\n\n", FILE_APPEND);
            }
        }
        return parent::save();
    }


    public function __get($columnName){
        if($columnName == 'displayname'){
            if(array_key_exists('deleted', $this->_data) && $this->_data['deleted']){
                return self::DELETED_USERNAME;
            }
        }
        return parent::__get($columnName);
    }

    /**
     * Gets the title of the user (their username)
     *
     * @return string
     */

    public function getTitle()
    {
        if(!empty($this->deleted)){
            return self::DELETED_USERNAME;
        }
        if ($this->getDS('userTitleFIO') && isset($this->last_name))
        {
            return Zend_Registry::get('Locale')->__toString() === 'en'
                ? Engine_Api::_()->getApi('string', 'core')->transliterate( $this->getFIO(true) )
                : $this->getFIO(true);
        }
        $title = null;
        if(isset($this->displayname) && '' !== trim($this->displayname)){
            $title =  $this->displayname;
        } else if(isset($this->username) && '' !== trim($this->username)){
            $title =  $this->username;
        } else if(isset($this->email) && '' !== trim($this->email)) {
            $tmp = explode('@', $this->email);
            $title = $tmp[0];
        } else {
            return "<i>" . Zend_Registry::get('Zend_Translate')->_("Deleted Member") . "</i>";
        }

        if (Zend_Registry::get('Locale')->__toString() === 'en'){
            /*@var $strUtils Core_Api_String */
            $title = Engine_Api::_()->getApi('string', 'core')->transliterate($title);
        }
        return $title;
    }

    public function getFIO($short = false){
        if(!empty($this->deleted)){
            return self::DELETED_USERNAME;
        }
        $last_name = isset($this->last_name) ? $this->last_name : '';
        $first_name = isset($this->first_name) ? $this->first_name : '';
        $middle_name = isset($this->middle_name) ? $this->middle_name : '';

        if ($short){
            return $last_name . ' ' .mb_substr( $first_name, 0, 1).'. '.mb_substr( $middle_name, 0, 1).'. ';
        }else{
            return $last_name . ' ' . $first_name . ' ' . $middle_name;
        }
    }

    protected static $_academiesUsing = null;
    /**
     * Gets an absolute URL to the page to view this item
     *
     * @return string
     */
    public function getHref($params = array())
    {
        /*КОСТЫЛЬ: В ЗФТШ - при переходе на пользователя препам/методистам/админам - отображается вкладка learninfo */
        $globalHref = false;
        if (!empty($params['_global_href'])){
            $globalHref = true;
        }
        if (!$globalHref && $this->getDS('key') == 'zftsh' && $this->academyStatus() != 'none'){
            $viewer = Engine_Api::_()->user()->getViewer();
            if ($viewer->getIdentity() && !in_array($viewer->academyStatus(), ['none','pupil_intramural','pupil_extramural'])){
                return $this->getAcademyContextHref($params);
            }
            if ($viewer->isSelf($this) && in_array($viewer->academyStatus(), ['pupil_intramural','pupil_extramural'])){
                return $this->getAcademyContextHref($params);
            }
        }
        /*КОСТЫЛЬ END*/

        $profileAddress = null;
        if(isset($this->username) && '' != trim($this->username))
        {
            $profileAddress = $this->username;
        } else if(isset($this->user_id) && $this->user_id > 0)
        {
            $profileAddress = $this->user_id;
        } else
        {
            return 'javascript:void(0);';
        }

        $params = array_merge(array(
            'route' => 'user_profile',
            'reset' => true,
            'id' => $profileAddress,
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble($params, $route, $reset);
    }

    public function getAcademyContextHref($params = array())
    {
        if ($this->academyStatus() == 'none'){
            /*WARNING: при редактировании проверьте логику getHref - getAcademyContextHref на рекурсию!*/
            return $this->getHref($params);
        }
        $action =  'learninfo';
        if (in_array($this->academyStatus(), array('pupil_intramural', 'pupil_extramural'))){
            $action =  'progress';

        } else if (in_array($this->academyStatus(), array('teacher_new', 'teacher_approved'))){
            $action =  'gradebook';
        }
        return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble(['action'=>$action, 'id' => $this->getIdentity()], 'zftsh-user-profile', true);
    }

    protected static $_academyStatusNamespace = null;
    public static function academyStatusKey()
    {
        if (!self::$_academyStatusNamespace){
            $DS = Engine_Api::_()->core()->getNowDomainSettings();
            self::$_academyStatusNamespace = !empty($DS['academyNamespace']) ? $DS['academyNamespace'] : 'abitu_academy_status';
        }
        return self::$_academyStatusNamespace;
    }

    public function academyStatus()
    {
        return isset($this->{self::academyStatusKey()}) ? $this->{self::academyStatusKey()} : 'none';
    }

    public function setAcademyStatus($status)
    {
        $this->{self::academyStatusKey()} = $status;
        $this->save();
        return $this;
    }

    public function hasMethodistRights()
    {
        return isset($this->level_id) && ( in_array($this->level_id, [1,2]) || in_array($this->academyStatus(), ['admin', 'methodist']));
    }

    public function hasAcademyAdminRights()
    {
        return isset($this->level_id) && ( in_array($this->level_id, [1,2]) || $this->academyStatus() == 'admin');
    }

    public function hasSchoolAdminRights($entity_type = 'project')
    {
        return in_array($this->level_id, [1,2]);
    }

    public function setDisplayName($displayName)
    {
        if(is_string($displayName))
        {
            $this->displayname = $displayName;
        } else if(is_array($displayName))
        {
            // Has both names
            if(!empty($displayName['first_name']) && !empty($displayName['last_name']))
            {
                $displayName = $displayName['first_name'] . ' ' . $displayName['last_name'];
            }
            // Has full name
            else if(!empty($displayName['full_name']))
            {
                $displayName = $displayName['full_name'];
            }
            // Has only first
            else if(!empty($displayName['first_name']))
            {
                $displayName = $displayName['first_name'];
            }
            // Has only last
            else if(!empty($displayName['last_name']))
            {
                $displayName = $displayName['last_name'];
            }
            // Has neither (use username)
            else
            {
                $displayName = $this->username;
            }

            $this->displayname = $displayName;
        }
    }

    public function setPhoto($photo)
    {
        if($photo instanceof Zend_Form_Element_File)
        {
            $file = $photo->getFileName();
            $fileName = $file;
        } else if($photo instanceof Storage_Model_File)
        {
            $file = $photo->temporary();
            $fileName = $photo->name;
        } else if($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id))
        {
            $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
            $file = $tmpRow->temporary();
            $fileName = $tmpRow->name;
        } else if(is_array($photo) && !empty($photo['tmp_name']))
        {
            $file = $photo['tmp_name'];
            $fileName = $photo['name'];
        } else if(is_string($photo) && file_exists($photo))
        {
            $file = $photo;
            $fileName = $photo;
        } else
        {
            throw new User_Model_Exception('invalid argument passed to setPhoto');
        }

        if(!$fileName)
        {
            $fileName = $file;
        }

        $name = basename($file);
        $extension = ltrim(strrchr(basename($fileName), '.'), '.');
        $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_type' => $this->getType(),
            'parent_id' => $this->getIdentity(),
            'user_id' => $this->getIdentity(),
            'name' => basename($fileName),
        );

        // Save
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

        // Resize image (main)
        $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(720, 720)
            ->write($mainPath)
            ->destroy();

        // Resize image (profile)
        $profilePath = $path . DIRECTORY_SEPARATOR . $base . '_p.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(200, 400)
            ->write($profilePath)
            ->destroy();

        // Resize image (normal)
        $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(140, 160)
            ->write($normalPath)
            ->destroy();

        // Resize image (icon)
        $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file);

        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;

        $image->resample($x, $y, $size, $size, 48, 48)
            ->write($squarePath)
            ->destroy();

        // Store
        $iMain = $filesTable->createFile($mainPath, $params);
        $iProfile = $filesTable->createFile($profilePath, $params);
        $iIconNormal = $filesTable->createFile($normalPath, $params);
        $iSquare = $filesTable->createFile($squarePath, $params);

        $iMain->bridge($iProfile, 'thumb.profile');
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iMain->bridge($iSquare, 'thumb.icon');

        // Remove temp files
        @unlink($mainPath);
        @unlink($profilePath);
        @unlink($normalPath);
        @unlink($squarePath);

        // Update row
        $this->modified_date = date('Y-m-d H:i:s');
        $this->photo_id = $iMain->file_id;
        $this->save();

        return $this;
    }

    public function isEnabled()
    {
        return ( $this->enabled );
    }

    public function isAdmin()
    {
        // Not logged in, not an admin
        if(!$this->getIdentity() || empty($this->level_id))
        {
            return false;
        }

        // Check level
        //return (bool) Engine_Registry::get('database-default')
        // return (bool) Zend_Registry::get('Zend_Db')
        return $this->getTable()->getAdapter()
            ->select()
            ->from('engine4_authorization_levels', new Zend_Db_Expr('TRUE'))
            ->where('level_id = ?', $this->level_id)
            ->where('type IN(?)', array('admin', 'moderator'))
            ->limit(1)
            ->query()
            ->fetchColumn();
    }

    public function isAdminOnly()
    {
        return $this->getIdentity() &&  in_array($this->level_id,[1,2]);
    }

    public function isAllowed($role, $action = 'view')
    {
        if (!($role instanceof User_Model_User) || !$role->getIdentity()){
            return parent::isAllowed($role, $action);
        }

        if($action == self::PERMISSION_DELETE){
            if($this->isSelf($role) || in_array($role->level_id,[1,2])){
                return Authorization_Api_Core::LEVEL_ALLOW;
            } else {
                return Authorization_Api_Core::LEVEL_DISALLOW;
            }
        }

        if($this->getIdentity() && $this->deleted && !in_array($role->level_id,[1,2])) {
            return Authorization_Api_Core::LEVEL_DISALLOW;
        }

        //разрешение редактировать профиль пользователя модераторам определённого мероприятия (при условии что пользователь участвует в нём)
        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        if (empty($this->level_id)){
            return Authorization_Api_Core::LEVEL_DISALLOW;
        }

        /* @var User_Model_DbTable_Users $userTbl */
        $userTbl = Engine_Api::_()->getItemTable('user');
        $extraItems = $userTbl->getExtraUserRightsObjectIds(false);
        if (!empty($_SESSION['user_extra_allow_items'])){
            $db = $userTbl->getAdapter();
            if (!in_array($action, ['extraview', 'view'])){
                $extraItems = array_filter($extraItems, function($value){
                    return $value == 'edit';
                });
            }
            $extraModerationList = array_intersect($_SESSION['user_extra_allow_items'], array_keys($extraItems) );
            $checkAllowByModeration = function($membershipType, $allowList) use ($db, $role){
                $allowList = array_map(function($guid) use ($membershipType){
                    $expl = explode('_', $guid);
                    return strpos($guid, $membershipType . '_') === 0 ? (int)$expl[count($expl) - 1] : null;
                }, $allowList);
                if ($allowList && ($organizerEventList = $db->fetchCol(
                        $db->select()->from('engine4_event_membership', ['resource_id'])
                            ->where('organizer = 1')
                            ->where('user_id = ?', $role->getIdentity())
                            ->where('resource_id IN(?)', $allowList)
                    ))){
                    if ($isMemberOfAny = $db->fetchOne(
                        $db->select()->from('engine4_event_membership', ['user_id'])
                            ->where('resource_id IN (?)', $organizerEventList)
                            ->where('user_id = ?', $this->getIdentity())
                    )){
                        return true;
                    };
                }

                /*!NOTE! - самая затратная по времени операция: проверка прав во всех мероприятиях с экстра правами. Для сглаживания производительности введена сессия*/
                foreach($allowList as $id){
                    /* @var Core_Model_Item_TreeNode $item */
                    $item = Engine_Api::_()->getItem($membershipType, $id);
                    if ($item && $item->authorization()->isAllowed($role, 'editusers') && $item->membership()->isMember($this)){
                        return true;
                    }
                }
                return false;
            };

            foreach(['event', 'group', 'course', 'olympic'] as $type){
                if ($checkAllowByModeration($type, $extraModerationList)) return Authorization_Api_Core::LEVEL_ALLOW;
            }
        }

        //разрешение просмотра данных для рецензоров конференций
        if ($action=='view' && !empty($_GET['checkconferencereviewer']) && ($checkitem = Engine_Api::_()->getItemByGuid($_GET['checkconferencereviewer']))){
            /* @var Olympic_Model_DbTable_Conferences $conferences */
            $conferences = Engine_Api::_()->getItemTable('conference');
            if ($conferences->isRecensor($role, $checkitem)){
                return Authorization_Api_Core::LEVEL_ALLOW;
            }
        }

        //разрешение редактировать профиль методистам академий
        if ($action=='view') {
            if( in_array($this->academyStatus(), ['teacher_new','teacher_approved']) ){
                return Authorization_Api_Core::LEVEL_ALLOW;
            }

            if (Engine_Api::_()->zftsh()->isUserViewableBy($this, $role)){
                return Authorization_Api_Core::LEVEL_ALLOW;
            }
        }else if ($action=='edit' && Engine_Api::_()->zftsh()->isUserEditableBy($this, $role) ){
            return Authorization_Api_Core::LEVEL_ALLOW;
        }
        return parent::isAllowed($role, $action);
    }

    // Internal hooks

    protected function _insert()
    {
        $settings = Engine_Api::_()->getApi('settings', 'core');

        // These need to be done first so the hook can see them
        $this->level_id = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel()->level_id;
        $this->approved = (int) ($settings->getSetting('user.signup.approve', 1) == 1);
        $this->verified = (int) ($settings->getSetting('user.signup.verifyemail', 1) < 2);
        $this->enabled = ( $this->approved && $this->verified );
        $this->search = true;

        if(empty($this->_modifiedFields['timezone']))
        {
            $this->timezone = $settings->getSetting('core.locale.timezone', 'Europe/Moscow');
        }
        if(empty($this->_modifiedFields['locale']))
        {
            $this->locale = $settings->getSetting('core.locale.locale', 'ru_RU');
        }
        if(empty($this->_modifiedFields['language']))
        {
            $this->language = $settings->getSetting('core.locale.language', 'ru_RU');
        }

        if('cli' !== PHP_SAPI)
        { // No CLI
            // Get ip address
            $db = $this->getTable()->getAdapter();
            $ipObj = new Engine_IP();
            $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
            $this->creation_ip = $ipExpr;
        }

        // Set defaults, process etc
        $this->salt = (string) rand(1000000, 9999999);
        if(!empty($this->password))
        {
            $this->password = md5($settings->getSetting('core.secret', 'staticSalt')
                . $this->password
                . $this->salt);
        } else
        {
            $this->password = '';
        }

        // The hook will be called here
        parent::_insert();
    }

    protected function _postInsert()
    {
        parent::_postInsert();

        // Create auth stuff
        $context = Engine_Api::_()->authorization()->context;

        // View
        $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'auth_view');
        if(empty($view_options) || !is_array($view_options))
        {
            $view_options = array('member', 'network', 'registered', 'everyone');
        }
        foreach($view_options as $role)
        {
            $context->setAllowed($this, $role, 'view', true);
        }

        // Comment
        $comment_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'auth_comment');
        if(empty($comment_options) || !is_array($comment_options))
        {
            $comment_options = array('member', 'network', 'registered', 'everyone');
        }
        foreach($comment_options as $role)
        {
            $context->setAllowed($this, $role, 'comment', true);
        }
    }

    protected function _update()
    {
        $settings = Engine_Api::_()->getApi('settings', 'core');

        // Hash password if being updated
        if(!empty($this->_modifiedFields['password']))
        {
            if(empty($this->salt))
            {
                $this->salt = (string) rand(1000000, 9999999);
            }
            $this->password = md5($settings->getSetting('core.secret', 'staticSalt')
                . $this->password
                . $this->salt);
        }

        // Update enabled, hook will set to false if necessary
        if(!empty($this->_modifiedFields['approved']) ||
            !empty($this->_modifiedFields['verified']) ||
            !empty($this->_modifiedFields['enabled']) ||
            !empty($this->_modifiedFields['deleted']))
        {
            if(2 === (int) $settings->getSetting('user.signup.verifyemail', 0))
            {
                $this->enabled = ( $this->approved && $this->verified );
            } else
            {
                $this->enabled = (bool) $this->approved;
            }
            $this->enabled = $this->enabled && !$this->deleted;
        }

        /*FIXME: Logging of name fields change*/
        //var_dump($this->_modifiedFields);
        if ( (!empty($this->_modifiedFields['first_name']) || !empty($this->_modifiedFields['last_name']) || !empty($this->_modifiedFields['middle_name']) || !empty($this->_modifiedFields['displayname'])) &&
            time() - strtotime($this->creation_date) > 20 )
        {
            try{
                throw new Exception("getStackTrace");
            }catch(Exception $e){
                $message =  "\nUTC: " . date('Y-m-d H:i:s').
                    "\nURL: //"  . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'] .
                    "\nUSER_ID: ". $this->getIdentity().
                    "\nVIEWER_ID: ". Engine_Api::_()->user()->getViewer()->getIdentity().
                    "\nFROM ". $this->_cleanData['last_name'] . ' ' . $this->_cleanData['first_name'] . ' ' . $this->_cleanData['middle_name'] . ' ('. $this->_cleanData['displayname'] . ')'.
                    "\n TO ". $this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name . ' ('. $this->displayname . ')'.
                    "\n STACK TRACE:\n" . $e->getTraceAsString()."\n\n";
                @file_put_contents(APPLICATION_PATH_TMP.'/log/change-fio.log', $message, FILE_APPEND);
            }
        }

        if (!empty($this->_modifiedFields['first_name']) || !empty($this->_modifiedFields['last_name'])){
            $this->displayname = $this->first_name . ' '. $this->last_name;
        }

        // Call parent
        parent::_update();
    }

    protected function _delete()
    {
        // Check level
        $level = Engine_Api::_()->getItem('authorization_level', $this->level_id);
        if($level->flag == 'superadmin')
        {
            throw new User_Model_Exception('Cannot delete superadmins.');
        }

        // Remove from online users
        $table = Engine_Api::_()->getDbtable('online', 'user');
        $table->delete(array('user_id = ?' => $this->getIdentity()));

        // Remove fields values
        Engine_Api::_()->fields()->removeItemValues($this);

        // Call parent
        parent::_delete();
    }

    // Ownership

    public function isOwner(Core_Model_Item_Abstract $owner)
    {
        // A user only can be owned by self
        return ( $owner->getGuid(false) === $this->getGuid(false) );
    }

    public function getOwner($recurseType = null)
    {
        // A user only can be owned by self
        return $this;
    }

    public function getParent($recurseType = null)
    {
        // A user can only belong to self
        return $this;
    }

    // Blocking

    public function isBlocked($user)
    {
        // Check auth?
        if(!Engine_Api::_()->authorization()->isAllowed('user', $user, 'block'))
        {
            return false;
        }

        $table = Engine_Api::_()->getDbtable('block', 'user');
        $select = $table->select()
            ->where('user_id = ?', $this->getIdentity())
            ->where('blocked_user_id = ?', $user->getIdentity())
            ->limit(1);
        $row = $table->fetchRow($select);
        return ( null !== $row );
    }

    public function isBlockedBy($user)
    {
        // Check auth?
        if(!Engine_Api::_()->authorization()->isAllowed('user', $user, 'block'))
        {
            return false;
        }

        $table = Engine_Api::_()->getDbtable('block', 'user');
        $select = $table->select()
            ->where('user_id = ?', $user->getIdentity())
            ->where('blocked_user_id = ?', $this->getIdentity())
            ->limit(1);
        $row = $table->fetchRow($select);
        return ( null !== $row );
    }

    public function getBlockedUsers()
    {
        $user = Engine_Api::_()->user()->getViewer();
        // Check auth?
        if(!Engine_Api::_()->authorization()->isAllowed('user', $user, 'block'))
        {
            return array();
        }

        $table = Engine_Api::_()->getDbtable('block', 'user');
        $select = $table->select()
            ->where('user_id = ?', $this->getIdentity());

        $ids = array();
        foreach($table->fetchAll($select) as $row)
        {
            $ids[] = $row->blocked_user_id;
        }

        return $ids;
    }

    public function addBlock(User_Model_User $user)
    {
        /* @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('Zend_Cache');
        // Check auth?
        //die(Engine_Api::_()->authorization()->isAllowed($user, $this, 'block'));
        if(!Engine_Api::_()->authorization()->isAllowed('user', $user, 'block'))
        {
            return $this;
        }

        if(!$this->isBlocked($user) && $user->getGuid(false) != $this->getGuid(false))
        {
            Engine_Api::_()->getDbtable('block', 'user')
                ->insert(array(
                    'user_id' => $this->getIdentity(),
                    'blocked_user_id' => $user->getIdentity()
                ));
            $cache->remove('user_search_'.$this->getIdentity());
        }

        return $this;
    }

    public function removeBlock(User_Model_User $user)
    {
        /* @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('Zend_Cache');
        // Check auth?
        if(!Engine_Api::_()->authorization()->isAllowed('user', $user, 'block'))
        {
            return $this;
        }

        Engine_Api::_()->getDbtable('block', 'user')
            ->delete(array(
                'user_id = ?' => $this->getIdentity(),
                'blocked_user_id = ?' => $user->getIdentity()
            ));
        $cache->remove('user_search_'.$this->getIdentity());

        return $this;
    }

    // Interfaces

    /**
     * Gets a proxy object for the likes handler
     *
     * @return Engine_ProxyObject
     * */
    public function likes()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
    }

    /**
     * Gets a proxy object for the comment handler
     *
     * @return Engine_ProxyObject
     * */
    public function comments()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
    }

    /**
     * Gets a proxy object for the fields handler
     *
     * @return Engine_ProxyObject
     */
    public function fields()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getApi('core', 'fields'));
    }

    /**
     * Gets a proxy object for the membership handler
     *
     * @return Core_Model_DbTable_MembershipProxyMock
     */
    public function membership()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('membership', 'user'));
    }

    public function lists()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('lists', 'user'));
    }

    /**
     * Gets a proxy object for the fields handler
     *
     * @return Engine_ProxyObject
     */
    public function status()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('status', 'core'));
    }

    // Utility

    protected function _readData($spec)
    {
        if(is_scalar($spec))
        {
            // Identity
            if(is_numeric($spec))
            {
                // Can't use find because it won't return a row class
                $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("user_id = ?", $spec));
            }

            // By email
            else if(is_string($spec) && strpos($spec, '@') !== false)
            {
                $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("email = ?", $spec));
            }

            // By username
            else if(is_string($spec))
            {
                $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("username = ?", $spec));
            }
        }

        parent::_readData($spec);
    }
    public function getNoPhotoUrl() {
        return (!isset($this->gender) || $this->gender == 2) ? '/application/modules/User/externals/images/man.jpg' : '/application/modules/User/externals/images/woman.jpg';
    }

    public function getAutosizedBackground($type = null, $nophotoImage = null, $noScale = false)
    {
        if ($nophotoImage == null){
            $nophotoImage = $this->getNoPhotoUrl();
        }
        return parent::getAutosizedBackground($type, $nophotoImage);
    }

    /*for profile-tabs widget - User not implements Core_Model_Item_TreeNode, that's why here need to define some methods..*/
    public function getTabs()
    {
        /* @var Core_Model_DbTable_Tabs $tabTable */
        $tabTable = Engine_Api::_()->getItemTable('tab');
        $tabs = $tabTable->getTabsForItem($this);
        if (!count($tabs)){
            $db = $tabTable->getAdapter();
            $db->beginTransaction();
            try{
                $tabs = $tabTable->setupDefaultItemTabs($this);

                foreach($tabs as $tab){
                    if (Engine_Api::_()->hasItemType($tab->widget) && !count( $this->getDivisions($tab->widget) )){
                        Engine_Api::_()->getItemTable('division')->addDivision(array(
                            'tab_id'        => $tab->getIdentity(),
                            'title'         => '',
                            'contain_type'  => $tab->widget,
                            'subject_type'  => 'user',
                            'subject_id'    => $this->getIdentity(),
                            'order'         => '1'
                        ));
                    }
                    if (Engine_Api::_()->hasItemType($tab->widget)){
                        $tab->totalItemCountRefresh();
                    }
                }
                $db->commit();
            } catch (Exception $ex) {
                $db->rollBack();
                throw new Exception("Error during User tabs autofill:\n".$ex);
            }
        }
        return $tabs;
    }
    public function getTab($widget)
    {
        $tabs = Engine_Api::_()->getItemTable('tab')->getTabsForItem($this, array($widget));
        return count($tabs)?$tabs[0]:null;
    }

    public function getDivisions($itemType)
    {
        return Engine_Api::_()->getItemTable('division')->getDivisionsForItem($this, $itemType);
    }

    public function updateTabCount($itemType)
    {
        $tab = $this->getTab($itemType);
        if ($tab){
            $tab->totalItemCountRefresh();
            return $tab->total_item_count;
        }
        return 0;
    }

    /*CHECKAGE - IF required fields filled, and set flag then*/
    public function getUnfilledRequiredFields()
    {
        if ((int)$this->is_required_fields_filled){
            return array();
        }
        $allModelFields = array('First Name'=>'first_name', 'Middle Name'=>'middle_name','Last Name'=>'last_name','Gender'=>'gender','Birthdate'=>'birthdate','Mobilephone'=>'mobilephone','Home phone'=>'homephone','Status'=>'profile_status','Email'=>'email');

        $fieldSettings = Engine_Api::_()->core()->getUserFieldsSettings();
        $requiredFields = array();
        $requiredCols = array();
        if (isset( $fieldSettings[$this->profile_status] )){
            foreach($fieldSettings[$this->profile_status] as $field=>$how){
                if ($how == 'required'){
                    if (in_array($field, $allModelFields)){
                        $requiredCols[] = $field;
                    }else{
                        $requiredFields[] = $field;
                    }
                }
            }
        }
        /*check engine4_users cols, that must be filled*/
        $failedCommonFields = array();
        foreach($requiredCols as $col){
            if (!$this->$col){
                $failedCommonFields[$col] = array('label'=>array_search($col, $allModelFields),'name'=>$col, 'category' => 'personal_information' ) ;
            }
        }
        /*check engine4_user_fields_values - for other actions*/
        $failedCustomFields = array();
        $db = $this->_getTable()->getAdapter();
        if (!empty($requiredFields)){
            $fieldSelect = $db->select()
                ->from('engine4_user_fields_meta', array('field_id', 'name', 'label'))
                ->where('name in (?)', $requiredFields)
                ->join('engine4_user_fields_values','engine4_user_fields_values.field_id = engine4_user_fields_meta.field_id', array('value'))
                ->where('item_id = ?', $this->getIdentity());
            $values = $db->fetchAll($fieldSelect);
            $keyedValues = array();
            foreach($values as $value){
                $keyedValues[$value['name']] = $value;
            }

            foreach($requiredFields as $field){
                if (empty($keyedValues[$field]['value']) || $keyedValues[$field]['value']=='0'){
                    $failedCustomFields[$field] = $field;
                }
            }

            if (count($failedCustomFields)){
                $labelsSelect = $db->select()->from('engine4_user_fields_meta', array('name', 'label'))
                    ->from('engine4_user_fields_meta AS categoryMeta', array('category' =>  'categoryMeta.name'))
                    ->where('engine4_user_fields_meta.data_type = categoryMeta.data_type AND categoryMeta.type = ?', 'heading')
                    ->where('engine4_user_fields_meta.name in (?)', $failedCustomFields)->order('engine4_user_fields_meta.order ASC');
                $labels = $db->fetchAll( $labelsSelect );
                $failedCustomFields = [];
                foreach($labels as $label){
                    $failedCustomFields[$label['name']] = $label;
                }
            }
        }
        $allUnfilled = array_merge($failedCommonFields, $failedCustomFields);
        if (!count($allUnfilled)){
            $this->is_required_fields_filled = 1;
            $this->save();
        }
        return $allUnfilled;
    }

    public function getAllFieldsByCategories()
    {
        $db = $this->_getTable()->getAdapter();
        $fieldSelect = $db->select()
            ->from('engine4_user_fields_meta', array('field_id', 'name', 'label', 'type', 'hidden'))
            ->joinLeft('engine4_user_fields_values','engine4_user_fields_values.field_id = engine4_user_fields_meta.field_id', array('value'))
            ->where('item_id = ?', $this->getIdentity())
            ->from('engine4_user_fields_meta AS categoryMeta', array('category' =>  'categoryMeta.name', 'categoryLabel'=> 'categoryMeta.label'))
            ->where('engine4_user_fields_meta.data_type = categoryMeta.data_type AND categoryMeta.type = ?', 'heading')
            ->order('engine4_user_fields_meta.order ASC');
        $fields = $db->fetchAll($fieldSelect);
        $fieldsCategorized = array();
        foreach($fields as $field){
            try{
                if ($field['type'] == 'multitext') $field['value'] = Zend_Json::decode ($field['value']);
            }catch(Exception $e){/*SILENCE*/}
            $fieldsCategorized[$field['category']][$field['name']] = $field;
        }

        return $fieldsCategorized;
    }

    public function getAllFieldValues(){

        $db = Engine_Db_Table::getDefaultAdapter();
        $tb_prefix = Engine_Db_Table::getTablePrefix();

        $basicFields = $this->toArray();

        $query = $db->select()
            ->from(array ('v' => $tb_prefix . 'user_fields_values'), array ())
            ->joinInner(array ('m' => $tb_prefix . 'user_fields_meta'), 'm.field_id = v.field_id', array ())
            ->columns(array ('m.name','v.value'))
            ->where('v.item_id = ?', $basicFields['user_id']);
        $extraFields = $db->fetchPairs($query);

        $user = array_merge($basicFields, $extraFields);
        return $user;

    }

    public function getDiplomIdIn($subject)
    {
        if (!$subject){
            return array();
        }
        $membership = $subject->membership()->getMemberInfo($this);

        return $membership && isset($membership->diplom_id) ? $membership->diplom_id : null;

    }

    /* фильтрация дочерних итемов для пользователя отличаеться от базовой логики (parent_type, parent_id) */
    public function filterSubItemsSelect($select, $subItemType, $domainRule = null) {
        if ($domainRule === null) $domainRule = Engine_Api::_()->core()->getDomainFilter();
        if ($domainRule){
            $select->where('domain IN (?)', $domainRule);
        }
        $relationMembership = array (
            'group',
            'event',
            'course'
        );
        $relationOwner = array (
            'article',
            'album',
            'folder_attachment'
        );
        $relationNoParent = array (
            'album'
        );

        $select->reset(Zend_Db_Select::ORDER);
        $select->order('modified_date DESC');
        $select->where('repost_id = ?', 0);

        $userId = $this->getIdentity();

        if (in_array($subItemType, $relationMembership)) {
            $db = $this->getTable()
                ->getAdapter();

            $subselect = $db->select()
                ->from('engine4_' . $subItemType . '_membership', array (
                    'resource_id'
                ))
                ->where('user_id = ?', $userId)
                ->where('user_approved = ?', 1);

            $select->where($subItemType . '_id in (?)', new Zend_Db_Expr($subselect->__toString()));
        } else {
            if (in_array($subItemType, $relationOwner)) {
                $select->where('owner_id = ?', $userId);
            } else {
                $select->where('user_id = ?', $userId);
            }

            if (in_array($subItemType, $relationNoParent)) {
                $select->where("parent_type = '' or parent_type = 'user'")
                    ->where("parent_id = 0 or parent_id = ?", $userId);
            }
        }
        return $select;
    }

    public function getAnyModeratedSchoolEntity()
    {
        $status = !empty($this->school_member) ? $this->school_member : '';
        return $status && in_array(substr($status, 0, 1), ['M' /*moderator*/, 'O' /*owner*/])
            ? Engine_Api::_()->getItem('school_entity', (int)substr($status, 1))
            : null;
    }

    public function getRegionCode()
    {
        return $this->region_code ?  sprintf('%02d', (int)$this->region_code) : '99';
    }

    //метод для элемента Core_View_Helper_FormAuthUserList
    public function getFormListPresention()
    {
        return array (
            'id'    => $this->getIdentity(),
            'value' => 1,
            'description' => Zend_Registry::get('Zend_View')->itemPhoto($this, 'thumb.icon').' '. $this->getFIO()
        );
    }

    //View counters - for indicate new messages in tabs
    protected $_metadataRow = null;
    protected function _fetchMetadata()
    {
        /* TODO необходимо сделать возможность получать мета-данные по-колоночно, иначе бывают очень толстые поля, например viewed_items_json */
        if (!$this->_metadataRow){
            $db = $this->getTable()->getAdapter();
            $this->_metadataRow = $db->fetchRow( $db->select()->from('engine4_user_metadata')->where('user_id = ?', $this->getIdentity()) );
            return $this->_metadataRow ? $this->_metadataRow :
                $this->_metadataRow = [
                    'viewed_counters_json' => [],
                    'viewed_items_json'    => []
                ];
        }
        return $this->_metadataRow;
    }

    protected function _fetchJsonMetadataField($field)
    {
        $this->_fetchMetadata();
        $this->_metadataRow[$field] = isset($this->_metadataRow[$field]) ? $this->_metadataRow[$field] : [];
        if (is_string($this->_metadataRow[$field])) {
            try {
                $this->_metadataRow[$field] = !$this->_metadataRow[$field] ? [] : json_decode($this->_metadataRow[$field], true);
            } catch (Exception $e) {
                $this->_metadataRow[$field] = [];
            }
        }
        return $this->_metadataRow[$field];
    }

    protected function _saveMetadata()
    {
        $db = $this->getTable()->getAdapter();
        $this->_fetchMetadata();
        $metadataRow = $this->_metadataRow;
        foreach($metadataRow as $key=>$data){
            if (is_array($data) || is_object($data)){
                $metadataRow[$key] = json_encode($data);
            }
        }
        if (isset( $this->_metadataRow['user_id'])){
            $db->update('engine4_user_metadata', $metadataRow, ['user_id = ?'=>$this->getIdentity()]);
        }else{
            try {
                $db->insert('engine4_user_metadata', array_merge($metadataRow, ['user_id' => $this->getIdentity()]));
                $this->_metadataRow['user_id'] = $this->getIdentity();
            }catch(Exception $e){
                $db->update('engine4_user_metadata', $metadataRow, ['user_id = ?'=>$this->getIdentity()]);
            }
        }
    }

    public function getViewedCounter($key)
    {
        $counters = $this->_fetchJsonMetadataField('viewed_counters_json');
        if ($key === null){
            return $counters;
        }
        return isset($counters[$key]) ? $counters[$key] : 0;
    }

    /**
     * @return null|User_Model_AccessToken
     */
    public function getAccessToken()
    {
        // Check user token
        /* @var $tokens_table User_Model_DbTable_AccessTokens */
        $tokens_table = Engine_Api::_()->getDbTable('AccessTokens', 'user');
        if (!($token = $tokens_table->getToken($this)) || $token->isExpired()) {
            $token = $tokens_table->createNew($this);
        }
        return $token;
    }

    public function setViewedCounter($key, $count)
    {
        $this->_fetchJsonMetadataField('viewed_counters_json');
        if (isset($this->_metadataRow['viewed_counters_json'][$key]) && $count == $this->_metadataRow['viewed_counters_json'][$key]){
            return;
        }
        $this->_metadataRow['viewed_counters_json'][$key] = $count;
        $this->_saveMetadata();
        return $this;
    }

    public function getFrozenFields()
    {
        $frozen = $this->_fetchJsonMetadataField('frozen_fields');
        if ($this->academyStatus() == "teacher_approved"){
            $frozen = array_merge($frozen, ['first_name', 'last_name', 'middle_name', 'birthdate', 'citizenship', 'profile_status', 'gender', 'mobilephone', 'homephone']);
        }else if ($this->checked){
            $frozen = array_merge($frozen, ['first_name', 'last_name', 'middle_name', 'birthdate', 'gender', 'mobilephone']);
        }
        return $frozen;
    }

    public function addFrozenFields($fields)
    {
        $frozen = $this->getFrozenFields();
        $frozen = array_values( array_unique(array_merge($frozen, $fields)) );
        $this->_metadataRow['frozen_fields'] = $frozen;
        $this->_saveMetadata();
        return $this;
    }

    public function unsetFrozenFields($fields){
        $oldFrozen = $this->getFrozenFields();
        $newFrozen = array_diff($oldFrozen, $fields);

        if(count($newFrozen) != count($oldFrozen)){
            $this->_metadataRow['frozen_fields'] = array_values($newFrozen);
            $this->_saveMetadata();
        }

        return $this;
    }

    public function isViewed(Core_Model_Item_Abstract $item = null)
    {
        if (!$item) return false;
        $viewedItems = $this->_fetchJsonMetadataField('viewed_items_json');
        return in_array($item->getGuid(), $viewedItems);
    }

    public function setViewed(Core_Model_Item_Abstract $item = null, $autoSave = true)
    {
        if (!$item) return false;
        $this->_fetchJsonMetadataField('viewed_items_json');
        if ( in_array($item->getGuid(),$this->_metadataRow['viewed_items_json']) ){
            return;
        }
        $this->_metadataRow['viewed_items_json'][] = $item->getGuid();
        $this->_saveMetadata();
        return $this;
    }

    public function toString($attribs = array())
    {
        $attribs = array_merge(['title' => $this->getFIO(false)], $attribs);
        if (!empty($attribs['short'])){
            unset($attribs['short']);
            return Zend_Registry::get('Zend_View')->htmlLink($this->getHref(), $this->getFIO(true), $attribs);
        }
        return parent::toString($attribs);
    }
}