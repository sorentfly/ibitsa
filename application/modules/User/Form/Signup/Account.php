<?

class User_Form_Signup_Account extends User_Form_Fields
{
    public function init()
    {
        $this->getView()->headScript()->appendFile('/externals/jquery-hideShowPassword/hideShowPassword.min.js');
        $this->getView()->headScript()->appendFile('/application/modules/Core/externals/scripts/captcha.js');

        $settings = Engine_Api::_()->getApi('settings', 'core');

        $translate = Zend_Registry::get('Zend_Translate');
        $this->setAttrib('class', 'global_form')
            ->setAttrib('id', 'registration_form')
            ->setAction('/signup/check/');
        

        
        $first_name_value = '';
        $middle_name_value = '';
        $last_name_value = '';
        $birthdate_value = '';
        $email_value = '';

        if (isset($_SESSION['registration_info'])) {
            if (isset($_SESSION['registration_info']['first_name'])) {
                $first_name_value = $_SESSION['registration_info']['first_name'];
            }

            if (isset($_SESSION['registration_info']['middle_name'])) {
                $middle_name_value = $_SESSION['registration_info']['middle_name'];
            }

            if (isset($_SESSION['registration_info']['last_name'])) {
                $last_name_value = $_SESSION['registration_info']['last_name'];
            }

            if (isset($_SESSION['registration_info']['birthdate'])) {
                $birthdate_value = $_SESSION['registration_info']['birthdate'];
            }

            if (isset($_SESSION['registration_info']['email'])) {
                $email_value = $_SESSION['registration_info']['email'];
            }
            if (!$middle_name_value){
                $splitted = explode(' ', trim($first_name_value));
                if (count($splitted) > 1){
                    $first_name_value = $splitted[0];
                    $middle_name_value = $splitted[1];
                }
            }
        }

        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        $first_name_options = array(
            'allowEmpty' => false,
            'required' => true,
            'value' => $first_name_value,
            'cyrillic_only' => !empty($domainSettings['zftshDefaults'])
        );

        $middle_name_options = array(
            'value' => $middle_name_value,
            'cyrillic_only' => !empty($domainSettings['zftshDefaults'])
        );

        $last_name_options = array(
            'allowEmpty' => false,
            'required' => true,
            'value' => $last_name_value,
            'cyrillic_only' => !empty($domainSettings['zftshDefaults'])
        );

        //generic_layout_container

        if ( !empty($domainSettings['referralsEnabled']) && !empty($_COOKIE['referral']) && ( $referral = (int)$_COOKIE['referral'] ) > 0 ) {
            $user = Engine_Api::_()->getItem('user', $referral);

            if (!$user) {
                setcookie('referral', $referral, time() - 86400, '/');
            } else {
                $this->addElement('Dummy', 'referral', array(
                    'label' => 'Пригласитель: ',
                    'content' => $user->toString()
                ));
            }
        }


        if (Zend_Registry::get('Locale')->__toString() === 'en') {
            $first_name_options['autofocus'] = 'autofocus';

            $this->addElement('PersonName', 'first_name', $first_name_options);
            $this->addElement('PersonName', 'middle_name', $middle_name_options);
            $this->addElement('PersonName', 'last_name', $last_name_options);
        } else {
            $last_name_options['autofocus'] = 'autofocus';

            $this->addElement('PersonName', 'last_name', $last_name_options);
            $this->addElement('PersonName', 'first_name', $first_name_options);
            $this->addElement('PersonName', 'middle_name', $middle_name_options);
        }

        $this->addElement('telephone', 'mobilephone', array(
            'allowEmpty' => true,
            'label' => $translate->_('Mobilephone'),
            'maxlength' => '127',
            'placeholder' => $translate->_('Mobilephone'),
            'required' => true
        ));
        
        $this->addElement('Birthdate', 'birthdate', array (
                        'label' => 'Birthdate',
                        'required' => true
        ));
        if ($birthdate_value){
            $this->birthdate->setValue($birthdate_value);
        }
        $this->birthdate->setAttrib('placeholder', Zend_Registry::get('Zend_Translate')->translate("Birthdate DD.MM.YYYY"));

        $this->addElement('select', 'profile_status', array (
                        'allowEmpty' => false,
                        'label' => 'Profile type',
                        'multiOptions' => Engine_Api::_()->getItemTable("user", "user")->getProfileTypes(),
                        'required' => true,
                        'value' => ''
        ));

        $this->profile_status->setAttrib('disable', ['']);

        $this->addElement('email', 'email', array(
            'allowEmpty' => false,
            'autocomplete' => 'off',
            'description' => $translate->_('You will use your email address to login. Emails are not published on the site and do not sell to spammers.'),
            'label' => $translate->_('E-mail'),
            'placeholder' => $translate->_('Enter your email'),
            'required' => true,
            'title' => $translate->_('Enter your current email address'),
            'validators' => array(
                array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'email')),
                //array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'core_bannedemails', 'email'))
            ),
            'value' => $email_value
        ));


        $this->getElement('email')->getValidator('Db_NoRecordExists')->setMessage($translate->_('Someone already has that email'), 'recordFound');

        if (!$settings->getSetting('user.signup.random')) {
            $this->addElement('password', 'password', array(
                    'allowEmpty' => false,
                    'label' => $translate->_('Password'),
                    'onkeyup' => 'passwordTextchange(this);',
                    'placeholder' => $translate->_('Enter a password'),
                    'required' => true,
                    )
            );

            $this->getElement('password')->getValidator('NotEmpty')->setMessage($translate->_('Your password is empty'), 'notEmptyInvalid');

            $this->addElement('password', 'password_confirm', array(
                    'allowEmpty' => false,
                    'label' => $translate->_('Сonfirm your password'),
                    'onkeyup' => 'passwordConfirmTextchange(this);',
                    'placeholder' => $translate->_('Reenter a password to confirm'),
                    'oncontextmenu' => 'return false;',
                    'oncut' => 'return false;',
                    'required' => true,
                )
            );

            $this->getElement('password_confirm')->getValidator('NotEmpty')->setMessage($translate->_('Re-enter password to confirm'), 'notEmptyInvalid');
            $this->getElement('password_confirm')->getValidator('Regex')->setMessage($translate->_('Incorrect password confirmation. Password can contain both letters and numbers plus any of the following symbols: ! # $ % ^ & * ( ) _ - + : ; . , @'), 'regexNotMatch');


            $specialValidator = new Engine_Validate_Callback(array($this, 'checkPasswordConfirm'), $this->getElement('password_confirm'));
            $specialValidator->setMessage($translate->_('Passwords do not match'), 'invalid');

            $this->getElement('password_confirm')->addValidator($specialValidator);
        }

        //to do: add user.signup.timezone to database & control panel
        // Element: recaptcha
        if ($settings->getSetting('core.spam.signup') && !isset($_SESSION['vk_registration_info']) && !isset($_SESSION['ya_registration_info']) && !isset($_SESSION['google_registration_info'])) {
            $this->addElement('recaptcha', 'recaptcha_response_field', array(
                'allowEmpty' => false,
                'onkeydown' => 'recaptchaTextchange(this);',
                'onpaste' => 'return false;',
                'required' => true
            ));
        }

        if (!empty($domainSettings['zftshDefaults'])){
            $this->addElement('Dummy', 'please_be_real', [
                'content' => 'Необходимо вводить подлинные данные',
            ]);
            $this->please_be_real->getDecorator('HtmlTag')->setOption('style', 'font-weight: bold;');
        }

        if ($settings->getSetting('user.signup.terms')) {
            $this->addElement('checkbox', 'terms', array(
                    'allowEmpty' => false,
                    'label' => $translate->_("I agree to the terms of the <a href='/help/terms/' id='user_agreement' onclick='toggleLicenseAgreement();return false;' target='_blank' title='The terms of the User Agreement'>User Agreement</a>"),
                    'onchange' => 'termsChange(this);',
                    'required' => true,
                    'title' => $translate->_('The terms of the User Agreement'),
                    'validators' => array(array('NotEmpty', true))
                )
            );

            $this->getElement('terms')->getValidator('NotEmpty')->setMessage($translate->_('You must accept the User Agreement'), 'isEmpty');
            $this->getElement('terms')->getValidator('NotEmpty')->setMessage($translate->_('You must accept the User Agreement'), 'notEmptyInvalid');
        }


        $this->addElement('button', 'submit', array(
                'ignore' => true,
                'label' => $translate->_('Next'),
                'title' => $translate->_('To the next step'),
                'type' => 'submit')
        );


        if ($domainSettings['key'] == 'kontrolnaya'){
            $this->shortSignupModification();
        }
        if ($domainSettings['key'] == 'mipt_conference'){
            $this->removeElement('profile_status');
            $this->addElement('Hidden', 'profile_status',[
                'value' => 'other',
                'order' => 100
            ]);
        }

        $this->getView()->headScript()->captureStart();
        echo 'jQuery(function(){window.abituCaptcha.insert.before("terms");});';
        $this->getView()->headScript()->captureEnd();
    }

    public function shortSignupModification()
    {
        $this->getView()->headScript()
            ->appendFile(JQUERY_UI_LIB)
            ->appendFile('/application/modules/Core/externals/scripts/notification.js')
            ->appendFile('/application/modules/User/externals/scripts/profile_edit.js')
        ;

        $this->removeElement('password');
        $this->addElement('Password', 'password', [
            'allowEmpty' => false,
            'label' => 'Password',
            'placeholder' => Zend_Registry::get('Zend_Translate')->_('Enter a password'),
            'required' => true,
        ]);

        $this->mobilephone->setRequired(false)->setAttrib('required', null);

        $this->profile_status->setMultiOptions([
            '' => 'Уровень образования?',
            'schoolboy' => 'Среднее',
            'student' => 'Незаконченное высшее',
            'postgraduate' => 'Высшее',
        ]);

        $this->addPrefixPath('Abitu_Form_Decorator', 'Engine/Abitu_Form/Decorator', 'decorator')
            ->addPrefixPath('Abitu_Form_Element', 'Engine/Abitu_Form/Element', 'element');

        $this->addElement('Country', 'country', ['label' => 'Страна']);
        $this->country->setAttrib('placeholder', 'Выберите страну')->setAttrib('required', 'required');
        $this->addElement('Region', 'region', ['label' => 'Регион']);
        $this->region->setAttrib('class', 'hidden');
        $this->addElement('City', 'city', ['label' => 'Город']);
        $this->city->setAttrib('placeholder', 'Начните вводить город')->setAttrib('required', 'required');


        $elementOrder = [
            'first_name' => 1,
            'last_name' => 2,
            'middle_name' => 3,
            'member_code' => 4,
            'profile_status' => 5,
            'country' => 6,
            'region' => 7,
            'city' => 8,
            'email' => 9,
            'mobilephone' => 10,
            'password' => 11,
            'terms' => 12,
            'submit' => 13
        ];
        foreach($this->getElements() as $element){
            /* @var Zend_Form_Element $element */
            $name = $element->getName();
            if (!isset($elementOrder[$name])){
                $this->removeElement($name);
            }else{
                $element->setOrder( $elementOrder[$name] );
            }
        }
    }

    public function checkPasswordConfirm($value, $passwordElement)
    {
        return ($value == $passwordElement->getValue());
    }

    public function checkInviteCode($value, $emailElement)
    {
        $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
        $select = $inviteTable->select()
            ->from($inviteTable->info('name'), 'COUNT(*)')
            ->where('code = ?', $value);

        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.checkemail')) {
            $select->where('recipient LIKE ?', $emailElement->getValue());
        }

        return (bool)$select->query()->fetchColumn(0);
    }

    public function isValid($data)
    {
        if (!empty($data['mobilephone']) && substr_count($data['mobilephone'], '_') > 3){
            $data['mobilephone'] = '';
            $this->mobilephone->setValue('');
        }
        $commonValid = parent::isValid($data);
        if (  !isset($_SESSION['abitu_captcha'])
            || empty($data['abitu_captcha'])
            || empty($data['abitu_captcha'])
            || $data['abitu_captcha'] != $_SESSION['abitu_captcha'] ) {
            return false;
        }
        return $commonValid;
    }
}
