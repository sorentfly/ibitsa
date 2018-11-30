<? 
//user is just loginned, and want to join somewhere
$autojoinObject = null;
try{
    if (strpos($_COOKIE['autojoin'], '_')!==false){
        $type = explode('_', $_COOKIE['autojoin'])[0];
        $id = (int)explode('_', $_COOKIE['autojoin'])[1];
        if (!$id || !in_array($type, Engine_Api::_()->core()->getAutoJoinAfterLoginItemTypes()) ){
            throw new Exception();
        }
        $autojoinObject = Engine_Api::_()->getItem($type, $id);
    }
}catch(Exception $e){}
if ($autojoinObject && method_exists($autojoinObject, 'membership')
    && (
        !$autojoinObject->membership()->isMember($this->viewer())
        || method_exists($autojoinObject, 'isByStepRegistration') && $autojoinObject->isByStepRegistration()
            && $autojoinObject->membership()->getMemberInfo($this->viewer())->bystep_registration_status != 'finished'
  )){ ?>
    <script type="text/javascript">
      en4.core.onSmoothboxPluginLoad(function(){
          if (document.location.href.indexOf('/members/edit') == 0) {
              return;
          }
          Smoothbox.open('//<?=$this->url([
              'module' => $type,
              'controller' => 'member',
              'action' => 'join',
              $type . '_id' => $id,
          ], $type . '_extended', true)?>', {noOverlayClose: true});
          setTimeout(function () {
              if (!Smoothbox.instance) return;
              if (!jQuery(Smoothbox.instance.window).find('form, button, input[type="submit"], input[type="button"]').length) return;
              jQuery(Smoothbox.instance.overlay).click(function () {
                  document.cookie = "autojoin=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/";
                  Smoothbox.close();
              });
          }, 1500);});
    </script>
<? }
setcookie('autojoin', '', 0, '/');