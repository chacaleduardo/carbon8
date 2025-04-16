<?
require_once("../inc/php/functions.php");

/*
cbSetPostHeader("0","aut") => Usuário possui Certificado Digital porém não tem Cookie da senha
cbSetPostHeader("1","aut") => Conteúdo assinado com sucesso
cbSetPostHeader("-1","aut") => Senha do Certificado Digital incorreta
cbSetPostHeader("-2","aut") => Não foi possível pegar o conteúdo do Certificado Digital
*/

$idpessoa = $_POST["idpessoa"];
$modulo = $_POST["modulo"];
$idpagina = $_POST["idpagina"];
$idcarrimbo = $_POST["idcarrimbo"];
$status = $_POST["status"];
$alteradoem = $_POST["alteradoem"];
$_idempresa = (!empty($_GET["_idempresa"])) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];

$criadoem = date('Y-m-d H:i:s');

if(empty($idpagina) OR empty($modulo) OR empty($idpessoa) OR empty($idcarrimbo)){
	cbSetPostHeader("-3","aut");
	die("Erro, não foram enviados os parâmetros necessários para o carrimbo.");
}

$content = $idpagina.$modulo.$idpessoa.$criadoem;

$result = consultarAssinarCertificado($idpessoa, $content, $_idempresa);

//-------------------------------------------------

// Espaço para atualizar qualquer tabela.

//-------------------------------------------------

if(!inserirAtualizarCarrimbo($status, $result, $idcarrimbo, $idpagina, false, $alteradoem, $_idempresa)){
	cbSetPostHeader("-3","aut");
	die("Erro ao atualizar carrimbo");
}else{
	//LTM - 05-10-2020: Gravar o arquivo com assinatura do Veterinário assim que ele assinar.
	if($modulo == 'amostratra'){
		// JWT com as informações do usuário que fazia o envio dos emails oficiais. Esse JWT não expira.
		$jwt_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXNsYXVkbyIsImlkbHAiOiI5IiwiaWR0aXBvcGVzc29hIjoiMSIsImlkcGVzc29hIjoiMjI2NiIsInVzdWFyaW8iOiJqb3Nlc291c2EiLCJpZGVtcHJlc2EiOiIxIn0.csC1v_813FLCMtGJKv8iypyYfP_aKzbdMoKovK35vmU";

		// Monta parâmetros que ser?o enviados por GET
		$content = http_build_query(array(
			'idamostra' => $idpagina,
			'unidadepadrao' => 9,
			'provisorio' => 'Y',
			'gravaarquivo' => 'Y'
		));
		
		// Cria a requisição com o método, conteúdo e seta um header com o JWT que será recuperado na validaacesso.php
		$context = stream_context_create(array(
			'http' => array(
			'method' => 'GET',
			'header'  => 'jwt: '.$jwt_,
			'content' => $content,
			),
		));

		// Envia a requisição para o arquivo de envio de email oficial com os parâmetros necessários
		$result = file_get_contents('https://sislaudo.laudolab.com.br/report/tra.php?'.$content, null, $context);		
		//$result = file_get_contents('http://laudolabcarbon.desenvolvimento.com/report/tra.php?'.$content, null, $context);	
		$tamanho = (!empty(filesize($result))) ? filesize($result) : 0;
		$parts = explode('/', $result);
		$nomeArquivo = end($parts);

		$sqlarquiv = "insert into arquivo (idempresa, tipoarquivo,caminho,tamanho,tamanhobytes,idpessoa,idobjeto,tipoobjeto,nome) 
			values (".$_idempresa.",'AMOSTRA','".addslashes($result)."','".tradbytes($tamanho)."',".$tamanho.",".$_SESSION["SESSAO"]["IDPESSOA"].",".$idpagina.",'amostra','".$nomeArquivo."')";
		$booins = mysql_query($sqlarquiv) or die ("Erro ao inserir arquivo: ".$sqlarquiv ." ". mysqli_error(d::b()));
		//Grava o novo arquivo no banco
	}

	cbSetPostHeader("1","aut");
	die("Assinado com Sucesso");
}

function tradbytes($bytes)
{
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}
?>