<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/contaitem_controller.php");


if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "contaitem";
$pagvalcampos = array(
	"idcontaitem" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contaitem where idcontaitem = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getTipoItem()
{
	global $JSON, $_1_u_contaitem_idcontaitem;

	$sq = "SELECT idtipoprodserv,tipoprodserv 
			from tipoprodserv t
			where status = 'ATIVO' and
				not exists(select 1 
							from contaitemtipoprodserv c 
							where t.idtipoprodserv = c.idtipoprodserv )
			" . getidempresa('idempresa', 'tipoprodserv') . "
			order by tipoprodserv";

	$rq = d::b()->query($sq) or die("Erro ao consultar Tipoprodserv");

	if (mysqli_num_rows($rq) > 0) {
		$arr = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rq)) {
			$arr[$i]["idtipoprodserv"] = $r["idtipoprodserv"];
			$arr[$i]["tipoprodserv"] = $r["tipoprodserv"];
			$i++;
		}
		$arr = $JSON->encode($arr);
	} else {
		$arr = 0;
	}

	return $arr;
}

function getjsonLp()
{
	global $JSON, $_1_u_contaitem_idcontaitem, $_1_u_contaitem_idempresa;

	$sq = "SELECT concat(e.sigla,' - ',lp.descricao) as sigla,
				lp.idlp,
				lp.descricao
			FROM carbonnovo._lp lp
				JOIN empresa e ON (lp.idempresa = e.idempresa)
			WHERE lp.status = 'ATIVO' 
				AND e.sigla is not null
				AND lp.idempresa = " . $_1_u_contaitem_idempresa . "
				AND NOT EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto = lp.idlp AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'contaitem' and ov.idobjetovinc = $_1_u_contaitem_idcontaitem)
			ORDER BY e.idempresa";

	$rq = d::b()->query($sq);

	if (mysqli_num_rows($rq) > 0) {
		$arr = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rq)) {
			$arr[$i]["idlp"] = $r["idlp"];
			$arr[$i]["descricao"] = $r["sigla"];
			$i++;
		}
		$arr = $JSON->encode($arr);
	} else {
		$arr = $JSON->encode([]);
	}

	return $arr;
}

?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td>
							<input
								name="_1_<?= $_acao ?>_contaitem_idcontaitem"
								type="hidden"
								value="<?= $_1_u_contaitem_idcontaitem ?>"
								readonly='readonly'>
						</td>
						<td class="nowrap">Categoria</td>
						<td>
							<input
								name="_1_<?= $_acao ?>_contaitem_contaitem"
								type="text"
								value="<?= $_1_u_contaitem_contaitem ?>"
								class="size50"
								<? echo ($_acao == 'u' ? 'disabled' : ''); ?>>
						</td>
						<td><i class="fa fa-pencil branco pointer hoverpreto" onclick="alteravalor('contaitem','<?= $_1_u_contaitem_contaitem ?>','modulohistorico',<?= $_1_u_contaitem_idcontaitem ?>,'Categoria:', '50')"></i></td>
						<?
						$ListarHistoricoModal = ContaItemController::buscarHistoricoAlteracao($_1_u_contaitem_idcontaitem, ['contaitem']);
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
						?>

							<td class="alterar_categoria">
								<i title="Histórico de alteração" class="fa btn-lg fa-info-circle branco pointer hoverpreto" onclick="modalhist('historico_categoria');"></i>
							</td>

							<td id="historico_categoria" style="display: none;">
								<table class="table table-hover">
									<?
									if ($qtdvh > 0) {
									?>
										<thead>
											<tr>
												<th scope="col">De</th>
												<th scope="col">Para</th>
												<th scope="col">Por</th>
												<th scope="col">Em</th>
											</tr>
										</thead>
										<tbody>
											<?
											foreach ($ListarHistoricoModal as $historicoModal) {
											?>
												<tr>
													<td><?= $historicoModal['valor_old'] ?></td>
													<td><?= $historicoModal['valor'] ?></td>
													<td><?= $historicoModal['nomecurto'] ?></td>
													<td><?= dmahms($historicoModal['criadoem']) ?></td>
												</tr>
											<?
											}
											?>
										</tbody>
									<?
									}
									?>
								</table>
							</td>
						<?
						}
						?>
						<td>
							<?
							if ($_1_u_contaitem_compra == "Y") {
								$valorCompras = 'N';
								$checked = 'fa-check-square-o';
							} else {
								$valorCompras = 'Y';
								$checked = 'fa-square-o';
							}
							?>
							<i style="padding-right: 0px;" class="fa <?= $checked ?> fa-1x btn-lg pointer" onclick="alterarCheckbox('compra', '<?= $valorCompras ?>');" alt="Alterar para Sim"></i>
						</td>
						<td>Compras</td>
						<!-- Soltag -->
						<td>
							<?
							if ($_1_u_contaitem_vinculo == "soltag") {
								$valorCompras = '';
								$checked = 'fa-check-square-o';
							} else {
								$valorCompras = 'soltag';
								$checked = 'fa-square-o';
							}
							?>
							<i style="padding-right: 0px;" class="fa <?= $checked ?> fa-1x btn-lg pointer" onclick="vincularGrupoES('vinculo', '<?= $valorCompras ?>');" alt="Alterar para Sim"></i>
						</td>
						<td>Soltag</td>
						<td>
							<?
							if ($_1_u_contaitem_devolucao == "Y") {
								$valorCompras = 'N';
								$checked = 'fa-check-square-o';
							} else {
								$valorCompras = 'Y';
								$checked = 'fa-square-o';
							}
							?>
							<i style="padding-right: 0px;" class="fa <?= $checked ?> fa-1x btn-lg pointer" onclick="vincularGrupoES('devolucao', '<?= $valorCompras ?>');" alt="Alterar para Sim"></i>
						</td>
						<td>Devolução</td>
						<td>
							<?
							if ($_1_u_contaitem_cancelamento == "Y") {
								$valorCompras = 'N';
								$checked = 'fa-check-square-o';
							} else {
								$valorCompras = 'Y';
								$checked = 'fa-square-o';
							}
							?>
							<i style="padding-right: 0px;" class="fa <?= $checked ?> fa-1x btn-lg pointer" onclick="vincularGrupoES('cancelamento', '<?= $valorCompras ?>');" alt="Alterar para Sim"></i>
						</td>
						<td class="col-sm-5">Cancelamento</td>
						<td>Tipo</td>
						<td>
							<select name="_1_<?= $_acao ?>_contaitem_tipo">
								<? fillselect("select 'PRODUTO','Produto' union select 'SERVICO','Serviço'", $_1_u_contaitem_tipo); ?>
							</select>
						</td>
						<td>Status</td>
						<td>
							<select name="_1_<?= $_acao ?>_contaitem_status">
								<? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_contaitem_status); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?php if ($_acao == 'u') { ?>
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-body">
					<table>
						<tr>
							<td>Conta Nível 2:</td>
							<td>
								<select class="size50" name="_1_<?= $_acao ?>_contaitem_idcontatipo" vnulo>
									<option value=""></option>
									<? fillselect("select idcontatipo,contatipo from contatipo where idempresa=" . CB::idempresa() . " and status ='ATIVO' order by contatipo", $_1_u_contaitem_idcontatipo); ?>
								</select>
								<a target="_blank" href="?_modulo=contatipo&_acao=u&idcontatipo=<?= $_1_u_contaitem_idcontatipo ?>"><i class="fa fa-bars azul pointer fa-lg"></i></a>
							</td>
						</tr>
						<tr>
							<td>Ordem:</td>
							<td>
								<input
									name="_1_<?= $_acao ?>_contaitem_ordem"
									type="text"
									class="size8"
									value="<?= $_1_u_contaitem_ordem ?>"
									vdecimal>
								<i class="fa fa-pencil blue pointer" onclick="alteravalor('ordem','<?= $_1_u_contaitem_ordem ?>','modulohistorico',<?= $_1_u_contaitem_idcontaitem ?>,'Ordem:','10')"></i>
							</td>
						</tr>
						<tr>
							<td>DRE:</td>
							<td>
								<select class="size8" name="_1_<?= $_acao ?>_contaitem_dre" vnulo>
									<?
									fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_contaitem_dre);
									?>
								</select>
								<i class="fa fa-pencil blue pointer" onclick="alteravalordre('dre','<?= $_1_u_contaitem_dre ?>','modulohistorico',<?= $_1_u_contaitem_idcontaitem ?>,'DRE:','50')"></i>
							</td>
						</tr>
						<tr>
							<td>Fluxo de Caixa:</td>
							<td>
								<select class="size8" name="_1_<?= $_acao ?>_contaitem_fluxocaixa" vnulo>
									<?
									fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_contaitem_fluxocaixa);
									?>
								</select>
								<i class="fa fa-pencil blue pointer" onclick="alteravalordre('fluxocaixa','<?= $_1_u_contaitem_fluxocaixa ?>','modulohistorico',<?= $_1_u_contaitem_idcontaitem ?>,'Fluxo de Caixa:','50')"></i>
							</td>
						</tr>
						<tr>
							<td>Soma Relatório:</td>
							<td>
								<? if (!empty($_1_u_contaitem_somarelatorio)) {
									$campdisabled = "disabled='disabled' ";
								} ?>
								<select <?= $campdisabled ?> class="size8" name="_1_<?= $_acao ?>_contaitem_somarelatorio" vnulo>
									<option value=""></option>
									<?
									fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_contaitem_somarelatorio);
									?>
								</select>
							&nbsp;&nbsp;&nbsp;Se "Não" só aparecerá para compras do tipo Outro(s).</td>
						</tr>
						<tr>
							<td>Cor:</td>
							<td>
								<input class="size8" name="_1_<?= $_acao ?>_contaitem_cor" type="color" id="cores" list="arcoIris" value="<?= strtoupper($_1_u_contaitem_cor) ?>" size="6">
								<datalist id="arcoIris">
									<option value="#FFA500">Laranja</option>
									<option value="#FF0000">Vermelho</option>
									<option value="#FFFF00">Amarelo</option>
									<option value="#008000">Verde</option>
									<option value="#0000FF">Azul</option>
									<option value="#4B0082">Indigo</option>
									<option value="#EE82EE">Violeta</option>
								</datalist>

							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<?
		if (!empty($_1_u_contaitem_idcontaitem)) {
		?>
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading">Subcategoria Relacionada(s)</div>
					<div class="panel-body">
						<input id="tipoprodserv" class="compacto" type="text" cbvalue placeholder="Selecione">
						<hr>
						<table class="table table-striped planilha">
							<?
							$sqlh = "select e.idcontaitemtipoprodserv,t.tipoprodserv,e.idtipoprodserv
							from contaitemtipoprodserv e  join tipoprodserv t on(t.idtipoprodserv=e.idtipoprodserv and t.status='ATIVO')
							where e.idcontaitem=" . $_1_u_contaitem_idcontaitem . " order by t.tipoprodserv ";

							$resh = d::b()->query($sqlh) or die("Erro ao buscar tipo de item : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);
							$qtdh = mysqli_num_rows($resh);
							if ($qtdh > 0) { ?>
								<tr>
									<td> Subcategoria</td>
									<td></td>
								</tr>
								<?
								$i = 1;
								while ($rowh = mysqli_fetch_assoc($resh)) {
								?>
									<tr>
										<td>
											<input name="_x<?= $i ?>_u_contaitemtipoprodserv_idcontaitemtipoprodserv" type="hidden" value="<?= $rowh['idcontaitemtipoprodserv'] ?>">
											<a title="Editar Teste" target="_blank" href="?_modulo=tipoprodserv&_acao=u&idtipoprodserv=<?= $rowh['idtipoprodserv'] ?>"><?= $rowh['tipoprodserv'] ?></a>
										</td>
										<td style="text-align: right;">
											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contaitemtipoprodserv',<?= $rowh['idcontaitemtipoprodserv'] ?>)" alt="Excluir !"></i>
										</td>
									</tr>
							<? $i++;
								}
							} ?>
						</table>
					</div>
				</div>
			</div>
		<?
		}
		?>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">LPs</div>
				<table class="table table-striped planilha">
					<?
					$sql = "SELECT gp.idlpgrupo as idlpgrupo,
											gp.descricao as descgrupo,
											g.idlpgrupo as idgrupo,
											g.descricao,
											e.empresa,
											e.sigla,
											l.idlp,
											l.idempresa as idempresa,
											ov.*
									FROM carbonnovo._lp l
										JOIN empresa e ON (l.idempresa = e.idempresa)
										JOIN carbonnovo._lpobjeto o ON o.idlp = l.idlp AND o.tipoobjeto = 'lpgrupo'
										JOIN carbonnovo._lpgrupo g ON g.idlpgrupo = o.idobjeto
										JOIN carbonnovo._lpgrupo gp ON gp.idlpgrupo = g.lpgrupopar
										JOIN objetovinculo ov ON (ov.idobjeto = l.idlp AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'contaitem')
										JOIN contaitem ci on (ov.idobjetovinc = ci.idcontaitem)
									WHERE ci.idcontaitem = " . $_1_u_contaitem_idcontaitem . "
											AND  l.status = 'ATIVO' 
											AND e.status = 'ATIVO' 
											AND g.status = 'ATIVO'
											AND gp.status = 'ATIVO'
									ORDER BY e.idempresa, gp.descricao, g.descricao";
					$empresa = "";
					$res = d::b()->query($sql) or die("Erro ao buscar tipo de item : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);

					while ($rw = mysqli_fetch_assoc($res)) {
						if ($empresa != $rw['empresa']) {
							$empresa = $rw['empresa']; ?>
							<tr style="background-color: #cccccc;">
								<td colspan="3" style="font-weight: bold; text-align:center;">
									<?= $rw['empresa'] ?>
								</td>
							</tr>
						<? } ?>
						<tr>
							<td class="hoverazul">
								<a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?= $rw['idlp'] ?>"><?= $rw['descricao'] ?></a>
							</td>
							<!-- <td class="hoverazul">
								<?= $rw['sigla'] ?>
							</td> -->
							<td>
								<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
									<i class="fa fa-trash cinzaclaro hoververmelho pointer" onclick="excluir('objetovinculo',<?= $rw['idobjetovinculo'] ?>)" style="margin-left: 6px; margin-right: 0px;"></i>
								<? } ?>
							</td>
						</tr>
					<? } ?>
					<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
						<tr>
							<td colspan="3">
								<input id='selectlps' style="display:none">
								<i class="fa fa-plus-circle verde pointer fa-lg" id="mais"></i>
							</td>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>
	</div>
<? } ?>
<?
if (!empty($_1_u_contaitem_idcontaitem)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_contaitem_idcontaitem; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "contaitem"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>


<script>
	<?php if (!empty($_1_u_contaitem_idcontaitem)) { ?>
		$(document).ready(function() {
			$("#mais").click(function() {
				$("#selectlps").toggle();
			});
		});
		var $jLp = <?= getjsonLp($_1_u_contaitem_idcontaitem); ?>;

		$("#selectlps").autocomplete({
			source: $jLp,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.descricao + "</a>").appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_objetovinculo_idobjeto": ui.item.idlp,
						"_x_i_objetovinculo_tipoobjeto": '_lp',
						"_x_i_objetovinculo_tipoobjetovinc": 'contaitem',
						"_x_i_objetovinculo_idobjetovinc": $("[name='_1_u_contaitem_idcontaitem']").val()
					},
					parcial: true
				});
			}
		});
		var jTipoItem = <?= getTipoItem() ?> || 0;

		if (jTipoItem != 0) {
			$("#tipoprodserv").autocomplete({
				source: jTipoItem,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

						lbItem = item.tipoprodserv;
						return $('<li>')
							.append('<a>' + lbItem + '</a>')
							.appendTo(ul);
					};
				},
				select: function(event, ui) {
					CB.post({
						objetos: {
							"_x_i_contaitemtipoprodserv_idcontaitem": $("[name=_1_u_contaitem_idcontaitem]").val(),
							"_x_i_contaitemtipoprodserv_idtipoprodserv": ui.item.idtipoprodserv,
						},
						parcial: true
					});
				}
			});
		}

		function excluir(tab, inid) {
			if (confirm("Deseja remover esta LP permanentemente?")) {
				CB.post({
					objetos: "_x_d_" + tab + "_id" + tab + "=" + inid
				});
			}

		}

		function alterarCheckbox(campo, inval) {
			CB.post({
				objetos: `_x_u_contaitem_idcontaitem=${$("[name=_1_u_contaitem_idcontaitem]").val()}&_x_u_contaitem_${campo}=${inval}`,
				parcial: true
			});
		}

		function vincularGrupoES(campo, inval) {
			CB.post({
				objetos: `_x_u_contaitem_idcontaitem=${$("[name=_1_u_contaitem_idcontaitem]").val()}&_x_u_contaitem_${campo}=${inval}`,
				parcial: true
			});
		}

		function alteravalor(campo, valor, tabela, inid, texto, size) {
			htmlTrModelo = "";
			htmlTrModelo = `<div id="alt${campo}${inid}">
				<table class="table table-hover">
					<tr>
						<td>${texto}</td>
						<td>
							<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
							<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
							<input name="_h1_i_${tabela}_tipoobjeto" value="contaitem" type="hidden">
							<input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
							<input name="_h1_i_${tabela}_valor" value="${valor}" class="size${size}" type="text">
						</td>
					</tr>
				</table> 
			</div>`;

			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");

			strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

			CB.modal({
				titulo: strCabecalho,
				corpo: "<table>" + objfrm.html() + "</table>",
				classe: 'sessenta',
				aoAbrir: function(vthis) {
					$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
				}
			});

		}

		function alteravalordre(campo, valor, tabela, inid, texto, size) {
			htmlTrModelo = "";
			htmlTrModelo = `<div id="alt${campo}${inid}">
				<table class="table table-hover">
					<tr>
						<td style="width: 15px">${texto}</td>
						<td>
							<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
							<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
							<input name="_h1_i_${tabela}_tipoobjeto" value="contaitem" type="hidden">
							<input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
							<select class="size8" name="_h1_i_${tabela}_valor" vnulo>
								<?
								fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_contaitem_dre);
								?>
							</select>
						</td>
					</tr>
				</table> 
			</div>`;

			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");

			strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

			CB.modal({
				titulo: strCabecalho,
				corpo: "<table>" + objfrm.html() + "</table>",
				classe: 'sessenta',
				aoAbrir: function(vthis) {
					$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
				}
			});

		}
	<? } ?>

	$("#cores").on('change', function() {

		var taxid = $("#cores").val(); // getting tax value here
		$("[name=_1_u_contaitem_cor]").val(taxid);

	});

	function modalhist(div) {
		debugger;

		var html = $(`#${div}`).html();

		CB.modal({
			titulo: "</strong>Histórico de Alteração:</strong>",
			corpo: html,
			classe: 'sessenta'
		});
	}
</script>