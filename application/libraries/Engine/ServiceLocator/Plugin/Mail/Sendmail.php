<?php

class Engine_ServiceLocator_Plugin_Mail_Sendmail extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Standard';
  
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
      $mailTransport = new Zend_Mail_Transport_Sendmail();
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
      'class' => 'Zend_Mail_Transport_Sendmail',
      'options' => null, //$data,
    );
  }
}
