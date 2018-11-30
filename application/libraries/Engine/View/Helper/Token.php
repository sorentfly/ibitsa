<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Token.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_Token extends Zend_View_Helper_Abstract
{
  public function token($class = null, $element = null, $salt = null)
  {
    if( null === $class )
    {
      $class = 'Zend_Form_Element_Hash';
    }

    if( null === $element )
    {
      $element = 'token';
    }

    if( null === $salt )
    {
      $salt = 'salt';
    }

    $session = new Zend_Session_Namespace($class . '_' . $salt . '_' . $element);
    $session->setExpirationHops(1, null, true);
    $session->setExpirationSeconds(300);
    $session->hash = md5(
        mt_rand(1,1000000)
        .  $salt
        .  $element
        .  mt_rand(1,1000000)
    );

    return $session->hash;
  }
}