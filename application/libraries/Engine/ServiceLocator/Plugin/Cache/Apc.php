<?php

class Engine_ServiceLocator_Plugin_Cache_Apc extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Cache_Standard';
  
  public function onView(array $config = array())
  {
    parent::onView();
    
    if( !empty($config['options']) && is_array($config['options']) ) {
      $this->getForm()->populate($config['options']);
    }
  }
  
  public function onSubmit(array $data = array())
  {
    unset($data['enabled']);
    
    try {
      $cache = new Zend_Cache_Backend_Apc($data);
      $this->_data = $data;
      return true;
    } catch( Exception $e ) {
      $this->getForm()->addError('Test failed: ' . $e->getMessage());
      return false;
    }
  }
  
  public function onProcess()
  {
    $data = $this->_data;
    return array(
      'class' => 'Zend_Cache_Backend_Apc',
      'options' => $data,
    );
  }
}
