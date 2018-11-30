<?

class User_Form_Fields extends Zend_Form
{
    protected $_user_id;
    protected $_data_type;
    protected $_title;
    protected $_notices;
    /* @var User_Model_User $_item */
    protected $_item;
    protected $_processedValues = array();
    protected $_topLevelId;
    protected $_topLevelValue;
    protected $_isCreation = false;
    public $db;
    public $tb_prefix;
    private $_translate;
    private $orderIndex = 1;

    public $hasFields = false;

    public static $_finishAction = null;

    // Add custom element paths?
    public function __construct($options = null)
    {
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
        $this->_translate = Zend_Registry::get('Zend_Translate');
        if (isset($options ['data_type'])) {
            $this->_data_type = $options ['data_type'];
            unset($options ['data_type']);
        }

        if (isset($options ['user_id'])) {
            $this->_user_id = $options ['user_id'];
            $this->_item = Engine_Api::_()->getItem('user', $this->_user_id);
        }

        Engine_Form::enableForm($this);
        self::enableForm($this);

        if (Engine_Api::_()->core()->hasSubject()) {
            self::$_finishAction = 'window.onFieldsSendedHook = function(){document.location.href = ' . Zend_Json::encode((empty($_COOKIE['registerWalkPath']) ? Engine_Api::_()->core()->getSubject()->getHref() : $_COOKIE['registerWalkPath']))
                . '; document.cookie = "registerWalkPath=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;";}'
                . '; jQuery(this).parents(\'form:first\').find("[type=\'submit\']:first").click()';
        }
        parent::__construct($options);
    }

    public static function enableForm(Zend_Form $form)
    {
        $form->addPrefixPath('Bitsa_Form_Decorator', 'Engine/Bitsa_Form/Decorator', 'decorator')
            ->addPrefixPath('Bitsa_Form_Element', 'Engine/Bitsa_Form/Element', 'element');
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        } else {
            $decorators = $this->getDecorators();
            if (empty($decorators)) {
                $this->addDecorator('FormElements')
                    ->addDecorator('HtmlTag', array(
                        'tag' => 'div',
                        'class' => 'form-elements'
                    ))
                    ->addDecorator('FormMessages', array(
                        'placement' => 'PREPEND'
                    ))
                    ->addDecorator('FormErrors', array(
                        'placement' => 'PREPEND'
                    ))
                    ->addDecorator('Description', array(
                        'placement' => 'PREPEND'
                    ))
                    ->addDecorator('FormTitle', array(
                        'tag' => 'h3'
                    ))
                    ->addDecorator('Form');
            }
        }
    }

    public function init()
    {
        if (!Engine_Api::_()->user()
            ->getViewer()
            ->getIdentity()
        ) {
            $this->hasFields = true;
            $this->registration();
        } else {
            $this->profileEdit();
        }
    }

    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setItem(Core_Model_Item_Abstract $item)
    {
        $this->_item = $item;
        return $this;
    }

    public function getItem()
    {
        return $this->_item;
    }

    public function setTopLevelId($id)
    {
        $this->_topLevelId = $id;
        return $this;
    }

    public function getTopLevelId()
    {
        return $this->_topLevelId;
    }

    public function setTopLevelValue($val)
    {
        $this->_topLevelValue = $val;
        return $this;
    }

    public function getTopLevelValue()
    {
        return $this->_topLevelValue;
    }

    public function setIsCreation($flag = true)
    {
        $this->_isCreation = ( bool )$flag;
        return $this;
    }

    public function setProcessedValues($values)
    {
        $this->_processedValues = $values;
        $this->_setFieldValues($values);
        return $this;
    }

    public function getProcessedValues()
    {
        return $this->_processedValues;
    }

    public function getFieldMeta()
    {
        return Engine_Api::_()->fields()
            ->getFieldsMeta($this->getItem());
    }

    public function addElement($element, $name = null, $options = null)
    {
        $ret = parent::addElement($element, $name, $options);

        if ($element instanceof Engine_Form_Element_Composite) {
            $element->setPluginLoader($this->getPluginLoader(self::DECORATOR));
        }

        return $ret;
    }

    public function createElement($type, $name, $options = null)
    {
        $element = parent::createElement($type, $name, $options);

        if ($element instanceof Engine_Form_Element_Composite) {
            $element->setPluginLoader($this->getPluginLoader(self::DECORATOR));
        }

        return $element;
    }

    public function addNotice($message)
    {
        $this->_notices [] = $message;
        return $this;
    }

    public function clearNotices()
    {
        $this->_notices = array();
        return $this;
    }

    public function getNotices()
    {
        return ( array )$this->_notices;
    }

    public static $valuesSelected = null;

    public function profileEdit()
    {
        if ($this->_data_type == null) {
            $this->_data_type = '0';
        }

        $user = $this->getUserInfo($this->_user_id);

        $userFiledSettings = $this->getUserFieldsSettings($user);

        if ($this->_data_type === '0') {
            $this->createCommonUserFields($user);
            $this->hasFields = true;
        } else {

            $fieldMap = $this->db->select()
                ->from($this->tb_prefix . 'user_fields_meta')
                ->where($this->tb_prefix . 'user_fields_meta.data_type = ' . $this->_data_type)
                ->where($this->tb_prefix . "user_fields_meta.type != 'heading'")
                ->where($this->tb_prefix . 'user_fields_meta.hidden != 2')
                ->order($this->tb_prefix . 'user_fields_meta.order')
                ->query()
                ->fetchAll();

            $fieldOptions = $this->db->select()
                ->from($this->tb_prefix . 'user_fields_options')
                ->order('order', 'option_id')
                ->query()
                ->fetchAll();

            foreach ($fieldMap as $fieldSpec) {
                if (array_key_exists($fieldSpec['name'], $userFiledSettings)) {

                    if ($userFiledSettings[$fieldSpec['name']] == 'required') {
                        $fieldSpec['required'] = 1;
                    } else {
                        $fieldSpec['required'] = 0;
                    }

                    $element = $this->createExtraField($fieldSpec, $fieldOptions);
                    $this->hasFields = true;
                    if (array_key_exists($fieldSpec['name'], $user)) {
                        $value = $user [$fieldSpec['name']];
                        if ($element instanceof Engine_Form_Element_Select) {
                            $element->setValue($element->getMultiOption($value));
                        } else if ($element instanceof Engine_Form_Element_MultiText) {
                            $value = json_decode($value);
                            $element->setValue($value);
                        } else {
                            $element->setValue($value);
                        }
                    }
                }
            }
            if ($this->country){
                $this->country->setAttrib('readonly', 'true');
            }
        }

        $this->addElement('Button', 'submit_fields'.$this->_data_type, array(
            'data-type' => $this->_data_type,
            'class' => 'save',
            'ignore' => true,
            'label' => $this->_translate->_('Save'),
            'title' => $this->_translate->_('Save values'),
            'type' => 'submit',
        ));

        $this->addElement('Button', 'finish_fields'.$this->_data_type, array(
            'data-type' => $this->_data_type,
            'ignore' => true,
            'label' => "<i class='fa fa-check'></i> &nbsp;" . $this->_translate->_('Finish'),
            'disabled' => 1,
            'onclick' => self::$_finishAction,
            'escape' => false
        ));

        $this->addElement('Button', 'next_fields'.$this->_data_type, array(
            'data-type' => $this->_data_type,
            'ignore' => true,
            'label' => $this->_translate->_('Next') . '&nbsp; <i class="fa fa-chevron-right"></i>',
            'onclick' => 'settingsNextSbm = true;',
            'type' => 'submit',
            'escape' => false
        ));

        $this->addDisplayGroup(array('submit_fields'.$this->_data_type, 'finish_fields'.$this->_data_type, 'next_fields'.$this->_data_type), 'buttons_fields'.$this->_data_type, array('order' => 1000));
    }

    static $users = array();

    private function getUserInfo($userId)
    {
        if (array_key_exists($userId, self::$users)) {
            return self::$users [$userId];
        }

        $query = $this->db->select()
            ->from($this->tb_prefix . 'users')
            ->where($this->tb_prefix . 'users.user_id = ? ', $userId);
        $basicFields = $this->db->fetchRow($query, null, Zend_Db::FETCH_ASSOC);

        $query = $this->db->select()
            ->from(array(
                'v' => $this->tb_prefix . 'user_fields_values'
            ), array())
            ->joinInner(array(
                'm' => $this->tb_prefix . 'user_fields_meta'
            ), 'm.field_id = v.field_id', array())
            ->columns(array(
                'm.name',
                'v.value'
            ))
            ->where('v.item_id = ?', $userId);
        $extraFields = $this->db->fetchPairs($query);

        $zftshFields = Engine_Api::_()->getItem('user', $userId)->getZftshMemberData();

        self::$users [$userId] = $user = array_merge($basicFields, $extraFields, $zftshFields);
        return $user;
    }

    private function getUserFieldsSettings($user)
    {
        $fildSettings = Engine_Api::_()->core()
            ->getUserFieldsSettings();
        $profileStatus = $user ['profile_status'];
        if (!array_key_exists($profileStatus, $fildSettings)) {
            $profileStatus = 'other';
        }
        if (!array_key_exists($profileStatus, $fildSettings)) {
            return array();
        } else {
            return $fildSettings [$profileStatus];
        }
    }

    private function createCommonUserFields($user)
    {
        //КОСТЫЛЬ для перевода во вкладке настроек Документы
        $this->getView()->headTranslate(['Other documents']);
        //

        $DS = Engine_Api::_()->core()->getNowDomainSettings();
        $this->addElement('PersonName', 'last_name', array(
            'label' => $this->_translate->_('Last Name'),
            'order' => $this->orderIndex++,
            'placeholder' => $this->_translate->_('Last name'),
            'required' => true,
            'title' => $this->_translate->_('Your last name, for example, Smith'),
            'value' => $user ['last_name'],
            'cyrillic_only' => !empty($DS['zftshDefaults'])
        ));

        $this->addElement('PersonName', 'first_name', array(
            'label' => $this->_translate->_('First Name'),
            'order' => $this->orderIndex++,
            'placeholder' => $this->_translate->_('First name'),
            'required' => true,
            'title' => $this->_translate->_('Your first name, for example, John'),
            'value' => $user ['first_name'],
            'cyrillic_only' => !empty($DS['zftshDefaults'])
        ));

        $this->addElement('PersonName', 'middle_name', array(
            'label' => $this->_translate->_('Middle Name'),
            'order' => $this->orderIndex++,
            'placeholder' => $this->_translate->_('Middle name'),
            'title' => $this->_translate->_('Your middle name, for example, Michael'),
            'value' => $user ['middle_name'],
            'cyrillic_only' => !empty($DS['zftshDefaults'])
        ));

        $this->addElement('Select', 'gender', array(
            'label' => 'Gender',
            'order' => $this->orderIndex++,
            'required' => true,
            'multiOptions' => array(2 => 'Male', 1 => 'Female'),
            'value' => $user ['gender']
        ));
        $this->addElement('Birthdate', 'birthdate', array(
            'label' => 'Birthdate',
            'order' => $this->orderIndex++,
            'required' => true,
            'description' => Zend_Registry::get('Zend_Translate')->translate("Date in format DD.MM.YYYY"),
            'value' => $user ['birthdate']
        ));


        if (!empty($DS['zftshDefaults'])) {
            if (Engine_Api::_()->user()->getViewer()->hasMethodistRights()) {
                $this->getView()->headScript()->appendFile('/application/modules/Zftsh/externals/scripts/member_code_field.js');

                $this->addElement('Text', 'member_code', array(
                    'label' => 'Персональный номер ЗФТШ',
                    'maxlength' => 20,
                    'order' => $this->orderIndex++,
                    'value' => isset($user ['member_code']) ? $user ['member_code'] : '',
                    'filters' => [new Zftsh_Form_MemcodeFilter()],
                    'validators' => [new Engine_Validate_Callback(function($value){
                        return preg_match(Zftsh_Form_MemcodeFilter::MEMBER_CODE_REGEX, $value);
                    }, ['message' => 'Неверный формат кода ЗФТШ. Укажите, если код вам ещё не выдан'])]
                ));
            }else if (!empty($user ['member_code'])){
                $this->addElement('Dummy', 'member_code', [
                    'label' => 'Персональный номер ЗФТШ',
                    'content' => $user ['member_code'],
                    'order' =>  $this->orderIndex++
                ]);
            }

            $isTeachersProfile = in_array($this->_item->academyStatus(), ['teacher_new','teacher_approved','methodist','admin']);

            $learninfoLabel = $isTeachersProfile ? "О себе (ЗФТШ)" : 'Информация об обучении (ЗФТШ)';
            $learninfoDescription = $isTeachersProfile ? "Информация, которую вы бы хотели дать обучающимся" : 'Дополнительная информация касаемо обучения в ЗФТШ';

            if (Engine_Api::_()->user()->getViewer()->hasMethodistRights() || in_array(Engine_Api::_()->user()->getViewer()->academyStatus(), ['teacher_new', 'teacher_approved'])) {
                $this->addElement('TinyMce', 'learninfo', array(
                    'label' => $learninfoLabel,
                    'order' => $this->orderIndex++,
                    'description' => $learninfoDescription,
                    'value' => isset($user ['learninfo']) ? $user ['learninfo'] : '',
                    'filters' => [new Engine_Filter_Html(['useDefaultLists' => true])],
                ));
            }else if (!empty($user ['learninfo'])){
                $this->addElement('Dummy', 'learninfo', [
                    'label' => $learninfoLabel,
                    'description' => $learninfoDescription,
                    'content' => $user ['learninfo'],
                    'order' =>  $this->orderIndex++
                ]);
            }

            if ($isTeachersProfile){
                $this->addElement('TinyMce', 'contact_information', array(
                    'label' => 'Контактная информация (ЗФТШ)',
                    'order' => $this->orderIndex++,
                    'description' => 'Текст, отображающийся пользователям при отправке сообщений Вам',
                    'value' => isset($user ['contact_information']) ? $user ['contact_information'] : '',
                    'filters' => [new Engine_Filter_Html(['useDefaultLists' => true])]
                ));

                $this->addElement('TinyMce', 'wishes', array(
                    'label' => 'Пожелания по работе в ЗФТШ-онлайн',
                    'order' => $this->orderIndex++,
                    'description' => 'Укажите по каким предметам и в каких классах Вы бы хотели работать, удобные способы связи с Вами, а так же любую информацию, которую Вы бы хотели сообщить методисту.',
                    'value' => isset($user ['wishes']) ? $user ['wishes'] : '',
                    'filters' => [new Engine_Filter_Html(['useDefaultLists' => true])]
                ));

                $this->addElement('Text', 'cell_number', array(
                    'label' => 'Номер ячейки (ЗФТШ)',
                    'maxlength' => 10,
                    'order' => $this->orderIndex++,
                    'value' => isset($user['cell_number']) ? $user['cell_number'] : '',
                    'description' => 'Номер ячейки'
                ));
            }
        }

        /*@var $usersDb User_Model_DbTable_Users */
        $usersDb = Engine_Api::_()->getItemTable("user", "user");
        $this->addElement('select', 'profile_status', array(
            'allowEmpty' => false,
            'label' => $this->_translate->_('Profile type'),
            'multiOptions' => $usersDb->getProfileTypes(),
            'order' => $this->orderIndex++,
            'value' => $user ['profile_status'],
            'required' => true
        ));


        $this->addElement('telephone', 'mobilephone', array(
            'label' => $this->_translate->_('Mobilephone'),
            'order' => $this->orderIndex++,
            'placeholder' => $this->_translate->_('Mobilephone'),
            'required' => true,
            'value' => $user ['mobilephone']
        ));

        $this->addElement('Text', 'homephone', array(
            'label' => $this->_translate->_('Home phone'),
            'order' => $this->orderIndex++,
            'description' => $this->_translate->_('Your home phone number in international format, for example, +1-123-456-78-90'),
            'placeholder' => $this->_translate->_('Home phone'),
            'title' => $this->_translate->_('Enter your home phone number in international format, for example, +1-123-456-78-90'),
            'value' => $user ['homephone']
        ));

        if (!empty($DS['zftshDefaults'])) {
            $this->addElement('multitext', 'parents_fio', array(
                'label' => 'ФИО родителей',
                'order' => $this->orderIndex++,
                'description' => 'Пожалуйста, укажите хотя-бы одного родителя или опекуна',
                'required' =>  $user ['profile_status'] == 'schoolboy' ? true : false,
                'value' => isset($user['parents_fio']) ? json_decode($user['parents_fio']) : [],
            ));

            $this->addElement('multitext', 'parents_phone', array(
                'label' => 'Телефоны родителей',
                'order' => $this->orderIndex++,
                'description' => 'Пожалуйста, укажите телефон хотя-бы одного опекуна',
                'required' =>  $user ['profile_status'] == 'schoolboy' ? true : false,
                'value' => isset($user['parents_phone']) ? json_decode($user['parents_phone']) : [],
            ));


        }

        $this->addElement('Text', 'citizenship', array(
            'label' => 'Citizenship',
            'inputType' => 'text',
            'order' => $this->orderIndex++,
            'value' => isset($user ['citizenship']) ? $user ['citizenship'] : '',
            'filters' => array(
                'StringTrim',
                'StripTags',
                new Engine_Filter_StringLength(array('max' => '50')),
            )
        ));

	}

    private function createExtraField($fieldSpec, $fieldOptions)
    {
        $inflectedType = Engine_Api::_()->fields()
            ->inflectFieldType($fieldSpec ['type']);

        $options = null;

        if ($this->orderIndex === 4 || $this->orderIndex === 6) {
            $this->orderIndex++;
        }

        $options ['order'] = $this->orderIndex++;

        if ($fieldSpec ['hidden'] === 1) {
            $options ['class'] = 'hidden';
        }

        $options ['required'] = $fieldSpec ['required'] === 1;

        if ($fieldSpec ['label'] != '') {
            $options ['label'] = $this->_translate->_($fieldSpec ['label']);
        }

        if ($fieldSpec ['placeholder'] != '') {
            $options ['placeholder'] = $this->_translate->_($fieldSpec ['placeholder']);
        }

        if ($fieldSpec ['description'] != '') {
            $options ['description'] = $this->_translate->_($fieldSpec ['description']);
        }

        if ($fieldSpec ['title'] != '') {
            $options ['title'] = $this->_translate->_($fieldSpec ['title']);
        }

        if ($fieldSpec ['validators']) {
            $options ['validators'] = json_decode($fieldSpec ['validators']);
        }

        $options ['id'] = $fieldSpec ['name'];

        if ($fieldSpec ['type'] === 'select') {
            foreach ($fieldOptions as $fieldOption) {
                if ($fieldOption['field_id'] == $fieldSpec['field_id']) {
                    $options['multiOptions'][$fieldOption['label'] ? $fieldOption['option_id'] : ''] = $fieldOption['label'];
                }
            }
        }
        $element = $this->createElement($inflectedType, $fieldSpec ['name'], $options);

        $this->addElement($element);
        if ($fieldSpec ['name'] == 'school_reference'){
            $refStatus = $this->_item ? $this->_item->school_reference_status : '';
            $this->addElement('Checkbox', 'school_reference_status', array(
                'label' => 'Certificate approved',
                'checkedValue' => '2',
                'uncheckedValue' => '1',
                'order' => $this->orderIndex++,
                'value' => $refStatus
            ));
            $viewer = Engine_Api::_()->user()->getViewer();
            if (!in_array($viewer->level_id, [1,2]) && !$viewer->hasMethodistRights()){
                $this->school_reference_status->setAttrib('disabled', 'disabled');
                if ($refStatus == 2){
                    $element->setAttrib('disabled', 'disabled');
                }
            }
        }
        return $element;
    }

    public function isValid($data)
    {
        $commonValid = parent::isValid($data);
        if ($this->_data_type === '0' && isset($this->member_code) && ($setCode = $this->member_code->getValue())){
            if ($userId = $this->db->fetchOne(
                $this->db->select()->from('engine4_zftsh_member_data', ['user_id'])
                    ->where('member_code = ?',$setCode)
                    ->where('user_id != ?', $this->_user_id)
            )){
                $this->member_code->addError('Такой персональный код уже зарегистрирован у пользователя ' . Engine_Api::_()->getItem('user', $userId) . '. Сбростье сначала существующий код.');
                $commonValid = false;
            };
        }
        if ($commonValid && ($element = $this->getElement('member_code')) && ($generatedCode = Engine_Api::_()->zftsh()->fillUserMemberCode($this->_item)) ){
            $element->setValue($generatedCode);
        }
        return $commonValid;
    }

    public function getValues($suppressArrayNotation = false)
    {
        $values = parent::getValues($suppressArrayNotation);

        foreach ($this->getElements() as $element) {
            if ($element instanceof Engine_Form_Element_Textarea && $element->getAttrib('disabled')){
                unset($values[$element->getName()]);
            }
        }
        return $values;
    }
}
