<?
class Abitu_Form_Decorator_FormCountry extends Zend_Form_Decorator_Abstract
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
        
        $elementId = $this->getElement()->getId();
        $elementName = $this->getElement()->getName();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $current_value = array('id' => '', 'value' => $this->getElement()->getValue());
        
        if(Zend_Registry::isRegistered($elementId . '_multioptions'))
        {
            $multioptions = Zend_Registry::get($elementId . '_multioptions');
        }else{
            $multioptions = array();
        }
        
        $items = '<div class="result_list" id="result_list_' . $elementId . '" style="display:none;"><ul>';
        $i = 0;
        foreach ($multioptions as $key => $value)
        {
            if($current_value['value'] === '')
            {
                $current_value = array('id' => $key, 'value' => trim($value) );
                $items .= '<li onmousedown="selectCountry(' . ($key ? $key : "''") . ', this);" onmouseover="highlightItem(event);">' . $value . '</li>';
                $i++;
                continue;
            }
            
            if ($current_value['id'] === '' && $current_value['value'] == $value)
            {
                $current_value['id'] = $key;
                $items .= '<li class="bold" onmousedown="selectCountry(' . ($key ? $key : "''") . ', this);" onmouseover="highlightItem(event);">' . $value . '</li>';
                $i++;
                continue;
            }
            
            $items .= '<li onmousedown="selectCountry(' . ($key ? $key : "''") . ', this);" onmouseover="highlightItem(event);">' . $value . '</li>';
            $i++;
        }
        
        $items .= '</ul></div>';

        $attribs = '';
        foreach ($this->getElement()->getAttribs() as $key=>$attrib)
        {
            $attribs .= " $key='".htmlspecialchars($attrib)."'";
        }
        $combobox_button = '<div class="custom-combobox-toggle" id="toggle_' . $elementId . '"></div>';
        
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                '<div class="form-element custom-combobox">'
                . '<input autocomplete="nope" class="country" data-id="' . $current_value['id'] . '" id="' . $elementId . '" maxlength="128" name="' . $elementName . '" placeholder="' . Zend_Registry::get('Zend_Translate')->_('Country') . '" type="text" value="' . $current_value['value'] . '"'.$attribs.'/>'
                . $combobox_button . '</div>';
        $wrapper .= $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>';
        $wrapper .= '</div>';        
        return $wrapper;
    }
}