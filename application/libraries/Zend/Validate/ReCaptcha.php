<?php
class Zend_Validate_ReCaptcha extends Zend_Validate_Abstract
{
    const INVALID  = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID => "Wrong entered symbols from the picture"     
    );

    public function isValid($value)
    {
        if(isset($_SERVER['HTTP_X_REAL_IP']))
        {
            $remote_address = $_SERVER['HTTP_X_REAL_IP'];
        }
        else
        {
            $remote_address = $_SERVER['REMOTE_ADDR'];
        }
        
        $resp = recaptcha_check_answer((Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptchaprivate), 
                $remote_address,
                $_POST['recaptcha_challenge_field'], 
                $value);
        
        if(!$resp->is_valid)
        {
            $this->_error(self::INVALID);
            return false;
        }
        else
        {
            return true;
        }
              
    }

}
