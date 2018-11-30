<?php

class Engine_ServiceLocator_Form_Captcha_Recaptcha extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    
    
    $this->addElement('Text', 'pubkey', array(
      'label' => 'Public Key',
      'allowEmpty' => false,
    ));
    
    $this->addElement('Text', 'privkey', array(
      'label' => 'Private Key',
      'allowEmpty' => false,
    ));
  }
}
