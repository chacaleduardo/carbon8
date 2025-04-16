<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/assinatura_controller.php");
require_once("controllers/fluxo_controller.php");
require_once("controllers/entregaepi_controller.php");
if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "entregaepi";
$pagvalcampos = array(
	"identregaepi" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from entregaepi where identregaepi = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if ($_1_u_entregaepi_idpessoa) {
	$func = (object) EntregaEpiController::buscaDadosColaborador($_1_u_entregaepi_idpessoa);
}

//selecionar todas as solmats disponíveis
$solmatdisponiveis = EntregaEpiController::buscaSolmatDisponivel($func->idempresa);
?>

<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<table>
				<td>
					<input id="identregaepi" name="_1_<?= $_acao ?>_entregaepi_identregaepi" type="hidden" value="<?= $_1_u_entregaepi_identregaepi ?>" readonly='readonly'>
				</td>
				<td>Colaborador</td>
				<? if ($_acao == 'i') { ?>
					<td class="col-xs-10">
						<input id="colaborador" name="_1_i_entregaepi_idpessoa" type="text" value="<?= $_1_u_entregaepi_idpessoa ?>">
						<input name="_1_i_entregaepi_idempresa" type="hidden" value="<?= $_GET['idempresa'] ?>">
					</td>
				<? } ?>
			</table>
		</div>
		<? if ($_acao != 'i') { ?>
			<div class="panel-body">
				<table class="col-xs-12">
					<tr>
						<td class="col-xs-4">
							<label for="validationCustom01" class="form-label">Nome</label>
							<input type="text" class="form-control" id="validationCustom01" value="<?= $func->nome ?>" readonly='readonly'>
						</td>
						<td class="col-xs-4">
							<label for="validationCustom02" class="form-label">CPF / CNPJ</label>
							<input type="text" class="form-control" id="validationCustom02" value="<?= $func->cpfcnpj ?>" readonly='readonly'>
						</td>
						<td class="col-xs-4">
							<label for="validationCustom02" class="form-label">ID</label>
							<input type="text" class="form-control" id="validationCustom02" value="<?= $func->idpessoa ?>" readonly='readonly'>
						</td>
					</tr>
					<tr>
						<td class="col-xs-4">
							<label for="validationCustom01" class="form-label">Empresa</label>
							<input type="text" class="form-control" id="validationCustom01" value="<?= $func->empresa ?>" readonly='readonly'>
						</td>
						<td class="col-xs-4">
							<label for="validationCustom02" class="form-label">Setor</label>
							<input type="text" class="form-control" id="validationCustom02" name="_1_u_entregaepi_setor" value="<?= $func->setor ?>" readonly='readonly'>
						</td>
						<td class="col-xs-4">
							<label for="validationCustom02" class="form-label">Função</label>
							<input type="text" class="form-control" id="validationCustom02" name="_1_u_entregaepi_cargo" value="<?= $func->cargo ?>" readonly='readonly'>
						</td>
					</tr>
					<tr>
						<td class="col-xs-4">
							<label for="validationCustom01" class="form-label">Data admissão</label>
							<input type="text" class="form-control" id="validationCustom01" value="<?= $func->contratacao ?>" readonly='readonly'>
						</td>
						<td class="col-xs-4">
							<label for="validationCustom02" class="form-label">Data demissão</label>
							<input type="text" class="form-control" id="validationCustom02" value="<?= $func->demissao ?>" readonly='readonly'>
						</td>
					</tr>
				</table>
				<input class="form-control" id="validationCustom02" name="_1_u_entregaepi_idempresa" value="<?= $func->idempresa ?>" type="hidden">
			</div>
		<? } ?>
	</div>
</div>

<div class="col-md-12 <?= $_acao == 'i' ? 'hidden' : '' ?>">
	<div class="panel panel-default">
		<div class="panel-heading">
			<table>
				<tr>
					<td>Selecionar EPI</td>
				</tr>
			</table>
		</div>
		<? if (!$row['idsolmat']) { ?>
			<div class="panel-body">
				<label class="form-label" type="hidden">Id Solicitação de Equipamento (Solmat)</label>
				<table>
					<tr>
						<td>
							<select id="idsolmatlist" class="compacto selectpicker" cbvalue placeholder="Selecione" multiple="multiple">
								<? foreach ($solmatdisponiveis as $r) { ?>
									<option data-tokens="<?= $r['idsolmat'] ?>" value="<?= $r['idsolmat'] ?>"><?= $r['idsolmat'] ?></option>
								<? } ?>
							</select>
							<button style="position: relative;bottom: -9px;" class="btn btn-xs btn-success" onclick="preenchesolmatitens()">Adicionar Solmat</button>
						</td>
					</tr>
				</table>
			</div>
		<? } ?>

		<div class="panel-body">
			<div id="divItens"></div>
		</div>

	</div>
</div>

<div class="col-md-12 <?= $row['idsolmat'] ? '' : 'hide' ?>">
	<div class="panel panel-default" style="font-size: small;">
		<div class="panel-heading">Declaração</div>
		<div class="panel-body" style="background-color:white;">
			<p class="bold">Declaro para todos os fins legais que: </p>
			<span class="bold">1°</span>- Recebi os Equipamentos de Proteção Individual constantes da lista abaixo, novos e em perfeitas condições de uso, respectivo treinamento quanto à necessidade da utilização dos mesmos, bem como da minha responsabilidade quanto ao seu uso conforme determinado na NR-01, da Portaria MTB 3214/78 e que estou ciente das obrigações descritas na NR-06, baixada pela Portaria MTB 3214/78, sub item 6.6.1, a saber:<br />
			a) usar o fornecido pela organização, observado o disposto no item 6.5.2;<br />
			b) utilizar apenas para a finalidade a que se destina;<br />
			c) responsabilizar-se pela limpeza, guarda e conservação;<br />
			d) comunicar à organização quando extraviado, danificado ou qualquer alteração que o torne impróprio para uso;<br />
			e) cumprir as determinações da organização sobre o uso adequado.<br />

			<span class="bold">2°</span>- Que estou ciente das disposições do Art. 462 e §1º da CLT, e autorizo o desconto salarial proporcional ao custo de reparação do dano que os EPIs aos meus cuidados venham apresentar e das disposições do Art 158 alínea "a" da CLT, e do item 1.8 da NR 1, em especial o item 1.8.1, de que constitui ato faltoso a recusa injustificada de usar o EPI fornecido pela empresa, incorrendo nas penas da Lei.<br />
			<span class="bold">3º</span>- Fico proibido de dar ou emprestar o equipamento que estiver sob minha responsabilidade, só podendo fazê-lo se receber ordem por escrito da pessoa autorizada para tal fim.<br />
			<span class="bold">4°</span>- Estando os equipamentos em minha posse, estarei sujeito a inspeções sem prévio aviso.<br />
			<span class="bold">5°- Declaro ainda ter ciência de que os EPI's descartáveis (Luvas cirúrgicas, Jalecos, Toucas e Máscaras cirúrgicas triplas) necessários ao desempenho das minhas atribuições também são colocados a minha disposição na unidade/setor no qual trabalho, em período integral. Tais EPI's serão substituídos conforme necessidade, sendo seu descarte obrigatório em local adequado para esse fim.</span><br />
		</div>
	</div>
</div>

<?
$lAss = AssinaturaController::buscarAssinatura($_1_u_entregaepi_identregaepi, 'entregaepi', 'base64');
$qtdAss = count($lAss);
if ($qtdAss < 1) {
	$cenviar = "";
	$climpar = "hide";
} else {
	$cenviar = "hide";
	$climpar = "";
}
?>
<div class="col-md-12" <?= $_1_u_entregaepi_status == 'ABERTO' ? 'style="display:none"' : '' ?>>
	<div class="panel panel-default">
		<div class="panel-heading">Rubrica de Recebimento</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-6 <?= $cenviar ?>">
					<canvas id="canvas" width="500" height="200" style="border:1px solid #00000047; background-color: white;"></canvas><br>
					<div style="margin:20px 0">
						<button id="saveButton" class="btn btn-primary btn-xs" type="submit" style="margin-right: 20px;">
							<i class="fa fa-check"></i>
							Enviar
						</button>
						<button id="clearButton" class="btn btn-xs " type="button" style="background:#da8610; color:#ffffff;">
							<i class="fa fa-eraser"></i>
							Limpar
						</button>
					</div>
				</div>
				<div class="col-md-6 <?= $climpar ?>">
					<div class="row">
						<div class="col-md-12" style="padding-left: 50px;">
							<img id="imgassinatura" style="margin: auto;background-color: hsl(0, 0%, 90%); width: 250px;" src="<?= $lAss['assinatura'] ?>" value="<?= $lAss['idassinatura'] ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 bold" style="padding-left: 50px;">
							Assinado em:<?= dmahms($lAss['criadoem']) ?>
						</div>
					</div>
					<div class="row" style="display:none">
						<div class="col-md-12 bold" style="padding-left: 50px;">
							<br>
							<button <?= $disablebt ?> id="trashButton" class="btn btn-danger btn-xs " type="button">
								<i class="fa fa-trash-o "></i>
								Retirar
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<?
if ($_acao == 'u') {
	$_idModuloParaAssinatura = $_1_u_entregaepi_identregaepi;
	$tabaud = "entregaepi";
	require 'viewCriadoAlterado.php';
}

?>
<script>
	$('.selectpicker').selectpicker('render');

	//--- bloco de rubricas

	const canvas = document.getElementById('canvas');
	const context = canvas.getContext('2d');
	let isDrawing = false;

	// Função para começar o desenho
	function startDrawing(e) {
		isDrawing = true;
		context.beginPath();
		const {
			x,
			y
		} = getCoordinates(e);
		context.moveTo(x, y);
	}

	// Função para continuar o desenho
	function continueDrawing(e) {
		if (isDrawing) {
			const {
				x,
				y
			} = getCoordinates(e);
			context.lineTo(x, y);
			context.stroke();
		}
	}

	// Função para finalizar o desenho
	function endDrawing() {
		isDrawing = false;
	}

	// Função para obter as coordenadas do evento (mouse ou toque)
	function getCoordinates(e) {
		let clientX, clientY;
		if (e.touches && e.touches.length > 0) {
			clientX = e.touches[0].clientX;
			clientY = e.touches[0].clientY;
		} else {
			clientX = e.clientX;
			clientY = e.clientY;
		}
		const rect = canvas.getBoundingClientRect();
		return {
			x: clientX - rect.left,
			y: clientY - rect.top
		};
	}

	// Adicionando os listeners de eventos para mouse e toque
	canvas.addEventListener('mousedown', startDrawing);
	canvas.addEventListener('mousemove', continueDrawing);
	canvas.addEventListener('mouseup', endDrawing);
	canvas.addEventListener('mouseout', endDrawing);

	canvas.addEventListener('touchstart', startDrawing);
	canvas.addEventListener('touchmove', continueDrawing);
	canvas.addEventListener('touchend', endDrawing);

	// Desabilitar a seleção de texto na área do canvas
	canvas.addEventListener('touchstart', (e) => {
		e.preventDefault();
	});



	document.getElementById('clearButton').addEventListener('click', () => {
		context.clearRect(0, 0, canvas.width, canvas.height);

	});

	document.getElementById('trashButton').addEventListener('click', () => {
		context.clearRect(0, 0, canvas.width, canvas.height);


		CB.post({
			objetos: {
				"_1_u_assinatura_idassinatura": $('#imgassinatura').attr('value'),
				"_1_u_assinatura_status": 'INATIVO'
			},
			parcial: true
		});
	});

	document.getElementById('saveButton').addEventListener('click', () => {
		const signatureData = canvas.toDataURL();
		// Enviar signatureData para o PHP para salvar no banco de dados
		console.log(signatureData);
		$("#imgassinatura").removeClass("hide");
		$("#clearButton").removeClass("hide");
		$('#imgassinatura').attr('src', signatureData);

		CB.post({
			objetos: {
				"_1_i_assinatura_idobjeto": $('#identregaepi').val(),
				"_1_i_assinatura_tipoobjeto": 'entregaepi',
				"_1_i_assinatura_status": 'ASSINADO',
				"_1_i_assinatura_assinatura": signatureData,
				"_1_i_assinatura_tipoassinatura": 'base64'
			},
			parcial: true
		});


	});

	function preenchesolmatitens(v) {
		$("#divItens").html("Procurando....");

		if (v) {
			$.ajax({
				type: "get",
				url: "ajax/buscaitementregaepi.php",
				data: {
					idsolmat: v,
					identregaepi: $("#identregaepi").val(),
					view: 'true'
				},

				success: function(data) {
					$("#divItens").html(data);
					$('#idsolmat').prop('readonly', true);
				},

				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);

				}
			})
		} else {
			$.ajax({
				type: "get",
				url: "ajax/buscaitementregaepi.php",
				data: {
					idsolmat: $("#idsolmatlist").val(),
					identregaepi: $("#identregaepi").val()
				},

				success: function(data) {
					$("#divItens").html(data);
					$('#tablelist').html('');
				},

				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);

				}
			})
		}
	}

	$("#colaborador").autocomplete({
		source: <?= getJfuncionario('CRIADOR'); ?>,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.label;

				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, funcionario) {
			/* CB.post({
			    objetos: {
			        "_x_i_entregaepi_identregaepi":
			        ,"_x_i_fluxoobjeto_idobjeto": funcionario.item.value
			        ,"_x_i_fluxoobjeto_tipoobjeto": 'pessoa'
			        ,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
			    }
			    ,parcial: true
			}); */
		}
	});

	<?
	function getJfuncionario($tipo = NULL)
	{
		global $JSON, $_1_u_fluxo_idfluxo;

		if (!empty($tipo)) {
			$condicao = " AND tipo = '$tipo'";
		}

		$sql = "SELECT DISTINCT a.idpessoa, ifnull(a.nomecurto,a.nome) as nomecurto,e.sigla
                   FROM pessoa a JOIN empresa e on(e.idempresa = a.idempresa) JOIN objempresa oe on oe.idobjeto = a.idpessoa 
                  WHERE a.status in('ATIVO', 'PENDENTE', 'AFASTADO')
                    AND (a.idtipopessoa in (1,8))
                    AND NOT a.usuario is null
                    AND NOT EXISTS(SELECT 1
						              FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
						             WHERE ms.idfluxo = '" . $_1_u_fluxo_idfluxo . "'
						               AND r.tipoobjeto = 'pessoa'
						               AND a.idpessoa = r.idobjeto $condicao)
									   and a.idempresa = " . cb::idempresa() . " 
					ORDER BY nomecurto asc";

		$rts = d::b()->query($sql) or die("oioi: " . mysql_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"] = $r["idpessoa"];
			$arrtmp[$i]["label"] = $r['sigla'] . ' - ' . $r["nomecurto"];
			$i++;
		}

		return $JSON->encode($arrtmp);
	}


	if ($row['idsolmat']) { ?>
		$(function() {
			preenchesolmatitens('<?= $row['idsolmat'] ?>');
		});
	<? } ?>
	<? if ($lAss['assinatura'] && $_1_u_entregaepi_status == "PENDENTE") { ?>
		$(function() {
			CB.post({
				objetos: '_2_u_entregaepi_status=RECEBIDO&_2_u_entregaepi_identregaepi=' + <?= $_1_u_entregaepi_identregaepi ?> + '&_2_u_entregaepi_idfluxostatus=' + <?= FluxoController::getIdFluxoStatus('entregaepi', 'RECEBIDO') ?>
			});
		})
	<?	} ?>
</script>
<script>
	function validateInput() {
		let inputElement = $('#numero');
		let maxValue = parseInt(inputElement.attr('max'));
		if (parseInt(inputElement.val()) > maxValue) {
			alert("O valor digitado é maior que o permitido (" + maxValue + ").");
			inputElement.val(maxValue);
		}
	}
</script>