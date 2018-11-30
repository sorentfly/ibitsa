<?php
class Engine_Form_Element_CallbackTime extends Zend_Form_Element_Text
{
    public function init()
    {
        parent::init();
        
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
            $this->addDecorator('ViewHelper');
            $this->addDecorator('FormCallbackTime');            
        }
    }

}
