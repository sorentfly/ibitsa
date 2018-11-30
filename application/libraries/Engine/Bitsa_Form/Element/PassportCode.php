<?php
class Bitsa_Form_Element_PassportCode extends Bitsa_Form_Element_Text
{
    public function init()
    {
        parent::init();

        $this->addFilter(
                 (new Zend_Filter_PregReplace())
                 ->setMatchPattern('@[^0-9-]+@')
                 ->setReplacement('')
             );
        $this->setAttrib('pattern', '[0-9-]+')
            ->setAttrib('title', 'допускаются цифры, и знак -');
    }
}