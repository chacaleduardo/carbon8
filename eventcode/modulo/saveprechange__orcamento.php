<?


    $arrpb=$_SESSION["arrpostbuffer"];
    reset($arrpb);
    //Gerar PARTIDA para qualquer linha que realize insert na lote
    while (list($linha, $arrlinha) = each($arrpb)) {
	while (list($acao, $arracao) = each($arrlinha)) {
	    if($acao=="i"){
		while (list($tab, $arrtab) = each($arracao)){
		    //Se for tabela de notafiscalitens deletar o array
		    if($tab=="orcamentoitem"){
			//print_r($_SESSION["arrpostbuffer"][$linha]);
                        $idtipoteste=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idtipoteste"];
                        $idorc=$_SESSION["arrpostbuffer"][$linha][$acao][$tab]["idorcamento"];
                        
                        $_sql="select * from prodserv where idprodserv=".$idtipoteste;
                        $res = mysql_query($_sql) or die("Erro ao retornar os testes: ".mysql_error());
                        $qtdrows1= mysql_num_rows($res);
                        $row = mysql_fetch_assoc($res);
                        
                        //Buscar contrato com descontos do cliente
			$sqldesc="select
                                            d.tipodesconto
                                            ,round(if(d.desconto IS NULL,0,d.desconto),2) as desconto
					from contratopessoa cp,contrato c,desconto d,orcamento o 
					where c.status = 'ATIVO'
					and d.idtipoteste = ".$idtipoteste."
					AND d.idcontrato = c.idcontrato
					and cp.idcontrato = c.idcontrato
					and cp.idpessoa = o.idpessoa 
					and o.idorcamento =".$idorc;
			$resdesc = mysql_query($sqldesc) or die("Erro ao buscar contrato com descontos do cliente sql=".$sqldesc);
			$rowdesc=mysql_fetch_assoc($resdesc);
			
			if($rowdesc["tipodesconto"]=='V' AND !empty($rowdesc["desconto"])){
				$desconto="0";
				$valoritem=$rowdesc["desconto"];
                                $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["desconto"]=$desconto;
			
			}elseif($rowdesc["tipodesconto"]=='P' AND !empty($rowdesc["desconto"])){
			
				$valoritem=$row["vlrvenda"];
                                $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["desconto"]=$rowdesc["desconto"];
			
			}else{
				$desconto="0";
				$valoritem=$row["vlrvenda"];
                                $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["desconto"]=$desconto;
			}
                        $_SESSION["arrpostbuffer"][$linha][$acao][$tab]["valorun"]=$valoritem; 
                       
		    }
		}
	    }
	}
    }
 //print_r( $_SESSION["arrpostbuffer"]);
//die;