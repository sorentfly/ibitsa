<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: S3.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_S3 extends Storage_Service_Abstract
{
  // General

  protected $_type = 's3';

  protected $_path;

  protected $_baseUrl;

  /**
   * @var Zend_Service_Amazon_S3
   */
  protected $_internalService;

  protected $_bucket;

  protected $_streamWrapperName;

  public function __construct(array $config)
  {
    if( empty($config['bucket']) ) {
      throw new Storage_Service_Exception('No bucket specified');
    }
    $this->_bucket = $config['bucket'];
    $this->_internalService = new Zend_Service_Amazon_S3(
        $config['accessKey'],
        $config['secretKey'],
        $config['region']
    );
    
    if( !empty($config['path']) ) {
      $this->_path = $config['path'];
    } else {
      $this->_path = 'public';
    }

    if( !empty($config['baseUrl']) ) {
      $this->_baseUrl = $config['baseUrl'];
      unset($config['baseUrl']);
      // Add http:// if no protocol
      if( false === strpos($this->_baseUrl, '://') ) {
        $this->_baseUrl = 'http://' . $this->_baseUrl;
      }
    }

    // Should we register the stream wrapper?
    $this->_streamWrapperName = 's3' . (int) @$config['service_id'];
    $this->_internalService->registerStreamWrapper($this->_streamWrapperName);
    
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
    // Remove bucket from storage path? (b/c)
    $path = $model->storage_path;
    if( substr($path, 0, strlen($this->_bucket) + 1) == $this->_bucket . '/' ) {
      $path = ltrim(substr($path, strlen($this->_bucket) + 1), '/');
    }

    // Make url
    if( !$this->_baseUrl ) {
      // Map to S3 bucket directly
      return 'http://' . $this->_bucket . '.s3.amazonaws.com/' . $path;
    } else {
      // Map to baseUrl (cloudfront)
      return rtrim($this->_baseUrl, '/') . '/' . $path;
    }
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
    $path = $this->getScheme()->generate($model->toArray());

    // Prefix path with bucket?
    //$path = $this->_bucket . '/' . $path;
    
    // Copy file
    try {
      $return = $this->_internalService->putFile($file, $this->_bucket . '/' . $path, array(
        Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
        'Cache-Control' => 'max-age=864000, public',
      ));
      if( !$return ) {
        throw new Storage_Service_Exception('Unable to store file.');
      }
    } catch( Exception $e ) {
      throw $e;
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
    $path = $this->_bucket . '/' . $model->storage_path;

    try {
      $response = $this->_internalService->getObject($path);
      if( !$response ) {
        throw new Storage_Service_Exception('Unable to write file.');
      }
    } catch( Exception $e ) {
      throw $e;
    }

    return $response;
  }

  /**
   * Creates a new file from data rather than an existing file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param string $data
   */
  public function write(Storage_Model_File $model, $data)
  {
    $path = $this->getScheme()->generate($model->toArray());

    // Prefix path with bucket?
    //$path = $this->_bucket . '/' . $path;

    // Copy file
    try {
      $return = $this->_internalService->putObject($this->_bucket . '/' . $file, $data, array(
        Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
        'Cache-Control' => 'max-age=864000, public',
      ));
      if( !$return ) {
        throw new Storage_Service_Exception('Unable to write file.');
      }
    } catch( Exception $e ) {
      throw $e;
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
      $path = $this->_bucket . '/' . $model->storage_path;
      try {
        $return = $this->_internalService->removeObject($path);
        if( !$return ) {
          throw new Storage_Service_Exception('Unable to remove file.');
        }
      } catch( Exception $e ) {
        throw $e;
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
    if( substr($model->storage_path, 0, strlen($this->_bucket)) == $this->_bucket ) {
      $path = $model->storage_path;
    } else {
      $path = $this->_bucket . '/' . $model->storage_path;
    }
    
    try {
      $rfh = fopen($this->_streamWrapperName . '://' . $path, 'r');
    } catch( Exception $e ) {
      throw $e;
    }
    
    $tmp_file = APPLICATION_PATH . '/public/temporary/' . basename($model['storage_path']);
    $fp = fopen($tmp_file, "w");
    stream_copy_to_stream($rfh, $fp);
    fclose($fp);
    @chmod($tmp_file, 0777);
    return $tmp_file;
  }
  
  public function removeFile($path)
  {
    // Should we add bucket here?
    $path = $this->_bucket . '/' . $path;
    $this->_internalService->removeObject($path);
  }
}