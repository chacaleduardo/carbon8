<?
require_once("../inc/php/functions.php");
require_once("controllers/log_controller.php");

error_reporting(E_ERROR);
ini_set("display_errors","1");

$_idobjeto = empty($_POST["idobjeto"]) ? $_GET["idobjeto"] : $_POST["idobjeto"];
$_tipoobjeto = empty($_POST["tipoobjeto"]) ? $_GET["tipoobjeto"] : $_POST["tipoobjeto"];
$_tipoarquivo = empty($_POST["tipoarquivo"]) ? $_GET["tipoarquivo"] : $_POST["tipoarquivo"];
$_tipoarquivo = empty($_tipoarquivo) ? "ANEXO" : $_tipoarquivo;
$_tipoarquivo = ($_tipoarquivo == 'undefined') ? "ANEXO" : $_tipoarquivo;
$_caminho = empty($_POST["caminho"]) ? "../upload/" : "../".$_POST["caminho"];
$_fileElementName = "file";
$erro = false;

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
if(!empty($_POST) or !empty($_FILES)){
	
	if(empty($_idobjeto) or empty($_tipoobjeto) or empty($_tipoarquivo) or empty($_caminho)){
		header("HTTP/1.1 500 Parâmetros inválidos");
		$arrres = "Parâmetros inválidos";
		$erro = true;
	}else{

		//Evento para pre-upload
		$arq_preupload = _CARBON_ROOT."eventcode/arquivo/preupload__".$_tipoobjeto."__".$_tipoarquivo.".php";
		if(file_exists($arq_preupload)) {
			include_once($arq_preupload);
		}

		//tamanho do arquivo
		$tamanho = $_FILES[$_fileElementName]["size"];
		
		//Gera um nome generico para o arquivo e retira caracteres indesejados
		$arq_nome = nomenovoarq($_FILES[$_fileElementName]['name']); 

		//concatena o caminho que foi passado via GET
		$arq_final = $_caminho . $arq_nome;
		
		// Coloca o arquivo na pasta finnal
		$booupload = move_uploaded_file($_FILES[$_fileElementName]['tmp_name'], $arq_final);
		
		//Se a pasta nao existir ou alguma falha ocorrer
		if(!$booupload){
			header("HTTP/1.1 500 Falha ao mover arquivo");
			$arrres = "Falha ao mover o arquivo [".$arq_final."] com [".$tamanho."] bytes";
			$erro = true;
		}else{

			//insere no banco os dados do arquivo
			$sqlarquiv = "INSERT INTO arquivo (idempresa, tipoarquivo,caminho,tamanho,tamanhobytes,idpessoa,idobjeto,tipoobjeto,nome,nomeoriginal) 
			VALUES (".cb::idempresa().",'".$_tipoarquivo."','".$arq_final."','".tradbytes($tamanho)."',".$tamanho.",".$_SESSION["SESSAO"]["IDPESSOA"].",".$_idobjeto.",'".$_tipoobjeto."','".$arq_nome."','".addslashes($_FILES[$_fileElementName]['name'])."')";

			$booins = mysql_query($sqlarquiv);
			
			//se houver algum erro deletar o arquivo enviado
			if(!$booins){
				//deleta o arquivo gerado
				@unlink($arq_nome);
				header("HTTP/1.1 500 Erro ao gravar dados do arquivo no DB");
				$arrres = "Erro ao gravar dados do arquivo no DB.\n Entre em contato com TI.";
				$erro = true;

				$dadosLog = [
					'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
					'sessao' => session_id(),
					'tipoobjeto' => $_tipoobjeto,
					'idobjeto' => $_idobjeto,
					'tipolog' => 'anexo',
					'log' => 'Erro:' . $sqlarquiv,
					'status' => '',
					'info' => mysql_error(),
					'criadoem' => "NOW()",
					'data' => "NOW()"
				];
				LogController::inserir($dadosLog);
			}
		}
		$lastInsertIdArquivo=mysql_insert_id();
		//Evento para pos-upload
		$arq_posupload = _CARBON_ROOT."eventcode/arquivo/posupload__".$_tipoobjeto."__".$_tipoarquivo.".php";
		if(file_exists($arq_posupload)) {
			include_once($arq_posupload);
		}
	}
}

if($erro == true)
{
	echo $arrres;

} elseif(!empty($_idobjeto)){
	 $sqlarq = "select a.*, dmahms(a.criadoem) as datacriacao,p.nomecurto as criadopor
				from arquivo a 
				left join pessoa p on(p.idpessoa = a.idpessoa)
				where 
					a.tipoobjeto = '".$_tipoobjeto."'
					and a.idobjeto = ".$_idobjeto."
					and tipoarquivo = '".$_tipoarquivo."'
				order by idarquivo asc";

	//die($sqlarq);

	$res = d::b()->query($sqlarq);
	
	if(!$res){
		header("HTTP/1.1 404 Erro ao recuperar arquivos");
		$arrres = array("Error" => "Erro ao recuperar arquivos");
		die("Erro ao recuperar arquivos");
	}else{

		$numarq= mysqli_num_rows($res);
		$arrColunas = mysqli_fetch_fields($res);
		$arrres=array();
		if($numarq>0){
			while($r = mysqli_fetch_assoc($res)){
				$arr = array();
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arr[$col->name] = $r[$col->name];
				}
				$arrres[]=$arr;
			}
		}else{
			null;
		}
	}
	//print_r($arrres);
	$jArquivos = json_encode($arrres);
	echo $jArquivos;
}

?>
