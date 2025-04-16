<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/tipoprodserv_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "contatipogrupo";
$pagvalcampos = array(
	"idcontatipogrupo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contatipogrupo where idcontatipogrupo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td>
							<input
								name="_1_<?= $_acao ?>_contatipogrupo_idcontatipogrupo"
								type="hidden"
								value="<?= $_1_u_contatipogrupo_idcontatipogrupo ?>"
								readonly='readonly'>
						</td>
						<td class="nowrap">Conta Nível 1</td>
						<td>
							<input
								name="_1_<?= $_acao ?>_contatipogrupo_grupo"
								type="text"
								class="size50"
								value="<?= $_1_u_contatipogrupo_grupo ?>"
								vnulo <? echo ($_acao == 'u' ? 'disabled' : ''); ?>>
						</td>
						<td><i class="fa fa-pencil branco pointer hoverpreto" onclick="alteravalor('grupo','<?= $_1_u_contatipogrupo_grupo ?>','modulohistorico',<?= $_1_u_contatipogrupo_idcontatipogrupo ?>,'Conta Nível 1:', '50')"></i></td>
						<?
						$ListarHistoricoModal = TipoProdServController::buscarHistoricoModuloAlteracao($_1_u_contatipogrupo_idcontatipogrupo, 'contatipogrupo', 'grupo');
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
						?>
							<td>
								<i title="Histórico de alteração" class="fa btn-lg fa-file-text branco pointer hoverpreto" onclick="modalhist('historico_contatipo');"></i>
							</td>

							<td id="historico_contatipo" style="display: none;">
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
						<td style="width: 100%;"></td>
						
						<td>Status</td>
						<td>
							<select name="_1_<?= $_acao ?>_contatipogrupo_status">
								<? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_contatipogrupo_status); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-body">
				<? if ($_1_u_contatipogrupo_tipo == 'N') { ?>
					<div>
						<div class="row">
							<div class="col-md-12 nowrap"><b>Calcular:</b></div>
						</div>
						<hr>
						<div class="row">
							<?
							$sqlo = "SELECT o.idobjetovinculo,c.idcontatipogrupo,c.grupo 
										from contatipogrupo c 
											left join objetovinculo o on(o.idobjeto=" . $_1_u_contatipogrupo_idcontatipogrupo . " and o.tipoobjeto='contatipogrupo' 
											and o.idobjetovinc = c.idcontatipogrupo and o.tipoobjetovinc='contatipogrupo')
											where c.status='ATIVO' and c.idempresa=" . $_1_u_contatipogrupo_idempresa . " AND c.tipo='I' order by grupo";
							$reso = d::b()->query($sqlo) or die("A Consulta dos produtos e serviçoes vinculados falhou :" . mysql_error() . "<br>Sql:" . $sqlo);
							while ($rowo = mysqli_fetch_assoc($reso)) { ?>
								<div class="col-md-3 nowrap" style="font-size:11px">
									<?
									if (!empty($rowo['idobjetovinculo'])) {
									?>
										<i class="fa fa-check-square-o fa-1x btn-lg pointer paddingRightZero" onclick="retirarN(<?= $rowo['idobjetovinculo'] ?>);" alt="Retirar Nível de Cálculo"></i>
									<? } else { ?>
										<i class="fa fa-square-o fa-1x btn-lg pointer paddingRightZero" onclick="inserirN(<?= $rowo['idcontatipogrupo'] ?>);" alt="Inserir Nível no Cálculo"></i>
									<? }
									echo ($rowo['grupo']);
									?>
								</div>
							<? } ?>
						</div>
					</div>
				<? } ?>
				<table>
					
					<tr>
					<td class="nowrap">Tipo Cálculo</td>
						<td>
							<select name="_1_<?= $_acao ?>_contatipogrupo_tipo">
								<? fillselect("select 'I','Item' union select 'N','Nível'", $_1_u_contatipogrupo_tipo); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Classificação:</td>
						<td>
							<select name="_1_<?= $_acao ?>_contatipogrupo_classificacao" <?php echo $_1_u_contatipogrupo_classificacao != '' ? 'disabled' : ''; ?>>
								<? fillselect("select 'RECEITA','Receita' union select 'DESPESA','Despesa'", $_1_u_contatipogrupo_classificacao); ?>
							</select>
						</td>
						<td><i class="fa fa-pencil preto pointer hoverazul" onclick="alteravalorclass('classificacao','<?= $_1_u_contatipogrupo_classificacao ?>','modulohistorico',<?= $_1_u_contatipogrupo_idcontatipogrupo ?>,'Classificação:', '10')" title="Alterar Classificação"></i></td>
						<?
						$ListarHistoricoModal = TipoProdServController::buscarHistoricoModuloAlteracao($_1_u_contatipogrupo_idcontatipogrupo, 'contatipogrupo', 'classificacao');
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
						?>
							<td>
								<i title="Histórico de alteração" class="fa btn-lg fa-file-text preto pointer hoverazul" onclick="modalhist('historico_contatipo');"></i>
							</td>

							<td id="historico_contatipo" style="display: none;">
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
					</tr>
				
					<tr>
						<td>Ordem:</td>
						<td>
							<input
								name="_1_<?= $_acao ?>_contatipogrupo_ordem"
								type="text"
								class="size10"
								value="<?= $_1_u_contatipogrupo_ordem ?>"
								vdecimal>
						</td>
					</tr>
					<tr>
					<td class="nowrap">Base de Cálculo para o Percentual
						<i class="btn fa fa-info-circle preto" title="Se marcado com SIM, o valor deste é utilizado como base cálculo, para o % no DRE e DFC. MARCAR SOMENTE 1 NÍVEL POR EMPRESA." ></i>

						</td>
						<td>
							<select name="_1_<?= $_acao ?>_contatipogrupo_percentual">
								<? fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_contatipogrupo_percentual); ?>
							</select>
						</td>
					
					</tr>
					<tr>
						<td>Cor:</td>
						<td>
							<input class="size10" name="_1_<?= $_acao ?>_contatipogrupo_cor" type="color" id="cores" list="arcoIris" value="<?= strtoupper($_1_u_contatipogrupo_cor) ?>" size="6">
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
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">Conta nivel 2 Relacionadas</div>
			<table class="table table-striped">
				<tbody>
					<?
					$sql = "select contatipo, idcontatipo from contatipo where idempresa=" . CB::idempresa() . " 
						and idcontatipogrupo=" . $_1_u_contatipogrupo_idcontatipogrupo . " and status = 'ATIVO' order by contatipo";

					$res = d::b()->query($sql) or die("Erro ao buscar contatipo: " . mysqli_error(d::b()) . "<p>SQL:" . $sql);

					while ($rw = mysqli_fetch_assoc($res)) { ?>
						<tr>
							<td colspan="3" style="text-align:left;">
								<a target="_blank" href="?_modulo=contatipo&_acao=u&idcontatipo=<?= $rw['idcontatipo'] ?>"> <?= $rw['contatipo'] ?></a>
							</td>
						</tr>
					<? } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?
if (!empty($_1_u_contatipogrupo_idcontatipogrupo)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_contatipogrupo_idcontatipogrupo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "contatipogrupo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>

<script>
	$("#cores").on('change', function() {
		var taxid = $("#cores").val(); // getting tax value here
		$("[name=_1_u_contatipogrupo_cor]").val(taxid);

	})

	function inserirN(idcontatipogrupo) {
		CB.post({
			objetos: "_x_i_objetovinculo_idobjeto=" + $("[name=_1_u_contatipogrupo_idcontatipogrupo]").val() + "&_x_i_objetovinculo_tipoobjeto=contatipogrupo&_x_i_objetovinculo_idobjetovinc=" + idcontatipogrupo + "&_x_i_objetovinculo_tipoobjetovinc=contatipogrupo"
		});
	}

	function retirarN(inidobjetovinculo) {
		CB.post({
			objetos: "_x_d_objetovinculo_idobjetovinculo=" + inidobjetovinculo,
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
							<input name="_h1_i_${tabela}_tipoobjeto" value="contatipogrupo" type="hidden">
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

	function alteravalorclass(campo, valor, tabela, inid, texto, size) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
				<table class="table table-hover">
					<tr>
						<td style="width: 15px">${texto}</td>
						<td>
							<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
							<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
							<input name="_h1_i_${tabela}_tipoobjeto" value="contatipogrupo" type="hidden">
							<input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
							<select name="_h1_i_${tabela}_valor" class="size${size}">
								<? fillselect("select 'RECEITA','Receita' union select 'DESPESA','Despesa'", $_1_u_contatipogrupo_classificacao); ?>
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

	function modalhist(div) {
		debugger;

		var html = $(`#${div}`).html();

		CB.modal({
			titulo: "</strong>Histórico de Alteração:</strong>",
			corpo: html,
			classe: 'sessenta'
		});
	}

	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>