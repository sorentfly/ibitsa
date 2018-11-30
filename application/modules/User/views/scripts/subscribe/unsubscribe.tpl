<?
$this->headTitle('Управление подпиской');
?>

<style>
    .obj-header__flex-item.col-search,
    .obj-header__flex-item.col-user{
        display: none;
    }
    .form-description {
        max-width: unset;
        margin: 2px auto;
        text-align: center;
    }
    form > div > div {
        text-align: center;
    }
</style>

<?= $this->form->render($this) ?>