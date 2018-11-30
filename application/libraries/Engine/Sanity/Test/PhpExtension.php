<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhpExtension.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_PhpExtension extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'noExtension' => 'The PHP extension %extension% is missing.',
    'tooLowVersion' => 'Requires at least version %min_version%',
    'tooHighVersion' => 'Requires no greater than %max_version%',
  );

  protected $_messageVariables = array(
    'extension' => '_extension',
    'min_version' => '_minVersion',
    'max_version' => '_maxVersion',
    'has_extension' => '_hasExtension',
    'extension_version' => '_extensionVersion',
  );
  
  protected $_extension;

  protected $_minVersion;

  protected $_maxVersion;

  protected $_hasExtension;

  protected $_extensionVersion;

  public function setExtension($extension)
  {
    $this->_extension = $extension;
    return $this;
  }

  public function getExtension()
  {
    return $this->_extension;
  }

  public function setMinVersion($minVersion)
  {
    $this->_minVersion = $minVersion;
    return $this;
  }

  public function getMinVersion()
  {
    return $this->_minVersion;
  }

  public function setMaxVersion($maxVersion)
  {
    $this->_maxVersion = $maxVersion;
    return $this;
  }

  public function getMaxVersion()
  {
    return $this->_maxVersion;
  }

  public function execute()
  {
    $extension = $this->getExtension();

    if( !empty($extension) ) {
      $method = '_execute_' . strtolower($extension);
      if( method_exists($this, $method) ) {
        $this->$method();
      } else {
        $this->_execute();
      }
    }
    
    return $this;
  }



  // Custom handlers

  protected function _execute()
  {
    $extension = $this->getExtension();
    $minVersion = $this->getMinVersion();
    $maxVersion = $this->getMaxVersion();
    
    $this->_hasExtension = $hasExtension = extension_loaded($extension);
    $extensionVersion = null;
    if( $hasExtension ) {
      $extensionVersion = phpversion($extension);
    }
    $this->_extensionVersion = $extensionVersion;

    // Tests
    if( !empty($extension) && !$hasExtension ) {
      return $this->_error('noExtension');
    }

    if( !empty($minVersion) && version_compare($extensionVersion, $minVersion, '<') ) {
      $this->_error('tooLowVersion');
    }

    if( !empty($maxVersion) && version_compare($extensionVersion, $maxVersion, '>') ) {
      $this->_error('tooHighVersion');
    }
  }

  protected function _execute_gd()
  {
    $minVersion = $this->getMinVersion();
    $maxVersion = $this->getMaxVersion();

    // Tests
    if( !function_exists('gd_info') ) {
      $this->_hasExtension = false;
      return $this->_error('noExtension');
    }

    $gd_info = gd_info();
    $current_version = $gd_info['GD Version'];
    preg_match('/[0-9.]+/', $current_version, $version_matches);
    $this->_extensionVersion = $extensionVersion = $version_matches[0];
    
    if( !empty($minVersion) && version_compare($extensionVersion, $minVersion, '<') ) {
      $this->_error('tooLowVersion');
    }

    if( !empty($maxVersion) && version_compare($extensionVersion, $maxVersion, '>') ) {
      $this->_error('tooHighVersion');
    }
  }
}