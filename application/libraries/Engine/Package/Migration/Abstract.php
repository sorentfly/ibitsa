<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Package_Migration_Abstract
{
  protected $_database;
  
  protected $_migrationTable = 'engine4_core_migrations';
  
  protected $_package;
  
  protected $_revision;
  
  /**
   * Set options
   * 
   * @param array $options
   * @return self
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

  public function setDatabase(Zend_Db_Adapter_Abstract $db = null)
  {
    if( null !== $this->_database ) {
      throw new Engine_Package_Installer_Exception('Database already set');
    }
    $this->_database = $db;
    return $this;
  }

  /**
   * @return Zend_Db_Adapter_Abstract
   */
  public function getDatabase()
  {
    if( null === $this->_database ) {
      throw new Engine_Package_Installer_Exception('Database not set');
    }
    return $this->_database;
  }
  
  public function getRevision()
  {
    if( null === $this->_revision ) {
      throw new Engine_Package_Migration_Exception('Revision must not be empty');
    }
    return $this->_revision;
  }
  
  public function getPackage()
  {
    if( null === $this->_package ) {
      throw new Engine_Package_Migration_Exception('Package must not be empty');
    }
    return $this->_package;
  }
  
  
  
  // Main
  
  public function up()
  {
    try {
      $this->_up();
      $this->_markUp();
    } catch( Exception $e ) {
      throw $e;
    }
    
    return $this;
  }
  
  public function down()
  {
    try {
      $this->_down();
      $this->_markDown();
    } catch( Exception $e ) {
      throw $e;
    }
    
    return $this;
  }
  
  
  
  // Abstract
  
  abstract protected function _up();
  
  abstract protected function _down();
  
  
  
  // Utility
  
  protected function _markUp()
  {
    $count = $this->getDatabase()->update($this->_migrationTable, array(
      'current' => $this->getRevision(),
    ), array(
      'package = ?' => $this->getPackage(),
    ));
    
    if( !$count ) {
      $this->getDatabase()->insert($this->_migrationTable, array(
        'package = ?' => $this->getPackage(),
        'current' => $this->getRevision(),
      ));
    }
  }
  
  protected function _markDown()
  {
    $count = $this->getDatabase()->update($this->_migrationTable, array(
      'current' => $this->getRevision() - 1, // Please don't skip revisions
    ), array(
      'package = ?' => $this->getPackage(),
    ));
    
    if( !$count ) {
      $this->getDatabase()->insert($this->_migrationTable, array(
        'package' => $this->getPackage(),
        'current' => $this->getRevision() - 1, // Please don't skip revisions
      ));
    }
  }
}
