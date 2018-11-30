<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Mirrored.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_Mirrored extends Storage_Service_Abstract
{
  protected $_type = 'mirrored';

  protected $_services;

  protected $_mirrorImmediately = true;

  public function  __construct(array $config = array())
  {
    // Get services
    if( !empty($config['services']) ) {
      $this->_services = $config['services'];
    } else {
      $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
      $this->_services = $serviceTable->select()
        ->from($serviceTable, 'service_id')
        ->where('enabled = ?', true)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
    }
    // Do not allow self
    $this->_services = array_diff($this->_services, (array) $config['service_id']);
    // Whoops, no services
    if( empty($this->_services) ) {
      throw new Storage_Service_Exception('No services available.');
    }

    parent::__construct($config);
  }

  public function getType()
  {
    return $this->_type;
  }




  /**
   * Returns a url that allows for external access to the file. May point to some
   * adapter which then retrieves the file and outputs it, if desirable
   *
   * @param Storage_Model_DbRow_File The file for operation
   * @return string
   */
  public function map(Storage_Model_File $model)
  {
    // Get service from mirrors
    $mirrors = $this->_getFileMirrors($model);
    $index = array_rand($mirrors);
    $service_id = $mirrors[$index];
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $otherService = $serviceTable->getService($service_id);

    return $otherService->map($model);
  }

  /**
   * Stores a local file in the storage service
   *
   * @param Zend_Form_Element_File|array|string $file Temporary local file to store
   * @param array $params Contains iden
   * @return string Storage type specific path (internal use only)
   */
  public function store(Storage_Model_File $model, $file)
  {
    if( count($this->_services) <= 0 ) {
      throw new Storage_Service_Exception('Unable to get storage service.');
    }

    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $mirrorsTable = Engine_Api::_()->getDbtable('mirrors', 'storage');
    $path = null;
    
    if( $this->_mirrorImmediately ) {

      foreach( $this->_services as $service_id ) {
        $otherService = $serviceTable->getService($service_id);
        $path = $otherService->store($model, $file);

        // Save to mirrors
        $mirrorsTable->insert(array(
          'file_id' => $model->file_id,
          'service_id' => $service_id,
        ));
      }
      
    } else {

      do {
        $index = $this->_getCounter() % count($this->_services);
      } while( !isset($this->_services[$index]) );
      $service_id = $this->_services[$index];

      // Get the other serice
      $otherService = $serviceTable->getService($service_id);

      $path = $otherService->store($model, $file);

      // Save to mirrors
      $mirrorsTable->insert(array(
        'file_id' => $model->file_id,
        'service_id' => $service_id,
      ));
      
    }
    
    return $path;
  }

  /**
   * Returns the content of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param array $params
   */
  public function read(Storage_Model_File $model)
  {
    // Get service from mirrors
    $mirrors = $this->_getFileMirrors($model);
    $index = array_rand($mirrors);
    $service_id = $mirrors[$index];
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $otherService = $serviceTable->getService($service_id);

    return $otherService->read($model);
  }

  /**
   * Creates a new file from data rather than an existing file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param string $data
   */
  public function write(Storage_Model_File $model, $data)
  {
    if( count($this->_services) <= 0 ) {
      throw new Storage_Service_Exception('Unable to get storage service.');
    }

    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $mirrorsTable = Engine_Api::_()->getDbtable('mirrors', 'storage');
    $path = null;

    if( $this->_mirrorImmediately ) {

      foreach( $this->_services as $service_id ) {
        $otherService = $serviceTable->getService($service_id);
        $path = $otherService->store($model, $file);

        // Save to mirrors
        $mirrorsTable->insert(array(
          'file_id' => $model->file_id,
          'service_id' => $service_id,
        ));
      }

    } else {

      do {
        $index = $this->_getCounter() % count($this->_services);
      } while( !isset($this->_services[$index]) );
      $service_id = $this->_services[$index];

      // Get the other serice
      $otherService = $serviceTable->getService($service_id);

      $path = $otherService->write($model, $data);

      // Save to mirrors
      $mirrorsTable->insert(array(
        'file_id' => $model->file_id,
        'service_id' => $service_id,
      ));

    }

    return $path;
  }

  /**
   * Removes the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function remove(Storage_Model_File $model)
  {
    if( !empty($model->storage_path) ) {
      $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
      $mirrors = $this->_getFileMirrors($model);
      foreach( $mirrors as $mirrorServiceId ) {
        $otherService = $serviceTable->getService($mirrorServiceId);
        $otherService->remove($model);
      }
    }
  }

  /**
   * Creates a local temporary local copy of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function temporary(Storage_Model_File $model)
  {
    // Get service from mirrors
    $mirrors = $this->_getFileMirrors($model);
    $index = array_rand($mirrors);
    $service_id = $mirrors[$index];
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $otherService = $serviceTable->getService($service_id);

    return $otherService->temporary($model);
  }

  public function removeFile($path)
  {
    $serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
    $mirrors = $this->_getFileMirrors($model);
    foreach( $mirrors as $mirrorServiceId ) {
      $otherService = $serviceTable->getService($mirrorServiceId);
      $otherService->removeFile($path);
    }
  }




  // Utility

  protected function _getCounter($increment = true)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $counter = $settings->getSetting('storage.service.mirrored.counter', 0);
    if( $increment ) {
      $counter++;
      $settings->setSetting('storage.service.mirrored.counter', $counter);
    }
    return $counter;
  }
  
  protected function _getFileMirrors(Storage_Model_File $file)
  {
    $mirrorsTable = Engine_Api::_()->getDbtable('mirrors', 'storage');
    return $mirrorsTable->select()
      ->from($mirrorsTable, 'service_id')
      ->where('file_id = ?', $file->file_id)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
  }
}