<?
class Bitsa_Form_Decorator_FormStreet extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden' && $_SESSION['mobile']['mobile'] !== true)
        {
            $style = ' style="display:none;"';
        }else{
            $style = '';
        }
                
        $elementId = $this->getElement()->getName();
        $label = $this->getElement()->getLabel();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $wrapper =  '<div class="form-wrapper" ' . $style . '>' .
                        '<div class="form-label"><label for="' . $elementId . '">' . $label . '</label></div>' .
                        '<div class="form-element">' . $content . '</div>' .
                        '<div class="result_list" data-type="' . $elementId . '" id="result_list_' . $elementId . '" style="display:none;"><ul></ul></div>' .
                        '<div class="form-element field_correct" id="' . $elementId . '_correct"></div>' .
                    '</div>';        
        
        return $wrapper;
    }
}