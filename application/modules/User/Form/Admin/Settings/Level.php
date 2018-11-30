<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Settings_Level extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Member Settings')
      ->setDescription('Specify what privacy options will be available to members in this level.');

    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOptions(array('tag' => 'h4', 'placement' => 'PREPEND'));

    // prepare user levels
    $table = Engine_Api::_()->getDbtable('levels', 'authorization');
    $select = $table->select();
    $user_levels = $table->fetchAll($select);

    foreach ($user_levels as $user_level) {
      $levels_prepared[$user_level->level_id] = $user_level->getTitle();
    }

    // category field
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'multiOptions' => $levels_prepared,
      'onchange' => 'javascript:fetchLevelSettings(this.value);',
      'ignore' => true,
    ));


    // Add block
    $this->addElement('Radio', 'block', array(
      'label' => 'Allow Blocking?',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_BLOCK_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      )
    ));
    $this->block->getDecorator('Description')->setOption('placement', 'PREPEND');

    // Add search
    $this->addElement('Radio', 'search', array(
      'label' => 'Search Privacy Options',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_SEARCH_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
    ));
    $this->search->getDecorator('Description')->setOption('placement', 'PREPEND');

    // Add view
    $this->addElement('MultiCheckbox', 'auth_view', array(
      'label' => 'Profile Viewing Options',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHVIEW_DESCRIPTION',
      'multiOptions' => array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'network' => 'My Network',
        'member' => 'My Friends',
        'owner' => 'Only Me',
      ),
    ));
    $this->auth_view->getDecorator('Description')->setOption('placement', 'PREPEND');


    // Add comment
    $this->addElement('MultiCheckbox', 'auth_comment', array(
      'label' => 'Profile Commenting Options',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHCOMMENT_DESCRIPTION',
      'multiOptions' => array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'network' => 'My Network',
        'member' => 'My Friends',
        'owner' => 'Only Me',
      )
    ));
    $this->auth_comment->getDecorator('Description')->setOption('placement', 'PREPEND');


    // Add status
    $this->addElement('Radio', 'status', array(
      'label' => 'Allow status messages?',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_STATUS_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      )
    ));

    // Add username
    $this->addElement('Radio', 'username', array(
      'label' => 'Allow profile address changes?',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_USERNAME_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      )
    ));
    $this->username->getDecorator('Description')->setOption('placement', 'PREPEND');


    // Add delete
    $this->addElement('Radio', 'delete', array(
      'label' => 'Allow account deletion?',
      'description' => 'If set to "yes", members will have the option of deleting their accounts.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      )
    ));
    $this->delete->getDecorator('Description')->setOption('placement', 'PREPEND');
    
    // Add submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}