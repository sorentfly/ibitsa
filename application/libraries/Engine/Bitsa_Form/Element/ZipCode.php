<?php
class Bitsa_Form_Element_ZipCode extends Zend_Form_Element_Text
{
    public function init()
    {
         $this->addFilter('StringTrim');     
        $this->addFilter('StripTags');
       // $this->addValidator('Regex', true, array('/^(\d{5}-\d{4})|(\d{5})$/'));
        // Fix messages
        //$this->getValidator('Regex')->setMessage("'%value%' is not a valid zip code.", 'regexNotMatch');
    }

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
                    ->addDecorator('FormText');
        }
    }

}