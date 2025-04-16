<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "orcamento";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idorcamento" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from orcamento where idorcamento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function jsonProdutos()
{

	$sql = "select idprodserv,descr 
	    from prodserv 
            where status='ATIVO'
	    and tipo='SERVICO'
             ".getidempresa('idempresa', 'prodserv')."order by descr";

	$res = d::b()->query($sql);

	$arrtmp = array();
	$i = 0;
	while ($r = mysqli_fetch_assoc($res)) {
		$arrtmp[$i]["value"] = $r["idprodserv"];
		$arrtmp[$i]["label"] = $r["descr"];
		$i++;
	}
	return $arrtmp;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd = jsonProdutos();
//print_r($arrCli); die;
$jsonProd = $JSON->encode($arrProd);
function getClientesnf()
{

	$sql = "SELECT
                p.idpessoa,
                if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome,
                CASE p.idtipopessoa
                    WHEN 5 THEN 'FORNECEDOR'
                    WHEN 2 THEN 'CLIENTE'					
                END as tipo
        FROM pessoa p			
        WHERE p.status = 'ATIVO'
                AND p.idtipopessoa  in (2,5,7)
                ".getidempresa('p.idempresa', 'pessoa')."
        ORDER BY p.nome";

	$res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

	$arrret = array();
	while ($r = mysqli_fetch_assoc($res)) {
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arrret[$r["idpessoa"]]["nome"] = $r["nome"];
		$arrret[$r["idpessoa"]]["tipo"] = $r["tipo"];
	}
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli = getClientesnf();
//print_r($arrCli); die;
$jCli = $JSON->encode($arrCli);

?>


<style>
	.divverde {
		width: 16px;
		height: 16px;
		background-image: url(../img/accept.png);
		background-repeat: no-repeat;
		cursor: pointer;
		cursor: hand;
		float: left;
	}

	.divvermelho {
		width: 16px;
		height: 16px;
		background-image: url(../img/rejected.png);
		background-repeat: no-repeat;
		cursor: pointer;
		cursor: hand;
		float: left;
	}

	.tdconcluido {
		background-color: #32CD32;
		border: 1px solid #32CD32;
	}

	.tdpendente {
		background-color: #FF4500;
		border: 1px solid #FF4500;
	}

	.tdaguardando {
		background-color: #FFFF00;
		border: 1px solid #FFFF00;
	}
</style>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td align="right">ID:</td>
						<td colspan="2"><input name="_1_<?=$_acao ?>_orcamento_idorcamento" id="idorc" type="hidden" value="<?=$_1_u_orcamento_idorcamento ?>" readonly='readonly'>
							<label class="idbox"><?=$_1_u_orcamento_controle ?></label>
						</td>
						<td align="right">Cliente:</td>
						<td nowrap>
							<input type="text" name="_1_<?=$_acao ?>_orcamento_idpessoa" vnulo cbvalue="<?=$_1_u_orcamento_idpessoa ?>" value="<?=$arrCli[$_1_u_orcamento_idpessoa]["nome"] ?>" style="width: 35em;" vnulo>
						</td>
						<td>

							<?
							if (!empty($_1_u_orcamento_idpessoa)) {
							?>
								<a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_orcamento_idpessoa ?>')"></a>
							<? } ?>
						</td>
						<td align="right">Endereço:</td>
						<td nowrap>
							<?
							if (empty($_1_u_orcamento_idendereco) and empty($_1_u_orcamento_idpessoa)) {
							?>
								<select name="_1_<?=$_acao ?>_orcamento_idendereco" id="idendereco" onchange="CB.post()" vnulo>
									<option value=""></option>
								</select>
							<?
							} elseif (!empty($_1_u_orcamento_idendereco)) {
							?>
								<select name="_1_<?=$_acao ?>_orcamento_idendereco" id="idendereco" onchange="CB.post()" vnulo>

									<? fillselect(
										"select e.idendereco,concat(t.tipoendereco,'-',e.endereco,'-',e.uf) as endereco from endereco e,tipoendereco t where t.idtipoendereco = e.idtipoendereco   and e.status = 'ATIVO' and e.idpessoa =".$_1_u_orcamento_idpessoa.";",
										$_1_u_orcamento_idendereco
									); ?>
								</select>
							<?
							} elseif (empty($_1_u_orcamento_idendereco) and !empty($_1_u_orcamento_idpessoa)) {
							?>
								<select name="_1_<?=$_acao ?>_orcamento_idendereco" id="idendereco" onchange="CB.post()" vnulo>

									<? fillselect(
										"select e.idendereco,concat(t.tipoendereco,'-',e.endereco,'-',e.uf) as endereco from endereco e,tipoendereco t where t.idtipoendereco = e.idtipoendereco  and e.status = 'ATIVO' and e.idpessoa =".$_1_u_orcamento_idpessoa.";",
										$_1_u_orcamento_idendereco
									); ?>
								</select>
							<?
							}
							?>
						</td>
						<td>
							<? if ($_1_u_orcamento_idendereco) { ?>
								<a class="fa fa-bars pointer hoverazul" title="Cadastro de  Endereço" onclick="janelamodal('?_modulo=endereco&_acao=u&idendereco=<?=$_1_u_orcamento_idendereco ?>')"></a>
							<? }
							if ($_1_u_orcamento_idpessoa) { ?>
								<a class="fa fa-plus-circle verde pointer hoverazul" title="Cadastro de  Endereço" onclick="janelamodal('?_modulo=endereco&_acao=i&idpessoa=<?=$_1_u_orcamento_idpessoa ?>')"></a>
							<? } ?>
						</td>
						<td align="right">Status:</td>
						<td>
							<select name="_1_<?=$_acao ?>_orcamento_status">
								<? fillselect("SELECT 'ABERTO','Aberto' union select 'ENVIADO','Enviado' 
                                    union select 'APROVADO','Aprovado' union select 'REPROVADO','Reprovado'", $_1_u_orcamento_status); ?>
							</select>
						</td>
						<td>
							<? if (!empty($_1_u_orcamento_idorcamento)) {								
								?>
								<a title="Imprimir Orçamento." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/orcamento.php?_acao=u&idorcamento=<?=$_1_u_orcamento_idorcamento ?>&_idempresa=<?=$_1_u_orcamento_idempresa ?><?=$linkLog?>')"></a>
							<? } ?>
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<table>
					<tr>
						<td align="right">A/C:</td>
						<td colspan="2">
							<div class="ui-widget">
								<input id="aoscuid" name="_1_<?=$_acao ?>_orcamento_resp" size="50" value="<?=$_1_u_orcamento_resp ?>">
							</div>
							<input id="idresp" name="_1_<?=$_acao ?>_orcamento_idresp" type="hidden" value="<?=$_1_u_orcamento_idresp ?>">
						</td>
						<?
						if (!empty($_1_u_orcamento_idpessoa)) {
							$cnpj = traduzid("pessoa", "idpessoa", "cpfcnpj", $_1_u_orcamento_idpessoa);
							$cnpj = formatarCPF_CNPJ($cnpj, true);
							if (!empty($cnpj)) {
								?>
								<td align="right">CNPJ:</td>
								<td colspan="2">
									<font size="2" style="color: red;"><b><?=$cnpj ?></b></font>
								</td>
								<?
							}
						}
						?>
						<td align="right">Telefone:</td>
						<td><input name="_1_<?=$_acao ?>_orcamento_telefone" type="text" size="12" value="<?=$_1_u_orcamento_telefone ?>"></td>
						<td align="right">Data:</td>
						<? if (empty($_1_u_orcamento_dataorc)) {
							$_1_u_orcamento_dataorc = date("d/m/Y");
						}
						?>
						<td><input name="_1_<?=$_acao ?>_orcamento_dataorc" id="data" size="8" type="text" value="<?=$_1_u_orcamento_dataorc ?>" vdata></td>

					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?
if (!empty($_1_u_orcamento_idorcamento)) {
	$sql = "SELECT 
			i.idorcamentoitem,i.idorcamento,i.idtipoteste,i.qtd,
			REPLACE(i.valor, '.', ',') as valor,
			REPLACE(i.desconto, '.', ',') as desconto,
			REPLACE(i.valtotal, '.', ',') as valtotal,
			p.idportaria,
			concat(t.tipoteste,' ',ifnull(p.codigo,' ')) as tipoteste,
			t.sigla,
			REPLACE(i.valorun, '.', ',') as valorun,
			i.obs
			FROM orcamentoitem i,vwtipoteste t left join portaria p on(t.idportaria=p.idportaria)
	        where t.idtipoteste = i.idtipoteste
	        and i.idorcamento = ".$_1_u_orcamento_idorcamento." order by t.tipoteste";

	$qr = d::b()->query($sql) or die("Erro ao buscar itens do orçamento:".mysqli_error(d::b()));
	$qtdrows = mysqli_num_rows($qr);
	//if($qtdrows>0){
?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Itens do Orçamento</div>
				<div class="panel-body">
					<table id="tbItens" class="table planilha">

						<thead>
							<tr>
								<th class="text-center">Qtd</th>
								<th class="text-left">Descrição</th>
								<th class="text-left">Sigla</th>
								<th class="text-left">Obs.</th>
								<th class="text-center">Vlr. Unit.</th>
								<th class="text-center">Vlr. Total</th>
								<th class="text-center">Desc %</th>
								<th class="text-center">Vlr. Final</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1;
							$vtotaliten = 0.00;
							$virg = "";
							while ($row = mysqli_fetch_array($qr)) {
								if (!empty($row['idportaria'])) {
									$legportaria = "S";
									$inidportaria = $inidportaria.$virg.$row['idportaria'];
									$virg = ",";
								}
								$i = $i + 1;
								$vtotaliten = $vtotaliten + $row["valtotal"];
							?>

								<tr class="dragExcluir" idorcamentoitem="<?=$row["idorcamentoitem"] ?>">
									<td class="text-center"><?=$row["qtd"] ?></td>
									<td class="text-left"><?=$row["tipoteste"] ?></td>
									<td class="text-left"><?=$row["sigla"] ?></td>
									<td class="text-left">
										<input name="_<?=$i ?>_u_orcamentoitem_idorcamentoitem" type="hidden" value="<?=$row["idorcamentoitem"]; ?>" readonly='readonly'>
										<input name="_<?=$i ?>_u_orcamentoitem_obs" type="text" value="<?=$row["obs"]; ?>" <?=$disabledcancel ?>>
									</td>
									<td class="text-center">R$<?=$row["valorun"] ?></td>
									<td class="text-center">R$<span id="<?=$i ?>_valor"><?=$row["valor"] ?></span></td>
									<td class="text-center">
										<input class="desconto" name="_<?=$i ?>_u_orcamentoitem_desconto" type="text" value="<?=$row["desconto"]; ?>" <?=$disabledcancel ?> onchange="atualizaTotalItem(<?=$i ?>)" autocomplete="off">
									</td>
									<td class="text-center">
										R$<span id="<?=$i ?>_valtotal"><?=$row["valtotal"]; ?></span>
										<input class="valores" name="_<?=$i ?>_u_orcamentoitem_valtotal" type="hidden" value="<?=$row["valtotal"]; ?>">
									</td>

									<?
									if ($_1_u_orcamento_status == 'ABERTO' or $_1_u_orcamento_status == 'ENVIADO') {
									?>
										<td class="text-center">
											<i class="fa fa-arrows cinzaclaro hover move" title="Excluir Item"></i>
										</td>
									<?
									}
									?>
								</tr>
							<?php
							}

							$fvtotaliten = round($vtotaliten, 2);
							?>
						</tbody>
						<tfoot>
							<tr id="total" class="dragLock">
								<td class="text-right" colspan="6"></td>
								<td class="text-center">
									<h4><b>Total</b></h4>
								</td>
								<td class="text-center">
									<h4><b>R$<span id="fvtotaliten"><?=number_format($fvtotaliten, 2, ',', ''); ?></span></b></h4>
								</td>
								<td class="text-center">
									<? if ($_1_u_orcamento_vertotalitem == "Y") { ?>
										<div class="divverde" id="vertotalitem" onclick="selecionaitem('vertotalitem','orcamento');"></div>
									<? } else { ?>
										<div class="divvermelho" id="vertotalitem" onclick="selecionaitem('vertotalitem','orcamento');"></div>
									<? } ?>
								</td>
							</tr>
						</tfoot>
					</table>

					<style>
						input.desconto {
							width: 60px !important;
							text-align: center;
						}

						.dragLock {
							pointer-events: none;
							background-color: white;
							border-top: 1px solid #d8d8d8;
						}

						/**reset carbon */
						.table>tbody>tr>td,
						.table>tbody>tr>th,
						.table>tfoot>tr>td,
						.table>tfoot>tr>th,
						.table>thead>tr>td,
						.table>thead>tr>th {
							border-top: none;
							padding-bottom: 4px;
							padding-top: 4px;
							text-indent: 8px;
						}
					</style>
					<script type="text/javascript">
						//@513177 - CAMPO DE COLOCAR O DESCONTO NO ORÇAMENTO NÃO ESTA FUNCIONANDO
						function floatToText(num)
						{
							return num.replace(".", ",").replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
						}
						function textToFloat(num)
						{
							return parseFloat(num.replace(",", ".").replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")).toFixed(2);
						}
						function atualizaTotalFinal() {
							let total = 0;
							$('input.valores').each(function(key, el) {
								total += parseFloat(el.value);
							});
							$('#fvtotaliten').text(floatToText(Number(parseFloat(total)).toFixed(2)));
						}

						function atualizaTotalItem(id) {
							let valor = textToFloat($('#' + id + '_valor').text())
							valor = parseFloat(valor);
							let desconto = parseFloat(textToFloat($('input[name=_' + id + '_u_orcamentoitem_desconto]').val()));
							if (!isNaN(desconto)) {
								desconto = Number((valor - ((valor / 100) * desconto))).toFixed(2);
								$('input[name=_' + id + '_u_orcamentoitem_valtotal]').val(floatToText(desconto));
								$('#' + id + '_valtotal').html(floatToText(desconto));
								atualizaTotalFinal();
							} else {
								alert("Informe um valor válido");
							}
						}
					</script>
					<table class="hidden" id="modeloNovoIten">
						<tr class='dragExcluir'>
							<td>
								<input type="hidden" name="#nameidorcamentoitem">
								<input type="hidden" name="#nameidorcamento" value="<?=$_1_u_orcamento_idorcamento ?>">
								<input style=" border: 1px solid silver;" name="#namequantidade" title="Qtd" placeholder="Qtd" type="text" size="2">
							</td>

							<td colspan="7">
								<input type="text" name="#nameidprodserv" class="idprodserv" cbvalue placeholder="Informe o produto">
							</td>
							<td><i class="fa fa-arrows cinzaclaro hover move"></i></td>
						</tr>
					</table>

					<div class="row ">
						<? if (!empty($_1_u_orcamento_idorcamento) and ($_1_u_orcamento_status == 'ABERTO' or $_1_u_orcamento_status == 'ENVIADO')) { ?>
							<div class="col-sm-6">
								<i id="novoitem" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoItem()" title="Inserir novo Item"></i>
							</div>
							<div class="col-sm-6 text-right">
								<i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer" id="excluirItem" title="Arraste o Item até aqui para excluir"></i>
							</div>
						<? } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">Adicionais do Orçamento</div>
				<div class="panel-body">
					<table class="table planilha">
						<thead>
							<tr>
								<th class="header" class="text-center">
									<span>Descrição</span>
								</th>
								<th class="header" class="text-center">
									<span>Valor</span>
								</th>
								<th class="text-center"></th>
							</tr>
						</thead>
						<tbody>
							<?
							$sql1 = "SELECT *
									FROM orcamentoadic 
									where idorcamento =".$_1_u_orcamento_idorcamento;
							$res1 = d::b()->query($sql1) or die("A Consulta  dos materiais solicitados falhou : ".mysqli_error(d::b())."<p>SQL: $sql1");
							$qtdrows1 = mysqli_num_rows($res1);
							if ($qtdrows1 > 0) {

								$vtotaadic = 0.00;
								while ($row1 = mysqli_fetch_array($res1)) {
									$vtotaadic = $vtotaadic + $row1["valor"];
									$i = $i + 1;
							?>
									<tr class="respreto" style="background-color: #FFFFFF">
										<td class="respreto text-left"><?=$row1["descr"]; ?></td>
										<td class="respreto" class="text-center">R$<?=number_format($row1["valor"], 2, ',', '');?></td>
										<td class="text-center">
											<a class="ui-droppable" onclick="excluiritenadic(<?=$row1['idorcamentoadic']; ?>)" alt="Excluir ITEM!"><i class="fa fa-trash fa-2x cinzaclaro hoververmelho pointer"></i></a>
										</td>
									</tr>
							<?
								}
								$fvtotaladic = $vtotaadic;
							}
							?>
							<tr class="respreto">
								<td class="text-center">
									<input name="orcamentoadicdescr" id="orcamentoadicdescr" size="40" type="text" value="" placeholder="Digite uma descrição">
								</td>
								<td style="display: inline-flex;align-items: center;">
									<span>R$</span><input name="orcamentoadicvalor" id="orcamentoadicvalor" type="text" value="" placeholder="0,00">
								</td>
								<td class="text-center">
									<a class="" onclick="novoItemdescr()" title="Inserir novo Item"><i class="fa fa-plus-circle fa-2x verde pointer"></i></a>
								</td>
							</tr>
							<? if ($fvtotaladic > 0) { ?>
								<tr class="dragLock">
									<td class="text-right">
										<h4><b>Total adicionais</b></h4>
									</td>
									<td class="text-left">
										<h4><b>R$<?=number_format($fvtotaladic, 2, ',', ''); ?></b></h4>
									</td>
									<td></td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?
}
?>
<?
if (!empty($_1_u_orcamento_idorcamento)) {
?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Rodapé</div>
				<div class="panel-body">

					<?
					if (!empty($_1_u_orcamento_idorcamento)) {
					?>
						<table>
							<?
							$_1_u_orcamento_total = $fvtotaladic + $fvtotaliten;
							?>
							<tr>
								<td>Total do orçamento:</td>
								<td>
									<div style="float:left;"><?=number_format($_1_u_orcamento_total, 2, ',', ''); ?>&nbsp;&nbsp;</div>
									<input name="_1_<?=$_acao ?>_orcamento_total" type="hidden" value="<?=$_1_u_orcamento_total ?>" vnulo>
									<? if ($_1_u_orcamento_vertotalorc == "Y") { ?>
										<div class="divverde" id="vertotalorc" onclick="selecionaitem('vertotalorc','orcamento');"></div>
									<? } else { ?>
										<div class="divvermelho" id="vertotalorc" onclick="selecionaitem('vertotalorc','orcamento');"></div>
									<? } ?>
								</td>
							</tr>
							<tr>
								<td>Proposta válida por:</td>
								<? if (empty($_1_u_orcamento_validade)) {
									$_1_u_orcamento_validade = 30;
								} ?>
								<td class="nowrap">
									<input name="_1_<?=$_acao ?>_orcamento_validade" size="1" type="text" value="<?=$_1_u_orcamento_validade ?>" vnulo> dias.
								</td>
							</tr>
							<tr>
								<td>Prazo de pagamento:</td>
								<? if (empty($_1_u_orcamento_diasentrada)) {
									$_1_u_orcamento_diasentrada = 28;
								} ?>
								<td class="nowrap"><input name="_1_<?=$_acao ?>_orcamento_diasentrada" size="1" type="text" value="<?=$_1_u_orcamento_diasentrada ?>"> dias.</td>
							</tr>
							<tr>
								<td>Forma de pagamento:</td>
								<td>
									<select name="_1_<?=$_acao ?>_orcamento_formapgto">
										<? fillselect("select 'BOLETO','Boleto' union select 'DEPOSITO','Depósito'", $_1_u_orcamento_formapgto); ?>
									</select>
								</td>
							</tr>
						</table>
					<? } ?>
					<table>
						<?
						if (!empty($_1_u_orcamento_idorcamento)) {
						?>
							<tr>
								<td colspan="2">Observação Orçamento:</td>
							</tr>
							<tr>
								<td colspan="2">
									<textarea class="caixa" name="_1_<?=$_acao ?>_orcamento_obs" style="width: 400px; height: 60px;"><?=$_1_u_orcamento_obs ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2">Observação Interna:</td>
							</tr>
							<tr>
								<td colspan="2">
									<textarea class="caixa" name="_1_<?=$_acao ?>_orcamento_obsint" style="width: 400px; height: 60px;"><?=$_1_u_orcamento_obsint ?></textarea>
								</td>
							</tr>
					</table>
				<?
						}
				?>

				<table>
					<tr>
						<td>
							<table>
								<tr>
									<td align="right">Remetente:</td>
									<?
									$sqlempresaemail = "SELECT * FROM empresaemails WHERE tipoenvio = 'ORCSERV' ".getidempresa('idempresa', 'empresa');
									$resempresaemail = d::b()->query($sqlempresaemail) or die("Erro ao buscar empresaemails sql=".$sqlempresaemail);
									$qtdempresaemail = mysqli_num_rows($resempresaemail);
									if ($qtdempresaemail == 1) {
										$nemails = 1;
									} else {
										if ($qtdempresaemail > 1) {
											$nemails = 2;
										} else {
											$nemails = 0;
										}
									}

									$sqlemailobj = "SELECT * FROM empresaemailobjeto WHERE tipoenvio = 'ORCSERV' and tipoobjeto = 'orcamento' and idobjeto =".$_1_u_orcamento_idorcamento." and idempresa =".$_1_u_orcamento_idempresa." order by idempresaemailobjeto desc limit 1";
									$resemailobj = d::b()->query($sqlemailobj) or die("Erro ao buscar empresaemailobjeto sql=".$sqlemailobj);
									$rowemailobj = mysqli_fetch_assoc($resemailobj);
									$qtdemailobj = mysqli_num_rows($resemailobj);

									if ($qtdemailobj < 1) {
										$setemail = 1;
									} else {
										$setemail = 0;
									}
									if ($nemails == 1) { ?>
										<td>
											<?
											$sqldominio = "SELECT em.idemailvirtualconf,em.idempresa,ev.email_original AS dominio
											FROM empresaemails em 
											JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
											WHERE em.tipoenvio = 'ORCSERV'
											AND ev.status = 'ATIVO'
											AND em.idempresa =".$_1_u_orcamento_idempresa;

											$resdominio = d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
											$rowdominio = mysqli_fetch_assoc($resdominio) ?>

											<input id="emailunico" type="hidden" value="<?=$rowdominio["idemailvirtualconf"] ?>">
											<input id="idempresaemail" type="hidden" value="<?=$rowdominio["idempresa"] ?>">
											<label class="alert-warning"><?=$rowdominio["dominio"] ?></label>
										</td>
										<? } else {
										if ($nemails > 1) {
											$sqldominio = "SELECT em.idemailvirtualconf,ev.email_original AS dominio
											FROM empresaemails em
											JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
											WHERE em.tipoenvio = 'ORCSERV'
											AND ev.status = 'ATIVO'
											AND em.idempresa =".$_1_u_orcamento_idempresa;

											$resdominio = d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
											$qtddominio = mysqli_num_rows($resdominio);
											if ($qtddominio > 0) {
												while ($rowdominio = mysqli_fetch_assoc($resdominio)) {
													if ($rowdominio["idemailvirtualconf"] == $rowemailobj["idemailvirtualconf"]) {
														$chk = 'checked';
													} else {
														$chk = '';
													} ?>
													<td>
														<input class="emailorcamento" title="Email Remetente" type="radio" <?=$chk ?> onclick="altremetenteemail(<?=$_1_u_orcamento_idorcamento ?>,<?=$rowdominio["idemailvirtualconf"] ?>,'ORCSERV',<?=$_1_u_orcamento_idempresa ?>)">
														<label class="alert-warning"><?=$rowdominio["dominio"] ?> </label>
													</td>
									<? }
											}
										}
									}
									?>
								</tr>
								<tr>
									<td align="right">Destinatário:</td>
									<td title="Mais de um email separado por vírgula."><input   title="Mais de um email separado por vírgula." name="_1_<?=$_acao ?>_orcamento_email" type="text" size="80" value="<?=$_1_u_orcamento_email ?>"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table>
								<tr>
									<?
									if ($_1_u_orcamento_formamostra == "Y") {
										$checked = 'checked';
										$vchecked = 'N';
									} else {
										$checked = '';
										$vchecked = 'Y';
									}
									?>
									<td align="right">Formulário de Amostra:</td>
									<td><input title="Enviar formulário de remessa de amostras" type="checkbox" <?=$checked ?> name="formremamostras" onclick="altcheck('orcamento','formamostra',<?=$_1_u_orcamento_idorcamento ?>,'<?=$vchecked ?>')"></td>

									<? if ($_1_u_orcamento_envioemail == 'Y' or $_1_u_orcamento_envioemail == 'E' or $_1_u_orcamento_envioemail == 'O') {
										if ($_1_u_orcamento_envioemail == 'Y') {
											$classtdemail = "amarelo";
										} elseif ($_1_u_orcamento_envioemail == 'O') {
											$classtdemail = "verde";
										} elseif ($_1_u_orcamento_envioemail == 'E') {
											$classtdemail = "vermelho";
										}
									?>
										<td align="right" nowrap>Enviar Email:</td>
										<td align="right" nowrap>
											<input id="setemail" type="hidden" value="<?=$setemail ?>">
											<a class="fa fa-envelope pointer <?=$classtdemail ?>" title="Enviar email Orçamento" id="envioemail<?=$_1_u_orcamento_idorcamento ?>" onclick="envioemail(<?=$_1_u_orcamento_idorcamento ?>,'envioemail','orcamento','Alterar o status de Envio de Email?',0);"></a>
										</td>

									<? } else { ?>
										<td class="" align="right" nowrap>Enviar Email:</td>
										<td class="" align="right" nowrap>
											<input id="setemail" type="hidden" value="<?=$setemail ?>">
											<a class="fa fa-envelope pointer cinza" title="Enviar email Orçamento" id="envioemail<?=$_1_u_orcamento_idorcamento ?>" onclick="envioemail(<?=$_1_u_orcamento_idorcamento ?>,'envioemail','orcamento','Enviar email com os dados do Orçamento para o Cliente?',<?=$nemails ?>);"></a>
										</td>

									<? } ?>

									<?
									$sqlemail = "SELECT 
					  m.idmailfila
				   FROM
					  mailfila m
				   WHERE
					  m.tipoobjeto = 'orcamentoserv'
						 AND m.idobjeto = ".$_1_u_orcamento_idorcamento."
						 ".getidempresa('m.idempresa', 'envioemail')."
				   ORDER BY
					  idmailfila DESC LIMIT 1";
									$resemail = d::b()->query($sqlemail) or die("Falha na consulta do email: ".mysqli_error()."<p>SQL: ".$sqlemail);
									$rowemail = mysqli_fetch_assoc($resemail);
									$numemail = mysqli_num_rows($resemail);
									if ($numemail > 0) { ?>
										<td align="right" nowrap>
											<a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?=$rowemail['idmailfila'] ?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
										</td>
									<? } ?>
								</tr>
							</table>
						</td>
					</tr>

				</table>

				</div>
			</div>
		</div>
	</div>
<? } ?>
<?
if (!empty($_1_u_orcamento_idorcamento)) {
	$sql = "select log,status,dmahms(criadoem) as criadoem 
	    from log 
	    where idobjeto =".$_1_u_orcamento_idorcamento." 
	    and tipoobjeto = 'orcamento'";
	$res = d::b()->query($sql) or die("Erro ao consultar log de email sql=".$sql);
	$qtd = mysqli_num_rows($res);
	if ($qtd > 0) {
?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Log de Envio de Email</div>
					<div class="panel-body">
						<table class="normal">
							<tr class="header">
								<td class="header">LOG</td>
								<td class="header">Status</td>
								<td class="header">Data</td>
							</tr>
							<?
							while ($row = mysqli_fetch_assoc($res)) {
							?>
								<tr class="respreto">
									<td class="respreto"><?=$row['log'] ?></td>
									<td class="respreto"><?=$row['status'] ?></td>
									<td class="respreto"><?=$row['criadoem'] ?></td>
								</tr>
							<?
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
<?
	}
}
?>
<?
if (!empty($_1_u_orcamento_idorcamento)) {
	$sql = "select p.idpessoa
                ,p.nome 
                ,CASE
                    WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE
                    WHEN c.status ='ATIVO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
            from carrimbo c ,pessoa p 
            where c.idpessoa = p.idpessoa
            and c.status IN ('ATIVO','PENDENTE')
            and c.tipoobjeto in('orcamento')
            and c.idobjeto =".$_1_u_orcamento_idorcamento."  order by nome";

	$res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql);
	$existe = mysqli_num_rows($res);
	if ($existe > 0) {
?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Assinaturas</div>
					<div class="panel-body">
						<table class="planilha grade compacto">
							<tr>
								<th>Funcionários</th>
								<th>Data Assinatura</th>
								<th>Status</th>
							</tr>
							<?
							while ($row = mysqli_fetch_assoc($res)) {
							?>
								<tr class="res">
									<td nowrap><?=$row["nome"] ?></td>
									<td nowrap><?=$row["dataassinatura"] ?></td>
									<td nowrap><?=$row["status"] ?></td>
								</tr>
							<?
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
<? }
} ?>

<?
if (!empty($_1_u_orcamento_idorcamento)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_orcamento_idorcamento; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "orcamento"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>
<script>
	<?
	if (!empty($_1_u_orcamento_idorcamento)) {
		$sqla = "select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_orcamento_idorcamento." 
	    and tipoobjeto in ('orcamento')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
		$resa = d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
		$qtda = mysqli_num_rows($resa);
		if ($qtda > 0) {
			$rowa = mysqli_fetch_assoc($resa);

	?>
			botaoAssinar(<?=$rowa['idcarrimbo'] ?>);
	<?

		} // if($qtda>0){
	} //if(!empty($_1_u_sgdoc_idsgdoc)){
	?>

	function botaoAssinar(inidcarrimbo) {
		$bteditar = $("#btAssina");
		if ($bteditar.length == 0) {
			CB.novoBotaoUsuario({
				id: "btAssina",
				rotulo: "Assinar",
				class: "verde",
				icone: "fa fa-pencil",
				onclick: function() {
					CB.post({
						objetos: "_x_u_carrimbo_idcarrimbo=" + inidcarrimbo + "&_x_u_carrimbo_status=ATIVO",
						parcial: true,
						posPost: function(data, textStatus, jqXHR) {
							escondebotao();
						}
					});
				}

			});
		}
	}

	function escondebotao() {
		$('#btAssina').hide();
		// document.location.reload(); 
	}

	jsonProd = <?=$jsonProd ?>; //// autocomplete produto
	//Autocomplete de produtos (nfitem)
	function criaAutocompletesProd() {
		$("#tbItens .idprodserv").autocomplete({
			source: jsonProd,
			delay: 0
		});
	}

	jCli = <?=$jCli ?>; // autocomplete cliente

	//mapear autocomplete de clientes
	jCli = jQuery.map(jCli, function(o, id) {
		return {
			"label": o.nome,
			value: id + "",
			"tipo": o.tipo
		}
	});

	//autocomplete de clientes
	$("[name*=_orcamento_idpessoa]").autocomplete({
		source: jCli,
		delay: 0,
		select: function(event, ui) {
			preencheendereco(ui.item.value);
		},
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
			};
		}
	});

	function preencheendereco() {
		vIdPessoa = $(":input[name=_1_" + CB.acao + "_orcamento_idpessoa]").cbval();

		if (vIdPessoa) {
			$("#idendereco").html("<option value=''>Procurando....</option>");
			//alert($("#idpessoa").val());	
			$.ajax({
				type: "get",
				url: "ajax/buscaendereco.php?idpessoa=" + vIdPessoa,
				success: function(data) {
					$("#idendereco").html(data);
				},
				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);
				}
			}) //$.ajax

		} else {
			console.warn("js: preencheendereco: Erro: idIdpessoa não informado;")
		}
	} //function preencheendereco(){

	function novoItemdescr() {
		var valor = $("#orcamentoadicvalor").val();
		var descr = $("#orcamentoadicdescr").val();
		CB.post({
			objetos: "_x_i_orcamentoadic_idorcamento=" + $("[name=_1_u_orcamento_idorcamento]").val() + "&_x_i_orcamentoadic_valor=" + valor + "&_x_i_orcamentoadic_descr=" + descr,
			parcial: true
		});
	}

	function excluiritenadic(inid) {
		CB.post({
			objetos: "_x_d_orcamentoadic_idorcamentoadic=" + inid,
			parcial: true
		});
	}


	// cria o item e chama o autocomplete
	function novoItem() {
		oTbItens = $("#tbItens tbody");
		iNovoItem = (oTbItens.find("input.idprodserv").length + 11000);
		htmlTrModelo = $("#modeloNovoIten").html();
		htmlTrModelo = htmlTrModelo.replace("#nameidorcamentoitem", "_" + iNovoItem + "_i_orcamentoitem_idorcamentoitem");
		htmlTrModelo = htmlTrModelo.replace("#nameidorcamento", "_" + iNovoItem + "_i_orcamentoitem_idorcamento");
		htmlTrModelo = htmlTrModelo.replace("#nameidprodserv", "_" + iNovoItem + "_i_orcamentoitem_idtipoteste");
		htmlTrModelo = htmlTrModelo.replace("#namequantidade", "_" + iNovoItem + "_i_orcamentoitem_qtd");
		htmlTrModelo = htmlTrModelo.replace("#namedesconto", "_" + iNovoItem + "_i_orcamentoitem_desconto");

		htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoItem);
		novoTr = "<tr class='dragExcluir'>" + htmlTrModelo + "</tr>";
		oTbItens.append(novoTr);
		criaAutocompletesProd();
	}

	function ordenaItens() {
		$.each($("#tbItens tbody").find("tr"), function(i, otr) {
			//Recupera objetos de update e de insert
			$(this).find(":input[name*=orcamentoitem_ord],:input[name*=ord]").val(i);
		})
	}

	$("#tbItens tbody").sortable({
		update: function(event, objUi) {
			ordenaItens();
		}
	});

	$("#excluirItem").droppable({
		accept: ".dragExcluir",
		drop: function(event, ui) {
			//verifica se existe o idresultado em mode de update. caso positivo, alternar para excluir
			$idorcamentoitem = $(ui.draggable).attr("idorcamentoitem");
			if (parseInt($idorcamentoitem) && CB.acao !== "i") {
				if (confirm("Deseja realmente excluir o teste selecionado?")) {
					ui.draggable.remove();
					CB.post({
						"objetos": "_x_d_orcamentoitem_idorcamentoitem=" + $idorcamentoitem
					});
				}
			} else {
				if ($(ui.draggable).find(":input[name*=_i_orcamentoitem_idorcamentoitem]").length == 1) { //Modo de inclusão
					ui.draggable.remove();
				}
			}
		}
	});


	function altcheck(vtab, vcampo, vid, vcheck) {
		CB.post({
			objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck
		});
	}

	function altremetenteemail(idorcamento, idemailvirtualconf, tipoenvio, idempresa) {
		CB.post({
			objetos: "_w_i_empresaemailobjeto_idempresa=" + idempresa + "&_w_i_empresaemailobjeto_idemailvirtualconf=" + idemailvirtualconf + "&_w_i_empresaemailobjeto_tipoenvio=" + tipoenvio + "&_w_i_empresaemailobjeto_tipoobjeto=orcamento&_w_i_empresaemailobjeto_idobjeto=" + idorcamento,
			parcial: true
		})
	}

	function envioemail(vidcampo, vcampo, vtabela, vmensagem, flag) {
			debugger;
		//var vidcampo = $("#idnf").val();	

		//alert(vidcampo);[
		if (flag == 1) {
			var idemailvirtualconf = $("#emailunico").val();
			var idempresa = $("#idempresaemail").val();

			if (confirm(vmensagem)) {
				$.ajax({
					type: "post",
					url: "ajax/checkitem.php",
					data: {
						idcampo: vidcampo,
						campo: vcampo,
						tabela: vtabela
					},

					success: function(data) { // retorno 200 do servidor apache
						vdata = data.replace(/(\r\n|\r|\n)/g, "");

						if (vdata == 'Y') {

							//document.location.reload(true);
							$("#envioemail" + vidcampo).removeClass("cinza");
							$("#envioemail" + vidcampo).addClass("amarelo");

							//gerar o arquivo com os detalhes para envio
							vurl = "report/orcamento.php?_acao=u&idorcamento=" + vidcampo + "&geraarquivo=Y&gravaarquivo=Y";
							document.body.style.cursor = "wait";

							$.ajax({
								type: 'get',
								url: vurl,
								success: function(data) { // retorno 200 do servidor apache
									vdata = data.replace(/(\r\n|\r|\n)/g, "");

									if (vdata == "OK") {
										alert('Arquivo de orçamento gerado para envio!');
										document.body.style.cursor = "default";
										// document.location.reload(true);
									} else {
										alert(data);
										document.body.style.cursor = "default";
										// document.location.reload(true);
									}

								},
								error: function(objxml) { // nao retornou com sucesso do apache
									document.body.style.cursor = "default";
									alert('Erro: ao gerar arquivo' + objxml.status);
								}
							}) //$.ajax

						} else {
							if (vdata == 'N') {
								$("#envioemail" + vidcampo).removeClass("amarelo");
								$("#envioemail" + vidcampo).removeClass("verde");
								$("#envioemail" + vidcampo).addClass("cinza");
								//document.location.reload(true);					
							} else {
								alert(data);
								document.body.style.cursor = "default";
							}
						}
					},
					error: function(objxml) { // nao retornou com sucesso do apache
						document.body.style.cursor = "default";
						alert('Erro: ' + objxml.status);
					}
				}) //$.ajax

				altremetenteemail(vidcampo, idemailvirtualconf, 'ORCSERV', idempresa)
			}
		} else {
			if (flag == 2) {
				var setemail = $("#setemail").val();
				if (setemail == "1") {
					alert("É necessário escolher um remetente para o envio");
				} else {
					if (confirm(vmensagem)) {
						$.ajax({
							type: "post",
							url: "ajax/checkitem.php",
							data: {
								idcampo: vidcampo,
								campo: vcampo,
								tabela: vtabela
							},

							success: function(data) { // retorno 200 do servidor apache
								vdata = data.replace(/(\r\n|\r|\n)/g, "");

								if (vdata == 'Y') {

									//document.location.reload(true);
									$("#envioemail" + vidcampo).removeClass("cinza");
									$("#envioemail" + vidcampo).addClass("amarelo");

									//gerar o arquivo com os detalhes para envio
									vurl = "report/orcamento.php?_acao=u&idorcamento=" + vidcampo + "&geraarquivo=Y&gravaarquivo=Y";
									document.body.style.cursor = "wait";

									$.ajax({
										type: 'get',
										url: vurl,
										success: function(data) { // retorno 200 do servidor apache
											vdata = data.replace(/(\r\n|\r|\n)/g, "");

											if (vdata == "OK") {
												alert('Arquivo de orçamento gerado para envio!');
												document.body.style.cursor = "default";
												// document.location.reload(true);
											} else {
												alert(data);
												document.body.style.cursor = "default";
												// document.location.reload(true);
											}

										},
										error: function(objxml) { // nao retornou com sucesso do apache
											document.body.style.cursor = "default";
											alert('Erro: ao gerar arquivo' + objxml.status);
										}
									}) //$.ajax

								} else {
									if (vdata == 'N') {
										$("#envioemail" + vidcampo).removeClass("amarelo");
										$("#envioemail" + vidcampo).removeClass("verde");
										$("#envioemail" + vidcampo).addClass("cinza");
										//document.location.reload(true);					
									} else {
										alert(data);
										document.body.style.cursor = "default";
									}
								}
							},
							error: function(objxml) { // nao retornou com sucesso do apache
								document.body.style.cursor = "default";
								alert('Erro: ' + objxml.status);
							}
						}) //$.ajax
					}
				}
			} else {
				if (confirm(vmensagem)) {
					$.ajax({
						type: "post",
						url: "ajax/checkitem.php",
						data: {
							idcampo: vidcampo,
							campo: vcampo,
							tabela: vtabela
						},

						success: function(data) { // retorno 200 do servidor apache
							vdata = data.replace(/(\r\n|\r|\n)/g, "");

							if (vdata == 'Y') {

								//document.location.reload(true);
								$("#envioemail" + vidcampo).removeClass("cinza");
								$("#envioemail" + vidcampo).addClass("amarelo");

								//gerar o arquivo com os detalhes para envio
								vurl = "report/orcamento.php?_acao=u&idorcamento=" + vidcampo + "&geraarquivo=Y&gravaarquivo=Y";
								document.body.style.cursor = "wait";

								$.ajax({
									type: 'get',
									url: vurl,
									success: function(data) { // retorno 200 do servidor apache
										vdata = data.replace(/(\r\n|\r|\n)/g, "");

										if (vdata == "OK") {
											alert('Arquivo de orçamento gerado para envio!');
											document.body.style.cursor = "default";
											// document.location.reload(true);
										} else {
											alert(data);
											document.body.style.cursor = "default";
											// document.location.reload(true);
										}

									},
									error: function(objxml) { // nao retornou com sucesso do apache
										document.body.style.cursor = "default";
										alert('Erro: ao gerar arquivo' + objxml.status);
									}
								}) //$.ajax

							} else {
								if (vdata == 'N') {
									$("#envioemail" + vidcampo).removeClass("amarelo");
									$("#envioemail" + vidcampo).removeClass("verde");
									$("#envioemail" + vidcampo).addClass("cinza");
									//document.location.reload(true);					
								} else {
									alert(data);
									document.body.style.cursor = "default";
								}
							}
						},
						error: function(objxml) { // nao retornou com sucesso do apache
							document.body.style.cursor = "default";
							alert('Erro: ' + objxml.status);
						}
					}) //$.ajax
				}
			}
		}
	}

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"] ?>_rodape
</script>