<? //Hermes 05/04/2013
include_once("../../../php/functions.php");
//ini_set("display_errors","1");
//error_reporting(E_ALL);
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

if(empty($idnotafiscal)){
 die("É necessário informar o ID da NF.");
}

$sql="select xmlret,SUBSTRING(idnfe,4) as idnfe from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error()." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['xmlret'])){
	die("Não encontrado o xml da NF");
}


	ob_end_clean();//não envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	//$infilename = ereg_replace("[^A-Za-z0-9s.]", "", $_header);	
	//gera o csv
	header("Content-type: text/xml; charset=iso-8859-1");
	header("Content-Disposition: attachment; filename=".$row['idnfe']."-procNfe.xml");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo($row['xmlret']);
