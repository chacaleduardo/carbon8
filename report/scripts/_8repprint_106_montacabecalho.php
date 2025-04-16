<?
    /*
	 * MONTA O CABECALHO
	 */
	$conteudoexport;// guarda o conteudo para exportar para csv
	$strtabheader = "\n<tr class='header'>";
	//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
	if($_showtotalcounter == "Y"){
		$strtabheader .= "<td class='tdcounter'></td>";
	}

	foreach(array_keys($relatorios[0]) as $key => $coluna)
	{
	    if($arrRep["_filtros"][$coluna]["visres"] == 'Y'){
	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}
	    	
			$strtabheader .= "<td class='header'>" . $arrRep["_filtros"][$coluna]["rotulo"] ."</td>";
	    }	$conteudoexport.= "\"".$arrRep["_filtros"][$coluna]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
	    $_i++;
	}
	
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	$strtabheader .= "</tr>";

	/*
	 * Variaveis para cabecalho do report
	 */
	$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
	$strtabini = "\n<table class='normal'>";
	$strtabheader = $strtabheader;
?>