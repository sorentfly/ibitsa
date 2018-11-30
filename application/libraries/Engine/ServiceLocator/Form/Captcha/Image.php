<?php

class Engine_ServiceLocator_Form_Captcha_Image extends Engine_ServiceLocator_Form_Abstract
{
  public function init()
  {
    parent::init();
    
    
    
    // Text
    
    $this->addElement('Text', 'wordLen', array(
      'label' => 'Word Length',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(4)),
      ),
      'value' => 6,
    ));
    
    $this->addElement('Text', 'font', array(
      'label' => 'Font File',
      'value' => 'application/modules/Core/externals/fonts/arial.ttf',
    ));
    
    $this->addElement('Text', 'fontSize', array(
      'label' => 'Font Size',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(10)),
      ),
      'value' => 30,
    ));
    
    
    
    // Image
    
    $this->addElement('Text', 'width', array(
      'label' => 'Image Width',
      //'description' => 'In pixels',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(100)),
      ),
      'value' => 200,
    ));
    
    $this->addElement('Text', 'height', array(
      'label' => 'Image Height',
      //'description' => 'In pixels',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(40)),
      ),
      'value' => 50,
    ));
    
    $this->addElement('Text', 'imgDir', array(
      'label' => 'Image Directory',
      'value' => 'public/temporary',
    ));
    
    $this->addElement('Text', 'imgUrl', array(
      'label' => 'Image URL',
      'value' => 'public/temporary',
    ));
    
    
    
    // Misc
    
    $this->addElement('Text', 'timeout', array(
      'label' => 'Timeout',
      'validators' => array(
        'Int',
        array('GreaterThan', true, array(30)),
      ),
      'value' => 300,
    ));
  }
}
