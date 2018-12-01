<?php
class Bitsa_Form_Element_Combobox extends Zend_Form_Element_Text
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
            $this->addDecorator('FormCombobox');            
        }
    }
}