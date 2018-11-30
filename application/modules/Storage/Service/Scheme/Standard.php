<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Standard.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_Scheme_Standard implements Storage_Service_Scheme_Interface
{
  public function generate(array $params)
  {
    if( empty($params['parent_type']) )
    {
      throw new Storage_Model_Exception('Unspecified resource parent type');
    }

    if( empty($params['parent_id']) || !is_numeric($params['parent_id']) )
    {
      throw new Storage_Model_Exception('Unspecified resource parent id');
    }

    if( empty($params['file_id']) || !is_numeric($params['file_id']) )
    {
      throw new Storage_Model_Exception('Unspecified resource identifier');
    }

    if( empty($params['extension']) )
    {
      throw new Storage_Model_Exception('Unspecified resource extension');
    }

    extract($params);

    $subdir = ( (int) $parent_id + 999 - ( ( (int) $parent_id - 1 ) % 1000) );

    return 'public' . '/'
      . strtolower($parent_type) . '/'
      . $subdir . '/'
      . $parent_id . '/'
      . $file_id . '.'
      . strtolower($extension);
  }
}