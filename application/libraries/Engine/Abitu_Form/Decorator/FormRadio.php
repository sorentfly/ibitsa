<?
class Abitu_Form_Decorator_FormRadio extends Zend_Form_Decorator_Abstract
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
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $description = $this->getElement()->getDescription();
        
        $wrapper =  '<div class="form-wrapper"' . $style . '>' .
                        '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                        '<div class="form-element">' . $content;
        
        $wrapper .= '<div class="field_correct" id="' . $elementName . '_correct"></div>';
            
        if ($description != null)
        {
            $wrapper .= '<p class="description" colspan="2">' . $description . '</p>';
        }

        $wrapper .= '</div>';
        $wrapper .= '</div>';

        return $wrapper;
                    
    }

}
