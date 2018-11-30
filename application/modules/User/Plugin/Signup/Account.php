<?

class User_Plugin_Signup_Account extends Core_Plugin_FormSequence_Abstract
{
    protected $_name = 'account';
    protected $_formClass = 'User_Form_Signup_Account';
    protected $_script = array('signup/form/account.tpl', 'user');
    protected $_adminFormClass = 'User_Form_Admin_Signup_Account';
    protected $_adminScript = array('admin-signup/account.tpl', 'user');
    public $email = null;


    public function getForm()
    {
        if (is_null($this->_form)) {
            Engine_Loader::loadClass($this->_formClass);
            $class = $this->_formClass;
            $this->_form = new $class();

            $data = $this->getSession()->data;
            if (is_array($data)) {
                $this->_form->populate($data);
            }
        }
        return $this->_form;
    }

    public function onView()
    {

    }

    public function onProcess()
    {
        $this->_registry = new stdClass();
        
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $random = ($settings->getSetting('user.signup.random', 0) == 1);
        $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);

        if ($emailadmin) {
            // the signup notification is emailed to the first SuperAdmin by default
            $users_table = Engine_Api::_()->getDbtable('users', 'user');
            $users_select = $users_table->select()
                ->where('level_id = ?', 1)
                ->where('enabled >= ?', 1);
            $super_admin = $users_table->fetchRow($users_select);
        }
        $data = $this->getSession()->data;
        if (empty($data['email'])){
            $data['email'] = '';
        }
        if (empty($data['password'])){
            $data['password'] = '';
        }
        
        // Add email and code to invite session if available
        $inviteSession = new Zend_Session_Namespace('invite');
        if (isset($data['email'])) {
            $inviteSession->signup_email = $data['email'];
        }
        if (isset($data['code'])) {
            $inviteSession->signup_code = $data['code'];
        }

        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($DS['default_locale'])){
            $data['language'] = $data['locale'] = $DS['default_locale'];
            Zend_Registry::get('Zend_Translate')->setLocale($DS['default_locale']);
            Zend_Registry::set('Locale', new Zend_Locale($DS['default_locale']) );
        }

        if (!isset($data['language'])) {
            $data['language'] = 'ru_RU';
        }

        if (empty($data['first_name'])){
            $data['first_name'] = '';
        }
        if (empty($data['last_name'])){
            $data['last_name'] = '';
        }

        if (empty($data['middle_name'])){
            $data['middle_name'] = '';
        }


        if ($random) {
            $data['password'] = Engine_Api::_()->user()->randomPass(10);
        }

        if (isset($data['locale'])) {
            $data['locale'] = $data['language'];
        }
        $mailAdminType = null;
        // Create user
        // Note: you must assign this to the registry before calling save or it
        // will not be available to the plugin in the hook
        $this->_registry->user = $user = Engine_Api::_()->getDbtable('users', 'user')->createRow($data);
        $user->save();

        Engine_Api::_()->user()->setViewer($user);

        // Increment signup counter
        Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.creations');

        if ($user->verified && $user->enabled) {
            Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity()); // Set user as logged in if not have to verify email
        }

        $mailType = null;
        $mailParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $data['email'],
            'date' => time(),
            'recipient_title' => $user->first_name,
            'recipient_link' => $user->getHref(),
            'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
            'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            'queue' => false
        );

        // Add password to email if necessary
        if ($random) {
            $mailParams['password'] = $data['password'];
        }

        $domSet = Engine_Api::_()->core()->getNowDomainSettings();
        // Mail stuff
        switch ($settings->getSetting('user.signup.verifyemail')) {
            case 0: /* Только приветственное сообщение */ {
                // only override admin setting if random passwords are being created
                if ($random) {
                    $mailType = 'core_welcome_password';
                }
                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';

                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,
                        'date' => date("F j, Y, g:i a"),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->displayname,
                        'object_link' => $user->getHref(),
                    );
                }
            }
                break;

            case 1: {
                // send welcome email
                if (!empty($domSet['welcome_email_templates']['welcome'])) {
                    $mailType = $domSet['welcome_email_templates']['welcome'];
                } else {
                    $mailType = ($random ? 'core_welcome_password' : 'core_welcome');
                }

                $mailParams['password'] = $data['password'];
                //Engine_Api::_()->getApi('mail', 'core')->sendSystem($user,  $mailType, $mailParams);
                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';

                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,

                        'date' => date("F j, Y, g:i a"),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->getTitle(),
                        'object_link' => $user->getHref(),
                    );
                }
            }
                break;

            case 2: {
                // verify email before enabling account
                $verify_table = Engine_Api::_()->getDbtable('verify', 'user');
                $verify_table->delete('user_id = '. $user->getIdentity());
                $verify_row = $verify_table->createRow();
                $verify_row->user_id = $user->getIdentity();
                $verify_row->code = md5($user->email . $user->creation_date . $settings->getSetting('core.secret', 'staticSalt') . (string)rand(1000000, 9999999));
                $verify_row->date = $user->creation_date;
                $verify_row->save();

                if (!empty($domSet['welcome_email_templates']['verification'])) {
                    $mailType = $domSet['welcome_email_templates']['verification'];
                } else {
                    $mailType = ($random ? 'core_verification_password' : 'core_verification');
                }

                $mailParams['object_link'] = '/signup/verify/' . $user->user_id . '/' . $verify_row->code . '/';
                $mailParams['password'] = $data['password'];

                //Engine_Api::_()->getApi('mail', 'core')->sendSystem($user,  $mailType, $mailParams);
                if ($emailadmin) {
                    $mailAdminType = 'notify_admin_user_signup';

                    $mailAdminParams = array(
                        'host' => $_SERVER['HTTP_HOST'],
                        'email' => $user->email,
                        'date' => date("F j, Y, g:i a"),
                        'recipient_title' => $super_admin->displayname,
                        'object_title' => $user->getTitle(),
                        'object_link' => $user->getHref(),
                    );

                }
            }
                break;

            default: // do nothing
                break;
        }

        if ($mailType && empty($_SESSION['registration_info']) ) {
            $this->_registry->mailParams = $mailParams;
            $this->_registry->mailType = $mailType;
            // Moved to User_Plugin_Signup_Fields
            if ($mailParams['email'])
            Engine_Api::_()->getApi('mail', 'core')->sendSystemRaw(
                $user,
                $mailType,
                $mailParams
            );
        }

        if ($mailAdminType) {
            $this->_registry->mailAdminParams = $mailAdminParams;
            $this->_registry->mailAdminType = $mailAdminType;
            // Moved to User_Plugin_Signup_Fields
            // Engine_Api::_()->getApi('mail', 'core')->sendSystem(
            //   $user,
            //   $mailType,
            //   $mailParams
            // );
        }
        
    }

    public function onAdminProcess($form)
    {
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $values = $form->getValues();
        $settings->user_signup = $values;
        if ($values['inviteonly'] == 1) {
            $step_table = Engine_Api::_()->getDbtable('signup', 'user');
            $step_row = $step_table->fetchRow($step_table->select()->where('class = ?', 'User_Plugin_Signup_Invite'));
            $step_row->enable = 0;
        }

        $form->addNotice('Your changes have been saved.');
    }

}