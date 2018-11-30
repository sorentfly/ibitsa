<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Delete.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Form_Admin_Level_Delete extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Delete Level')
      ->setDescription('Are you sure you want to delete this level? Members in this level will be re-assigned to the default level.');
    
    $level_id = new Zend_Form_Element_Hidden('level_id');
    $level_id
      //->clearDecorators()
      //->addDecorator('ViewHelper');
      ->addValidator('Int')
      ->addValidator('DbRecordExists', array(
        'table' => Engine_Api::_()->getDbtable('levels', 'authorization'),
        'field' => 'level_id'
      ));

    $this->addElements(array(
      $level_id
    ));
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Delete Level',
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

    Engine_Form::setFormElementTypeClasses($this);
  }
}