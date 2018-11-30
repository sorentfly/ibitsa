<?php
class Bitsa_Form_Element_Hash extends Zend_Form_Element_Hash
{
    protected $_order = 998;
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
        }
    }
}