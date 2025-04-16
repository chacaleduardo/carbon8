<?
if($_FILES[$_fileElementName]['type'] == 'image/jpeg' || $_FILES[$_fileElementName]['type'] == 'image/jpg' || $_FILES[$_fileElementName]['type'] == 'image/png'){
	//Gera um nome generico para o arquivo e retira caracteres indesejados
	$arq_nome = nomenovoarq($_FILES[$_fileElementName]['name']); 
	$_caminhoaux = "../inc/img/";
	//concatena o caminho que foi passado via GET
	$arq_final = $_caminhoaux . $arq_nome;

	// Coloca o arquivo na pasta final
	$booupload = move_uploaded_file($_FILES[$_fileElementName]['tmp_name'], $arq_final);

	//Se a pasta nao existir ou alguma falha ocorrer
	if(!$booupload){
		header("HTTP/1.1 500 Falha ao mover arquivo");
		die("Falha ao mover o arquivo [".$arq_final."] com [".$tamanho."] bytes");
	}else{
		$sqlcert = "UPDATE frasco 
					   SET imagemfrasco = '".$arq_final."', 
                            alteradopor =  '".$_SESSION["SESSAO"]["USUARIO"]."',
                             alteradoem = now()
					  WHERE idfrasco = $_idobjeto";
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
			die("Imagem do Rótulo inserida");
		}
	}
}else{
	header("HTTP/1.1 500 Parâmetros inválidos");
	cbSetPostHeader("0","erro");
	die("Formato da Imagem Inválido");
}
?>