<?php
class Bitsa_Form_Element_VK extends Zend_Form_Element_Text
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate'); 
        $this->addFilter('StringTrim');
        $this->addFilter('CapitalLetter');
        $this->addFilter('StripTags');
        $this->setAttrib('maxlength', '256');
        $this->setAttrib('pattern', '^(vk\.com\/[_\w]+)$');
        //$this->setAttrib('data-enable-chars', '/^[_\w\/]$/');
        
        
        //$this->addValidator('VKPage', true);                
        $this->addValidator('NotEmpty', true);        
        $this->addValidator('StringLength', true, array(2, 256));        
        $this->addValidator('Regex', true, array('/^(vk\.com\/[_\w]+)$/'));
        
        $this->getValidator('NotEmpty')->setMessage($translate->_('Your page address in VK social network is empty'));
        $this->getValidator('Regex')->setMessage($translate->_('Your page address in VK social network is incorrect'), 'regexNotMatch');
        $this->getValidator('StringLength')->setMessage($translate->_('Your page address in VK social network must have at least 2 characters'), 'stringLengthTooShort');
        $this->getValidator('StringLength')->setMessage($translate->_('Your page address in VK social network must have at most 256 characters'), 'stringLengthTooLong');
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
                    ->addDecorator('FormText');
        }
    }
}