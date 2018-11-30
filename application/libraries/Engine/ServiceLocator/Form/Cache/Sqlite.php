<?php

class Engine_ServiceLocator_Form_Cache_Sqlite extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'cache_db_complete_path', array(
      'label' => 'SQLite Database File',
      'required' => true,
      'allowEmpty' => false,
      'value' => '',
    ));
  }
}
