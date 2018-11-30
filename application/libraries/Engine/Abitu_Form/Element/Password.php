<?php
class Abitu_Form_Element_Password extends Zend_Form_Element_Password
{
    private $translator;
    const PASSWORD_PATTERN_GENERAL = '/^[-_A-z0-9\!@#\$\%\^\&\*\(\)\+\:\;\,\.]*$/';
    public function init()
    {
        $this->translator = $this->getTranslator();
        $this->addFilters(array('StringTrim', 'StripTags'));
        
        $attributes = array(
        'autocomplete' => 'off',
        'maxlength' => '32',
        'pattern' => trim(json_encode('^[-_A-z0-9\!@#\$\%\^\&\*\(\)\+\:\;\,\.]{6,32}$'), '"'),
        'oncopy' => 'return false;',
        'title' => $this->translator->translate('Your password must contain between 6 and 32 characters. It can contain both letters and numbers plus any of the following symbols: ! # $ % ^ & * ( ) _ - + : ; . , @')
        );
        
        if($this->isRequired())
        {
            $attributes['required'] = 'required';
        }         
        
        $this->setAttribs($attributes);
            
        $this->addValidator('NotEmpty', true);
        $this->addValidator('StringLength', true, array(6, 32));
        $this->addValidator('Regex', true, array(self::PASSWORD_PATTERN_GENERAL));
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Your password is empty'), 'isEmpty');
        $this->getValidator('Regex')->setMessage($this->translator->translate('Incorrect password') . '. ' . $this->translator->translate('Your password must contain between 6 and 32 characters. It can contain both letters and numbers plus any of the following symbols: ! # $ % ^ & * ( ) _ - + : ; . , @'), 'regexNotMatch');
        $this->getValidator('StringLength')->setMessage($this->translator->translate('Passwords must have at least %min% characters'), 'stringLengthTooShort');
        $this->getValidator('StringLength')->setMessage($this->translator->translate('Passwords must have at most %max% characters'), 'stringLengthTooLong');
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
                 ->addDecorator('FormPassword');
        }
    }

}