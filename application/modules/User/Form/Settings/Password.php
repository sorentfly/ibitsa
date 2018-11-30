<?

class User_Form_Settings_Password extends Engine_Form
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        // @todo fix form CSS/decorators
        // @todo replace fake values with real values
        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

        // Init old password
        $this->addElement('Password', 'oldPassword', array(
            'allowEmpty' => false,
            'label' => 'Old Password',
            'maxlength' => '32',
            'placeholder' => $translate->_('Old Password'),
            'required' => true,
            'title' => $translate->_('Old Password'),
        ));

        // Init password
        $this->addElement('Password', 'password', array(
            'allowEmpty' => false,
            'description' => 'Passwords must be at least 6 characters in length.',
            'label' => 'New Password',
            'maxlength' => '32',
            'placeholder' => $translate->_('New Password'),
            'required' => true,
            'title' => $translate->_('New Password'),
            'validators' => array(
                array('stringLength', false, array(6, 32))
            )
        ));
        $this->password->getDecorator('Description')->setOption('placement', 'APPEND');

        // Init password confirm
        $this->addElement('Password', 'passwordConfirm', array(
            'allowEmpty' => false,
            'description' => 'Enter your password again for confirmation.',
            'label' => 'New Password (again)',
            'maxlength' => '32',
            'placeholder' => $translate->_('New Password (again)'),
            'required' => true,
            'title' => $translate->_('New Password (again)'),
            'validators' => array(
                array('stringLength', false, array(6, 32))
            )
        ));
        $this->passwordConfirm->getDecorator('Description')->setOption('placement', 'APPEND');

        // Init submit
        $this->addElement('Button', 'submit_password', array(
            'class' => 'save',
            'label' => 'Change Password',
            'type' => 'submit',
            'ignore' => true
        ));

        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    }
}
