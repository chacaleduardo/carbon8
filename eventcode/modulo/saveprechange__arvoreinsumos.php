<?
$arrpb=$_SESSION["arrpostbuffer"];
reset($arrpb);
//Gerar PARTIDA para qualquer linha que realize insert na lote
while (list($linha, $arrlinha) = each($arrpb)) {
	while (list($acao, $arracao) = each($arrlinha)) {
		if($acao=="i"){
			while (list($tab, $arrtab) = each($arracao)){
				//Se for tabela de lote, gerar incondicionalmente a Partida
				if($tab=="lote"){
					$_numlote = geraLote($arrtab["idprodserv"]);
					//Enviar o campo para a pagina de submit
					$_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["partida"] = $_numlote;					
				}

			}
		}
	}
}

?>