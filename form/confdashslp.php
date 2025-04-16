<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/_lp_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "_lp";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idlp" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "SELECT 
            p.*, e.empresa, e.corsistema
            FROM
            carbonnovo._lp p
                JOIN
            empresa e ON (e.idempresa = p.idempresa)
            WHERE
            p.idlp = '#pkid'
			limit 1";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");
$re = _LpController::buscarSiglaECorsistemaDaEmpresa($_1_u__lp_idempresa);

$_sigla = $re['sigla'];
$_corsistema = $re['corsistema'];
?>
<link rel="stylesheet" href="<?= "form/css/_lp_css.css" ?>" />
<div class='' id="dashcards_disponiveis_<?= $_1_u__lp_idlp ?>">
	<div class="panel-heading col-md-12 dash_<?= $_1_u__lp_idlp ?>">
		<i id="popMenuDash_<?= $_1_u__lp_idlp ?>" class="fa fa-gear fa-2x pointer p10"></i>
		<button id="order_grupo_<?= $_1_u__lp_idlp ?>" class="btn btn-success btn-xs hidden" onclick="ordenarDashGrupo(<?= $_1_u__lp_idlp ?>);"><i class="fa fa-check" style="margin-right: 0;"></i>Finalizar</button>
		<i id="_salvarJsondashboardconfIndicator_<?= $_1_u__lp_idlp ?>" class="fa fa-exclamation-triangle laranja hidden" title="As configurações desse dashboard não foram salvas"></i>
	</div>
	<div class="panel-body dash_<?= $_1_u__lp_idlp ?>" id="dashcards-disponiveis-<?= $_1_u__lp_idlp ?>" style="padding-top: 10px !important">

		<? $rs = _LpController::buscarDashboards($_1_u__lp_idlp, $_1_u__lp_idempresa);
		foreach ($rs as $k => $rw) { ?>
			<div class="col-md-1 mb-4 dashcard-disponivel" iddashcard='<?= $rw['iddashcard'] ?>' iddashpanel='<?= $rw['iddashpanel'] ?>'>
				<div class="cardpanel" iddashcard='<?= $rw['iddashcard'] ?>'>
					<?= $rw['panel_title'] ?>
				</div>
				<div class="card border-left-<?= $rw['card_border_color'] ?> shadow h-100 py-2 bg-<?= $rw['card_bg_class'] ?>" iddashcard='<?= $rw['iddashcard'] ?>' titulo="<?= $rw['card_title'] ?>" subtitulo="<?= $rw['card_title_sub'] ?>" cor="<?= $rw['card_border_color'] ?>" style="border-radius:8px;">
					<span onclick="CB.post({objetos:'_1x_d__lpobjeto_idlpobjeto=<?= $rw['idlpobjeto'] ?>'})" class="bg-danger badge badgedash pointer" title="Excluir">X</span>
					<div class="card-body">
						<div class="row no-gutters align-items-center">
							<div class="col-md-12">
								<div class="text-xs text-uppercase mb-1" style="color:#888;text-align:left;padding:0px 8px"><?= $rw['iddashcard'] . ' - ' . $rw['card_title'] ?></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="h7 mb-0 font-weight_bold text_gray-800 titulo-<?= $rw['card_color'] ?>" style="text-align:center;font-weight:bolder;"><span class='card_value'></span></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div style="text-align:left;font-weight:bolder;"><span id='card_title_sub' iddashcard='<?= $rw['iddashcard'] ?>' class="bg-<?= $rw['card_border_color'] ?>" card_titlesub><?= $rw['card_title_sub'] ?></span></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<? } ?>

	</div>
	<div class="panel-body dash_<?= $_1_u__lp_idlp ?> dash_conf_<?= $_1_u__lp_idlp ?>">
		<input type="hidden" name="_jsondashboardconf<?= $_1_u__lp_idlp ?>_u__lp_idlp" value="<?= $_1_u__lp_idlp ?>">
		<input type="hidden" id="_salvarJsondashboardconf_<?= $_1_u__lp_idlp ?>" idlp="<?= $_1_u__lp_idlp ?>">
		<textarea class="hidden" name="_jsondashboardconf<?= $_1_u__lp_idlp ?>_u__lp_jsondashboardconf" id="jsondashboardconf_<?= $_1_u__lp_idlp ?>">
            <? if (!empty($_1_u__lp_jsondashboardconf)) {
				echo $_1_u__lp_jsondashboardconf;
				$jDash = $_1_u__lp_jsondashboardconf;
			} else {
				echo '{"dashgrupo":[]}';
				$jDash = 0;
			} ?>
        </textarea>

		<div id="dashgrupo-conf-<?= $_1_u__lp_idlp ?>"></div>
		<div class="input-group" style="width: 20%;margin-top: 10px;">
			<input type="text" id="novo-dashgrupo-input-<?= $_1_u__lp_idlp ?>" cor-sistema="<?= $_1_u__lp_corsistema ?>" placeholder="Novo Grupo" onfocus="addEnterEventDash(this,<?= $_1_u__lp_idlp ?>)" onfocusout="removeEnterEventDash(this,<?= $_1_u__lp_idlp ?>)">
			<span class="input-group-addon pointer" onclick="novoDashGrupo(<?= $_1_u__lp_idlp ?>,$(`#novo-dashgrupo-input-` + <?= $_1_u__lp_idlp ?>))">
				<i class="fa fa-check verde"></i>
			</span>
		</div>

	</div>
</div>
<div class="row ">
	<div class="col-md-12 dashdisp-<?= $_1_u__lp_idlp ?>">
		<div class="panel panel-default">
			<div class="panel-heading">
				Dashboards Disponíveis
			</div>
			<div class="panel-body" id="dashdisp-<?= $_1_u__lp_idlp ?>">
				<?
				$resm =  _LpController::buscarDashboardsDisponiveis($_1_u__lp_idlp, $_1_u__lp_idempresa);
				$grupo = false;
				$painel = false;
				foreach ($resm as $k => $rowm) {
					// Verifica se o mudou o grupo de uma linha para a outra
					// Caso sim, desenha um cabeçalho novo.
					if ($grupo != $rowm['iddashgrupo']) {

						// Caso seja o primeiro grupo, não fechar as DIVs do grupo
						if ($grupo != false) {
							$painel = false;
				?>
			</div>
		</div>
	</div>
</div>
<? }

						$grupo = $rowm['iddashgrupo'];
?>

<div class="panel panel-primary" grupo-pai="<?=$rowm['iddashgrupo']?>" style="border-left-color:<?= $rowm['corsistema'] ?>; border-left-width:3px; margin-bottom:30px;">
	<div class="panel-body" style="background:#F3F3F3; padding:30px; border-radius:4px;">
		<h3 class="text-on-pannel text-primary" style="background: <?= $rowm['corsistema'] ?>;color:#F3F3F3 !important;">
			<strong class="text-uppercase"><?= $rowm['grupo_rotulo'] ?></strong>
			<input type="hidden" cor-sistema="<?= $rowm['corsistema'] ?>" id="grupo-<?=$rowm['iddashgrupo']?>-<?=$_1_u__lp_idlp?>-conf" value="<?= $rowm['grupo_rotulo'] ?> ">
			<i class="fa fa-plus pointer" onclick="addGrupoJson(<?=$_1_u__lp_idlp?>,<?= $rowm['iddashgrupo'] ?>)"></i>
		</h3>
		<? }

					// Desenha cada Painel do grupo
					if ($painel != $rowm['iddashpanel']) {
						if ($painel != false) { ?>
	</div>
</div>
<? }

						$painel = $rowm['iddashpanel'];
?>

<div class="col-md-12 painel">
	<h3 class="text-on-pannel text-primary">
		<input type="hidden" panel-pai="<?=$rowm['iddashpanel']?>" value="<?=$rowm['panel_title']?>" id="grupo-<?=$rowm['iddashgrupo']?>-<?=$_1_u__lp_idlp?>-panel-<?=$rowm['iddashpanel']?>">
		<strong class="text-uppercase"><?= $rowm['panel_title'] ?></strong>
	</h3>
	<div filho-panel="<?=$rowm['iddashpanel']?>" class="col-md-12 painel-cards">
	<? }

					// Desenha cada Card do grupo
	?>

	<div class="col-md-1 mb-4 draggable_<?= $rowm['iddashcard'] ?> pointer <?= $class ?>" titulo="<?=$rowm['card_title']?>" titulopersonalizado="" subtitulo="<?= $rowm['card_title_sub'] ?>" cor="<?= $rowm['card_border_color'] ?>" onclick="relacionaDashboard(this,<?= $_1_u__lp_idlp ?>, <?= $painel ?>)" iddashcard='<?= $rowm['iddashcard'] ?>'>
		<div class="card border-left-<?= $rowm['card_border_color'] ?> shadow h-100 py-2 bg-<?= $rowm['card_bg_class'] ?>" titulo="<?= $rowm['card_title'] ?>" subtitulo="<?= $rowm['card_title_sub'] ?>" cor="<?= $rowm['card_border_color'] ?>" iddashcard='<?= $rowm['iddashcard'] ?>' style="border-radius:8px;">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col-md-12">
						<div class="text-xs text-uppercase mb-1" style="color:#888;text-align:left;padding:0px 8px"><?=$rowm['sigla']. ' - ' .$rowm['iddashcard'] . ' - ' . $rowm['card_title'] ?></div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="h7 mb-0 font-weight_bold text_gray-800 titulo-<?= $rowm['card_color'] ?>" style="text-align:center;font-weight:bolder;"><span class='card_value'></span></div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div style="text-align:left;font-weight:bolder;"><span id='card_title_sub' iddashcard="<?= $rowm['iddashcard'] ?>" class="bg-<?= $rowm['card_border_color'] ?>" card_titlesub><?= $rowm['card_title_sub'] ?></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
<? } ?>
	</div>
</div>
<script>
	var idlp = <?= $_1_u__lp_idlp ?>;
	$(`#popMenuDash_${idlp}`).webuiPopover({
		content: `
        <ul class="popul">
            <li class="pointer" onclick="ordenarDashGrupo(${idlp});"><i class="fa fa-arrows"></i> Ordenar Grupos</li>
            <li class="pointer" onclick="salvarDashConf(${idlp});"><i class="fa fa-floppy-o"></i> Salvar</li>
        </ul>
        `
	});
	(_ => {
		let jdash = getJDashBoardConf(idlp);
		if (jdash != 0) {
			for (let grupo of jdash["dashgrupo"]) {
				construirDashGrupo('w', grupo["id"], idlp, jdash);
				for (let panel of grupo["dashpanel"]) {
					construirDashPanel('w', panel["id"], grupo["id"], idlp, jdash);
					for (let card of panel["dashcard"]) {
						$(`.dashcard-disponivel[iddashcard="${card.iddashcard}"]`).hide();
						if ($(`[iddashcard="${card.iddashcard}"] `).length > 0) {
							card.titulo = (card.titulo == '') ? $(`[iddashcard="${card.iddashcard}"] .card-body .text-xs.text-uppercase.mb-1`).text() : card.titulo;
							card.subtitulo = (card.subtitulo == '') ? $(`[iddashcard="${card.iddashcard}"] #card_title_sub`).text() : card.subtitulo;
						} else {
							card.titulo = '';
							card.subtitulo = '';
						}

						construirDashCard(card, panel["id"], grupo["id"], idlp);
						$(`[iddashcard=${card.iddashcard}].hidden`).find('#card_title_sub').addClass("pointer")
						$(`[iddashcard=${card.iddashcard}].hidden`).find('#card_title_sub').on('click', function(e) {
							janelamodal("?_modulo=dashcard&_acao=u&iddashcard=" + card.iddashcard);
						});

						$(`[iddashcard=${card.iddashcard}].hidden`).removeClass('hidden')
					}
				}
			}
			$(`.dashcard-disponivel:visible`).find('.cardpanel').addClass("pointer");
			$(".dashcard-disponivel").find('.cardpanel').on('click', function(e) {
				janelamodal("?_modulo=dashcard&_acao=u&iddashcard=" + $(e.target).attr('iddashcard'));
			});
		}
	})();

	function getJDashBoardConf(idLp) {
		return JSON.parse($("#jsondashboardconf_" + idLp).val());
	}

	function relacionaDashboard(inO,inidlp, inIdDashPanel){
		let $inO = $(inO);

		if(!$inO.hasClass('added-card') && $('.drag-drop-enable').length == 0){
			$inO.addClass('added-card');

			let iddashcard = $inO.attr('iddashcard');

			let content = `
				<input type="hidden" name="_novodashcard${iddashcard}_i__lpobjeto_idlp" value="${inidlp}"/>
				<input type="hidden" name="_novodashcard${iddashcard}_i__lpobjeto_idobjeto" value="${iddashcard}"/>
				<input type="hidden" name="_novodashcard${iddashcard}_i__lpobjeto_tipoobjeto" value="dashboard"/>
				<div class="col-md-1 mb-4 dashcard-disponivel" iddashpanel="${inIdDashPanel}">
					<div class="cardpanel">
						${$inO.parent().siblings('h3').text()}                                   
					</div>
					${$inO.html()}
				</div>
			`;

			let dashPanels = $(`.dashcard-disponivel[iddashpanel=${inIdDashPanel}]`);
			if(dashPanels.length > 0){
				$(content).insertAfter($(dashPanels[dashPanels.length - 1]));
			}else{
				$("#dashcards-disponiveis-"+inidlp).append(content);
			}

			$inO.remove();
			jsonDashConfAlterado(inidlp);
		}
	}
	function editarCard(idPanel, idGrupo, idlp) {
		$(`i.fa-ellipsis-v`).addClass('hidden');
		$(`#pop-conf-${idPanel}-${idGrupo}-${idlp}`).webuiPopover('hide');
		$('.input-group').addClass('hidden');
		$(`#rot-conf-${idPanel}-${idGrupo}-${idlp} :first-child`).first().append(`<button id="button-conf-${idPanel}-${idGrupo}-${idlp}" class="btn btn-success btn-xs" onclick="voltaCard(${idPanel},${idGrupo},${idlp})" style="margin-left: 20px;" ><i class="fa fa-check"></i> Finalizar</button>`)
		$(`#rot-conf-${idPanel}-${idGrupo}-${idlp} :first-child`).first().append(`<button id="button-restaurar-${idPanel}-${idGrupo}-${idlp}" class="btn btn-primary btn-xs" onclick="restauraCard(${idPanel},${idGrupo},${idlp})" style="margin-left: 20px;" ><i class="fa fa-arrow-circle-left"></i> Restaurar Indicadores</button>`)

		$("#dashpanel-conf-" + idPanel + "-" + idGrupo + "-" + idlp).find("[iddashcard]").each(function(i, e) {
			let beforeChange, $card, $oInput;
			$card = $(e);
			beforeChange = $(e).find('.text-xs.mb-1').html();
			if (beforeChange != '') {
				$oInput = $(`
				<div style="display: inline-flex;">
					<input type="text" id="edit_card_${i}_${idlp}" style="width:70%;" value="${beforeChange}">
					<div class="editButton">
						<i class="fa fa-times vermelho" id="edit_card_cancel_${i}_${idlp}"></i>
					</div>
					<div class="editButton">
						<i class="fa fa-check verde" id="edit_card_confirm_${i}_${idlp}"></i>
					</div>
				</div>
			`);

				$oInput.find(`#edit_card_cancel_${i}_${idlp}`).on('click', function(event) {
					$(e).find('.text-xs.mb-1').html(beforeChange);
				});
				$oInput.find(`#edit_card_confirm_${i}_${idlp}`).on('click', $(e), function(event) {
					$str = $(`#edit_card_${i}_${idlp}`).val();
					if ($str != beforeChange) {
						$(e).attr("titulopersonalizado", $str);
						$(e).find('.text-xs.mb-1').html($str);
						jsonDashConfAlterado(idlp, 'show');
					} else {
						$(e).find('.text-xs.mb-1').html(beforeChange);
					}
				});
				$(e).find('.text-xs.mb-1').html($oInput);
			}

		});
	}

	function voltaCard(idPanel, idGrupo, idlp) {
		$("#dashpanel-conf-" + idPanel + "-" + idGrupo + "-" + idlp).find("[iddashcard]").each(function(i, e) {
			if ($(e).find(`#edit_card_${i}_${idlp}`).val()) {
				if (($(e).attr("titulo") != $(e).find(`#edit_card_${i}_${idlp}`).val()) || ($(e).attr("titulopersonalizado") != $(e).find(`#edit_card_${i}_${idlp}`).val())) {
					$(e).attr("titulopersonalizado", $(e).find(`#edit_card_${i}_${idlp}`).val());
					jsonDashConfAlterado(idlp, 'show');
				}
				$(e).find('.text-xs.mb-1').html($(e).find(`#edit_card_${i}_${idlp}`).val());
			}
		});
		$(`i.fa-ellipsis-v`).removeClass('hidden');
		$('.input-group').removeClass('hidden');
		$(`#button-conf-${idPanel}-${idGrupo}-${idlp}`).remove();
		$(`#button-restaurar-${idPanel}-${idGrupo}-${idlp}`).remove();
	}

	function restauraCard(idPanel, idGrupo, idlp) {
		$("#dashpanel-conf-" + idPanel + "-" + idGrupo + "-" + idlp).find("[iddashcard]").each(async function(i, e) {
			let iddash = $(e).attr('iddashcard');
			await $.ajax({
				type: "get",
				url: "ajax/getCardName.php",
				data: {
					iddashcard: iddash
				},
				success: function(data) {
					$(e).attr('titulo', data);
					$(e).attr('titulopersonalizado', '');
					$(e).find('.text-xs.mb-1').html(data);
				},
				error: function(objxmlreq) {
					console.error('Erro:<br>' + objxmlreq.status);
				}
			});
			jsonDashConfAlterado(idlp, 'show');
		});
		$(`i.fa-ellipsis-v`).removeClass('hidden');
		$('.input-group').removeClass('hidden');
		$(`#button-conf-${idPanel}-${idGrupo}-${idlp}`).remove();
		$(`#button-restaurar-${idPanel}-${idGrupo}-${idlp}`).remove();
	}

	function popMenuDashConfGrupo(idLp, idGrupo, descr) {
		let popContent = `
		<ul class="popul">
			<li class="pointer" onclick="editarDashGrupo('${descr}', ${idLp}, ${idGrupo})"><i class="fa fa-pencil"></i> Editar Grupo</li>
			<li class="pointer" onclick="ordenarDashPanel(${idLp}, ${idGrupo})"><i class="fa fa-arrows"></i> Ordenar Painéis</li>
			<li class="pointer" onclick="removerDash(${idLp}, ${idGrupo})"><i class="fa fa-trash"></i> Excluir Grupo</li>
		</ul>
	`;

		return popContent;
	}

	function popMenuDashConfPanel(idLp, idGrupo, idPanel, descr) {
		let popContent = `
		<ul class="popul">
			<li class="pointer" onclick="editarDashPanel('${descr}', ${idLp}, ${idGrupo}, ${idPanel})"><i class="fa fa-pencil"></i> Editar Painel</li>
			<li class="pointer" onclick="addCardDashPanel(${idPanel}, ${idGrupo}, ${idLp});"><i class="fa fa-plus"></i> Adicionar Indicadores</li>
			<li class="pointer" onclick="editarCard(${idPanel},${idGrupo}, ${idLp});"><i class="fa fa-edit"></i> Editar Indicadores</li>
			<li class="pointer" onclick="removerDash(${idLp}, ${idGrupo}, ${idPanel})"><i class="fa fa-trash"></i> Excluir Painel</li>
		</ul>
	`;

		return popContent;
	}

	CB.on('prePost', function(inParam) {
		if ($("input.json-changed").length > 0) {
			if (confirm("Existem configurações da Lista de Permissão não salvos. Deseja salvá-las?")) {
				$("input.json-changed").each(function(i, o) {
					let idlp = $(o).attr('idlp');
					salvarDashConf(idlp);
				});
				if (inParam && inParam.parcial && inParam.parcial == true) {
					inParam.parcial = undefined;
				}
			}
		}
		return true;
	});

	function addGrupoJson(idlp,iddashgrupo){
		var input = $(`#grupo-${iddashgrupo}-${idlp}-conf`);
		var posicaogrupo = novoDashGrupo(idlp,input);
		// let jDashConf = getJDashBoardConf(idLp);
		$(`[grupo-pai=${iddashgrupo}]`).hide()
		.find(`input[id^=grupo-${iddashgrupo}-${idlp}-panel-]`)
		.each((i,e)=>{
			posicaopainel = novoDashPanel(posicaogrupo,idlp,$(e));
			iddashpanel = $(e).attr('panel-pai');
			$(`[filho-panel=${iddashpanel}]`).find('.col-md-1[iddashcard]').each((i1,e2)=>{

				$("#cbModuloForm").append(`<input type="hidden" name="_novodashcard${$(e2).attr('iddashcard')}_i__lpobjeto_idlp" value="${idlp}"/>
				<input type="hidden" name="_novodashcard${$(e2).attr('iddashcard')}_i__lpobjeto_idobjeto" value="${$(e2).attr('iddashcard')}"/>
				<input type="hidden" name="_novodashcard${$(e2).attr('iddashcard')}_i__lpobjeto_tipoobjeto" value="dashboard"/>`)
				if(i1 % 12 == 0 && i1!= 0)
					adicionarLinhaDashPanel(posicaopainel,posicaogrupo,idlp);

				$($(`#dashpanel-conf-${posicaopainel}-${posicaogrupo}-${idlp}`).children()[i1]).append($(e2).clone().removeClass('drag-enable').removeAttr('onclick').removeClass('col-md-1'))
				// construirDashCard(card, panel["id"], grupo["id"], idlp)
				enableDragDrop(posicaopainel,posicaogrupo,idlp)
			});
		});
		jsonDashConfAlterado(idlp, 'show')
	}

	function salvarDashConf(idLp) {debugger
		$(`#popMenuDash_${idLp}`).webuiPopover('hide');

		let jDash = getJDashBoardConf(idLp);
		let temp;
		let ordem;

		for (let i in jDash["dashgrupo"]) {
			if (jDash["dashgrupo"][i]) {
				for (let j in jDash["dashgrupo"][i]["dashpanel"]) {
					if (jDash["dashgrupo"][i]["dashpanel"][j]) {
						let idGrupo = jDash["dashgrupo"][i]["id"];
						let idPanel = jDash["dashgrupo"][i]["dashpanel"][j]["id"];

						jDash["dashgrupo"][i]["dashpanel"][j]["dashcard"] = [];
						jDash["dashgrupo"][i]["dashpanel"][j]["ymax"] = $("#dashpanel-conf-" + idPanel + "-" + idGrupo + "-" + idLp).attr('y-max');

						$("#dashpanel-conf-" + idPanel + "-" + idGrupo + "-" + idLp + " .card-space").children().each(function(k, o) {
							let $o = $(o);

							jDash["dashgrupo"][i]["dashpanel"][j]["dashcard"].push({
								iddashcard: $o.attr('iddashcard'),
								x: $o.parent().attr('x'),
								y: $o.parent().attr('y'),
								cor: $o.attr('cor'),
								titulo: $o.attr('titulo'),
								titulopersonalizado: $o.attr('titulopersonalizado') || '',
								subtitulo: $o.attr('subtitulo'),
							});
						});

						ordem = parseInt($("#div-conf-" + idPanel + "-" + idGrupo + "-" + idLp).attr('ordem'));

						if (jDash["dashgrupo"][i]["dashpanel"][j]["ordem"] != ordem) {
							jDash["dashgrupo"][i]["dashpanel"][j]["ordem"] = ordem;

							if (jDash["dashgrupo"][i]["dashpanel"][ordem]) {
								jDash["dashgrupo"][i]["dashpanel"][ordem]["ordem"] = j;
							}

							temp = jDash["dashgrupo"][i]["dashpanel"][ordem];
							jDash["dashgrupo"][i]["dashpanel"][ordem] = jDash["dashgrupo"][i]["dashpanel"][j];
							jDash["dashgrupo"][i]["dashpanel"][j] = temp;
						}
					}
				}

				ordem = $("#div-conf-" + jDash["dashgrupo"][i]["id"] + "-" + idLp).attr('ordem');
				if (jDash["dashgrupo"][i]["ordem"] != ordem) {
					jDash["dashgrupo"][i]["ordem"] = ordem;

					if (jDash["dashgrupo"][ordem]) {
						jDash["dashgrupo"][ordem]["ordem"] = i;
					}


					temp = jDash["dashgrupo"][ordem];
					jDash["dashgrupo"][ordem] = jDash["dashgrupo"][i];
					jDash["dashgrupo"][i] = temp;
				}
			}
		}

		jDash["dashgrupo"] = jDash["dashgrupo"].filter(n => n);
		for (let i in jDash["dashgrupo"]) {
			jDash["dashgrupo"][i]["id"] = i;
			jDash["dashgrupo"][i]["dashpanel"] = jDash["dashgrupo"][i]["dashpanel"].filter(n => n);
			for (let j in jDash["dashgrupo"][i]["dashpanel"]) {
				jDash["dashgrupo"][i]["dashpanel"][j]["id"] = j;
			}
		}

		setJDashBoardConf(idLp, jDash);
		jsonDashConfAlterado(idLp, 'hide')
	}

	function editarDashGrupo(descr, idLp, idGrupo) {
		let vthis = $(`#rot-conf-${idGrupo}-${idLp}`);
		let beforeChange = vthis.html();

		$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover('hide');

		let $oInput = $(`
		<div style="display: inline-flex;">
			<input type="text" id="edit_conf_${idGrupo}_${idLp}" style="width:80%;" value="${descr}">
			<div class="editButton pointer"><i class="fa fa-times vermelho" id="edit_conf_cancel_${idGrupo}_${idLp}"></i></div>
			<div class="editButton pointer"><i class="fa fa-check verde" id="edit_conf_confirm_${idGrupo}_${idLp}"></i></div>
		</div>
	`);

		$oInput.find(`#edit_conf_cancel_${idGrupo}_${idLp}`).on('click', function() {
			$(vthis).html(beforeChange);
			$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({
				content: popMenuDashConfGrupo(idLp, idGrupo, descr)
			});
		});

		$oInput.find(`#edit_conf_confirm_${idGrupo}_${idLp}`).on('click', function() {
			let str = $(`#edit_conf_${idGrupo}_${idLp}`).val();

			if (str.trim() == descr.trim()) {
				$(vthis).html(beforeChange);
				$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({
					content: popMenuDashConfGrupo(idLp, idGrupo, descr)
				});
				return;
			}

			if (str.trim() == "") {
				$(`#edit_conf_${idGrupo}_${idLp}`).addClass("alertaCbvalidacao");
				alertAtencao("Campo não pode ser vazio!");
				return;
			}

			let jDash = getJDashBoardConf(idLp);
			jDash["dashgrupo"][idGrupo]["rotulo"] = str.trim();
			setJDashBoardConf(idLp, jDash);
			jsonDashConfAlterado(idLp);

			$(vthis).html(beforeChange.replace(descr, str.trim()));
			$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({
				content: popMenuDashConfGrupo(idLp, idGrupo, str.trim())
			});
		});

		$(vthis).html($oInput);
		$(`#edit_conf_${idGrupo}_${idLp}`).select();
	}

	function editarDashPanel(descr, idLp, idGrupo, idPanel) {
		let vthis = $(`#rot-conf-${idPanel}-${idGrupo}-${idLp}`);
		let beforeChange = vthis.html();

		$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');

		let $oInput = $(`
		<div style="display: inline-flex;">
			<input type="text" id="edit_conf_${idPanel}_${idGrupo}_${idLp}" style="width:80%;" value="${descr}">
			<div class="editButton pointer"><i class="fa fa-times vermelho" id="edit_conf_cancel_${idPanel}_${idGrupo}_${idLp}"></i></div>
			<div class="editButton pointer"><i class="fa fa-check verde" id="edit_conf_confirm_${idPanel}_${idGrupo}_${idLp}"></i></div>
		</div>
	`);

		$oInput.find(`#edit_conf_cancel_${idPanel}_${idGrupo}_${idLp}`).on('click', function() {
			$(vthis).html(beforeChange);
			$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({
				content: popMenuDashConfPanel(idLp, idGrupo, idPanel, descr)
			});
		});

		$oInput.find(`#edit_conf_confirm_${idPanel}_${idGrupo}_${idLp}`).on('click', function() {
			let str = $(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).val();

			if (str.trim() == descr.trim()) {
				$(vthis).html(beforeChange);
				$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({
					content: popMenuDashConfPanel(idLp, idGrupo, idPanel, descr)
				});
				return;
			}

			if (str.trim() == "") {
				$(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).addClass("alertaCbvalidacao");
				alertAtencao("Campo não pode ser vazio!");
				return;
			}

			let jDash = getJDashBoardConf(idLp);
			jDash["dashgrupo"][idGrupo]["dashpanel"][idPanel]["rotulo"] = str.trim();
			setJDashBoardConf(idLp, jDash);
			jsonDashConfAlterado(idLp);

			$(vthis).html(beforeChange.replace(descr, str.trim()));
			$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({
				content: popMenuDashConfPanel(idLp, idGrupo, idPanel, str.trim())
			});
		});

		$(vthis).html($oInput);
		$(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).select();
	}

	function removerDash(idLp, idGrupo, idPanel) {
		let str = (idPanel) ? "painel" : "grupo";
		if (!confirm("Deseja realmente excluir este " + str + "?"));

		let jDash = getJDashBoardConf(idLp);
		if (idPanel != undefined) {
			delete jDash["dashgrupo"][idGrupo]["dashpanel"][idPanel];
			setJDashBoardConf(idLp, jDash);

			$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');
			$(`#div-conf-${idPanel}-${idGrupo}-${idLp}`).remove();

			$("#dashpanel-conf-" + idGrupo + "-" + idLp + ">div[ordem]").each(function(i, o) {
				let ordem = o.getAttribute('ordem');
				if (i != ordem) {
					$("#dashpanel-conf-" + idGrupo + "-" + idLp + ">div[ordem='" + i + "']").attr('ordem', ordem);
					o.setAttribute('ordem', i);
				}
			});
		} else {
			delete jDash["dashgrupo"][idGrupo];
			setJDashBoardConf(idLp, jDash);

			$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover('hide');
			$(`#div-conf-${idGrupo}-${idLp}`).remove();

			$("#dashgrupo-conf-" + idLp + ">div[ordem]").each(function(i, o) {
				let ordem = o.getAttribute('ordem');
				if (i != ordem) {
					$("#dashgrupo-conf-" + idLp + ">div[ordem='" + i + "']").attr('ordem', ordem);
					o.setAttribute('ordem', i);
				}
			});
		}
		jsonDashConfAlterado(idLp);
	}

	function novoDashGrupo(idLp,input) {
		let oInp = input;
		let str = oInp.val().trim();
		let corSistema = oInp.attr('cor-sistema') || "#666666";

		if (str != "") {
			let jDashConf = getJDashBoardConf(idLp);
			let posicao = jDashConf["dashgrupo"].push({
				rotulo: str,
				corsistema: corSistema,
				dashpanel: [],
			});

			jDashConf["dashgrupo"][posicao - 1]["id"] = posicao - 1;
			jDashConf["dashgrupo"][posicao - 1]["ordem"] = posicao - 1;

			setJDashBoardConf(idLp, jDashConf);

			oInp.val("");

			construirDashGrupo('w', posicao - 1, idLp, getJDashBoardConf(idLp));
			jsonDashConfAlterado(idLp);
			return (posicao - 1);
		}
	}

	function novoDashPanel(idGrupo, idLp,input) {
		let oInp = input;
		let str = oInp.val().trim();

		if (str != "") {
			let jDashConf = getJDashBoardConf(idLp);

			// aqui também poderia ser utilizado o .filter
			// mas como o ID do grupo é o mesmo que a posição dele no Array,
			// podemos acessá-lo dessa maneira

			let posicao = jDashConf["dashgrupo"][idGrupo]["dashpanel"].push({
				rotulo: str,
				ymax: 0,
				dashcard: [],
			});

			jDashConf["dashgrupo"][idGrupo]["dashpanel"][posicao - 1]["id"] = posicao - 1;
			jDashConf["dashgrupo"][idGrupo]["dashpanel"][posicao - 1]["ordem"] = posicao - 1;

			setJDashBoardConf(idLp, jDashConf);

			oInp.val("");

			construirDashPanel('w', posicao - 1, idGrupo, idLp, getJDashBoardConf(idLp));
			jsonDashConfAlterado(idLp);
			return (posicao - 1);
		}
	}

	function addEnterEventDash(vthis, idlp) {
		if (!vthis.onkeyup) {
			vthis.onkeyup = function(e) {
				if (e.keyCode == 13) {
					$(vthis).siblings().click();
				}
			};
		}
	}

	function removeEnterEventDash(vthis, idlp) {
		vthis.onkeyup = null;
	}

	function jsonDashConfAlterado(idLp, cmd = 'show') {
		if (cmd == 'show') {
			$("#_salvarJsondashboardconf_" + idLp).addClass('json-changed');
			$("#_salvarJsondashboardconfIndicator_" + idLp).removeClass('hidden');
		} else {
			$("#_salvarJsondashboardconf_" + idLp).removeClass('json-changed');
			$("#_salvarJsondashboardconfIndicator_" + idLp).addClass('hidden');
		}
	}

	function addCardDashPanel(idPanel, idGrupo, idLp) {

		if ($(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).hasClass('drag-drop-enable')) {
			$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('drag-drop-enable');

			$(`#div-conf-${idPanel}-${idGrupo}-${idLp} button`).addClass('hidden');
			$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('hidden');

			$(`#dashgrupo-conf-${idLp}`).children('.hidden').removeClass('hidden');
			$(`#dashpanel-conf-${idGrupo}-${idLp}`).children('.hidden').removeClass('hidden');

			$(`i.fa-ellipsis-v`).removeClass('hidden');
			$('.input-group').removeClass('hidden');

			disableDragDrop(idPanel, idGrupo, idLp);
		} else {
			$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('drag-drop-enable');

			$(`#div-conf-${idPanel}-${idGrupo}-${idLp} button.hidden`).removeClass('hidden');
			$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('hidden');

			$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');

			$(`#dashgrupo-conf-${idLp}`).children().not(`#div-conf-${idGrupo}-${idLp}`).addClass('hidden');
			$(`#dashpanel-conf-${idGrupo}-${idLp}`).children().not(`#div-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('hidden');

			$(`i.fa-ellipsis-v`).addClass('hidden');

			$('.input-group').addClass('hidden');

			enableDragDrop(idPanel, idGrupo, idLp);
		}

	}

	function disableDragDrop(idPanel, idGrupo, idLp) {
		$(`.column-${idPanel}-${idGrupo}-${idLp}.drop-enable`).droppable('destroy');
		$(`#dashcards-disponiveis-${idLp} div[iddashcard].card.drag-enable`).draggable('destroy');
		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).droppable('destroy');
		$(`.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`).draggable('destroy');

		$(`.column-${idPanel}-${idGrupo}-${idLp}`).removeClass("drop-enable");
		$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).removeClass("drag-enable");
		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('drop-enable');
	}

	function enableDragDrop(idPanel, idGrupo, idLp) {
		let options = {
			helper: function(event) {
				return $("<div class='ui-widget-header' style='width:70%'>" + event.currentTarget.innerHTML + "</div>");
			},
		}

		$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).not('.drag-enable').draggable(options);

		$(`.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`).draggable(options);

		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).not('drop-enable').droppable({
			accept: `.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`,
			drop: function(event, ui) {debugger
				$(`.dashcard-disponivel[iddashcard="${ui.draggable.attr('iddashcard')}"]`).show();
				ui.draggable.remove();
				jsonDashConfAlterado(idLp);
			}
		});

		$(`.column-${idPanel}-${idGrupo}-${idLp}`).not(".drop-enable").droppable({
			accept: `.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard], #dashcards-disponiveis-${idLp} div[iddashcard].card`,
			drop: function(event, ui) {debugger
				if ($(event.target).children().length == 0) {
					if (!(ui.draggable.parent().hasClass('card-space'))) {
						ui.draggable.parent().append(ui.draggable.clone().removeClass('drag-enable'));
						ui.draggable.parent().hide();
						enableDragDrop(idPanel, idGrupo, idLp);
					}
					$(event.target).append(ui.draggable);
					jsonDashConfAlterado(idLp);
				}
			}
		});

		$(`.column-${idPanel}-${idGrupo}-${idLp}`).addClass("drop-enable");
		$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).addClass("drag-enable");
		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('drop-enable');
	}

	function constroiCardSpaceByLvl(level, idPanel, idGrupo, idLp) {
		let x = 12;
		let col = 1;
		let content = "";

		for (let i = 0; i < x; i++) {
			content += `<div class="card-space column-${idPanel}-${idGrupo}-${idLp} p10 col-md-${col} card-space-height" x="${i}" y="${level}" style="height: 91px;"></div>`;
		}

		return content;
	}

	function adicionarLinhaDashPanel(idPanel, idGrupo, idLp) {
		let ymax = $(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max');
		$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).append(constroiCardSpaceByLvl(parseInt(ymax) + 1, idPanel, idGrupo, idLp));
		$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max', parseInt(ymax) + 1);
		enableDragDrop(idPanel, idGrupo, idLp);
		jsonDashConfAlterado(idLp);
	}

	function removerLinhaDashPanel(idPanel, idGrupo, idLp) {
		let ymax = $(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max');
		if (ymax > 0) {
			$(`.column-${idPanel}-${idGrupo}-${idLp}[y=${ymax}]`).remove();
			$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max', parseInt(ymax) - 1);
			jsonDashConfAlterado(idLp);
		}
	}

	function setJDashBoardConf(idLp, json) {
		$("#jsondashboardconf_" + idLp).val(JSON.stringify(json));
	}

	function ordenarDashGrupo(idLp){
	$(`#popMenuDash_${idLp}`).webuiPopover('hide');
	let grupos = $("#dashgrupo-conf-"+idLp);

	if(grupos.hasClass('moveActive')){
		$(`i.fa-ellipsis-v`).removeClass('hidden');
		$(`#order_grupo_${idLp}`).addClass('hidden');

		$("#dashgrupo-conf-"+idLp+">div[ordem]").removeClass('moveDash');

		$("#dashgrupo-conf-"+idLp+" .row").removeClass('hidden');
		$("#dashgrupo-conf-"+idLp+" .input-group").removeClass('hidden');
		$("#dashgrupo-conf-"+idLp).siblings(".input-group").removeClass('hidden');
		$("#dashgrupo-conf-"+idLp+" i.fa").removeClass('disabledbutton');

		grupos.removeClass('moveActive');

		grupos.sortable("destroy");

	}else{
		$(`i.fa-ellipsis-v`).addClass('hidden');
		$(`#order_grupo_${idLp}`).removeClass('hidden');

		$("#dashgrupo-conf-"+idLp+">div[ordem]").addClass('moveDash');

		$("#dashgrupo-conf-"+idLp+" .row").addClass('hidden');
		$("#dashgrupo-conf-"+idLp+" .input-group").addClass('hidden');
		$("#dashgrupo-conf-"+idLp).siblings(".input-group").addClass('hidden');
		$("#dashgrupo-conf-"+idLp+" i.fa").addClass('disabledbutton');

		grupos.addClass('moveActive');

		grupos.sortable({
			update: function( event, ui ) {

				$("#dashgrupo-conf-"+idLp+">div[ordem]").each(function(i, o){
					let ordem = o.getAttribute('ordem');
					if(i != ordem){
						$("#dashgrupo-conf-"+idLp+">div[ordem="+i+"]").attr('ordem', ordem);
						o.setAttribute('ordem', i);
					}
				});
				jsonDashConfAlterado(idLp);
			}
		});
	}
}

function ordenarDashPanel(idLp, idGrupo){
	$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover('hide');

	let painel = $("#dashpanel-conf-"+idGrupo+"-"+idLp);

	if(painel.hasClass('moveActive')){
		$(`i.fa-ellipsis-v`).removeClass('hidden');
		
		$(`#order_panel_${idGrupo}_${idLp}`).addClass('hidden');

		$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem]").removeClass('moveDash');

		$("#dashpanel-conf-"+idGrupo+"-"+idLp+" div[y-max]").removeClass('hidden');
		$("#dashpanel-conf-"+idGrupo+"-"+idLp).siblings(".input-group").removeClass('hidden');

		painel.removeClass('moveActive');

		painel.sortable("destroy");

	}else{
		$(`i.fa-ellipsis-v`).addClass('hidden');

		$(`#order_panel_${idGrupo}_${idLp}`).removeClass('hidden');

		$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem]").addClass('moveDash');

		$("#dashpanel-conf-"+idGrupo+"-"+idLp+" div[y-max]").addClass('hidden');
		$("#dashpanel-conf-"+idGrupo+"-"+idLp).siblings(".input-group").addClass('hidden');

		painel.addClass('moveActive');

		painel.sortable({
			update: function( event, ui ) {

				$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem]").each(function(i, o){
					let ordem = parseInt(o.getAttribute('ordem'));
					if(i != ordem){
						$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem='"+i+"']").attr('ordem', ordem);
						o.setAttribute('ordem', i);
					}
				});
				jsonDashConfAlterado(idLp);
			}
		});
	}
}


//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>