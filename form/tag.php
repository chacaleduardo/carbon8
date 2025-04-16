<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../model/tag.php");

// QUERY
require_once(__DIR__."/querys/_iquery.php");
require_once(__DIR__."/querys/tag_query.php");
require_once(__DIR__."/querys/tipotagcampos_query.php");

// CONTROLLER
require_once(__DIR__."/controllers/tag_controller.php");
require_once(__DIR__."/controllers/tagtipo_controller.php");
require_once(__DIR__."/controllers/tipotagcampos_controller.php");
require_once(__DIR__."/controllers/unidade_controller.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "tag";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idtag" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from tag where idtag = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$idTag = $_1_u_tag_idtag;
$idTagTipo = $_1_u_tag_idtagtipo;
$tagReserva = false;
$classificacoes = TagController::buscarTagClass($_1_u_tag_idtagclass);
$idTagClass = $_1_u_tag_idtagclass;
$sequenciaDaTag = TagController::buscarSequenciaDaTag(cb::idempresa());
$idUnidade = $_1_u_tag_idunidade;
// Tags da classificacao prateleira
$tagsPrateleiras = TagController::buscarTagPorIdTagClassEIdEmpresa('4, 15', $idUnidade, cb::idempresa());

$campoTagSequence = "<td>
						<input  name='_1_".$_acao."_tag_tag' size='2' id='tag' type='hidden' value='$sequenciaDaTag'>
						<label class='alert-warning' style='text-transform:uppercase;'>$sequenciaDaTag</label>
					</td> ";

if($idTag)
{
	$camposVisiveis = TipoTagCamposController::buscarCampos($idTagTipo);
	$devices		= TagController::buscarDevicesPeloIdTag($idTag);
	$hasAtiv 		= count(TagTipoController::buscarAtividadesVinculadasPorIdTagTipo($idTagTipo)) ? true : false;

	$tagPai = SQL::ini(TagQuery::buscarPaiPeloIdTag(), [
		'idtag' => $idTag
	])::exec();
	
	$sqlBuscarTagReserva = TagReservaQuery::buscarPeloIdObjeto();
	$sqlBuscarTagReserva .= " ORDER BY tr.alteradoem DESC LIMIT 1;";
	
	$tagReserva = SQL::ini($sqlBuscarTagReserva, [
		'idobjeto' => $idTag,
		'tipoobjeto' => 'tag'
	])::exec();
	
	$tagLocada = TagController::buscarLocacao($idTag);

	$sequenciaDaTag = TagController::buscarSequenciaDaTag(cb::idempresa(), $idTag);

	$campoTagSequence = "<div class='flex w-100'>
							<label class='idbox w-100'>[$sequenciaDaTag]</label>
							<i title='Etiqueta da TAG' class='fa fa-print pull-right fa-lg cinza pointer hoverazul px-2' onclick='showModalEtiqueta()'></i>
						</div>";

	$unidades = UnidadeController::buscarUnidadesPorIdEmpresa(cb::idempresa(), $_1_u_tag_idunidade);

	$revisado='Y';
	$checkedob="";

	if($_1_u_tag_revisado =='Y')
	{
		$revisado='N';
		$checkedob="checked";
	}

	$nf = [];
	$arquivosNf = [];
	
	if($_1_u_tag_idobjetoorigem)
	{
		$nf = TagController::buscarNfPorIdObjetoOrigem($_1_u_tag_idobjetoorigem);
		$arquivosNf = TagController::buscarArquivosNfPorIdNfItem($_1_u_tag_idobjetoorigem);
	}

	$tagDim = TagController::buscarTagDimPorIdTag($idTag);
	$sqllink = UnidadeController::buscarModuloDaUnidadePorIdunidade($idUnidade);

	$link = "lotealmoxarifado";

	if($sqllink)
	{
		$link = $sqllink['modulo'];
	}

	$arrTagsLocalizadaEm = TagController::buscarTagPaiOuFilhoPorIdTag($idTag);
	$arrTagsFilhas = TagController::buscarTagPaiOuFilhoPorIdTag($idTag, 'idtagpai');

	$arquivosDeUmaLocacao = TagController::buscarArquivoDaLocacaoPorIdObjeto($idTag);
}

$rotulo = getStatusFluxo($pagvaltabela, 'idtag', $_1_u_tag_idtag)['rotulo'] ? getStatusFluxo($pagvaltabela, 'idtag', $_1_u_tag_idtag)['rotulo'] : $_1_u_tag_status;

if($_acao != 'u')
{
	$statusRotulo = TagController::buscarStatusRotuloPorTipo();
	$rotulo = $statusRotulo['rotulo'];
	$_1_u_tag_status = $statusRotulo['statustipo'];
}

$idTagPai = null;

//Valida se tem permissão para alocar equipamentos
$permissaoParaLocar = TagController::verificarPermissaoParaLocar();
$tagDescricoes = TagController::buscarDescricaoDasTags(true);
$tagDimLotes = [];
$letrasVisiveis = [];
?>

<link href="<?= "/form/css/tag_css.css?_".date('dmYhms') ?>" rel="stylesheet" />

<script>
function atualizacampos(vthis,idtag){
    CB.post({
		objetos: "_x_u_tag_idtag="+idtag+"&_x_u_tag_idtagtipo="+vthis,
		refresh:"refresh"
	});
}

</script>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading py-3 py-md-2">			
				<div class="w-100 flex flex-wrap align-items-center flex-between">
					<!-- IDTAG -->
					<input name="_1_<?=$_acao?>_tag_idtag"  type="hidden"	value="<?=$_1_u_tag_idtag?>" readonly='readonly'>
					<div class="flex flex-wrap form-group col-xs-12 col-md-1">
						<!-- Tag -->
						<label for="" class="label-heading">Tag:</label>
						<?= $campoTagSequence ?>
						<?
							if(!empty($idTagClass)){
								$disabled = "disabled='disabled'";
							}
						?>
					</div>
					<div class="flex flex-wrap form-group col-xs-12 col-md-2">
						<label for="" class="label-heading">Classificação:</label>
						<input type="hidden" name="tag_oldidtagclass" value=<?=$idTagClass?>>
						<select name="_1_<?=$_acao?>_tag_idtagclass" id="idtagclass" vnulo onchange="showdim(this);" class="form-control">
							<option></option>
							<?fillselect($classificacoes, $idTagClass);?>
						</select> 
					</div>
					<div class="flex flex-wrap form-group col-xs-12 col-md-2">
						<label for="" class="label-heading">Descrição:</label>
						<input id="tag-descricao" name="_1_<?=$_acao?>_tag_descricao" value="<?= strtoupper($_1_u_tag_descricao ?? '') ?>" type="text" class="form-control w-100" vnulo>
					</div>
					<? if($_1_u_tag_idtag) { ?>
						<div class="flex flex-wrap form-group col-xs-12 col-md-2">
							<label for="" class="label-heading">Unidade:</label>
							<select name="_1_<?=$_acao?>_tag_idunidade" class="form-control w-100" vnulo>
								<option value=""></option>
								<?fillselect($unidades, $idUnidade);?>		
							</select>
						</div>
						<div class="flex col-xs-12 col-md-6 col-md-2 flex-between px-0">
							<div class="col-xs-6 col-md-6 flex">
								<label for="" class="label-heading">Revisado:</label>
								<input name="_1_<?=$_acao?>_tag_revisado" type="checkbox" atval="<?=$revisado?>" value="<?=$_1_u_tag_revisado?>" <?=$checkedob?> idtag="<?=$_1_u_tag_idtag?>" onclick="flgrevisado(this)">
							</div>
							<div class="col-xs-6 col-md-6 flex flex-between">
								<i title="Informações Gerais" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/reltag.php?idtag=<?=$_1_u_tag_idtag?>')"></i>
								<i title="Informações Principais" class="fa fa-file pull-right  cinza hoverazul" onclick="janelamodal('report/reltag.php?idtag=<?=$_1_u_tag_idtag?>&listaacao=N')"></i>
								<?if($permissaoParaLocar && $_1_u_tag_status == 'LOCADO'){?>
									<a title="Locar Tag" style="font-size: 20px;" class="fa fa-sitemap fade pointer hoverazul" onclick="modalEmpresa()" target="_blank"></a>
								<? } ?>
							</div>
						</div>
					<? }
					foreach($devices as $device)
					{ ?>
						<label class="hide"><? if ($device['url']){ ?>Supervisório:<?}?></label>
						<label class="hide">
							<? if ($device['url']){ ?><a href="<?=$device['url'];?>" target="_blank"><img style="width:24px; margin-left:10%" src="../inc/img/d1.png" alt="Monitoramento" srcset="" title="Monitoramento"></a><? } ?>
						</label>
						<label class="hide">
							<? if ($device['urlplanta']){ ?><a href="<?=$device['urlplanta'];?>" target="_blank"><img style="width:24px" src="../inc/img/d2.png" alt="Planta" srcset="" title="Planta"></a><? } ?>
						</label>
						<label class="hide">
							<? if ($device['urlplanta2']){ ?><a href="<?=$device['urlplanta2'];?>" target="_blank"><img style="width:24px" src="../inc/img/d3.png" alt="Monitoramento Planta" srcset="" title="Monitoramento Planta"></a><? } ?>
						</label>
					<? } ?>
					<div class="flex col-xs-12 col-md-2 flex-between flex-col form-group">
						<label for="" class="label-heading">Status:</label>
						<input name="_1_<?=$_acao?>_tag_status" type="hidden" value="<?= $_1_u_tag_status ?>" />
						<label class="alert-warning w-100" title="<?=$_1_u_tag_status?>" id="statusButton"><?=  mb_strtoupper($rotulo,'UTF-8') ?> </label>
					</div>
				</div>
			</div>
			<div class="panel-body main-form">                    
			<? if(!empty($idTagClass)){ ?>
				<? if(in_array($idTagClass, [4, 15])){echo "<hr>";}else{$display='none';}?>
					<table>
						<tr style=" display:<?=$display?>;" id="trdimensoes" > 
							<td align="right">Linhas:</td> 
							<td>
								<select <?=$disabled?> name="_1_<?=$_acao?>_tag_linha" vnulo>
								<option value=""></option>
										<?fillselect(TagController::$linhas, $_1_u_tag_linha);?>		
								</select>
							</td>  
							<td align="center">X</td>
							<td align="right">Colunas:</td> 
							<td>
								<select <?=$disabled?> name="_1_<?=$_acao?>_tag_coluna" vnulo>
								<option value=""></option>
										<?fillselect(TagController::$colunas ,$_1_u_tag_coluna);?>
								</select>
							</td>
							<td align="center">X</td>
							<td align="right">Caixas:</td> 
							<td>
								<select <?=$disabled?> name="_1_<?=$_acao?>_tag_caixa" vnulo>
								<option value=""></option>
										<?fillselect(TagController::$caixas, $_1_u_tag_caixa);?>
								</select>
							</td>
						</tr>
					</table>
					<hr>
					<?if(!empty($_1_u_tag_idtag)){ ?>
					<div class="row w-100 flex flex-wrap">
						<div class="col-xs-12 col-md-3 form-group">


							<label for="_tipo">Tipo:</label>
							<div class="w-100 inputs d-flex">
								<div class="col-xs-10 col-md-9 py-0 px-0">
									<select name="_1_<?=$_acao?>_tag_idtagtipo" id="_tipo" class="select-picker form-control" onchange="atualizacampos(this.value,<?=$_1_u_tag_idtag?>)" data-live-search="true">
										<option value=""></option>
										<?
											fillselect(TagTipoController::buscarTagTipoPorIdTagClass($idTagClass, true, $_1_u_tag_idtagtipo), $_1_u_tag_idtagtipo);
										?>
									</select>
								</div>


								
								<div class="col-xs-2 col-md-3 py-0 px-0 d-flex justify-between align-items-center">
									<?if($_1_u_tag_idtagtipo){?>
										<span class="p-3 addon col-xs-6" title="Editar opção selecionada" onclick="janelamodal('?_modulo=tagtipo&_acao=u&idtagtipo=<?=$_1_u_tag_idtagtipo?>')"><i class="fa fa-pencil pointer"></i></span>
									<?}?>
									<span class="p-3 addon col-xs-6" title="Nova Opção" onclick="janelamodal('?_modulo=tagtipo&_acao=i')"><i class="fa fa-plus pointer"></i></span>
								</div>
								<? if($hasAtiv) { ?>
									<div class="alert alert-warning has-ativ">
										<b>Há atividade(s) vínculada(s) à este tipo</b>
										<div></div>
									</div>
								<? } ?>

								
							</div>



						</div>
						<? if(!empty($_1_u_tag_idtagtipo)){ ?>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_renavam", $camposVisiveis) ?:'hidden' ?>">
								<label for="_renavam" class="">Renavam:</label>
								<input name="_1_<?=$_acao?>_tag_renavam" id="_renavam" type="text" value="<?=$_1_u_tag_renavam?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_placa", $camposVisiveis) ?:'hidden' ?>">
								<label for="_placa" class="">Placa:</label>
								<input name="_1_<?=$_acao?>_tag_placa" id="_placa" type="text" value="<?=$_1_u_tag_placa?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_tara", $camposVisiveis) ?:'hidden' ?>">
								<label for="_tara" class="">Tara:</label>
								<input name="_1_<?=$_acao?>_tag_tara" id="_tara" type="text" value="<?=$_1_u_tag_tara?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_tpCar", $camposVisiveis) ?:'hidden' ?>">
								<label for="_tpCar" class="">Carroceria:</label>
								<select name="_1_<?=$_acao?>_tag_tpCar" id="_tpCar" class="form-control">
									<?
										fillselect(TagController::$carrocerias, $_1_u_tag_tpCar);
									?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_tpRod", $camposVisiveis) ?:'hidden' ?>">
								<label for="_tpRod" class="">Rodado:</label>
								<select name="_1_<?=$_acao?>_tag_tpRod" id="_tpRod" class="form-control">
									<?
										fillselect(TagController::$rodados, $_1_u_tag_tpRod);
									?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_uf", $camposVisiveis) ?:'hidden' ?>">
								<label for="_uf" class="col">UF Licenciamento:</label>
								<select name="_1_<?=$_acao?>_tag_uf" id="_uf" class="form-control">
									<? fillselect(TagController::$ufBr, $_1_u_tag_uf); ?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_funcionario", $camposVisiveis) ?:'hidden' ?>">
								<label for="_funcionario" class="">Funcionário/Pessoa:</label>
								<input name="_idpessoa_old" id="_idpessoa_old" type="hidden" value="<?=$_1_u_tag_idpessoa?>"/>
								<select name="_1_<?=$_acao?>_tag_idpessoa" id="_funcionario" class="select-picker form-control" data-live-search="true">
									<option value=""></option>
									<? //Alterado para pegar somente as pessoas que são da empresa logada (Lidiane - 15/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)			
										fillselect(TagController::buscarPessoasDaEmpresaLogada($_1_u_tag_idpessoa), $_1_u_tag_idpessoa);
									?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_satusveiculo", $camposVisiveis) ?:'hidden' ?>">
								<label for="_satusveiculo" class="">Status Veículo:</label>
								<select name="_1_<?=$_acao?>_tag_satusveiculo" id="_satusveiculo" class="select-picker form-control" data-live-search="true">
									<option value=""></option>
									<? //Alterado para pegar somente as pessoas que são da empresa logada (Lidiane - 15/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)			
										fillselect(TagController::$statusVeiculo, $_1_u_tag_satusveiculo);
									?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_chassi", $camposVisiveis) ?:'hidden' ?>">
								<label for="_chassi" class="">Chassi:</label>
								<input name="_1_<?=$_acao?>_tag_chassi" id="_chassi" type="text" value="<?=$_1_u_tag_chassi?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_modelo", $camposVisiveis) ?:'hidden' ?>">
								<label for="_modelo" class="">Modelo:</label>
								<input name="_1_<?=$_acao?>_tag_modelo" id="_modelo" type="text" value="<?=$_1_u_tag_modelo?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_fabricante", $camposVisiveis) ?:'hidden' ?>">
								<label for="_fabricante" class="">Fabricante:</label>
								<input name="_1_<?=$_acao?>_tag_fabricante" id="_fabricante" type="text" value="<?=$_1_u_tag_fabricante?>" class="form-control" />
							</div>	
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_crlv", $camposVisiveis) ?:'hidden' ?>">
								<label for="_crlv" class="">CRLV:</label>
								<input name="_1_<?=$_acao?>_tag_crlv" id="_crlv" type="text" value="<?=$_1_u_tag_crlv?>" class="form-control" />
							</div>			
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_ano", $camposVisiveis) ?:'hidden' ?>">
								<label for="_ano" class="">Ano Fabricação Veículo:</label>
								<input name="_1_<?=$_acao?>_tag_ano" id="_ano" type="text" value="<?=$_1_u_tag_ano?>" class="form-control" />
							</div>			
							
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_nfe", $camposVisiveis) ?:'hidden' ?>">
								<label for="_nfe" class="">NFe:</label>
								<?
								if(!count($nf))
								{
									if(!empty($_1_u_tag_numnfe)){?>
										<input name="_1_<?=$_acao?>_tag_numnfe" type="text"  value="<?=$_1_u_tag_numnfe?>" class="form-control"/>
									<?}else{?>
										<input name="_1_<?=$_acao?>_tag_numnfe" type="text"  value="<?=$_1_u_tag_numnfe?>" class="form-control"/>
									<?}
								}

								foreach($nf as $item)
								{ ?>
									<input type="text"  value="<?=$item["nnfe"]?>" readonly class="col-xs-10">
									<? if(empty($rowReserva['idtag'])) { ?>
										<a class="fa fa-bars pointer hoverazul col-xs-2" title="Nota Fiscal" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$item['idnf'] ?>')"></a>
									<? } else { ?>
										<a class="fa fa-print pointer hoverazul col-xs-2" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?=$item['idnf']?>')"></a>
									<? }
								} ?>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_localizacao", $camposVisiveis) ?:'hidden' ?>">
								<label for="_localizacao" class="">Localização:</label>
								<select name="idtagpai" class="select-picker form-control" onchange="inserirlocal(this);" id="_localizacao" data-live-search="true">
									<option value=""></option>
									<!-- Alteração realizada referente ao quarto térmico  para permitir adicionar ao mesmo apenas equipamentos. (ALBT - 09/04/2021 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=416013 ) -->
									<?fillselect(TagController::buscarLocalizacoes(), $idTagPai);?>			
								</select>
							</div>							
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_nserie", $camposVisiveis) ?:'hidden' ?>">
								<label for="_nserie" class="">Nº Série:</label>
								<input name="_1_<?=$_acao?>_tag_nserie" id="_nserie" type="text" size="12" value="<?=$_1_u_tag_nserie?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_ip", $camposVisiveis) ?:'hidden' ?>">
								<label for="_ip" class="">IP:</label>
								<input name="_1_<?=$_acao?>_tag_ip" type="text" size="2" id="_ip" value="<?=$_1_u_tag_ip?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_varcarbon", $camposVisiveis) ?:'hidden' ?>">
								<label for="_varcarbon" class="">Var Carbon:</label>
								<div class="flex align-items-center flex-between">
									<div class="col-xs-11 px-0">
										<select name="_1_<?=$_acao?>_tag_varcarbon" id="_varcarbon" class="form-control">
											<option value=""></option>
											<?fillselect(TagController::buscarVarCarbonPorIdTag($idTag) ,$_1_u_tag_varcarbon);?>
										</select>
									</div>
									<a class="fa fa-info-circle pointer hoverazul" title="Imp CQ - CQ - Etiquetas do biotério. Imp Sementes - Etiqueta sementes autógenas. Imp Almoxarifado - Etiqueta partidas do almoxarifado,Imp Almoxarifado Itens - Imprime lista com itens da nota fiscal para envio, Imp Produção Zebra - Etiqueta lote produção impressora Zebra. Imp Produção Sementes - Etiqueta lote produção com as sementes." ></a>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_exatidaorequerida", $camposVisiveis) ?:'hidden' ?>">
								<label for="_exatidaorequerida" class="">Exatidão Requerida:</label>
								<input name="_1_<?=$_acao?>_tag_exatidao" type="text" size="6" id="_exatidaorequerida" value="<?=$_1_u_tag_exatidao?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_padraotempmin", $camposVisiveis) ?:'hidden' ?>">
								<label for="_padraotempmin" class="">Parâmetro Mínimo:</label>
								<input name="_1_<?=$_acao?>_tag_padraotempmin" type="text" size="2" id="_padraotempmin" value="<?=$_1_u_tag_padraotempmin?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_padraotempmax", $camposVisiveis) ?:'hidden' ?>">
								<label for="_padraotempmax" class="">Parâmetro Máximo:</label>
								<input name="_1_<?=$_acao?>_tag_padraotempmax" type="text" size="2"	value="<?=$_1_u_tag_padraotempmax?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_processador", $camposVisiveis) ?:'hidden' ?>">
								<label for="_processador" class="">Processador:</label>
								<input name="_1_<?=$_acao?>_tag_processador" type="text" size="2" id="_processador" value="<?=$_1_u_tag_processador?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_memoria", $camposVisiveis) ?:'hidden' ?>">
								<label for="_memoria" class="">Memória:</label>
								<input name="_1_<?=$_acao?>_tag_memoria" type="text" size="2" id="_memoria" value="<?=$_1_u_tag_memoria?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_hd", $camposVisiveis) ?:'hidden' ?>">
								<label for="_hd" class="">HD:</label>
								<input name="_1_<?=$_acao?>_tag_hd" type="text" size="2" id="_hd" value="<?=$_1_u_tag_hd?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_video", $camposVisiveis) ?:'hidden' ?>">
								<label for="_video" class="">Vídeo:</label>
								<input name="_1_<?=$_acao?>_tag_video" type="text" size="2" id="_video"	value="<?=$_1_u_tag_video?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_so", $camposVisiveis) ?:'hidden' ?>">
								<label for="_so" class="">SO:</label>
								<select name="_1_<?=$_acao?>_tag_so" id="_so" class="form-control">
									<option value=""></option>
									<?fillselect(TagController::$sistemasOperacionais, $_1_u_tag_so);?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_nemei", $camposVisiveis) ?:'hidden' ?>">
								<label for="_nemei" class="">Nº Emei:</label>
								<input name="_1_<?=$_acao?>_tag_nemei" type="text" size="2" id="_nemei"	value="<?=$_1_u_tag_nemei?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_plano", $camposVisiveis) ?:'hidden' ?>">
								<label for="_plano" class="">Plano:</label>
								<input name="_1_<?=$_acao?>_tag_plano" type="text" size="2" id="_plano"	value="<?=$_1_u_tag_plano?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_nchip", $camposVisiveis) ?:'hidden' ?>">
								<label for="_nchip" class="">Nº Chip:</label>
								<input name="_1_<?=$_acao?>_tag_nchip" type="text" size="2" id="_nchip"	value="<?=$_1_u_tag_nchip?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_office", $camposVisiveis) ?:'hidden' ?>">
								<label for="_office" class="">Office:</label>
								<select name="_1_<?=$_acao?>_tag_office" id="_office" class="form-control">
									<option value=""></option>
									<?fillselect(TagController::$tagOffice, $_1_u_tag_office);?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_voltagem", $camposVisiveis) ?:'hidden' ?>">
								<label for="_voltagem" class="">Voltagem:</label>
								<select name="_1_<?=$_acao?>_tag_voltagem" id="_voltagem" class="form-control">
									<option value=""></option>
									<?fillselect("select '110V','110V' union select '220V','220V' union select 'BIVOLT','BIVOLT'",$_1_u_tag_voltagem);?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_consumo", $camposVisiveis) ?:'hidden' ?>">
								<label for="_consumo" class="">Consumo:</label>
								<input name="_1_<?=$_acao?>_tag_consumo" type="text" id="_consumo" value="<?=$_1_u_tag_consumo?>" placeholder="Watts" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_datacalibracao", $camposVisiveis) ?:'hidden' ?>">
								<label for="_datacalibracao" class="">Venc. C.:</label>
								<input autocomplete="off" title="Vencimento Calibração" type="text" name="_1_<?=$_acao?>_tag_datacalibracao" id="_datacalibracao" class="calendario" value="<?=$_1_u_tag_datacalibracao?>"  class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_dataqualificacao", $camposVisiveis) ?:'hidden' ?>">
								<label for="_dataqualificacao" class="">Venc. Q.:</label>
								<input autocomplete="off" title="Vencimento Qualificação" type="text" name="_1_<?=$_acao?>_tag_dataqualificacao" id="_dataqualificacao" class="calendario" value="<?=$_1_u_tag_dataqualificacao?>"  class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_calibracao", $camposVisiveis) ?:'hidden' ?>">
								<label for="_calibracao" class="">Calibração:</label>
								<select name="_1_<?=$_acao?>_tag_calibracao" id="_calibracao" class="form-control">
									<option value=""></option>
									<?fillselect("select 'RBC','RBC' union select 'RASTREAVEL','Rastreável' union select 'N/A','N/A'",$_1_u_tag_calibracao);?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_qualificacao", $camposVisiveis) ?:'hidden' ?>">
								<label for="_qualificacao" class="">Qualificação:</label>
								<select name="_1_<?=$_acao?>_tag_qualificacao" id="_qualificacao" class="form-control">
									<option value=""></option>
									<?fillselect("select 'RBC','RBC' union select 'RASTREAVEL','Rastreável' union select 'HVAC','HVAC' union select 'N/A','N/A'",$_1_u_tag_qualificacao);?>
								</select>
							</div>
							<?if($_1_u_tag_status == 'ATIVO' && $_1_u_tag_idtagtipo == 21){

							$disabledmac =  "disabled='disable'";
							$stylemac = 'background-color:#E0E0E0;';
							}?>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_macaddress", $camposVisiveis) ?:'hidden' ?>">
								<label for="_macaddress" class="">Mac Address:</label>
								<input name="_1_<?=$_acao?>_tag_macaddress" type="text" id="_macaddress" value="<?=$_1_u_tag_macaddress?>" placeholder="Mac Address" class="form-control" <?= $disabledmac?> style="<?= $stylemac?>"/>
							</div>	
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_certificado", $camposVisiveis) ?:'hidden' ?>">
								<label for="_certificado" class="">Certificado:</label>
								<select name="_1_<?=$_acao?>_tag_certificado" id="_certificado" class="form-control">
									<?fillselect("select 'N','Não' union select 'Y','Sim'",$_1_u_tag_certificado);?>
								</select>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_remoto", $camposVisiveis) ?:'hidden' ?>">
								<label for="_remoto" class="">Remoto:</label>
								<input name="_1_<?=$_acao?>_tag_remoto" type="text" id="_remoto" value="<?=$_1_u_tag_remoto?>" placeholder="Remoto (AnyDesk)" class="form-control">
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_linguagem", $camposVisiveis) ?:'hidden' ?>">
								<label for="_linguagem" class="">Linguagem:</label>
								<select name="_1_<?=$_acao?>_tag_linguagem" id="_linguagem" class="form-control">
									<option value=""></option>
									<?fillselect("select 'ESCPOS','ESC/POS' union select 'ZPL','ZPL' union select 'TSPL','TSPL'",$_1_u_tag_linguagem);?>
								</select>
							</div>
							<? if(in_array("_indpressao", $camposVisiveis)) { ?>
								<div class="col-xs-12 col-sm-6 col-md-3 form-group">
									<label for="_indpressao" class="">Ind. de pressão:</label>
									<select name="_1_<?=$_acao?>_tag_indpressao" id="_indpressao" class="form-control">
										<option value=""></option>
										<?fillselect("select '1','+' union select '2','++' union select '3','+++'",$_1_u_tag_indpressao);?>
									</select>
								</div>
							<? } ?>
							<? if($_1_u_tag_idtagclass == 3) { ?>
								<div class="col-xs-12 col-sm-6 col-md-3 form-group">
									<label for="_cor" class="">Cor:</label>
									<select name="_1_<?=$_acao?>_tag_cor" id="_cor" class="form-control">
										<option value=""></option>
										<?fillselect(TagController::$cores, $_1_u_tag_cor);?>
									</select>
								</div>
							<? } elseif (in_array("_cor", $camposVisiveis)) { ?>
								<div class="col-xs-12 col-sm-6 col-md-3 form-group">
									<label for="_cor" class="">Cor:</label>
									<input type="color" name="_1_<?=$_acao?>_tag_cor" id="_cor" value="<?= $_1_u_tag_cor ?>" class="form-control" />
								</div>
							<? } ?>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_categoria", $camposVisiveis) ?:'hidden' ?>">
								<label for="_categoria" class="">Categoria:</label>
								<input name="_1_<?=$_acao?>_tag_categoria" id="_categoria" type="text" value="<?=$_1_u_tag_categoria?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_combustivel", $camposVisiveis) ?:'hidden' ?>">
								<label for="_combustivel" class="">Combustível:</label>
								<select name="_1_<?=$_acao?>_tag_combustivel" id="_combustivel" class="form-control">
									<option value=""></option>
									<?fillselect(TagController::$combustivel, $_1_u_tag_combustivel);?>
								</select>
							</div> 
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_contrato", $camposVisiveis) ?:'hidden' ?>">
								<label for="_contrato" class="">Contrato:</label>
								<input name="_1_<?=$_acao?>_tag_contrato" id="_contrato" type="text" value="<?=$_1_u_tag_contrato?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_valoraluguel", $camposVisiveis) ?:'hidden' ?>">
								<label for="_valoraluguel" class="">Valor do Aluguel (R$):</label>
								<input name="_1_<?=$_acao?>_tag_valoraluguel" id="_valoraluguel" type="text" value="<?=$_1_u_tag_valoraluguel?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_equipe", $camposVisiveis) ?:'hidden' ?>">
								<label for="_equipe" class="">Equipe:</label>
								<input name="_1_<?=$_acao?>_tag_equipe" id="_equipe" type="text" value="<?=$_1_u_tag_equipe?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_gps", $camposVisiveis) ?:'hidden' ?>">
								<label for="_gps" class="">GPS:</label>
								<input name="_1_<?=$_acao?>_tag_gps" id="_gps" type="text" value="<?=$_1_u_tag_gps?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_seguradora", $camposVisiveis) ?:'hidden' ?>">
								<label for="_seguradora" class="">Seguro:</label>
								<input name="_1_<?=$_acao?>_tag_seguradora" id="_seguradora" type="text" value="<?=$_1_u_tag_seguradora?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_pedagio", $camposVisiveis) ?:'hidden' ?>">
								<label for="_pedagio" class="">Pedágio:</label>
								<input name="_1_<?=$_acao?>_tag_pedagio" id="_pedagio" type="text" value="<?=$_1_u_tag_pedagio?>" class="form-control" />
							</div>
							<div class="col-xs-12 col-sm-6 col-md-3 form-group <?= in_array("_kmtrocaoleo", $camposVisiveis) ?:'hidden' ?>">
								<label for="_kmtrocaoleo" class="">Margem Troca Oléo (KM):</label>
								<input name="_1_<?=$_acao?>_tag_kmtrocaoleo" id="_kmtrocaoleo" type="text" value="<?=$_1_u_tag_kmtrocaoleo?>" class="form-control" />
							</div>
							<?if(!empty($_1_u_tag_idtagtipo)){
								$bioensaio = traduzid("tagtipo","idtagtipo","bioensaio",$_1_u_tag_idtagtipo);
								if ($bioensaio == "Y") {?>
									<div class="col-xs-12 col-sm-6 col-md-3 form-group">
										<label for="_lotacao" class="">Lotação:</label>
										<input name="_1_<?=$_acao?>_tag_lotacao" type="text" id="_lotacao" value="<?=$_1_u_tag_lotacao?>" placeholder="Lotacao" class="form-control" />
									</div>
									<div class="col-xs-12 col-sm-6 col-md-3 form-group">
										<label for="_multiensaio" class="">Multiensaio:</label>
										<select name="_1_<?=$_acao?>_tag_multiensaio" id="_multiensaio" value="<?=$_1_u_tag_multiensaio?>" placeholder="Multiensaio" class="form-control">
											<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_tag_multiensaio);?>
										</select>
									</div>
									<div class="col-xs-12 col-sm-6 col-md-3 form-group">
										<label for="_tempo" class="">Tempo de Alojamento:</label>
										<input name="_1_<?=$_acao?>_tag_tempo" type="text" id="_tempo" value="<?=$_1_u_tag_tempo?>" placeholder="Tempo" class="form-control" />
									</div>
									<div class="col-xs-12 col-sm-6 col-md-3 form-group">
										<label for="_ordem" class="">Ordem:</label>
										<input name="_1_<?=$_acao?>_tag_ordem" type="text" id="_ordem" value="<?=$_1_u_tag_ordem?>" placeholder="Ordem" class="form-control" />
									</div>
								<?}
							}
						}?>
				<?// *************************************** CRIE OS NOVOS CAMPOS AQUI EM CIMA, SEGUINDO O PADRÃO DE ROWS ********************************************//> ?>
					<div class="col-xs-12 form-group">
						<label for="_obs" class="">Obs:</label>
						<textarea name="_1_<?=$_acao?>_tag_obs" id="obs" rows="6" cols="84" class="form-control"><?=$_1_u_tag_obs?></textarea>
					</div>
					<!-- Upload -->
					<div class="d-flex flex-wrap w-100">
						<div class="form-group col-xs-12 col-md-9">
							<label for="tag" class="">Upload:</label>
							<div class="w-100 inputs">
								<div class="cbupload" id="tag" title="Clique ou arraste arquivos para cá" style="width:100%;">
									<i class="fa fa-cloud-upload fonte18"></i>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-3">
							<?
							if ($_1_u_tag_tipoobjetoorigemorigem == 'nfitem' and !empty($_1_u_tag_idobjetoorigem))
							{
								if(count($arquivosNf))
								{ ?>
									<ul class="cab px-0 list-group">
										<h4 class="mt-0">Arquivos Anexos (<?= count($arquivosNf) ?>)</h4>
										<? foreach($arquivosNf as $arquivo) {?>
											<li class="except"><a title="Abrir arquivo" target="_blank"  href="./upload/<?=$arquivo["nome"]?>"><?=$arquivo["nome"]?></a></li>
										<? }?>
									</ul>
								<?}
							}
							?>	
						</div>
					</div>
					<!-- Upload do certificado -->
					<? if(in_array("_uploadcertificado", $camposVisiveis)) { ?>
						<div class="col-xs-12 form-group">
							<label for="certificado">Certificado:</label>
							<div class="cbupload w-100" id="certificado" title="Clique ou arraste arquivos para cá">
								<i class="fa fa-cloud-upload fonte18"></i>
							</div>
						</div>
					<? } ?>
					<!-- 13: Classificacao BLOCO -->
					<? if($idTagClass == 13) { ?>
						<!-- Planta ( imagem ) -->
						<div class="col-xs-12 form-group">
							<label for="planta">Planta:</label>
							<div class="cbupload w-100" id="planta" title="Clique ou arraste arquivos para cá">
								<i class="fa fa-cloud-upload fonte18"></i>
							</div>
						</div>
					<? } ?>
				</div>
			<?} // if(!empty($_1_u_tag_idtag))?>
					
			</div>
		</div>
	</div>
</div>
<?
	if(!empty($_1_u_tag_idtag) and in_array($idTagClass, [4, 15]))
	{
		if(count($tagDim))
		{ ?>
			<div class="row">
				<div class="col-xs-12">
					<div class="panel panel-default" >
						<div class="panel-heading"><?=$_1_u_tag_descricao?> - Locações</div>
						<div  class="panel-body">
							<div class="d-flex flex-wrap mb-3">
								<div class="col-xs-12">
									<span>Colunas</span>
								</div>
								<div class="letras d-flex flex-wrap col-xs-12">
									<? foreach(TagController::$alfabeto as $letra) { ?>
										<a id="link-<?= $letra ?>" href="#<?= $letra ?>" disabled><?= $letra ?></a>
									<?}?>
								</div>
								<!-- Impressao em lote -->
								<div id="showimpressao" class="row w-100">
									<div class="col-xs-12 d-flex flex-wrap justify-content-center">
										<div class="input-group">
											<select class="form-control" id="impressoraetiqueta">
												<?$idmodulo = traduzid('carbonnovo._modulo','modulo','idmodulo',$_GET['_modulo'])?> 
												<?fillselect(TagController::buscarImpressorasTipoZebraDoModulo($idmodulo))?>
											</select>
											<span role="button" onclick="imprimeEtiquetaAndarColuna(<?=$_1_u_tag_idtag?>)" class="btn btn-primary btn-md input-group-addon">Impressão em Lote</span>
										</div>
									</div>
								</div>
								<!-- Impresao por lote -->
								<div id="lista-total-lote" class="row d-flex flex-wrap justify-content-center">
									<div class="col-xs-12 col-md-10 d-flex">
										<h5 class="mr-2">Total de lotes: </h5>
										<h5 id="qtd-lotes"></h5>
									</div>
									<div id="impressao-por-lote" class="col-xs-12 col-md-10 d-flex flex-wrap justify-content-center"></div>
								</div>
								<!-- Transferencia em lote -->
								<div id="transferencia-lotes" class="row w-100 my-3" style="display: none;">
									<div class="w-full d-flex flex-wrap justify-content-center flex-wrap">
										<div class="col-xs-12 col-md-7" style="margin: auto;">
											<div class="row d-flex align-items-end m-0" data-idtagdim>
												<div class="col-xs-5 form-group">
													<label>Transferir para </label>
													<input id="input-prateleiras" class="form-control" type="text" />
												</div>
												<div class="col-xs-2 form-group">
													<label>Coluna </label>
													<select id="transferir-coluna" class="form-control" disabled></select>
												</div>
												<div class="col-xs-2 form-group">
													<label>Linha </label>
													<select id="transferir-linha" class="form-control" disabled></select>
												</div>
												<div class="col-xs-3 form-group">
													<button id="btn-verificar-disponibilidade" class="btn btn-primary" data-idtagdim disabled>
														<span class="mr-2">Transferir</span>
														<i class="fa fa-sign-out text-white"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="d-flex flex-wrap">
								<!-- Colunas -->
								<?foreach($tagDim as $tag)
								{
									if($coluna != $tag["coluna"])
									{
										array_push($letrasVisiveis, TagController::$alfabeto[$tag["coluna"]]);
										$coluna = $tag["coluna"];
										$localizacaoColuna = 'Coluna '.TagController::$alfabeto[$tag["coluna"]];
										?>
										<div id="<?= TagController::$alfabeto[$tag["coluna"]] ?>" class="coluna col-xs-12 col-sm-6 col-md-4 col-lg-2 mb-3">	
											<div class="d-flex justify-content-center align-items-center w-100" title="Coluna" style="background-color:#BEBEBE; text-align: center; font-size: 13px; font-weight: bold;">
												Coluna <?=TagController::$alfabeto[$tag["coluna"]]?>
											</div>	
											<?
											$linhas = TagController::buscarTagDimPorIdTag($_1_u_tag_idtag, $tag["coluna"]);
										
											$linha = '';

											// Linhas
											foreach($linhas as $item)
											{
												if(!in_array($item['idtagdim'], $tagDimLotes)){
													array_push($tagDimLotes, $item['idtagdim']);
												}

												if($linha != $item["linha"])
												{
													$linha = $item["linha"];
													?>
													<div class="d-flex flex-wrap linha py-2">
														<div title="Linha" class="d-flex justify-content-center flex-col align-items-center col-xs-2 relative" style="background-color:#bebebe94; border: 1px solid #ebe3e3; font-weight: bold;">
															<input type="checkbox" class="showimpressao checkbox-lote" id="<?=$tag["coluna"]."_".$item["linha"]?>" data-idtagdim="<?= $item['idtagdim'] ?>" hidden>
															<label title="Imprimir em Lote" for="<?=$tag["coluna"]."_".$item["linha"]?>" class="pointer" onclick="habilitaImpressao(this)">
																<strong><?= str_pad($item["linha"], 2, '0', STR_PAD_LEFT) ?></strong>
															</label>
														</div>
														<div class="caixas d-flex flex-wrap col-xs-10 p-0">
															<?
															$localizacaoLinha = "$localizacaoColuna Linha {$item["linha"]}";
															$caixas = TagController::buscarTagDimPorIdTag($_1_u_tag_idtag, $tag["coluna"], $item["linha"]);

															if(count($caixas) > 1) {
																// Caixas
																foreach($caixas as $caixa)
																{ 
																	array_push($tagDimLotes, $caixa['idtagdim']);
																?>
																	<div id="tagdim-<?= $caixa['idtagdim'] ?>" title="Caixa" class="d-flex align-items-center justify-content-center col-xs-6" style="background-color: #e0bd0863;" data-label="<?= "$localizacaoLinha Caixa {$caixa['caixa']}" ?>" data-idtagdim="<?= $caixa['idtagdim'] ?>" data-linhacoluna="<?=$tag["coluna"]."_".$item["linha"]?>">
																		<?= ($caixa["caixa"] ? str_pad($caixa["caixa"], 2, '0', STR_PAD_LEFT) : 0) ?>
																	</div>
																<? }
															} else { ?>
																<div id="tagdim-<?=$item["idtagdim"]?>" title="Itens" class="d-flex align-items-center justify-content-center col-xs-12 tagdim-<?= $item["linha"] ?>-<?= $item["coluna"] ?>" style="background-color: #e0bd0863;" data-label="<?= "$localizacaoLinha" ?>" data-idtagdim="<?= $item['idtagdim'] ?>" data-linhacoluna="<?=$tag["coluna"]."_".$item["linha"]?>">
																	<?= ($item["linha"] ? str_pad($item["linha"], 2, '0', STR_PAD_LEFT) : 0) ?>
																</div>
															<? } ?>
														</div>
													</div>
												<? }
											} ?>
										</div>
									<?}
								}?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?}
	}?>
<div class="row">
	<? if($_1_u_tag_idtagclass == 3) { ?>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">HISTÓRICO</div>
				<div class="panel-body">
					<div class="w-100 overflow-x-auto">
						<table class="table table-striped planilha">
							<tr>
								<th>PLACA</th>
								<th>DATA RETIRADA</th>
								<th>DATA RETORNO</th>
								<th>CONDUTOR</th>
							</tr>
							<? 							
							$listarHistorico = TagController::listarHistoricoTagVeiculo($_1_u_tag_idtag);
							foreach($listarHistorico as $historico){
							?>
								<tr>
									<td><?=$historico['placa']?></td>
									<td>
										<?=dmahms($historico['datainicio']);?>
									</td>
									<td>
										<?=($historico['datafim'] != '0000-00-00 00:00:00') ? dmahms($historico['datafim']) : "-";?>
									</td>
									<td>
										<label class="alert-warning"><?=$historico['nome'];?>
											<a class="fa fa-bars pointer fade" href="?_modulo=funcionario&_acao=u&idpessoa=<?=$historico['idpessoa'];?>" target="_blank"></a>
										</label>
									</td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
			</div>
		</div>
	<? } ?>
		
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">LOCALIZADA EM</div>
			<div class="panel-body">
				<div class="w-100 overflow-x-auto">
					<table class="table table-striped planilha">
						<tr>
							<th>TAG</th>
							<th>BLOCO</th>
							<th>DESCRIÇÃO</th>
							<th>CLASSE</th>
							<th>TIPO</th>
							<th>STATUS</th>
							<th></th>
						</tr>
						<? 
						foreach($arrTagsLocalizadaEm as $key => $localizacao){
						?>
							<tr>
								<td>
									<label class="alert-warning"><?=$localizacao['tag'];?>
										<a class="fa fa-bars pointer fade" href="?_modulo=tag&amp;_acao=u&amp;idtag=<?=$localizacao['idtag'];?>" target="_blank"></a>
									</label>
									
								</td>
								<td>
									<?=$localizacao['bloco'];?>
								</td>
								<td>
									<?=$localizacao['descricao'];?>
								</td>
								<td>
									<?=$localizacao['tagclass'];?>
								</td>
								<td>
									<?=$localizacao['tagtipo'];?>
								</td>
							
								<td>
									<?=$localizacao['status'];?>
								</td>
								<td>
									<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer pull-right" onclick="excluirtag(<?=$localizacao['idtagsala']?>)" 
									alt="<?=dmahms($localizacao['criadoem']);?>" title="<?=dmahms($localizacao['criadoem']);?>"></i>
								</td>
							</tr>
						<? } ?>
					</table>
				</div>
				<? if ($key == 0) { ?>
					<div class="col-xs-12">
						<select name="idtagpai" class="select-picker" onchange="inserirlocal(this);" id="_localizacao" data-live-search="true">
							<option value="">- Adicionar Tag -</option>
						
								<?fillselect(TagController::buscarTagsQuePossuemVinculoComTipoTag($idTag));
							?>			
						</select>
					</div>
				<? } ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
				<div class="panel-heading">POSSUI&nbsp;&nbsp;<i class="fa fa-print cinza pointer" onclick="showModalEtiqueta(1,'true')"></i></div>
				<div class="panel-body">
					<div class="w-100 overflow-x-auto">
						<table class="table table-striped planilha">
							<tr>
								<th>TAG</th>
								<th>DESCRIÇÃO</th>
								<th>CLASSE</th>
								<th>TIPO</th>
								<th>STATUS</th>
								<th></th>
							</tr>
							<? foreach($arrTagsFilhas as $tagFilha)
							{ ?>
								<tr>
									<td>
										<label class="alert-warning"><?=$tagFilha['tag'];?>
											<a class="fa fa-bars pointer fade" href="?_modulo=tag&amp;_acao=u&amp;idtag=<?=$tagFilha['idtag'];?>" target="_blank"></a>
										</label>
										
									</td>
									<td>
										<?=$tagFilha['descricao'];?>
									</td>
									<td>
										<?=$tagFilha['tagclass'];?>
									</td>
									<td>
										<?=$tagFilha['tagtipo'];?>
									</td>
									
									<td>
										<?=$tagFilha['status'];?>
									</td>
									<td>
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer pull-right" onclick="excluirtag(<?=$tagFilha['idtagsala']?>)" 
										alt="<?=dmahms($tagFilha['criadoem']);?>" title="<?=dmahms($tagFilha['criadoem']);?>"></i>
									</td>
								</tr>
								<? } ?>
						</table>
					</div>
					<div class="col-xs-12">	
						<select name="idtagpai" class="select-picker" onchange="inserirlocalcontem(this);" id="_localizacao" data-live-search="true">
							<option value="">- Adicionar Tag -</option>
							<?fillselect(TagController::buscarTagsParaVincularPorIdTag($idTag));?>			
						</select>
					</div>
				</div>
		</div>
	</div>
<?

if($tagReserva && $tagReserva->numRows()) 
{ ?>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading"  data-toggle="collapse" href="#gpOrigemTag">Origem Tag</div>
			<div class="panel-body collapse" id="gpOrigemTag" style="padding-top: 8px !important;">
				<table  class="table table-striped planilha"> 
					<tr>
						<td>Tag</td>
						<td>Descrição</td>
						<td>Status</td>
						<td>Início Locação</td>
						<td>Fim Locação</td>
					</tr>
					<? foreach($tagReserva->data as $tag) { ?>
						<tr>
							<td>
								<?=$tag['sigla']?>-<?=$tag['tag']?>
								<a class="fa fa-bars pointer fade" href="?_modulo=tag&_acao=u&idtag=<?=$tag['idtag']?>"></a>
							</td>
							<td><?=$tag["descricao"]?></td>
							<td><?=$tag["status"]?></td>
							<td><?=dma($tag["inicio"])?></td>
							<td><? if(empty($tag["fim"])) {echo '-'; } else {echo dma($tag["fim"]);}?></td>
						</tr>
					<?}?>
				</table>
			</div>            
		</div>   
	</div>
<?
}
    if(count($arquivosDeUmaLocacao))
    {
		?>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading"  data-toggle="collapse" href="#gpAnexo">Arquivo(s) Anexo(s)</div>
				<div class="panel-body collapse" id="gpAnexo" style="padding-top: 8px !important;">
					<table  class="table table-striped planilha"> 
						<tr>
							<td>Nome</td>
							<td>Criado Em</td>
							<td></td>
						</tr>
						<? foreach($arquivosDeUmaLocacao as $arquivo) 
						{ ?>
							<tr>
								<td><?=$arquivo['nome']?></td>
								<td><?=dma($arquivo["criadoem"])?></td>
								<td><i class="fa fa-paperclip" onclick="janelamodal('upload/<?=$arquivo['caminho']?>')"></i></td>
							</tr>
						<?}?>
					</table>
				</div>            
			</div>   
		</div>
		<?
	}
}
?>
</div>
<div id="voltar-topo" class="hidden">
	<i class="fa fa-chevron-up fa-2x"></i>
</div>
<?
if(!empty($_1_u_tag_idtag)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_tag_idtag; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "tag"; //pegar a tabela do criado/alterado em antigo
$_disableDefaultDropzone = true;
require 'viewCriadoAlterado.php';

require_once(__DIR__."/../form/js/tag_js.php");
?>
