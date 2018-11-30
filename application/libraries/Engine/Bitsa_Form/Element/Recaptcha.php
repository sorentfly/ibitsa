<?php
require_once('application/libraries/recaptcha-php-1.11/recaptchalib.php');
class Bitsa_Form_Element_Recaptcha extends Zend_Form_Element_Text
{
    private $translator;
    public function init()
    {
        $this->translator = $this->getTranslator();
        $this->addFilters(array('StringTrim', 'StripTags'));
        
        $attributes = array(
            'autocomplete' => 'off',
            'maxlength' => '128',
            'onpaste' => 'return false;',
            'placeholder' => $this->translator->translate('Enter the symbols from the picture'),
            'title' => $this->translator->translate('Enter the symbols from the picture')
        );
        
        $this->setLabel($this->translator->translate('Enter the symbols from the picture'));                
        
        if($this->isRequired())
        {
            $attributes['required'] = 'required';
        }
        
        $this->setAttribs($attributes);
        
        $this->addValidator('NotEmpty', true);
        $this->addValidator('ReCaptcha', true);
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Symbols from the picture is not entered'), 'isEmpty');
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Symbols from the picture is not entered'), 'notEmptyInvalid');        
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
                 ->addDecorator('FormRecaptcha');
        }
    }

}