<?
class Abitu_Form_Decorator_FormSchoolName extends Zend_Form_Decorator_Abstract
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
            $country = Zend_Registry::get('school_country');
            $region= Zend_Registry::isRegistered('school_region') ? Zend_Registry::get('school_region') : null;
            $cityName = Zend_Registry::get('school_city_value');
            $items.="<SCRIPT>"
                    . "var {$elementId}loadPopup = function(e){if (!jQuery('#result_list_{$elementId} ul li').length) cityTextchange('{$country}', '{$region}', '{$cityName}', document.getElementById('result_list_school_city'), function(){ changeSchoolsList('{$country}', jQuery('#school_city').attr('data-id')); }); jQuery('#{$elementId}').parent().off('mousedown', {$elementId}loadPopup); }; "
                    . "jQuery(document).ready(function(){ jQuery('#{$elementId}').parent().on('mousedown', {$elementId}loadPopup );  });"
                    . "</SCRIPT>";
        }
        
        $items .= '<li onmousedown="selectSchool(0, \'\');" onmouseover="highlightItem(event);">' . Zend_Registry::get('Zend_Translate')->_('_NOT_SPECIFIED_M') . '</li>';
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
                                '<input autocomplete="nope"  class="school" data-id="' . $current_value['id'] . '" id="' . $elementId . '" name="' . $elementName . '" placeholder="' . Zend_Registry::get('Zend_Translate')->_('_NOT_SPECIFIED_M') . '" type="text" value="' . htmlspecialchars($this->getElement()->getValue()) . '"'.$attribs.'/>' .
                            $combobox_button . '</div>' .
                            $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>' .
                        '</div>';
        return $wrapper;
        
        

    }
}