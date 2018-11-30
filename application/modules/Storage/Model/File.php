<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Model_File extends Core_Model_Item_Abstract
{
  // Item stuff

  public function getPhotoUrl($type = null)
  {
    if( !$type ) {
      if( empty($this->type) ) {
        $file = $this;
      } else if( !empty($this->parent_file_id) ) {
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->parent_file_id);
      } else {
        $file = $this;
      }
    } else {
      if( !empty($this->type) ) {
        if( $this->type == $type ) {
          $file = $this;
        } else if( !empty($this->parent_file_id) ) {
          $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->parent_file_id, $type);
        } else {
          $file = $this;
        }
      } else {
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
      }
    }

    // no file
    if( !$file ) {
      return null;
    }

    // should we filter out non-image extensions?
    if( !in_array($file->extension, array('jpg', 'png', 'jpeg', 'gif', 'tif', 'bmp')) ) {
      return null;
    }

    return $file->map();
  }

  public function getHref()
  {
    //bitsa edit: goncharov: for correct file get for test DB - add original http link
    return $this->getStorageService()->map($this);
  }

  public function getParent($recurseType = null)
  {
    if( $this->parent_type == 'temporary' ||
        $this->parent_type == 'system' ) {
      return null;
    } else {
      return parent::getParent($recurseType);
    }
  }

  // Storage stuff

  public function getStorageService($type = null)
  {
    $type = $type ? $type : $this->service_id;
    return Engine_Api::_()->getDbtable('services', 'storage')
        ->getService($type);
  }

  public function getChildren($type, $params = array())
  {
    $table = $this->getTable();
    $select = $table->select()
      ->where('parent_file_id = ?', $this->file_id);
    return $table->fetchAll($select);
  }



  // Simple operations
  
  public function bridge(Storage_Model_File $file, $type, $isChild = false)
  {
    $child  = ( $isChild ? $this : $file );
    $parent = ( $isChild ? $file : $this );
    $child->parent_file_id = $parent->file_id;
    $child->type = $type;
    $child->save();

    return $this;
  }

  public function map()
  {
    $uri = $this->getStorageService()->map($this);
    /*fix a bug - bitsa*/
    if (_ENGINE_SSL && strpos($uri, 'http:') === 0){
        $uri = str_replace('http:', 'https:', $uri);
    }
    /*end fix*/

    /*ABSOLUTE PATH TO FILES*/
    if ($this->modified_date < '2018-04-01 00:00:00'){
        $uri .= '?c=' . substr($this->hash, 0, 4);
    }else{
        $uri .= '?t=' . dechex(substr( strtotime($this->modified_date), -5));
    }

    if (defined('ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa') && defined('OLDFILE_LIMIT_DATE') && ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa
        && strpos($uri, 'http://') !== 0 && strpos($uri, 'https://') !== 0 && strpos($uri, '//') !== 0
       ){
          if (strtotime($this->creation_date)  <= strtotime(OLDFILE_LIMIT_DATE)){
              return ENABLE_OLDFILE_ABSOLUTE_PATH_TO_bitsa . $uri;
          }
    }
    /*ABSOLUTE PATH end*/

    return $uri;
  }

  public function store($spec = NULL)
  {
    /* @var Storage_Service_Local $service */
    $service = $this->getStorageService();
    $isCreate = empty($this->file_id);
    $meta = $service->fileInfo($spec);
    if (isset($meta['mime_major']) && $meta['mime_major'] == 'image'){
        list($meta['width'], $meta['height']) = getimagesize( is_array($spec) ? $spec['tmp_name'] : $spec );
    }
    if ($this->name){
        unset($meta['name']);
    }
    $this->setFromArray($meta);
    $this->service_id = $service->getIdentity();
    if( empty($this->user_id) &&
        $this->parent_type != 'temporary' &&
        $this->parent_type != 'system' ) {
      $this->user_id = Engine_Api::_()->user()->getViewer()->getIdentity(); // @todo this is wrong
    }

    // Have to initialize now if creation
    if( $isCreate ) {
      $this->storage_path = '';
      $this->save();   
    }
    
    // Store file to service
    $path = $service->store($this, $meta['tmp_name']);

    // If a file existed before and not same name, try to remove the old one
    if( !empty($this->storage_path) &&
        $this->storage_path != 'temp' &&
        $this->storage_path != $path ) {
      $service->removeFile($this->storage_path);
    }

    // We still have to update the path even if we just created it
    $this->storage_path = $path;
    $this->save();
    
    return $this;
  }
    
  public function write($data, $meta)
  {
    $service = $this->getStorageService();
    $isCreate = empty($this->file_id);

    $meta['hash'] = md5($data);
    $meta['size'] = strlen($data);

    $this->setFromArray($meta);
    $this->service_id = $service->getIdentity();
    if( empty($this->user_id) &&
        $this->parent_type != 'temporary' &&
        $this->parent_type != 'system' ) {
      $this->user_id = Engine_Api::_()->user()->getViewer()->getIdentity(); // @todo this is wrong
    }

    // Have to initialize now if creation
    if( $isCreate ) {
      $this->save();
    }
    
    // Write data to service
    $path = $service->write($this, $data);

    // If a file existed before and not same name, try to remove the old one
    if( !empty($this->storage_path) &&
        $this->storage_path != 'temp' &&
        $this->storage_path != $path ) {
      $service->removeFile($this->storage_path);
    }
    
    // We still have to update the path even if we just created it
    $this->storage_path = $path;
    $this->save();

    return $this;
  }

  public function read()
  {
    return $this->getStorageService()->read($this);
  }

  public function remove()
  {
    $this->getStorageService()->remove($this);
    $this->delete();
  }

  public function temporary()
  {
    return $this->getStorageService()->temporary($this);
  }



  // Complex

  public function move($storage)
  {
    $originalStorage = $this->getStorageService();
    
    if( !is_object($storage) ) {
      $storage = $this->getStorageService($storage);
    }

    if( !($storage instanceof Storage_Service_Interface) ) {
      throw new Exception("Storage must be an instance of File_Service_Storage_Interface");
    }

    if( $storage->getIdentity() == $originalStorage->getIdentity() ) {
      throw new Exception('You may not move a file within a storage type');
    }

    $originalPath = $this->storage_path;

    // Store using temp file
    $tmp_file = $originalStorage->temporary($this);
    $path = $storage->store($this, $tmp_file);

    $this->service_id = $storage->getIdentity();
    $this->storage_path = $path;
    $this->modified_date = date('Y-m-d H:i:s');
    $this->save();
    
    // Now remove original and temporary file
    $originalStorage->removeFile($originalPath);
    @unlink($tmp_file);

    return $this;
  }

  /* @return Storage_Model_File */
  public function copy($params = array(), $storage = null)
  {
    $storage = $this->getStorageService($storage);

    if( !($storage instanceof Storage_Service_Interface) ) {
      throw new Exception("Storage must be an instance of File_Service_Storage_Interface");
    }

    // Create new row
    // @todo store this in main model?
    $params = array_merge($this->toArray(), $params);
    $params['service_id'] = $storage->getIdentity();
    $params['storage_path'] = 'temp';
    unset($params['file_id']);

    $newThis = $this->getTable()->createRow();
    $newThis->setFromArray($params);
    $newThis->save();

    // Read into temp file and store
    $tmp_file = $this->getStorageService()->temporary($this);
    $path = $storage->store($this, $tmp_file);
    
    // Update
    // @todo make sure file is removed if this fails
    $newThis->storage_path = $path;
    $newThis->save();

    // Remove temp file
    @unlink($tmp_file);

    return $newThis;
  }

  public function updatePath()
  {
    $service = $this->getStorageService();

    $oldPath = $this->storage_path;
    $newPath = $service->getScheme()->generate($this->toArray());

    // No update required
    if( $oldPath == $newPath ) {
      return $this;
    }

    // @todo maybe update this to move the file internally
    $tmpFile = $this->temporary();

    // Store file to service
    $path = $service->store($this, $tmpFile);

    // Update the path and remove the old file if necessary
    if( $oldPath != $path ) {
      $this->storage_path = $path;
      $this->save();

      $service->removeFile($oldPath);
    }

    return $this;
  }

  protected function _delete()
  {
    if( $this->_disableHooks ) return;
    
    try {
      $this->getStorageService()->remove($this);
    } catch( Exception $e ) {
      
    }
  }
  
  public function isImage()
  {
      return in_array(strtolower($this->extension), array('jpg', 'png', 'jpeg', 'gif', 'tif', 'bmp'));
  }
  
  public function getIconClass(){

  	if( $this->isImage()) {
  		return 'fa fa-file-image-o file-type-'.$this->extension;
  	} else if( in_array($this->extension, array('pdf')) ) {
  		return 'fa fa-file-pdf-o file-type-'.$this->extension;
  	} else if( in_array($this->extension, array('zip', 'rar', 'tar', '7z'))) {
  		return 'fa fa-file-aschive-o file-type-'.$this->extension;
  	} else if( in_array($this->extension, array('doc', 'dot', 'docx', 'docm', 'dotx', 'dotm', 'docb'))) {
  		return 'fa fa-file-word-o file-type-'.$this->extension;
  	} else if( in_array($this->extension, array('xls', 'xlt', 'xlm', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xla', 'xlam', 'xll', 'xlw'))) {
  		return 'fa fa-file-excel-o file-type-'.$this->extension;
  	} else if( in_array($this->extension, array('ppt', 'pot', 'pps', 'pptx', 'pptm', 'potx', 'potm', 'ppam', 'ppsx', 'ppsm', 'sldx', 'sldm'))) {
  		return 'fa fa-file-powerpoint-o file-type-'.$this->extension;  	
  	} else if( in_array($this->extension, array('wav', 'wma', 'mp3'))) {
  		return 'fa fa-file-audio-o file-type-'.$this->extension; 
  	} else if( in_array($this->extension, array('avi', 'mov', 'wmv', 'mkv', 'mp4', 'mpg', 'mpeg', ))) {
  		return 'fa fa-file-video-o file-type-'.$this->extension;
  	} else {
  		return 'fa fa-file file-type-'.$this->extension;
  	}
  	
  }

    public function getAnnotations(User_Model_User $annotationsFor = NULL)
    {
        $annotationTable = Engine_Api::_()->getDbTable('annotations', 'core');
        $select = $annotationTable->select()
            ->where('resource_type = ?', $this->getType())
            ->where('resource_id = ?', $this->getIdentity());
        if ($annotationsFor){
            $select->where('user_id = ?', $annotationsFor->getIdentity());
        }
        $anotationRows = $select->query()->fetchAll();

        if ($anotationRows && ($overdrawData = json_decode($anotationRows[0]['annotation_json']))){
            return $overdrawData;
        }
        return [];
    }

    public function transformImage($transformType /* 'rotate' or 'flip'*/, $transformProperty /* angle or side to flip */)
    {
        $filepath = APPLICATION_PATH . '/' .$this->storage_path;
        if (!file_exists($filepath)){
            return false;
        }

        $ext = strtolower(substr($this->storage_path, strrpos($this->storage_path, '.') + 1));

        $bgColor = 0;
        $doTransform = function($imagePointer) use ($bgColor, $transformProperty, $transformType){
            if ($transformType == 'rotate') return imagerotate($imagePointer, $transformProperty, $bgColor);
            if ($transformType == 'flip'){
                imageflip($imagePointer, $transformProperty == 0 ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
                return $imagePointer;
            }
        };

        $transformed = null;
        $source = null;
        if(strtolower($ext) == 'png'){
            $source = imagecreatefrompng($filepath);
            $bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
            // Transform
            $transformed = $doTransform($source);
            if (!$transformed) return false;
            imagesavealpha($transformed, true);
            imagepng($transformed,$filepath);
        }else if(in_array(strtolower($ext), ['jpg', 'jpeg'])){
            $source = imagecreatefromjpeg($filepath);
            // Transform
            $transformed = $doTransform($source);
            if (!$transformed) return false;
            imagejpeg($transformed, $filepath);
        }else if (strtolower($ext) == 'gif'){
            $source = imagecreatefromgif($filepath);
            // Transform
            $transformed = $doTransform($source);
            if (!$transformed) return false;
            imagegif($transformed, $filepath);
        }else{
            return false;
        }
        $this->modified_date = date('Y-m-d H:i:s');
        $this->save();
        // Free the memory
        if ($transformed && $transformed!=$source) imagedestroy($transformed);
        if ($source) imagedestroy($source);

        return true;
    }
}