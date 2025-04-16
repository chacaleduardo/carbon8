<?
//require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
error_reporting(E_ERROR);
ini_set("display_errors","1");
//a
$_idobjeto = empty($_POST["idobjeto"])?$_GET["idobjeto"]:$_POST["idobjeto"];
$_tipoobjeto = empty($_POST["tipoobjeto"])?$_GET["tipoobjeto"]:$_POST["tipoobjeto"];
$_tipoarquivo = empty($_POST["tipoarquivo"])?$_GET["tipoarquivo"]:$_POST["tipoarquivo"];
$_tipoarquivo = empty($_tipoarquivo)?"ANEXO":$_tipoarquivo;
$_tipoarquivo =($_tipoarquivo=='undefined')?"ANEXO":$_tipoarquivo;
$_caminho = empty($_POST["caminho"])?"../upload/":"../".$_POST["caminho"];
$_fileElementName = "file";

//Cria um nome novo para o arquivo enviado para evitar espaços ou caracteres estranhos no nome do arquivo
function nomenovoarq($infilename){
	/* Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$novofilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);

	//Coloca todos os caracteres em minusculo
	$novofilename = strtolower($novofilename);
	
	//Para se resguardar no caso de a pessoa enviar o mesmo arquivo 2 vezes e substituir o anterior, o ponto (.) do nome do arquivo será concatenado uma sequencia numerica antes do nome original + underline '_'
	//se o arquivo possui 2 pontos, os 2 serao substituidos
	$idunico = "_".substr(md5(uniqid(time())),0,5).".";//somente os 5 primeiros caracteres
	$novofilename = str_replace(".",$idunico,$novofilename);

	return $novofilename;
} 

//Rà³tulo do tamanho
function tradbytes($bytes){
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}

//verifica se o arquivo foi enviado via post
if(!empty($_POST) or !empty($_POST['file'])){
	
	$result = [];
	$data = str_replace(" ","+",$_POST['file']); //O envio do dado pelo XMLHttpRequest tende a trocar o + por espaço, por isso a necessidade de substituir. 
	$name = md5(time().uniqid()); 
	$path = "../upload/{$name}.jpg";

	//data
	$data = explode(',', $data);
	
	//Save data
	file_put_contents($path, base64_decode(trim($data[1])));

    $imginfo = getimagesize($path);
    // header("Content-type: {$imginfo['mime']}");
    // readfile($path);


    $sqlarquiv = "INSERT INTO arquivo (idempresa, tipoarquivo,caminho,tamanho,tamanhobytes,idpessoa,idobjeto,tipoobjeto,nome,nomeoriginal) 
			VALUES (".cb::idempresa().",'".$_tipoarquivo."','".$path."','".tradbytes($imginfo['bits'])."',".($imginfo['bits']).",".$_SESSION["SESSAO"]["IDPESSOA"].",".$_idobjeto.",'".$_tipoobjeto."','".$name.".jpg"."','".md5(time().uniqid())."')";

    $booins = mysql_query($sqlarquiv);
	
	//Print Data
	$result['img'] = $path;
	echo json_encode($result, JSON_PRETTY_PRINT);
}

?>
