<?php
class Abitu_Form_Element_Hidden extends Zend_Form_Element_Hidden
{
    protected $_order = 999;

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