<script>
    <? if ($_1_u_endereco_idtipoendereco != 6) { ?>
        $('#trpropriedade').hide();
        $('#trpropriedade2').hide();

    <? } else { ?>
        $('#trpropriedade').show();
        $('#trpropriedade2').show();

    <? } ?>

    function validacmp(vthis) {
        if ($(vthis).val() == 6) {
            $('#trpropriedade').show();
            $('#trpropriedade2').show();
        } else {
            $('#trpropriedade').hide();
            $('#trpropriedade2').hide();
        }
    }

    function preenchecidade() {

        $("#idcidade").html("<option value=''>Procurando....</option>");

        $.ajax({
            type: "get",
            url: "ajax/buscacodcidade.php",
            data: {
                uf: $("#iduf").val()
            },

            success: function(data) {
                $("#idcidade").html(data);
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax

    }

    $().ready(function() {
        $("#iduf").change(function() {
            preenchecidade();
        });
    });

     //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>