<?

class User_Form_Special_JoinZftsh extends Engine_Form
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
            ->setTitle('Вступить в Онлайн-ЗФТШ');

        $this->addPrefixPath('Abitu_Form_Decorator', 'Engine/Abitu_Form/Decorator', 'decorator')
            ->addPrefixPath('Abitu_Form_Element', 'Engine/Abitu_Form/Element', 'element');




        $code = $this->createElement('Text', 'code', array(
            'label' => 'ZFTSH membercode',
            'allowEmpty' => false,
            'required' => true,
        ));

        //$code->addValidator('regex', false, array('/^[a-z]+/'));

        $this->addElement($code);

        $this->addElement('MultiCheckbox', 'subjects', array(
            'label' => 'Subjects',
        ));

        $this->addElement('Checkbox', 'Math', array(
            'label' => 'Math',
        ));
        $this->addElement('Checkbox', 'Physics', array(
            'label' => 'Physics',
        ));
        $this->addElement('Checkbox', 'IT', array(
            'label' => 'Informatics and IKT',
        ));
        $this->addElement('Checkbox', 'Chemistry', array(
            'label' => 'Chemistry',
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
        return "Ваши данные успешно сохранены.";
    }
}
