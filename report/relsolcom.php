
<?
include_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
?>
<html>
	<head>
		<title>Solicitação de Compras</title>
		<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
		<link href="../inc/css/sislaudo.css" media="all" rel="stylesheet" type="text/css" />
		<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
		<script src="../inc/js/functions.js"></script>
	</head>	
	<body style="margin: auto; margin-top: 0.2cm; margin-bottom: 1cm; padding: 3mm 10mm; width: 21cm;">
	
		<?
		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('solcom', 'idsolcom', $_REQUEST['idsolcom']);
		
		if($_timbrado != 'N'){
	
			$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERSERVICO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado = mysql_fetch_assoc($_restimbrado);

			$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
			$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado1 = mysql_fetch_assoc($_restimbrado1);

			$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
			$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado2 = mysql_fetch_assoc($_restimbrado2);
			
			$_timbradocabecalho = $_figtimbrado["caminho"];
			$_timbradomarcadagua = $_figtimbrado1["caminho"];
			$_timbradorodape = $_figtimbrado2["caminho"];
			
			if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}
		}

		
		if (!empty($_GET['idsolcom'])) {
			$_idsolcom = $_GET['idsolcom'];
		}else{
			die ("Não é possível imprimir Relatório");
		}	
		
		/*
		 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
		 * $pagvalcampos: Informar os Parâmetros GET que devem ser validados para compor o select principal
		 *                pk: indica Parâmetro chave para o select inicial
		 *                vnulo: indica Parâmetros secundários que devem somente ser validados se nulo ou não
		 */
		$pagvaltabela = "solcom";
		$pagvalcampos = array(
			"idsolcom" => "pk"
		);
		
		/*
		 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
		 */
		$pagsql = "SELECT * 
					 FROM solcom
					WHERE idsolcom = '#pkid'";
		/*
		 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
		 */
		include_once("../inc/php/controlevariaveisgetpost.php");
		
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
			}
			.btn-success.disabled {
				background-color: #5cb85c;
				border-color: #4cae4c;
				opacity: .65;
				border: 1px solid transparent;
			}
			.tdId{
				width: 15%;
			}
			.tdUnidade {
				width: 50%;
			}
			th {
				text-align: left;
			}
		</style>
		
		<table>
			<tr>
				<td>
					<table class="tbrepheader">
						<tr>
							<td colspan="3">
								<span class="evento">Solicitação de Compras</span>								
							</td>
						</tr>
						<tr>
							<td class="tdId">ID: <?=$_1_u_solcom_idsolcom;?></td>
							<td class="tdUnidade">UNIDADE: <?=traduzid('unidade','idunidade','unidade',traduzid("pessoa",'usuario','idunidade',$_1_u_solcom_criadopor));?></td>
							<td class="status">Status: <? 
								if(!empty($_1_u_solcom_idsolcom))
								{
									$sqls="SELECT s.rotulo
											 FROM solcom sc JOIN fluxo ms ON ms.modulo = 'solcom'
											 JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo AND mf.idfluxostatus = sc.idfluxostatus
											 JOIN "._DBCARBON."._status s on s.idstatus = mf.idstatus
											WHERE sc.idsolcom = ".$_1_u_solcom_idsolcom;
									$rest = d::b()->query($sqls) or die("Erro ao buscar status do Solcom: ".mysqli_error(d::b()));
									$rowst = mysqli_fetch_assoc($rest);
									echo $rowst['rotulo'];
								}
								?>
							</td>
						</tr>
					</table><br>
				</td>
			</tr>
		</table>
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Itens</legend>
		</fieldset>	
		<table class='normal'>		
			<? 
			$sqlSolcomItens = "SELECT si.qtdc, si.un, si.urgencia, si.obs, si.idsolmatitem, p.descr
								 FROM solcomitem si JOIN prodserv p ON p.idprodserv = si.idprodserv
								WHERE idsolcom = $_1_u_solcom_idsolcom";
			$rsSolcomItens = d::b()->query($sqlSolcomItens) or die("Erro ao configuracao de SLA : " . mysql_error() . "<p>SQL:".$sqlSolcomItens);
			?>	
			<tr>
				<th>Qtd</th>
				<th>Un</th>
				<th>Descrição</th>
				<th>Observação</th>
				<th>Urgente</th>
				<th>Solicitação de Material</th>
			</tr>
			<?			
			while($owSolcomItens = mysqli_fetch_assoc($rsSolcomItens))
			{
				?>
				<tr>
					<td><?=$owSolcomItens['qtdc']?></td>
					<td><?=$owSolcomItens['un']?></td>
					<td><?=$owSolcomItens['descr']?></td>
					<td><? if(!empty($owSolcomItens['obs'])) { echo $owSolcomItens['obs']; } else {echo  '-';} ?></td>
					<td><?=$owSolcomItens['urgencia']?></td>
					<td><? if(!empty($owSolcomItens['idsolmatitem'])) { echo $owSolcomItens['idsolmatitem']; } else {echo  '-';} ?></td>
				</tr>
			<? }  ?>		 
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
			<? $sqlc="SELECT p.nomecurto, 
							 m.criadoem,
							 m.descricao,
							 if(sc.idpessoa = p.idpessoa, 'Y', 'N') as dono
					    FROM modulocom m JOIN pessoa p ON p.usuario = m.criadopor
						JOIN solcom sc ON sc.idsolcom = m.idmodulo AND modulo = 'solcom'
					   WHERE sc.idsolcom = ".$_1_u_solcom_idsolcom."
					     AND m.status = 'ATIVO' ORDER BY m.criadoem DESC";
			$resc=d::b()->query($sqlc) or die("Erro ao buscar comentarios.".$sqlc);

			while($rowc=mysqli_fetch_assoc($resc))
			{
				if ($rowc["dono"] == 'Y'){ $rowc["nomecurto"] = '<i><b>ANÔNIMO</b></i>'; }
				?>
				<tr>
					<td><?=dmahms($rowc['criadoem'])?></td>
					<td><?=$rowc['nomecurto']?></td>
					<td><?=nl2br($rowc['descricao'])?></td>
				</tr>
			<? } ?>
		</table>
		<p>&nbsp;</p>
		<?
		if(!empty($_timbradorodape)){?>
			<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="90px" width="100%"></div>
		<?}?>
	</body>
</html>


