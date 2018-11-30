<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Form_Admin_Level_Create extends Engine_Form
{
  public function init()
  {
    // Set form attributes
    $this->setTitle('Create Member Level');
    $this->setDescription("AUTHORIZATION_FORM_ADMIN_LEVEL_EDIT_DESCRIPTION");

    // Element: title
    $this->addElement('Text', 'title', array(
      'label' => 'Member Level Name',
      'allowEmpty' => false,
      'required' => true,
    ));

    // Element: description
    $this->addElement('Textarea', 'description', array(
      'label' => 'Description',
      'allowEmpty' => true,
      'required' => false,
    ));

    // Element: type
    $this->addElement('Select', 'type', array(
      'label' => 'Type',
      'description' => 'The type cannot be changed after creation.',
      'multiOptions' => array(
        'admin' => 'Administrator',
        'moderator' => 'Moderator',
        'user' => 'Normal',
      ),
      'value' => 'user',
    ));
    $this->type->getDecorator('Description')->setOption('placement', 'append');

    // Element: parent
    $defaultLevelIdentity = null;
    $parentMultiOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if( $level->type == 'public' ) {
        continue;
      }
      $parentMultiOptions[$level->level_id] = $level->getTitle() . ' (' . $this->type->options[$level->type] . ')';
      if( $level->flag == 'default' ) {
        $defaultLevelIdentity = $level->level_id;
      }
    }
    $this->addElement('Select', 'parent', array(
      'label' => 'Copy Values From:',
      'description' => 'You must select a level that is the same type as selected above.',
      'multiOptions' => $parentMultiOptions,
      'value' => $defaultLevelIdentity,
    ));
    $this->parent->getDecorator('Description')->setOption('placement', 'append');


    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Create Level',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => 'admin/levels',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}