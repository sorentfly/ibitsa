<?php
class User_AuthController extends Core_Controller_Action_Standard
{
  public function loginAction()
  {
    if ($this->getRequest()->isPost() && empty($_REQUEST['x'])){
        die(); /* NOTE: временно, защита от дрочения логина */
    }
    //NOTE: интерфейс залогинивания целиком перенесён в виджет user.login-or-signup, здесь только обработка
    if( !$this->getRequest()->isPost() || !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || !empty($_SERVER['HTTP_ORIGIN'])) ) {
        return $this->forward('index', 'index', 'core', array());
    }
    header('Set-Cookie: ');
    $this->_helper->layout->disableLayout();
    // Already logged in
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already signed in.');
      if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
        $this->_helper->redirector->gotoRoute(array(), 'default', true);;
      }
      return;
    }

    // Make form
    $this->view->form = $form = new User_Form_Login();
    $form->setAction($this->view->url(array('return_url' => null)));
    $form->populate(array(
      'return_url' => $this->_getParam('return_url'),
    ));

    // Facebook login
//    if( User_Model_DbTable_Facebook::authenticate($form) ) {
//      // Facebook login succeeded, redirect to home
//      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
//    }

    // Render
    $this->_helper->content
        //->setNoRender()
        ->setEnabled()
        ;
        
    // Not a post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }

    // Form not valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Check login creds
    /* @var string $email */
      /* @var string $password */
    extract($form->getValues()); // $email, $password
    $user_table = Engine_Api::_()->getDbtable('users', 'user');
    $user_select = $user_table->select()
      ->where('email = ?', $email);          // If post exists
    $user = $user_table->fetchRow($user_select);
    // Get ip address
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    
    // Check if user exists
    if( empty($user) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No user or bad password');//'No record of a member with that email was found.'
      $form->addError(Zend_Registry::get('Zend_Translate')->_('No user or bad password'));//'No record of a member with that email was found.'

      // Register login
      Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
        'email' => $email,
        'ip' => $ipExpr,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'state' => 'no-member',
		'useragent' => $_SERVER['HTTP_USER_AGENT']
      ));

      return;
    }

    // Check if user is verified and enabled
    if( !$user->enabled ) {
      if( !$user->verified ) {
        $this->view->status = false;
        
        $resend_url = '/signup/resend/' . $user->user_id;
		//$this->_helper->url->url(array('action' => 'resend', 'email'=>$email), 'user_signup', true);
        $translate = Zend_Registry::get('Zend_Translate');
        $error = $translate->_('This account still requires either email verification.');
        $error .= ' ';
        $error .= sprintf($translate->_("Click <a href='%s'>here</a> to resend the email."), $resend_url);
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));
        
        return;
      } else if( !$user->approved ) {
        $this->view->status = false;
        
        $translate = Zend_Registry::get('Zend_Translate');
        $error = $translate->translate('This account still requires admin approval.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));
        
        return;
      }
      // Should be handled by hooks or payment
      //return;
    }
    
    // Handle subscriptions
    if( Engine_Api::_()->hasModuleBootstrap('payment') ) {
      // Check for the user's plan
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if( !$subscriptionsTable->check($user) ) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'unpaid',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));
        // Redirect to subscription page
        $subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
        $subscriptionSession->unsetAll();
        $subscriptionSession->user_id = $user->getIdentity();
        return $this->_helper->redirector->gotoRoute(array('module' => 'payment',
          'controller' => 'subscription', 'action' => 'index'), 'default', true);
      }
    }
    
    // Run pre login hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginBefore', $user);
    foreach( (array) $event->getResponses() as $response ) {
      if( is_array($response) ) {
        if( !empty($response['error']) && !empty($response['message']) ) {
          $form->addError($response['message']);
        } else if( !empty($response['redirect']) ) {
          $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
        } else {
          continue;
        }
        
        // Register login
        setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'third-party',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));

        // Return
        return;
      }
    }

    // Version 3 Import compatibility
    if( empty($user->password) ) {
      $compat = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.compatibility.password');
      $migration = null;
      try {
        $migration = Engine_Db_Table::getDefaultAdapter()->select()
          ->from('engine4_user_migration')
          ->where('user_id = ?', $user->getIdentity())
          ->limit(1)
          ->query()
          ->fetch();
      } catch( Exception $e ) {
        $migration = null;
        $compat = null;
      }
      if( !$migration ) {
        $compat = null;
      }
      
      if( $compat == 'import-version-3' ) {

        // Version 3 authentication
        $cryptedPassword = self::_version3PasswordCrypt($migration['user_password_method'], $migration['user_code'], $password);
        if( $cryptedPassword === $migration['user_password'] ) {
          // Regenerate the user password using the given password
          $user->salt = (string) rand(1000000, 9999999);
          $user->password = $password;
          $user->save();
          Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
          // @todo should we delete the old migration row?
        } else {
          $this->view->status = false;
          $this->view->error = Zend_Registry::get('Zend_Translate')->_('No user or bad password');//Invalid credentials
          $form->addError(Zend_Registry::get('Zend_Translate')->_('No user or bad password'));//Invalid credentials supplied
          return;
        }
        // End Version 3 authentication

      } else {
        $form->addError('There appears to be a problem logging in. Please reset your password with the Forgot Password link.');

        // Register login
        setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'v3-migration',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));
        
        return;
      }
    }

    // Normal authentication
    else {
      $authResult = Engine_Api::_()->user()->authenticate($email, $password);
      $authCode = $authResult->getCode();
      Engine_Api::_()->user()->setViewer();

      if( $authCode != Zend_Auth_Result::SUCCESS ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid credentials');
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
        
        // Register login
        setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'bad-password',
		  'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));

        return;
      }
    }

    // -- Success! --

    // Register login
    $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
    setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
    $loginTable->insert(array(
      'user_id' => $user->getIdentity(),
      'email' => $email,
      'ip' => $ipExpr,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'state' => 'success',
      'active' => true,
	  'useragent' => $_SERVER['HTTP_USER_AGENT']
    ));
    $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();
    

    $lifetime = 30*86400; // Month
    Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
    Zend_Session::rememberMe($lifetime);
    

    // Increment sign-in count
    Engine_Api::_()->getDbtable('statistics', 'core')
        ->increment('user.logins');

    // Test activity @todo remove
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $viewer->lastlogin_date = date("Y-m-d H:i:s");
      if( 'cli' !== PHP_SAPI ) {
        $viewer->lastlogin_ip = $ipExpr;
      }
      $viewer->save();
    }

    // Assign sid to view for json context
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Login successful');
    $this->view->sid = Zend_Session::getId();
    $this->view->sname = Zend_Session::getOptions('name');
    if (in_array($viewer->level_id, [1,2,3,6])){
        echo('moderator '.Zend_Session::getId());
    }else{
        echo('ok '.Zend_Session::getId());
    }

  /* TODO - следущие редиректы неплохо было бы завернуть в Api соответсвующих модулей - наприме в метод hasLoginRedirect() */
  /* NOTE - свой редирект происходит и при установленной куке next_login_location */
  if (!empty($_COOKIE['next_login_location'])){
      if ( strpos($_COOKIE['next_login_location'], 'userschool') === 0 && $viewer->school_member){
        $exp = explode('_', $_COOKIE['next_login_location']);
        $entity = Engine_Api::_()->getItem('school_entity', count($exp) > 1 ? $exp[1] : substr($viewer->school_member, 1));
        if ($entity){
            echo ' ' . $entity->getHref();
        }
      }
      setcookie('next_login_location', '', time() - 86400, '/');
      die();
  }

    /* NOTE - при авторизации ЗФТШ редирект идёт свой */
    $DS = Engine_Api::_()->core()->getNowDomainSettings();
    if (!empty($DS['zftshDefaults'])){
      if ($redirect = Engine_Api::_()->zftsh()->getAuthRedirectUrl($viewer)){
        echo ' ' . $redirect;
      }
    }
    die();
  }

  public function foreignLoginAction()
  {
      /* @var $foreign_host string|boolean Domain that requires user data */
      $this->view->host = $foreign_host = $this->getParam('foreign_host');
      /* @var $redirect_url string|boolean URL user shall be redirected to */
      $redirect_uri = $this->getParam('redirect_uri');
      /* @var $viewer User_Model_User */
      $viewer = Engine_Api::_()->user()->getViewer();
      if (   !$foreign_host
          || !$redirect_uri
          || !in_array($foreign_host, explode(',', Engine_Api::_()->getApi('settings', 'core')->getSetting("core.api.externalAuthAllow")))
          || ($redirect_host = parse_url($redirect_uri, PHP_URL_HOST)) != $foreign_host
      ) {
          return $this->forward('requiresubject', 'error', 'core', array(
              'smoothboxClose' => true,
              'parentRefresh' => false,
              'messages' => array(Zend_Registry::get('Zend_Translate')->_('Произошла ошибка входа, приносим свои извинения.'))
          ));
      }

      // Not logged in
      if (!$viewer || !$viewer->getIdentity()) {
          return;
      }

      // Construct redirect
      $redirect_path = parse_url($redirect_uri, PHP_URL_PATH);
      $redirect_url = 'https://' . $redirect_host . $redirect_path;
      // Redirect user
      $this->_helper->redirector->gotoUrl(
          $redirect_url.
          '?access_token='.
          $viewer->getAccessToken()
      );
  }

  public function logoutAction()
  {
    // Check if already logged out
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already logged out.');
      if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
        $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
      return;
    }
    
    // Run logout hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutBefore', $viewer);

    // Update online status
    $onlineTable = Engine_Api::_()->getDbtable('online', 'user')
        ->delete(array(
            'user_id = ?' => $viewer->getIdentity(),
          ));
    
    // Logout
    Engine_Api::_()->user()->getAuth()->clearIdentity();
    
    if( !empty($_SESSION['login_id']) ) {
      Engine_Api::_()->getDbtable('logins', 'user')->update(array(
        'active' => 0,
      ), array(
        'login_id = ?' => $_SESSION['login_id'],
      ));
      unset($_SESSION['login_id']);
    }
    
    
    // Run logout hook
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutAfter', $viewer);
    
    $doRedirect = true;
    
    // Clear twitter/facebook session info
    
    // facebook api
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    if( $facebook && 'none' != $settings->core_facebook_enable ) {
      /*
      $logoutUrl = $facebook->getLogoutUrl(array(
        'next' => 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array(), 'default', true),
      ));
      */
      if( method_exists($facebook, 'getAccessToken') && 
          ($access_token = $facebook->getAccessToken()) ) {
        $doRedirect = false; // javascript will run to log them out of fb
        $this->view->appId = $facebook->getAppId();
        $access_array = explode("|", $access_token);
        if ( ($session_key = $access_array[1]) ) {
          $this->view->fbSession = $session_key;
        }
      }
      try {
        $facebook->clearAllPersistentData();
      } catch( Exception $e ) {
        // Silence
      }
    }
    
    unset($_SESSION['facebook_lock']);
    unset($_SESSION['facebook_uid']);
    
    // twitter api
    /*
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
    $twitter = $twitterTable->getApi();
    $twitterOauth = $twitterTable->getOauth();
    if( $twitter && $twitterOauth ) {
      try {
        $result = $accountInfo = $twitter->account->end_session();
      } catch( Exception $e ) {
        // Silence
        echo $e;die();
      }
    }
    */
    unset($_SESSION['twitter_lock']);
    unset($_SESSION['twitter_token']);
    unset($_SESSION['twitter_secret']);
    unset($_SESSION['twitter_token2']);
    unset($_SESSION['twitter_secret2']);
    
    // Response
    $this->view->status = true;
    $this->view->message =  Zend_Registry::get('Zend_Translate')->_('You are now logged out.');

    if( $doRedirect && null === $this->_helper->contextSwitch->getCurrentContext() ) {
      if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/logout')!==0 && !$this->getParam('redirect')){
      	return $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER'], array('prependBase' => false));
      } else{
		return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
    }
  }

  public function forgotAction()
  {
    // no logged in users
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // Make form
    $this->view->form = $form = new User_Form_Auth_Forgot();

    // Check request
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check data
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Check for existing user
    $user = Engine_Api::_()->getDbtable('users', 'user')
      ->fetchRow(array('email = ?' => $form->getValue('email')));
    if( !$user || !$user->getIdentity() ) {
      $form->addError('A user account with that email was not found.');
      return;
    }

    // Check to make sure they're enabled
    if( !$user->enabled ) {
      $form->addError('That user account has not yet been verified or disabled by an admin.');
      return;
    }

    // Ok now we can do the fun stuff
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $db = $forgotTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Delete any existing reset password codes
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));

      // Create a new reset password code
      $code = base_convert(md5($user->salt . $user->email . $user->user_id . uniqid(time(), true)), 16, 36);
      $forgotTable->insert(array(
        'user_id' => $user->getIdentity(),
        'code' => $code,
        'creation_date' => date('Y-m-d H:i:s'),
      ));

      // Send user an email
      Engine_Api::_()->getApi('mail', 'core')->sendSystemRaw($user, 'core_lostpassword', array(
        'host' => $_SERVER['HTTP_HOST'],
        'email' => $user->email,
        'date' => time(),
        'recipient_title' => $user->getTitle(),
        'recipient_link' => $user->getHref(),
        'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
        'object_link' => $this->_helper->url->url(array('action' => 'reset', 'code' => $code, 'uid' => $user->getIdentity())),
        'queue' => false,
      ));

      // Show success
      $this->view->sent = true;

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function resetAction()
  {
    // no logged in users
    if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // Check for empty params
    $user_id = $this->_getParam('uid');
    $code = $this->_getParam('code');

    if( empty($user_id) || empty($code) ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check user
    $user = Engine_Api::_()->getItem('user', $user_id);
    if( !$user || !$user->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check code
    $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
    $forgotSelect = $forgotTable->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('code = ?', $code);
      
    $forgotRow = $forgotTable->fetchRow($forgotSelect);
    if( !$forgotRow || (int) $forgotRow->user_id !== (int) $user->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Code expired
    // Note: Let's set the current timeout for 6 hours for now
    $min_creation_date = time() - (3600 * 24);
    if( strtotime($forgotRow->creation_date) < $min_creation_date ) { // @todo The strtotime might not work exactly right
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Make form
    $this->view->form = $form = new User_Form_Auth_Reset();
    $form->setAction($this->_helper->url->url(array()));

    // Check request
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check data
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();

    // Check same password
    if( $values['password'] !== $values['password_confirm'] ) {
      $form->addError('The passwords you entered did not match.');
      return;
    }
    
    // Db
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      // Delete the lost password code now
      $forgotTable->delete(array(
        'user_id = ?' => $user->getIdentity(),
      ));
      
      // This gets handled by the post-update hook
      $user->password = $values['password'];
      $user->save();
      
      $db->commit();

      $this->view->reset = true;
      //return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }

  public function facebookAction()
  {
    // Clear
    if( null !== $this->_getParam('clear') ) {
      unset($_SESSION['facebook_lock']);
      unset($_SESSION['facebook_uid']);
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    $facebook = $facebookTable->getApi();
    $settings = Engine_Api::_()->getDbtable('settings', 'core');

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    
    // Enabled?
    if( !$facebook || 'none' == $settings->core_facebook_enable ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Already connected
    if( $facebook->getUser() ) {
      $code = $facebook->getPersistentData('code');
      
      // Attempt to login
      if( !$viewer->getIdentity() ) {
        $facebook_uid = $facebook->getUser();
        if( $facebook_uid ) {
          $user_id = $facebookTable->select()
              ->from($facebookTable, 'user_id')
              ->where('facebook_uid = ?', $facebook_uid)
              ->query()
              ->fetchColumn();
        }
        if( $user_id &&
            $viewer = Engine_Api::_()->getItem('user', $user_id) ) {
          Zend_Auth::getInstance()->getStorage()->write($user_id);

          // Register login
          $viewer->lastlogin_date = date("Y-m-d H:i:s");

          if( 'cli' !== PHP_SAPI ) {
            $viewer->lastlogin_ip = $ipExpr;

            Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
              'user_id' => $user_id,
              'ip' => $ipExpr,
              'timestamp' => new Zend_Db_Expr('NOW()'),
              'state' => 'success',
              'source' => 'facebook',
            ));
          }
          
          $viewer->save();
        } else if( $facebook_uid ) {
          // They do not have an account
          $_SESSION['facebook_signup'] = true;
          return $this->_helper->redirector->gotoRoute(array(
            //'action' => 'facebook',
          ), 'user_signup', true);
        }
      } else {
        // Attempt to connect account
        $info = $facebookTable->select()
            ->from($facebookTable)
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetch();
        if( empty($info) ) {
          $facebookTable->insert(array(
            'user_id' => $viewer->getIdentity(),
            'facebook_uid' => $facebook->getUser(),
            'access_token' => $facebook->getAccessToken(),
            'code' => $code,
            'expires' => 0, // @todo make sure this is correct
          ));
        } else {
          //if( !empty($info['facebook_uid']) && $info['facebook_uid'] != $facebook->getUser() ) {
            // Incorrect user
            // Should we reconnect?
          //} else {
            // Save info to db
            $facebookTable->update(array(
              'facebook_uid' => $facebook->getUser(),
              'access_token' => $facebook->getAccessToken(),
              'code' => $code,
              'expires' => 0, // @todo make sure this is correct
            ), array(
              'user_id = ?' => $viewer->getIdentity(),
            ));
          //}
        }
      }

      // Redirect to home
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Not connected
    else {
      // Okay
      if( !empty($_GET['code']) ) {
        // This doesn't seem to be necessary anymore, it's probably
        // being handled in the api initialization
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
      
      // Error
      else if( !empty($_GET['error']) ) {
        // @todo maybe display a message?
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }

      // Redirect to auth page
      else {
        $url = $facebook->getLoginUrl(array(
          'redirect_uri' => (_ENGINE_SSL ? 'https://' : 'http://') 
              . $_SERVER['HTTP_HOST'] . $this->view->url(),
          'scope' => join(',', array(
            'email',
            'user_birthday',
            'user_status',
            'publish_stream',
            'offline_access',
          )),
        ));
        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    }
  }

  public function twitterAction()
  {
    // Clear
    if( null !== $this->_getParam('clear') ) {
      unset($_SESSION['twitter_lock']);
      unset($_SESSION['twitter_token']);
      unset($_SESSION['twitter_secret']);
      unset($_SESSION['twitter_token2']);
      unset($_SESSION['twitter_secret2']);
    }
    
    if( $this->_getParam('denied') ) {
      $this->view->error = 'Access Denied!';
      return;
    }

    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
    $twitter = $twitterTable->getApi();
    $twitterOauth = $twitterTable->getOauth();

    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

    // Check
    if( !$twitter || !$twitterOauth ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Connect
    try {
      
      $accountInfo = null;
      if( isset($_SESSION['twitter_token2'], $_SESSION['twitter_secret2']) ) {
        // Try to login?
        if( !$viewer->getIdentity() ) {
          // Get account info
          try {
            $accountInfo = $twitter->account->verify_credentials();
          } catch( Exception $e ) {
            // This usually happens when the application is modified after connecting
            unset($_SESSION['twitter_token']);
            unset($_SESSION['twitter_secret']);
            unset($_SESSION['twitter_token2']);
            unset($_SESSION['twitter_secret2']);
            $twitterTable->clearApi();
            $twitter = $twitterTable->getApi();
            $twitterOauth = $twitterTable->getOauth();
          }
        }
      }

      if( isset($_SESSION['twitter_token2'], $_SESSION['twitter_secret2']) ) {
        // Try to login?
        if( !$viewer->getIdentity() ) {
          
          $info = $twitterTable->select()
              ->from($twitterTable)
              ->where('twitter_uid = ?', $accountInfo->id)
              ->query()
              ->fetch();

          if( empty($info) ) {
            // They do not have an account
            $_SESSION['twitter_signup'] = true;
            return $this->_helper->redirector->gotoRoute(array(
              //'action' => 'twitter',
            ), 'user_signup', true);
          } else {
            Zend_Auth::getInstance()->getStorage()->write($info['user_id']);
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
          }
        }
        // Success
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        
      } else if( isset($_SESSION['twitter_token'], $_SESSION['twitter_secret'],
          $_GET['oauth_verifier']) ) {
        $twitterOauth->getAccessToken('http://twitter.com/oauth/access_token', $_GET['oauth_verifier']);

        $_SESSION['twitter_token2'] = $twitter_token = $twitterOauth->getToken();
        $_SESSION['twitter_secret2'] = $twitter_secret = $twitterOauth->getTokenSecret();

        // Reload api?
        $twitterTable->clearApi();
        $twitter = $twitterTable->getApi();

        // Get account info
        $accountInfo = $twitter->account->verify_credentials();

        // Save to settings table (if logged in)
        if( $viewer->getIdentity() ) {
          $info = $twitterTable->select()
              ->from($twitterTable)
              ->where('user_id = ?', $viewer->getIdentity())
              ->query()
              ->fetch();

          if( !empty($info) ) {
            $twitterTable->update(array(
              'twitter_uid' => $accountInfo->id,
              'twitter_token' => $twitter_token,
              'twitter_secret' => $twitter_secret,
            ), array(
              'user_id = ?' => $viewer->getIdentity(),
            ));
          } else {
            $twitterTable->insert(array(
              'user_id' => $viewer->getIdentity(),
              'twitter_uid' => $accountInfo->id,
              'twitter_token' => $twitter_token,
              'twitter_secret' => $twitter_secret,
            ));
          }

          // Redirect
          return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else { // Otherwise try to login?
          $info = $twitterTable->select()
              ->from($twitterTable)
              ->where('twitter_uid = ?', $accountInfo->id)
              ->query()
              ->fetch();

          if( empty($info) ) {
            // They do not have an account
            $_SESSION['twitter_signup'] = true;
            return $this->_helper->redirector->gotoRoute(array(
              //'action' => 'twitter',
            ), 'user_signup', true);
          } else {
            Zend_Auth::getInstance()->getStorage()->write($info['user_id']);

            // Register login
            $viewer = Engine_Api::_()->getItem('user', $info['user_id']);
            $viewer->lastlogin_date = date("Y-m-d H:i:s");

            if( 'cli' !== PHP_SAPI ) {
              $viewer->lastlogin_ip = $ipExpr;

              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                'user_id' => $info['user_id'],
                'ip' => $ipExpr,
                'timestamp' => new Zend_Db_Expr('NOW()'),
                'state' => 'success',
                'source' => 'twitter',
              ));
            }

            $viewer->save();

            // Redirect
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
          }
          
        }

      } else {
        
        unset($_SESSION['twitter_token']);
        unset($_SESSION['twitter_secret']);
        unset($_SESSION['twitter_token2']);
        unset($_SESSION['twitter_secret2']);
        
        // Reload api?
        $twitterTable->clearApi();
        $twitter = $twitterTable->getApi();
        $twitterOauth = $twitterTable->getOauth();
        
        // Connect account
        $twitterOauth->getRequestToken('http://twitter.com/oauth/request_token',
            'http://' . $_SERVER['HTTP_HOST'] . $this->view->url());

        $_SESSION['twitter_token']  = $twitterOauth->getToken();
        $_SESSION['twitter_secret'] = $twitterOauth->getTokenSecret();

        $url = $twitterOauth->getAuthorizeUrl('http://twitter.com/oauth/authenticate');

        return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
      }
    } catch( Services_Twitter_Exception $e ) {
      if( in_array($e->getCode(), array(500, 502, 503)) ) {
        $this->view->error = 'Twitter is currently experiencing technical issues, please try again later.';
        return;
      } else {
        throw $e;
      }
    } catch( Exception $e ) {
      throw $e;
    }
  }
  
  public function janrainAction()
  {
    // Exit if no token is posted
    if( !($token = $this->_getParam('token')) ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Get settings
    $settings = Engine_Api::_()->getDbtable('settings', 'core');
    $janrainSettings = $settings->core_janrain;
    if( empty($janrainSettings['key']) ||
        empty($janrainSettings['enable']) ||
        $janrainSettings['enable'] == 'none' ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Get info
    $viewer = Engine_Api::_()->user()->getViewer();
    $janrainTable = Engine_Api::_()->getDbtable('janrain', 'user');
    
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    
    $log = Zend_Registry::get('Zend_Log');
    
    // Call auth_info
    $post_data = array('token'  => $token,
                       'apiKey' => $janrainSettings['key'],
                       'format' => 'json',
                       'extended' => 'false'); //Extended is not available to Basic.

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    $result = curl_exec($curl);
    if( $result == false ) {
      $log->log(
          'Janrain Error' . PHP_EOL .
          'Curl error: ' . curl_error($curl) . PHP_EOL .
          'HTTP code: ' . curl_errno($curl) . PHP_EOL .
          var_export($post_data, true), Zend_Log::DEBUG);
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    curl_close($curl);
    
    // Decode json
    $auth_info = Zend_Json::decode($result, true);

    if( $auth_info['stat'] !== 'ok' ) {
      $log->log(
          'Janrain Error' . PHP_EOL .
          var_export($result, true) . PHP_EOL . 
          var_export($auth_info, true), Zend_Log::DEBUG);
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    $profile = $auth_info['profile'];
    if( !empty($auth_info['merged_poco']) ) {
      $profile['merged_poco'] = $auth_info['merged_poco'];
    }
    $identifier = $profile['identifier'];
    $provider = $profile['providerName'];
    
    // Check if already exists
    $info = $janrainTable->select()
        ->from($janrainTable)
        ->where('identifier = ?', $identifier)
        ->limit(1)
        ->query()
        ->fetch();
    
    if( $info ) {
      if( $viewer->getIdentity() ) {
        // Already associated
        $this->view->error = 'That account has already been connected to ' . 
            'another member on this site.';
      } else {
        // Sign-in
        Zend_Auth::getInstance()->getStorage()->write($info['user_id']);

        // Register login
        $viewer = Engine_Api::_()->getItem('user', $info['user_id']);
        $viewer->lastlogin_date = date("Y-m-d H:i:s");

        if( 'cli' !== PHP_SAPI ) {
          $viewer->lastlogin_ip = $ipExpr;

          Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
            'user_id' => $info['user_id'],
            'ip' => $ipExpr,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'state' => 'success',
            'source' => 'janrain',
          ));
        }

        $viewer->save();

        // Redirect
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
    } else {
      if( $viewer->getIdentity() ) {
        // Connect
        $janrainTable->insert(array(
          'user_id' => $viewer->getIdentity(),
          'identifier' => $identifier,
          'provider' => $provider,
          'token' => $token,
        ));

        // Redirect
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      } else {
        // Sign-up
        $_SESSION['janrain_signup'] = true;
        $_SESSION['janrain_signup_info'] = $profile;
        $_SESSION['janrain_signup_token'] = $token;
        return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
      }
    }
  }

  static protected function _version3PasswordCrypt($method, $salt, $password)
  {
    // For new methods
    if( $method > 0 ) {
      if( !empty($salt) ) {
        list($salt1, $salt2) = str_split($salt, ceil(strlen($salt) / 2));
        $salty_password = $salt1.$password.$salt2;
      } else {
        $salty_password = $password;
      }
    }

    // Hash it
    switch( $method ) {
      // crypt()
      default:
      case 0:
        $user_password_crypt = crypt($password, '$1$'.str_pad(substr($salt, 0, 8), 8, '0', STR_PAD_LEFT).'$');
      break;

      // md5()
      case 1:
        $user_password_crypt = md5($salty_password);
      break;

      // sha1()
      case 2:
        $user_password_crypt = sha1($salty_password);
      break;

      // crc32()
      case 3:
        $user_password_crypt = sprintf("%u", crc32($salty_password));
      break;
    }

    return $user_password_crypt;
  }
}
