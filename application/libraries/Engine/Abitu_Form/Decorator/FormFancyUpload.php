<?
class Abitu_Form_Decorator_FormFancyUpload extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $data = $this->getElement()->getAttrib('data');
        if ($data)
        {
            $this->getElement()->setAttrib('data', null);
        }
        $view = $this->getElement()->getView();
        return $view->action('upload', 'upload', 'storage', array(
                'name' => $this->getElement()->getName(),
                'data' => $data,
                'element' => $this->getElement(),
                'value' => $this->getElement()->getValue()
        ));
    }
}