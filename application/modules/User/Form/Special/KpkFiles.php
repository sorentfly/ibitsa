<?

class User_Form_Special_KpkFiles extends Engine_Form
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        $this
            ->getView()->headScript()
            ->appendFile(JQUERY_UI_LIB)
            ->appendFile('/application/modules/Core/externals/scripts/notification.js')
            ->appendFile('/application/modules/User/externals/scripts/profile_edit.js');

        $this
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setMethod('POST')
            ->setTitle('Файлы');

        $this
            ->addPrefixPath('Abitu_Form_Decorator', 'Engine/Abitu_Form/Decorator', 'decorator')
            ->addPrefixPath('Abitu_Form_Element', 'Engine/Abitu_Form/Element', 'element');


  /*      $passport = $this->createElement('File', 'passport', array(
            'label' => 'Поспорт',
            'allowEmpty' => false,
            'required' => true,
        ));

        $diplom = $this->createElement('File', 'diplom', array(
            'label' => 'Диплом',
            'allowEmpty' => false,
            'required' => true,
        ));

        $reference = $this->createElement('File', 'reference', array(
            'label' => 'Справка',
            'allowEmpty' => false,
            'required' => true,
        ));

        $agreement = $this->createElement('FancyUpload', 'agreement', array(
            'label' => 'Согласие на обработку персональных данных',
            'allowEmpty' => false,
            'required' => true,
        ));


        $this->addElement($passport)
            ->addElement($diplom)
            ->addElement($reference)
            ->addElement($agreement);
*/
        $this->addElement('FancyUpload', 'files', array(
            'description' => 'Загрузите пожалуйста ваши документы',
            'response_id_field' => 'file_id',
            'maxfilesize' => 10*1024*1024
        ));


        $this->addElement('Button', 'submit', array(
            'label' => 'Прикрепить файлы',
            'ignore' => true,
            'decorators' => array (
                'ViewHelper'
            ),
            'type' => 'submit'
        ));

        $this->addElement('Cancel', 'cancel', array (
            'label' => 'cancel',
            'link' => true,
            'href' => '',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array (
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array (
            'submit',
            'cancel'
        ), 'buttons');
    }

    public function alreadyHasDataHook($data)
    {
        $this->submit->setLabel('Прикрепить файлы повторно');
        //$this->populate(array_pop($data));
        return $this;
    }

    public function getSuccessLabel()
    {
        return "Ваши данные успешно сохранены.";
    }
}
