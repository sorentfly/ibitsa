<?php
class Abitu_Form_Element_Float extends Engine_Form_Element_Text
{
    public function init()
    {
        $this->addValidator('Float', true);
    }
}