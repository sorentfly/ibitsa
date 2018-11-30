<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: EnableLinks.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Filter_EnableLinks implements Zend_Filter_Interface
{
  /**
   *
   * @var string
   */
  protected $_class;

  /**
   *
   * @var Zend_View_Abstract
   */
  protected $_view;

  /**
   * Constructor
   * 
   * @param array $options
   */
  public function __construct($options = array())
  {
    if( !empty($options['class']) )
    {
      $this->_class = $options['class'];
    }

    if( !empty($options['view']) )
    {
      $this->_view = $options['view'];
    }

    else if( Zend_Registry::isRegistered('Zend_View') )
    {
      $this->_view = Zend_Registry::get('Zend_View');
    }
  }

  /**
   * Replace normal links with html links
   * @param string $value
   * @return string
   */
  public function filter($value)
  {
    return preg_replace_callback('/http\S+/i', array($this, '_replace'), $value);
  }

  /**
   * Does the hard work for preg_replace_callback() in self::filter()
   * 
   * @param string $matches
   * @return string
   */
  protected function _replace($matches)
  {
    $url = $matches[0];
    $cssClass = $this->_class;
    if (mb_strpos($url, 'format=smoothbox')!==false){
        $url = str_replace('format=smoothbox', '', $url);
        $url = str_replace(['?&', '&&'], ['?', '&'], $url);
        $url = rtrim($url, '?&');
        $cssClass .= ' smoothbox';
    }
    if( $this->_view instanceof Zend_View_Abstract )
    {
      $href = $this->_view->escape($url);
    }

    else
    {
      $href = htmlspecialchars($url);
    }
    
    return '<a'
      . ' href="' . $href . '"'
      . ( null !== $cssClass ? ' class="'.$cssClass.'"' : '' )
      . ' target="_blank" rel="nofollow"'
      . '>'
      . $url
      . '</a>'
      ;
  }
}
