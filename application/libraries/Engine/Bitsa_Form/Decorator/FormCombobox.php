<?

class Bitsa_Form_Decorator_FormCombobox extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;

    public function render($content)
    {
        if ($this->getElement()->getAttrib('class') == 'hidden' && $_SESSION['mobile']['mobile'] !== true) {
            $style = 'style="display:none;"';
        }else{
            $style = '';
        }
        $elementId = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $current_value = $this->getElement()->getValue();

        switch ($elementId) {
            case 'school_name': {
                $placeholder = Zend_Registry::get('Zend_Translate')->_('_NONE_SELECTED_F');
                $onmousedown_function_name = 'selectSchool';
            }
                break;
            case 'university_name': {
                $placeholder = Zend_Registry::get('Zend_Translate')->_('_NONE_SELECTED_M');
                $onmousedown_function_name = 'selectUniversity';
            }
                break;
            case 'university_major': {
                $placeholder = Zend_Registry::get('Zend_Translate')->_('_NONE_SELECTED_F');
                $onmousedown_function_name = 'selectChair';
            }
                break;
            default: {
                $placeholder = Zend_Registry::get('Zend_Translate')->_('_NONE_SELECTED_M');
                $onmousedown_function_name = 'selectItem';
            }
        }

        if (Zend_Registry::isRegistered($elementId . '_multioptions')) {
            $multioptions = Zend_Registry::get($elementId . '_multioptions');
        }else{
            $multioptions = array();
        }

        $items = '<div class="result_list" id="result_list_' . $elementId . '" style="display:none;"><ul>';
        if ($multioptions){
            foreach ($multioptions as $key => $value) {
                $items .= '<li onmousedown="' . $onmousedown_function_name . '(\'' . $key . '\', \'' . str_replace("'", "\\'" , $value) . '\');" onmouseover="highlightItem(event);">' . $value . '</li>';
            }
        }
        $items .= '</ul></div>';


        $combobox_button = '<a class="custom-combobox-toggle" id="toggle_' . $elementId . '"></a>';

        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
            '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
            '<div class="form-element custom-combobox">'
            . '<input autocomplete="nope" id="' . $elementId . '" name="' . $elementId . '" placeholder="' . $placeholder . '" type="text" value="' . $current_value . '"/>'
            . $combobox_button . '</div>';
        $wrapper .= $items . '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>';

        if ($this->getElement()->getDescription()) {
            $wrapper .= $items . '<p class="description" style="margin-top: 25px;">' . $this->getElement()->getDescription() . '</p>';
        }

        $wrapper .= '</div>';
        return $wrapper;

    }

}
