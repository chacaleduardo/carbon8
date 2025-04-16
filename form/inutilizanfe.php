<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$sql = "select chave1 as maxidnf from sequence where sequence = 'nnfe' and idempresa = ".cb::idempresa();
$res = d::b()->query($sql) or die("Erro ao consultar última Nota Fiscal existente. SQL: ".$sql);
$num = mysql_num_rows($res);

if($num > 1){
    die("Mais de uma sequência para Notas Fiscais encontradas para a empresa, favor revisar a consulta ao banco de dados");
}else{
    if($num == 0){
        die("Nenhuma sequência para Notas Fiscais encontrada para a empresa, favor revisar a consulta ao banco de dados");
    }else{
        $row = mysql_fetch_assoc($res);
        ?>
        <div class="row">
            <div class="col-md-12" >
                <div class="panel panel-default" >
                    <div class="panel-heading" >Inutilizar NFe </div>
                    <div class="panel-body" >
                        <input type="hidden" name="" id="inu_max" value="<?=$row["maxidnf"]?>">
                        <input type="hidden" name="" id="_idempresa" value="<?=cb::idempresa()?>">
                        <table style="width: 70%;">
                            <tr>
                                <td class="rotulo" style="width: 15%;text-align:right;">NFe: <i class="fa fa-info-circle hoverazul pointer" title="O número da NFe não deve ser maior que o número da última NFe existente"></i></td>
                                <td><input autocomplete="off" id="inu_ini" type="number" min="1" max="<?=$row["maxidnf"]?>" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57"></td>
                                <td><span style="color: red;">Última NF cadastrada: ID <?=$row["maxidnf"]?></span></td>
                            </tr>
                            <tr>
                                <td class="rotulo" style="vertical-align: top;width: 15%;text-align:right;">Justificativa: <i class="fa fa-info-circle hoverazul pointer" title="A Justificativa deve possuir no mínimo 15 caracteres"></i></td>
                                <td>
                                    <textarea id="inu_jus" cols="30" rows="10"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button style="float: right;" class="btn btn-default btn-danger" onclick="inutilizar()">
                                        Inutilizar NF's
                                    </button> 
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?}
}?>

<script>
function inutilizar(){

    var ini = parseInt($("#inu_ini").val()) || 0;
    var _idempresa=$("#_idempresa").val();
    var jus = $("#inu_jus").val() || "";
    var max = parseInt($("#inu_max").val()) || <?=$row["maxidnf"]?>;

    if(ini > max || (jus.length < 15 && jus !== "")){

        if(ini > max && ini !== 0){
            $("#inu_ini").css('border','1px solid red');
            alert("O valor de NF inserido é inexistente. Última NF: ID "+max);
        }else{
            $("#inu_ini").css('border','1px solid #cccccc');
        }
           
        if(jus.length < 15){
            $("#inu_jus").css('border','1px solid red');
            alert("A Justificativa deve possuir no mínimo 15 caracteres");
        }else{
            $("#inu_jus").css('border','1px solid #cccccc');
        }
        
    }else{

        if(ini !== 0 && jus !== ""){

            var obj = {
                ini : ini,
                jus : jus,
                max : max,
                _idempresa:_idempresa
            }

            fetch('inc/nfe/sefaz4/func/inutilizanf.php',{
                    method: 'POST',
                    body: Object.keys(obj)
                        .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(obj[k])}`)
                        .join('&'),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(res => {
                    var cbResposta = res.headers.get('X-CB-RESPOSTA')

                    if(cbResposta && cbResposta == 1) return res.json();

                    return res.text();
                })
                .then(data => {
                    if(typeof data === 'object'){
                        alert(data.infInut.xMotivo);
                    }else{
                        alert(data);
                    }
                    $("#inu_ini").val("").prop( "disabled", false ).css('background','');
                    $("#inu_jus").val("").prop( "disabled", false ).css('background','');
                });

            $("#inu_ini").css('border','1px solid #cccccc').prop( "disabled", true ).css('background','bottom');
            $("#inu_jus").css('border','1px solid #cccccc').prop( "disabled", true ).css('background','bottom');


        }else{

            (ini === 0) ?   $("#inu_ini").css('border','1px solid red') : $("#inu_ini").css('border','1px solid #cccccc');
            (jus === "")?   $("#inu_jus").css('border','1px solid red') : $("#inu_jus").css('border','1px solid #cccccc');

        }
    }
}

</script>