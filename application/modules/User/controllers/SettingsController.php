<?

class User_SettingsController extends Core_Controller_Action_Standard
{
    protected $_user;

    public function init()
    {
        if($this->_getParam('action') == 'changemail')
        {
            //ignore require* checks
            return;
        }
        $this->_helper->requireUser();
        $this->_helper->requireSubject();
        $subject = null;
        
        if(!Engine_Api::_()->core()->hasSubject()){
        	// Can specifiy custom id
        	$id = $this->_getParam('id', null);        	
        	if($id === null) {
        		$subject = Engine_Api::_()->user()->getViewer();
        		Engine_Api::_()->core()->setSubject($subject);
        	} else {
        		$subject = Engine_Api::_()->getItem('user', $id);
        		Engine_Api::_()->core()->setSubject($subject);
        	}
        	
        }

        // Set up require's
        
        $this->_helper->requireAuth()->setAuthParams(
            $subject,
            null,
            'edit'
        );

        // Set up navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_settings', (empty($id) ? array() : array('params' => array('id' => $id))));

        $contextSwitch = $this->_helper->contextSwitch;
        $contextSwitch->initContext();
        if($this->_isAjax){
        	$this->_helper->layout()->disableLayout();
        } 
    }

    public function generalAction(){
    	
    	// Config vars
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $userSettings = Engine_Api::_()->getDbtable('settings', 'user');
        $user = Engine_Api::_()->core()->getSubject();
        $this->view->form = $form = new User_Form_Settings_General(array(
            'item' => $user
        ));
        $form->setAction($this->view->url(['module' => 'user', 'controller' => 'settings', 'action' => 'general', 'id' => $user->getIdentity()], 'user_extended'));
        $form->removeElement('accountType');


        // Removed disabled features
        if ($form->getElement('username') && (!Engine_Api::_()->authorization()->isAllowed('user', $user, 'username') || Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) <= 0)) {
            $form->removeElement('username');
        }

        // Facebook
        if ('none' != $settings->getSetting('core.facebook.enable', 'none')) {
            $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
            $facebook = $facebookTable->getApi();
            if ($facebook && $facebook->getUser()) {
                $form->removeElement('facebook');
                $form->getElement('facebook_id')->setAttrib('checked', true);
            } else {
                $form->removeElement('facebook_id');
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('facebook');
            @$form->removeElement('facebook_id');
        }

        // Twitter
        if ('none' != $settings->getSetting('core.twitter.enable', 'none')) {
            $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
            $twitter = $twitterTable->getApi();
            if ($twitter && $twitterTable->isConnected()) {
                $form->removeElement('twitter');
                $form->getElement('twitter_id')->setAttrib('checked', true);
            } else {
                $form->removeElement('twitter_id');
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('twitter');
            @$form->removeElement('twitter_id');
        }


        // Check if post and populate
        if (!$this->getRequest()->isPost()) {
            $form->populate($user->toArray());
            $form->populate(array(
                'janrainnoshare' => $userSettings->getSetting($user, 'janrain.no-share', 0),
            ));
            
            if ($this->_getParam('email_changed')){
                $form->addNotice('New E-mail was succsessefly confirmed!');
            }

            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
            return;
        }

        // Check if valid
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        // -- Process --

        $values = $form->getValues();
        //banner save operations
        if (isset($values['banner_id']) && strlen($values['banner_id']) > 0) {
            $params = array(
                'parent_id' => $user->getIdentity(),
                'parent_type' => 'user',
            );
            $storage = Engine_Api::_()->storage();
            if ($user->banner_id && ($old = $storage->get($user->banner_id))  ){
               $old->delete();
            }
            $iMain = $storage->create($form->banner_id->getFileName(), $params);
            @unlink($form->banner_id->getFileName());

            $values['banner_id'] = $iMain->getIdentity();
        }else if (array_key_exists('banner_id', $values)){
            unset($values['banner_id']);
        }

        // Check email against banned list if necessary
        if (($emailEl = $form->getElement('email')) &&
            isset($values['email']) &&
            $values['email'] != $user->email
        ) {
            
            //
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $changeMailLink = $router->assemble(array('action'=>'changemail', 'user_id'=>$user->user_id, 'token'=>md5($user->user_id. '*' . $user->creation_date.'*'. $user->email.'*'. $values['email']) ), null);
            Engine_Api::_()->getApi('mail', 'core')->sendSystemRaw($values['email'], 'user_change_email', array(
                'change_mail_link' =>$changeMailLink,
                'host' => $_SERVER['HTTP_HOST'],
                'email' => $values['email'],
                'date' => time(),
                'object_title' => $user->getTitle(),
                'object_link' => $user->getHref(),
                'object_photo' => $user->getPhotoUrl('thumb.icon')
            ));
            $form->addNotice($this->view->translate('Confirm message of e-mail change sended to %s. E-mail will change only after confirm.', $values['email']));
            $user->email_req = $values['email'];
            $values['email'] = $user->email;
        }
        
        // Check username against banned list if necessary
        if (($usernameEl = $form->getElement('username')) &&
            isset($values['username']) &&
            $values['username'] != $user->username
        ) {
            $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
            if ($bannedUsernamesTable->isUsernameBanned($values['username'])) {
                return $usernameEl->addError('This profile address is not available, please use another one.');
            }
        }
        $values['locale'] = $values['language'];
        $needRefresh = false;
        if ($values['language']!=$user->language){
            $needRefresh = true;
        }
        // Set values for user object
        $user->setFromArray($values);

        // If username is changed
        //$aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
        //$user->setDisplayName($aliasValues);

        $user->save();


        // Update account type
        /*
        $accountType = $form->getValue('accountType');
        if( isset($aliasedFields['profile_type']) )
        {
          $valueRow = $aliasedFields['profile_type']->getValue($user);
          if( null === $valueRow ) {
            $valueRow = Engine_Api::_()->fields()->getTable('user', 'values')->createRow();
            $valueRow->field_id = $aliasedFields['profile_type']->field_id;
            $valueRow->item_id = $user->getIdentity();
          }
          $valueRow->value = $accountType;
          $valueRow->save();
        }
         *
         */

        // Update facebook settings
        if (isset($facebook) && $form->getElement('facebook_id')) {
            if ($facebook->getUser()) {
                if (empty($values['facebook_id'])) {
                    // Remove integration
                    $facebookTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    $facebook->clearAllPersistentData();
                }
            }
        }

        // Update twitter settings
        if (isset($twitter) && $form->getElement('twitter_id')) {
            if ($twitterTable->isConnected()) {
                if (empty($values['twitter_id'])) {
                    // Remove integration
                    $twitterTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    unset($_SESSION['twitter_token2']);
                    unset($_SESSION['twitter_secret2']);
                    unset($_SESSION['twitter_token']);
                    unset($_SESSION['twitter_secret']);
                }
            }
        }

        // Update janrain settings
        if (!empty($values['janrainnoshare'])) {
            $userSettings->setSetting($user, 'janrain.no-share', true);
        } else {
            $userSettings->setSetting($user, 'janrain.no-share', null);
        }

        // Send success message
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Settings saved.');
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
    }
    
    public function changemailAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $user_id = $this->_getParam('user_id');
        $token = $this->_getParam('token');
        $user = Engine_Api::_()->getItem('user', $user_id);
        
        if (!$user || !$token || !$user->email_req)
        {
            return;
        }
        
        if ($token != md5($user->user_id. '*' . $user->creation_date.'*'. $user->email.'*'. $user->email_req))
        {
            return;
        }
        $user->email = $user->email_req;
		if ( mb_strpos($user->email , '@phystech.edu') !== false) {
			$user->level_id = 6;
		}
        $user->email_req = '';
        $user->save();
        
        //Login user email changed to
        $_SESSION['Zend_Auth']['storage'] = $user_id;
        setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
        $lifetime = 1209600; /* Человек запоминается системой на две недели */
        Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
        Zend_Session::rememberMe($lifetime);
        
        //show succes email change confirm
        $this->_helper->redirector->gotoRoute(array('module'=>'user', 'controller'=>'settings', 'action'=>'general', 'email_changed'=>1),null,true);
    }

    public function privacyAction()
    {
        $user = Engine_Api::_()->core()->getSubject();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $auth = Engine_Api::_()->authorization()->context;

        $this->view->form = $form = new User_Form_Settings_Privacy(array(
        		'item' => $user
        ));
        $form->setAction($this->view->url(['module' => 'user', 'controller' => 'settings', 'action' => 'privacy', 'id' => $user->getIdentity()], 'user_extended'));
        // Init blocked
        $this->view->blockedUsers = array();

        if (Engine_Api::_()->authorization()->isAllowed('user', $user, 'block')) {
            foreach ($user->getBlockedUsers() as $blocked_user_id) {
                $this->view->blockedUsers[] = Engine_Api::_()->user()->getUser($blocked_user_id);
            }
        } else {
            $form->removeElement('blockList');
        }

        if (!Engine_Api::_()->getDbtable('permissions', 'authorization')->isAllowed($user, $user, 'search')) {
            $form->removeElement('search');
        }


        // Hides options from the form if there are less then one option.
//         if (count($form->privacy->options) <= 1) {
//             $form->removeElement('privacy');
//         }
//         if (count($form->comment->options) <= 1) {
//             $form->removeElement('comment');
//         }
        
        // Populate form
        $form->populate($user->toArray());

        // Set up activity options
//         if ($form->getElement('publishTypes')) {
//             $actionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getEnabledActionTypesAssoc();
//             unset($actionTypes['signup']);
//             unset($actionTypes['postself']);
//             unset($actionTypes['post']);
//             unset($actionTypes['status']);
//             $form->publishTypes->setMultiOptions($actionTypes);
//             $actionTypesEnabled = Engine_Api::_()->getDbtable('actionSettings', 'activity')->getEnabledActions($user);
//             $form->publishTypes->setValue($actionTypesEnabled);
//         }

        // Check if post and populate
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        $form->save();
        $user->setFromArray($form->getValues())
            ->save();

        // Update notification settings
        if ($form->getElement('publishTypes')) {
            $publishTypes = $form->publishTypes->getValue();
            $publishTypes[] = 'signup';
            $publishTypes[] = 'post';
            $publishTypes[] = 'status';
            Engine_Api::_()->getDbtable('actionSettings', 'activity')->setEnabledActions($user, (array)$publishTypes);
        }

        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
    }

    public function passwordAction()
    {
        $user = Engine_Api::_()->core()->getSubject();

        $this->view->form = $form = new User_Form_Settings_Password();
        $form->setAction($this->view->url(['module' => 'user', 'controller' => 'settings', 'action' => 'password', 'id' => $user->getIdentity()], 'user_extended'));
        $form->populate($user->toArray());

        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Check conf
        if ($form->getValue('passwordConfirm') !== $form->getValue('password')) {
            $form->getElement('passwordConfirm')->addError(Zend_Registry::get('Zend_Translate')->_('Passwords did not match'));
            return;
        }

        // Process form
        $userTable = Engine_Api::_()->getItemTable('user');
        $db = $userTable->getAdapter();

        // Check old password
        $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');
        $select = $userTable->select()
            ->from($userTable, new Zend_Db_Expr('TRUE'))
            ->where('user_id = ?', $user->getIdentity())
            ->where('password = ?', new Zend_Db_Expr(sprintf('MD5(CONCAT(%s, %s, salt))', $db->quote($salt), $db->quote($form->getValue('oldPassword')))))
            ->limit(1);
        $valid = $select
            ->query()
            ->fetchColumn();

        if (!$valid) {
            $form->getElement('oldPassword')->addError(Zend_Registry::get('Zend_Translate')->_('Old password did not match'));
            return;
        }


        // Save
        $db->beginTransaction();

        try {

            $user->setFromArray($form->getValues());
            $user->save();

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
    }

    public function notificationsAction()
    {
        $user = Engine_Api::_()->core()->getSubject();

        // Build the different notification types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $notificationTypes = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();
        $notificationSettings = Engine_Api::_()->getDbtable('notificationSettings', 'activity')->getEnabledNotifications($user);

        $notificationTypesAssoc = array();
        $notificationSettingsAssoc = array();
        foreach ($notificationTypes as $type) {
            if (in_array($type->module, array('core', 'activity', 'fields', 'authorization', 'messages', 'user'))) {
                $elementName = 'general';
                $category = 'General';
            } else if (isset($modules[$type->module])) {
                $elementName = preg_replace('/[^a-zA-Z0-9]+/', '-', $type->module);
                $category = $modules[$type->module]->title;
            } else {
                $elementName = 'misc';
                $category = 'Misc';
            }

            if ($elementName == 'school'){ $category = 'Projects in schools'; }
            else if ($elementName == 'zftsh'){ $category = 'Online-school'; }

            $notificationTypesAssoc[$elementName]['category'] = $category;
            $notificationTypesAssoc[$elementName]['types'][$type->type] = 'ACTIVITY_TYPE_' . strtoupper($type->type);

            if (in_array($type->type, $notificationSettings)) {
                $notificationSettingsAssoc[$elementName][] = $type->type;
            }
        }

        ksort($notificationTypesAssoc);

        $notificationTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $notificationTypesAssoc));

        // Make form
        $this->view->form = $form = new Engine_Form(array(
            'description' => 'Which of the these do you want to receive email alerts about?' 
        ));
        $form->setAction($this->view->url(['module' => 'user', 'controller' => 'settings', 'action' => 'notifications', 'id' => $user->getIdentity()], 'user_extended'));

        if ( in_array($user->academyStatus(), ['teacher_new','teacher_approved','methodist','admin'])){
            $form->addElement('Checkbox', 'notify_academy_solved',[
                'label' => 'Отправлять уведомления о сданных учениками тестированиях в ЗФТШ',
                'value' => (int)Engine_Api::_()->fields()->getValByName($user, 'notify_academy_solved_disable') ? 0 : 1,
                'checkedValue' => 1,
                'uncheckedValue' => 0,

            ]);
        }

        $post = $this->getRequest()->getPost();
        foreach ($notificationTypesAssoc as $elementName => $info) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array)@$notificationSettingsAssoc[$elementName],
            ));
            if (!empty($post['spam'])) {
                $post[$elementName] = (array)@$notificationSettingsAssoc[$elementName];
            }
        }

        $form->addElement('Checkbox', 'spam', [
            'label' => 'Unsubscribe from all notifications by mail',
            'value' => (int)$user->spam,
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'order' => 0
        ]);

        $form->addElement('Button', 'submit_notifications', array(
            'class' => 'save',
            'label' => 'Save Changes',
            'type' => 'submit',
        ));

        // Check method
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($post)) {
            return;
        }

        if(!empty($post['spam'])) {
            $user->spam = (int)$post['spam'];
            $user->save();
            $form->addNotice('Your changes have been saved.');
            return;
        } else {
            $user->spam = 0;
            $user->save();
        }

        // Process
        $values = array();
        foreach ($form->getValues() as $key => $value) {
            if ($key == 'notify_academy_solved'){
                Engine_Api::_()->fields()->setValByName($user, 'notify_academy_solved_disable', (int)$value ? '0' : '1');
            }
            if (!is_array($value)) continue;

            foreach ($value as $skey => $svalue) {
                if (!isset($notificationTypesAssoc[$key]['types'][$svalue])) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        // Set notification setting
        Engine_Api::_()->getDbtable('notificationSettings', 'activity')
            ->setEnabledNotifications($user, $values);



        $form->addNotice('Your changes have been saved.');
    }

    public function deleteAction()
    {
        $isPopup = 'smoothbox' === $this->_helper->contextSwitch->getCurrentContext();
        $viewer = Engine_Api::_()->user()->getViewer();
        /* @var User_Model_User $user */
        $user = Engine_Api::_()->core()->getSubject();

        $doubleAuthCheck = function() use ($viewer, $user){
            //DOUBLE AUTH
            /* @see \Core_Controller_Action_Admin::__construct */
            if( !Engine_Api::_()->authorization()->isReAuthenticated() ) {
                $_SESSION['ADMIN_REQ_URL'] = $_SERVER['REQUEST_URI'];
                return $this->_helper->redirector->gotoRoute(array('controller' => 'auth', 'action' => 'login'), 'admin_default', true);
            }
            return true;
        };

        if (!$viewer->getIdentity()){
            $this->_helper->viewRenderer->setNoRender();
            return;
        }
        if (!$user->authorization()->isAllowed($viewer, User_Model_User::PERMISSION_DELETE)){
            $this->_helper->viewRenderer->setNoRender();
            return;
        }

        // Form
        $this->view->form = $form = new User_Form_Settings_Delete(['user' => $user]);

        if ($isPopup) $doubleAuthCheck();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$isPopup) $doubleAuthCheck();/*NOTE: так сделано поскольку экшен страницы рендерится при открытии любой страницы настроек
                                                  , и если просто сделать двойную авторизацию на старте экшена - каждая страница настроек потребует авторизацию */

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $db = Engine_Api::_()->getDbtable('users', 'user')->getAdapter();
        $db->beginTransaction();
        try {
            $user->deleted = 1;
            $user->search = 0;
            $user->save();
            
            $auth = Engine_Api::_()->authorization()->context;
            
            $roles = array('owner', 'member', 'network', 'registered', 'everyone');
            
            $permissions = [User_Model_User::PERMISSION_VIEW,
            		User_Model_User::PERMISSION_EDIT,
            		User_Model_User::PERMISSION_VIEW_USERNAME,
            		User_Model_User::PERMISSION_VIEW_OLYMPICS,
            		User_Model_User::PERMISSION_VIEW_HOME_ADDRESS,
            		User_Model_User::PERMISSION_VIEW_SECONDARY_EDUCATION,
            		User_Model_User::PERMISSION_VIEW_HIGHER_EDUCATION,
            		User_Model_User::PERMISSION_VIEW_CHILDS_INFO,
            		User_Model_User::PERMISSION_VIEW_WORK_INFO,
            		User_Model_User::PERMISSION_DELETE];
            
            foreach ($roles as $role){
            	foreach ($permissions as $action){
            		$auth->setAllowed($user, $role, $action, 0);
            	}
            }
                        
            $db->commit();            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        if ($user->isSelf($viewer)){
            return $this->forward('success', 'utility', 'core', array(
                'messages' => 'Пользователь успешно заблокирован.',
                'layout' => 'default-simple',
                'parentRefresh' => 3500,
            ));
        }
        return $this->forward('logout', 'auth', 'user', ['redirect' => true]);
        
        
    }
}