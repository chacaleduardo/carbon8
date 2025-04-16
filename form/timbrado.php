<style>
	body {
		margin-top: 2.5cm;
		margin-left: 0cm;
		margin-right: 0cm;
		margin-bottom: 2cm;
	}
	#_timbradocabecalho { 
		position: fixed; 
		height: 2cm;
		top: 0cm;
		left: 0cm;
		right: 0cm;
	}

	#_timbradomarcadagua { 
		position: fixed; 
		bottom: 2cm;
		width: 500px; 
		height: 300px; 
		right: 0cm;
		opacity: .2;
		z-index: -1;
	}
	#_timbradorodape { 
		position: fixed; 
		bottom: 0cm;
		left: 0cm;
		right: 0cm;
		height: 2cm;
	}
</style>
<?
//Validação realizada por causa da Multiempresa.
if(!empty($nnfe)) //reldetalhenf.php
{
	$idempresa = getImagemRelatorio('notafiscal', 'idnotafiscal', $idnotafiscal);	
} elseif(!empty($_1_u_nf_idnf)){
	$idempresa = getImagemRelatorio('nf', 'idnf', $_1_u_nf_idnf);	
} elseif($_GET['_idempresa']){
	$idempresa = ' AND idempresa = '.$_GET['_idempresa'];
} else {
	$idempresa = getidempresa('idempresa','empresa');
}

$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:$idempresa;

if($_timbrado != 'N'){
	
	$_sqltimbrado="select tipoimagem,caminho from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem in ('".$_timbradoheader."','IMAGEMMARCADAGUA','IMAGEMRODAPE')";
	$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
	
	while($_figtimbrado=mysql_fetch_assoc($_restimbrado)){
		switch($_figtimbrado["tipoimagem"]){
			case $_timbradoheader:
				$_timbradocabecalho = $_figtimbrado["caminho"];
				break;
			case "IMAGEMMARCADAGUA":
				$_timbradomarcadagua = $_figtimbrado["caminho"];
				break;
			case "IMAGEMRODAPE":
				$_timbradorodape = $_figtimbrado["caminho"];
				break;
			default:
				$_timbradocabecalho = "";
				$_timbradomarcadagua = "";
				$_timbradorodape = "";
				break;
		}
	}
	
	if(!empty($_timbradocabecalho)){?>
		<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="100%" width="100%"></div>
	<?}
	if(!empty($_timbradomarcadagua)){?>
		<div id="_timbradomarcadagua"><img src="<?=$_timbradomarcadagua?>" height="100%" width="100%"></div>
	<?}
	if(!empty($_timbradorodape)){?>
		<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="100%" width="100%"></div>
	<?}
}

//die($sqlfig1);
?>