<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: HighlightText.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_HighlightText extends Zend_View_Helper_Abstract
{
  protected $_tag = 'span';

  protected $_class = 'highlighted-text';

  protected $_startPlaceholder = "\x02";

  protected $_endPlaceholder = "\x03";
  
  public function highlightText($body, $text)
  {
    // Process text into array
    if( is_string($text) ) {
      $text = array_map('trim', array_filter(explode(' ', $text)));
    } else if( !is_array($text) || empty($text) ) {
      return $body;
    }

    // Ignore strings that are less than two letters or have the special chars
    $search = array();
    $replace = array();
    $count = count($text);
    foreach( $text as $index => $str ) {
      if( strlen($str) < 2 ) {
        unset($text[$index]);
      } else {
        $search[$count * strlen($str) + $index] = $str;
        $replace[$count * strlen($str) + $index] = $this->_startPlaceholder . $str . $this->_endPlaceholder;
      }
    }

    // Sort
    krsort($search);
    krsort($replace);

    // Strip keys
    $search = array_values($search);
    $replace = array_values($replace);
    
    // Replace with placeholders
    $body = str_ireplace($search, $replace, $body);

    // Replace placeholders with html
    $body = str_replace(array(
      $this->_startPlaceholder,
      $this->_endPlaceholder,
    ), array(
      '<' . $this->_tag . ' class="' . $this->_class . '">',
      '</' . $this->_tag . '>',
    ), $body);
    
    return $body;
  }
}