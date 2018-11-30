<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Style.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Edit_Style extends Engine_Form
{
  public function init()
  {
    $this->addElement('Textarea', 'style', array(
      'label' => 'Profile Style',
      'description' => 'Add your own CSS code above to give your profile a more personalized look.'
    ));
    $this->style->getDecorator('Description')->setOption('placement', 'APPEND');
    
    $this->addElement('Button', 'done', array(
      'label' => 'Save Styles',
      'type' => 'submit',
    ));
  }
}