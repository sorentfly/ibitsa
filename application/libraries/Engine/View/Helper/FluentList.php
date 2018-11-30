<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FluentList.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FluentList extends Zend_View_Helper_Abstract
{
  /**
   * Generates a fluent list of item. Example:
   *   You
   *   You and Me
   *   You, Me, and Jenny
   * 
   * @param array|Traversable $items
   * @return string
   */
  public function fluentList($items, $translate = false)
  {
    if( 0 === ($num = count($items)) )
    {
      return '';
    }
    
    $comma = $this->view->translate(',');
    $and = $this->view->translate('and');
    $index = 0;
    $content = '';
    foreach( $items as $item )
    {
      if( $num > 2 && $index > 0 ) $content .= $comma . ' '; else $content .= ' ';
      if( $num > 1 && $index == $num - 1 ) $content .= $and . ' ';

      $href = null;
      $title = null;

      if( is_object($item) ) {
        if( method_exists($item, 'getTitle') && method_exists($item, 'getHref') ) {
          $href = $item->getHref();
          $title = $item->getTitle();
        } else if( method_exists($item, '__toString') ) {
          $title = $item->__toString();
        } else {
          $title = (string) $item;
        }
      } else {
        $title = (string) $item;
      }
      
      if( $translate ) {
        $title = $this->view->translate($title);
      }

      if( null === $href ) {
        $content .= $title;
      } else {
        $content .= $this->view->htmlLink($href, $title);
      }
      
      $index++;
    }
    
    return $content;
  }
}