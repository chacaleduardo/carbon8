<?
//var_dump($_FILES[$_fileElementName]);

	//Gera um nome generico para o arquivo e retira caracteres indesejados
	$arq_nome = nomenovoarq($_FILES[$_fileElementName]['name']); 
	$_caminhoaux = "upload/firm/";
	//concatena o caminho que foi passado via GET
	$arq_final = $_caminhoaux . $arq_nome;

	//echo _CARBON_ROOT."tmp/".$arq_nome;die;
	// Coloca o arquivo na pasta finnal
	$booupload = move_uploaded_file($_FILES[$_fileElementName]['tmp_name'], _CARBON_ROOT."upload/firm/".$arq_nome);
	
	//Se a pasta nao existir ou alguma falha ocorrer
	if(!$booupload){
		header("HTTP/1.1 500 Falha ao mover arquivo");
		die("Falha ao mover o arquivo ["._CARBON_ROOT."upload/firm/".$arq_nome."] com [".$tamanho."] bytes");
	}else{
        $sqlcert = "UPDATE devicefirm SET iddevicefirm = ".$_idobjeto.", caminho = 'https://sislaudo.laudolab.com.br/".$arq_final."', alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."', alteradoem = now()
        WHERE `iddevicefirm` = ".$_idobjeto;
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
			die("Arquivo armazenado");
		}
	}

?>