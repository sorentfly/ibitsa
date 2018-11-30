<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Interface.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
interface Storage_Service_Interface
{
  /**
   * Returns a string that identifies the storage type
   *
   * @return string
   */
  public function getIdentity();
  
  /**
   * Returns a string that identifies the storage type
   *
   * @return string
   */
  public function getType();

  /**
   * Returns a url that allows for external access to the file. May point to some
   * adapter which then retrieves the file and outputs it, if desirable
   *
   * @param Storage_Model_DbRow_File The file for operation
   * @return string
   */
  public function map(Storage_Model_File $model);

  /**
   * Stores a local file in the storage service
   *
   * @param Zend_Form_Element_File|array|string $file Temporary local file to store
   * @param array $params Contains iden
   * @return string Storage type specific path (internal use only)
   */
  public function store(Storage_Model_File $model, $file);

  /**
   * Returns the content of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param array $params
   */
  public function read(Storage_Model_File $model);

  /**
   * Creates a new file from data rather than an existing file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param string $data
   */
  public function write(Storage_Model_File $model, $data);

  /**
   * Removes the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function remove(Storage_Model_File $model);

  /**
   * Removes a file
   *
   * @param string $path The file for operation
   */
  public function removeFile($path);

  /**
   * Creates a local temporary local copy of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function temporary(Storage_Model_File $model);

  /**
   * Get the naming scheme object
   *
   * @return Storage_Service_Scheme_Interface
   */
  public function getScheme();

  /**
   * Sets the naming scheme object
   *
   * @param Storage_Service_Scheme_Interface $scheme
   * @return Storage_Service_Interface
   */
  public function setScheme(Storage_Service_Scheme_Interface $scheme);
}