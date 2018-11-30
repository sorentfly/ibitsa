<?php

abstract class Engine_File_Archive
{
  protected $_filename;
  
  final public function __construct($filename, array $options = null)
  {
    $this->_filename = $filename;
    if( is_array($options) ) {
      $this->setOptions($options);
    }
    $this->init();
  }

  final public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }
    return $this;
  }

  public function init()
  {
    
  }



  // Generic
  
  abstract public function insert($source);

  abstract public function extract($target);
}