<?
class Bitsa_Form_Decorator_FormUsername extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
        $label = $this->getElement()->getLabel();
        //$type = $this->getElement()->getType();
        if($this->getElement()->isRequired())
        {
        	$label .= '<b>*</b>';
        }
        $description = $this->getElement()->getDescription();
        
        $wrapper = '<div class="form-wrapper">' .
                '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                '<div class="form-element">' . $content .
                '<div id="'.$elementName.'_status"></div>';
        
        $wrapper .= '<div class="field_correct" id="' . $elementName . '_correct"></div>';
        
        if ($description != null)
        {
            $wrapper .= '<p class="description">' . $description . '</p>';        
        }
        
        $wrapper .= '</div>';
        $wrapper .= '</div>';

        return $wrapper;
    }
}