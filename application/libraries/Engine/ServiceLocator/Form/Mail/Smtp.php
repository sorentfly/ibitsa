<?php

class Engine_ServiceLocator_Form_Mail_Smtp extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'host', array(
      'label' => 'Host',
      'value' => '127.0.0.1',
    ));
    
    $this->addElement('Text', 'port', array(
      'label' => 'Port',
      'value' => '',
    ));
    
  }
}
    