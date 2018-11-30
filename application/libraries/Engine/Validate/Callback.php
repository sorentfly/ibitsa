<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Validate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Callback.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Validate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Validate_Callback extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID        => "Please enter a valid value"
    );

    /**
     * Callback in a call_user_func format
     *
     * @var string|array
     */
    protected $_callback = null;

    /**
     * Default options to set for the filter
     *
     * @var mixed
     */
    protected $_options = null;

    /**
     * Constructor
     *
     * @param string|array $callback Callback in a call_user_func format
     * @param mixed        $options  (Optional) Default options for this filter
     */
    public function __construct($callback, $options = null)
    {
        $this->setCallback($callback);
        $this->setOptions($options);
    }

    /**
     * Returns the set callback
     *
     * @return string|array Set callback
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * Sets a new callback for this validator
     *
     * @param callback $callback
     * @return Engine_Validate_Callback
     */
    public function setCallback($callback, $options = null)
    {
        if (!is_callable($callback)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Callback can not be accessed');
        }

        $this->_callback = $callback;
        $this->setOptions($options);
        return $this;
    }

    /**
     * Returns the set default options
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets new default options to the callback filter
     *
     * @param mixed $options Default options to set
     * @return Engine_Validate_Callback
     */
    public function setOptions($options)
    {
        if (is_array($options) && isset($options['message'])){
            $this->_messageTemplates[self::INVALID] = $options['message'];
            unset($options['message']);
        }
        $this->_options = $options;
        return $this;
    }

    /**
     * Check if value is valid by callback
     * 
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
      $options = array();

        if ($this->_options !== null) {
            if (!is_array($this->_options)) {
                $options = array($this->_options);
            } else {
                $options = $this->_options;
            }
        }

        array_unshift($options, $value);
        
        $valid = (bool) call_user_func_array($this->_callback, $options);
        if( !$valid ){
          $this->_error(self::INVALID);
        }

        return $valid;
    }
}