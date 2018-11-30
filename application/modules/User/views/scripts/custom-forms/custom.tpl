<?
$this->headLink()->appendStylesheet('/application/modules/Core/externals/styles/customForms.css');
?>
<script type="text/javascript">
    function fetchSchoolResponseData(school_id) {
        var current_location = window.location.href,
            pos = current_location.search('format=smoothbox');
        if (pos !== -1)
            current_location = current_location.substr(current_location, pos - 1);
        pos = current_location.search('/school_id');
        if (pos !== -1)
            current_location = current_location.substr(current_location, pos);
        current_location += `/school_id/${school_id}`;
        window.location.href = current_location;
    }
</script>
<?=$this->form?>