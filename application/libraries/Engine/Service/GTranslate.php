<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_GTranslate
 * @version    $Id: GTranslate.php 7533 2010-10-02 09:42:49Z john $
 */

/**
 * GTranslate
 * A class to comunicate with Google Translate(TM) Service
 * Google Translate(TM) API Wrapper
 * More info about Google(TM) service can be found on
 * http://code.google.com/apis/ajaxlanguage/documentation/reference.html
 * This code has no affiliation with Google (TM), its a PHP Library that allows
 * communication with public a API
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jose da Silva <jose@josedasilva.net>
 * @author John Boehr <j@webligo.com>
 * @license LGPLv3 http://www.gnu.org/licenses/lgpl.html
 *
 * <code>
 * <?php
 * try {
 *  $gt = new Engine_Service_GTranslate();
 *  echo $gt->query("en", "de", "hello world");
 * } catch( Engine_Service_GTranslate_Exception $e ) {
 *  echo $e->getMessage();
 * }
 * ?>
 * </code>
 */
class Engine_Service_GTranslate
{
  /**
   * Google Translate(TM) Api endpoint
   *
   * @access protected
   * @var string
   */
  protected $_url = "http://ajax.googleapis.com/ajax/services/language/translate";

  /**
   * Google Translate (TM) Api Version
   *
   * @access protected
   * @var string
   */
  protected $_apiVersion = "1.0";

  /**
   * Comunication Transport Method
   * Available: http / curl
   * 
   * @access protected
   * @var string
   */
  protected $_requestType = "http";

  /**
   * Holder to the parse of the ini file
   * 
   * @access protected
   * @var array
   */
  static protected $_availableLanguages;

  static protected $_knownLanguages = array(
    'AFRIKAANS' => 'af',
    'ALBANIAN' => 'sq',
    'AMHARIC' => 'am',
    'ARABIC' => 'ar',
    'ARMENIAN' => 'hy',
    'AZERBAIJANI' => 'az',
    'BASQUE' => 'eu',
    'BELARUSIAN' => 'be',
    'BENGALI' => 'bn',
    'BIHARI' => 'bh',
    'BULGARIAN' => 'bg',
    'BURMESE' => 'my',
    'BRETON' => 'br',
    'CATALAN' => 'ca',
    'CHEROKEE' => 'chr',
    'CHINESE' => 'zh',
    'CHINESE_SIMPLIFIED' => 'zh-CN',
    'CHINESE_TRADITIONAL' => 'zh-TW',
    'CORSICAN' => 'co',
    'CROATIAN' => 'hr',
    'CZECH' => 'cs',
    'DANISH' => 'da',
    'DHIVEHI' => 'dv',
    'DUTCH' => 'nl',
    'ENGLISH' => 'en',
    'ESPERANTO' => 'eo',
    'ESTONIAN' => 'et',
    'FAROESE' => 'fo',
    'FILIPINO' => 'tl',
    'FINNISH' => 'fi',
    'FRENCH' => 'fr',
    'FRISIAN' => 'fy',
    'GALICIAN' => 'gl',
    'GEORGIAN' => 'ka',
    'GERMAN' => 'de',
    'GREEK' => 'el',
    'GUJARATI' => 'gu',
    'HAITIAN_CREOLE' => 'ht',
    'HEBREW' => 'iw',
    'HINDI' => 'hi',
    'HUNGARIAN' => 'hu',
    'ICELANDIC' => 'is',
    'INDONESIAN' => 'id',
    'INUKTITUT' => 'iu',
    'IRISH' => 'ga',
    'ITALIAN' => 'it',
    'JAPANESE' => 'ja',
    'JAVANESE' => 'jw',
    'KANNADA' => 'kn',
    'KAZAKH' => 'kk',
    'KHMER' => 'km',
    'KOREAN' => 'ko',
    'KURDISH' => 'ku',
    'KYRGYZ' => 'ky',
    'LAO' => 'lo',
    'LAOTHIAN' => 'lo',
    'LATIN' => 'la',
    'LATVIAN' => 'lv',
    'LITHUANIAN' => 'lt',
    'LUXEMBOURGISH' => 'lb',
    'MACEDONIAN' => 'mk',
    'MALAY' => 'ms',
    'MALAYALAM' => 'ml',
    'MALTESE' => 'mt',
    'MAORI' => 'mi',
    'MARATHI' => 'mr',
    'MONGOLIAN' => 'mn',
    'NEPALI' => 'ne',
    'NORWEGIAN' => 'no',
    'OCCITAN' => 'oc',
    'ORIYA' => 'or',
    'PASHTO' => 'ps',
    'PERSIAN' => 'fa',
    'POLISH' => 'pl',
    'PORTUGUESE' => 'pt',
    'PORTUGUESE_PORTUGAL' => 'pt-PT',
    'PUNJABI' => 'pa',
    'QUECHUA' => 'qu',
    'ROMANIAN' => 'ro',
    'RUSSIAN' => 'ru',
    'SANSKRIT' => 'sa',
    'SCOTS_GAELIC' => 'gd',
    'SERBIAN' => 'sr',
    'SINDHI' => 'sd',
    'SINHALESE' => 'si',
    'SLOVAK' => 'sk',
    'SLOVENIAN' => 'sl',
    'SPANISH' => 'es',
    'SUNDANESE' => 'su',
    'SWAHILI' => 'sw',
    'SWEDISH' => 'sv',
    'SYRIAC' => 'syr',
    'TAJIK' => 'tg',
    'TAMIL' => 'ta',
    'TAGALOG' => 'tl',
    'TATAR' => 'tt',
    'TELUGU' => 'te',
    'THAI' => 'th',
    'TIBETAN' => 'bo',
    'TONGA' => 'to',
    'TURKISH' => 'tr',
    'UKRAINIAN' => 'uk',
    'URDU' => 'ur',
    'UZBEK' => 'uz',
    'UIGHUR' => 'ug',
    'VIETNAMESE' => 'vi',
    'WELSH' => 'cy',
    'YIDDISH' => 'yi',
    'YORUBA' => 'yo'
  );

  static protected $_unavailableLanguages = array(
    'AMHARIC' => 'am',
    'ARMENIAN' => 'hy',
    'AZERBAIJANI' => 'az',
    'BASQUE' => 'eu',
    'BENGALI' => 'bn',
    'BIHARI' => 'bh',
    'BRETON' => 'br',
    'BURMESE' => 'my',
    'CHEROKEE' => 'chr',
    'CORSICAN' => 'co',
    'DHIVEHI' => 'dv',
    'ESPERANTO' => 'eo',
    'FAROESE' => 'fo',
    'FRISIAN' => 'fy',
    'GEORGIAN' => 'ka',
    'GUJARATI' => 'gu',
    'INUKTITUT' => 'iu',
    'JAVANESE' => 'jw',
    'KANNADA' => 'kn',
    'KAZAKH' => 'kk',
    'KHMER' => 'km',
    'KURDISH' => 'ku',
    'KYRGYZ' => 'ky',
    'LAO' => 'lo',
    'LAOTHIAN' => 'lo',
    'LATIN' => 'la',
    'LUXEMBOURGISH' => 'lb',
    'MALAYALAM' => 'ml',
    'MAORI' => 'mi',
    'MARATHI' => 'mr',
    'MONGOLIAN' => 'mn',
    'NEPALI' => 'ne',
    'OCCITAN' => 'oc',
    'ORIYA' => 'or',
    'PASHTO' => 'ps',
    'PUNJABI' => 'pa',
    'QUECHUA' => 'qu',
    'SANSKRIT' => 'sa',
    'SCOTS_GAELIC' => 'gd',
    'SINDHI' => 'sd',
    'SINHALESE' => 'si',
    'SUNDANESE' => 'su',
    'SYRIAC' => 'syr',
    'TAJIK' => 'tg',
    'TAMIL' => 'ta',
    'TATAR' => 'tt',
    'TELUGU' => 'te',
    'TIBETAN' => 'bo',
    'TONGA' => 'to',
    'UIGHUR' => 'ug',
    'URDU' => 'ur',
    'UZBEK' => 'uz',
    'YORUBA' => 'yo'
  );
  
  static protected $_localeToLanguage = array(
    'he' => 'iw',
  );

  /**
   * Google Translate api key
   *
    * @access protected
   * @var string
   */
  protected $_apiKey = null;

  /**
   * Converts locale code to google equivalent
   * 
   * @param string $language
   * @return string
   */
  static public function getLanguage($language)
  {
    if( is_array($language) ) {
      return array_map(array('self', 'getLanguage'), $language);
    } else if( is_string($language) ) {
      $language = str_replace('_', '-', $language);
      if( isset(self::$_localeToLanguage[$language]) ) {
        $language = self::$_localeToLanguage[$language];
      }
      return $language;
    } else {
      throw new Engine_Service_GTranslate_Exception('Not a string or array of strings.');
    }
  }

  /**
   * Get available languages
   *
   * @param boolean $translateLocales
   * @return string
   */
  static public function getAvailableLanguages($translateLocales = true)
  {
    if( null === self::$_availableLanguages ) {
      self::$_availableLanguages = array_diff(self::$_knownLanguages, self::$_unavailableLanguages);
    }
    if( !$translateLocales ) {
      return self::$_availableLanguages;
    } else {
      $availableLanguages = array();
      $reverseTranslate = array_flip(self::$_localeToLanguage);
      foreach( self::$_availableLanguages as $availableLanguage ) {
        if( isset($reverseTranslate[$availableLanguage]) ) {
          $availableLanguage = $reverseTranslate[$availableLanguage];
        }
        $availableLanguages[] = $availableLanguage;
      }
      return $availableLanguages;
    }
  }

  /**
   * Check if the specified language is supported
   * 
   * @param string $language
   * @return boolean
   */
  static public function isAvailableLanguage($language)
  {
    if( is_array($language) ) {
      return count($language) == array_sum(array_map(array('self', 'isAvailableLanguage'), $language));
    } else if( is_string($language) ) {
      return in_array(self::getLanguage($language), self::getAvailableLanguages());
    } else {
      throw new Engine_Service_GTranslate_Exception('Not a string or array of strings.');
    }
  }

  /**
   * Test if the specified language is supported by sending a request to google
   *
   * @param string $language
   * @return boolean
   */
  static public function testAvailableLanguage($language)
  {
    $adapter = new self();
    if( extension_loaded('curl') ) {
      $adapter->setRequestType('curl');
    } else {
      $adapter->setRequestType('http');
    }

    if( !self::isAvailableLanguage($language) ) {
      return false;
    }

    try {
      $adapter->query('en', $language, 'test');
    } catch( Exception $e ) {
      var_dump($e->__toString());
      return false;
    }

    return true;
  }


  
  /**
   * Define the request type
   * 
   * @access public
   * @param string $type
   * @return self
   */
  public function setRequestType($type = 'http')
  {
    $method = '_request' . ucfirst($type);
    if( !method_exists($this, $method) ) {
      throw new Engine_Service_GTranslate_Exception('Request type is not available');
    }
    $this->_requestType = $type;
    return $this;
  }

  /**
   * Define the Google Translate Api Key
   * 
    * @access public
   * @param string $key
   * @return self
   */
  public function setApiKey($key)
  {
    $this->_apiKey = $key;
    return $this;
  }

  /**
   * Query the Google(TM) endpoint
   * 
   * @access protected
   * @param string $from Locale to translate from
   * @param string $to Locale to translate to
   * @param string|array $message The string to translate
   * @return string|array $translatedMessage
   * @throws Engine_Service_GTranslate_Exception On invalid parameters
   */
  public function query($from, $to, $message)
  {
    // Check data types
    if( !is_string($from) && !is_array($from) ) {
      throw new Engine_Service_GTranslate_Exception('Invalid data type given for from.');
    }
    if( !is_string($to) && !is_array($to) ) {
      throw new Engine_Service_GTranslate_Exception('Invalid data type given for to.');
    }
    if( !is_string($message) && !is_array($message) ) {
      throw new Engine_Service_GTranslate_Exception('Invalid data type given for message.');
    }
    if( is_array($message) && array_sum(array_map('is_string', $message)) != count($message) ) {
      throw new Engine_Service_GTranslate_Exception('Invalid data type given for message.');
    }
    
    // Check locales
    if( !self::isAvailableLanguage($from) ) {
      throw new Engine_Service_GTranslate_Exception('"From" language not available.');
    }
    if( !self::isAvailableLanguage($to) ) {
      throw new Engine_Service_GTranslate_Exception('"To" language not available.');
    }

    // Check array lengths
    if( is_array($from) && is_array($message) && count($from) != count($message) ) {
      throw new Engine_Service_GTranslate_Exception('Number of "from" languages does not match number of messages.');
    }
    if( is_array($to) && is_array($message) && count($to) != count($message) ) {
      throw new Engine_Service_GTranslate_Exception('Number of "to" languages does not match number of messages.');
    }
    
    // Translate locale
    $from = self::getLanguage($from);
    $to = self::getLanguage($to);
    
    // Process
    $method = '_request' . ucfirst($this->_requestType);
    $response = $this->$method(array(
      'v' => $this->_apiVersion,
      'from' => $from,
      'to' => $to,
      'q' => $message,
    ));

    // Validate
    $text = $this->_processResponse($response);

    return $text;
  }



  // Request methods

  /**
   * Query Wrapper for Http Transport
   * 
   * @access protected
   * @param array $args
   * @return string $response
   */
  protected function _requestHttp($args)
  {
    $url = $this->_url . '?' . $this->_buildQuery($args);
    return Zend_Json::decode(file_get_contents($url));
  }
  
  /**
   * Query Wrapper for Curl Transport
   * 
   * @access protected
   * @param array $args
   * @return string $response
   */
  protected function _requestCurl($args)
  {
    $query = $this->_buildQuery($args);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    $body = curl_exec($ch);
    curl_close($ch);

    return Zend_Json::decode($body);
  }


  
  // Utility

  /**
   * Build the query
   * 
   * @param array $args
   * @return string
   */
  protected function _buildQuery($args)
  {
    $from = $args['from'];
    $to = $args['to'];
    $q = $args['q'];
    unset($args['to']);
    unset($args['from']);
    unset($args['q']);

    // Fill in language pairs
    if( is_string($to) && !is_string($from) ) {
      $to = array_fill(0, count($from), $to);
    } else if( !is_string($to) && is_string($from) ) {
      $from = array_fill(0, count($to), $from);
    }

    // Langpair is string
    if( is_string($to) && is_string($from) ) {
      $args['langpair'] = $from . '|' . $to;
      if( !is_array($q) ) {
        $args['q'] = $q;
      }
      $query = http_build_query($args);
      if( is_array($q) ) {
        foreach( $q as $message ) {
          $query .= '&' . http_build_query(array('q' => $message));
        }
      }
    }

    // Message is string, langpair is not string
    else if( is_string($q) ) {
      $args['q'] = $q;
      $query = http_build_query($args);
      foreach( $to as $index => $oneTo ) {
        $query .= '&' . http_build_query(array('langpair' => $from[$index] . '|' . $to[$index]));
      }
    }

    // They're all arrays
    else
    {
      $query = http_build_query($args);
      foreach( $to as $index => $oneTo ) {
        $query .= '&' . http_build_query(array(
          'q' => $q[$index],
          'langpair' => $from[$index] . '|' . $to[$index]
        ));
      }
    }
    
    return $query;
  }

  /**
   * Response Evaluator, validates the response
   *
   * @access protected
   * @param array $response
   * @return string|array
   * @throws Engine_Service_GTranslate_Exception On error
   */
  protected function _processResponse($response)
  {
    switch( $response['responseStatus'] )
    {
      case 200:
        if( isset($response['responseData']['translatedText']) && is_string($response['responseData']['translatedText']) ) {
          return $response['responseData']['translatedText'];
        }

        if( is_array($response['responseData']) ) {
          $text = array();
          foreach( $response['responseData'] as $subResponse ) {
            $text[] = $this->_processResponse($subResponse);
          }
          return $text;
        }

        // What happened here?
        throw new Engine_Service_GTranslate_Exception("Unable to perform translation: something weird happened");
        
        break;
      default:
        throw new Engine_Service_GTranslate_Exception("Unable to perform translation: " . $response['responseDetails']);
      break;
    }
  }
}
