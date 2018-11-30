<?
class Engine_Form_Element_Telephone extends Zend_Form_Element_Text
{
    private $translate;

    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        $this->addFilters(array('StringTrim', 'StripTags'));

        $attributes = array('maxlength' => '32');

        if ($this->isRequired()) {
            $attributes['required'] = 'required';
            $this->addValidator('NotEmpty', true);
            $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your telephone is empty'), 'isEmpty');
            $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your telephone is empty'), 'notEmptyInvalid');
        }

        $this->setOptions(array('inputType' => 'tel'));

        if ($this->getValue() !== '') {
            $this->addValidator('StringLength', false, array(3, 127));
            $this->addValidator('Regex', false, array('/^[+][0-9]{1,3}-[0-9]{2,3}-[0-9]{3}-[0-9]{2}-[0-9]{2}$/'));
            $this->getValidator('Regex')->setMessage($this->translate->_('Incorrect telephone'), 'regexNotMatch');
            $this->getValidator('StringLength')->setMessage($this->translate->_('Telephone must have at least %min% characters'), 'stringLengthTooShort');
            $this->getValidator('StringLength')->setMessage($this->translate->_('Telephone must have at most %max% characters'), 'stringLengthTooLong');
        }

        $this->setAttribs($attributes);
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper')
                ->addDecorator('FormTelephone');
        }
    }
}