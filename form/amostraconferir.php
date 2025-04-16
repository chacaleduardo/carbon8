<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/amostraconferir_controller.php");

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
$modulo			= $_GET["_modulo"];

$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
if(empty($unidadepadrao)){
	die('Configurar Unidade Padrão no Módulo');
}

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
			<div class="panel-heading" >Filtros para Listagem</div>
			<div class="panel-body" >
				<table>
					<tr>	
						<td class="rotulo">Exercício:</td>
						<td></td>
						<td><input type="text" name="exercicio" vpar="" id="exercicio" value="<?=$exercicio?>" autocomplete="off" class="input10"></td>	
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
						<td><input type="text" name="cliente" vpar="" id="cliente" value="<?=$nome?>" autocomplete="off" class="input10"></td>	
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
if($_GET and !empty($_GET['_acao']))
{
	$rescount = AmostraConferirController::buscarAmostrasParaTransferencia($exercicio,$nome,$idregistro_1,$idregistro_2,$dataamostra_1,$dataamostra_2,$unidadepadrao,cb::idempresa());
	echo "<!-- ".$rescount["sql"]." -->";
	if(!empty($rescount["msg"])){
		foreach ($rescount["msg"] as $key => $value) {
			echo '<br><br><br><div align="center"> '.$value.'</div> <br> <div align="center">';
		}
	}
}
if($_GET and empty($rescount["msg"])){
	?>
	<div class="row">
		<div class="col-md-12" >
			<div class="panel panel-default" >
				<div class="panel-heading">Resultados para Transferir - <?=$rescount["numRows"]?> </div>
				<div class="panel-body">
					<table class="tbcorpo" border='1'  id="inftable">  
						<thead>	      
							<tr class='header3'>
								<td nowrap>Ano</td>
								<td nowrap>Nº Reg.</td>
								<td nowrap>Cliente</td>
								<td nowrap>Teste</td>
								<td class="nowrap">Transferir <a class="fa fa-caret-down fa-2x azul hoverazul pointer" title="Seleciona tudo / Inverte seleção" onclick="check('amostra','checked');"></a></td>
								<td>Imprimir Etiqueta <a class="fa fa-caret-down fa-2x azul hoverazul pointer" title="Seleciona tudo / Inverte seleção" onclick="check('impremeamostra','checked');"></a></td>
							</tr>
						</thead>
						<tbody>
							<?$ip =0;//variavel para o form
							foreach($rescount["data"] as $k => $row){
								$ip=$ip+1;
								if($row['alerta']=="Y"){
									$strback="background-color:#FF8491;";
								}else{
									$strback="";
								}
								?>
								<tr class="res1" style="<?=$strback?>">
									<td nowrap style="border:1px dotted;" ><?=$row["exercicio"]  ?></td>
									<td nowrap style="text-align: center; border:1px dotted; cursor:pointer; color:blue;" onclick="janelamodal('?_modulo=<?=$modamostra?>provisorio&_acao=u&idamostra=<?=$row["idamostra"]?>')"><?=$row["idregistro"] ?></td>
									<td nowrap style="border:1px dotted;"><?=$row["nome"]?></td>		    
									<td style="border:1px dotted;"><?=strip_tags($row["teste"])?></td>
									<td style="border:1px dotted;" align='center' >
										<span style="display:inline;" roh="true">  
											<input name="_<?=$ip?>_u_amostra_idamostra" type="hidden" value="<?=$row["idamostra"]?>">			
											<input name="_<?=$ip?>_u_amostra_status" type="hidden" value="ABERTO">
											<input name="_<?=$ip?>_u_amostra_idunidade" type="hidden" value="<?=$row['idunidade']?>">
											<input name="_<?=$ip?>_u_amostra_exercicio" type="hidden" value="<?=$row["exercicio"]  ?>">
											<input name="_<?=$ip?>_u_amostra_idregistro" type="hidden" value="<?=$row["idregistro"]  ?>">
															
											<input name="amostra" style="background-color:#cccccc;" atname="checked" value="<?=$row["idamostra"]?>" type="checkbox">	
										</span>	
									</td>
									<td align='center'>
										<div id="onclicknaoimprime<?=$row["idamostra"]?>">
											<input style="background-color:#cccccc;" disabled type="checkbox">	
										</div>
										<div style="display: none" id="onclickimprime<?=$row["idamostra"]?>">
											<input name="impremeamostra" value="<?=$row["idamostra"]?>" idunidade ="<?=$row["idunidade"]?>" type="checkbox">	
										</div>
									</td>
								</tr>   
							<?}?>							
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
								<button class="btn btn-danger btn-xs" onclick="transferir(this);">
									<i class="fa fa-exchange"></i> Transferir
								</button>	
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>	
							<td class='nowrap'>
								&nbsp;&nbsp;&nbsp;
								<button id="onclicknaoimprime" class="btn btn-xs" alt="Imprimir Etiquetas" style="cursor: default;">
									<i class="fa fa-print"></i> Imprimir Selecionados
								</button>	
								<button style="display: none" id="onclickiimprime" class="btn btn-danger btn-xs" onclick="imprimeTestes();">
									<i class="fa fa-print"></i> Imprimir Selecionados
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
//FUNÇÃO PARA Transferir 
function transferir(vthis){
	//pega todos os inputs checkados 		
	var inputprenchido= $("#inftable").children().find("input:checkbox:checked");

	//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
	var vsubmit= $(inputprenchido).parent().parent().find("input:text, input:hidden").serialize();
	vsubmit=vsubmit.concat("&status=ABERTO");
	//Alterado para enviar todas as impressões de uma vez (LTM - 17/08/2020 - 367226)
	CB.post({
		objetos: vsubmit		
		,parcial:true
		,refresh: false
		,msgSalvo:"Transferido"
		,posPost: function(){
			$('input[name=amostra]').each(function(index, vobj) {
				if (vobj.type == "checkbox") {
					if (vobj.checked) {
						$("#onclickimprime"+vobj.value).css('display','block');
						$("#onclicknaoimprime"+vobj.value).css('display','none');
						$("#onclickimprime"+vobj.value).find('input:checkbox').prop('checked', true);
						$("#onclickiimprime").css('display','block');
						$("#onclicknaoimprime").css('display','none');						
					} 
				}
			});
			$("input[name=amostra]:checked").remove('');
		}
	})
}

function imprimeTestes(){
	var imprimir=true;
	CB.imprimindo=true;
	var vsubmit = '';
	var virg = '';
	var cbpostEnvia = '';
	var com = '';	

	//pega todos os inputs checkados 	
	//Alterado para enviar todas as impressões de uma vez (LTM - 17/08/2020 - 367226)	
	$("input[name=impremeamostra]:checked").each(function(index, vobj) {
		vsubmit += virg + $(vobj).val();
		virg = ',';
		cbpostEnvia += com+"_x"+index+"_u_amostra_idamostra="+$(vobj).val()+"&_x"+index+"_u_amostra_status=FECHADO"+"&_x"+index+"_u_amostra_idunidade="+$(vobj).attr('idunidade');
		com = '&';
	});

	//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
	vsubmit = "idamostra="+vsubmit+"&status=ABERTO";

	if(CB.lotesImpressaoAlterado){
		if(!confirm("Você alterou os lotes de impressão.\nDeseja realmente enviar para a impressora?")){
			imprimir=false;
		}
	}

	//Alterado para enviar todas as impressões de uma vez (LTM - 17/08/2020 - 367226)
	if(imprimir){
		$.ajax({
			type: "get",
			url : "ajax/impetiqueta.php?impressora=IMP_DIAGNOSTICO&"+vsubmit,
			//url : "ajax/impetiqueta.php?impressora=IMP_DIAGNOSTICO_PROVISORIO&"+vsubmit,
			success: function(data){
				console.log(data);
				alertAzul("Enviado para impressão","",1000);
				CB.lotesImpressaoAlterado=false;
				if($("[name=_1_u_amostra_status]").val() && $("[name=_1_u_amostra_status]").val()=="ABERTO"){
					CB.post({
						objetos: cbpostEnvia
						,parcial:true
						,refresh: false
						,callback: function(){
							CB.oBtNovo.removeClass("disabled");
							CB.oBtSalvar.addClass("disabled");							
						}
						,posPost: function(){
							console.log(inIdamostra);
							//$("#"+inIdamostra).removeClass("azulclaro");
							//$("."+inIdamostra).removeClass("azulclaro");
							//$("#"+inIdamostra).addClass("verdeclaro");	
							//$("."+inIdamostra).addClass("verdeclaro");	
							console.log('inIdamostra');
						}
					});
				}
			}
		});
	}
}

function check(inobj, incheckname) 
{
	$('input[name='+inobj+']').each(function(index, vobj) {
  		if (vobj.type == "checkbox") {
			if (vobj.getAttribute('atname').indexOf(incheckname)  >= 0) {
			if (vobj.checked) {
					vobj.checked = false;
				} else {
					vobj.checked = true;
				}
			}
		}
	});
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