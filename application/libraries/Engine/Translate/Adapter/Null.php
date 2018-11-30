<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Null.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Adapter_Null extends Zend_Translate_Adapter
{
  protected $_automatic = true;

  protected $_extToParser = array(
    'php' => 'Engine_Translate_Parser_Array',
    'csv' => 'Engine_Translate_Parser_Csv',
    'mo'  => 'Engine_Translate_Parser_Gettext', // Double check extension
    'ini' => 'Engine_Translate_Parser_Ini'
  );

  public function __construct($options)
  {
    $this->setOptions($options);
    $locale = $this->getLocale();
    if (($locale === "auto") or ($locale === null)) {
        $this->_automatic = true;
    } else {
        $this->_automatic = false;
    }
  }

  public function setTranslationData($data)
  {
    $this->_translate = $data;
    if ($this->_automatic === true) {
        $find = new Zend_Locale();
        $browser = $find->getEnvironment() + $find->getBrowser();
        arsort($browser);
        foreach($browser as $language => $quality) {
            if (isset($this->_translate[$language])) {
                $this->_options['locale'] = $language;
                break;
            }
        }
    }
    return $this;
  }

  public function getTranslationData()
  {
    return $this->_translate;
  }

  protected function _addTranslationData($data, $locale, array $options = array())
  {
    try {
        $locale    = Zend_Locale::findLocale($locale);
    } catch (Zend_Locale_Exception $e) {
        require_once 'Zend/Translate/Exception.php';
        throw new Zend_Translate_Exception("The given Language '{$locale}' does not exist");
    }

    if ($options['clear'] || !isset($this->_translate[$locale])) {
        $this->_translate[$locale] = array();
    }
    
    $ext = strtolower(ltrim(strrchr($data, '.'), '.'));
    if( !isset($this->_extToParser[$ext]) )
    {
      return false;
    }
    $class = $this->_extToParser[$ext];
    
    $temp = call_user_func(array($class, 'parse'), $data, $locale, $options);

    if (empty($temp)) {
      $temp = array();
    }

    $keys = array_keys($temp);
    foreach($keys as $key)
    {
      if (!isset($this->_translate[$key])) {
          $this->_translate[$key] = array();
      }

      $this->_translate[$key] = $temp[$key] + $this->_translate[$key];
    }
    
    if ($this->_automatic === true) {
        $find = new Zend_Locale($locale);
        $browser = $find->getEnvironment() + $find->getBrowser();
        arsort($browser);
        foreach($browser as $language => $quality) {
            if (isset($this->_translate[$language])) {
                $this->_options['locale'] = $language;
                break;
            }
        }
    }

    return $this;
  }

  protected function _loadTranslationData($data, $locale, array $options = array())
  {
    return $data;
  }

  public function toString()
  {
    'Null';
  }






  // Copy and paste me


    /**
     * Add translation data
     *
     * It may be a new language or additional data for existing language
     * If $clear parameter is true, then translation data for specified
     * language is replaced and added otherwise
     *
     * @param  array|string       $data    Translation data
     * @param  string|Zend_Locale $locale  (optional) Locale/Language to add data for, identical
     *                                        with locale identifier, see Zend_Locale for more information
     * @param  array              $options (optional) Option for this Adapter
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function addTranslation($data, $locale = null, array $options = array())
    {
        try {
            $locale    = Zend_Locale::findLocale($locale);
        } catch (Zend_Locale_Exception $e) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("The given Language '{$locale}' does not exist");
        }

        $originate = (string) $locale;

        $this->setOptions($options);
        if (is_string($data) and is_dir($data)) {
            $data = realpath($data);
            $prev = '';
            foreach (new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($data, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                     RecursiveIteratorIterator::SELF_FIRST) as $directory => $info) {
                $file = $info->getFilename();
                if (strpos($directory, DIRECTORY_SEPARATOR . $this->_options['ignore']) !== false) {
                    // ignore files matching first characters from option 'ignore' and all files below
                    continue;
                }

                if ($info->isDir()) {
                    // pathname as locale
                    if (($this->_options['scan'] === self::LOCALE_DIRECTORY) and (Zend_Locale::isLocale($file, true, false))) {
                        if (strlen($prev) <= strlen($file)) {
                            $locale = $file;
                            $prev   = (string) $locale;
                        }
                    }
                } else if ($info->isFile()) {
                    // filename as locale
                    if ($this->_options['scan'] === self::LOCALE_FILENAME) {
                        $filename = explode('.', $file);
                        array_pop($filename);
                        $filename = implode('.', $filename);
                        if (Zend_Locale::isLocale((string) $filename, true, false)) {
                            $locale = (string) $filename;
                        } else {
                            $parts  = explode('.', $file);
                            $parts2 = array();
                            foreach($parts as $token) {
                                $parts2 += explode('_', $token);
                            }
                            $parts  = array_merge($parts, $parts2);
                            $parts2 = array();
                            foreach($parts as $token) {
                                $parts2 += explode('-', $token);
                            }
                            $parts = array_merge($parts, $parts2);
                            $parts = array_unique($parts);
                            $prev  = '';
                            foreach($parts as $token) {
                                if (Zend_Locale::isLocale($token, true, false)) {
                                    if (strlen($prev) <= strlen($token)) {
                                        $locale = $token;
                                        $prev   = $token;
                                    }
                                }
                            }
                        }
                    }
                    try {
                        $this->_addTranslationData($info->getPathname(), (string) $locale, $this->_options);
                        if ((isset($this->_translate[(string) $locale]) === true) and (count($this->_translate[(string) $locale]) > 0)) {
                            $this->setLocale($locale);
                        }
                    } catch (Zend_Translate_Exception $e) {
                        // ignore failed sources while scanning
                    }
                }
            }
        } else {
            $this->_addTranslationData($data, (string) $locale, $this->_options);
            if ((isset($this->_translate[(string) $locale]) === true) and (count($this->_translate[(string) $locale]) > 0)) {
                $this->setLocale($locale);
            }
        }

        if ((isset($this->_translate[$originate]) === true) and (count($this->_translate[$originate]) > 0) and ($originate !== (string) $locale)) {
            $this->setLocale($originate);
        }

        return $this;
    }
}