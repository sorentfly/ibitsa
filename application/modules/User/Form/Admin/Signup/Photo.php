<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Photo.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Signup_Photo extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
  
    // Get step and step number
    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepSelect = $stepTable->select()->where('class = ?', str_replace('_Form_Admin_', '_Plugin_', get_class($this)));
    $step = $stepTable->fetchRow($stepSelect);
    $stepNumber = 1 + $stepTable->select()
      ->from($stepTable, new Zend_Db_Expr('COUNT(signup_id)'))
      ->where('`order` < ?', $step->order)
      ->query()
      ->fetchColumn()
      ;
    $stepString = $this->getView()->translate('Step %1$s', $stepNumber);
    $this->setDisableTranslator(true);


    // Custom
    $this->setTitle($this->getView()->translate('%1$s: Add Your Photo', $stepString));

    // Element: enable
    $this->addElement('Radio', 'enable', array(
      'label' => 'User Photo Upload',
      'description' => 'Do you want your users to be able to upload a photo of ' .
        'themselves upon signup?',
      'multiOptions' => array(
        '1' => 'Yes, give users the option to upload a photo upon signup.',
        '0' => 'No, do not allow users to upload a photo upon signup.',
      ),
    ));
  
    // Element: require_photo
    $this->addElement('Radio', 'require_photo', array(
      'label' => 'Require User Photo',
      'description' => 'Do you want to require your users to upload a photo of ' .
        'themselves upon signup?',
      'multiOptions' => array(
        '1' => 'Yes, require users upload a photo upon signup.',
        '0' => 'No, do not require users upload a photo upon signup.',
      ),
    ));

    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

    // Populate
    $this->populate(array(
      'enable' => $step->enable,
      'require_photo' => $settings->getSetting('user.signup.photo', 0),
    ));
  }
}