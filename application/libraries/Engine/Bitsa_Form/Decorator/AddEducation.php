<?
class Bitsa_Form_Decorator_AddEducation extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
        
        $options = $this->getOptions();
        $label = $options['label'];
       
        $wrapper = '<div class="form-wrapper">' .
                       '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
                       '<div class="form-element">' . $content . '</div>'.
                   '</div>';

        return $wrapper;
    }
}