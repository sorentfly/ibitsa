<?

/**
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
abstract class Core_Controller_Action_Standard extends Engine_Controller_Action
{
    public $autoContext = true;
    public $tb_prefix;
    public $db;
    public $translate;
    public $base_href;

    protected $_isAjax = false;

    /**
     * Core_Controller_Action_Standard constructor.
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = [])
    {
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
        $this->base_href = rtrim((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/');
        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->_isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        // Pre-init setSubject
        try {
            if ('' !== ($subject = trim((string)$request->getParam('subject'))) && $subject != /*strange notice fix - group_photo is DEPRECATED */ 'group_photo') {
                $subject = Engine_Api::_()->getItemByGuid($subject);
                if (($subject instanceof Core_Model_Item_Abstract) && $subject->getIdentity() && !Engine_Api::_()->core()->hasSubject()) {
                    Engine_Api::_()->core()->setSubject($subject);
                }
            }
        } catch (Exception $e) {}

        if (!$request->getParam('module') || !$request->getParam('controller')){
            parent::__construct($request, $response, $invokeArgs);
            return;
        }

        //SSL redirects for admins
        $viewer = Engine_Api::_()->user()->getViewer();
        if ( $viewer && $viewer->getIdentity() ){
            $M = mb_strtolower($request->getParam('module'));
            $C = mb_strtolower($request->getParam('controller'));
            if ( count($viewer->getUnfilledRequiredFields()) && ( $M!='user' || $C!='edit'&&$C!='auth'&&$C!='signup' ) && ($M!='activity' || $C!='notifications') ){
                header('Location: /members/edit/profile');
                die();
            }
            if ( (!defined('PREVENT_SSL') || !PREVENT_SSL) && !_ENGINE_SSL ){
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                die();
            }
        }

        //Locale overload
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($domainSettings['language'])){
            Zend_Registry::get('Zend_Translate')->setLocale($domainSettings['language']);
            Zend_Registry::set('Locale', new Zend_Locale($domainSettings['language']) );
        }else if (!empty($domainSettings['default_locale']) && !$viewer->getIdentity()){
            Zend_Registry::get('Zend_Translate')->setLocale($domainSettings['default_locale']);
            Zend_Registry::set('Locale', new Zend_Locale($domainSettings['default_locale']) );
        }

        //
        $this->processApplications($request);
        // Parent
        parent::__construct($request, $response, $invokeArgs);
        //After init
        $this->processReferrals();
    }


    /**
     *
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper->contextSwitch->setAutoJsonSerialization(false);//FIX SECURITY HOLE

        if (($layout = $this->_getParam('layout')) && $layout == 'ajax'){
            $this->_helper->layout->setLayout('ajax');
        }

        if (Engine_Api::_()->core()->hasSubject() && isset($_SERVER['HTTP_HOST']) && !$this->_isAjax){
            /* see Core_Plugin_Core->onItemCreateAfter() */
            try{
                $subject = Engine_Api::_()->core()->getSubject();
                $request = Zend_Controller_Front::getInstance()->getRequest();
                if ($subject && ($subject instanceof Core_Model_Item_TreeNode) && !empty($subject->domain)
                    && $subject->domain !=  Engine_Api::_()->core()->getNowDomainSettings()['key'] && $this->_helper->contextSwitch->getCurrentContext() != "smoothbox"){
                    $M_C = mb_strtolower($request->getParam('module')). '_'. mb_strtolower($request->getParam('controller'));
                    $wholeDs = Engine_Api::_()->core()->getDomainsSettings();
                    if (isset($wholeDs[$subject->domain]) && !in_array($M_C, ['zftsh_activity', 'core_item', 'olympic_diplomas']) && mb_strtolower($request->getParam('controller') != 'membership')){
                        header('Location: //'.($wholeDs[$subject->domain]['domain']). $_SERVER['REQUEST_URI']);
                        die();
                    }
                }
            }catch(Exception $e){/*SILENCE*/}
        }
    }

    /**
     *
     */
    private function processReferrals()
    {
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($domainSettings['referralsEnabled']) && !empty($_GET['ref']) && ($referral = (int)$_GET['ref']) > 0){
            $user = Engine_Api::_()->getItem('user', $referral);
            if ($user){
                setcookie('referral', $referral, 0, '/');
                if( !empty($_GET['utm_term']) )
                {
                    $_SESSION['referral_term'] =  urldecode($_GET['utm_term']);
                }
                $this->view->referal_info = $user->toArray();
            }
        }
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    private function processApplications(Zend_Controller_Request_Abstract $request)
    {
        if ( !$request->getParam('application_token')
            || !_ENGINE_SSL
            || !($appToken = Engine_Api::_()->getItemTable('application_token')->findToken($request->getParam('application_token')))
            || mb_strpos($request->getControllerName(),'admin')===0) return false;

        $appPerm = Engine_Api::_()->application()->getPrivilegeLevelFor($appToken->privelegies,
            [
                'module'    => $request->getModuleName(),
                'controller'=>$request->getControllerName(),
                'action'=>$request->getActionName()
            ]
        );
        if (!$appPerm){
            return false;
        }

        $levelId = 4;
        $advanced = [];
        if (is_int($appPerm)){
            $levelId = $appPerm;
        }else if (is_array($appPerm)){
            $advanced = $appPerm;
            if (isset($advanced['level_id'])){
                $levelId = $advanced['level_id'];
                unset($advanced['level_id']);
            }
        }

        $appAccount = new Application_Model_AllowAccount(['data' => ['level_id' => $levelId]]);
        Engine_Api::_()->user()->setViewer($appAccount);
        $request->setParams($advanced);
        return true;
    }

    /**
     *
     */
    public function postDispatch()
    {
        $layoutHelper = $this->_helper->layout;
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        $currentLayout = $layoutHelper->getLayout();
        if ($layoutHelper->isEnabled() && !$currentLayout) {
            $layoutHelper->setLayout('default');
        }
        if (('default' == $currentLayout || !$currentLayout) && $this->_getParam('module', false)) {
            // Increment page views and referrer
            /* bitsa: temporary statistics DEPRECATE -no need to do updates/inserts each request
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.views');
            Engine_Api::_()->getDbtable('referrers', 'core')->increment();
            */
        }
    }

    /**
     * @param $to
     * @param array $options
     */
    protected function _redirectCustom($to, $options = [])
    {
        $options = array_merge(array(
            'prependBase' => false
        ), $options);

        // Route
        if (is_array($to) && empty($to['uri'])) {
            $route = (!empty($to['route']) ? $to['route'] : 'default');
            $reset = (isset($to['reset']) ? $to['reset'] : true);
            unset($to['route']);
            unset($to['reset']);
            $to = $this->_helper->url->url($to, $route, $reset);
            // Uri with options
        } else if (is_array($to) && !empty($to['uri'])) {
            $to = $to['uri'];
        } else if (is_object($to) && method_exists($to, 'getHref')) {
            $to = $to->getHref();
        }

        if (!is_scalar($to)) {
            $to = (string)$to;
        }

        $message = (!empty($options['message']) ? $options['message'] : Zend_Registry::get('Zend_Translate')->_('Changes saved!') );

        switch ($this->_helper->contextSwitch->getCurrentContext()) {
            case 'smoothbox':
                return $this->forward('success', 'utility', 'core', array(
                    'messages' => array($message),
                    'smoothboxClose' => true,
                    'redirect' => $to
                ));
                break;
            case 'json':
            case 'xml':
            case 'async':
                // What should be do here?
                //break;
            default:
                return $this->_helper->redirector->gotoUrl($to, $options);
                break;
        }
    }

    /**
     *
     */
    public function disab() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
}