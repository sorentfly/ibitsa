<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: HtmlSpecialChars.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Filter_HtmlSpecialChars implements Zend_Filter_Interface
{
    /**
     * Corresponds to the second htmlentities() argument
     *
     * @var integer
     */
    protected $_quoteStyle;

    /**
     * Corresponds to the third htmlentities() argument
     *
     * @var string
     */
    protected $_charSet;

    /**
     * Corresponds to the forth htmlentities() argument
     *
     * @var unknown_type
     */
    protected $_doubleQuote;

    /**
     * Sets filter options
     *
     * @param  integer|array $quoteStyle
     * @param  string  $charSet
     * @return void
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            trigger_error('Support for multiple arguments is deprecated in favor of a single options array', E_USER_NOTICE);
            $options = func_get_args();
            $temp['quotestyle'] = array_shift($options);
            if (!empty($options)) {
                $temp['charset'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!isset($options['quotestyle'])) {
            $options['quotestyle'] = ENT_COMPAT;
        }

        if (!isset($options['charset'])) {
            $options['charset'] = 'UTF-8'; //'ISO-8859-1';
        }

        if (!isset($options['doublequote'])) {
            $options['doublequote'] = true;
        }

        $this->setQuoteStyle($options['quotestyle']);
        $this->setCharSet($options['charset']);
        $this->setDoubleQuote($options['doublequote']);
    }

    /**
     * Returns the quoteStyle option
     *
     * @return integer
     */
    public function getQuoteStyle()
    {
        return $this->_quoteStyle;
    }

    /**
     * Sets the quoteStyle option
     *
     * @param  integer $quoteStyle
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setQuoteStyle($quoteStyle)
    {
        $this->_quoteStyle = $quoteStyle;
        return $this;
    }

    /**
     * Returns the charSet option
     *
     * @return string
     */
    public function getCharSet()
    {
        return $this->_charSet;
    }

    /**
     * Sets the charSet option
     *
     * @param  string $charSet
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setCharSet($charSet)
    {
        $this->_charSet = $charSet;
        return $this;
    }

    /**
     * Returns the doubleQuote option
     *
     * @return boolean
     */
    public function getDoubleQuote()
    {
        return $this->_doubleQuote;
    }

    /**
     * Sets the doubleQuote option
     *
     * @param boolean $doubleQuote
     * @return Zend_Filter_HtmlEntities Provides a fluent interface
     */
    public function setDoubleQuote($doubleQuote)
    {
        $this->_doubleQuote = (boolean) $doubleQuote;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, converting characters to their corresponding HTML entity
     * equivalents where they exist
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        // Webligo PHP 5.1 compat
        if( version_compare(PHP_VERSION, '5.2.3', '<') ) {
          return htmlspecialchars((string) $value, $this->_quoteStyle, $this->_charSet);
        } else {
          return htmlspecialchars((string) $value, $this->_quoteStyle, $this->_charSet, $this->_doubleQuote);
        }
    }
}
