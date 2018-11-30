<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormDuration.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormDuration extends Zend_View_Helper_FormElement
{
  public function formDuration($name, $value = null, $attribs = null,
      $options = null, $listsep = " ")
  {
    // Process
    if( is_string($value) ) {
      if( preg_match('/^\d+\s+\w+$/', $value, $matches) ) {
        $value = array($matches[1], $matches[2]);
      } else {
        $value = array(null, null);
      }
    }
    
    if( is_array($value) ) {
      if( count($value) != 2 || !is_numeric($value[0]) || !is_string($value[1]) ) {
        $value = array(null, null);
      } else {
        $value[1] = rtrim($value[1], 's'); // Remove s
      }
    } else {
      $value = array(null, null);
    }

    return $this->view->formText($name . '[]', $value[0], array(
        'id' => $name . '-text',
        'style' => ( null !== $value && ($value[1] == 'forever' || $value[1] == 'lifetime') ? 'display:none;' : '' ),
        'disable' => !empty($attribs['disable']),
      ))
      . $listsep
      . $this->view->formSelect($name . '[]', $value[1], array(
          'multiple' => false,
          'id' => $name . '-select',
          'onchange' => 'var el = document.getElementById("' . $name . '-text' . '"); if( this.value == "forever" || this.value == "lifetime" ) { el.value = "0"; el.style.display = "none"; } else { el.style.display = ""; }',
          'disable' => !empty($attribs['disable']),
        ), $options);
  }
}