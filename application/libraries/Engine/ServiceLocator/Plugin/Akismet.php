<?php

class Engine_ServiceLocator_Plugin_Akismet extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Akismet';
  
  public function onView(array $config = array())
  {
    parent::onView();
    
    if( !empty($config['args']) ) {
      $this->getForm()->populate(array(
        'key' => $config['args'][0],
        'url' => $config['args'][1],
      ));
    }
  }
  
  public function onSubmit(array $data = array())
  {
    unset($data['enabled']);
    
    $apiKey = @$data['key'];
    $blog = @$data['url'];
    
    try {
      $akismet = new Zend_Service_Akismet($apiKey, $blog);
      $akismet->verifyKey();
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
      'class' => 'Zend_Service_Akismet',
      'args' => array(
        $data['key'],
        $data['url']
      )
    );
  }
}
