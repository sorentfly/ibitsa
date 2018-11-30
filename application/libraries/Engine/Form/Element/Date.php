<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Date.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_Date extends Zend_Form_Element_Xhtml
{
  public $helper = 'formDate';

  protected $_yearMin;

  protected $_yearMax;

  protected $_dayOptions;

  protected $_monthOptions;

  protected $_yearOptions;

  public function setMultiOptions($options)
  {
    // @todo
    return $this;
  }

  public function getMultiOptions()
  {
    if( is_null($this->options) )
    {
      $this->options = array(
        'day' => $this->getDayOptions(),
        'month' => $this->getMonthOptions(),
        'year' => $this->getYearOptions()
      );
    }
    return $this->options;
  }

  public function getDayOptions()
  {
    if( is_null($this->_dayOptions) )
    {
      //if( $this->getAllowEmpty() ) {
        $this->_dayOptions[0] = ' ';
      //}
      for( $i = 1 ; $i<=31; $i++ )
      {
        $this->_dayOptions[$i] = $i;
      }
    }
    return $this->_dayOptions;
  }

  public function getMonthOptions()
  {
    if( is_null($this->_monthOptions) )
    {
      //if( $this->getAllowEmpty() ) {
        $this->_monthOptions[0] = ' ';
      //}
      for( $i = 1 ; $i<=12; $i++ )
      {
        $this->_monthOptions[$i] = $i;
      }
    }
    return $this->_monthOptions;
  }

  public function getYearOptions()
  {
    if( is_null($this->_yearOptions) )
    {
      //if( $this->getAllowEmpty() ) {
        $this->_yearOptions[0] = ' ';
      //}
      for( $i = $this->getYearMax(), $m = $this->getYearMin(); $i>$m; $i-- )
      {
        $this->_yearOptions[$i] = (string) $i;
      }
    }
    return $this->_yearOptions;
  }

  public function setYearMin($min)
  {
    $this->_yearMin = (int) $min;
    return $this;
  }

  public function getYearMin()
  {
    // Default is 100 years ago
    if( is_null($this->_yearMin) )
    {
      $date = new Zend_Date();
      $this->_yearMin = (int) $date->get(Zend_Date::YEAR) - 100;
    }
    return $this->_yearMin;
  }

  public function setYearMax($max)
  {
    $this->_yearMax = $max;
    return $this;
  }

  public function getYearMax()
  {
    // Default is this year
    if( is_null($this->_yearMax) )
    {
      $date = new Zend_Date();
      $this->_yearMax = (int) $date->get(Zend_Date::YEAR);
    }
    return $this->_yearMax;
  }

  public function setValue($value)
  {
    if( is_array($value) )
    {
        $value = $value['year'].'-'.$value['month'].'-'.$value['day'];
        if ($value == "0-0-0")
        {
          return parent::setValue(NULL);
        }
    }
    return parent::setValue($value);
  }

  public function getValue()
  {
    return parent::getValue();
  }
  
  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
    if( $this->loadDefaultDecoratorsIsDisabled() )
    {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) )
    {
      $this->addDecorator('ViewHelper');
      Engine_Form::addDefaultDecorators($this);
    }
  }
}