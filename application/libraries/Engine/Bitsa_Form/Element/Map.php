<?php
class Bitsa_Form_Element_Map extends Zend_Form_Element
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
            $this->addDecorator('FormMap');
            Engine_Form::addDefaultDecorators($this);
        }
    }

}