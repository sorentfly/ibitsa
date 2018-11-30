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
class Storage_Plugin_Core
{
  public function onItemDeleteBefore($event)
  {
    $item = $event->getPayload();

    if( $item->getType() !== 'storage_file' ) {
      $table = Engine_Api::_()->getItemTable('storage_file');
      $select = $table->select()
        ->where('parent_type = ?', $item->getType())
        ->where('parent_id = ?', $item->getIdentity());

      foreach( $table->fetchAll($select) as $file ) {
        try {
          $file->delete();
        } catch( Exception $e ) {
          if( !($e instanceof Engine_Exception) ) {
            $log = Zend_Registry::get('Zend_Log');
            $log->log($e->__toString(), Zend_Log::WARN);
          }
        }
      }
    }
  }
}