<?
class Bitsa_Form_Decorator_FormCheckbox extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden' && $_SESSION['mobile']['mobile'] !== true)
        {
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
        $wrapper = '<div class="form-wrapper" ' . $style . '>' .
                        '<div class="form-label"></div>' .
                        '<div class="form-element">' . $content . ' <label for="' . $elementId . '">' . $label . '</label>' .
                            '<div class="field_correct" id="' . $elementId . '_correct"></div>' .
                        '</div>' .
                    '</div>';
        
        $view = $this->getElement()->getView();
        if ($elementId == 'terms')
        {
            $wrapper .= '<div class="license_agreement"><div id="license_agreement" style="display: none;">'.$view->partial('help/terms.tpl', 'core', array('site_name'=>$_SERVER['HTTP_HOST'])).'</div></div>';
        }
        return $wrapper;
    }
}