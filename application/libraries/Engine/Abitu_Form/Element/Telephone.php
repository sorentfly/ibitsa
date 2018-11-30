<?php
class Abitu_Form_Element_Telephone extends Zend_Form_Element_Text
{
    private $translate;
    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate');        
        $this->addFilters(array('StringTrim', 'StripTags'));
        $attributes = array(
            'maxlength' => '127',
            'placeholder' => $this->translate->_('Mobilephone'),
            //'title' => $this->translate->_('Mobile phone number in international format, for example, +12345678900')
        );
        
        //$this->setDescription($this->translate->_('Mobile phone number in international format, for example, +12345678900'));
        $this->setLabel($this->translate->_('Mobilephone'));
         
        if($this->isRequired())
        {
            $attributes['required'] = 'required';
            $this->addValidator('NotEmpty', true);
            $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your telephone is empty'), 'isEmpty');
            $this->getValidator('NotEmpty')->setMessage($this->translate->_('Your telephone is empty'), 'notEmptyInvalid');
        }
        
        $this->setOptions(array('inputType' => 'tel'));
        
        if ($this->getValue() !== '')
        {
            $current_value = $this->getValue();
            $this->addFilter(new Zend_Filter_StringTrim(['charlist' => ' _']));
            $this->addValidator('StringLength', false, array(6, 127));
            $this->addValidator('Regex', false, array('/^[\+]{1}([\d]+\s*[\-]?[\d]+)+$/'));
            $this->getValidator('Regex')->setMessage($this->translate->_('Incorrect telephone'), 'regexNotMatch');
            $this->getValidator('StringLength')->setMessage($this->translate->_('Telephone must have at least %min% characters'), 'stringLengthTooShort');
            $this->getValidator('StringLength')->setMessage($this->translate->_('Telephone must have at most %max% characters'), 'stringLengthTooLong');
        }
        else
        {
            $this->setValue('+7-___-___-__-__');
            $this->setAttrib('data-pattern', '\+7-[0-9_]{3}-[0-9_]{3}-[0-9_]{2}-[0-9_]{2}');
        }
        
        $this->setAttribs($attributes);
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
                 ->addDecorator('FormTelephone');
                //->addDecorator('FormText');
        }
    }
}