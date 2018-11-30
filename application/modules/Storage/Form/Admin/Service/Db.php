<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Db.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Admin_Service_Db extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    $this->setDescription('You may leave all fields below blank to use the ' .
        'default SocialEngine database.');
    
    // Element: adapter
//    $this->addElement('Text', 'adapter', array(
//      'label' => 'Database Adapter',
//    ));

    // Element: host
    $this->addElement('Text', 'host', array(
      'label' => 'Database Host',
    ));

    // Element: username
    $this->addElement('Text', 'username', array(
      'label' => 'Database Username',
    ));

    // Element: password
    $this->addElement('Text', 'password', array(
      'label' => 'Database Password',
    ));

    // Element: database
    $this->addElement('Text', 'dbname', array(
      'label' => 'Database Name',
    ));
    
    parent::init();
  }

  public function isValid($data)
  {
    $valid = parent::isValid($data);

    // Custom valid
    if( $valid ) {
      // Check auth
      $config = $data;
      if( /* !empty($config['adapter']) && */
          !empty($config['host']) &&
          !empty($config['username']) &&
          !empty($config['password']) ) {
        try {
          $defaultAdapter = Engine_Api::_()->getDbtable('chunks', 'storage')->getAdapter();
          $adapterName = strtolower( !empty($config['adapter']) ? $config['adapter'] : array_pop(explode('_', get_class($defaultAdapter))) );
          $adapterNamespace = ( $adapterName == 'mysql' ? 'Engine_Db_Adapter' : 'Zend_Db_Adapter' );
          $adapter = Zend_Db::factory($adapterName, array(
            'host' => $config['host'],
            'username' => $config['username'],
            'password' => $config['password'],
            'dbname' => $config['dbname'],
            'adapterNamespace' => $adapterNamespace,
          ));
          $table = new Storage_Model_DbTable_Chunks(array(
            'adapter' => $adapter,
            //'name' => 'engine4_storage_chunks',
          ));
          $table->info();
        } catch( Exception $e ) {
          $this->addError('Could not connect to database.');
          $this->addError($e->getMessage());
          return false;
        }
      }
    }

    return $valid;
  }
}