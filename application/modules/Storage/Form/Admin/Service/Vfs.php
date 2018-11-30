<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Vfs.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Admin_Service_Vfs extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: adapter
    $this->addElement('Select', 'adapter', array(
      'label' => 'VFS Adapter',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        'ftp' => 'FTP',
        'ssh' => 'SSH/SCP',
      )
    ));

    // Element: host
    $this->addElement('Text', 'params_host', array(
      'label' => 'Remote Host',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: username
    $this->addElement('Text', 'params_username', array(
      'label' => 'Username',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: password
    $this->addElement('Text', 'params_password', array(
      'label' => 'Password',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: path
    $this->addElement('Text', 'params_path', array(
      'label' => 'Path',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: baseUrl
    $this->addElement('Text', 'baseUrl', array(
      'label' => 'Base URL',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    parent::init();
  }

  public function isValid($data)
  {
    $valid = parent::isValid($data);

    // Custom valid
    if( $valid ) {
      $params = array();
      foreach( $data as $key => $value ) {
        if( false !== strpos($key, '_') ) {
          list($p, $c) = explode('_', $key, 2);
          if( $p == 'params' ) {
            $params[$c] = $value;
          }
        }
      }

      try {
        $vfs = Engine_Vfs::factory($data['adapter'], $params);
        $vfs->getSystemType(); // Used to test connection
      } catch( Exception $e ) {
        $this->addError('Could not create VFS connection. Error was:');
        $this->addError($e->getMessage());
        return false;
      }
    }

    return $valid;
  }
}