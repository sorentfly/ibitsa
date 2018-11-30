<?

class User_Form_Special_MiptConferenceMember extends Engine_Form
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        $this
            ->getView()->headScript()
            ->appendFile(JQUERY_UI_LIB)
            ->appendFile('/application/modules/Core/externals/scripts/notification.js')
            ->appendFile('/application/modules/User/externals/scripts/profile_edit.js');

        $this
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setMethod('POST')
            ->setTitle('Регистрация слушателей');

        $this
            ->addPrefixPath('Abitu_Form_Decorator', 'Engine/Abitu_Form/Decorator', 'decorator')
            ->addPrefixPath('Abitu_Form_Element', 'Engine/Abitu_Form/Element', 'element');

        $lastname = $this->createElement('Text', 'lastname', array(
            'label' => 'Last Name',
            'allowEmpty' => false,
            'required' => true,
        ));

        $firstname = $this->createElement('Text', 'firstname', array(
            'label' => 'First Name',
            'allowEmpty' => false,
            'required' => true,
        ));

        $middlename = $this->createElement('Text', 'middlename', array(
            'label' => 'Middle Name',
            'allowEmpty' => false,
            'required' => true,
        ));

        $work = $this->createElement('Text', 'work', array(
            'label' => 'Место работы',
            'allowEmpty' => false,
            'required' => true,
        ));

        $position = $this->createElement('Text', 'position', array(
            'label' => 'Должность',
            'allowEmpty' => false,
            'required' => true,
        ));

        $sections = $this->createElement('MultiCheckbox', 'sections', array(
            'label' => 'Укажите пленарные заседания, в которых планируете принять участие',
            'checkedValue' => 0,
            'multiOptions' => array (
                '0' => 'Физтех-школа фундаментальной и прикладной физики',
                //'1' => 'Физтех-школа аэрокосмических технологий',
                '5' => 'Физтех-школа аэрокосмических технологий (ФАКИ, г. Долгопрудный)',
                '6' => 'Физтех-школа аэрокосмических технологий (ФАЛТ, г. Жуковский)',
                '2' => 'Физтех-школа электроники, фотоники и молекулярной физики',
                '3' => 'Физтех-школа прикладной математики и информатики',
                '4' => 'Физтех-школа биологической и медицинской физики',
            ),
        ));

        $this
            ->addElement($lastname)
            ->addElement($firstname)
            ->addElement($middlename)
            ->addElement($work)
            ->addElement($position)
            ->addElement($sections);

        $this->addElement('Button', 'submit', array(
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
        $this->submit->setLabel('Добавить ещё одну заявку');
        //$this->populate(array_pop($data));
        return $this;
    }

    public function getSuccessLabel()
    {
        $this->reset();
        return "Ваши данные успешно сохранены.";
    }
}
