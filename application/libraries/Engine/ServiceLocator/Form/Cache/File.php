<?php

class Engine_ServiceLocator_Form_Cache_File extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'cache_dir', array(
      'label' => 'Cache Directory',
      'value' => 'temporary/cache',
    ));
    
    $this->addElement('Checkbox', 'file_locking', array(
      'label' => 'File Locking?',
      'value' => '1',
    ));
    
    $this->addElement('Text', 'file_name_prefix', array(
      'label' => 'File Name Prefix',
      'value' => 'zend_cache',
    ));
  }
}
