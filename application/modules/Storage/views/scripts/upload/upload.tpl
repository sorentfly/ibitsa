<?php
  $this->headScript()
    ->appendFile('/externals/mini-upload/assets/js/jquery.knob.js')
    ->appendFile('/externals/mini-upload/assets/js/jquery.ui.widget.js')
    ->appendFile('/externals/mini-upload/assets/js/jquery.iframe-transport.js')
	->appendFile('/externals/mini-upload/assets/js/jquery.fileupload.js');
  $this->headLink()
    ->appendStylesheet('/externals/mini-upload/assets/css/style.css');
  $accept = $this->element->accept;
  $maxfilesize = $this->element->maxfilesize;

  $response_id_field = $this->element->response_id_field;

  $uploadId = 'upload' . (isset($this->element->identity) ? $this->element->identity : '');
?>

<style type="text/css">
.photo-uploader-frame::-webkit-scrollbar {
    background-color: #2e3134;
}
.photo-uploader-frame::-webkit-scrollbar-thumb {
    background-color: #369;
    border-radius: 1em;
}
</style>
<? /* !Внимание! Этот скрипт здесь не просто так, а для того чтобы он выполнялся каждую ajax-загрузку этого элемента! Если его добавть в head - не будет выполняться при ajax. */ ?>
<script type="text/javascript" src="/externals/mini-upload/assets/js/script.js"></script>
<div id="upload" data-upload-id="<?=$uploadId?>">
	<div id="drop">
        <span id="desktop_view" style="display: none;">
            <?=$this->translate("Drag here")?> <?=$this->translate("or")?>
            <a><?=$this->translate("Select files")?></a>
        </span>
        <span id="mobile_view" style="display: none;">
            <a class="drop_photo" data-role="photo"><i class="fa fa-camera"></i> <?=$this->translate("Take a photo")?></a> <?=$this->translate("or")?>
            <a><?=$this->translate("Select files")?></a>
        </span>

        <input type="hidden" name="<?= $this->name ?>"  data-response-id-field="<?=$response_id_field?>" id="uploadfileids" value ="<?= !empty($this->value) ?  implode(' ', $this->value). ' ' : '' ?>" />
        <input type="file" name="Filedata" <?=$accept?'accept="'.$accept.'" data-fix-accept="1"' : ''?> <?=$maxfilesize?'data-maxfilesize="'.$maxfilesize.'"' : ''?> multiple />
	</div>

	<ul class="photo-uploader-frame">
		<?php if($this->value) {
		    foreach($this->value as $val) {
		        if(Zend_Controller_Front::getInstance()->getRequest()->getModuleName() == 'album'){
		            /* КОСТЫЛЬ */
                    $photo = Engine_Api::_()->getItem('album_photo', $val);
                    $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
                }else{
                    $file = Engine_Api::_()->getItem('storage_file', $val);
                }
                if ($file){
                    ?>
                    <li>
                        <input type="text" value="<?= $val ?>" data-width="48" data-height="48" data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" />
                        <p><?= $file->name ?><i><?= intval($file->size / 1000) ?> KB</i></p>
                        <span title="Удалить" onclick="removeUploadItem(jQuery(this).parent())"></span>
                    </li>
                    <div class='uploading_img'><img src="<?= $file->map() ?>"></div>
            <?php } } ?>
        <?php } ?>
	</ul>
</div>

<script>

    function removeUploadItem(item){
        var uf = jQuery('[data-upload-id=<?=$uploadId?>] #uploadfileids');
        var uploadFileIds = trim(uf.val()).split(' ');
        uploadFileIds.forEach(function(elem, i, arr) {
            if(elem == item.find('input').val()){
                arr.splice(i, 1);
            }
        });

        uf.val(uploadFileIds.join(' '));

        item.fadeOut(function(){
            item.remove();
        });

        item.next().fadeOut(function(){
            item.next().remove();
        });
    }

    jQuery(function(){
        jqUploadInit(jQuery('[data-upload-id=<?=$uploadId?>]'));
    });
</script>