<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: BBCode.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

require_once('PEAR.php');

require_once('HTML/BBCodeParser.php');

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_BBCode extends Zend_View_Helper_Abstract
{
  protected $_filters = array(
    'Basic',
    'Extended',
    'Links',
    'Images',
    'Lists',
    'Email'
  );
  
  public function BBCode($text, $options = array())
  {
    $parser = new HTML_BBCodeParser(array_merge(array(
      'filters' => join(',', $this->_filters)
    ), $options));
    $parser->setText($text);
    $parser->parse();
    return $parser->getParsed();
  }

  public function addFilter($name)
  {
    $name = ucfirst($name);
    $this->_filters = array_unique(array_merge($this->_filters, array($name)));
    return $this;
  }

  public function removeFilter($name)
  {
    $name = ucfirst($name);
    $this->_filters = array_diff($this->_filters, array($name));
    return $this;
  }
}