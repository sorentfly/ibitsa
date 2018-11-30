<?php
class Abitu_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    public function init()
    {
        
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
            Engine_Form::addDefaultDecorators($this);
        }
    }

}
