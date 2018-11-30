<?php

class Engine_ServiceLocator_Form_Cache_Memcache extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'host', array(
      'label' => 'Server Host',
      'value' => 'localhost',
    ));
    
    $this->addElement('Text', 'port', array(
      'label' => 'Server Port',
      'value' => '11211',
    ));
  }
}
