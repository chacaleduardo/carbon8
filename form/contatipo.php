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
$pagvaltabela = "contatipo";
$pagvalcampos = array(
	"idcontatipo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contatipo where idcontatipo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getTipoGrupo()
{
	global $JSON;

	$sq = "SELECT idcontatipogrupo,grupo 
			from contatipogrupo t
			where idempresa=" . CB::idempresa() . " and  status = 'ATIVO'  and
			order by grupo";

	$rq = d::b()->query($sq) or die("Erro ao consultar contatipogrupo");

	if (mysqli_num_rows($rq) > 0) {
		$arr = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rq)) {
			$arr[$i]["idcontatipogrupo"] = $r["idcontatipogrupo"];
			$arr[$i]["grupo"] = $r["grupo"];
			$i++;
		}
		$arr = $JSON->encode($arr);
	} else {
		$arr = 0;
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
								name="_1_<?= $_acao ?>_contatipo_idcontatipo"
								type="hidden"
								value="<?= $_1_u_contatipo_idcontatipo ?>"
								readonly='readonly'>
						</td>
						<td class="nowrap">Conta Nível 2:</td>
						<td>
							<input
								name="_1_<?= $_acao ?>_contatipo_contatipo"
								type="text"
								class="size50"
								value="<?= $_1_u_contatipo_contatipo ?>"
								<? echo ($_acao == 'u' ? 'disabled' : ''); ?>>
						</td>
						<td><i class="fa fa-pencil branco pointer hoverpreto" onclick="alteravalor('contatipo','<?= $_1_u_contatipo_contatipo ?>','modulohistorico',<?= $_1_u_contatipo_idcontatipo ?>,'Conta Nível 2:', '50')"></i></td>
						<?
						$ListarHistoricoModal = TipoProdServController::buscarHistoricoModuloAlteracao($_1_u_contatipo_idcontatipo, 'contatipo', 'contatipo');
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
						<td>Status:</td>
						<td>
							<select name="_1_<?= $_acao ?>_contatipo_status">
								<? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_contatipo_status); ?>
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
				<div class="panel-body">
					<table>
						<tr>
							<td>Conta Nível 1:</td>
							<td>
								<select class="size50" name="_1_<?= $_acao ?>_contatipo_idcontatipogrupo" vnulo>
									<option value=""></option>
									<? fillselect("select idcontatipogrupo,grupo from contatipogrupo where idempresa=" . CB::idempresa() . " and  status = 'ATIVO' order by grupo", $_1_u_contatipo_idcontatipogrupo); ?>
								</select>
								<a target="_blank" href="?_modulo=contatipogrupo&_acao=u&idcontatipogrupo=<?= $_1_u_contatipo_idcontatipogrupo ?>"><i class="fa fa-bars azul pointer fa-lg"></i></a>
							</td>
						</tr>
						<tr>
							<td>Ordem:</td>
							<td>
								<input
									name="_1_<?= $_acao ?>_contatipo_ordem"
									type="text"
									class="size10"
									value="<?= $_1_u_contatipo_ordem ?>"
									vdecimal>
							</td>
						</tr>
						<tr>
							<td>Cor:</td>
							<td>
								<input class="size10" name="_1_<?= $_acao ?>_contatipo_cor" type="color" id="cores" list="arcoIris" value="<?= strtoupper($_1_u_contatipo_cor) ?>" size="6">
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
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">Categorias Relacionadas</div>
			<table class="table table-striped">
				<tbody>
					<?
					$sql = "select contaitem, idcontaitem from contaitem where idempresa=" . CB::idempresa() . " 
						and idcontatipo=" . $_1_u_contatipo_idcontatipo . " and status = 'ATIVO' order by contaitem";

					$res = d::b()->query($sql) or die("Erro ao buscar contaitem: " . mysqli_error(d::b()) . "<p>SQL:" . $sql);

					while ($rw = mysqli_fetch_assoc($res)) { ?>
						<tr>
							<td colspan="3" style="text-align:left;">
								<a target="_blank" href="?_modulo=contaitem&_acao=u&idcontaitem=<?= $rw['idcontaitem'] ?>"><?= $rw['contaitem'] ?></a>
							</td>
						</tr>
					<? } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<? if (!empty($_1_u_contatipo_idcontatipo)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_contatipo_idcontatipo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "contatipo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php'; ?>
<script>
	$("#cores").on('change', function() {

		debugger;

		var taxid = $("#cores").val(); // getting tax value here

		$("[name=_1_u_contatipo_cor]").val(taxid);

	})

	function alteravalor(campo, valor, tabela, inid, texto, size) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
				<table class="table table-hover">
					<tr>
						<td>${texto}</td>
						<td>
							<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
							<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
							<input name="_h1_i_${tabela}_tipoobjeto" value="contatipo" type="hidden">
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