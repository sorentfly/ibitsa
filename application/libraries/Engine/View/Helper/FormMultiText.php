<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormMultiText.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormMultiText extends Zend_View_Helper_FormText
{
  public function formMultiText($name, $value = null, $attribs = null)
  {
    if( null !== $value && is_scalar($value) ) {
      $value = array($value);
    } else if( !is_array($value) ) {
      $value = array();
    }
    $value = array_values($value);

    $separator = '<br class="multi-text-separator" />';
    $content = '';
    for( $i = 0, $l = max(count($value),2); $i < $l; $i++ ) {
      //if( $i !== 0 ) {
      //  $content .= $separator;
      //}
      $cAttr = $attribs;
      $cAttr['id'] = trim($name, '[]') . '-' . ($i + 1);
      $cVal = ( isset($value[$i]) ? $value[$i] : '' );
      $content .= $this->formText($name, $cVal, $cAttr);
      $content .= $separator;
      if (!empty($attribs['required'])) unset($attribs['required']);
    }

    // Add javascript for adding anothing text link
    // Add anchor for haxing
    $tName = trim($name, '[]');
    $content .= '<a href="javascript:void(0);" id="' . $tName . '" class="fa fa-plus" data-action="multitext-add-line" title="' . $this->view->translate('Add') . '"></a>';
    $script = <<<EOF
window.addEvent('domready', function() {
  var anchor = $('$tName');
  if( !anchor ) return;
  anchor.addEvent('click', function(event) {
    event.preventDefault();
    
    var ref = anchor.getParent();
    var children = ref.getChildren('input[type=text]');
    if( !anchor || !ref || !children || children.length == 0 ) {
      return false;
    }

    var child = children[0];
    child.clone().set('value', '').inject(anchor, 'before');
    (new Element('br', {class:'multi-text-separator'})).inject(anchor, 'before');
  });
});
EOF;
    $this->view->headScript()->appendScript($script);

    return $content;
  }
}