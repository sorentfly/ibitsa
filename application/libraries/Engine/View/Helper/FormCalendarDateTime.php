<?php

/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormCalendarDateTime.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormCalendarDateTime extends Zend_View_Helper_FormElement
{
  public function formCalendarDateTime($name, $value = null, $attribs = null,
      $options = null, $listsep = "<br />\n")
  {
    $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
    extract($info); // name, value, attribs, options, listsep, disable

    // Get date format
    if( isset($attribs['dateFormat']) ) {
      $dateFormat = $attribs['dateFormat'];
      //unset($attribs['dateFormat']);
    } else {
      $dateFormat = 'ymd';
    }

    // Get use military time
    if( isset($attribs['useMilitaryTime']) ) {
      $useMilitaryTime = $attribs['useMilitaryTime'];
      //unset($attribs['useMilitaryTime']);
    } else {
      $useMilitaryTime = true;
    }

    // Check value type

    if( is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})( (\d{2}):(\d{2})(:(\d{2}))?)?$/', $value, $m) ) {
      $tmpDateFormat = trim(str_replace(array('d', 'y', 'm'), array('.%3$02d', '.%1$d', '.%2$02d'), $dateFormat), '.');
      $value = array();

      // Get date
      $value['date'] = sprintf($tmpDateFormat, $m[1], $m[2], $m[3]);
      if( $value['date'] == '0/0/0' ) {
        unset($value['date']);
      }

      // Get time
      if( isset($m[6]) ) {
        $value['hour'] = $m[5];
        $value['minute'] = $m[6];
        if( !$useMilitaryTime ) {
          $value['ampm'] = ( $value['hour'] >= 12 ? 'PM' : 'AM' );
          if( 0 == (int) $value['hour'] ) {
            $value['hour'] = 12;
          } else if( $value['hour'] > 12 ) {
            $value['hour'] -= 12;
          }
        }
      }
    }


    if( !is_array($value) ) {
      $value = array();
    }

      $this->view->headScript()->appendFile($this->view->baseUrl() . '/application/modules/Core/externals/scripts/datetime.js');
      $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/application/modules/Core/externals/styles/datetimepicker.css');

    return
      '<div class="event_calendar_container" style="display:inline-block">'
      .'<i class="fa fa-calendar formIconCalendar" onclick="document.getElementById(\''.$name.'-date\').focus();" title="Выберите дату"></i>' .
        $this->view->formText($name . '[date]', @$value['date'], array_merge(['id' => $name . '-date', 'class' => 'calendar_new'], (array) @$attribs['dateAttribs'])) .

      '</div><script>jQuery(function($){
        $("#'.$name.'-date").datetimepicker({
            timepicker: false,
            format: "d.m.Y",
            formatDate: "d.m.Y",
            lang: "ru",
            scrollMonth:false
        });
      })</script>' .
      $this->view->formTime($name, $value, $attribs, $options)
      ;
  }
}