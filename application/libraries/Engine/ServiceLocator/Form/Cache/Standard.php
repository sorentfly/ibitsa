<?php

class Engine_ServiceLocator_Form_Cache_Standard extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    $this->addElement('Text', 'lifetime', array(
      'label' => 'Default Lifetime',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(10)),
      ),
      'value' => 600,
      'order' => 9980,
    ));
  }
}
