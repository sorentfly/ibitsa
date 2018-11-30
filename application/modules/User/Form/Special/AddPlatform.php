<?

class User_Form_Special_AddPlatform extends Engine_Form
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        $this->getView()->headScript()
            ->appendFile(JQUERY_UI_LIB)
            ->appendFile('/application/modules/Core/externals/scripts/notification.js')
            ->appendFile('/application/modules/User/externals/scripts/profile_edit.js')
        ;

        $this
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setMethod('POST')
            ->setTitle('Стать площадкой проведения контрольной');

        $this->addPrefixPath('Abitu_Form_Decorator', 'Engine/Abitu_Form/Decorator', 'decorator')
            ->addPrefixPath('Abitu_Form_Element', 'Engine/Abitu_Form/Element', 'element');




        $this->addElement('Text', 'title', array(
            'label' => 'Title',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,48)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));

        $this->addElement('Text', 'fullname', array(
            'label' => 'Fullname',
            'allowEmpty' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,256)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));


        $this->addElement('Country', 'country', ['label' => 'Страна']);
        $this->country->setRequired(true)->setAttrib('placeholder', 'Выберите страну')->setAttrib('required', 'required');

        $this->addElement('City', 'city', ['label' => 'Город']);
        $this->city->setRequired(true)->setAttrib('placeholder', 'Начните вводить город')->setAttrib('required', 'required');

        $this->addElement('Region', 'region', ['label' => 'Регион']);
        $this->region->setRequired(true)->setAttrib('placeholder', 'Выберите регион')->setAttrib('required', 'required');

        $this->addElement('Text', 'address', array(
            'label' => 'Address',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,256)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));

        $this->addElement('telephone', 'mobilephone', array(
            'allowEmpty' => false,
            'label' => $translate->_('Mobilephone'),
            'maxlength' => '127',
            'placeholder' => $translate->_('Mobilephone'),
            'required' => true
        ));


        $this->addElement('Text', 'person', array(
            'label' => 'Контактное лицо',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,256)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));

        $this->addElement('Text', 'logo_url', array(
            'label' => 'Ссылка на логотип ',
            'allowEmpty' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,256)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));

        $this->addElement(
            (new Engine_Form_Element_CalendarDateTime('start_date'))
            ->setLabel('Время проведения')
            ->setAllowEmpty(false)
            ->setRequired(true)
        );

        $this->addElement('Text', 'places_count', array(
            'label' => 'Количество посадочных мест',
            'allowEmpty' => false,
            'required' => true,
            'inputType' => 'number',
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(0,256)),
            ),
            'filters' => array(
                'StringTrim',
                'StripTags'
            ),
        ));



        $this->addElement('Button', 'submit', array (
            'label' => 'Добавить заявку',
            'ignore' => true,
            'decorators' => array (
                'ViewHelper'
            ),
            'type' => 'submit'
        ));

        $this->addElement('Cancel', 'cancel', array (
            'label' => 'cancel',
            'link' => true,
            'href' => '',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array (
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array (
            'submit',
            'cancel'
        ), 'buttons');
    }

    public function alreadyHasDataHook($data)
    {
        $this->submit->setLabel('Редактировать заявку');
        return $this;
    }

    public function getSuccessLabel()
    {
        return "Ваша заявка на проведение контрольной успешно сохранена!";
    }
}
