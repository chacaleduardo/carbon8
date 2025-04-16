<?php
require_once "../inc/php/validaacesso.php";
require_once __DIR__ . "/controllers/_lp_controller.php";
require_once(__DIR__."/../inc/php/validaacesso.php");

function buscarBI(){
	require_once(__DIR__."/controllers/confacessofuncionario_controller.php");
	
	$sqlLpBi = "SELECT b.bipai, b.idbi, b.nome, b.ordem, l.reportid
				  FROM "._DBCARBON."._bi b JOIN _linkportalbi l ON l.idbi = b.idbi 
				  JOIN "._DBCARBON."._lpbi lb ON lb.idbi = b.idbi
				  JOIN "._DBCARBON."._lp lp ON lp.idlp = lb.idlp AND lp.idempresa = ".cb::idempresa()." 
				 WHERE lb.idlp IN ({$_SESSION['SESSAO']['LPS']}) 
				   AND l.empresa = ".cb::idempresa()."    
				   AND l.reportid IS NOT NULL
				   AND b.status = 'ATIVO'
		UNION  ALL
			    SELECT DISTINCT b2.bipai, b2.idbi, b2.nome, b2.ordem, '' as reportid
				  FROM "._DBCARBON."._bi b LEFT JOIN "._DBCARBON."._lpbi lb ON lb.idbi = b.idbi
				  JOIN "._DBCARBON."._bi b2 ON b2.idbi = b.bipai
				  JOIN "._DBCARBON."._lp lp ON lp.idlp = lb.idlp AND lp.idempresa = ".cb::idempresa()."                               
				 WHERE lb.idlp IN ({$_SESSION['SESSAO']['LPS']})  
				   AND b.status = 'ATIVO'
				   AND b2.idbi NOT IN (SELECT GROUP_CONCAT(idbi) FROM _linkportalbi)
			ORDER BY bipai, ordem;";
	$resbi = d::b()->query($sqlLpBi);	
	$bi = [];
	echo "<!-- <pre> $sqlLpBi </pre> -->";
	foreach ($resbi as $key => $b) {
		if(is_null($b['bipai'])){
			if(!isset($bi[$b['idbi']])){
				$bi[$b['idbi']]['nome'] = $b['nome'];
				$bi[$b['idbi']]['idbi'] = $b['idbi'];
				$bi[$b['idbi']]['bipai'] = null;
				$bi[$b['idbi']]['filhos'] = [];
			}
			if(!isset($bi[$b['idbi']]['filhos'])){
				$bi[$b['idbi']]['filhos'] = [];
			}
		}else{
			$bi[$b['bipai']]['filhos'][] = $b;
		}
	}
	
	return $bi;
}

$telasBI = buscarBI();
?>

<style>
	.collapse.in {
		display: flex;
		flex-direction: column;
		width: 100%;
	}
	 
	exploration-footer-modern {
		display: none !important;
	}

	#displayBarraLateral {
		z-index: 999;
	}

	#displayBarraLateral.fechado #menufiltro {
		display:  none;
	}

	#btn-abrir-fechar {
		width: 30px;
		height: 30px;
		position: absolute;
		left: 100%;
		top: 0;
		display: none;
	}

	#displayBarraLateral.fechado #btn-abrir-fechar {
		display: flex;
	}

	#displayBarraLateral.fechado + div {
		width: calc(100% - 40px) !important;
		margin-left: auto;
	}
</style>

<link rel="stylesheet" href="./inc/css/dashboard.css"/>
<link rel="stylesheet" href="./inc/css/menurelatorio.css?version=1.0"/>
<div class="row m-0">
	<div title="Voltar para o Topo" id="rollUp" class="pull-end " style="z-index: 1000; display: none; position: fixed; bottom: 8px; right: 8px; font-size: 20px; color: green; cursor: pointer;">
		<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>
	</div>
	<div class="col-md-2" style="max-width:210px" id="displayBarraLateral">
		<a id="btn-abrir-fechar" onclick="abrirFecharFiltros()" class="tipoItem pointer list-group-item col-xs-2 align-items-center justify-content-center" style="border-radius: 4px !important;padding: 5px;">
			<i title="Ocultar Menu Lateral" style="font-size: 14px;" id="esconder-menu" class="fa fa-angle-right fa-2x cinzaclaro hoverpreto pointer" aria-hidden="true"></i>
		</a>
		<div class="panel panel-default position-relative" id="menufiltro" style="margin-top: 0 !important;">
			<div class="panel-heading">
				<div class="row m-0">
					<div class="nowrap" style="text-align: center">
						<div class="w-100 d-flex">
							<a title="Mostrar Menu de Relatórios" onclick="listaRel()" class="tipoItem h-100 pointer list-group-item col-xs-10" style="font-size: 10px;border-radius: 4px 0 0 4px !important;padding: 5px;">
								BI
							</a>
							<a title="Mostrar Menu de Relatórios" onclick="abrirFecharFiltros()" class="tipoItem h-100 pointer list-group-item col-xs-2 d-flex align-items-center justify-content-center" style="border-radius: 0 4px 4px 0 !important;padding: 5px;">
								<i title="Ocultar Menu Lateral" style="font-size: 14px;" id="esconder-menu" class="fa fa-angle-left fa-2x cinzaclaro hoverpreto pointer" aria-hidden="true"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-body">
				<div style="text-align:center;">
					<input onchange="reportSearch(this)" id="_relSearch" style="width:78%; margin-top:-25px;" type="text" placeholder="Buscar BI" class="form-control">
					<button title="Buscar BI" onclick="reportSearch($('#_relSearch')[0])" style="margin-top: -25px; margin-left: -15px;" class="btn btn-primary btn-sm">
						<i class="fa fa-search" aria-hidden="true"></i>
					</button>
				</div>
				
				<div style="display:none;padding: 5px 20px;" class="col-md-12" id="grupoSearch"></div> 
				<div class="col-md-12" id="gruposRel">
					<!-- pai -->
					<?						
					foreach($telasBI as $key => $pai){ ?>
						<div class="nowrap" style="text-align: center">
							<div class="col-md-12 px-0">
								<a href="#id_<?= $pai['idbi'] ?>" class="pointer relative list-group-item text-uppercase menusuperior" 
								data-toggle='collapse' data-target="#id_<?= $pai['idbi'] ?>" aria-expanded='false' aria-controls='id_<?= $pai['idbi'] ?>'
								id="mod_visualizaponto" style="font-size: 10px; width: 100%; padding: 5px; white-space: normal;" 
								idmodulopesq="<?= $pai['idbi'] ?>" onclick="hideShowRepTipos(this)">
									<?= $pai['nome'] ?>
									<i class="arrow-icon fa fa-chevron-right"></i>
								</a>
								<div id="id_<?= $pai['idbi'] ?>" class="collapse" style="margin-top:5px;">
									<!-- filho -->
									<? foreach($pai['filhos'] as $key => $filho){ ?>
										<div class="nowrap" style="text-align: left">
											<div style="margin: 5px;">
												<a class="pointer list-group-item text-uppercase inicio inative" 
													id="mod_<?= $pai['idbi'] ?>" mod="<?= $pai['idbi'] ?>" idbi="<?= $pai['idbi'] ?>" 
													style="font-size: 9px; width: 100%; padding: 5px; white-space: normal;" 
													onclick="buscarBI('<?= $filho['nome'] ?>','<?= $filho['reportid'] ?>')">
													<?= $filho['nome'] ?>
												</a>
												<div id="mod_<?= $pai['idbi'] ?>" style="margin-top:5px"></div>
											</div>
										</div>
									<? } ?>
								</div>
							</div>
						</div>
					<? } ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-10" id="displayRelatorio">
		<div id="conteudo_relatorio"></div>
	</div>
</div>

<script>
	buscarBI = async (title, reportID) => {		
		await $('#conteudo_relatorio').html(`
			<iframe id="${reportID}" title="${title}" style="width:100%;height:80vh;" 
				src="https://app.powerbi.com/reportEmbed?reportId=${reportID}&autoAuth=true&navContentPaneEnabled=true&filterPaneEnabled=false" 
				frameborder="0" allowFullScreen="true">
			</iframe>			
		`);
		CB.breadcrumb.removeBreadcrumb();
		CB.breadcrumb.addBreadcrumb(' / '+title);
	}

	class Breadcrumb {
		constructor(element) {
			this.element = document.querySelector(element);
			this.items = Array.from(this.element.querySelectorAll('.breadcrumb-item'));
		}

		addBreadcrumb(text) {
			const newItem = document.createElement('span');
			newItem.classList.add('breadcrumb-item');
			newItem.textContent = text;
			this.element.insertBefore(newItem, this.element.lastElementChild);
			this.items.push(newItem);
		}

		removeBreadcrumb() {
			if (this.items.length > 0) {
				const itemToRemove = this.items.pop();
				this.element.removeChild(itemToRemove);
			}
		}
	}

    CB.breadcrumb = new Breadcrumb('#cbModuloBreadcrumbRotulo');

	function reportSearch(input) {
		var filter = input.value.toLowerCase();
		$('#gruposRel .menusuperior').each(function() {
			var parent = $(this);
			var parentText = parent.text().toLowerCase();
			var match = false;

			if (parentText.includes(filter)) {
				match = true;
				parent.show();
				parent.next('.collapse').addClass('in');
				parent.next('.collapse').find('.list-group-item').show();
			} else {
				parent.next('.collapse').find('.list-group-item').each(function() {
					var child = $(this);
					var childText = child.text().toLowerCase();
					if (childText.includes(filter)) {
						child.show();
						match = true;
					} else {
						child.hide();
					}
				});
			}

			if (match) {
				parent.show();
				parent.next('.collapse').addClass('in');
			} else {
				parent.hide();
				parent.next('.collapse').removeClass('in');
			}
		});
	}

	function abrirFecharFiltros() {

		if($('#displayBarraLateral').hasClass('fechado')) {
			$('#displayBarraLateral').removeClass('fechado');
			$('#displayBarraLateral').css('transform', 'translateX(0)');
		} else {
			$('#displayBarraLateral').addClass('fechado');
			$('#displayBarraLateral').css('transform', 'translateX(-100%)');
		}
	}	
</script>