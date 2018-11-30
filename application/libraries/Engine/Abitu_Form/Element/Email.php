<?php
class Abitu_Form_Element_Email extends Zend_Form_Element_Text
{
    private $translator;
    public function init()
    {
        $this->translator = $this->getTranslator();
        $this->addFilters(array('StringTrim', 'StripTags'));
        
        $this->setAttrib('maxlength', '127');
        $this->setOptions(array('inputType' => 'email'));
        
        $this->addValidator('NotEmpty', true);
        $this->addValidator('EmailAddress', true);        
        $this->addValidator('StringLength', true, array(3, 127));
        
        if($this->isRequired())
        {
            $this->setAttrib('required', 'required');
        }
        
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Your email is empty'), 'isEmpty');
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Your email is empty'), 'notEmptyInvalid');
        $this->getValidator('EmailAddress')->setMessage($this->translator->translate('Incorrect email'), 'emailAddressInvalidFormat');
        $this->getValidator('EmailAddress')->setMessage($this->translator->translate('Incorrect email'), 'emailAddressInvalidHostname');
        $this->getValidator('StringLength')->setMessage($this->translator->translate('Email must have at least %min% characters'), 'stringLengthTooShort');
        $this->getValidator('StringLength')->setMessage($this->translator->translate('Email must have at most %max% characters'), 'stringLengthTooLong');
        
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled())
        {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators))
        {
            $this->addDecorator('ViewHelper')
                 ->addDecorator('FormEmail');
        }
    }
}