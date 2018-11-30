<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormTinyMce.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormTinyMce extends Zend_View_Helper_FormTextarea
{
    protected $_tinyMce;

    public function formTinyMce($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);
        /** @var  $attribs array */
        /** @var  $options array|null */
        /** @var  $id string */
        /** @var  $name string */
        /** @var  $value string */
        /** @var  $listsep string|null */
        /** @var  $disable bool */
        /** @var  $escape bool */
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        if( Zend_Registry::isRegistered('Locale') ) {
          $locale = Zend_Registry::get('Locale');
          if( method_exists($locale, '__toString') ) {
            $locale = $locale->__toString();
          } else {
            $locale = (string) $locale;
          }
          $localeData = Zend_Locale_Data::getList($locale, 'layout');
          $directionality = ( @$localeData['characters'] == 'right-to-left' ? 'rtl' : 'ltr' );

          $this->view->tinyMce()->language = $locale;
          $this->view->tinyMce()->directionality = $directionality;
        }
        $this->view->tinyMce()->identity = $info['id'];

        if (!empty($attribs['renderJSFunction'])) {
            $this->view->tinyMce()->setRenderJSFunction($attribs['renderJSFunction']);
            unset($attribs['renderJSFunction']);
        }else{
            $this->view->tinyMce()->setRenderJSFunction('initTinymce_full');
        }
        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->rows;
        }
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->cols;
        }
        if (isset($attribs['editorOptions'])) {
            if ($attribs['editorOptions'] instanceof Zend_Config) {
                $attribs['editorOptions'] = $attribs['editorOptions']->toArray();
            }
            $this->view->tinyMce()->setOptions($attribs['editorOptions']);
            unset($attribs['editorOptions']);
        }
        $this->view->tinyMce()->render();
        /* NOTE bitsa: it is very important to keep autocomplete="off" attribute, otherwise value can remember without saving */
        $xhtml = '<textarea rows=24, cols=80, style="width:553px;"  autocomplete="off" name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs) . '>'
                . $this->view->escape($value) . '</textarea>';

        return $xhtml;
    }
}
