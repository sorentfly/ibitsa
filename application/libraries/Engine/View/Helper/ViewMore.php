<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ViewMore.php 10053 2013-06-12 02:12:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_ViewMore extends Zend_View_Helper_HtmlElement
{
  protected $_moreLength = 255; // Note: truncation at 255 + 4 = 259 (for " ...")
  protected $_lessLength = 511;
  protected $_maxLength = 1027;
  protected $_fudgesicles = 10;
  protected $_maxLineBreaks = 4; // Truncate early if more than this nl
  protected $_tag = 'span';

  public function viewMore($string, $moreLength = null, $maxLength = null, 
      $lessLength = null, $nl2br = true)
  {
    if( !is_numeric($moreLength) || $moreLength <= 0 ) {
      $moreLength = $this->_moreLength;
    }
    if( !is_numeric($maxLength) || $maxLength <= 0 ) {
      $maxLength = $this->_maxLength;
    }
    if( !is_numeric($lessLength) || $lessLength <= 0 ) {
      $lessLength = $this->_lessLength;
    }
    
    // If using line breaks, ensure that there are not too many line breaks
    if( $nl2br ) {
      $string = trim(preg_replace('/[\r\n]+/', "\n", $string));
      if( ($c = substr_count($string, "\n")) > $this->_maxLineBreaks) {
        $pos = 0;
        for( $i = 0; $i < $this->_maxLineBreaks; $i++ ) {
          $pos = strpos($string, "\n", $pos + 1);
        }
        if( $pos <= 0 || !is_int($pos) ) {
          $pos = null;
        }
        if( $pos && $pos < $moreLength ) {
          $moreLength = $pos;
        }
      }
    }
    
    // If length is less than max len, just return
    $strLen = Engine_String::strlen($string);
    if( $strLen <= $moreLength + $this->_fudgesicles ) {
      if( $nl2br ) {
        return nl2br($string);
      } else {
        return $string;
      }
    }
    
    // Otherwise truncate
    if( $strLen >= $maxLength ) {
      $strLen = $maxLength;
      $string = Engine_String::substr($string, 0, $maxLength) . $this->view->translate('... &nbsp;');
    }
    
    $shortText = Engine_String::substr($string, 0, $moreLength);
    $fullText = $string;
    
    // Do nl2br
    if( $nl2br ) {
      $shortText = nl2br($shortText);
      $fullText = nl2br($fullText);
    }
    
    $onclick = <<<EOF
var me = $(this).getParent(), other = $(this).getParent().getNext(), fn = function() {
  me.style.display = 'none';
  other.style.display = '';
};
fn();
setTimeout(fn, 0);
EOF;
    $content = '<'
      . $this->_tag
      . ' class="view_more"'
      . '>'
      . $shortText
      . $this->view->translate('... &nbsp;')
      . '<a class="view_more_link" href="javascript:void(0);" onclick="' . htmlspecialchars($onclick) . '">'
      . $this->view->translate('more')
      . '</a>'
      . '</'
      . $this->_tag
      . '>'
      . '<'
      . $this->_tag
      . ' class="view_more"'
      . ' style="display:none;"'
      . '>'
      . $fullText
      . ' &nbsp;'
      ;

    if( $strLen >= $lessLength ) {
      $onclick = <<<EOF
var me = $(this).getParent(), other = $(this).getParent().getPrevious(), fn = function() {
  me.style.display = 'none';
  other.style.display = '';
};
fn();
setTimeout(fn, 0);
EOF;
      $content .= '<a class="view_less_link" href="javascript:void(0);" onclick="' . htmlspecialchars($onclick) . '">'
          . $this->view->translate('less')
          . '</a>';
    }

    $content .= '</'
      . $this->_tag
      . '>'
      ;

    return $content;
  }

  public function setMoreLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_moreLength = $length;
    }

    return $this;
  }

  public function setMaxLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_maxLength = $length;
    }

    return $this;
  }
}