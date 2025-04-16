<?
//var_dump($_FILES[$_fileElementName]);

if($_FILES[$_fileElementName]['type'] == 'application/x-pkcs12'){
	//Gera um nome generico para o arquivo e retira caracteres indesejados
	$arq_nome = nomenovoarq($_FILES[$_fileElementName]['name']); 
	$_caminhoaux = "../upload/cert/";
	//concatena o caminho que foi passado via GET
	$arq_final = $_caminhoaux . $arq_nome;
	
	// Coloca o arquivo na pasta finnal
	$booupload = move_uploaded_file($_FILES[$_fileElementName]['tmp_name'], $arq_final);
	
	//Se a pasta nao existir ou alguma falha ocorrer
	if(!$booupload){
		header("HTTP/1.1 500 Falha ao mover arquivo");
		die("Falha ao mover o arquivo [".$arq_final."] com [".$tamanho."] bytes");
	}else{
		$sqlcert = "INSERT INTO novocertificadodigital (idobjeto,objeto,nome,caminho,criadopor,criadoem)
			VALUES(".$_idobjeto.",'".$_tipoobjeto."','".$arq_nome."','".$arq_final."','".$_SESSION["SESSAO"]["USUARIO"]."',now())";
		$booins = mysql_query($sqlcert);
				
		//se houver algum erro deletar o arquivo enviado
		if(!$booins){
			//deleta o arquivo gerado
			@unlink($_FILES[$_fileElementName]['tmp_name']);
			header("HTTP/1.1 500 Erro ao gravar dados do arquivo no DB");
			//die("Erro ao gravar dados do arquivo no Banco de Dados:\n<br>".mysql_error()."\n<br>Sql:".$sqlarquiv);
			die("Erro ao gravar dados do arquivo no DB");
		}else{
			cbSetPostHeader("1","alert");
			die("Certificado armazenado");
		}
	}
}else{
	header("HTTP/1.1 500 Parâmetros inválidos");
	cbSetPostHeader("0","erro");
	die("Formato de Certificado Inválido");
}
?>