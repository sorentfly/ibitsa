<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ReplaceLinks.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_ReplaceLinks extends Engine_View_Helper_HtmlLink
{
  protected $_attribs;

  public function replaceLinks($string, $attribs = array())
  {
    // Set attribs
    $attribs = array_merge(array(
      'target' => '_blank',
      'rel' => 'nofollow',
    ), $attribs);
    $this->_attribs = $attribs;

    // Replace and return
    return preg_replace_callback('/http\S+/i', array($this, '_replace'), $value);
  }
  
  protected function _replace($matches)
  {
    return $this->htmlLink($matches[0], $matches[0], $this->_attribs);
  }
}