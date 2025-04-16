<link rel="stylesheet" href="./inc/css/dashboard.css" />
<style>
	.row{margin-left: 0 !important;margin-right: 0 !important;}
@media screen {
	.carddash{
		width:220px;
		float:left;
	}
	#cbModuloPesquisa{
		display:none;
	}

	#cbModuloHeader{
		display:none;
	}
	.text-card{
		font-size: 12px;
	}

	.text-on-panel{
		height: auto;
		padding: 8px 8px;
		position: absolute;
		margin-top: -47px;
		border: 1px solid #ddd;
		border-radius: 8px;
	}

	h3{
		width:180px;
		z-index:999;
	}

	.inative{
		background-color: #ddd;
	}

	.row2 {

	margin-top:100px;
	-moz-column-count: 5;
	-webkit-column-count: 5;
	column-count: 5;

	}
	.ative{
		background-color: #666 ;
		color: #fff !important;
		
	}
	.ative:hover , .ative:focus{
		background-color: #4c4b4b  !important;
	}


	.botao-menu-lateral.ativo {
		background: #337ab7;
		color: white;
	}
	
	.panel {

	margin:  0;
	padding:  0; 
	width:100%;
	page-break-inside: avoid;
	}

	div.panel:nth-child(1) { 
		margin-top:0px !important;
	}

	.hideshowtable {
		display: none !important;
	}

}

@media print {

	/*header, footer, aside, nav, form, iframe, #cbContainer, #cbModal{
		display: none !important;
	}*/

	.hideshowtable {
		display: block !important;
	}

   /* All unecessary elements */
	#cbContainer, #cbModal{
		display: none !important;
	}

	body{
		font-family: "Helvetica Neue",Helvetica,Arial,sans-serif !important;
	}

   	table{
		break-inside: auto;
	}

	table, tr, td{
		border: 1px solid #eee;
	}

	thead{
		font-weight: bold;
	}

	a[href]:after {
			content: " (" attr(href) ")";
	}
 
	a[href^="#"]:after,
	a[href^="javascript"]:after {
			content: "";
	}

    /* Fix Header and footer class on page */
    .footer, 
    .header {
        position: fixed;
        top: 0;
        left: 0;
        height: 40px;
        width: 100%;
        background-color: white;
        margin-left: 25px;
    }

    .footer{
        top: auto;
        bottom: 0;
    }
    
    @page{
		margin: 2cm;
    	counter-increment: page;
    	counter-reset: page 1;
    }
}

	.botao-menu-lateral {
		width: 100%;
		background: white;
		border: 1px solid #e3e3e3;
		border-radius: 4px;
		font-size: 10px;
		padding: 5px;
		text-transform: uppercase;
	}

	.painel-cards [class*="col-"], .painel [class*="col-"] {
		padding: 3px 3px;
	}
</style>
<?
require_once("../inc/php/functions.php");
validaToken();
//print_r(getModsUsr("MODULOS"));
//echo array_key_exists("doc", getModsUsr("MODULOS"));

if(!array_key_exists("doc", getModsUsr("MODULOS"))){
	$sql_doc = "and exists (select 1 from fluxostatuspessoa fspp where fspp.modulo like 'documento%' and fspp.idmodulo = s.idsgdoc
	and fspp.idobjeto = ".$_SESSION['SESSAO']['IDPESSOA']." and fspp.tipoobjeto = 'pessoa' and fspp.tipoobjetoext = fsp.tipoobjeto and fspp.idobjetoext = fsp.idobjeto)";
	$docpessoa = "and c.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']."";

}else{
	$sql_doc = '';
	$docpessoa = '';
}
	 $s = "	
	 select * from (

		SELECT
		sd.idsgdepartamento AS iddashgrupo,
		concat(if(e.idempresa = 1,'LD - ',if(e.idempresa = 2,'IN - ','')),'',REPLACE(sd.departamento,'Departamento ','')) AS grupo_rotulo,
		sd.idsgdepartamento AS iddashcard,
		extrairnumeros(hex(concat(sd.idsgdepartamento,t.idsgdoctipo))) AS panel_id,
		'' AS panel_class_col,
		upper(t.idsgdoctipo) AS panel_title,
		sd.idsgdepartamento AS card_id,
		'col-md-12 col-sm-12 col-xs-12' AS card_class_col,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(idsgdoc
					SEPARATOR ','),
				']') AS card_url,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
								`s`.`datavencimento`,
								(CASE
									WHEN (`t`.`vencimento` < 0) THEN ''
									ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
								END))
							AS DATE) < NOW()
							OR (SELECT 
								1
							FROM
								carrimbo c
							WHERE
								c.tipoobjeto like 'documento%'
									AND c.idobjeto = s.idsgdoc
									AND status = 'PENDENTE'
									".$docpessoa."
									
							LIMIT 1) = 1,
						s.idsgdoc,
						NULL)),
				']') AS card_atraso_url,
		'' AS card_url_tipo,
		'' AS card_url_js,
		'titulo' AS ordenacao,
		'asc' AS sentido,
		'' AS card_notification_bg,
		'N' AS card_notification,
		'primary' AS card_color,
		'primary' AS card_border_color,
		'' AS card_bg_class,
		concat(' ',departamento) AS card_title,
		'' AS card_title_sub,
		COUNT(s.idsgdoc) AS card_value,
		SUM(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
					`s`.`datavencimento`,
					(CASE
						WHEN (`t`.`vencimento` < 0) THEN ''
						ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
					END))
				AS DATE) < NOW()
				OR (SELECT 
					1
				FROM
					carrimbo c
				WHERE
					c.tipoobjeto like 'documento%'
						AND c.idobjeto = s.idsgdoc
						AND status = 'PENDENTE'
						".$docpessoa."
				LIMIT 1) = 1,
			1,
			0)) AS card_atraso_value,
		'' AS card_icon,
		'' AS card_row,
		'Documentos' AS card_title_modal,
		'_modulo=documento&_acao=u' AS card_url_modal,
		departamento AS card_ordem,
		sd.departamento AS panel_ordem,
		'' AS grupo_ordem,
		'' AS code,
		'' AS tab,
		'' AS modulo,
		'' AS col
FROM
    sgdoc s
        LEFT JOIN
    `sgdoctipodocumento` `t` ON ((`t`.`idsgdoctipodocumento` = `s`.`idsgdoctipodocumento`))
        JOIN
    fluxostatuspessoa fsp ON fsp.modulo like 'documento%'
        AND fsp.idmodulo = s.idsgdoc
        AND fsp.tipoobjeto IN ('sgdepartamento')
        JOIN
    sgdepartamento sd ON sd.idsgdepartamento = fsp.idobjeto and sd.status = 'ATIVO'
	join empresa e on e.idempresa = sd.idempresa
	WHERE
    1 AND NOT s.status = 'OBSOLETO'
	 ".getidempresa('sd.idempresa','documento')." 
		".$sql_doc."

GROUP BY fsp.tipoobjeto , fsp.idobjeto, t.idsgdoctipo

	union all 


	SELECT
		sd.idsgdepartamento AS iddashgrupo,
		concat(if(e.idempresa = 1,'LD - ',if(e.idempresa = 2,'IN - ','')),'',REPLACE(sd.departamento,'Departamento ','')) AS grupo_rotulo,
		ss.idsgsetor AS iddashcard,
		extrairnumeros(concat(sd.idsgdepartamento,t.idsgdoctipo)) AS panel_id,
		'' AS panel_class_col,
		ifnull(upper(t.idsgdoctipo),'Outros')  AS panel_title,
		ss.idsgsetor AS card_id,
		'col-md-12 col-sm-12 col-xs-12' AS card_class_col,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(idsgdoc
					SEPARATOR ','),
				']') AS card_url,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
								`s`.`datavencimento`,
								(CASE
									WHEN (`t`.`vencimento` < 0) THEN ''
									ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
								END))
							AS DATE) < NOW()
							OR (SELECT 
								1
							FROM
								carrimbo c
							WHERE
								c.tipoobjeto like 'documento%'
									AND c.idobjeto = s.idsgdoc
									AND status = 'PENDENTE'
									".$docpessoa."
							LIMIT 1) = 1,
						s.idsgdoc,
						NULL)),
				']') AS card_atraso_url,
		'' AS card_url_tipo,
		'' AS card_url_js,
		'titulo' AS ordenacao,
		'asc' AS sentido,
		'' AS card_notification_bg,
		'N' AS card_notification,
		'primary' AS card_color,
		'primary' AS card_border_color,
		'' AS card_bg_class,
		setor AS card_title,
		'' AS card_title_sub,
		COUNT(s.idsgdoc) AS card_value,
		SUM(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
					`s`.`datavencimento`,
					(CASE
						WHEN (`t`.`vencimento` < 0) THEN ''
						ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
					END))
				AS DATE) < NOW()
				OR (SELECT 
					1
				FROM
					carrimbo c
				WHERE
					c.tipoobjeto like 'documento%'
						AND c.idobjeto = s.idsgdoc
						AND status = 'PENDENTE'
						".$docpessoa."
				LIMIT 1) = 1,
			1,
			0)) AS card_atraso_value,
		'' AS card_icon,
		'' AS card_row,
		'Documentos' AS card_title_modal,
		'_modulo=documento&_acao=u' AS card_url_modal,
		setor AS card_ordem,
		sd.departamento AS panel_ordem,
		'' AS grupo_ordem,
		'' AS code,
		'' AS tab,
		'' AS modulo,
		'' AS col
FROM
    sgdoc s
        LEFT JOIN
    `sgdoctipodocumento` `t` ON ((`t`.`idsgdoctipodocumento` = `s`.`idsgdoctipodocumento`))
        JOIN
    fluxostatuspessoa fsp ON fsp.modulo like 'documento%'
        AND fsp.idmodulo = s.idsgdoc
        AND fsp.tipoobjeto IN ('sgsetor')
        JOIN
    sgsetor ss ON ss.idsgsetor = fsp.idobjeto  and ss.status = 'ATIVO'
        JOIN
    objetovinculo ovs ON ovs.idobjetovinc = ss.idsgsetor
        AND ovs.tipoobjetovinc = 'sgsetor'
        JOIN
    sgdepartamento sd ON sd.idsgdepartamento = ovs.idobjeto
        AND ovs.tipoobjeto = 'sgdepartamento' and sd.status = 'ATIVO'
		join empresa e on e.idempresa = sd.idempresa
        
WHERE
    1 AND NOT s.status = 'OBSOLETO'
	 ".getidempresa('sd.idempresa','documento')." 
	".$sql_doc."
GROUP BY fsp.tipoobjeto , fsp.idobjeto, t.idsgdoctipo



union all

SELECT
		fsp.idobjeto AS iddashgrupo,
		' Outros' AS grupo_rotulo,
		fsp.idobjeto AS iddashcard,
		fsp.idobjeto AS panel_id,
		'' AS panel_class_col,
		ifnull(upper(t.idsgdoctipo),'Outros') AS panel_title,
		fsp.idobjeto AS card_id,
		'col-md-12 col-sm-12 col-xs-12' AS card_class_col,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(idsgdoc
					SEPARATOR ','),
				']') AS card_url,
		CONCAT('_modulo=documento&_pagina=0&_ordcol=titulo&_orddir=asc&idsgdoc=[',
				GROUP_CONCAT(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
								`s`.`datavencimento`,
								(CASE
									WHEN (`t`.`vencimento` < 0) THEN ''
									ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
								END))
							AS DATE) < NOW()
							OR (SELECT 
								1
							FROM
								carrimbo c
							WHERE
								c.tipoobjeto like 'documento%'
									AND c.idobjeto = s.idsgdoc
									AND status = 'PENDENTE'
									".$docpessoa."
							LIMIT 1) = 1,
						s.idsgdoc,
						NULL)),
				']') AS card_atraso_url,
		'' AS card_url_tipo,
		'' AS card_url_js,
		'titulo' AS ordenacao,
		'asc' AS sentido,
		'' AS card_notification_bg,
		'N' AS card_notification,
		'primary' AS card_color,
		'primary' AS card_border_color,
		'' AS card_bg_class,
		' Outros' AS card_title,
		'' AS card_title_sub,
		COUNT(s.idsgdoc) AS card_value,
		SUM(IF(CAST(IF((`s`.`idsgdoctipodocumento` = 326),
					`s`.`datavencimento`,
					(CASE
						WHEN (`t`.`vencimento` < 0) THEN ''
						ELSE (`s`.`alteradoem` + INTERVAL `t`.`vencimento` DAY)
					END))
				AS DATE) < NOW()
				OR (SELECT 
					1
				FROM
					carrimbo c
				WHERE
					c.tipoobjeto like 'documento%'
						AND c.idobjeto = s.idsgdoc
						AND status = 'PENDENTE'
						".$docpessoa."
				LIMIT 1) = 1,
			1,
			0)) AS card_atraso_value,
		'' AS card_icon,
		'' AS card_row,
		'Documentos' AS card_title_modal,
		'_modulo=documento&_acao=u' AS card_url_modal,
		' Outros' AS card_ordem,
		' Outros' AS panel_ordem,
		'' AS grupo_ordem,
		'' AS code,
		'' AS tab,
		'' AS modulo,
		'' AS col
FROM
    sgdoc s
        LEFT JOIN
    `sgdoctipodocumento` `t` ON ((`t`.`idsgdoctipodocumento` = `s`.`idsgdoctipodocumento`))
        JOIN
    fluxostatuspessoa fsp ON fsp.modulo like 'documento%'
        AND fsp.idmodulo = s.idsgdoc
        AND fsp.tipoobjeto = ('pessoa') and fsp.idobjeto = ".$_SESSION['SESSAO']['IDPESSOA']."
      
WHERE
    1 AND NOT s.status = 'OBSOLETO'
	and not exists (select 1 from fluxostatuspessoa fspp where fspp.modulo like 'documento%' and fspp.idmodulo = s.idsgdoc
	and fspp.idobjeto = ".$_SESSION['SESSAO']['IDPESSOA']." and fspp.tipoobjeto = 'pessoa' and fspp.tipoobjetoext in ('sgdepartamento','sgsetor'))
	".getidempresa('s.idempresa','documento')."
GROUP BY fsp.tipoobjeto , fsp.idobjeto, t.idsgdoctipo



	) a

		order by
		grupo_rotulo, panel_title, panel_id,  card_title";

	
	//$sqldash = "select * from (".$sqldash.") a ";
	echo '<!-- '.$s.'-->';
//	die();
	$resfig = d::b()->query($s) or die("Erro ao recuperar figura para cabeçalho do relatório: " . mysqli_error(d::b()) . "<p>SQL: $s");
	$i = -1;
	$j = 0;
	$c = -1;

	if(mysqli_num_rows($resfig) == 0){
		header("Location: ../form/sgdoc.php");
		die("Não existem documentos nesta empresa ou não existem usuários vinculados aos documentos");
	}

	while ($_row = mysql_fetch_assoc($resfig)){
		

		/*if ($_row['code'] != ''){
			if ($_row['code'] == 'idpessoa'){
				$idpessoa = "and idpessoa = '".$_SESSION["SESSAO"]["IDPESSOA"]."'";
			}else{
				$idpessoa = '';

			}

			$sqlp = "select count(1) as card_value, concat('_modulo=".$_row['modulo']."&".$_row['col']."=[',group_concat(".$_row['col']."),']') as card_url  from ".$_row['tab']." where 1 ".$clausula." ".$pessoas." ".$idpessoa." ";
			echo '<!-- '.$sqlp.'-->';
			$resp= d::b()->query($sqlp) or die("Erro Atualização Dashcard ".$_row['iddashcard'].": " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
			while ($_rowp = mysql_fetch_assoc($resp)){
				$_row['card_value'] = $_rowp['card_value'];
				$_row['card_url'] = $_rowp['card_url'];
			}
		}*/
		
		if ($iddashgrupo != $_row['iddashgrupo']){
			$iddashgrupo = $_row['iddashgrupo'];
			$c++;
			
			$json[$c]['iddashgrupo']							= $_row['iddashgrupo'];
			$json[$c]['grupo_rotulo']							= $_row['grupo_rotulo'];
		
			$i = -1;
		}
		
		if ($panel_id != $_row['panel_id']){
			$panel_id = $_row['panel_id'];
	
			$i++;
			
			$json[$c]['panels'][$i]['panel_id']							= $_row['panel_id'];
			$json[$c]['panels'][$i]['panel_class_col']					= $_row['panel_class_col'];
			$json[$c]['panels'][$i]['panel_title']						= !empty($_row['panel_title'])?$_row['panel_title']:'Outros';
			$j = 0;
		}else{
			
			$json[$c]['panels'][$i]['panel_class_col']					= 'col-md-'.(($j)*2).' aqui '.$j;
		}		
		$json[$c]['panels'][$i]['cards'][$j]['iddashcard']				= $_row['iddashcard'];
		$json[$c]['panels'][$i]['cards'][$j]['card_id']					= $_row['card_id'];
		$json[$c]['panels'][$i]['cards'][$j]['card_class_col']			= 'col-md-12';
		$json[$c]['panels'][$i]['cards'][$j]['card_url']					= $_row['card_url'].'&_ordcol='.$_row['ordenacao'].'&_orddir='.$_row['sentido'];
		$json[$c]['panels'][$i]['cards'][$j]['card_atraso_url']				= $_row['card_atraso_url'].'&_ordcol='.$_row['ordenacao'].'&_orddir='.$_row['sentido'];
		$json[$c]['panels'][$i]['cards'][$j]['card_notification_bg']		= $_row['card_notification_bg'];
		$json[$c]['panels'][$i]['cards'][$j]['card_notification']			= $_row['card_notification'];
		$json[$c]['panels'][$i]['cards'][$j]['card_border_color']			= $_row['card_border_color'];
		$json[$c]['panels'][$i]['cards'][$j]['card_color']				= $_row['card_color'];
		$json[$c]['panels'][$i]['cards'][$j]['card_bg_class']				= $_row['card_bg_class'];
		$json[$c]['panels'][$i]['cards'][$j]['card_title']				= $_row['card_title'];
		$json[$c]['panels'][$i]['cards'][$j]['card_title_sub']			= $_row['card_title_sub'];
		$json[$c]['panels'][$i]['cards'][$j]['card_title_modal']			= $_row['card_title_modal'];
		$json[$c]['panels'][$i]['cards'][$j]['card_url_modal']			= $_row['card_url_modal'];
		$json[$c]['panels'][$i]['cards'][$j]['card_value']				= $_row['card_value'];
		$json[$c]['panels'][$i]['cards'][$j]['card_atraso_value']				= $_row['card_atraso_value'];
		$json[$c]['panels'][$i]['cards'][$j]['card_icon']					= $_row['card_icon'];
		$json[$c]['panels'][$i]['cards'][$j]['card_row']					= $_row['card_row'];


		if($_row['card_url_tipo'] == 'JS'){
			$fname="_".md5(uniqid());
			$onclick=$fname."()";
			?>
			<script>
				function <?=$fname?>() {
					<?=$_row["card_url_js"]?>
				}
				//# sourceURL=snippet_<?=$s["idsnippet"]?>
			</script>
			<?
			$json[$c]['panels'][$i]['cards'][$j]['card_url_tipo']	= 'JS';
			$json[$c]['panels'][$i]['cards'][$j]['card_url']		= $onclick;
			$json[$c]['panels'][$i]['cards'][$j]['card_atraso_url']		= $onclick;
		}
	//	echo $i;
		$j++;	
	}
//print_r($json);
	//console.log(json); 
	$json = json_encode($json);
//	echo $json;
?>

<script>
function montagrupo(json, callback){
	var i=0;
	 var grupo = '<div id="dashboards" class="w-100" style="margin-top:30px"><div class="row">';
	 var passou=false;
	 console.log(json);
	 json.forEach(function(item, index) {
		 i++;
		 console.log('criar '+item.iddashgrupo);
		 if($('#' + item.iddashgrupo).length == 0){
			  console.log('passou '+item.iddashgrupo);
			 passou = true;
			 grupo = grupo + 
		  `	<!-- LOOP DOS Grupos --> 
			<div class="row" id="grupo_${item.iddashgrupo}" style="display:none">
					<div class="panel panel-primary "  style="border-color:#F3F3F3">
				
						<div class="panel-body" style="background:#F3F3F3;padding:30px;">
							<h3 style="width:auto;color:#F3F3F3 !important;;background:#666;border-color:#666;" class="text-on-pannel text-primary" id="${item.grupo_rotulo}"><strong class="text-uppercase"> ${item.grupo_rotulo}</strong></h3>
							<div id="g${item.iddashgrupo}" style="margin-top:20px">
							</div>
						</div>
					</div>
				</div>
			<!-- FIM LOOP DOS Grupos -->`;
		}
	 });
	 $('#cbModuloForm').append(grupo);
	 $('#cbModuloForm').append('</div></div>');
	 /* if(passou){
		$('#cbModuloForm').html(panel);
	  } */
	 callback(json);
}  
function montapanel(json, callback){

	 var panel = '';
	 var passou=false;
	 console.log(json);
	 console.log('PAINEL DEB');
	 json.forEach(function(item, index) {

		let itensPanels=[]
		$.each(item["panels"], function(i,n) {
			if(n.panel_title){
				itensPanels.push(n);
			}
		});

		itensPanels.forEach(function(i, x) {
			 passou = true;
			 if($('#g'+item.iddashgrupo+' #painel_' + i.panel_title.replaceAll(' ','.')).length == 0){ 
				panel = panel + 
				`	<!-- LOOP DOS PAINÉIS --> 
							<div class="row" id="painel_${i.panel_title.replaceAll(' ','.')}" style="margin-bottom:40px">
								<div class="panel panel-primary "  style="background-color:#fcfcfc;border-color:#fcfcfc">
							
									<div class="panel-body">
										<h3 style="width: auto;" class="text-on-pannel text-primary" id="${i.panel_title.replaceAll(' ','.')}"><strong class="text-uppercase"> ${i.panel_title}</strong></h3>
										<div id="p${i.panel_title.replaceAll(' ','.')}" class="col-md-12 painel-cards">
										</div>
									</div>
								</div>
							</div>
					
					<!-- FIM LOOP DOS PAINÉIS -->`;
					
					$('#g' + item.iddashgrupo).append(panel);
					$('#g' + item.iddashgrupo).find( ".cbIBadgeSnippet2" ).hide();
					panel = '';
			}
		});
	
	 });
	
	 /* if(passou){
		$('#cbModuloForm').html(panel);
	  } */
	 callback(json);
	 
} 

	 
montaCards=function(json){

	var card = '';
	var hide='';
	var card_title_modal_atraso = "";
	for(let item of json){
		for(let i in item['panels']){
			let panel = item['panels'][i];
			for(let j of panel.cards){
				if(panel.panel_title){
					if($('#p'+panel.panel_title.replaceAll(' ','.')+' #c' + j.card_id).length == 0){   
						if(j.card_value == 0){
							j.card_url = '';
						}
						card_title_modal_atraso = j.card_title_modal + ' PENDENTE';

							card = `
								<!-- LOOP DOS BLOCOS -->
								<div id="c${j.card_id}" class="col-md-1">
									<div class="w-100 mb-4 pointer hovercinzaclaro"  >
										<span onclick="popLink('${j.card_atraso_url}','${card_title_modal_atraso}','${j.card_border_color}','${j.card_url_modal}','${j.card_url_tipo}')" class="cbIBadgeSnippet2 bg-danger badge badgedash" data-value-atraso="${j.card_atraso_value}" title="${j.card_title} (pendente)" style="" ibadge="${j.card_notification}">${j.card_atraso_value}</span>
										<div onclick="popLink('${j.card_url}','${j.card_title_modal}','${j.card_border_color}','${j.card_url_modal}','${j.card_url_tipo}')" class="card border-left-${j.card_border_color} shadow h-100 py-2 bg-${j.card_bg_class}"style="border-radius:8px;">
											<div class="card-body">
												<div class="row no-gutters align-items-center">
													<div class="col-md-12">
														<div class="text-xs negrito text-uppercase mb-1">${j.card_title}</div>
														
													</div>
													
												</div>
												<div class="row">
													<div class="col-md-12">
													<div class="h6 mb-0 font-weight_bold text_gray-800 titulo-${j.card_color}" style="text-align:center;font-weight:bolder;"><span id='card_value'>${j.card_value}</span></div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
													<div  style="text-align:left;font-weight:bolder;"><span id='card_title_sub' class="bg-${j.card_border_color}" card_titlesub>${j.card_title_sub}</span></div>
													
													</div>
												</div>
											</div>
										</div>
									</div> 
								</div> 
									
								<!-- FIM LOOP DOS BLOCOS --> `;

								if(j.card_row == 'Y'){
									card = card + '<div class="row">';
								}
							
								$('#g'+item.iddashgrupo+' #p' + panel.panel_title).append(card);
								//$('#p' + i.panel_id).find( "#cbIBadgeSnippet2" ).hide();
						
					}else{
						console.log('existe'); 
					//	$('#p' + i.panel_id).find( ".cbIBadgeSnippet2" ).removeClass();
						$('#p' + panel.panel_title.replaceAll(' ','.')).find( ".cbIBadgeSnippet2" ).addClass(' badge badgedash bg-danger');
						
						$('#p' + panel.panel_title.replaceAll(' ','.')).find( ".cbIBadgeSnippet2" ).addClass(panel["card_notification_bg"]);
						$('#p' + panel.panel_title.replaceAll(' ','.')).find( ".cbIBadgeSnippet2" ).html(panel["card_notification"]);

					
					}
				}
			}
		}
	}

$('#dashboards').find( ".cbIBadgeSnippet2[data-value-atraso='0']" ).addClass('hide');
	 //return (card);
} 

//funcao responsavel por construir o menu de exibicao dos grupos e paineis do dashboard -- ALBT 12/05/21 -- sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=463572
function construirMenuFiltro(json, prefGrupo){
	//reorganiza a tela, deixando a lista de dashboards selecionados em col-md10, e o filtro em col-md-2 -- apenas para os usuarios selecionados 
	//para o preenchimento das informações foi usado jquery logo abaixo (com um for percorrendo o json para obter as informaçoes ja recuperadas)
	// as divs com os id's: "grupos" e "lista_dashboards" tem o fechamento a frente na mesma linha 
	let filtro = $(`<div class="row"> 
						<div id="_menu_lateral_" class="col-md-2" style="margin-top:25px;">
							<div class="panel panel-default" id="menufiltro">
								<div class="panel-heading">
									<div class="row m-0">
										<div class="nowrap">
											<div class="col-md-4 pull-left">
												<a title="Ocultar menu lateral" class="tipoItem pointer list-group-item" style="font-size: 16px; width: 80%; text-align:center; padding: 0px!important;" href="javascript:ocultarMenuLateral();"><i class="fa fa-angle-left"></i></a>                            
											</div>
											<div class="col-md-8 px-0">
												<a class="tipoItem pointer list-group-item" style="font-size: 10px; width: 100%; padding: 5px; text-align:center;"  href="javascript:limpaFiltro();">LIMPAR FILTROS</a>                            
											</div>
										</div>
									</div>
								</div>
								<div class="panel panel-body" style="padding-top: 0px !important;">
									<div class="col-md-12" id="grupos">	</div> 
								</div>
							</div>
						</div>
						<div id="lista_dashboards" class="col-md-10 _col_md_control_"></div>
					</div>`);
	//for responsavel por percorrer o json e esconder no inicio de acordo com as preferencias
	// no dash constroi a div maior que contem o nome dos grupos, q contem um ou mais paineis dentro. 
	for(let o of json){
		//muda a cor de acordo com a visualizacao Y é aberto e N é fechado
			// se a class for active já envia o background-color padrão, se for inative já envia as configurações de cor setadas no código no entre as tags <style></style>
			//e na mesma condicao ja mostra ou esconde o grupo. 
			let classe = "";
			if(prefGrupo[o.iddashgrupo] == "Y"){
				$(`#grupo_${o.iddashgrupo}`).show();
				classe = "ativo";
			}else{
				$(`#grupo_${o.iddashgrupo}`).hide();
			}
			let dash = $(`
					<div class="list-group" style="text-align: center">
						<div class="col-md-12 p-0">
							<a class="block pointer text-uppercase botao-menu-lateral ${classe}" id="grp${o.iddashgrupo}"  style="font-size: 10px; width: 100%; padding: 5px;">${o.grupo_rotulo}</a>
						</div>
					</div>
			`);
		
			
		dash.find(`a`).on('click', function(){
			var vthis = this;
			$(`#grupo_${o.iddashgrupo}`).toggle("fast", function(){
				if($(vthis).hasClass("ativo")){
					$(vthis).removeClass("ativo");
					CB.setPrefUsuario('m','{"'+CB.modulo+'":{"grupo":{"'+o.iddashgrupo+'":"N"}}}');
				}else{
					$(vthis).addClass("ativo");
					CB.setPrefUsuario('m','{"'+CB.modulo+'":{"grupo":{"'+o.iddashgrupo+'":"Y"}}}');
				}
			});
			
		});
		filtro.find("#grupos").append(dash); //aqui envia os grupos já com os paineis dentro
	}
	filtro.find("#lista_dashboards").append($("#dashboards"));   // aqui envia para a lista dash os grupos/paineis que foram marcados para ser visualizados. 
	$('#cbModuloForm').append(filtro);
}

var json = <?=$json;?>;

if (json[0]){

	montagrupo(json, function(resultado){
		montapanel(json, function(resultado){
			montaCards(resultado);
		});
	});
}
<?
//aqui são os usuarios permitidos a ver e modificar o filtro, e ter preferencias. 
//in array busca o usuario atual da sessao com os nomes que estao setados no array, caso for necessario adicionar mais algum usuario colocar o nome no array
//userPref -> functions, carbon.js

 
$userPref = userPref('s', $_GET['_modulo'].".grupo");
$userPref = json_decode($userPref,true);
if(!$userPref){
	$userPref = [];
}else{
	$userPref = $userPref[$_GET["_modulo"].".grupo"];
}?>

construirMenuFiltro(json, <?=json_encode($userPref)?>);   //logo após a montagem do grupo, panel e cards chama a funcao passando o json já pronto para montagem do filtro do dashboard. 




function popLink(url,title,color,urlmodal, urltipo){
	$("#_dashresultscontent").remove();
	if (urltipo == 'JS'){
		//console.log(url);
		eval(url);
	}else{
	//	alert(url);

		var strCabecalho = "</strong><label class='fonte08'><span class='titulo-"+color+"'>"+title+"</span></label></strong>";

		//Altera o cabeçalho da janela modal
		$("#cbModalTitulo")
					.html(strCabecalho)
					.append("&nbsp;&nbsp;<label id='resultadosEncontrados' class='fonte08'></label>")
					.append(`<i class='fa fa-file-excel-o floatright' id='btPrintNucleo' title='Impressão' onclick="gerarCsv('${title}')"></i>`)
					.append(`<i class='fa fa-print floatright' id='btPrintNucleo' title='Impressão' onclick="printNucleo('${title}')"></i>`)
					//.append("<i class='fa fa-eye floatright' title='Marcar como visualizados' onclick=\"resetNotificacoesPorNucleo(2)\"></i>")
		;

		if (url != '' && url != 'null'){
			//console.log('teste'+url);
			if (url.search("php")>=0 || url.search("novajanela")>=0){
				link= './'+url;
				janelamodal(url);
			}else{
				link='form/_modulofiltrospesquisa.php?_pagina=0'
			
				//Realiza a chamada da pagina de pesquisa manualmente
				$.ajax({
					context: this,
					type: 'get',
					cache: false,
					url: link,
					data: url,
					dataType: "json",
					beforeSend: function(){
						alertAguarde();

					},
					error: function(data) {
												
						var str = JSON.stringify(data);
						var part = str.substring( str.lastIndexOf("[") + 1, str.lastIndexOf("]"));
						if (part){
							alertAtencao("Sem permissão ao módulo: "+part+"<br>Favor entrar em contato com Departamento de Processos - Ramal: 110");
						}
						
					},
					success: function(data, status, jqXHR){
						
						//Json contem resultados encontrados?
						if(!$.isEmptyObject(data)){
							//Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
							if(parseInt(data.numrows)>parseInt(CB.jsonModulo.limite)||data.numrows>2000){
								alertAtencao("Mais de "+CB.jsonModulo.limite+" resultados foram encontrados!\n<a href='?" + vGetAutofiltro+"' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
								janelamodal("?" + vGetAutofiltro);
							}else{
								
								$("#cbModal").addClass("noventa").modal();
								var tblRes = CB.montaTableResultados(data, function(obj, event){

									oTr = $(obj);
									oTr.css("backgroundColor","transparent");
									
										//janelamodal("'"+urlmodal+"'" + oTr.attr("goParam"));
								
									janelamodal('?'+urlmodal+'&' + oTr.attr("goParam"));
									
								});
								$("#cbModal #cbModalCorpo").html(tblRes);

								$("body").append(`<div id="_dashresultscontent" class="hideshowtable">
													<table>${tblRes.html()}</table>
												</div>`);
								
								if(data.numrows){
									$("#resultadosEncontrados").html("("+data.numrows+" resultados encontrados)").attr("cbnumrows",data.numrows);
								}
							}
						}else{
							
							alert("Nenhum resultado encontrado.");
							
						}
					},
					complete: function(){
						CB.aguarde(false);
						if(CB.limparResultados==true){
							CB.resetDadosPesquisa();
						}
					}
				});
			}
		}
	}
}

// GVT - 21/06/2021 - Abre a impressão da página caso o modal estiver aberto e visível
// Verifique as formatações CSS p/ impressão e a construção da table na função popLink
function printNucleo( titulo ) {
	if($("#restbl").is(":visible")){
		let titleAnt = document.title;
		document.title = titulo;
		window.print();
		document.title = titleAnt;
	}
}

function gerarCsv( tituloCsv ) {

	if($("#restbl").is(":visible")){
		var CsvContent = "";
		var virg = "";

		$("#restbl").find("tr").each((i, o) => {
			$(o).find("td").each((j, k) => {
				CsvContent += virg + $(k).text();
				virg = ";";
			});
			CsvContent += "\n";
			virg = "";
		});

		tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');

		let dt = new Date();
		let csvDate = `${
			dt.getDate().toString().padStart(2, '0')}${
			(dt.getMonth()+1).toString().padStart(2, '0')}${
			dt.getFullYear().toString().padStart(4, '0')}${
			dt.getHours().toString().padStart(2, '0')}${
			dt.getMinutes().toString().padStart(2, '0')}${
			dt.getSeconds().toString().padStart(2, '0')}`;

		let hiddenElement = document.createElement('a');
		hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(CsvContent);
		hiddenElement.target = '_blank';
		hiddenElement.download = tituloCsv+csvDate+'.csv';
		hiddenElement.click();
	}
}

//	montaHTML();
	var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';
	$('#dashboards').prepend('<div class="row" style="margin-bottom:40px"><a href="javascript:window.location.href = \'?_modulo=documento\'" class="fa fa-book" style="float: left;border: 1px solid #ddd; padding: 4px; border-radius: 8px; background: #eee;" title="Documentos"></a><button class="btn btn-xs btn-primary" onclick="window.location.href = \'?_modulo=documento&_acao=i'+idempresa+'\'" style="position: relative; float: left; position: relative; margin-left: 10px;">Novo Documento</button></div>');

	function ocultarMenuLateral(){
        $('#_menu_lateral_').hide()
        $('._col_md_control_').removeClass('col-md-10').addClass('col-md-12');
        $('._col_md_control_').prepend(`<a id="_btn_mostrar_menu_" title="Exibir menu lateral" class="tipoItem pointer list-group-item" style="font-size: 16px;width: 32px;text-align:center;padding: 3px!important;margin-top: 10px;border-radius: 8px;" href="javascript:mostrarMenuLateral();"><i class="fa fa-angle-right"></i></a>`)
        
    }

	function mostrarMenuLateral(){
        $('#_menu_lateral_').show()
        $('._col_md_control_').removeClass('col-md-12').addClass('col-md-10');
        $('#_btn_mostrar_menu_').remove()
        
    }

	
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
