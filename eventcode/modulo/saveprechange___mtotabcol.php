<?
//Exemplo do arrpostbuffer $_SESSION['arrpostbuffer']["1"]["i"]["wfxfluxo"]["idmtotabcol"]){

//echo _CARBON_ROOT;

$pastaEventosMtotabcol =_CARBON_ROOT."eventcode/mtotabcol/";

if(!is_writable($pastaEventosMtotabcol)){
	echo "Erro: pasta eventos [" .$pastaEventosMtotabcol."] n&atilde;o possui permiss&otilde;es de escrita";
}

foreach($_SESSION['arrpostbuffer'] as $linha => $acao){
	
	$iud = key($acao);//verifica qual &atilde;© a a&atilde;§&atilde;o, atrav&atilde;©s da chave atual
	
	//print_r($acao[$iud]["_mtotabcol"]);
	$nomearq="";
	$nomearq = $pastaEventosMtotabcol . $acao[$iud]["_mtotabcol"]["tab"] . "__". $acao[$iud]["_mtotabcol"]["col"] ."__prompt";
	
	//verifica se vai colocar conteudo no arquivo (sera concatenado '.php' no final no nome) ou vai deletar o arquivo relacionado
	if(!empty($acao[$iud]["_mtotabcol"]["code"])){

		//cria ou substitui o arquivo
		file_put_contents($nomearq.".php", $acao[$iud]["_mtotabcol"]["code"]);
		
		//altera as permissoes
		chmod($nomearq.".php", 0750);
	}else{
		
		//a funcao glob procura por um arquivo utilizando wildcards (curingas).
		//Isto eh necessario, visto que caso o usuario retire o valor existente no campo [prompt] neste ponto nao se sabe exatamente o valor contido nele, portanto sera efetuado um loop com o nome da coluna para encontrar o arquivo
		
		$resun = $nomearq."*";
		foreach (glob($resun) as $arqtmp) {
			if(filesize($arqtmp)>0){
				unlink($arqtmp);
				//echo $arqtmp;
			}
		}
	}
}

?>