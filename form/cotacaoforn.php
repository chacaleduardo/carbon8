<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/cotacao_controller.php");

require_once(__DIR__."/controllers/fluxo_controller.php");
 

$_token = $_GET["_token"] == '' ? $_GET["token"] : $_GET["_token"];

//verifica se foi enviado o _token de autenticação
if (!empty($_token)) {
	//desencripta o _token
	$str_token = des($_token);
	//verifica se deu certo a desencriptação
	if ($str_token == false) {
		die("CB-ERROR: Falha #2 ao autenticar _token");
	} else {
		/*
	* Passa a string da variavel $str_token para o array $arr_token
	*/
		parse_str($str_token, $arr_token);
		//print_r($arr_token);
		/*
	* while list
	* o array contem chave mais valor 
	* O comando abaixo irá preencher os GETS com a chave = valor
	*/
		while (list($chave, $valor) = each($arr_token)) 
		{
			$_GET[$chave] = $valor;
		}
	}
}

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parametros GET que devem ser validados para compor o select principal
 *                pk: indica parametro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nf";
$pagvalcampos = array(
	"idnf" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// GVT - 15/06/2021 -  Caso seja um fornecedor e a cotação ainda não foi visualizada por ele, atualiza o registro.
if ($_SESSION["SESSAO"]["USUARIO"] == "token_cotacao" and empty($_1_u_nf_visualizadoem)) 
{
	CotacaoController::atualizarDataVisualizacaoFornecedor($_1_u_nf_idnf);
}
?>
<link href="../form/css/cotacao_css.css?_<?=date("dmYhms")?>" rel="stylesheet">

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<? 
					$rowinf = CotacaoController::buscarDadosFornecedorNf($_1_u_nf_idnf); 
					$idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'RESPONDIDO');
					?>
					<tr>
						<td align="right">ORÇAMENTO:</td>
						<td>
							<label class="idbox">
								<?=$_1_u_nf_idobjetosolipor?>
							</label>
						</td>
						<td>COTAÇÃO:</td>
						<td>
							<label class="idbox"><?=$_1_u_nf_idnf ?></label>
							<input name="_1_<?=$_acao ?>_nf_idnf" type="hidden" id="idnf" value="<?=$_1_u_nf_idnf ?>" readonly='readonly'>
							<input name="_1_<?=$_acao ?>_nf_status" type="hidden" id="idnf" value="RESPONDIDO" readonly='readonly'>
							<input name="_1_<?=$_acao ?>_nf_idfluxostatus" type="hidden" id="idfluxostatus" value="<?=$idfluxostatus ?>" readonly='readonly'>
							<input name="_1_<?=$_acao ?>_nf_envioemailorc" type="hidden" id="idnf" value="N" readonly='readonly'>
						</td>
						<? 
						if ($_1_u_nf_status == "ENVIADO" or $_1_u_nf_status == "RESPONDIDO") 
						{
							$numo = count(CotacaoController::buscarQuantidadeAuditoriaPorObjetoColunaStatus($_1_u_nf_idnf));
							if ($numo == 0) 
							{ ?>
								<td style="width:100%" align="right">
									<button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="CB.post()" title="Enviar">
										<i class="fa fa-circle"></i>Enviar Cotação
									</button>
								</td>
							<? } else { ?>
								<td align="right" style="width:100%">Status do Orçamento:</td>
								<td style="color:red;">
									<b><?=$_1_u_nf_status ?> </b>
								</td>
							<? } ?>
						<? } else { ?>
							<td align="right" style="width:100%">Status do Orçamento:</td>
							<td style="color:red;">
								<b><?=$_1_u_nf_status ?> </b>
							</td>
						<? } ?>
					</tr>
					<tr>
						<td align="right">PRAZO:</td>
						<td colspan="3"><label class="idbox"><?=dma($rowinf['prazo']) ?></label></td>
					</tr>
					<tr>
						<? if (!empty($rowinf['nomecurto'])) { ?>
							<td align="right">RESPONSÁVEL:</td>
							<td colspan="3">
								<label class="idbox"><?=$rowinf['nomecurto'] ?></label>
							</td>
						<? } ?>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<table>
					<tr>
						<td style="width:100%">
							<b>FORNECEDOR:</b>
							<br />
							<input name="_1_<?=$_acao ?>_nf_idpessoa" type="hidden" value="<?=$_1_u_nf_idpessoa ?>" readonly='readonly'>
							<?=traduzid("pessoa", "idpessoa", "razaosocial", $_1_u_nf_idpessoa) ?>
							<br />
							<? $rowfornecedor = CotacaoController::buscarEnderecoPessoa($_1_u_nf_idpessoa); ?>
							CNPJ: <?=formatarCPF_CNPJ($rowfornecedor['cpfcnpj'], true); ?> | I.E: <?=$rowfornecedor['inscrest'] ?><br />
							<? echo $rowfornecedor['logradouro'] . " " . $rowfornecedor['endereco'] . ", " . $rowfornecedor['numero'] . " - " . $rowfornecedor['bairro'] ?>
							<? if (!empty($rowfornecedor['complemento'])) {
								echo " - " . $rowfornecedor['complemento'];
							} ?>
							<br />
							CEP: <? echo $rowfornecedor['cep'] . " - " . $rowfornecedor['cidade'] . "/" . $rowfornecedor['uf'] . " - (" . $rowfornecedor['dddfixo'] . ") " . $rowfornecedor['telfixo'] ?> <br />
						</td>

					</tr>
				</table>
				<hr style="border:1px solid;color: #e6e6e6">
				<? $rowempresa = CotacaoController::buscarEmpresaPorIdEmpresa($_1_u_nf_idempresa); ?>
				<table class="table padding0">
					<tr>
						<td colspan="2"><b>SOLICITANTE</b> (Dados para faturamento, cobrança e entrega)</td>
					</tr>
				</table>
				<table class="table padding0">
					<tr>
						<td><?=nl2br($rowempresa["infosolicitante"]) ?></td>
						<td>
							<br>
							<font color="red">OBS:</font><br>
							<?=nl2br($rowempresa["rodapecotacao"]) ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<table>
					<tr>
						<td align="right" class="nowrap">Nº Orçamento:</td>
						<td><input class="size10" name="_1_<?=$_acao ?>_nf_pedidoext" type="text" value="<?=$_1_u_nf_pedidoext ?>" vnulo onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'pedidoext')"></td>
						<td align="right">Vendedor(a):</td>
						<td><input class="size20" name="_1_<?=$_acao ?>_nf_aoscuidados" type="text" value="<?=$_1_u_nf_aoscuidados ?>" vnulo onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'aoscuidados')"></td>
						<td align="right">Telefone:</td>
						<td><input class="size10" name="_1_<?=$_acao ?>_nf_telefone" type="text" value="<?=$_1_u_nf_telefone ?>" vnulo onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'telefone')"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Itens <? if (!empty($_1_u_nf_idfinalidadeprodserv)) { ?> (Finalidade da Compra: <b> <? $finalidade = traduzid('finalidadeprodserv', 'idfinalidadeprodserv', 'finalidadeprodserv', $_1_u_nf_idfinalidadeprodserv);
																															echo trim($finalidade); ?></b>)<? } ?></div>
			<div class="panel-body">
				<table class="table table-striped planilha">
					<tr>
						<th>QTD</th>
						<th>Un</th>
						<th>Descrição</th>
						<th>Anexos</th>
						<th>Moeda*</th>
						<th title="Valor Unitário">Valor Un</th>
						<th title="Alíquota de ICMS">ICMS %</th>
						<th title="Valor ICMS ST">ICMS ST</th>
						<th title="Alíquota de IPI">IPI %</th>
						<th title="Total do Item">Total</th>
						<th>Validade Produto</th>
						<th>Previsão de Entrega</th>
						<th>Observação</th>
					</tr>
					<? 
					$itensNf = CotacaoController::buscarItensNfPorIdNf($_1_u_nf_idnf);

					$j = 1;
					foreach ($itensNf AS $_itensNf) 
					{
						$idprodserv = $_itensNf['idprodserv'];
						$j = $j + 1;
						if (!empty($first) && !empty($_itensNf['idprodserv'])) 
						{ ?>
							<tr>
								<td colspan="20">
									<hr>
								</td>
							</tr>
						<? }
						$first = 1;
						if ($_itensNf['moeda'] == "BRL" && empty($_itensNf['moedaext'])) {
							$total = $total + $_itensNf["total"] + $_itensNf["valipi"];
						} else {
							$total = $total + $_itensNf["totalext"];
						} ?>
						<tr class="trnormal" alertaimg="<?=$_itensNf['idprodserv'] ?>">
							<td align="left">
								<?=$_itensNf["qtdsol"] ?>
							</td>
							<td align="left">
								<? if (empty($_itensNf["unforn"])) {
									if (empty($_itensNf["unidade"])) {
										echo ($_itensNf["unidadeci"]);
									} else {
										echo ($_itensNf["unidade"]);
									}
								} else {
									echo ($_itensNf["unforn"]);
								} ?>
							</td>

							<td align="left">
								<? 
								if (!empty($_itensNf['idprodserv'])) 
								{
									if (empty($_itensNf["codforn"])) {
										echo ($_itensNf["descr"] . " - " . $_itensNf["codprodserv"]);
									} else {
										echo ($_itensNf["codforn"]);
									}
								} else {
									echo ($_itensNf["prodservdescr"]);
								} ?>
							</td>
							<td>
								<? 
								if (!empty($idprodserv)) 
								{
									$anexosCotacao = CotacaoController::buscarAnexosPorTipoObjetoIdObjeto('arqCotacao', $idprodserv);
									if (count($anexosCotacao) > 0) { ?>
										<i idprodserv="<?=$_itensNf['idprodserv'] ?>" style="font-size: 1.5em;" class="fa fa-paperclip cinza hoverazul popoverprodserv"></i>
										<div class="hidden prodserv_<?=$idprodserv ?>">
											<table>
												<? foreach($anexosCotacao as $rowarq) { ?>
													<tr>
														<td>
															<a href="<?="upload/anexo_orcamento/" . $rowarq['nome'] ?>" target="_blank"><?=$rowarq['nomeoriginal'] ?></a>
														</td>
													</tr>
												<? } ?>
											</table>
										</div>
									<? } ?>
								<? } ?>
							</td>
							<td class="nowrap">
								<? if (empty($_itensNf['moedaext'])) {
									$moedaext = 'BRL';
								?>
									<select <?=$vdisabled ?> class="size5" name="_<?=$j?>_u_nfitem_moeda" onchange="salvacampor(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'moeda')">
										<? fillselect("select 'BRL' ,'BRL' union select 'USD','USD' union select 'EUR','EUR'", $_itensNf["moeda"]); ?>
									</select>
								<? } else {
									echo ($_itensNf['moedaext']);
									$moedaext = $_itensNf['moedaext'];
								} ?>
							</td>
							<td class="tdbr" align="center">
								<input name="_<?=$j?>_u_nfitem_idnfitem" type="hidden" value="<?=$_itensNf["idnfitem"]; ?>" readonly='readonly'>
								<? if ($_itensNf['moeda'] == "BRL" and empty($_itensNf['moedaext'])) { ?>
									<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_vlritem" class="size7" type="text" value="<?=$_itensNf["vlritem"]; ?>" vnulo vdecimal onchange="salvacampor(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'vlritem')">
								<? } else { ?>
									<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_moedaext" type="hidden" value="<?=$_itensNf['moeda'] ?>">
									<input <?=$vreadonly?> class="size7" name="_<?=$j?>_u_nfitem_vlritemext" type="text" value="<?=$_itensNf['vlritemext'] ?>" vnulo vdecimal onchange="salvacampoext(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'vlritemext','<?=$_itensNf["moeda"] ?>');">
								<? } ?>
							</td>
							<td>
								<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_aliqicms" type="text" class="size4" value="<?=$_itensNf['aliqicms'] ?>" onchange="salvacampor(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'aliqicms')">
							</td>
							<td>
								<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_vst" type="text" class="size5" value="<?=$_itensNf['vst'] ?>" onchange="salvacampor(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'vst')">
							</td>
							<td>
								<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_aliqipi" type="text" class="size4" value="<?=$_itensNf['aliqipi'] ?>" onchange="salvacampor(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'aliqipi')">
							</td>
							<td style="width:70px;" align="right">
								<? if ($_itensNf['moeda'] == "BRL" and empty($_itensNf['moedaext'])) { ?>
									<input readonly='readonly' name="_<?=$j?>_u_nfitem_total" size="8" type="hidden" value="<?=$_itensNf["total"]; ?>" vdecimal>
									<?=number_format(tratanumero($_itensNf["total"]), 2, ',', '.'); ?>
								<? } else { ?>
									<input readonly='readonly' name="_<?=$j?>_u_nfitem_totalext" size="8" type="hidden" value="<?=$_itensNf["totalext"]; ?>" vdecimal>
									<?=$_itensNf["totalext"]; ?>
								<? } ?>
							</td>
							<td nowrap>
								<? if ($_itensNf['validadeforn'] == 'Y') {
									$vunlo = 'vnulo';
								} ?>
								<input <?=$vreadonly?> <?=$vunlo?> name="_<?=$j?>_u_nfitem_validade" id="validade" class="calendario size7" type="text" value="<?=$_itensNf['dmavalidade'] ?>">
							</td>
							<td class="tdbr" align="left">
								<? //Campo será obrigatório - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327836 - LTM (22-06-2020)
								?>
								<input <?=$vreadonly?> name="_<?=$j?>_u_nfitem_previsaoentrega" onkeydown="return false" id="previsaoentrega" _idnfitem="<?=$_itensNf["idnfitem"] ?>" _idnf="<?=$_1_u_nf_idnf ?>" class="calendario size7" type="text" value="<?=dma($_itensNf['previsaoentrega']) ?>" vnulo>
							</td>
							<td class="tdbr" align="left">
								<textarea class="caixa observacaocotacaoforn" <?=$vreadonly?> name="_<?=$j?>_u_nfitem_obs" onchange="salvacampo(this,'nfitem',<?=$_itensNf["idnfitem"] ?>,'obs')"><?=$_itensNf["obs"]; ?></textarea>
							</td>
						</tr>
					<? $j = $j + 1;
						$idprodserv = null;
					} ?>
					<tr>
						<td colspan="9" align="right">Sub-Total:<?=$moedaext ?></td>
						<td class="header" align="right"><?=number_format(tratanumero($total), 2, ',', '.'); ?></td>
						<td colspan="3"></td>
					</tr>

					<? $vtotal =  $total + tratanumero($_1_u_nf_frete);
					if (!empty($vtotal)) { ?>
						<tr>
							<td colspan="9" align="right">Total:<b><?=$moedaext ?> </b></td>
							<td align="right"><b><?=number_format(tratanumero($vtotal), 2, ',', '.'); ?></b></td>
							<td colspan="3"><input name="_1_<?=$_acao ?>_nf_total" type="hidden" value="<?=$vtotal ?>" vdecimal></td>
						</tr>
					<? } ?>
				</table>
				<br>
				<table>
					<tr>
						<th>*Moeda</th>
					</tr>
					<tr>
						<td>
							<b>BRL:</b> Real Brasileiro / <b>USD:</b> Dólar dos Estados Unidos / <b>EUR:</b> Zona Euro
						</td>
					</tr>
				</table>
				<br>
				<table style="width: 50%;">
					<tr>
						<th>Anexe sua proposta aqui:</th>
					</tr>
					<tr>
						<td>
							<div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
								<i class="fa fa-cloud-upload fonte18"></i>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Transporte</div>
			<div class="panel-body">
				<table>
					<tr>
						<td align="right">
							Transportadora:
						</td>
						<td>
							<select <?=${"disabled" . $i} ?> <?=$vdisabled ?> name="_1_<?=$_acao ?>_nf_idtransportadora" onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'idtransportadora')">
								<option value=""></option>
								<? fillselect("select idpessoa,nome from pessoa where idtipopessoa = 11  and status = 'ATIVO' order by nome", $_1_u_nf_idtransportadora); ?>
							</select>
						</td>
						<td title="<?=CotacaoController::$tituloFrete?>">Frete:</td>
						<td title="<?=CotacaoController::$tituloFrete?>">
							<select <?=${"disabled" . $i} ?> <?=$vdisabled ?> name="_1_<?=$_acao ?>_nf_modfrete">
								<? fillselect(CotacaoController::$tipoFrete, $_1_u_nf_modfrete); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Obs:</td>
						<td>
							<textarea class="caixa" <?=$vreadonly?> name="_1_<?=$_acao ?>_nf_obs" style="height: 30px;" onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'obs')"><?=$_1_u_nf_obs ?></textarea>
						</td>
						<td align="right">Frete(R$):</td>
						<td>
							<input <?=${"readonly" . $i} ?> <?=$vreadonly?> name="_1_<?=$_acao ?>_nf_frete" size="8" type="text" value="<?=$_1_u_nf_frete ?>" onchange="atualizafrete(this,<?=$_1_u_nf_idnf ?>)" vdecimal>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Pagamento</div>
			<div class="panel-body">
				<table>
					<tr>
						<td align="right">Pagamento:</td>
						<td>
							<select <?=$vdisabled ?> name="_1_<?=$_acao ?>_nf_formapgto" <?=${"disabled" . $i} ?> onchange="salvacampo(this,'nf',<?=$_1_u_nf_idnf ?>,'formapgto')" vnulo>
								<option></option>
								<? fillselect("select distinct(formapagamento) as id,formapagamento from formapagamento where status='ATIVO' " . getidempresa('idempresa', 'formapagamento') . " order by  formapagamento", $_1_u_nf_formapgto); ?>
							</select>
						</td>
						<td align="right" class="nowrap">
							1º Vencimento:
						</td>
						<td>
							<input <?=$vreadonly?> name="_1_<?=$_acao ?>_nf_diasentrada" class="diasentrada" size="2" type="text" value="<?=$_1_u_nf_diasentrada ?>" vdecimal onchange="atualizaparc(<?=$_1_u_nf_idnf ?>)" vnulo>
						</td>
						<td>
							Dias.
						</td>
					</tr>
					<tr>
						<td align="right">
							Parcelas:
						</td>
						<td>
							<select id="parcelas" <?=$vdisabled ?> name="_1_<?=$_acao ?>_nf_parcelas" class="parcelas"  <?=${"disabled" . $i} ?> onchange="atualizaparc(<?=$_1_u_nf_idnf ?>)" vnulo>
								<option value=""></option>
								<?
								for($isel = 1; $isel <= 60; $isel++)
								{
									if($isel == 1){
										$arrayParcelas[$isel] = $isel."x";
									} else {
										$arrayParcelas[$isel] = $isel."x";
									}
								}
								fillselect($arrayParcelas, $_1_u_nf_parcelas);
								?>
							</select>
						</td>
						<? if ($_1_u_nf_parcelas > 1) {
							$strdivtab = "";
						} else {
							$strdivtab = "hide";
						} ?>
						<td align="right">
							<div class="divtab <?=$strdivtab ?>" id="divtab1">
								Intervalo Parcelas:
							</div>
						</td>
						<td class='nowrap'>
							<div class="divtab  nowrap <?=$strdivtab ?>" id="divtab2">
								<input <?=$vreadonly?> name="_1_<?=$_acao ?>_nf_intervalo" class="intervalo" type="text" value="<?=$_1_u_nf_intervalo ?>" vdecimal onchange="atualizaparc(<?=$_1_u_nf_idnf ?>)">
								Dias
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Observações do Solicitante</div>
			<div class="panel-body">
				<table>
					<tr>
						<td>
							<?=nl2br($rowempresa["obssolicitante"]) ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<? if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) { ?>
<? if (!empty($_1_u_nf_idnf)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_nf_idnf; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}
	$tabaud = "nf"; //pegar a tabela do criado/alterado em antigo
	$_disableDefaultDropzone = true; // desabilitar dropzone default
	require 'viewCriadoAlterado.php';
} 

require_once('../form/js/cotacaoforn_js.php');
?>
