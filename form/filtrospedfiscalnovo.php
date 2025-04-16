<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}


$dtInicio= $_GET["dtInicio"];
$dtFim= $_GET["dtFim"];
$vencidos = $_GET["vencidos"];
$_idempresa=$_GET["idempresa"];

?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
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
                <!--td align="right" >Mês:</!--td> 
                <?if(empty($mes)){$mes= date('m');}?>
                <td><input name="mes" type="text" size="2" id="mes" value="<?=$mes?>" vnulo></td -->       
                <td align="right" >Ano:</td> 
                <?if(empty($ano)){$ano= date('Y');}?>
                <td><input name="ano" type="text" size="3" id="ano" value="<?=$ano?>" vnulo></td> 
                <td></td>               
                <td colspan="2">
                <a class="pointer" title="Gerar Inventario uma vez ao ano primeiro dia do ano" target="_blank" onclick="gerainventario();">
                    Gerar Inventario Anual
                </a>
                </td>  
                <td><label class="alert-warning" id='msninv'></label></td>    
        </tr>
        <tr>
		<td class="rotulo">Período entre </td>
		<td><input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>" autocomplete="off"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off"></td>
	    </tr>
        	<tr>
		<td>Inventário</td>
		<td>
			<select  name="inventario" id="inventario">
				<option value=""></option>
				<?fillselect("select 'Y','Sim' ",$inventario);?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Rastrear IDNF</td>
		<td>
			<select  name="veridnf" id="veridnf">
				<option value=""></option>
				<?fillselect("select 'Y','Sim' ",$veridnf);?>
			</select>
		</td>
	</tr>
    
	<tr>
		<td></td>
                <td>
                <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
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
    var veridnf = $("[name=veridnf]").val();
    var inventario = $("[name=inventario]").val();
    var vencimento_1=$("#vencimento_1").val();
    var vencimento_2=$("#vencimento_2").val();
    var idempresa=$("#idempresa").val();
    //var _idempresa = getUrlParameter('_idempresa');
    if (idempresa !== '') {
        url= "ajax/spedfiscalnovo.php?dtInicio="+vencimento_1+"&dtFim="+vencimento_2+"&ano="+ano+"&mes="+mes+"&veridnf="+veridnf+"&inventario="+inventario+'&_idempresa='+idempresa;
    }else{
        url= "ajax/spedfiscalnovo.php?dtInicio="+vencimento_1+"&dtFim="+vencimento_2+"&ano="+ano+"&mes="+mes+"&veridnf="+veridnf+"&inventario="+inventario;
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

function gerainventario(){
    var ano = $("[name=ano]").val();
    var $_idempresa =$("[name=idempresa]").val(); 
	
	$("#msninv").html("Gerando Inventário.");
	
	$.ajax({
			type: "get",
			url : "ajax/geraspedinventario.php",
			data: { ano : $("[name=ano]").val()
                    ,idempresa : $("[name=idempresa]").val() },

			success: function(data){
                alert("Gerado com sucesso.");
				$("#msninv").html("");
			},

			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 

			}
		})//$.ajax

}

</script>