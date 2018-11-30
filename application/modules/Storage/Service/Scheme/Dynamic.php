<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Dynamic.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Service_Scheme_Dynamic implements Storage_Service_Scheme_Interface
{
  public function generate(array $params)
  {
    if( empty($params['parent_type']) ) {
      throw new Storage_Model_Exception('Unspecified resource parent type');
    } else if( empty($params['file_id']) || !is_numeric($params['file_id']) ) {
      throw new Storage_Model_Exception('Unspecified resource identifier');
    } else if( empty($params['extension']) ) {
      throw new Storage_Model_Exception('Unspecified resource extension');
    }

    extract($params);
    
    $path = 'public' . '/';
    $path .= $parent_type . '/';
    $base = 255;
    $tmp = $file_id;
    
    // Generate subdirs while id > $base
    do {
      $mod = ( $tmp % $base );
      $tmp -= $mod;
      $tmp /= $base;
      $path .= sprintf("%02x", $mod) . '/';
    } while( $tmp > 0 );

    $path .= sprintf("%04x", $file_id)
      . '_' . substr($hash, 4, 4)
      . '.' . $extension;

    return $path;
  }
}