<?php
class User_Form_Settings_General extends Engine_Form
{

    protected $_item;

    public function setItem(User_Model_User $item)
    {
        $this->_item = $item;
    }

    public function getItem()
    {
        if (null === $this->_item)
        {
            throw new User_Model_Exception('No item set in ' . get_class($this));
        }

        return $this->_item;
    }

    public function init()
    {
        // @todo fix form CSS/decorators
        // @todo replace fake values with real values
        $this
                ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
        
        $translate = Zend_Registry::get('Zend_Translate');

        // Init email
        $this->addElement('Text', 'email', array(
            'filters' => array('StringTrim', 'StripTags'),
            'label' => 'Email Address',
            'placeholder' => $translate->_('Email Address'),
            'required' => true,
            'allowEmpty' => false,
            'validators' => array(
                array('NotEmpty', true),
                array('EmailAddress', true),
                array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users',
                    'email', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
            ),            
        ));
        $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
        $this->email->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');

        // Init username
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) > 0)
        {
            $this->addElement('Text', 'username', array(
                'filters' => array('StringTrim', 'StripTags'),
                'label' => 'Profile Address',
                'placeholder' => $translate->_('Profile Address'),
                'required' => true,
                'allowEmpty' => false,
                'validators' => array(
                    array('Alnum', true),
                    array('NotEmpty', true),                
                    array('StringLength', true, array(4, 64)),
                    array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
                    array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users',
                    'username', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
                ),
            ));
            $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid profile address.', 'isEmpty');
            $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this profile address, please use another one.', 'recordFound');
            $this->username->getValidator('Regex')->setMessage('Profile addresses must start with a letter.', 'regexNotMatch');
            $this->username->getValidator('Alnum')->setMessage('Profile addresses must be alphanumeric.', 'notAlnum');
        }

        // Init timezone
        $this->addElement('Select', 'timezone', array(
            'label' => 'Timezone',
            'description' => 'Select the city closest to you that shares your same timezone.',
            'multiOptions' => array(
                'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
                'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
                'US/Central' => '(UTC-6) Central Time (US & Canada)',
                'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
                'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
                'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
                'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
                'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
                'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
                'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
                'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
                'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
                'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
                'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
                'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
                'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
                'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
                'Iran' => '(UTC+3:30) Tehran',
                'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
                'Asia/Kabul' => '(UTC+4:30) Kabul',
                'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
                'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
                'Asia/Katmandu' => '(UTC+5:45) Nepal',
                'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
                'India/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
                'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
                'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
                'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
                'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
                'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
                'Asia/Magadan' => '(UTC+11) Magadan, Soloman Is., New Caledonia',
                'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
            ),
        ));


        $this->addElement('Select', 'language', array(
            'label' => 'Locale',
            'description' => 'Language of user interface',
            'multiOptions' => array('en_EN' => ' - English - ', 'ru_RU' => ' - Русский - ')
        ));

        
        // Фото баннера - в шапке ЗФТШ
        $domainSets = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($domainSets['zftshDefaults'])){
            $this->addElement('ImageFile', 'banner_id', array(
                'description' => 'Фото, размещаемое над профилем пользователя. Рекомендуемый размер от 760 × 300px',
                'label' => 'Banner Photo',
                'validators' => array(
                    array('Extension', false, 'jpg,jpeg,png,gif'),
                ),
            ));
            $this->banner_id->setImageConfig(array(
                'width'=>482,
                'height'=>124
            ));
        }

        // Init submit
        $this->addElement('Button', 'submit_general', array(
            'class' => 'save',
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
        ));

        // Create display group for buttons
        #$this->addDisplayGroup($emailAlerts, 'checkboxes');
        // Set default action
        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
                'module' => 'user',
                'controller' => 'settings',
                'action' => 'general',
                        ), 'default'));
    }

}