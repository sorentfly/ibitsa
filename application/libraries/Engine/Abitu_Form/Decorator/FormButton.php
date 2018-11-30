<?
class Abitu_Form_Decorator_FormButton extends Zend_Form_Decorator_Abstract
{
     protected $_placement = null;
    public function render($content)
    {
        if($this->getElement()->getAttrib('class') == 'hidden')
        {
            $class = ' hidden';
        }else{
            $class = '';
        }
        
        $wrapper = '<hr/><div class="form-wrapper' . $class . '" for="'.$this->getElement()->getName().'">' .
                '<div class="form-label"></div>' .
                '<div class="form-element">' . $content . '</div>' .
                '</div>';
        
        
        return $wrapper;
    }
}