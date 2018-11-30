<?php

class Engine_ServiceLocator_Plugin_Cache_Memcache extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Cache_Memcache';
  
  public function onView(array $config = array())
  {
    parent::onView();
    
    if( !empty($config['options']) && 
        is_array($config['options']) && 
        !empty($config['options']['servers']) ) {
      $this->getForm()->populate(array(
        'host' => $config['options']['servers']['host'],
        'port' => $config['options']['servers']['port'],
      ));
    }
  }
  
  public function onSubmit(array $data = array())
  {
    unset($data['enabled']);
    
    $data['servers'] = array(
      'host' => $data['host'],
      'port' => $data['port'],
    );
    unset($data['host']);
    unset($data['port']);
    
    
    try {
      $cache = new Zend_Cache_Backend_Memcached($data);
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
      'class' => 'Zend_Cache_Backend_Memcached',
      'options' => $data,
    );
  }
}
