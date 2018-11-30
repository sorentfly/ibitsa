<?php
class Bitsa_Form_Element_District extends Zend_Form_Element_Text
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate'); 
        $this->addFilter('StringTrim');
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