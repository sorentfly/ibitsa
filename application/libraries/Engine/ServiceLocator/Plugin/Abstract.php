<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_ServiceLocator_Plugin_Abstract
{
  protected $_data;
  
  protected $_form;
  
  protected $_formClass;
  
  protected $_view;
  
  
  
  // General
  
  public function __construct(array $options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }
  
  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }
  }
  
  
  
  // Options
  
  public function getFormClass()
  {
    return $this->_formClass;
  }
  
  public function setFormClass($formClass)
  {
    if( is_string($formClass) ) {
      $this->_formClass = $formClass;
    }
    return $this;
  }
  
  public function getForm()
  {
    if( null === $this->_form ) {
      if( null === $this->_formClass ) {
        throw new Engine_ServiceLocator_Exception('No form class');
      }
      $class = $this->_formClass;
      if( !class_exists($class, true) ) {
        throw new Engine_ServiceLocator_Exception('No form class');
      }
      $this->_form = new $class();
    }
    return $this->_form;
  }
  
  public function setForm(Zend_Form $form)
  {
    $this->_form = $form;
    $this->_formClass = get_class($form);
    return $this;
  }
  
  public function getView()
  {
    if( null === $this->_view ) {
      throw new Engine_ServiceLocator_Exception('No view');
    }
    return $this->_view;
  }
  
  public function setView(Zend_View_Interface $view)
  {
    $this->_view = $view;
    return $this;
  }
  
  
  
  // Events
  
  public function onInit()
  {
    
  }
  
  public function onView(array $config = array())
  {
    // Nothing
  }
  
  public function onSubmit(array $data = array())
  {
    $this->_data = $data;
  }
  
  public function onTest()
  {
    return true;
  }
  
  abstract public function onProcess();
}
