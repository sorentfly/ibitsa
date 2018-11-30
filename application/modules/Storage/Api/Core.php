<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Api_Core extends Core_Api_Abstract
{
  const SPACE_LIMIT_REACHED_CODE = 3999;

  /* @return Storage_Model_DbTable_Files*/
  public function table()
  {
      return Engine_Api::_()->getItemTable('storage_file');
  }

  /* @return Storage_Service_Abstract|null */
  public function getService($serviceIdentity = null)
  {
    return Engine_Api::_()->getDbtable('services', 'storage')
      ->getService($serviceIdentity);
  }

  /* @return Storage_Model_File|null */
  public function get($id, $relationship = null)
  {
    return $this->table()->getFile($id, $relationship);
  }

  /* @return Storage_Model_File|null */
  public function lookup($id, $relationship)
  {
    return $this->table()->lookupFile($id, $relationship);
  }

  /* @return Storage_Model_File|null */
  public function create($file, $params)
  {
    return $this->table()->createFile($file, $params);
  }

  public function getStorageLimits()
  {
    return $this->table()->getStorageLimits();
  }
}