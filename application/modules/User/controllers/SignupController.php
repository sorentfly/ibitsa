<?
class User_SignupController extends Core_Controller_Action_Standard
{
    public $regform;

    public function indexAction()
    {
        if (Engine_Api::_()->user()->getViewer()->getIdentity()) {
            header('Location: https://' . $_SERVER['SERVER_NAME']); //Если человек авторизован, то он будет перенаправлен
            return;
        }

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || !empty($_SERVER['HTTP_ORIGIN'])) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
        } else {
            $this->view->headScript()->appendFile('/application/modules/Core/externals/scripts/notification.js');

            $this->view->headScript()->appendFile('/externals/datetime.js') /* jQuery Календарик */
                                     ->appendFile('/externals/zxcvbn.js'); /* Вычисление сложности пароля */

            if($this->view->locale()->getLocale()->__toString() === 'en') {
                $this->view->headScript()->appendFile('/application/modules/User/externals/scripts/registration_titles_en.js');
            } else {
                $this->view->headScript()->appendFile('/application/modules/User/externals/scripts/registration_titles_ru.js');
            }

            $this->view->headScript()->appendFile('/application/modules/User/externals/scripts/registration.js');

            $this->view->headLink()->appendStylesheet('/application/modules/Core/externals/styles/datetimepicker.css')
                                   ->appendStylesheet('/application/modules/User/externals/styles/registration.css');
        }


        $settings = Engine_Api::_()->getApi('settings', 'core');

        $this->view->base_href = $this->base_href;
        
        $domain = Engine_Api::_()->core()->getNowDomainSettings();
        $this->view->enableSocials = $enableSocials = empty($domain['login_socials_disabled']);
        $this->view->isSubsite = $domain['key'] != 'abitu';
                
        if ($enableSocials){
            $vkAppId = $settings->getSetting('core.vk.appid');
            $vkRedirectUri = $this->base_href . 'simple_api/social_auth.php?method=vk_auth';
            $vkDisplay = $settings->getSetting('core.vk.display'); // page, popup или mobile
            $vkApiVersion = $settings->getSetting('core.vk.version'); //Версия API
            $vkPermissions = $settings->getSetting('core.vk.permissions'); //Подробный список разрешений тут: https://vk.com/dev/permissions
            $vkResponseType = 'code';

            $yaClientId = $settings->getSetting('core.ya.clientid');

            $miptRedirectUri = $this->base_href . 'simple_api/social_auth.php?method=mipt_auth&state=xyz&scope=userinfo%20email';
            $miptClientId = $settings->getSetting('core.mipt.appid');

            $googleRedirectUri = urlencode($this->base_href . 'simple_api/social_auth.php?method=google_auth');
            $googleClientId = $settings->getSetting('core.google.clientid');

            $mailruRedirectUri = urlencode($this->base_href . 'simple_api/social_auth.php?method=mailru_auth');
            $mailruClientId = $settings->getSetting('core.mailru.clientid');

            $facebookRedirectUri = urlencode($this->base_href . 'simple_api/social_auth.php?method=facebook_auth');
            $facebookClientId = $settings->getSetting('core.facebook.key');

            $okRedirectUri = urlencode($this->base_href . 'simple_api/social_auth.php?method=ok_auth');
            $okClientId = $settings->getSetting('core.ok.clientid');

            $twitterRedirectUri = urlencode($this->base_href . 'simple_api/social_auth.php?method=twitter_auth');
            $twitterClientId = $settings->getSetting('core.twitter.clientid');


            $this->view->vkLink = 'https://oauth.vk.com/authorize?client_id=' . $vkAppId . '&scope=' . $vkPermissions . '&redirect_uri=' . urlencode($vkRedirectUri) . '&display=' . $vkDisplay . '&v=' . $vkApiVersion . '&response_type=' . $vkResponseType;
            $this->view->miptLink = 'https://mipt.ru/oauth/authorize.php?response_type=code&client_id=' . $miptClientId . '&redirect_uri=' . $miptRedirectUri;
            $this->view->yandexLink = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $yaClientId;
            $this->view->googleLink = 'https://accounts.google.com/o/oauth2/auth?redirect_uri=' . $googleRedirectUri . '&response_type=code&client_id= ' .$googleClientId . '&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile';
            $this->view->mailruLink = 'https://connect.mail.ru/oauth/authorize?client_id=' . $mailruClientId . '&response_type=code&redirect_uri=' . $mailruRedirectUri;
            $this->view->okLink = '';
            $this->view->twitterLink = '';
            $this->view->facebookLink = 'https://www.facebook.com/dialog/oauth?client_id=' . $facebookClientId . '&redirect_uri=' . $facebookRedirectUri . '&response_type=code';
        }

        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
            $_SESSION['signup_referer'] = $_SERVER['HTTP_REFERER'];
        }

        $this->view->headTitle('Registration');
        $this->view->headMeta()->appendName('description', 'Регистрация на ' . $_SERVER['SERVER_NAME']);

        $this->view->isSmoothBox = $isSmoothBox = ($this->_helper->contextSwitch->getCurrentContext() == "smoothbox");

        $this->view->form = new User_Form_Signup_Account;
    }

    public function checkAction() /* Регистрация (обработка ajax-запроса) */
    {
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $formSequence = new User_Plugin_Signup_Account;

        $form = $formSequence->getForm();

        if (!$form->isValid($this->getRequest()->getPost())) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $form->getMessages()));
            return;
        }
        $values = $form->getValues();
        $formSequence->getSession()->data['email'] = $values['email'];
        $formSequence->getSession()->data['password'] = $values['password'];

        require_once(APPLICATION_PATH_LIB.'/NameCaseLib/Library/NCL.NameCase.ru.php');
        $nameCaseLib = new NCLNameCaseRu();
        $gender = $nameCaseLib->genderDetect( $values['first_name'] . ' ' . $values['last_name'] ) == NCL::$WOMAN ? 1 : 2;

        //$this->getSession()->data['timezone_offset'] = $values['timezone_offset'];
        unset($values['terms']);
        unset($values['password_confirm']);
        unset($values['recaptcha_response_field']);
        unset($values['recaptcha_challenge_field']);
        unset($values['password']);
        unset($values['email']);

        $transactionCommited = false;
        $this->db->beginTransaction();
        $formSequence->onProcess();//Creating an user row 
        try {
            $user_id = $this->db->select('user_id')
                ->from($this->tb_prefix . 'users')
                ->where($this->tb_prefix . 'users.email= ?', $formSequence->getSession()->data['email'])
                ->query()->fetchColumn();

            $social_reg = null;

            if (isset($_SESSION['registration_info'])) {
                if (isset($_SESSION['registration_info']['vk_id'])) { //связываем аккаунт на абиту и вк
                    $social_reg = 'vk.com';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['vk_id'], 'vk');
                }

                if (isset($_SESSION['registration_info']['mipt_id'])) { //связываем аккаунт на абиту и МФТИ
                    $social_reg = 'mipt.ru';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['mipt_id'], 'mipt');
                }

                if (isset($_SESSION['registration_info']['yandex_id'])) {  //связываем аккаунт на абиту и Яндекс
                    $social_reg = 'yandex.ru';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['yandex_id'], 'yandex', $_SESSION['registration_info']['email']);
                }

                if (isset($_SESSION['registration_info']['google_id'])) { //связываем аккаунт на абиту и Google
                    $social_reg = 'google.com';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['google_id'], 'google', $_SESSION['registration_info']['email']);
                }

                if (isset($_SESSION['registration_info']['mailru_id'])) {
                    $social_reg = 'mail.ru';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['mailru_id'], 'mailru', $_SESSION['registration_info']['email']);
                }

                if (isset($_SESSION['registration_info']['fb_id'])) {
                    $social_reg = 'facebook.com';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['fb_id'], 'fb');
                }

                if (isset($_SESSION['registration_info']['twitter_id'])) {
                    $social_reg = 'twitter.com';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['twitter_id'], 'twitter');
                }

                if (isset($_SESSION['registration_info']['ok_id'])) {
                    $social_reg = 'ok.ru';
                    $this->linkSocialNetwork($user_id, $_SESSION['registration_info']['ok_id'], 'ok');
                }
            }

            $main_values = array('displayname' => $values['first_name'] . ' ' . $values['last_name'],
                'gender' => $gender,
                'profile_status' => $values['profile_status'],
                'mobilephone' => $values['mobilephone'],
                'first_name' => $values['first_name'],
                'middle_name' => $values['middle_name'],
                'last_name' => $values['last_name']
            );
            if (!empty($values['birthdate'])){
                $main_values['birthdate'] = (new DateTime($values['birthdate']))->format('Y-m-d');
            }
            if (!empty($values['country'])){
                Engine_Api::_()->fields()->setValByName( $user_id , 'country', $values['country'] );
            }
            if (!empty($values['region'])){
                Engine_Api::_()->fields()->setValByName( $user_id , 'region', $values['region'] );
            }
            if (!empty($values['city'])){
                Engine_Api::_()->fields()->setValByName( $user_id , 'city', $values['city'] );
            }

            if($social_reg) {
                $main_values['social_reg'] = $social_reg;
            }

            if (!empty($_SESSION['referral_term']) ) {
                $main_values['utm_term'] = $_SESSION['referral_term'];
            }

            if (!empty($_SESSION['utm_source']) ) {
                $main_values['utm_source'] = $_SESSION['utm_source'];
                unset($_SESSION['utm_source']);
            }

            if (!empty($_SESSION['utm_medium'])) {
                $main_values['utm_medium'] = $_SESSION['utm_medium'];
                unset($_SESSION['utm_medium']);
            }

            if (!empty($_SESSION['utm_campaign']) ) {
                $main_values['utm_campaign'] = $_SESSION['utm_campaign'];
                unset($_SESSION['utm_campaign']);
            }

            $main_values['registration_site'] = $_SERVER['HTTP_HOST'];
        
            $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();

            /* @var User_Model_User $userNew */
            $userNew = Engine_Api::_()->getItem('user', $user_id);
            if (!$userNew->profile_status){
                $userNew->profile_status = 'other';
            }
            if ( isset($userNew->email_original))
            {
                $userNew->email_original = $userNew->email;
                $userNew->save();
            }

            $DS = Engine_Api::_()->core()->getNowDomainSettings();
            if (!empty($DS['default_locale'])){
                $userNew->locale = $userNew->language = $DS['default_locale'];
                $userNew->save();
            }
            
            if ( !empty($domainSettings['referralsEnabled']) && !empty($_COOKIE['referral']) && ( $referral = (int)$_COOKIE['referral'] ) > 0 ) {
                $userRef = Engine_Api::_()->getItem('user', $referral);
                if ($userRef){
                    $this->db->insert($this->tb_prefix . 'user_referrals', 
                            array(
                                    'idmaster' => $referral, 
                                    'idslave' => $user_id, 
                                    'ip' => Engine_IP::getRealRemoteAddress(), 
                                    'cookie' => $_COOKIE['PHPSESSID'], 
                                    'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
                                    'utm_term' => !empty($_SESSION['referral_term']) ? $_SESSION['referral_term'] : '',
                                    'site'  => $domainSettings['key']
                            )
                    );
                }
                setcookie('referral', $referral, time() - 86400, '/');
            }
            
            //при рега юзера с почтой @phystech.edu - автоматически присваиваем ему level_id = 6 - костыль для мероприятий с точками проведения ( event/601 напр. )
            $emailExploded = explode('@', $userNew->email);
            if (count($emailExploded)==2 && $emailExploded[1] == 'phystech.edu')
            {
                $userNew->level_id = 6;
                $userNew->save();
            }

            
            //автовступление в событие после рега - относительно домена
            if (!empty($domainSettings['autojoinEvents'])){
                foreach($domainSettings['autojoinEvents'] as $eventId)
                {
                    $joinEvent = Engine_Api::_()->getItem('event', $eventId);
                    if ($joinEvent){
                        $joinEvent->membership()->addMember($userNew)->setUserApproved($userNew);
                    }
                }
            }
            
            
            $this->db->insert($this->tb_prefix . 'cadastre', array('user_id' => $user_id, 'result' => 0));
            $fieldsTable = Fields_Model_DbTable_Abstract::factory('user', 'values');
            
            if (isset($values['school_class']) && intval($values['school_class']) > 0 && intval($values['school_class']) < 12) {
                if (intval(date('n')) > 7) {
                    $values['school_graduation'] = date('Y') + 12 - intval($values['school_class']);
                } else {
                    $values['school_graduation'] = date('Y') + 11 - intval($values['school_class']);
                }
                $fieldsTable->createRow(array(// 4 — id статуса типа «школьник», 6 — id статуса типа «студент»
                    'item_id'   =>$user_id,
                    'field_id'  => 10,
                    'value'     => 4
                ))->save();
                
            } elseif (isset($values['school_class'])) {
                unset($values['school_class']);
            }

            if (isset($values['university_course']) && intval($values['university_course']) > 0 && intval($values['university_course']) < 7) {
                $fieldsTable->createRow(array(// 4 — id статуса типа «школьник», 6 — id статуса типа «студент»
                    'item_id'   =>$user_id,
                    'field_id'  => 10,
                    'value'     => 6
                ))->save();
            } elseif (isset($values['university_course'])) {
                unset($values['university_course']);
            }
            
            if (isset($_REQUEST['mobilephone_country']) && empty($values['country'])){
                $values['country'] = $_REQUEST['mobilephone_country'];
            }
            
            
            /*GENERAL UPDATE*/
            foreach($values as $key=>$value){
                Engine_Api::_()->fields()->setValByName( $user_id , $key, $value );
            }
            $this->db->update($this->tb_prefix . 'users', $main_values, 'user_id = ' . $user_id);
            /*END: GENERAL UPDATE*/

            $this->db->commit();
            $transactionCommited = true;

            $_SESSION['first_visit'] = true;

            if(!empty($_SESSION['registration_info']) && !empty($_SESSION['registration_info']['user_photo']) ) { /* Загружаем фото из сторонней соц. сети в Abitu */
                try{
                    $file_url = $_SESSION['registration_info']['user_photo'];
                    $file_paths = explode('/', $file_url);
                    $file_name_with_extension = str_replace('?', '', $file_paths[count($file_paths) - 1]);

                    $name_parts = explode('.', $file_name_with_extension);
                    if (count($name_parts) > 1) {
                        unset($name_parts[count($name_parts) - 1]);
                    }


                    $file_binary = @file_get_contents($file_url);
                    $file_info = @getimagesize($file_url);
                    if (!$file_info){
                        sleep(1);
                        $file_info = @getimagesize($file_url);
                    }
                    if (!$file_binary){
                        sleep(1);
                        $file_binary = @file_get_contents($file_url);
                    }
                    if ($file_binary && $file_info && ($file_info['mime'] === 'image/jpeg' || $file_info['mime'] === 'image/png' || $file_info['mime'] === 'image/gif') ) {
                        $file_extension = str_replace('image/', '', $file_info['mime']);
                        $file_name = implode('', $name_parts);

                        $file_name .= '.' . $file_extension;

                        $file_server_path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . $file_name;

                        file_put_contents($file_server_path, $file_binary);

                        $new_user = Engine_Api::_()->getItem('user', intval($user_id));
                        $new_user->setPhoto(array('tmp_name' => $file_server_path, 'name' => $file_name), intval($user_id));
                        unlink($file_server_path);
                    }
                } catch (Exception $e) {  Engine_Api::_()->core()->logException($e, 'registration_photo'); }
            }

            if (Engine_Api::_()->core()->getNowDomainSettings()['key']  == 'kontrolnaya'){
                $this->db->update($this->tb_prefix . 'users', array('verified' => 1, 'approved' => 1, 'enabled' => 1, /*суперчит, костыль*/ 'is_required_fields_filled' => 1), 'user_id = ' . $user_id);
                $this->userLogin($user_id);
                echo json_encode(['status' => 'success', 'redirect' => isset($_COOKIE['registerWalkPath']) ? $_COOKIE['registerWalkPath'] : '/']);
                return;
            }
            
            if ($domainSettings['key']  == 'zftsh'){
            	$updateArr = [
            			'check_zftsh_fields' => Zftsh_Api_Core::ZFTSH_FIELDS__JUSTREGISTERED
            	];
            	$this->db->update($this->tb_prefix . 'users', $updateArr, 'user_id = ' . $user_id);
            }

            if ( $settings->getSetting('user.signup.verifyemail') === '2' && !empty($_SESSION['registration_info']) ) {
                $this->db->update($this->tb_prefix . 'users', array('verified' => 1, 'approved' => 1, 'enabled' => 1), 'user_id = ' . $user_id);
                $this->userLogin($user_id);
                $_SESSION['registration_info'] = null;
                echo json_encode(array('status' => 'success', 'redirect' => true, 'message' => '<span class="success">'.$this->translate->_("Registration completed. Now you will direct to your profile page.").'</span>'));
            } else if ($settings->getSetting('user.signup.verifyemail') === '2') {
                $message = $this->translate->_('Registration is almost complete. You have to click the verification link that we have sent you at your e-mail to finish the registration.');
                $message .= ' '.$this->translate->_("If you did not receive the message with the verification link, ") . '<a href="/signup/resend/' . $user_id . '" target="_blank">'.$this->translate->_("click here") .'</a> '.$this->translate->_("to send it again.");

                if ($settings->getSetting('user.signup.approve') === '0') {
                    $message .= $this->translate->_('After clicking on the link to confirm of email, wait until the account administrator to check and approve it. It may take up to 1 day.');
                }
                echo json_encode(array('status' => 'success', 'redirect' => false, 'message' => $message));
            } else {
                $this->userLogin($user_id);
                $_SESSION['registration_info'] = null;
                echo json_encode(array('status' => 'success', 'redirect' => true, 'message' => '<span class="success">'.$this->translate->_("Registration completed. Now you will direct to your profile page.").'</span>'));
            }
        } catch (Exception $e) {
            Engine_Api::_()->core()->logException($e, 'registration');
            if (!$transactionCommited){
                $this->db->rollBack();
                echo json_encode(array('status' => 'fail'));
            }else{
                echo json_encode(array('status' => 'success', 'redirect' => false, 'message' => 'Регистрация завершена. Требуется подтверждение аккаунта через почту.'));
            }
            throw $e;
        }
        return;
    }


    public function verifyAction()
    {
        $verify = $this->_getParam('code'); //код проверки
        $user_id = $this->_getParam('id'); //id юзера

        // No code or email
        if (!$verify || !$user_id) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Wrong verification code');
            return;
        }

        // Get verify user
        $userTable = Engine_Api::_()->getDbtable('users', 'user');
        $user = $userTable->fetchRow($userTable->select()->where('user_id = ?', $user_id));


        if (!$user || !$user->getIdentity()) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('The email does not match an existing user');
            return;
        }

        // If the user is already verified, just redirect
        if ($user->verified) {
            $this->view->status = true;
            ob_clean();
            header('Location: https://' . $_SERVER['HTTP_HOST'] . '/members/edit/profile/');
            return;
        }

        // Get verify row
        $verifyTable = Engine_Api::_()->getDbtable('verify', 'user');
        $verifyRow = $verifyTable->fetchRow($verifyTable->select()->where('user_id = ?', $user_id));

        if (!$verifyRow || $verifyRow->code != $verify) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('There is no verification info for that user');
            return;
        }

        // Process

        $this->db->beginTransaction();

        try {
            $verifyRow->delete();
            $user->verified = 1;
            $user->save();

            if ($user->enabled) {
                Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserEnable', $user);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $_SESSION['Zend_Auth']['storage'] = $user_id;
        $ipObj = new Engine_IP();
        $ipExpr = new Zend_Db_Expr($this->db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

        $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
        $loginTable->insert(array(
            'user_id' => $user_id,
            'email' => $user->email,
            'ip' => $ipExpr,
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'state' => 'success',
            'active' => true,
        ));
        $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();

        $lifetime = 1209600; /* Человек запоминается системой на две недели */
        Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
        Zend_Session::rememberMe($lifetime);

        Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.logins'); // Increment sign-in count

        $_SESSION['first_visit'] = true;

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        ob_clean();
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/members/edit/profile/');
        return;
    }

    /* Повторная отправка письма для подтверждения аккаунта */
    public function resendAction()
    {
        $user_id = $this->_getParam('user_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() || !$user_id) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        $userTable = Engine_Api::_()->getDbtable('users', 'user');
        $user = $userTable->fetchRow($userTable->select()->where('user_id = ?', $user_id));

        if (!$user) {
            $this->view->error = 'That email was not found in our records.';
            return;
        }
        if ($user->verified) {
            $this->view->error = 'That email has already been verified. You may now login.';
            return;
        }

        // resend verify email
        $verifyTable = Engine_Api::_()->getDbtable('verify', 'user');
        $verifyRow = $verifyTable->fetchRow($verifyTable->select()->where('user_id = ?', $user->user_id)->limit(1));

        if (!$verifyRow) {
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $verifyRow = $verifyTable->createRow();
            $verifyRow->user_id = $user->getIdentity();
            $verifyRow->code = md5($user->email . $user->creation_date . $settings->getSetting('core.secret') . (string)rand(1000000, 9999999));
            $verifyRow->date = $user->creation_date;
            $verifyRow->save();
        }

        $mailParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => time(),
            'recipient_title' => $user->getTitle(),
            'recipient_link' => $user->getHref(),
            'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
            'queue' => false,
        );

        $mailParams['object_link'] = '/signup/verify/' . $user->user_id . '/' . $verifyRow->code . '/';

        Engine_Api::_()->getApi('mail', 'core')->sendSystemRaw($user, 'core_verification', $mailParams); /* Отсылка письма с кодом подтверждения */

        $this->view->resend_email = $user->email;
        $this->view->current_user_id = $user_id;
        return;
    }



    /* ajax проверка emailа регулярными выражениями, проверка на наличие в чёрном списке
     и на наличие в списке зарегистрированных emailов */
    public function mailcheckAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if (empty($_GET['email'])) {
            echo json_encode(array('status' => 'fail'));
            return;
        }

        $validator = new Zend_Validate_EmailAddress();
        $validator->setMessage($this->translate->_('Incorrect email'), Zend_Validate_EmailAddress::INVALID_FORMAT)
            ->setMessage($this->translate->_('Incorrect hostname'), Zend_Validate_EmailAddress::INVALID_HOSTNAME)
            ->setMessage($this->translate->_('Email too long'), Zend_Validate_EmailAddress::LENGTH_EXCEEDED)
            ->setMessage($this->translate->_('Incorrect email'), Zend_Validate_EmailAddress::INVALID_MX_RECORD);

        $validator_exist = new Zend_Validate_Db_NoRecordExists($this->tb_prefix . 'users', 'email');
        $validator_exist->setMessage($this->translate->_('Someone already has that email'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        $validator_banned = new Zend_Validate_Db_NoRecordExists($this->tb_prefix . 'core_bannedemails', 'email');
        $validator_banned->setMessage($this->translate->_('This email is in the black list'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        if (!$validator->isValid($_GET['email'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator->getMessages()));
            return;
        }

        if (!$validator_exist->isValid($_GET['email'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator_exist->getMessages()));
            return;
        }

        if (!$validator_banned->isValid($_GET['email'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator_banned->getMessages()));
            return;
        }

        echo json_encode(array('status' => 'success'));
    }

    /* Проверка телефона на наличие в списке зарегистрированных */
    public function phonecheckAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $checkedPhoneValue = trim($_REQUEST['phone']);

        if ($checkedPhoneValue === '') {
            echo json_encode(array('status' => 'fail'));
            return;
        }

        $validatorPhoneExist = new Zend_Validate_Db_NoRecordExists($this->tb_prefix . 'users', 'email');
        $validatorPhoneExist->setMessage($this->translate->_('Someone already has that email'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        if (!$validatorPhoneExist->isValid($checkedPhoneValue)) {
            echo (json_encode(array_merge(array('status' => false), $validatorPhoneExist->getMessages())));
            return;
        }

        echo json_encode(array('status' => true));
    }

    /* ajax проверка адреса профиля регулярными выражениями, проверка на наличие в чёрном списке и на наличие в списке занятых адресов */
    public function usercheckAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($_GET['username'] === '') {
            echo json_encode(array('status' => 'fail'));
            return;
        }

        $validator = new Zend_Validate_Regex('/^[A-z]{1}[A-z0-9]*$/');
        $validator->setMessage($this->translate->_('Profile address is empty'), Zend_Validate_Regex::INVALID)
                  ->setMessage($this->translate->_('Incorrect profile address'), Zend_Validate_Regex::NOT_MATCH);

        $validator_length = new Zend_Validate_StringLength(4, 64);
        $validator_length->setMessage($this->translate->_('Profile address must have at least %min% characters'), Zend_Validate_StringLength::TOO_SHORT);
        $validator_length->setMessage($this->translate->_('Profile address must have at most %max% characters'), Zend_Validate_StringLength::TOO_LONG);

        $validator_exist = new Zend_Validate_Db_NoRecordExists($this->tb_prefix . 'users', 'username');
        $validator_exist->setMessage($this->translate->_('Someone already has that profile address'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        $validator_banned = new Zend_Validate_Db_NoRecordExists($this->tb_prefix . 'core_bannedusernames', 'username');
        $validator_banned->setMessage($this->translate->_('Profile address not available'), Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        if (!$validator->isValid($_GET['username'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator->getMessages()));
            return;
        }

        if (!$validator_length->isValid($_GET['username'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator_length->getMessages()));
            return;
        }

        if (!$validator_exist->isValid($_GET['username'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator_exist->getMessages()));
            return;
        }

        if (!$validator_banned->isValid($_GET['username'])) {
            echo json_encode(array_merge(array('status' => 'incorrect'), $validator_banned->getMessages()));
            return;
        }

        echo json_encode(array('status' => 'success'));
    }

    public function takenAction()
    {
        $username = $this->_getParam('username');
        $email = $this->_getParam('email');

        // Sent both or neither username/email
        if ((bool)$username == (bool)$email) {
            $this->view->status = false;
            $this->view->error = $this->translate->_('Invalid param count');
            return;
        }

        // Username must be alnum
        if ($username) {
            $validator = new Zend_Validate_Alnum();
            if (!$validator->isValid($username)) {
                $this->view->status = false;
                $this->view->error = $this->translate->_('Invalid param value');
                //$this->view->errors = $validator->getErrors();
                return;
            }

            $table = Engine_Api::_()->getItemTable('user');
            $row = $table->fetchRow($table->select()->where('username = ?', $username)->limit(1));

            $this->view->status = true;
            $this->view->taken = ($row !== null);
            return;
        }

        if ($email) {
            $validator = new Zend_Validate_EmailAddress();
            if (!$validator->isValid($email)) {
                $this->view->status = false;
                $this->view->error = $this->translate->_('Invalid param value');
                //$this->view->errors = $validator->getErrors();
                return;
            }

            $table = Engine_Api::_()->getItemTable('user');
            $row = $table->fetchRow($table->select()->where('email = ?', $email)->limit(1));

            $this->view->status = true;
            $this->view->taken = ($row !== null);
            return;
        }
    }

    public function confirmAction()
    {
        $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
        $this->view->approved = $this->_getParam('approved', $confirmSession->approved);
        $this->view->verified = $this->_getParam('verified', $confirmSession->verified);
        $this->view->enabled = $this->_getParam('verified', $confirmSession->enabled);
    }

    /* Связывание аккаунта в соц. сети при регистрации */
    private function linkSocialNetwork($user_id = null, $social_network_user_id = null, $social_network_name = null, $social_network_email = null)
    {
        if ($user_id == null || $social_network_user_id == null || $social_network_name == null) {
            return false;
        }

        switch ($social_network_name) {
            case 'yandex':
            case 'google': {
                if ($social_network_email != null) {
                    $linking_query = "INSERT INTO engine4_users_social(user_id, " . $social_network_name . "_id, " . $social_network_name . "_email) VALUES (" . $user_id . ", " . $social_network_user_id . ", '" . $social_network_email . "') ON DUPLICATE KEY UPDATE " . $social_network_name . "_id = " . $social_network_user_id . ", " . $social_network_name . "_email = '" . $social_network_email . "';";
                } else {
                    $linking_query = 'INSERT INTO engine4_users_social(user_id, ' . $social_network_name . '_id) VALUES (' . $user_id . ', ' . $social_network_user_id . ') ON DUPLICATE KEY UPDATE ' . $social_network_name . '_id = ' . $social_network_user_id . ';';
                }
            }
                break;

            case 'mailru': {
                if ($social_network_email != null) {
                    $linking_query = "INSERT INTO engine4_users_social(user_id, " . $social_network_name . "_id, " . $social_network_name . "_email) VALUES (" . $user_id . ", '" . $social_network_user_id . "', '" . $social_network_email . "') ON DUPLICATE KEY UPDATE " . $social_network_name . "_id = '" . $social_network_user_id . "', " . $social_network_name . "_email = '" . $social_network_email . "';";
                } else {
                    $linking_query = 'INSERT INTO engine4_users_social(user_id, ' . $social_network_name . '_id) VALUES (' . $user_id . ', ' . $social_network_user_id . ') ON DUPLICATE KEY UPDATE ' . $social_network_name . '_id = ' . $social_network_user_id . ';';
                }
            }
                break;

            default: $linking_query = 'INSERT INTO engine4_users_social(user_id, ' . $social_network_name . '_id) VALUES (' . $user_id . ', ' . $social_network_user_id . ') ON DUPLICATE KEY UPDATE ' . $social_network_name . '_id = ' . $social_network_user_id . ';';
        }

        $this->db->query($linking_query);
    }

    /* Автоматическая авторизация пользователя */
    private function userLogin($user_id)
    {
        $_SESSION['Zend_Auth']['storage'] = $user_id;

        $ipObj = new Engine_IP();
        $ipExpr = new Zend_Db_Expr($this->db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
        $formSequence = new User_Plugin_Signup_Account;
        
        $loginTable = Engine_Api::_()->getDbtable('logins', 'user');
        $loginTable->insert(array(
            'active' => true,
            'email' => $formSequence->getSession()->data['email'],
            'ip' => $ipExpr,
            'state' => 'success',
            'timestamp' => new Zend_Db_Expr('NOW()'),
            'user_id' => $user_id,
            'useragent' => $_SERVER['HTTP_USER_AGENT']
        ));
        $_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();

        $lifetime = 1209600; /* Человек запоминается системой на две недели */
        Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
        Zend_Session::rememberMe($lifetime);
        Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.logins'); // Increment sign-in count
        setcookie('lastLoginTime', time(), 0, '/');/* for crossdomain usage */
    }
}