<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: DateTime.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_DateTime extends Zend_View_Helper_FormElement
{
  public function dateTime($datetime)
  {
    trigger_error('Please use the locale view helper: $view->locale()->toDateTime()', E_USER_DEPRECATED);
    return $this->view->locale()->toDateTime($datetime);
  }
}