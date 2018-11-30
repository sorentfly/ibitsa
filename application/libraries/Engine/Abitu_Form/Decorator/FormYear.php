<?
class Abitu_Form_Decorator_FormYear extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $element_id = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        if($this->getElement()->getAttrib('class') == 'hidden')
        {            
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
        
        $items = '<div class="result_list" id="result_list_' . $element_id . '" style="display:none;"><ul>';
         
        $items .= '<li onmousedown="selectItem(event);" onmouseover="highlightItem(event);">' . $translate->_('_NOT_SPECIFIED_M') . '</li>';
        
        for($year = 1970; $year < date('Y') + 20; $year++)
        {            
            $items .= '<li onmousedown="selectItem(event);" onmouseover="highlightItem(event);">' . $year . '</li>';
        }
        $items .= '</ul></div>';

        $attribs = '';
        foreach ($this->getElement()->getAttribs() as $key=>$attrib)
        {
            $attribs .= " $key='".htmlspecialchars($attrib)."'";
        }

        $combobox_button = '<div class="custom-combobox-toggle" id="toggle_' . $element_id . '"></div>';
        
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                '<div class="form-label"><label for="' . $element_id . '">' . $label . '</label></div>' .
                '<div class="form-element custom-combobox">'
                . '<input autocomplete="off" id="' . $element_id . '" name="' . $this->getElement()->getName() . '" placeholder="' . $translate->_('_NOT_SPECIFIED_M') . '" readonly="true" type="text" value="' . $this->getElement()->getValue() .'"'.$attribs.'/>'
                . $combobox_button . '</div>';
        $wrapper .= $items . '<div class="form-element field_correct" id="' . $element_id . '_correct"></div>';
        $wrapper .= '</div>';
        
        return $wrapper;
                    
    }

}
