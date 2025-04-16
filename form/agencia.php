<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "agencia";
$pagvalcampos = array(
	"idagencia" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from agencia where idagencia = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


function getjsonLp()
{
	global $JSON, $_1_u_agencia_idagencia, $_1_u_agencia_idempresa;

	$sq = "SELECT concat(e.sigla,' - ',lp.descricao) as sigla,
				lp.idlp,
				lp.descricao
			FROM carbonnovo._lp lp
				JOIN empresa e ON (lp.idempresa = e.idempresa and e.idempresa=" . $_1_u_agencia_idempresa . ")
			WHERE lp.status = 'ATIVO' 
				AND e.sigla is not null
				AND NOT EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto = lp.idlp AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'agencia' and ov.idobjetovinc = $_1_u_agencia_idagencia)
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
<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<table>
				<tr>
					<td>
						<input name="_1_<?= $_acao ?>_agencia_idagencia" type="hidden" value="<?= $_1_u_agencia_idagencia ?>" readonly='readonly'>
					</td>
					<td>Agência</td>
					<td>
						<input name="_1_<?= $_acao ?>_agencia_agencia" type="text" value="<?= $_1_u_agencia_agencia ?>" vnulo>
					</td>
					<td> Status</td>
					<td>
						<select name="_1_<?= $_acao ?>_agencia_status">
							<? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_agencia_status); ?>
						</select>
					</td>
					<td> Código Agência Domínio</td>
					<td>
						<input name="_1_<?= $_acao ?>_agencia_agencia_dominio" type="text" value="<?= $_1_u_agencia_agencia_dominio ?>" vnulo>
					</td>
				</tr>
			</table>
		</div>
		<div class="panel-body">
			<div class="col-md-8">
				<table>
					<tr>
						<td>Nº Banco</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_nbanco" type="text" class='size3' value="<?= $_1_u_agencia_nbanco ?>" vnulo>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Nº Agência</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_nagencia" type="text" class='size8' value="<?= $_1_u_agencia_nagencia ?>" vnulo>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>N° Operação</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_noperacao" type="text" class='size8' value="<?= $_1_u_agencia_noperacao ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>N° Conta</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_nconta" type="text" class='size8' value="<?= $_1_u_agencia_nconta ?>"
								vnulo>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Chave pix</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_chavepix" type="text" class='size8' value="<?= $_1_u_agencia_chavepix ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Data de Criação</td>
						<td>
							<input style="width:8em;" name="_1_<?= $_acao ?>_agencia_criacao" class="calendario" size="10" value="<?= $_1_u_agencia_criacao ?>" vnulo autocomplete="off">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Gerente</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_gerente" type="text" class='size15' value="<?= $_1_u_agencia_gerente	?>" vnulo>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Remessa</td>
						<td>
							<select name="_1_<?= $_acao ?>_agencia_remessa" class='size15'>
								<? fillselect("select 'remessa','Itaú-Remessa' union select 'remessasicredi','Sicredi-Remessa '", $_1_u_agencia_remessa); ?>
							</select>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Boleto</td>
						<td>
							<select name="_1_<?= $_acao ?>_agencia_boleto" class='size15'>
								<? fillselect("select 'boleto_itau','Itaú-Boleto' union select 'boleto_sicredi','Sicredi-Boleto'", $_1_u_agencia_boleto); ?>
							</select>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Retono Boleto:</td>
						<td>
							<select name="_1_<?= $_acao ?>_agencia_retornorm" class='size15'>
								<? fillselect("select 'RETORNOBOLETO','Arquivo-Itaú' union select 'RETORNOSICREDI','Arquivo-Sicredi'", $_1_u_agencia_retornorm); ?>
							</select>
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Taxa de Boleto:</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_txboleto" type="text" class='size8' value="<?= $_1_u_agencia_txboleto ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Juros 1 dia (%):</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_juros" type="text" class='size8' value="<?= $_1_u_agencia_juros ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Multa Atraso (%):</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_multa" type="text" class='size8' value="<?= $_1_u_agencia_multa ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Telefone</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_telefone" type="text" class='size8' value="<?= $_1_u_agencia_telefone ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Ordem</td>
						<td>
							<input name="_1_<?= $_acao ?>_agencia_ord" type="text" class='size8' value="<?= $_1_u_agencia_ord ?>">
						</td>
						<td></td>
					</tr>
					<tr>
						<td>Mensagem de protesto:</td>
						<td colspan="2">
							<select name="_1_<?= $_acao ?>_agencia_instrucao" class='size15'>
								<option value=""></option>
								<? fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_agencia_instrucao); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading">LPs</div>
					<div class="panel-body">
						<table class="table table-striped planilha">
							<?
							$sql = "SELECT 
										l.descricao,
										e.empresa,
										e.sigla,
										l.idlp,
										l.idempresa AS idempresa,
										ov.*
									FROM
										carbonnovo._lp l
											JOIN
										empresa e ON (l.idempresa = e.idempresa)
											JOIN
										objetovinculo ov ON (ov.idobjeto = l.idlp
											AND ov.tipoobjeto = '_lp'
											AND ov.tipoobjetovinc = 'agencia')
									WHERE
										ov.idobjetovinc = " . $_1_u_agencia_idagencia . "
											AND l.status = 'ATIVO'
											AND e.status = 'ATIVO'
									ORDER BY l.descricao, l.idempresa";;
							$empresa = "";
							$res = d::b()->query($sql) or die("Erro ao buscar tipo de item : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);

							while ($rw = mysqli_fetch_assoc($res)) {
								if ($empresa != $rw['empresa']) {
									$empresa = $rw['empresa']; ?>
									<tr style="background-color: #cccccc;">
										<td colspan="3" style="font-weight: bold; text-align:center;">
											<a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?= $rw['idlp'] ?>"><?= $rw['empresa'] ?></a>
										</td>
									</tr>
								<? } ?>
								<tr>
									<td class="hoverazul">
										<?= $rw['descricao'] ?>
									</td>
									<td class="hoverazul">
										<?= $rw['sigla'] ?>
									</td>
									<td>
										<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
											<i class="fa fa-trash vermelho hoverpreto pointer" onclick="excluir('objetovinculo',<?= $rw['idobjetovinculo'] ?>)" style="margin-left: 6px; margin-right: 0px;"></i>
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
		</div>
	</div>
</div>


<div class="col-md-12">
	<? if ($_acao == 'u') {
		$_idModuloParaAssinatura = $_1_u_agencia_idagencia;
		require '../form/viewAssinaturas.php';
	}
	?>
	<? $tabaud = "agencia";
	require 'viewCriadoAlterado.php'; ?>
	<!-- <div class="panel panel-default">		
	<div class="panel-body">
		<div class="row col-md-12">		
			<div class="col-md-1 nowrap">Criado Por:</div>     
			<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_criadopor"} ?></div>
			<div class="col-md-1 nowrap">Criado Em:</div>     
			<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_criadoem"} ?></div>   
		</div>
		<div class="row col-md-12">            
			<div class="col-md-1 nowrap">Alterado Por:</div>     
			<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_alteradopor"} ?></div>
			<div class="col-md-1 nowrap">Alterado Em:</div>     
			<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_alteradoem"} ?></div>       
		</div>
	</div>
</div> -->
</div>


<script>
	function excluir(tab, inid) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: "_x_d_" + tab + "_id" + tab + "=" + inid
			});
		}

	}

	$(document).ready(function() {
		$("#mais").click(function() {
			$("#selectlps").toggle();
		});
	});

	function novo(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_idagencia=" + $("[name=_1_u_agencia_idagencia]").val()
		});

	}
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
					"_x_i_objetovinculo_tipoobjetovinc": 'agencia',
					"_x_i_objetovinculo_idobjetovinc": $("[name='_1_u_agencia_idagencia']").val()
				},
				parcial: true
			});
		}
	});

	if ($("[name=_1_u_agencia_idagencia]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_agencia_idagencia]").val(),
			tipoObjeto: 'agencia',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});
	}

	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>