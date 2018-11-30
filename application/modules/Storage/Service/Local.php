<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Local.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_Local extends Storage_Service_Abstract
{
  // General

  protected $_type = 'local';
  
  protected $_path;
  
  protected $_baseUrl;

  public function __construct(array $config)
  {
    if( !empty($config['path']) ) {
      $this->_path = $config['path'];
    } else {
      $this->_path = 'public';
    }
    
    if( !empty($config['baseUrl']) ) {
      $this->_baseUrl = $config['baseUrl'];
    }

    parent::__construct($config);
  }

  public function getType()
  {
    return $this->_type;
  }
  
  public function getBaseUrl()
  {
    if( null === $this->_baseUrl ) {
      $this->_baseUrl = $this->_removeScriptName(Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    return $this->_baseUrl;
  }

    
  public function map(Storage_Model_File $file)
  {
    return rtrim($this->getBaseUrl(), '/') . '/' . $file->storage_path;
  }

  public function store(Storage_Model_File $model, $file)
  {
    $path = str_replace([',','&','*', '#','?', ' '], '_', $this->getScheme()->generate($model->toArray()) );
    //die($path);
    // Copy file
    try
    {
      $this->_mkdir(dirname(APPLICATION_PATH . DS . $path));
      $this->_copy($file, APPLICATION_PATH . DS . $path);
      @chmod(APPLICATION_PATH . DS . $path, 0777);
    }

    catch( Exception $e )
    {
      @unlink(APPLICATION_PATH . DS . $path);
      throw $e;
    }
    
    return $path;
  }

  public function read(Storage_Model_File $model)
  {
      if (defined('ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa') && defined('OLDFILE_LIMIT_DATE')
          && ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa
          && strtotime($model->creation_date)  <= strtotime(OLDFILE_LIMIT_DATE)
      ){
          $file = 'http:' . ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa . '/'. $model->storage_path;
      }else{
          $file = APPLICATION_PATH . '/' . $model->storage_path;
      }
    return @file_get_contents($file);
  }
  
  public function write(Storage_Model_File $model, $data)
  {
    // Write data
    $path = $this->getScheme()->generate($model->toArray());

    try
    {
      $this->_mkdir(dirname(APPLICATION_PATH . DS . $path));
      $this->_write(APPLICATION_PATH . DS . $path, $data);
      @chmod($path, 0777);
    }

    catch( Exception $e )
    {
      @unlink(APPLICATION_PATH . DS . $path);
      throw $e;
    }

    return $path;
  }
  
  public function remove(Storage_Model_File $model)
  {
    if( !empty($model->storage_path) )
    {
      $this->_delete(APPLICATION_PATH . DS . $model->storage_path);
    }
  }

  public function temporary(Storage_Model_File $model)
  {
    if (defined('ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa') && defined('OLDFILE_LIMIT_DATE')
          && ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa
          && strtotime($model->creation_date)  <= strtotime(OLDFILE_LIMIT_DATE)
    ){
        try {
            $rfh = fopen('http:' . ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa . '/'. $model->storage_path, 'r');
        } catch( Exception $e ) {
            throw $e;
        }
        $tmp_file = APPLICATION_PATH . '/public/temporary/' . basename($model->storage_path);
        $fp = fopen($tmp_file, "w");
        stream_copy_to_stream($rfh, $fp);
        fclose($fp);
        fclose($rfh);
        @chmod($tmp_file, 0777);
        return $tmp_file;
    }

    $file = APPLICATION_PATH . DS . $model->storage_path;
    $tmp_file = APPLICATION_PATH . '/public/temporary/'.basename($model['storage_path']);
    $this->_copy($file, $tmp_file);
    @chmod($tmp_file, 0777);
    return $tmp_file;
  }


  public function removeFile($path)
  {
    $this->_delete($path);
  }
}