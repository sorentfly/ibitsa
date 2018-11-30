<?
class Abitu_Form_Decorator_FormRegion extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden')
        {
             $style = 'style="display:none;"';
        }else{
            $style = '';
        }
        
        $elementName = $this->getElement()->getname();
        $elementId = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $current_value = array('id' => '', 'value' => $this->getElement()->getValue());
        
        $items = '<div class="result_list" id="result_list_' . $elementId . '" style="display:none;"><ul>';
        if(Zend_Registry::isRegistered($elementId . '_multioptions'))
        {
            $loadCountry = (int)(Zend_Registry::get($elementId . '_multioptions'));
            $items.="<SCRIPT>"
                    . "var {$elementId}loadPopup = function(e){if (!jQuery('#result_list_{$elementId} ul li').length) changeRegionsList({$loadCountry}, '{$elementId}', true);  jQuery('#{$elementId}').parent().off('mousedown', {$elementId}loadPopup); }; "
                    . "jQuery(document).ready(function(){ jQuery('#{$elementId}').parent().on('mousedown', {$elementId}loadPopup );  });"
                    . "</SCRIPT>";
        }
        $regionVal = Zend_Registry::isRegistered($elementId) ? (int)Zend_Registry::get($elementId) : 0;
        
        $items .= '<li onmousedown="selectRegion(\'\', this);" onmouseover="highlightItem(event);">' . Zend_Registry::get('Zend_Translate')->_('_NOT_SPECIFIED_M') . '</li>';
        
        
        $items .= '</ul></div>';

        $attribs = '';
        foreach ($this->getElement()->getAttribs() as $key=>$attrib)
        {
            $attribs .= " $key='".htmlspecialchars($attrib)."'";
        }

        $combobox_button = '<div class="custom-combobox-toggle" id="toggle_' . $elementId . '"></div>';
        
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                            '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                            '<div class="form-element custom-combobox">' .
                                '<input autocomplete="off"  class="region" data-id="' . $regionVal . '" id="' . $elementId . '" name="' . $elementName . '" placeholder="' . Zend_Registry::get('Zend_Translate')->_('_NOT_SPECIFIED_M') . '" readonly="true" type="text" value="' . $this->getElement()->getValue() . '"'.$attribs.'/>' .
                            $combobox_button . '</div>' .
                            $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>' .
                        '</div>';
        return $wrapper;
        
        

    }
}