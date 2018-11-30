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
class Engine_Translate_Adapter_Array extends Zend_Translate_Adapter_Array
{
    protected $_subjectExtendTranslateAllowedTypes = array('olympic', 'school_entity');
    protected static $_translateSubject = null;

    public static function getTranslateSubject()
    {
        return self::$_translateSubject;
    }
    public static function setTranslateSubject($translateSubject)
    {
        self::$_translateSubject = $translateSubject;
    }
    
    protected function _subjectExtendTranslate($messageId, $locale, $result = null)
    {
        $subject = self::$_translateSubject ? self::$_translateSubject : ( Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null);
        if (!$subject){
            return false;
        }

        if ($subject instanceof Closure){
            return $subject($messageId, $locale, $result);
        }

        if ( in_array($subject->getType(), $this->_subjectExtendTranslateAllowedTypes) ){
              /* @var School_Model_Entity|Olympic_Model_Olympic $subject */
              return $subject->translateExtended($messageId, $locale, $result);
        }
        return false;
    }
    
    public function translate($messageId, $locale = null) {
        if ($locale === null) {
            $locale = $this->_options['locale'];
        }
        if ($extendedResult = $this->_subjectExtendTranslate($messageId, $locale )){
            return $extendedResult;
        };
        $result = parent::translate($messageId, $locale);
        /*if ($result==$messageId && preg_match('/[a-z]/i', $messageId)){
                $lines = explode("\n", file_get_contents('C:/OpenServer/testtranslate.txt'));
                if (!in_array($messageId, $lines))file_put_contents('C:/OpenServer/testtranslate.txt', $messageId . "\n", FILE_APPEND);
        }*/
        if ($extendedResult = $this->_subjectExtendTranslate($messageId, $locale, $result )){
            return $extendedResult;
        };
        return $result;
    }
}