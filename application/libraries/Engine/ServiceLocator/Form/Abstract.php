<?php

class Engine_ServiceLocator_Form_Abstract extends Engine_Form
{
  public function init()
  {
    
    $this->addElement('Radio', 'enabled', array(
      'label' => 'Enabled?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => '0',
      'order' => 9990,
    ));
    
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'order' => 9991,
    ));
  }
}
