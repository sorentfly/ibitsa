<?php

class Engine_ServiceLocator_Plugin_Captcha_Image extends Engine_ServiceLocator_Plugin_Abstract
{
  protected $_formClass = 'Engine_ServiceLocator_Form_Captcha_Image';
  
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
      $captcha = new Zend_Captcha_Image($data);
      $captcha->render();
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
      'class' => 'Zend_Captcha_Image',
      'options' => $data,
    );
  }
}
