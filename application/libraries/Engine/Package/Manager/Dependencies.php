<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Dependencies.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager_Dependencies
{
  protected $_package;

  protected $_packageGuid;

  protected $_packageKey;

  protected $_dependencies;

  protected $_selected;

  //protected $_dependecyPackages;
  
  public function __construct(Engine_Package_Manifest_Entity_Package $package, $selected = false)
  {
    //$this->_package = $package;
    $this->_packageGuid = $package->getGuid();
    $this->_packageKey = $package->getKey();
    $this->_selected = (bool) $selected;
  }

  public function __sleep()
  {
    return array('_packageGuid', '_packageKey', '_dependencies', '_selected',
      /* , '_package', '_dependecyPackages' */);
  }



  // Package

  /*
  public function setPackage(Engine_Package_Manifest_Entity_Package $package)
  {
    if( null === $this->_package &&
        null !== $this->_packageKey &&
        $package->getKey() === $this->_packageKey ) {
      $this->_package = $package;
    }
    return $this;
  }

  public function getPackage()
  {
    if( null === $this->_package ) {
      throw new Engine_PAckage_Manager_Exception('No package key in dependencies collection');
    }
    return $this->_package;
  }
   * 
   */

  public function getPackageKey()
  {
    return $this->_packageKey;
  }
  


  // Dependencies

  public function addDependency($dependency)
  {
    if( !($dependency instanceof Engine_Package_Manifest_Entity_Dependency) ) {
      $dependency = new Engine_Package_Manifest_Entity_Dependency($dependency);
    }
    $dependency->setSelected($this->_selected);
    
    $this->_dependencies[$dependency->getGuid()] = $dependency;

    return $this;
  }

  public function addDependencies(array $dependencies = null)
  {
    foreach( $dependencies as $dependency ) {
      $this->addDependency($dependency);
    }
    return $this;
  }

  public function clearDependencies()
  {
    $this->_dependencies = array();
    return $this;
  }

  public function getDependency($package)
  {
    $guid = null;
    if( is_string($package) ) {
      $guid = $package;
    } else if( $package instanceof Engine_Package_Manifest_Entity_Package ) {
      $guid = $package->getGuid();
    } else {
      return false; // throw?
    }

    if( isset($this->_dependencies[$guid]) ) {
      return $this->_dependencies[$guid];
    }

    return null;
  }

  public function getDependencies()
  {
    return $this->_dependencies;
  }

  public function setDependency($dependency)
  {
    $this->addDependency($dependency);
    return $this;
  }

  public function setDependencies(array $dependencies = null)
  {
    $this->addDependencies($dependencies);
    return $this;
  }



  // Comparison

  public function compare($package, $selected = false)
  {
    foreach( (array) $this->getDependencies() as $dependency ) {
      // Cannot have dependency on self
      if( $dependency->getGuid() == $this->_packageGuid ) {
        continue;
      }
      // Don't check if the dependency doesn't match?
      if( $dependency->getGuid() != $package->getGuid() ) {
        continue;
      }
      // Compare
      $dependency->compare($package, $selected);
    }
    return $this;
  }

  public function hasErrors()
  {
    $hasErrors = false;
    //$excludedPackages = array();
    foreach( $this->_dependencies as $dependency ) {
      $status = $dependency->getStatus();
      if( $status != Engine_Package_Manifest_Entity_Dependency::OKAY ) {
        //if( $status == Engine_Package_Manifest_Entity_Dependency::HAS_EXCLUDED ) {
        //  $excludedPackages[] = $dependency->getGuid();
        //} else {
          $hasErrors = true;
        //}
      }
    }
    return $hasErrors;
  }
}