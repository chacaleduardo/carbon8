<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lotetipo";
$pagvalcampos = array(
	"idlotetipo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from lotetipo where idlotetipo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// ********************************************* 04/12/2019 POR GABRIEL TIBURCIO ******************************************* 
//
// FUNÇÃO BUSCA O ID DO TIPOTAGCAMPO E DEVOLVE PARA O ATRIBUTO VALUE DO BUTTON
// O ATRIBUTO VALUE DO BUTTON É UTILIZADO NAS FUNÇÕES JAVASCRIPT NO FINAL DESSE ARQUIVO
function buscaid($campo){
	global $_1_u_lotetipo_idlotetipo;
	$sql="SELECT idlotetipocampos FROM lotetipocampos WHERE 1 ".getidempresa('idempresa','lotetipo')." and campo='".$campo."' AND idlotetipo ='".$_1_u_lotetipo_idlotetipo."'";
    $resc = d::b()->query($sql) or die("Erro ao recuperar id campo: ".d::b()->error);
    $nrow = mysqli_num_rows($resc);
    $row = mysqli_fetch_assoc($resc);
    if($nrow == 1){
        return $row["idlotetipocampos"];
    }else{
        return "";
    }
}

function listaProdserv(){
	global $_1_u_lotetipo_idlotetipo,$_1_u_lotetipo_lotetipo,$_1_u_lotetipo_especial;

	$sql="SELECT l.idlotetipoprodserv,p.idprodserv,p.descr,p.comprado,p.fabricado,p.venda,p.comissionado,p.material,p.especial
	FROM lotetipoprodserv l join prodserv p on (l.idprodserv = p.idprodserv)
	WHERE
		1
		".getidempresa('p.idempresa','lotetipo')."
		AND p.status = 'ATIVO'
		AND l.idlotetipo = ".$_1_u_lotetipo_idlotetipo;

    $resc = d::b()->query($sql) or die("Erro ao recuperar lista prodserv: ".d::b()->error);
	
	$tabela = "<table>";
    while($row = mysqli_fetch_assoc($resc)){
		$title = "";
		$estilo = "";
		switch($_1_u_lotetipo_lotetipo){
			case 'COMPRADO':
				if($row["comprado"] == 'N'){
					$title .= "title ='Item não tem a mesma classificação na prodserv: ".$_1_u_lotetipo_lotetipo."'";
					$estilo .= "style = 'color:red;'";
				}
				break;
			case 'FORMULADO':
				if($row["fabricado"] == 'N'){
					$title .= "title ='Item não tem a mesma classificação na prodserv: ".$_1_u_lotetipo_lotetipo."'";
					$estilo .= "style = 'color:red;'";
				}
				break;
			case 'VENDA':
				if($row["venda"] == 'N'){
					$title .= "title ='Item não tem a mesma classificação na prodserv: ".$_1_u_lotetipo_lotetipo."'";
					$estilo .= "style = 'color:red;'";
				}
				break;
			case 'COMISSIONADO':
				if($row["comissionado"] == 'N'){
					$title .= "title ='Item não tem a mesma classificação na prodserv: ".$_1_u_lotetipo_lotetipo."'";
					$estilo .= "style = 'color:red;'";
				}
				break;
			case 'MATERIAL':
				if($row["material"] == 'N'){
					$title .= "title ='Item não tem a mesma classificação na prodserv: ".$_1_u_lotetipo_lotetipo."'";
					$estilo .= "style = 'color:red;'";
				}
				break;
			default:
				$title .= "";
				$estilo .= "style = 'color:red;'";
				break;
		}

		if($_1_u_lotetipo_especial != $row["especial"]){
			if(!empty($title)){
				$title = rtrim($title,"'");
				$title .= ", Autógena'";
			}else{
				$title .= "title ='Item não tem a mesma classificação na prodserv: Autógena'";
				$estilo .= "style = 'color:red;'";
			}
		}

		$tabela .= "<tr><td><a href='?_modulo=prodserv&_acao=u&idprodserv=".$row["idprodserv"]."' target='_blank' ".$title." ".$estilo.">".$row["descr"]."</a></td>";
		$tabela .= "<td><i class='fa fa-trash vermelho fade hoververmelho' title='Desvincular'  onclick='desvincular(".$row["idlotetipoprodserv"].");'></i></td></tr>";

	}
	$tabela .= "</table>";

	echo $tabela;
}

function getProdserv(){
	global $JSON,$_1_u_lotetipo_lotetipo,$_1_u_lotetipo_idlotetipo,$_1_u_lotetipo_especial;

	switch($_1_u_lotetipo_lotetipo){
		case 'COMPRADO':
			$aux = " AND p.comprado = 'Y'";
			break;
		case 'FORMULADO':
			$aux = " AND p.fabricado = 'Y'";
			break;
		case 'VENDA':
			$aux = " AND p.venda = 'Y'";
			break;
		case 'COMISSIONADO':
			$aux = " AND p.comissionado = 'Y'";
			break;
		case 'MATERIAL':
			$aux = " AND p.material = 'Y'";
			break;
		default:
			$aux = "";
			break;
	}

	($_1_u_lotetipo_especial == 'Y')? $especial = " AND p.especial = 'Y'": $especial = "";

	$sql="SELECT p.idprodserv,p.descr
	FROM prodserv p
	WHERE
		1
		".getidempresa('p.idempresa','lotetipo')."
		".$aux."
		".$especial."
		AND status = 'ATIVO'
		AND not exists (SELECT 1 FROM lotetipoprodserv l WHERE l.idprodserv = p.idprodserv and l.idlotetipo = ".$_1_u_lotetipo_idlotetipo.")";
    $resc = d::b()->query($sql) or die("Erro ao recuperar id campo: ".$sql);

	$arrret = array();
	$i = 0;
    while($row = mysqli_fetch_assoc($resc)){
		$arrret[$i]["idprodserv"]=$row["idprodserv"];
		$arrret[$i]["descr"]=$row["descr"];
		$i++;
	}
	return $JSON->encode($arrret);
}

if(!empty($_1_u_lotetipo_idlotetipo)){
	$jProdserv = getProdserv();
}

?>
<style>
	#campos{
		width:100%;
	}
	#campos button{
		width: 100%;
	}

	i{
		cursor: pointer;
	}
</style>
<div class="row ">
    <div class="col-md-4">
		<div class="panel panel-default" >
			<div class="panel-heading ">Tipo de Lote</div>
			<div class="panel-body" style="padding-top:10px !important;">
				<input name="_1_<?=$_acao?>_lotetipo_idlotetipo"  type="hidden" value="<?=$_1_u_lotetipo_idlotetipo?>">
				<table style="width: 100%;">
					<tr>
						<td align="right" style="width: 1%;">Classificação:</td>
						<td>
							<select name="_1_<?=$_acao?>_lotetipo_lotetipo" vnulo="">
							<option></option>
							<?fillselect("select 'COMPRADO','Comprado'	
                                            union select 'FORMULADO','Formulado'
											union select 'VENDA','Venda'
											union select 'COMISSIONADO','Comissionado'
											union select 'MATERIAL','Material'",$_1_u_lotetipo_lotetipo);?>	
							</select>
						</td>
						<?if(!empty($_1_u_lotetipo_idlotetipo)){?>
							<td style="text-align: end;">
								Autógena
							</td>
							<td>
								<?if($_1_u_lotetipo_especial=="Y"){?>
									<i style="padding-left: 0px;" class="fa fa-star fa-1x laranja  btn-lg pointer" onclick="alt('N','especial');" title="Alterar Autógena para Não"></i>
								<?}else{?>	
									<i style="padding-left: 0px;" class="fa fa-star fa-1x  btn-lg pointer" onclick="alt('Y','especial');" title="Alterar Autógena para Não"></i>
								<?}?>
							</td>
						<?}?>
					</tr>
					<tr>
						<td align="right"  style="width: 1%;">Status:</td>
						<td>
							<select name="_1_<?=$_acao?>_lotetipo_status" vnulo="">
								<option></option>
								<?fillselect("select 'ATIVO','Ativo'	
                                            union select 'INATIVO','Inativo'",$_1_u_lotetipo_status);?>	
							</select>
						</td>
					</tr>
				</table>

			</div>	
		</div>
    </div>
	
	<? 
	if(!empty($_1_u_lotetipo_idlotetipo)){

		$aux = 0;
		$botao="btn btn-default btn-sm pointer";

		// PARA ADICIONAR NOVOS CAMPOS, BASTA ACRESCENTAR NO FINAL DA LISTA ABAIXO O NOME DO CAMPO(banco de dados) E QUAL O NOME QUE IRÁ APARECER NA TELA(tela Tag Tipo)
		$listacampos = array
		("_partext" => "Part. Ext.",
		"_cliente" => "Cliente",
		"_formula" => "Fórmula",
		"_nnfe" => "NNfe",
		"_solipor" => "Solicitado por",
		"_isoladoem" => "Isolado em",
		"_tipoamostra" => "Tipo Amostra",
		"_tipificacao" => "Tipificação",
		"_fabricante" => "Fabricante",
		"_datafabricacao" => "Data Fabricação",
		"_vencimento" => "Vencimento",
		"_qtdpedida" => "Qtd. Pedida",
		"_qtdproduzida" => "Qtd. Produzida",
		"_unmedida" => "Un. Medida",
		"_obs" => "Observação",
		"_local" => "Local",
		"_eventosassoc" => "Eventos Associados",
		"_upload" => "Upload",
		"_reservas" => "Reservas",
		"_complotes" => "Comp. Lotes",
		"_compsellotes" => "Comp. Sel. Lotes",
		"_analiselote" => "Análise Lote");

		$jlistacampos = $JSON->encode($listacampos);
		?>
	
		<div class="col-md-8" >
			<div class="panel panel-default" >
				<div class="panel-heading ">
					<table style="width: 100%;">
						<tr>
							<td>Lote Campos</td>
							<td style="width: 10%;" nowrap>Marcar Todos:</td>
							<?if($_1_u_lotetipo_marcartodos=="Y"){?>
								<td style="width: 1%;"><input type="checkbox" onclick="alttodos('N')" checked></td>
							<?}else{?>
								<td style="width: 1%;"><input type="checkbox" onclick="alttodos('Y')"></td>
							<?}?>
						</tr>
					</table>
				</div>
				<div class="panel-body">

					<table id="campos">
							
							<tr>
								
								<? // Para cada campo na lista acima, crie um botão
								
								//print_r($listacampos);
								foreach ($listacampos as $chave => $valor){
									//?>
									<td>
										<button class="<?=$botao?>" id="<?=$chave?>" onclick="mostraCampo(this,'<?=$_1_u_lotetipo_idlotetipo?>')" value="<?=buscaid($chave)?>"><i class="fa fa-eye-slash"></i> <?=$valor?></button>
									</td>
								<?
									$aux++;
									if($aux == 5){ // Mudar esse valor para alterar o layout de apresentação dos botões. Nesse caso 5 botões por linha.
										$aux = 0;
									?>
										</tr><tr>
									<?}
								}?>
							</tr>
							
					</table>
				</div>

			</div>
		</div>
	<?}?>
</div>
<?if(!empty($_1_u_lotetipo_idlotetipo)){?>
<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<input id="prodserv" class="compacto" type="text" cbvalue placeholder="Selecione">
			</div>
			<div class="panel-body">
				<?=listaProdserv();?>
			</div>
		</div>
	</div>
</div>
<?}?>
<?
if(!empty($_1_u_lotetipo_idlotetipo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_lotetipo_idlotetipo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "lotetipo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<script>

	<?if(!empty($_1_u_lotetipo_idlotetipo)){?>
		$("[name=_1_u_lotetipo_lotetipo]").change(function() {
			var valor = $("[name=_1_u_lotetipo_lotetipo]").val();
			alt(valor,'lotetipo');
		});

		jProdserv = <?=$jProdserv?>;
		if(jProdserv != 0){
			$("#prodserv").autocomplete({
				source: jProdserv
				,delay: 0
				,create: function(){
					$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
						return $('<li>').append("<a>"+item.descr+"<span class='cinzaclaro'> "+item.idprodserv+"</span></a>").appendTo(ul);
					};
				}
				,select: function(event, ui){
					CB.post({
						objetos: "_x_i_lotetipoprodserv_idlotetipo="+$(":input[name=_1_u_lotetipo_idlotetipo]").val()+"&_x_i_lotetipoprodserv_idprodserv="+ui.item.idprodserv
						,parcial: true
					});
				}
			});
		}
		
		function alttodos(inval){
			var jlistacampos = <?=$jlistacampos?>;
			var str = "";
			var i = 0;
			if(inval == 'Y'){
				Object.keys(jlistacampos).forEach(key => {
					if(!($("#"+key).val())){
						str += "_x"+i+"_i_lotetipocampos_idlotetipo="+$("[name=_1_u_lotetipo_idlotetipo]").val()+"&_x"+i+"_i_lotetipocampos_campo="+key+"&";
						i++;
					}
				});
			}else{
				Object.keys(jlistacampos).forEach(key => {
					if($("#"+key).val()){
						str += "_x"+i+"_d_lotetipocampos_idlotetipocampos="+$("#"+key).val()+"&";
						i++;
					}
				});
			}

			str = str.slice(0, -1);

			CB.post({
				objetos: str
				,posPost: function(){
					CB.post({
						objetos: "_x_u_lotetipo_idlotetipo="+$("[name=_1_u_lotetipo_idlotetipo]").val()+"&_x_u_lotetipo_marcartodos="+inval
					});
				}
			});
		}
	<?}?>
	
	// ********************************* FUNÇÃO PARA MUDAR A COR DE FUNDO E O ÍCONE DE CADA BOTÃO QUE ESTÁ SELECIONADO ********************************** //
	$(document).ready(function(){
		$("#campos button").each(function(){
			if(this.value){
				$("#"+this.id).css("background-color","#5cb85c");
				$("#"+this.id).css("color","white");
				$("#"+this.id).css("border-color","#4cae4c");
				$("#"+this.id+" i").removeClass("fa fa-eye-slash").addClass("fa fa-eye");
			}
		});
	});
	
	// ******************************* FUNÇÃO QUE ADICIONA/DELETA O BOTÃO NA TABELA TIPOTAGCAMPOS PARA SER UTILIZADO NA TELA DE TAGS ***************************** //
	// 
	// OBS: É NECESSÁRIO QUE O CB POST DÊ REFRESH NA PÁGINA PARA QUE AS CORES DO BOTÃO E OS VALORES SEJAM ALTERADOS
	function mostraCampo(btn,tipo){
		var campo = btn.id;
		var valor = btn.value;
		if(!(valor)){
			CB.post({
				objetos: "_x_i_lotetipocampos_idlotetipo="+tipo+"&_x_i_lotetipocampos_campo="+campo
			});
		}else{
			CB.post({
				objetos: "_x_d_lotetipocampos_idlotetipocampos="+valor
			});
		}
	}

	function alt(inval,incampo){
		CB.post({
			objetos: "_x_u_lotetipo_idlotetipo="+$("[name=_1_u_lotetipo_idlotetipo]").val()+"&_x_u_lotetipo_"+incampo+"="+inval,
		});
	}

	function desvincular(inid){
		CB.post({
			objetos: "_x_d_lotetipoprodserv_idlotetipoprodserv="+inid
			,parcial:true
		});      
	}

</script>