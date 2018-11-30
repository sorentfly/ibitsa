<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: StringLength.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Filter_StringLength implements Zend_Filter_Interface
{
  protected $_maxLength;
  
  public function __construct($options = array())
  {
    if( !empty($options['max']) )
    {
      $this->_maxLength = $options['max'];
    }
  }

  public function filter($value)
  {
    return Engine_String::substr($value, 0, $this->_maxLength);
  }
}