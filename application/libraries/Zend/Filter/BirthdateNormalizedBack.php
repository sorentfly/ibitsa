<?php
class Zend_Filter_BirthdateNormalizedBack implements Zend_Filter_Interface
{
    public function filter($value)
    {  
        return date('d.m.Y',strtotime($value));
    }
}