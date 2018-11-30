<?php
class Zend_Filter_BirthdateNormalized implements Zend_Filter_Interface
{
    public function filter($value)
    {  
        return date('Y-m-d',strtotime($value));
    }
}