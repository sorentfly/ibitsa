<?php
class Abitu_Form_Element_SubmitButton extends Zend_Form_Element_Button
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
                    ->addDecorator('FormSubmitButton');
        }
    }
}