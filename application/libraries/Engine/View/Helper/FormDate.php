<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormDate.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormDate extends Zend_View_Helper_FormElement
{
  /**
   * Generates a set of radio button elements.
   *
   * @access public
   *
   * @param string|array $name If a string, the element name.  If an
   * array, all other parameters are ignored, and the array elements
   * are extracted in place of added parameters.
   *
   * @param mixed $value The radio value to mark as 'checked'.
   *
   * @param array $options An array of key-value pairs where the array
   * key is the radio value, and the array value is the radio text.
   *
   * @param array|string $attribs Attributes added to each radio.
   *
   * @return string The radio buttons XHTML.
   */
  public function formDate($name, $value = null, $attribs = null,
      $options = null, $listsep = "<br />\n")
  {
    if( is_string($value) )
    {
      $parsedValue = array();
      list($parsedValue['year'], $parsedValue['month'], $parsedValue['day']) = @explode('-', $value, 3);
      //list($parsedValue['day'], $parsedValue['month'], $parsedValue['year']) = @explode('-', $value, 3);
      $value = $parsedValue;
    }
    
    if( !is_array($value) )
    {
      $value = null;
    }
    
    $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
    extract($info); // name, value, attribs, options, listsep, disable

    $localeObject = Zend_Registry::get('Locale');

    // Translate year names from locale
    $yearFormat = $localeObject->getTranslation('yyyy', 'dateitem', $localeObject);
    if( $yearFormat ) {
      $date = new Zend_Date();
      foreach( $options['year'] as $key => &$val ) {
        if( $key <= 0 ) continue;
        $date->set($key, Zend_Date::YEAR);
        $val = $date->toString($yearFormat, 'iso', $localeObject);
      }
    }
    
    // Translate month names from locale
    $monthLabels = $localeObject->getTranslationList('Month', $localeObject);
    if( isset($options['month'][0]) && count($monthLabels) + 1 == count($options['month']) ) {
      array_unshift($monthLabels, '');
    }
    if( count($monthLabels) == count($options['month']) ) {
      $options['month'] = array_combine(array_keys($options['month']), array_values($monthLabels));
    }

    // Translate day names from locale
    $dayFormat = $localeObject->getTranslation('d', 'dateitem', $localeObject);
    if( $dayFormat ) {
      $date = new Zend_Date();
      $date->set(1, Zend_Date::MONTH);
      foreach( $options['day'] as $key => &$val ) {
        if( $key <= 0 ) continue;
        $date->set($key, Zend_Date::DAY);
        $val = $date->toString($dayFormat, 'iso', $localeObject);
      }
    }
    
    // Get order from locale
    $dateLocaleString = $localeObject->getTranslation('long', 'Date', $localeObject);
    $dateLocaleString = preg_replace('~\'[^\']+\'~', '', $dateLocaleString);
    $dateLocaleString = strtolower($dateLocaleString);
    $dateLocaleString = preg_replace('/[^mdy]/i', '', $dateLocaleString);
    $dateLocaleString = preg_replace(array('/(m)+/', '/(d)+/', '/(y)+/'), array(' %2$s ', ' %3$s ', ' %1$s '), $dateLocaleString);
    $dateLocaleString = preg_replace('/ +/i', '&nbsp;', trim($dateLocaleString));

    return sprintf(
      $dateLocaleString,
      $this->view->formSelect($name.'[year]', $value['year'], $attribs, $options['year']),
      $this->view->formSelect($name.'[month]', $value['month'], $attribs, $options['month']),
      $this->view->formSelect($name.'[day]', $value['day'], $attribs, $options['day'])
    );
  }
}