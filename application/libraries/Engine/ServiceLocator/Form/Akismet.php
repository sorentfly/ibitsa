<?php

class Engine_ServiceLocator_Form_Akismet extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'key', array(
      'label' => 'API Key',
      'allowEmpty' => false,
      'required' => true,
    ));
    
    $this->addElement('Text', 'url', array(
      'label' => 'Site URL',
      'value' => 'http://' . $_SERVER['HTTP_HOST'] . constant('_ENGINE_R_BASE'),
      'allowEmpty' => false,
      'required' => true,
    ));
  }
}
