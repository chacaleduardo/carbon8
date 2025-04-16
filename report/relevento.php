<?
include_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
?>
<html>
	<head>
		<title>Evento</title>
		<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
		<link href="../inc/css/sislaudo.css" media="all" rel="stylesheet" type="text/css" />
		<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
		<script src="../inc/js/functions.js"></script>
	</head>	
	<body style="margin: auto; margin-top: 0.2cm; margin-bottom: 1cm; padding: 3mm 10mm; width: 21cm;">
	
		<?
		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('evento', 'idevento', $_REQUEST['idevento']);
		
		if($_timbrado != 'N'){
	
			$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERSERVICO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado=mysql_fetch_assoc($_restimbrado);

			$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
			$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

			$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
			$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
			
			$_timbradocabecalho = $_figtimbrado["caminho"];
			$_timbradomarcadagua = $_figtimbrado1["caminho"];
			$_timbradorodape = $_figtimbrado2["caminho"];
			
			if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}
		}

		
		if (!empty($_REQUEST['idevento'])) {
			$id = $_REQUEST['idevento'];
		}else{
			die ("Não é possível imprimir Relatório");
		}	
		
		/*
		 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
		 * $pagvalcampos: Informar os Parâmetros GET que devem ser validados para compor o select principal
		 *                pk: indica Parâmetro chave para o select inicial
		 *                vnulo: indica Parâmetros secundários que devem somente ser validados se nulo ou não
		 */
		$pagvaltabela = "evento";
		$pagvalcampos = array(
			"idevento" => "pk"
		);
		
		/*
		 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
		 */
		$pagsql = "SELECT e.*, 
					TIME_FORMAT(TIMEDIFF(es.sla,
						TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
						IF((SELECT  MIN(r1.alteradoem)
								FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
								JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
								JOIN fluxostatus mf1 ON mf1.idfluxo = ms1.idfluxo AND mf1.idfluxostatus = r1.idfluxostatus
                                JOIN carbonnovo._status s1 ON mf1.idstatus = s1.idstatus
										AND s1.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
								WHERE
									r1.idmodulo = e.idevento IS NOT NULL),
							(SELECT MIN(r1.alteradoem)
								FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
								JOIN fluxostatus mf1 ON mf1.idfluxo = ms1.idfluxo AND mf1.idfluxostatus = r1.idfluxostatus
								JOIN "._DBCARBON."._status s1 ON mf1.idstatus = s1.idstatus
										AND s1.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
								WHERE
									r1.idmodulo = e.idevento),
							NOW())) * 60)),
						'%H:%i:%s')),
					'%H:%i') AS datasla,
					s.statustipo AS posicao
				FROM evento e 
                LEFT JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento'
                LEFT JOIN fluxostatus mf ON mf.idfluxo = ms.idfluxo AND mf.idfluxostatus = e.idfluxostatus
                 JOIN "._DBCARBON."._status s ON mf.idstatus = s.idstatus
				LEFT JOIN eventosla es ON es.ideventotipo = e.ideventotipo
						AND e.prioridade = es.prioridade
						AND e.servico = es.servico
				WHERE idevento = '#pkid'";
		/*
		 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
		 */
		include_once("../inc/php/controlevariaveisgetpost.php");
		
		if(empty($_1_u_evento_idpessoa)){
			$_1_u_evento_idpessoa=$_SESSION["SESSAO"]["IDPESSOA"];
		}
		
		$eventoTipo = filter_input(INPUT_GET, 'eventotipo');
		$calendario = filter_input(INPUT_GET, 'calendario');
		$idmodulo   = filter_input(INPUT_GET, 'idmodulo');
		$modulo     = filter_input(INPUT_GET, 'modulo');
		$inicio     = filter_input(INPUT_GET, 'inicio');
		$fim        = filter_input(INPUT_GET, 'fim');
	  
		$subevento  = false;
		$dataclick  = str_replace("/", "-", filter_input(INPUT_GET, 'dataclick'));
		$horainicio = roundUpToMinuteInterval(new DateTime(now));
		$horafim    = roundUpToMinuteInterval(new DateTime(now));
		$horafim->modify('+1 hour');
		$newdate    = date_create()->format('d/m/Y');
		
		if(empty($idevento)) 
		{
			$_1_u_evento_ideventotipo   = (empty($eventoTipo)) ? '' : $eventoTipo;
			$calendario                 = (empty($calendario)) ? '' : $calendario;
			$_1_u_evento_repetirate     = '';
			$_1_u_evento_fimsemana      = 'N';
			$_1_u_evento_periodicidade  = '';
			$_1_u_evento_idsgdoc        = '';
		}
		
		$evento		= traduzid("eventotipo", "ideventotipo", "eventotipo", $idevento);
		
		if (!empty($idevento)) 
		{        
			$sql = "SELECT s.idstatus
					  FROM evento e JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.tipoobjeto = 'ideventotipo'
					  JOIN fluxostatus mf ON mf.idfluxo = ms.idfluxo 
					  JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus AND s.statustipo = 'INICIO'
					 WHERE e.idevento = '".$idevento."'";
			
			$res = d::b()->query($sql);

			$r = mysqli_fetch_assoc($res);
			$tokeninicial = $r['idstatus'];
			
			$sqk="SELECT s.statustipo AS posicao,
						 ordem,
						 r.idfluxostatuspessoa,
						 mf.fluxo
					FROM fluxostatuspessoa r JOIN evento e ON e.idevento = r.idmodulo AND r.modulo = 'evento'
					JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento'
					JOIN fluxostatus mf ON mf.idfluxo = ms.idfluxo 
					JOIN "._DBCARBON."._status s ON mf.idstatus = s.idstatus 
					 AND r.idfluxostatus = mf.idfluxostatus AND ms.idobjeto = ".$_1_u_evento_ideventotipo."
				   WHERE e.idevento = ".$idevento."
					 AND r.tipoobjeto = 'pessoa'
					 AND r.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"];
			
			$rek=d::b()->query($sqk) or die("Erro ao buscar status inicial do usuario: ". mysqli_error(d::b()));
			$rok=mysqli_fetch_assoc($rek);
			if($rok['posicao']=="INICIO")
			{
				$sqk1="SELECT idstatus,ordem
						 FROM  fluxostatus  
						WHERE idobjeto = ".$_1_u_evento_ideventotipo." AND modulo = 'evento'
						  AND (posicao  !=  'INICIO' or posicao is null) 
						  AND ordem > ".$rok['ordem']." order by ordem limit 1";
				$rek1=d::b()->query($sqk1) or die("Erro ao buscar proximo status apos a leitura: ". mysqli_error(d::b()));
				$rok1=mysqli_fetch_assoc($rek1);

				$arrSt = explode(",",$rok['fluxo']);  

				if(!empty($arrSt['0']))
				{
					$idst=$arrSt['0'];
					$_x_u_fluxostatuspessoa_idfluxostatuspessoa=$rok['idfluxostatuspessoa'];
					$_x_u_fluxostatuspessoa_ideventostatus=$idst;
					$inocultar='N';
					atualizaEventoStatus($_x_u_fluxostatuspessoa_idfluxostatuspessoa,$_x_u_fluxostatuspessoa_ideventostatus,$inocultar,'false');
				}
			}
		}
		
		function roundUpToMinuteInterval(\DateTime $dateTime, $minuteInterval = 30) 
		{
			return $dateTime->setTime(
				$dateTime->format('H'),
				ceil($dateTime->format('i') / $minuteInterval) * $minuteInterval, 0
			);
		}
	
		function getCamposVisiveisC($inideventotipo)
		{
			$sql = "SELECT DISTINCT(t.col) as col, t.rotulo, t.prompt, t.code, c.datatype, t.ord, c.dropsql
					  FROM eventotipocampos t join "._DBCARBON."._mtotabcol c on (c.col=t.col AND c.tab='evento')
					 WHERE t.ideventotipo=".$inideventotipo."
					   AND (t.visivel='Y' or t.col in('inicio','prazo'))
					   AND t.ord is not null
				       AND c.rotpsq is not null order by t.ord,t.rotulo";

			$rts = d::b()->query($sql) or die("getCamposVisiveis: ". mysqli_error(d::b()));

			$arrtmp = array();
			while ($r = mysqli_fetch_assoc($rts)) {
				$arrtmp[$r["ord"]]["col"] = $r["col"];
				$arrtmp[$r["ord"]]["rotulo"] = $r["rotulo"];  
				$arrtmp[$r["ord"]]["prompt"] = $r["prompt"];
				$arrtmp[$r["ord"]]["code"] = $r["code"];
				$arrtmp[$r["ord"]]["datatype"] = $r["datatype"];
				$arrtmp[$r["ord"]]["dropsql"] = $r["dropsql"];
			}

			return $arrtmp;
		}
		
		$sql="SELECT * FROM eventotipo t WHERE ideventotipo=".$_1_u_evento_ideventotipo;
        $res = d::b()->query($sql) or die("Erro ao buscar informações do EventoTipo: ". mysqli_error(d::b()));
        $row=mysqli_fetch_assoc($res);
        $anonimo = $row['anonimo'];
		
		?>
		<style>
		
			fieldset.scheduler-border {
				border: 1px solid #eee !important;
				padding: 8px;
				margin: 0 0 1.5em 0 !important;
				-webkit-box-shadow:  0px 0px 0px 0px #000;
				box-shadow:  0px 0px 0px 0px #000;
			}

			legend.scheduler-border {
				font-size: 11px !important;
				font-weight: bold !important;
				text-align: left !important;
				text-transform:uppercase;
			}
			legend {
				border-bottom:none;
				margin-bottom:0px !important;
			}
			#mceu_4{
				top: 24px !important;
			}
			.multiselects{
				width:100% !important;
			}
			.messageboxok{
				width:150px;
				border:1px solid #349534;
				background:#C9FFCA;
				padding:3px;
				font-weight:bold;
				color:#008000;
			}
			.messageboxerror{
				width:150px;
				border:1px solid #CC0000;
				background:#F7CBCA;
				padding:3px;
				font-weight:bold;
				color:#CC0000;
			}
			
			.btn-success.disabled{
				background-color: #5cb85c;
				border-color: #4cae4c;
				opacity: .65;
				border: 1px solid transparent;
			}
			
			.btn-success-signature {
				color: #fff;
				background-color: #296292;
				border-color: #337ab7;
				border: 1px solid transparent;
			}
			.btn {
				border: 1px solid transparent;
			}			
			.btn-warning {
				color: #fff;
				background-color: #f0ad4e;
				border-color: #eea236;
			}
			.informacoes {
				text-transform: uppercase;
				font-weight: bold;
				width: 140px;
			}
			.evento {
				font-weight: bold;
				font-size: 18px;
				text-align: left;
			}
			.status {
				text-transform: uppercase;
				text-align: right;
				padding-top: 20px;
			}
			.btn-success.disabled {
				background-color: #5cb85c;
				border-color: #4cae4c;
				opacity: .65;
				border: 1px solid transparent;
			}
		</style>
		
		<table>
			<tr>
				<td>
					<table class="tbrepheader">
						<tr>
							<td style="width:70%;">
								<span class="evento"><?=traduzid("eventotipo", "ideventotipo", "eventotipo", $_1_u_evento_ideventotipo);?></span>
								<br />
								ID: <?=$idevento;?>
							</td>
							<td class="status"> Status: <? 
								if(!empty($_1_u_evento_idevento))
								{
									$sqls="SELECT s.rotulo,
												  mf.ordem,
												  mf.fluxoocultar
											 FROM evento e JOIN fluxo ms ON e.ideventotipo = ms.idobjeto AND ms.modulo = 'evento'
											 JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo AND mf.idfluxostatus = e.idfluxostatus
											 JOIN "._DBCARBON."._status s on s.idstatus = mf.idstatus
											WHERE e.idevento=".$_1_u_evento_idevento."  
										 ORDER BY mf.ordem DESC LIMIT 1";
									$rest = d::b()->query($sqls) or die("Erro ao buscar status do evento: ".mysqli_error(d::b()));
									$rowst = mysqli_fetch_assoc($rest);
									echo $rowst['rotulo'];
								}
								?></td>
						</tr>
					</table><br>
				</td>
			</tr>
		</table>
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Informações</legend>
		</fieldset>	
		<table class='normal'>
			<? $arrcab = getCamposVisiveisC($_1_u_evento_ideventotipo);
			foreach($arrcab as $ord => $value)
			{
				?>
				<tr>
					<td class="informacoes"><?=$value['rotulo']?>:</td>
					<td class="res">
						<? 
						$fvar = "_1_u_evento_".$value['col'];
						if(($_1_u_evento_posicao == 'CANCELADO' || $_1_u_evento_posicao == 'FIM' || $_1_u_evento_posicao == 'CONCLUIDO') && $value['col'] == 'prazo'){
							echo 'Concluído';
						}elseif($value['col'] == 'idpessoaev' && $$fvar != ""){
							$sql = "SELECT nome
									  FROM pessoa 
									 WHERE idpessoa =".$$fvar;

							$rts = d::b()->query($sql) or die("getPessoa: " . mysqli_error(d::b()));
							$r = mysqli_fetch_assoc($rts);
							echo $r['nome'];
							
						}elseif($value['col'] == 'idequipamento' && $$fvar != ""){
							$sql = "SELECT concat(tag,' - ',descricao) as descrtag
									  FROM tag
									 WHERE idtag =".$$fvar;

							$rts = d::b()->query($sql) or die("getTagsEquipamento: " . mysqli_error(d::b()));
							$r = mysqli_fetch_assoc($rts);
							echo $r['descrtag'];
							
						}elseif($value['col'] == 'idsgdoc' && $$fvar != ""){
							$sql = "SELECT concat(idregistro,'-',titulo) as titulo
									  FROM sgdoc 
									 WHERE idsgdoc =".$$fvar;

							$rts = d::b()->query($sql) or die("getTagsDoc: " . mysqli_error(d::b()));
							$r = mysqli_fetch_assoc($rts);
							echo $r['titulo'];
						}elseif($value['col'] == 'idsgsetor' && $$fvar != ""){
							$sql = "SELECT setor
									  FROM sgsetor
									 WHERE idsgsetor =".$$fvar;

							$rts = d::b()->query($sql) or die("sgsetor: " . mysqli_error(d::b()));
							$r = mysqli_fetch_assoc($rts);
							echo $r['setor'];

						}elseif($value['col'] == 'idsgdepartamento' && $$fvar != ""){
							$sql = "SELECT departamento
									  FROM sgdepartamento 
									 WHERE idsgdepartamento =".$$fvar;

							$rts = d::b()->query($sql) or die("sgdepartamento: " . mysqli_error(d::b()));
							$r = mysqli_fetch_assoc($rts);
							echo $r['departamento'];

						} else {
							$fvar=$$fvar;
							echo strip_tags($fvar);
						}
						?>
					</td>
				</tr>
			<? } ?>
			<? if($row['evcor']=='Y'){ ?>
				<tr>
					<td class="informacoes">Cor:</td>
					<td><?=$_1_u_evento_cor;?></td>
				</tr>
			<? } 
			
			if($row['rnc']=='Y' and !empty($_1_u_evento_idevento)){
			?>
				<tr>
					<td class="informacoes">RNC:</td>
					<td>
						<? if(empty($_1_u_evento_idsgdoc)){ ?>						
							<input type="text" id="motivornc" value="" style="" vnulo="" class="ui-autocomplete-input size20" autocomplete="off">
							<i id="criarrnc" class="fa fa-plus-circle verde btn-lg pointer" onclick="fnovornc(<?=$_1_u_evento_idevento?>);" title="Criar novo RNC"></i>
						<? }else{ ?>
							<a title="Documento RNC" href="javascript:janelamodal('../?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc?>')">
								<?=traduzid("sgdoc","idsgdoc","titulo",$_1_u_evento_idsgdoc);?>
							</a>
							<a title="Documento RNC" href="javascript:janelamodal('../?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc?>')">
								<?= $_1_u_evento_idsgdoc?>
							</a>
						<? } ?>
					</td>
				</tr>
			<? } 
			
			 if(!empty($_1_u_evento_ideventotipo))
			 {
				$sqsp="SELECT * FROM eventosla where ideventotipo = ".$_1_u_evento_ideventotipo;
				$rsp = d::b()->query($sqsp) or die("Erro ao configuracao de SLA : " . mysql_error() . "<p>SQL:".$sqsp);
				$qtdsla= mysqli_num_rows($rsp);

				if($qtdsla>0)
				{ ?>
					<tr>
						<td class="informacoes">Serviço:</td>
						<td><?=$_1_u_evento_servico?></td>
					</tr>
					<tr>
						<td class="informacoes">Prioridade:</td>
						<td><?=$_1_u_evento_prioridade?></td>
					</tr>
				<? }
			 } ?>		 
		</table>
		<p>&nbsp;</p>	
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Comentários</legend>
		</fieldset>	
		<table class='normal'>
			<tr> 
				<td class="header">Data</td>
				<td class="header">Nome</td>	
				<td class="header">Descrição</td>			
			</tr>
			<? $sqlc="SELECT p.nomecurto,e.* , 
							et.anonimo,
							if(ev.idpessoa = p.idpessoa, 'Y', 'N') as dono
					   FROM modulocom e JOIN pessoa p on(p.usuario=e.criadopor)
					   JOIN evento ev on ev.idevento = e.idmodulo AND e.modulo = 'evento'
					   JOIN eventotipo et on et.ideventotipo = ev.ideventotipo
					  WHERE ev.idevento=".$_1_u_evento_idevento."
					    AND e.status = 'ATIVO' order by e.criadoem desc";
			$resc=d::b()->query($sqlc) or die("Erro ao buscar comentarios.".$sqlc);

			while($rowc=mysqli_fetch_assoc($resc))
			{
				if ($rowc["anonimo"] == 'Y' && $rowc["dono"] == 'Y'){ $rowc["nomecurto"] = '<i><b>ANÔNIMO</b></i>'; }
				?>
				<tr>
					<td><?=dmahms($rowc['criadoem'])?></td>
					<td><?=$rowc['nomecurto']?></td>
					<td><?=nl2br($rowc['descricao'])?></td>
				</tr>
			<? } ?>
		</table>
		<p>&nbsp;</p>
		
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Participantes</legend>
		</fieldset>	
		
		<table class="normal"> 
			<tr> 
				<td class="header">Status</td>
				<td class="header">Nome</td>
				<td class="header">Setor</td>	
				<td class="header">Assinatura</td>					
			</tr>
			<?=listaPessoaEventoRelatorio()?>			
		</table>	
		<hr style="background-color: solid silver;">	
		<p>&nbsp;</p>
		<?
		if(!empty($_timbradorodape)){?>
			<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="90px" width="100%"></div>
		<?}?>
	</body>
</html>


