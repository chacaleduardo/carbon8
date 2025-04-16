<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}


$dtInicio= $_GET["dtInicio"];
$dtFim= $_GET["dtFim"];
$vencidos = $_GET["vencidos"];

?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem do arquivo SPED</div>
        <div class="panel-body" >
        <table>
            <tr>
                <td  align="right" >Empresa:</td>
                <td colspan="3">                
                <select id="idempresa"  name="idempresa" vnulo>
                    <option value=""></option>
                    <?fillselect("select idempresa, nomefantasia from empresa  where status ='ATIVO' order by nomefantasia",$_idempresa);?>		
                </select>
                </td>
            </tr>
            <tr> 
                <td align="right" >Mês:</td> 
                <?if(empty($mes)){$mes= date('m');}?>
                <td><input name="mes" type="text" size="2" id="mes" value="<?=$mes?>" vnulo></td> 
                <td align="right" >Ano:</td> 
                <?if(empty($ano)){$ano= date('Y');}?>
                <td><input name="ano" type="text" size="3" id="ano" value="<?=$ano?>" vnulo></td>
				<td>
				<span class="dropdown" style=" margin-left:12px">
					<button id="cbPesquisar" type="button" class="btn btn-info dropdown-toggle  btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" >
						<span class="fa fa-search"></span>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="cbPesquisar" style="color:#898989; font-size:11px;text-transform:uppercase;text-transform: uppercase; margin-top: 17px; left: -120px;">
                      		
                    <li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="pesquisar();"  data-value="another action" style="color:#898989 !important;">Gerar arquivo SPED Completo</a></li>
                    <li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="montar_rel('servicos');"  data-value="another action" style="color:#898989 !important;">Gerar relatório PIS/COFINS dos Serviços</a></li>
                    <li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="montar_rel('produtos');"  data-value="another action" style="color:#898989 !important;">Gerar relatório PIS/COFINS dos Produtos</a></li>
		  
                    </ul>
				</span>
                </td>
        </tr>
        </table>

        </div>
    </div>
    </div>
</div>
<script>
function pesquisar(){
     
    var ano = $("[name=ano]").val();
    var mes = $("[name=mes]").val();
   // var _idempresa = getUrlParameter('_idempresa');

   var idempresa=$("#idempresa").val();

    if (idempresa !== '') {
        url= 'ajax/spedcontribuicoes.php?mes='+mes+'&ano='+ano+'&_idempresa='+idempresa;
    }else{
        url= 'ajax/spedcontribuicoes.php?mes='+mes+'&ano='+ano;
    }
    janelamodal(url);
    /*
     $.ajax({
            type: "get",
            url : "ajax/sped.php",
            data: { mes : mes,ano:ano },

            success: function(data){
                    $("#listaagentes"+idnfitem).html(data);
            },

            error: function(objxmlreq){
                    alert('Erro:<br>'+objxmlreq.status); 

            }
        })//$.ajax
         
     */
}
function montar_rel(tipo){
    var ano = $("#ano").val();
    var mes = $("#mes").val();
    var url = '';
   // var _idempresa = getUrlParameter('_idempresa');4
   var idempresa=$("#idempresa").val();
   // if (_idempresa !== '') {
        url= 'report/relsped.php?mes='+mes+'&ano='+ano+'&tipo='+tipo+'&_idempresa='+idempresa;
   /* }else{
        url= 'report/relsped.php?mes='+mes+'&ano='+ano+'&tipo='+tipo;
    }*/
	window.open(url,'_blank');
}
</script>