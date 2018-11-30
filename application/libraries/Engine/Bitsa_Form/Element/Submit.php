<?php
class Bitsa_Form_Element_Submit extends Zend_Form_Element_Submit
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
                    ->addDecorator('DivDivDivWrapper');
        }
    }

}