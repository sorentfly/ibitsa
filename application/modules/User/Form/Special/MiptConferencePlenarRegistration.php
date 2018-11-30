<?

class User_Form_Special_MiptConferencePlenarRegistration extends Engine_Form
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
            ->setTitle('Регистрация на пленарное заседание 60-й научной конференции МФТИ');

        $this
            ->addPrefixPath('Bitsa_Form_Decorator', 'Engine/Bitsa_Form/Decorator', 'decorator')
            ->addPrefixPath('Bitsa_Form_Element', 'Engine/Bitsa_Form/Element', 'element');

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

        $this
            ->addElement($lastname)
            ->addElement($firstname)
            ->addElement($middlename)
            ->addElement($work)
            ->addElement($position);

        $this->addElement('Button', 'submit', array(
            'label' => 'Зарегистрировать участника',
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
        $this->setTitle('Вы успешно зарегистрировались');
        $this->removeElement('lastname');
        $this->removeElement('firstname');
        $this->removeElement('middlename');
        $this->removeElement('work');
        $this->removeElement('position');
        $this->removeElement('submit');
        $this->removeElement('cancel');
        return $this;
    }

    public function getSuccessLabel()
    {
        return "";
    }
}
