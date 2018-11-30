<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: HtmlLink.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_HtmlLink extends Zend_View_Helper_HtmlElement
{
  public function htmlLink($href = null, $content = "", $attribs = array())
  {
    if( 0 == func_num_args() ) {
      return $this;
    }

    // You can give href an array to use router
    if( is_array($href) )
    {
      $href = $this->url($href);
      //if( $query ) {
      //  $href .= '?' . $query;
      //}
      //if( $fragment ) {
      //  $href .= '#' . $fragment;
      //}
    }

    // $href is an object with a getHref() method
    else if( is_object($href) && method_exists($href, 'getHref') )
    {
      $href = $href->getHref();
    }

    if( null !== $href ) {
      $attribs = array_merge(array(
        'href' => $href
      ), $attribs);
    }
    
    // Merge data and type
    return '<a ' . $this->_htmlAttribs($attribs) . '>' . $content . '</a>';
  }

  public function url($params)
  {
    $urlParams = array_diff_key($params, array(
      'route' => null,
      'reset' => null,
      'APPEND' => null,
      'QUERY' => null,
      'HASH' => null,
    ));

    $route = @$params['route'];
    $reset = ( isset($params['reset']) ? $params['reset'] : true );
    //if( $reset && !$route ) $route = 'default';

    $href = $this->view->url($urlParams, $route, $reset);
    
    if( !empty($params['APPEND']) ) {
      $href .= $params['APPEND'];
    }
    if( !empty($params['QUERY']) ) {
      if( is_string($params['QUERY']) ) {
        $params['QUERY'] = trim($params['QUERY'], '?&');
      } else if( is_array($params['QUERY']) ) {
        $params['QUERY'] = http_build_query($params['QUERY']);
      }
      $href .= '?' . $params['QUERY'];
    }
    if( !empty($params['HASH']) ) {
      if( is_string($params['HASH']) ) {
        $params['HASH'] = ltrim($params['HASH'], '#');
      }
      $href .= '#' . $params['HASH'];
    }

    return $href;
  }
}