<?
class User_Form_Login 
/*!THIS FORM IS DEPRECATED for rendering - see user.login-or-signup widget!*/

extends Engine_Form
{
    protected $_mode;

    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    public function getMode()
    {
        if (null === $this->_mode)
        {
            $this->_mode = 'page';
        }
        return $this->_mode;
    }

    public function init()
    {
        
        $tabindex = 1;

        $description = '';
        $description = sprintf($description, Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));

        // Init form
        $this->setTitle('Member Sign In');
        $this->setDescription($description);
        $this->setAttrib('id', 'user_form_login');
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);
        
        $email_label = Zend_Registry::get('Zend_Translate')->_('Email Address');
        // Init email
        $this->addElement('Text', 'email', array(
            'allowEmpty' => false,
            'autofocus' => 'autofocus',
            'class' => 'text',
            'filters' => array('StringTrim', 'StripTags'),
            'inputType' => 'email',
            'label' => $email_label,            
            'placeholder' => $email_label,
            'required' => true,
            'tabindex' => $tabindex++,
            'title' => Zend_Registry::get('Zend_Translate')->_('Your email'),
            'validators' => array('EmailAddress')
        ));
        $this->getElement('email')->setAttrib('required', 'required');

        $password_label = Zend_Registry::get('Zend_Translate')->_('Password');
        // Init password
        $this->addElement('Password', 'password', array(
            'allowEmpty' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'label' => $password_label,
            'oninput' => 'checkPasswordLayout(this)',
			'pattern' => '^[^\u0410-\u044f\u0451\u0401]+$',
            'placeholder' => $password_label,
            'required' => true,
            'tabindex' => $tabindex++,
            'title' => Zend_Registry::get('Zend_Translate')->_('Your password')
        ));
        $this->getElement('password')->setAttrib('required', 'required');

        $this->addElement('Hidden', 'return_url', array());
        // Init submit
        $this->addElement('Button', 'submit', array(
            'ignore' => true,
            'label' => 'Sign In',
            'tabindex' => $tabindex++,
            'type' => 'submit'
        ));
        // Set default action
        $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login'));
    }

}
