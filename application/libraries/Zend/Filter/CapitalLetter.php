<?php
class Zend_Filter_CapitalLetter implements Zend_Filter_Interface
{
    
    protected $_encoding = null;
    
    public function setEncoding($encoding = null)
    {
        if (!function_exists('mb_convert_case'))
        {
            throw new Zend_Filter_Exception('mb_convert_case is required for this feature');
        }
        $this->_encoding = $encoding;
    }
    
    
    public function filter($value)
    {
        if ($this->_encoding) 
        {
            return mb_convert_case($value, MB_CASE_TITLE, $this->_encoding);
        }
        
        return mb_convert_case($value, MB_CASE_TITLE);
    }

}