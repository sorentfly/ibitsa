<?
class Bitsa_Form_Decorator_FormSubmitButton extends Zend_Form_Decorator_Abstract
{
     protected $_placement = null;
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
        $label = $this->getElement()->getLabel();        
        $wrapper = '<div class="form-wrapper">' .
                    '<div class="form-label"><button id="reset" title="' . Zend_Registry::get('Zend_Translate')->_('Reset values') . '" type="reset">' . Zend_Registry::get('Zend_Translate')->_('Reset form') . '</button></div>' .
                    '<div class="form-element">' . $content . '</div>' .
                    '</div>' .
                    '<div class="form-wrapper">' .
                    '<span><b>*</b></span> — ' .Zend_Registry::get('Zend_Translate')->_('required fields') .
                    '</div>';
        return $wrapper;
    }
}