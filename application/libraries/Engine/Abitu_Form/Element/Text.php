<?php
class Abitu_Form_Element_Text extends Zend_Form_Element_Text
{
    public function init()
    {
        //$translate = Zend_Registry::get('Zend_Translate');
        $this->addFilters(array('StringTrim', 'StripTags'));        
        
        if($this->isRequired())
        {
            $this->setAttrib('required', 'required');
        }
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