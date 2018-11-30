<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Form_Admin_Level extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Message Settings')
      ->setDescription('Specify what messaging options will be available to members in this level.');

    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOptions(array('tag' => 'h4', 'placement' => 'PREPEND'));

    $send = new Zend_Form_Element_MultiCheckbox('send');
    $send
      ->setLabel('Who can users send private messages to?')
      ->setDescription("If you don't want to allow private messaging, de-select all options below.")
      ->setMultiOptions(array(
        'registered' => 'All Registered Members',
        'network' => 'Users in the same network',
        'members' => 'Friends'
      ));
    $send->getDecorator('Description')->setOption('placement', 'PREPEND');

    $submit = new Zend_Form_Element_Button('submit', array('type' => 'submit'));
    $submit
      ->setLabel('Edit Level')
      ->setIgnore(true);

    $level_id = new Zend_Form_Element_Hidden('level_id');
    $level_id
      ->addValidator('Int')
      ->addValidator('DbRecordExists', array(
        'table' => Engine_Api::_()->getDbtable('levels', 'authorization'),
        'field' => 'level_id'
      ));

    // Add elements
    $this->addElements(array(
      $send,
      $level_id,
      $submit
    ));

    // Set element type classes
    //Engine_Form::setFormElementTypeClasses($this);
  }
}