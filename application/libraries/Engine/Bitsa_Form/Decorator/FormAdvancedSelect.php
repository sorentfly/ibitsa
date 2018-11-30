<?
class Bitsa_Form_Decorator_FormAdvancedSelect extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $elementId = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        $value = $this->getElement()->getValue();
        
        if($this->getElement()->getAttrib('class') == 'hidden')
        {            
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
        
        switch($elementId)
        {
            case 'university_faculty':
            {
                if(Zend_Registry::isRegistered('university_name') && Zend_Registry::get('university_name') !== '')
                {
                    $style = '';
                }
                $onmousedown_function_name = 'selectFaculty';
                $placeholder = $translate->_('_NONE_SELECTED_M');
            }
                break;
            case 'university_mode_study':
            {
                if(Zend_Registry::isRegistered('university_name') && Zend_Registry::get('university_name') !== '')
                {
                    $style = '';
                }
                $onmousedown_function_name = 'selectStudyMode';
                $placeholder = $translate->_('_NONE_SELECTED_F');
            }
                break;
            case 'university_current_status':
            {
                if(Zend_Registry::isRegistered('university_name') && Zend_Registry::get('university_name') !== '')
                {
                    $style = '';
                }
                $onmousedown_function_name = 'selectCurrentStatus';
                $placeholder = $translate->_('_NONE_SELECTED_M');
            }
                break;
            
            default:
            {
                $onmousedown_function_name = 'selectItem';
                $placeholder = $translate->_('_NOT_SPECIFIED_M');
            }
        }
        
        $multioptions = $this->getElement()->getMultiOptions();
        
        $items = '<div class="result_list" id="result_list_' . $elementId . '" style="display:none;"><ul>';
               
        foreach ($multioptions as $key => $val)
        {            
            $items .= '<li onmousedown="selectItem(event, ' . $key . ');" onmouseover="highlightItem(event);">' . $val . '</li>';
        }
        $items .= '</ul></div>';
        
        $combobox_button = '<a class="custom-combobox-toggle" id="toggle_' . $elementId . '"></a>';
        if(array_key_exists($value, $multioptions)){
        	$displayValue = $multioptions[$value];
        } else {
        	$displayValue = '';
        }
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                '<div class="form-element custom-combobox">'
                . '<input autocomplete="nope" data-id="'. $value .'" id="' . $elementId . '" name="' . $elementId . '" placeholder="' . $placeholder . '" readonly="true" type="text" value="' . $displayValue .'"/>'
                . $combobox_button . '</div>';
        $wrapper .= $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>';
        $wrapper .= '</div>';
        
        return $wrapper;
                    
    }

}
