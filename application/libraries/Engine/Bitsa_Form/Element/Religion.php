<?php
class Bitsa_Form_Element_Religion extends Zend_Form_Element_Select
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
            $this->addDecorator('ViewHelper')
                ->addDecorator('FormSelect');
        }
    }
}