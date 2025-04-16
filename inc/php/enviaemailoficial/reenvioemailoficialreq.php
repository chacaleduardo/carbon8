<?php
require_once("../inc/php/validaacesso.php");
require "../inc/php/composer/vendor/autoload.php";
require_once "../inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";

ob_start();

$echosql=false;

$simulacao=false; //Para teste: Não enviar os emails

/********************************************************************************************************
 *	GVT - 26/02/2020 - Implementando reenvio de emails oficiais											*
 * 																										*
 ********************************************************************************************************/


$_usuario = 'josesousa';
$_idpessoa = 2266;

Dompdf\Autoloader::register();
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
	URL PARA TESTE
	https://sislaudo.laudolab.com.br/tmp/reenvioemailoficialreq.php?reenvio=Y&idempresa=1&destinatario=ule_tangaraserra@indea.mt.gov.br&idobjeto=62069
*/

if($_GET["reenvio"]=="Y"){
	
	$vIdobjeto = $_GET["idobjeto"];
	$vDestinatario = $_GET["destinatario"];
	$vIdempresa = $_GET["idempresa"];
	
	unset($_GET["reenvio"]);
	unset($_GET["idobjeto"]);
	unset($_GET["destinatario"]);
	unset($_GET["idempresa"]);
	
	if((!empty($vIdobjeto)) and (!empty($vDestinatario))){
		if(!empty($vIdempresa)){
			$_idempresa = $vIdempresa;
		}else{
			$_idempresa = 1;
		}
		// Consulta a comunicação externa
		$_rsql = "select * from comunicacaoext where idcomunicacaoext = ".$vIdobjeto;
		$_rres = d::b()->query($_rsql) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql);

		$_rrow = mysqli_fetch_assoc($_rres);

		// Consulta a registro de log de mailfila
		$_rsql2 = "select * from mailfila where idobjeto = ".$vIdobjeto." and tipoobjeto = 'comunicacaoext' and destinatario = '".$vDestinatario."'";
		$_rres2 = d::b()->query($_rsql2) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql2);

		$_rrow2 = mysqli_fetch_assoc($_rres2);
		
		$avids=array();
		$sqlu1 = "SELECT * FROM comunicacaoextitem WHERE idcomunicacaoext = ".$vIdobjeto;
		$resu1 = d::b()->query($sqlu1) or die("erro ao vincular buscar resultados da comunicacaoext [".mysqli_error()."] ".$sqlu1);
		while($rowu1 = mysqli_fetch_assoc($resu1)){
			$avids[] = $rowu1["idobjeto"];
		}
		//Utiliza somente o parametro _vids para filtrar os resultados a serem "impressos" 
		$_GET["_vids"]=implode(",",$avids);
		
		//Invoca a emissaoresultado
		require_once("../report/emissaoresultado.php");
		
		
		$html = ob_get_contents();

		ob_end_clean();

		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		$nomearq="resultados_".$vIdobjeto;
		$nomearqcompleto="/var/www/carbon8/upload/comunicacaoext/".$nomearq.".pdf";
		$link = str_replace("/var/www/carbon8", "", $nomearqcompleto);
		
		/*//Nomeia o arquivo e abre uma variavel também para o caminho completo, para anexar posteriormente
		$nomearq="resultados_".$newidcomunicacao;
		$nomearqcompleto="/var/www/carbon8/upload/comunicacaoext/".$nomearq.".pdf";
		$link = str_replace("/var/www/carbon8", "", $nomearqcompleto);*/

		if($downloadpdf=='Y'){
			//Exibir para o usuário
			$dompdf->stream($nomearq.".pdf");
		}else{
			//Salvar no diretório  do sistema
			$output = $dompdf->output();
			file_put_contents($nomearqcompleto,$output);
		}
	}

}
?>