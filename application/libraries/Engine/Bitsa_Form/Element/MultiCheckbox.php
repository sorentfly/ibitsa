<?php
class Bitsa_Form_Element_MultiCheckbox extends Zend_Form_Element_MultiCheckbox
{
    protected $_separator = '<li>';
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