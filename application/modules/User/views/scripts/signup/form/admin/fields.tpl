<?php
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array('topLevelId' => 0))
?>

<link href="/externals/pmd/styles/default/redmond.datepick.css" rel="stylesheet"/>
<script src="/externals/pmd/lib/datepick.js" type="text/javascript"></script>
<script src="/externals/pmd/lib/datepick-ru.js" type="text/javascript"></script>
<script src="/externals/pmd/scripts/registration_personal_data.js" type="text/javascript"></script>

<?php echo $this->form->render($this) ?>
