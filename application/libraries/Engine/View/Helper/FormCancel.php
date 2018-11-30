<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormCancel.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormCancel extends Zend_View_Helper_FormElement
{
    public function formCancel($name, $value = null, $attribs = null)
    {
        $info    = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // Is a link?
        $link = false;
        if( isset($attribs['link']) ) {
          $link = true;
          unset($attribs['link']);
        }

        // onclick
        $onclick = null;
        if( isset($attribs['onclick']) ) {
          $onclick = $attribs['onclick'];
          unset($attribs['onclick']);
        }

        // Href
        $href = null;
        if( isset($attribs['href']) ) {
          $href = $attribs['href'];
          unset($attribs['href']);
        }

        // Get content
        $content = '';
        if (isset($attribs['content'])) {
            $content = $attribs['content'];
            unset($attribs['content']);
        } else {
            $content = $value;
        }

        // Ensure type is sane
        $type = 'button';

        $content = ($escape) ? $this->view->escape($content) : $content;

        $xhtml = '';

        if( isset($attribs['prependText']) ) {
          $xhtml .= $this->view->translate($attribs['prependText']);
          unset($attribs['prependText']);
        }
        
        // Render as button
        if( !$link ) {

          if( $href && $onclick ) {
            // throw away href
            $attribs['onclick'] = $onclick;
          } else if( !$href && $onclick ) {
            $attribs['onclick'] = $onclick;
          } else if( $href && !$onclick ) {
            $attribs['onclick'] = 'window.location.href = "'.$this->view->escape($href).'";';
          } else {
            $attribs['onclick'] = 'history.go(-1); return false;';
          }
          
          $xhtml .= '<button'
                  . ' name="' . $this->view->escape($name) . '"'
                  . ' id="' . $this->view->escape($id) . '"'
                  . ' type="' . $type . '"';

          // add a value if one is given
          if (!empty($value)) {
              $xhtml .= ' value="' . $this->view->escape($value) . '"';
          }

          // add attributes and close start tag
          $xhtml .= $this->_htmlAttribs($attribs) . '>';

          // add content and end tag
          $xhtml .= $content . '</button>';
        }

        // Render as link
        else
        {
          if( $href && $onclick ) {
            // throw away href
            $attribs['href'] = $href;
            $attribs['onclick'] = $onclick;
          } else if( !$href && $onclick ) {
            $attribs['href'] = 'javascript:void(0);';
            $attribs['onclick'] = $onclick;
          } else if( $href && !$onclick ) {
            $attribs['href'] = $href;
          } else {
            $attribs['href'] = 'javascript:void(0);';
            $attribs['onclick'] = 'history.go(-1); return false;';
          }
          
          $xhtml .= '<a'
                  . ' name="' . $this->view->escape($name) . '"'
                  . ' id="' . $this->view->escape($id) . '"'
                  . ' type="' . $type . '"';
          
          // add attributes and close start tag
          $xhtml .= $this->_htmlAttribs($attribs) . '>';

          // add content and end tag
          $xhtml .= $content . '</a>';
        }


        return $xhtml;
    }
}