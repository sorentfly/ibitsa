<?php

class Engine_ServiceLocator_Plugin_Image_Imagick extends Engine_ServiceLocator_Plugin_Abstract
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
      $image = new Engine_Image_Adapter_Imagick();
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
      'class' => 'Engine_Image_Adapter_Imagick',
      'options' => $data,
    );
  }
}
