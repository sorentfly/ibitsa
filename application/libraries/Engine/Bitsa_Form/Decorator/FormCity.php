<?
class Bitsa_Form_Decorator_FormCity extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    private $tb_prefix;
    private $db;
    
    public function render($content)
    {
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
        
        if($this->getElement()->getAttrib('class') == 'hidden')
        {
            $style = 'style="display:none;"';
        }else{
            $style = '';
        }
        $elementName = $this->getElement()->getName();
        $elementId = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $current_value = $this->getElement()->getValue();
        Zend_Registry::set($elementId . '_value', $this->getElement()->getValue());
        
        if(Zend_Registry::isRegistered($elementId))
        {
            $current_value_id = Zend_Registry::get($elementId);
        }else{
            $current_value_id = trim($current_value) == 'Москва' ? 1 : ( trim($current_value) == 'Санкт-Петербург' ? 2 : 0);
        }
        
        $items = '<div class="result_list" id="result_list_' . $elementId . '" style="display:none;"><ul>';
        if(Zend_Registry::isRegistered($elementId . '_multioptions'))
        {
            $loadC_R = explode('_', Zend_Registry::get($elementId . '_multioptions'));
            $country = (int)$loadC_R[0];
            $region = (int)$loadC_R[1];
            
            $items.="<SCRIPT>"
                    . "var {$elementId}loadPopup = function(e){ if (!jQuery('#{$elementId}').is(':visible')) return; if (!jQuery('#result_list_{$elementId} ul li').length) changeCitiesList({$country}, {$region}, '{$elementId}', true);  jQuery('#{$elementId}').parent().off('mousedown', {$elementId}loadPopup); }; "
                    . "jQuery(document).ready(function(){ jQuery('#{$elementId}').parent().on('mousedown', {$elementId}loadPopup );  });"
                    . "</SCRIPT>";
        }
        
        $items .= '</ul></div>';

        $attribs = '';
        foreach ($this->getElement()->getAttribs() as $key=>$attrib)
        {
            $attribs .= " $key='".htmlspecialchars($attrib)."'";
        }

        $combobox_button = '<a class="custom-combobox-toggle" id="toggle_' . $elementId . '"></a>';
        
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                '<div class="form-element custom-combobox">' .                
                '<input autocomplete="nope" class="city" data-id="' . $current_value_id . '" id="' . $elementId . '" maxlength="128" name="' . $elementName . '" placeholder="' . Zend_Registry::get('Zend_Translate')->_('_NONE_SELECTED_M') . '" type="text" value="' . $current_value . '"'.$attribs.'/>' .
                $combobox_button . '</div>';
        $wrapper .= $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>';
        $wrapper .= '</div>';
        return $wrapper;
                    
    }

}
