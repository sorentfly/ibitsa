<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_ServiceLocator
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_ServiceLocator_Backend_File extends Engine_ServiceLocator_Backend_Abstract
{
  // Properties
  
  /**
   * @var string
   */
  protected $_configDirectory;
  
  
  
  // General
  
  /**
   * Constructor
   * 
   * @param array $options 
   */
  public function __construct(array $options = null)
  {
    if( null !== $options ) {
      $this->setOptions($options);
    }
    
    $this->getConfigDirectory();
  }
  
  /**
   * Set options
   * 
   * @param array $options
   * @return Engine_ServiceLocator_Backend_File 
   */
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
  
  /**
   * Get the directory where the configuration files are located
   * 
   * @return string
   */
  public function getConfigDirectory()
  {
    if( null === $this->_configDirectory ) {
      throw new Engine_ServiceLocator_Exception('No configuration directory specified');
    }
    return $this->_configDirectory;
  }
  
  /**
   * Set the directory where the configuration files are located
   * 
   * @return Engine_ServiceLocator_Backend_File
   */
  public function setConfigDirectory($configDirectory)
  {
    if( !is_dir($configDirectory) ) {
      throw new Engine_ServiceLocator_Exception('Specified configuration directory does not exist');
    }
    $this->_configDirectory = $configDirectory;
    return $this;
  }

  /**
   * Get the resource configuration
   * 
   * @param string $type
   * @param string $profile
   * @return mixed
   */
  public function get($type, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    if( !isset($this->_data[$key]) ) {
      $this->_loadFile($type, $profile);
    }
    
    if( isset($this->_data[$key]) &&
        is_array($this->_data[$key]) ) {
      return $this->_data[$key];
    } else {
      return null;
    }
  }

  /**
   * Check if the resource configuration exists
   * 
   * @param string $type
   * @param string $profile
   * @return boolean 
   */
  public function has($type, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    if( !isset($this->_data[$key]) ) {
      $this->_loadFile($type, $profile);
    }
    
    return ( isset($this->_data[$key]) && is_array($this->_data[$key]) );
  }

  /**
   * Set the resource configuration
   * 
   * @param string $type
   * @param array $value
   * @param string $profile
   * @return Engine_ServiceLocator_Backend_File 
   */
  public function set($type, $value, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    $this->_data[$key] = $value;
    return $this;
  }
  
  
  
  // Utility
  
  /**
   * Load the configuration for one resource
   * 
   * @param string $type
   * @param string $profile
   * @return boolean
   */
  protected function _loadFile($type, $profile = null)
  {
    $key = $type . '-' . ( $profile ? $profile : 'default' );
    
    $file = $this->getConfigDirectory() . DIRECTORY_SEPARATOR . $key . '.php';
    
    if( file_exists($file) ) {
      $config = include $file;
    } else {
      $this->_data[$key] = false;
      return false;
    }
    
    if( !empty($config) && is_array($config) ) {
      $this->_data[$key] = $config;
      return true;
    } else {
      $this->_data[$key] = false;
      return false;
    }
  }
}
