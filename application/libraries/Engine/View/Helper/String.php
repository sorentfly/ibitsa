<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: String.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_String extends Zend_View_Helper_Abstract
{
  /**
   * Accessor
   * 
   * @return Engine_View_Helper_String
   */
  public function string()
  {
    return $this;
  }
  
  public function quoteJavascript($string, $inHtml = true)
  {
    return '"' . $this->escapeJavascript($string, $inHtml) . '"';
  }

  public function escapeJavascript($string, $inHtml = true)
  {
    // Json encode it first
    $string = Zend_Json::encode($string);
    
    // Double quote is default (remove)
    if( $string[0] == '"' && $string[strlen($string)-1] == '"' ) {
      $string = substr($string, 1, strlen($string) - 2);
    }

    // If we're in html, encode quotes
    if( $inHtml ) {
      $string = $this->htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
    }
    
    return $string;
  }

  public function chunk($string, $size = 10, $break = '<wbr>&shy;', $cut = true)
  {
    if( empty($string) || strlen($string) < $size ) return $string;
    $pattern = "/(".( $cut ? '.' : '\S' )."{".$size."})/u";
    return preg_replace($pattern, "\${1}" . $break, $string);
    /*
    return wordwrap($string, $size, $chunk, true);
    
    $anchor = ' ~~~ ';
    $size = ( $size > 0 ? 10 : $size );
    $regex = "/\S{".$size."}(?!".preg_quote($anchor).")/";
    while( preg_match($regex, $string, $m) && $whoops > 0 )
    {
      $string = str_replace($m[0], $m[0] . $anchor, $string);
      $whoops--;
    }
    $string = str_replace($anchor, $chunk, $string);
    return $string;
     */
  }

  public function htmlspecialchars($string, $quote_style = ENT_QUOTES, $charset = 'UTF-8', $double_encode = false)
  {
    if( version_compare(PHP_VERSION, '5.2.3', '>=') ) {
      $string = htmlspecialchars($string, $quote_style, $charset, $double_encode);
    } else if( $double_encode != false ) {
      $string = htmlspecialchars($string, $quote_style, $charset);
    } else {
      // Doesn't respect charset
      $search = array('<', '>');
      $replace = array('&lt;', '&gt;');
      if( $quote_style != ENT_NOQUOTES ) {
        $search[] = '"';
        $replace[] = '&quot;';
      }
      if( $quote_style == ENT_QUOTES ) {
        $search[] = "'";
        $replace[] = '&#039;';
      }
      // Escape & but not other special chars
      $string = str_replace($search, $replace, $string);
      $string = preg_replace('/&(?!(#\d{1,5}|\w{2,5});)/', '&amp;', $string);
    }

    return $string;
  }

  public function truncate($string, $length = 300, $chopString = null)
  {
    if( Engine_String::strlen($string) <= $length ) {
      return $string;
    }
    if( null === $chopString ) {
      $chopString = '...';
    }
    $chopString = $this->view->translate($chopString);
    return Engine_String::substr($string, 0, $length) . $chopString;
  }

  public function stripTags($string, $allowableTags = null)
  {
    //return Engine_String::strip_tags($str, $allowable_tags);
    return strip_tags($string, $allowableTags);
  }
}