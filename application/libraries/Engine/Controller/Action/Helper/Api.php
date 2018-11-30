<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Api.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Controller_Action_Helper_Api extends Zend_Controller_Action_Helper_Abstract
{
  /**
   * Simply retuns the instance of Engine_Api
   * 
   * @return Engine_Api
   */
  public function direct()
  {
    return Engine_Api::_();
  }
}