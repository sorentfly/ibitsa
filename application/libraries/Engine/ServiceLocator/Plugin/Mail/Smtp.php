<?php

class Engine_ServiceLocator_Plugin_Mail_Smtp extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Mail_Stmp';
  
  public function onView(array $config = array())
  {
    parent::onView();
    
    if( !empty($config['options']) ) {
      $this->getForm()->populate($config['options']);
    }
  }
  
  public function onSubmit(array $data = array())
  {
    unset($data['enabled']);
    
    try {
      $host = !empty($data['host']) ? $data['host'] : '127.0.0.1';
      $mailTransport = new Zend_Mail_Transport_Smtp($host, $data);
      $this->_data = $data;
      return true;
    } catch( Exception $e ) {
      $this->getForm()->addError('Test failed: ' . $e->getMessage());
      return false;
    }
  }
  
  public function onProcess()
  {
    $data = array_filter($this->_data);
    $host = !empty($data['host']) ? $data['host'] : '127.0.0.1';
    unset($data['host']);
    if( empty($data) ) {
      $args = array($host);
    } else {
      $args = array($host, $data);
    }
    return array(
      'class' => 'Zend_Mail_Transport_Smtp',
      'args' => $args,
    );
  }
}
