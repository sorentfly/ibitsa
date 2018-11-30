<style>
  .form-wrapper
  {
    margin: 0 !important;
  }
  .form-wrapper.telephoneWrapper {
    margin: 0 !important;
  }
  .result_list
  {
    right: 45px; <? /* КОСТЫЛЬ: выпадающий список регионов/городов прижимается влево, почему - разбираться времени нет */ ?>
  }
</style>
<? if ( isset($this->successMessage) ){ ?>
    <div class="green_hinnt">
      <?=$this->successMessage?>
    </div>
<? } ?>
<?=$this->form->render()?>