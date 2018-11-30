<?
class Abitu_Form_Decorator_FormSelect extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden')
        {
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
        $elementName = $this->getElement()->getName();
        $label = $this->getElement()->getLabel();

        $description = $this->getElement()->getDescription();

        if($this->getElement()->isRequired())
        {
            $label .= '<b>*</b>';
        }
        
        $wrapper = '<div class="form-wrapper"' . $style . '>' .
                '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                '<div class="form-element">' . $content;
            
        if ($description != null)
        {
            $wrapper .= '<p class="description">' . $description . '</p>';
        }
        
        $wrapper .= '</div>';
        $wrapper .= '<div class="form-element field_correct" id="' . $elementName . '_correct"></div>';
        $wrapper .= '</div>';

        return $wrapper;
                    
    }

}
