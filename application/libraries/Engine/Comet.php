<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Comet
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Comet.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Comet
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Comet
{
  /**
   * @var Engine_Comet_Backend_Abstract
   */
  protected $_backend;

  /**
   * @var Engine_Comet_Frontend_Abstract
   */
  protected $_frontent;
  
  public function __construct($options = array())
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }

    // Check options
    if( null === $this->_backend ) {
      throw new Engine_Comet_Exeption('No backend configured.');
    }
    if( null === $this->_frontend ) {
      throw new Engine_Comet_Exeption('No frontend configured.');
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
    return $this;
  }

  public function setBackend($backend)
  {
    if( is_array($backend) ) {
      $backend = $this->_loadBackend($backend);
    } else if( !($backend instanceof Engine_Comet_Backend_Abstract) ) {
      throw new Engine_Comet_Exception(sprintf('Invalid type passed to %s : %s', __METHOD__, gettype($backend)));
    }
    $this->_backend = $backend;
    return $this;
  }

  public function setFrontend($frontend)
  {
    if( is_array($frontend) ) {
      $frontend = $this->_loadFrontend($frontend);
    } else if( !($frontend instanceof Engine_Comet_Frontend_Abstract) ) {
      throw new Engine_Comet_Exception(sprintf('Invalid type passed to %s : %s', __METHOD__, gettype($frontend)));
    }
    $this->_frontend = $frontend;
    return $this;
  }



  // Utility
  
  protected function _loadBackend(array $options)
  {
    if( !isset($options['adapter']) || !is_string($options['adapter']) ) {
      throw new Engine_Comet_Exception('Adapter not set or not string.');
    }
    $adapter = $options['adapter'];
    unset($options['adapter']);
    $class = 'Engine_Comet_Backend_' . ucfirst($adapter);
    Engine_Loader::loadClass($class);
    if( !is_subclass_of($class, 'Engine_Comet_Backend_Abstract') ) {
      throw new Engine_Comet_Exception(sprintf('Adapter %s does not extend Engine_Comet_Backend_Abstract', $class));
    }
    return new $class($options);
  }

  protected function _loadFrontend(array $options)
  {
    if( !isset($options['adapter']) || !is_string($options['adapter']) ) {
      throw new Engine_Comet_Exception('Adapter not set or not string.');
    }
    $adapter = $options['adapter'];
    unset($options['adapter']);
    $class = 'Engine_Comet_Frontend_' . ucfirst($adapter);
    Engine_Loader::loadClass($class);
    if( !is_subclass_of($class, 'Engine_Comet_Frontend_Abstract') ) {
      throw new Engine_Comet_Exception(sprintf('Adapter %s does not extend Engine_Comet_Frontend_Abstract', $class));
    }
    return new $class($options);
  }
};
