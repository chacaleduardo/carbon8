<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/conferirresultado_controller.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

/*
 * Parametros para relatorio 
 */

$nome			= $_GET["cliente"];
$exercicio		= $_GET["exercicio"];
$idregistro_1	= $_GET["registro_1"];
$idregistro_2	= $_GET["registro_2"];
$dataamostra_1	= $_GET["dataregistro_1"];
$dataamostra_2	= $_GET["dataregistro_2"];
$idtipoteste	= $_GET["teste"];
$statusres      = $_GET["statusres"];
$modulo			= $_GET["_modulo"];
?>

<style>
.tbcorpo{
	border: 1px solid black;
	border-collapse: collapse;
	margin-bottom: 15;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 7pt !important;
	/* weight: bold; */
	color: black;
}

.tbcorpo tr{
	border: 1px solid black;
}
.tbcorpo tr td{
	border: 1px solid dotted;
}
</style>
<style>
/* footer= linha onde fica os botões assinar retirar assinatura e o alerta*/
#Footer {
    text-align:center;
    /* align:center; */
    color: black;
    border: 1px solid silver;
    position:fixed;
    /**adjust location**/
    right: 0px;
    bottom: 0px;
    padding: 0 10px 0 10px;
    width: 100%;
        /* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
    _position: absolute;
}
.clsFootera {
    background: #00FF00;
}	
.clsFooterf {
    background: silver;
}

</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	<tr>
	    <td class="rotulo">Exercício:</td>
	    <td></td>
	    <td><input type="text" name="exercicio" vpar="" id="exercicio"
		    value="<?=$exercicio?>" autocomplete="off" class="input10"></td>	
	</tr>
	 <tr>
		<td class="rotulo">ID. Registro:</td>
		<td><font class="9graybold">entre</font></td>
		<td><input name="registro_1" vpar="" id="registro_1" size="10" style="width: 90px;" value="<?=$idregistro_1?>"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input name="registro_2" vpar="" id="registro_2" size="10" style="width: 90px;" value="<?=$idregistro_2?>"></td>
	</tr>
	    <tr>
		<td class="rotulo">Data Registro:</td>
		<td><font class="9graybold">entre</font></td>
		<td><input name="dataregistro_1" vpar="" id="dataregistro_1" class="calendario" size="10" style="width: 90px;" value="<?=$dataamostra_1?>"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input name="dataregistro_2" vpar="" id="dataregistro_2"class="calendario" size="10" style="width: 90px;" value="<?=$dataamostra_2?>"></td>
	    </tr>
	<tr>
	    <td class="rotulo">Cliente:</td>
	    <td></td>
	    <td><input type="text" name="cliente" vpar="" id="cliente"
		    value="<?=$nome?>" autocomplete="off" class="input10"></td>	
	</tr>
        <tr>
            <td >Teste:</td> 
            <td></td>
            <td colspan="10">
                <select name="teste"  id="teste" >
                    <option></option>
                    <?fillselect("select idprodserv, concat (codprodserv,'-',descr) teste
                                    from prodserv 
                                    where conferenciares = 'Y' 
                                    and tipo='SERVICO' 
                                    ".getidempresa('idempresa','prodserv')."
                                    and status  = 'ATIVO' order by teste",$idtipoteste);?>
                </select>	
            </td>
        </tr>
        <tr>
            <td>Status:</td>
            <td></td>
            <td >
                <select name="statusres"  id="statusres" >
                    <?fillselect("select 'FECHADO','Fechado' union select 'ASSINADO','Assinado' union select 'ABERTO','Aberto' ",$statusres);?>                    
                </select>	
            </td>
        </tr>      
	</table>	
	<div class="row"> 
	    <div class="col-md-8">
	   </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	   
	</div>
        </div>
    </div>
    </div>
</div>
<?
/*
 * TRATAMENTO E CONCATENACAO DO SQL PRINCIPAL
 */
if($_GET and !empty($_GET['_acao'])){
	
	$rescount = ConferirResultadoController::buscarResultadosParaConferencia($exercicio,$statusres,$idpessoa,$nome,$idnucleo,$nucleoamostra,$lote,$tipogmt,$idtipoteste,$idresultado,$controle,$idunidade,$idregistro,$idregistro_1,$idregistro_2,$dataamostra_1,$dataamostra_2,$idade,$tc,$partida,$idtipoamostra,$idsubtipoamostra,$_SESSION["SESSAO"]["IDPESSOA"],getidempresa('a.idempresa',$_GET['_modulo']));
	$qtdcount = $rescount['count'];
        echo "<!-- ".$rescount["sql"]." -->";
	if(empty($qtdcount)){
		echo '<br><br><br><div align="center">Não existem mais registros.</div> <br> <div align="center">ou <div><br> <div align="center">Não há nenhum registro para os parâmetros informados!</div>';
		//die;
	}elseif($rescount["erro"]){
		foreach($rescount['msg'] as $k => $value){
		echo '<br><br><br><div align="center">'.$value.'</div> <br>';
		}
	}
}
if($_GET and $qtdcount >0){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultados a Conferir - <?=$qtdcount?> </div>
        <div class="panel-body">

    <table class="tbcorpo" border='1'  id="inftable">  
	<thead>	      
	    <tr class='header3'>
		<td nowrap>Ano</td>
		<td nowrap>Nº Reg.</td>
		<td nowrap>Teste</td>
		<td nowrap align='center' style='width:900px;'>Resultado</td>
		<td class="nowrap">Conferir <a class="fa fa-caret-down fa-2x azul hoverazul pointer" title="Seleciona tudo / Inverte seleção" onclick="check(this,'checked');"></a></td>
	      </tr>
	</thead>
	<tbody>
<?
$ip =0;//variavel para o form
foreach($rescount["data"] as $k =>$row){
    $ip=$ip+1;
    if($row['alerta']=="Y"){
	$strback="background-color:#FF8491;";
    }else{
	$strback="";
    }
	//Recuperar o modulo de resultados associado conforme a unidade
$modResultadosPadrao = getModuloResultadoPadrao($row['idunidade']);
?>
		<tr class="res1" style="<?=$strback?>">
		    <td nowrap style="border:1px dotted;" ><?=$row["exercicio"]  ?></td>
		    <td nowrap style="text-align: center; border:1px dotted; cursor:pointer; color:blue;" onclick="janelamodal('?_modulo=<?=$modResultadosPadrao?>&_acao=u&idamostra=<?=$row["idamostra"]?>&idresultado=<?=$row["idresultado"]?>')"><?=$row["idregistro"] ?></td>
		    <td nowrap style="border:1px dotted;"><?=$row["quantidadeteste"]?>-<?=$row["tipoteste"]?></td>		    
		    <td style="border:1px dotted;"><?=strip_tags($row["descritivo"])?></td>
		    <td style="border:1px dotted;" align='center' >
			<span style="display:inline;" roh="true">                                    
				<input name="_<?=$ip?>_i_carrimbo_idpessoa" type="hidden" value="<?=$_SESSION["SESSAO"]["IDPESSOA"]?>">
				<input name="_<?=$ip?>_i_carrimbo_idobjeto" type="hidden" value="<?=$row["idresultado"]?>">
				<input name="_<?=$ip?>_i_carrimbo_tipoobjeto" type="hidden" value="<?=$modResultadosPadrao?>">
				<input name="_<?=$ip?>_i_carrimbo_idobjetoext" type="hidden" value="<?=traduzid("resultado", "idresultado", "idfluxostatus", $row["idresultado"])?>">
				<input name="_<?=$ip?>_i_carrimbo_tipoobjetoext" type="hidden" value="idfluxostatus">
				<input name="_<?=$ip?>_i_carrimbo_status" type="hidden" value="CONFERIDO">
				<input name="_<?=$ip?>r_u_resultado_idresultado" type="hidden" value="<?=$row["idresultado"]?>">
				<input name="_<?=$ip?>r_u_resultado_status" type="hidden" value="CONFERIDO">
				<input <? if($row['fechador']) echo('disabled title="Você não pode conferir este resultado!"'); else echo("checked");?>  style="background-color:#cccccc;" atname="checked" name="chk[<?=$ip?>]" value="<?=$row["idresultado"]?>" type="checkbox">	
		    </span>	
		    </td>
		  </tr>   
 <?
}
?>
	</tbody>
    </table>
</div>
</div>
</div>
</div>
<br>
<br>
<div id="Footer" class="clsFooterf">
<table width="100%">
<tr>
    <td style="font-size:12px;padding-left:15px;width:120px;"></td>
    <td align="center">		
	<table align="center">
	<tr>
	<td class='nowrap'>
	    &nbsp;&nbsp;&nbsp;
	    <button class="btn btn-danger btn-xs" onclick="conferir(this);">
		<i class="fa fa-circle"></i> Conferir
	    </button>	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</td>	
	</tr>
	</table>
    </td>
    <td style="width:150px;"></td>
</tr>
</table>
</div>
<?
}
?>
<script>
    //FUNÇÃO PARA CONFERIR 
function conferir(vthis){
    //pega todos os inputs checkados 		
    var inputprenchido= $("#inftable").children().find("input:checkbox:checked");

    //pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
    var vsubmit= $(inputprenchido).parent().parent().find("input:text, input:hidden").serialize();
    vsubmit=vsubmit.concat("&status=CONFERIDO");
    console.log(vsubmit);
 
    //insere no banco de dados via submitajax
    //CB.post(vsubmit);
    
    CB.post({
	objetos: vsubmit		
	,parcial:true
	,msgSalvo:"Conferido"
    })
}

function check(inobj, incheckname) {
    if (inobj.parentNode.nodeName == "TD") {

        objtbl = inobj.parentNode.offsetParent;
        colobj = objtbl.getElementsByTagName("INPUT");
        try {
            for (i = 0; i < colobj.length; i++) {
                vobj = colobj[i];
                if (vobj.type == "checkbox" && !vobj.hasAttribute("disabled")) {
                    if (vobj.getAttribute('atname').indexOf(incheckname)  >= 0) {
                        if (vobj.checked) {
                            vobj.checked = false;
                        } else {
                            vobj.checked = true;
                        }
                    }
                }
            }
        } catch (err) {
            window.status = "Falhou ao percorrer os items CHECKBOX;";
        }

    } else {
        window.status = "Nenhum <TD> encontrado para [inobj.parentNode.nodeName]; Verifique se o objeto imediatamente superior = <TD>;";
    }
}

function pesquisar(){
    var registro_1 = $("[name=registro_1]").val();
    var registro_2 = $("[name=registro_2]").val();
    var dataregistro_1 = $("[name=dataregistro_1]").val();
    var dataregistro_2 = $("[name=dataregistro_2]").val(); 
    var cliente = $("[name=cliente]").val(); 
    var teste = $("[name=teste]").val();    
    var exercicio = $("[name=exercicio]").val();   
    var status = $("[name=status]").val();  
    var statusres = $("[name=statusres]").val();  
  
 
    var str="registro_1="+registro_1+"&registro_2="+registro_2+"&dataregistro_1="+dataregistro_1+"&dataregistro_2="+dataregistro_2+"&cliente="+cliente+"&teste="+teste+"&exercicio="+exercicio+"&statusres="+statusres;
    
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>