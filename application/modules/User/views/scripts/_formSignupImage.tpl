  <?
    $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'externals/moolasso/Lasso.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/moolasso/Lasso.Crop.js')
  ?>
<div>
  <? 
    if (isset($_SESSION['TemporaryProfileImg'])){
      echo '<img src="'.$_SESSION['TemporaryProfileImgProfile'].'" alt="" id="lassoImg"/>';
    }
    else
      echo '<img src="application/modules/User/externals/images/nophoto_user_thumb_profile.png" alt="" id="lassoImg" />';
    ?>
</div>
<br/>
<div id="preview-thumbnail" class="preview-thumbnail">
    <? 
    if (isset($_SESSION['TemporaryProfileImg'])){
      echo '<img class ="thumb_icon item_photo_user thumb_icon" src="'.$_SESSION['TemporaryProfileImgSquare'].'" alt="Profile Photo" id="previewimage" />  <br />';
    }
    else
      echo
      '<img id="previewimage" class="thumb_icon item_photo_user thumb_icon" src="application/modules/User/externals/images/nophoto_user_thumb_icon.png" alt="Profile Photo" />'
    ?>
</div>
<div id="thumbnail-controller" class="thumbnail-controller">
    <?
      if (isset($_SESSION['TemporaryProfileImg'])){
        echo '<a href="javascript:void(0);" onclick="lassoStart();">'.$this->translate('Edit Thumbnail').'</a>';
      }
    ?>
</div>
<br/>
<div>
    <? 
      $settings = Engine_Api::_()->getApi('settings', 'core');
      
      if (isset($_SESSION['TemporaryProfileImg']) && $settings->getSetting('user.signup.photo', 0) == 1){
        echo '<button name="done" id="done" type="submit" onClick="javascript:finishForm();">Save Photo</button>';
      }
    ?>
</div>
<script type="text/javascript">
    var loader = new Element('img', {src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});
    ;
    var orginalThumbSrc;
    var originalSize;
    var lassoCrop;

    var lassoSetCoords = function (coords)
    {
        var delta = (coords.w - 48) / coords.w;

        $('coordinates').value =
                coords.x + ':' + coords.y + ':' + coords.w + ':' + coords.h;

        $('previewimage').setStyles({
            top: -(coords.y - (coords.y * delta)),
            left: -(coords.x - (coords.x * delta)),
            height: (originalSize.y - (originalSize.y * delta)),
            width: (originalSize.x - (originalSize.x * delta))
        });
    }
    var myLasso;
    var lassoStart = function ()
    {
        if (!orginalThumbSrc)
            orginalThumbSrc = $('previewimage').src;
        originalSize = $("lassoImg").getSize();

        //this.style.display = 'none';
        myLasso = new Lasso.Crop('lassoImg', {
            ratio: [1, 1],
            preset: [10, 10, 58, 58],
            min: [48, 48],
            handleSize: 8,
            opacity: .6,
            color: '#7389AE',
            border: '<?=$this->layout()->staticBaseUrl . '/externals/moolasso/crop.gif' ?>',
            onResize: lassoSetCoords
        });


        var sourceImg = $('lassoImg').src;
        $('previewimage').src = $('lassoImg').src;
        $('coordinates').value = 10 + ':' + 10 + ':' + 58 + ':' + 58;

        //$('preview-thumbnail').innerHTML = '<img id="previewimage" alt="cropping test" src="'+sourceImg+'"/>';
        $('thumbnail-controller').innerHTML = '<a href="javascript:void(0);" onclick="lassoEnd();"><?=$this->translate('Apply Changes');?></a>';
    }

    var uploadSignupPhoto = function () {
        $('uploadPhoto').value = true;
        $('thumbnail-controller').innerHTML = "<div><img class='loading_icon' src='application/modules/Core/externals/images/loading.gif'/><?=$this->translate('Loading...');?></div>";
        $('SignupForm').submit();
        $('Filedata-wrapper').innerHTML = "";
    }
    
    var lassoEnd = function () {
        $('lassoImg').setStyle('display', 'block');
        $('thumbnail-controller').innerHTML = '<a href="javascript:void(0);" onclick="lassoStart();"><?=$this->translate('Edit Thumbnail');?></a>';
        $('lassoMask').destroy();
    }
</script>