<?php
class Abitu_Form_Element_Image extends Zend_Form_Element_Image
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
            $this
                    ->addDecorator('Tooltip')
                    ->addDecorator('Image');

            Engine_Form::addDefaultDecorators($this);
        }
    }
}