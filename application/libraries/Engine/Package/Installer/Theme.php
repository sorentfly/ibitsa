<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Theme.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Installer_Theme extends Engine_Package_Installer_Abstract
{
  public function onInstall()
  {
    $db = $this->getDb();
    $package = $this->getOperation()->getPrimaryPackage();

    // Remove
    if( 'remove' == $this->_operation->getOperationType() ) {

      try {
        $oldInfo = $db->select()
          ->from('engine4_core_themes')
          ->where('name = ?', $package->getName())
          ->limit(1)
          ->query()
          ->fetch();
        
        if( !empty($oldInfo) ) {
          $db->delete('engine4_core_themes', array(
            'name = ?' => $package->getName(),
          ));
        }

        if( !empty($oldInfo['active']) ) {
          $randomThemeInfo = $db->select()
            ->from('engine4_core_themes')
            ->limit(1)
            ->query()
            ->fetch();
          if( !empty($randomThemeInfo) ) {
            $db->update('engine4_core_themes', array(
              'active' => 1,
            ),array(
              'theme_id = ?' => $randomThemeInfo['theme_id'],
            ));
            $this->_message('The default theme has been changed to "' . $randomThemeInfo['title'] . '"');
          } else {
            $this->_error('There were no more themes to re-assign as default. Please re-install a theme immediately.');
          }
        }
      } catch( Exception $e ) {
        $this->_error('Unable to update theme info.');
        return $this;
      }

      $this->_message('Theme info removed.');
    }

    // General
    else {
      $newInfo = array(
        'name' => (string) $package->getName(),
        //'version' => $package->getVersion(),
        'title' => (string) $package->getTitle(),
        'description' => (string) $package->getDescription(),
      );

      try {
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_themes')
          ->where('name = ?', $package->getName())
          ->limit(1);

        $oldInfo = $select->query()->fetch();

        if( empty($oldInfo) ) {
          $db->insert('engine4_core_themes', $newInfo);
        } else {
          $db->update('engine4_core_themes', $newInfo, array(
            'name = ?' => $package->getName(),
          ));
        }
      } catch( Exception $e ) {
        $this->_error('Unable to update theme info.');
        return $this;
      }

      $this->_message('Theme info updated.');
    }

    return $this;
  }
}