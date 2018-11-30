<?php

class Engine_ServiceLocator_Form_Cache_Xcache extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'user', array(
      'label' => 'Admin Username',
    ));
    
    $this->addElement('Checkbox', 'password', array(
      'label' => 'Admin Password',
    ));
  }
}
