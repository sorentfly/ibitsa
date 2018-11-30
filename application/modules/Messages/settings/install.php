<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Installer extends Engine_Package_Installer_Module
{
  public function onInstall()
  {
    //$this->_addComposePage();
    $this->_addInboxPage();
    $this->_addOutboxPage();
    
    parent::onInstall();
  }
  
  protected function _addInboxPage()
  {
    $db = $this->getDb();
    
    $page = 'messages_messages_inbox';
    $displayname = 'Messages Inbox Page';
    $title = 'Inbox';
    $description = '';
    
    // check page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', $page)
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => $page,
        'displayname' => $displayname,
        'title' => $title,
        'description' => $description,
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
      ));
      $middle_id = $db->lastInsertId();
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 2
      ));
      
      // Extra
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'messages.menu',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 1
      ));
    }
  }
  
  protected function _addOutboxPage()
  {
    $db = $this->getDb();
    
    $page = 'messages_messages_outbox';
    $displayname = 'Messages Outbox Page';
    $title = 'Inbox';
    $description = '';
    
    // check page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', $page)
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => $page,
        'displayname' => $displayname,
        'title' => $title,
        'description' => $description,
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
      ));
      $middle_id = $db->lastInsertId();
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 2
      ));
      
      // Extra
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'messages.menu',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 1
      ));
    }
  }
  
  protected function _addComposePage()
  {
    $db = $this->getDb();
    
    $page = 'messages_messages_compose';
    $displayname = 'Messages Compose Page';
    $title = 'Inbox';
    $description = '';
    
    // check page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', $page)
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => $page,
        'displayname' => $displayname,
        'title' => $title,
        'description' => $description,
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
      ));
      $middle_id = $db->lastInsertId();
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 2
      ));
      
      // Extra
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'messages.menu',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
        'order' => 1
      ));
    }
  }
}

  