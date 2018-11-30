<?
$this->headTitle($this->translate('Login'). ' ' . $this->host);
?>
<style type="text/css">
    #leftMenuMain,.col-search,.col-user,.obj-login-modal__flex-item.mod-sidebar+.obj-login-modal__flex-item{display:none!important}
    .obj-login-modal__flex-item.mod-redirect-title {position: absolute;height: 40px;display: flex;width: 100%;justify-content: center;align-items: center}
    .obj-login-modal__flex-item.mod-redirect-title>p{font-size:18px}

</style>
<script type="text/javascript">
    window.onLoginSuccess = ()=>{
        let url = window.location.href;
        window.location.href = url;
        return true;
    };
    window.onFormInitialize = ()=>{
        let text = jQuery('<p />', {html: 'Вход на'}).append('&nbsp;<a href="http://<?=$this->host?>"><?=$this->host?></a>'),
            wrapper = jQuery('<div />', {'class':'obj-login-modal__flex-item mod-redirect-title'}).append(text);
        jQuery(jQuery('#TB_ajaxContent').find('.obj-login-modal__flex-container')[0]).prepend(wrapper);
    };

    en4.core.runonce.add(function() {
        globalLoginFormOpen();
    });
</script>