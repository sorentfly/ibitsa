<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Form_Admin_Manage_Filter extends Engine_Form
{
  public function init()
  {
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET')
      ;

    // Element: query
    /*
    $this->addElement('Text', 'query', array(
      'label' => 'Search',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
     * 
     */
    
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

    // Element: extension
    $extensions = $filesTable->select()
      ->from($filesTable, 'extension')
      ->distinct()
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
    if( !empty($extensions) ) {
      $multiOptions = array_merge(
        array('' => ''),
        array_combine($extensions, $extensions)
      );
      $this->addElement('Select', 'extension', array(
        'label' => 'Extension',
        'multiOptions' => $multiOptions,
        'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div')),
        ),
      ));
    }

    // Element: mime
    $mimes = $filesTable->select()
      ->from($filesTable, array('mime_major', 'mime_minor'))
      ->distinct()
      ->query()
      ->fetchAll();
    if( !empty($mimes) ) {
      $multiOptions = array('' => '');
      foreach( $mimes as $mime ) {
        $val = $mime['mime_major'] . '/' . $mime['mime_minor'];
        $multiOptions[$val] = $val;
      }
      $this->addElement('Select', 'mime', array(
        'label' => 'MIME Type',
        'multiOptions' => $multiOptions,
        'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div')),
        ),
      ));
    }
    
    // Element: type
    $types = $filesTable->select()
      ->from($filesTable, 'type')
      ->distinct()
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
    if( !empty($types) ) {
      $multiOptions = array('' => '(Everything)', 'none' => '(No Thumbnails)');
      foreach( $types as $type ) {
        if( !empty($type) ) {
          $multiOptions[$type] = trim(ucwords(str_replace(array('.', 'thumb'), array(' ', ''), $type)));
        }
      }
      $this->addElement('Select', 'type', array(
        'label' => 'Thumbnail Type',
        'multiOptions' => $multiOptions,
        'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div')),
        ),
      ));
    }
    
    // Element: order
    $this->addElement('Hidden', 'order', array(
      'order' => 10004,
    ));
    /*
    $this->addElement('Select', 'order', array(
      'label' => 'Order',
      'multiOptions' => array(
        '' => '',
        'transaction_id' => 'ID',
        'user_id' => 'Member ID',
        'gateway_id' => 'Gateway ID',
        'type' => 'Type',
        'state' => 'State',
        'amount' => 'Amount',
        'timestamp' => 'Date',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
     *
     */

    // Element: direction
    $this->addElement('Hidden', 'direction', array(
      'order' => 10005,
    ));
    /*
    $this->addElement('Select', 'direction', array(
      'label' => 'Direction',
      'multiOptions' => array(
        '' => '',
        'ASC' => 'A-Z',
        'DESC' => 'Z-A',
      ),
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));
     *
     */

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Search',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
        array('HtmlTag2', array('tag' => 'div')),
      ),
    ));
  }
}