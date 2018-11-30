<?php
class Bitsa_Form_Element_Multiselect extends Zend_Form_Element_Multiselect
{
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
            Engine_Form::addDefaultDecorators($this);
        }
    }
}