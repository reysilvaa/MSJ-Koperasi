<script>
    $(document).ready(function() {
        // get value source
        var sourceVal = $('select[name="source"]').val();
        //check value is empty then set default
        (sourceVal == '') ? default_val(sourceVal): '';
        // function change selected source
        $('select[name="source"]').change(function() {
            default_val($(this).val());
        })
    });
    // function default
    function default_val(val) {
        $('input[name="length"]').val('');
        $('input[name="external"]').val('0');
        $('input[name="external"]').css('display', 'none');
        $('select[name="internal"]').val('-');
        $('select[name="internal"]').css('display', 'none');
        if (val == 'th2') {
            $('input[name="length"]').val(2);
        } else if (val == 'th4') {
            $('input[name="length"]').val(4);
        } else if (val == 'bln') {
            $('input[name="length"]').val(2);
        } else if (val == 'tgl') {
            $('input[name="length"]').val(2);
        } else if (val == 'ext') {
            $('input[name="external"]').css('display', '');
        } else if (val == 'int') {
            $('select[name="internal"]').css('display', '');
        } else if (val == 'cnt') {
            $('input[name="length"]').val(3);
        }
    }
</script>
