<?

class Engine_Form_Element_Label extends Engine_Form_Element_Dummy
{
    public function loadDefaultDecorators()
    {
        if( $this->loadDefaultDecoratorsIsDisabled() ) {
            return;
        }

        $attribs = $this->getAttribs();

        $this->addDecorator('HtmlTag', array('tag' => $attribs['tag']));
    }
}