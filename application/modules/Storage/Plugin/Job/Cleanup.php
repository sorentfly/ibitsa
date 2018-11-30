<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Cleanup.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Plugin_Job_Cleanup extends Core_Plugin_Job_Abstract
{
  protected function _execute()
  {
    $table = Engine_Api::_()->getDbtable('files', 'storage');
    
    // Prepare
    $position   = $this->getParam('position', 0);
    $progress   = $this->getParam('progress', 0);
    $total      = $this->getParam('total');
    $limit      = $this->getParam('limit', 100);
    $isComplete = false;
    $break      = false;


    // Populate total
    if( null === $total ) {
      $total = $table->select()
        ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
        ->query()
        ->fetchColumn(0)
        ;
      $this->setParam('total', $total);
      if( !$progress ) {
        $this->setParam('progress', 0);
      }
      if( !$position ) {
        $this->setParam('position', 0);
      }
    }

    // Complete if nothing to do
    if( $total <= 0 ) {
      $this->_setWasIdle();
      $this->_setIsComplete(true);
      return;
    }


    // Execute
    $count = 0;
    
    while( !$break && $count <= $limit ) {

      $file = $table->fetchRow($table->select()
          ->where('file_id >= ?', (int) $position + 1)
          ->order('file_id ASC')
          ->limit(1));

      // Nothing left
      if( !$file ) {
        $break = true;
        $isComplete = true;
      }

      // Main
      else {
        $position = $file->getIdentity();
        $count++;
        $progress++;

        $shouldDelete = false;

        if( $file->parent_type == 'system' ) {
          // It's a system file
          $parent = true;
        } else if( $file->parent_type == 'temporary' ) {
          // Check for expired temporary file
          // store temporary files for up to a day?
          if( strtotime($file->creation_date) > time() + 86400 ) {
            $parent = false;
          } else {
            $parent = true;
          }
        } else {
          // Check for parent
          try {
            $parent = $file->getParent();
            if( !$parent ) {
              $shouldDelete = true;
            }
          } catch( Exception $e ) {
            $this->_addMessage(sprintf('%1$d had no parent.', $file->file_id));
            $shouldDelete = true;
          }
          // Check for user?
//          if( !empty($file->user_id) ) {
//            try {
//              $user = $file->getOwner();
//            } catch( Exception $e ) {
//              $this->_addMessage(sprintf('%1$d had no owner.', $file->file_id));
//              $shouldDelete = true;
//            }
//          }
        }
        
        // Delete?
        if( $shouldDelete ) {
          try {
            $file->delete();
          } catch( Exception $e ) {
            $this->_addMessage(sprintf('%1$d could not be deleted.', $file->file_id));
          }
        }

        // Otherwise, should we update the path?
        else {
          $file->updatePath();
        }

        unset($shouldDelete);
	unset($parent);
	unset($user);
        unset($file);
      }
    }

    // Cleanup
    $this->setParam('position', $position);
    $this->setParam('progress', $progress);
    $this->_setIsComplete($isComplete);
  }
}
