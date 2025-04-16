<? 
require_once("../inc/php/permissao.php");

function getFillSelectTransportadora()
{
	$arrTransportadora = [];
	$sqlTransportadora = "SELECT p.idpessoa, p.nome 
						  FROM pessoa p 
						 WHERE p.idtipopessoa = 11 
							   ".share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa")." 
						   AND status = 'ATIVO' ORDER BY nome";                
	$resTransportadora = d::b()->query($sqlTransportadora) or die("Erro ao buscar getFillSelectTransportadora <br>Sql: <pre>".mysqli_error(d::b()).$sqlTransportadora);
	while($rowTransportadora = mysqli_fetch_assoc($resTransportadora))
	{
		$arrTransportadora[$rowTransportadora['idpessoa']] = $rowTransportadora['nome'];	
	}

	return $arrTransportadora;
}

function getEmpresaemailobjeto($_idnfs)
{
	$arrEmailObjeto = [];
	$sqlemailobj = "SELECT COUNT(1) AS cont, tipoenvio, idobjeto
					  FROM empresaemailobjeto 
					 WHERE tipoobjeto = 'nf' AND idobjeto IN ($_idnfs) 
					 	   ".getidempresa('idempresa','empresa')." 
				  ORDER BY idempresaemailobjeto DESC";
	$resemailobj = d::b()->query($sqlemailobj) or die("Erro ao buscar empresaemailobjeto <br>Sql: <pre>".mysqli_error(d::b()).$sqlemailobj);
	while($rowemailobj = mysqli_fetch_assoc($resemailobj))
	{
		$arrEmailObjeto[$rowemailobj['idobjeto']]['tipoenvio'] = $rowemailobj['tipoenvio'];
	}

	return $arrEmailObjeto;
}

function getNfCotacaoPorProduto($_idcotacao)
{
	 $sqlnf = "SELECT DISTINCT(i.idprodserv) AS idprodserv,
	 				  i.qtd,
					  i.idnf
		   		 FROM nf n JOIN nfitem i
		    	WHERE n.idobjetosolipor = ".$_idcotacao." 
		    	  AND n.tipoobjetosolipor = 'cotacao'
               	  AND i.idprodserv is not null
		    		  ".getidempresa('n.idempresa','nf')."
		    	  AND i.idnf = n.idnf group by i.idprodserv" ;
	$resnf = d::b()->query($sqlnf) or die("Erro ao buscar nfs da cotacao[Por Produto]:  <br>Sql: <pre>".mysqli_error(d::b()).$sqlnf);
	$qtdnf = mysqli_num_rows($resnf);
	$arrNfItem = [];
	if($qtdnf > 0)
	{
		while($rowi = mysqli_fetch_assoc($resnf))
		{	
			array_push($arrIdProdserv, $rowi['idprodserv']);
			$arrNf[$rowi['idnf']]['idprodserv'] = $rowi['idprodserv'];
			$arrNf[$rowi['idnf']]['qtd'] = $rowi['qtd'];
			$arrNf[$rowi['idnf']]['idnf'] = $rowi['idnf'];		
		}

		$arrNf['itens'] = getNfItemCotacaoPorProduto($_idcotacao, $arrIdProdserv);
	}

	return $arrNf;
}

function getNfItemCotacaoPorProduto($_idcotacao, $_idprodservs)
{
	 $sqlnf = "SELECT p.nome,
	 				  i.qtd,
					  i.total, 
					  i.nfe, 
					  i.idnfitem, 
					  i.vlritem, 
					  i.obs, 
					  n.status, 
					  i.previsaoentrega,
					  i.idnf
				 FROM nf n,nfitem i,pessoa p
				WHERE n.idobjetosolipor = ".$_idcotacao." 
                      ".getidempresa('n.idempresa','nf')."
				  AND p.idpessoa = n.idpessoa
				  AND n.tipoobjetosolipor = 'cotacao'
				  AND i.idnf = n.idnf
				  AND i.idprodserv IN ($_idprodservs) 
			 ORDER BY p.nome" ;
	$resnf = d::b()->query($sqlnf) or die("Erro ao buscar nfs da cotacao[Por Produto]:  <br>Sql: <pre>".mysqli_error(d::b()).$sqlnf);
	$qtdnf = mysqli_num_rows($resnf);
	$arrNfItem = [];
	if($qtdnf > 0)
	{
		while($rowi = mysqli_fetch_assoc($resnf))
		{	
			$arrNfItem[$rowi['idnfitem']]['nome'] = $rowi['nome'];
			$arrNfItem[$rowi['idnfitem']]['qtd'] = $rowi['qtd'];
			$arrNfItem[$rowi['idnfitem']]['total'] = $rowi['total'];
			$arrNfItem[$rowi['idnfitem']]['nfe'] = $rowi['nfe'];
			$arrNfItem[$rowi['idnfitem']]['idnfitem'] = $rowi['idnfitem'];
			$arrNfItem[$rowi['idnfitem']]['vlritem'] = $rowi['vlritem'];
			$arrNfItem[$rowi['idnfitem']]['obs'] = $rowi['obs'];
			$arrNfItem[$rowi['idnfitem']]['status'] = $rowi['status'];
			$arrNfItem[$rowi['idnfitem']]['previsaoentrega'] = $rowi['previsaoentrega'];		
		}
	}

	return $arrNfItem;
}

function getAssinaturas($_idcotacao)
{
	 $sql = "SELECT p.idpessoa,
					p.nome,
					CASE
                        WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                        ELSE ''
                    END as dataassinatura,
					CASE
                        WHEN c.status ='ATIVO' THEN 'ASSINADO'
                        ELSE 'PENDENTE'
                    END as status
               FROM carrimbo c ,pessoa p 
              WHERE c.idpessoa = p.idpessoa
                	".getidempresa('c.idempresa','carrimbo')."
                AND c.status IN ('ATIVO','PENDENTE')
                AND c.tipoobjeto in('cotacao')
                AND c.idobjeto =".$_idcotacao."  ORDER BY nome";

	$res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 

	return $res;
}

function getSolicitacaoCompras($_idcotacao)
{
	$sqlSolcom = "SELECT n.idnf, s.idsolcom, s.criadoem, s.criadopor, s.status, u.unidade, p.nomecurto, ps.descrcurta
	 				FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf AND n.idobjetosolipor = $_idcotacao AND tipoobjetosolipor = 'cotacao'
                    JOIN prodserv ps ON ps.idprodserv = ni.idprodserv
                    JOIN solcomitem si ON si.idcotacao = n.idobjetosolipor AND si.idprodserv = ni.idprodserv
                    JOIN solcom s ON s.idsolcom = si.idsolcom
                    JOIN unidade u ON u.idunidade = s.idunidade
                    JOIN pessoa p ON p.idpessoa = s.idpessoa
                   WHERE n.status NOT IN ('REPROVADO', 'CANCELADO')";
    $resSolcom = d::b()->query($sqlSolcom) or die("Erro ao buscar arquivo  <br>Sql: <pre>".mysqli_error(d::b()).$sqlSolcom);
	return $resSolcom;
}

function getDominio($_idempresa)
{
	$sqldominio = "SELECT em.idemailvirtualconf, em.idempresa, ev.email_original AS dominio, em.tipoenvio
					 FROM empresaemails em
					 JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
					WHERE em.idempresa = $_idempresa
					  AND ev.status = 'ATIVO'";
                                            
	$resdominio = d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa  <br>Sql: <pre>".mysqli_error(d::b()).$sqldominio);
	$rowdominio = mysqli_fetch_assoc($resdominio);

	return $rowdominio;
}

function getTipoprodserv()
{
	$sql = "SELECT idtipoprodserv,tipoprodserv 
			  FROM tipoprodserv 
			 WHERE status = 'ATIVO'
		  ORDER BY tipoprodserv";

	$res = d::b()->query($sql) or die("getTipoprodserv:  <br>Sql: <pre>".mysqli_error(d::b()).$sql);

	$arrret=array();
	while($r = mysqli_fetch_assoc($res)){

		$arrret[$r["idtipoprodserv"]]["tipoprodserv"]=$r["tipoprodserv"];     
	}
	return $arrret;
}



function getContaItem($_idcotacao, $_idprodservs = NULL)
{
	if(!empty($_idprodservs))
	{
		$Where = " AND pc.idprodserv IN (".$_idprodservs.")";
		$join = " JOIN prodservcontaitem pc ON pc.idcontaitem = c.idcontaitem";
		$col = ", pc.idprodserv";
	} else {
		$join = " JOIN objetovinculo ov ON ov.idobjeto = '$_idcotacao' AND ov.tipoobjeto = 'cotacao' AND ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem'";
	}

	$sq = "SELECT c.idcontaitem, c.contaitem $col
			 FROM contaitem c $join
			WHERE c.status = 'ATIVO' $Where	
			and c.idempresa=".cb::idempresa()."		
		 ORDER BY c.contaitem";

	$rq = d::b()->query($sq) or die("Erro ao consultar Tipoprodserv.  <br>Sql: <pre>".mysqli_error(d::b()).$sq);
    $arr = array();

	if(!empty($_idprodservs))
	{
		while($r = mysqli_fetch_assoc($rq))
		{
			$arr[$r["idcontaitem"]]["contaitem"] = $r["contaitem"];
		}
	} else {
		while($r = mysqli_fetch_assoc($rq))
		{
			$arrMarcados[$r['idnf']]['idprodserv'] = $r['idobjeto'];
			$arrMarcados[$r['idnf']]['nome'] = $r['nome'];
		}
    }

	return $arr;
}

function getTipoItem($_idcotacao, $_idempresa)
{
	$sqlSolCom = "SELECT CONCAT(e.sigla,' - ', s.idsolcom) AS siglaidsolcom, 
						 s.idsolcom,
						 s.criadoem, 
						 si.idsolcomitem, 
						 si.qtdc, 
						 si.un, 
						 CONCAT(e2.sigla,' - ', p.descr) AS descr, 
						 si.obs, 
						 si.urgencia,
						 si.dataprevisao,
						 si.idprodserv,
						 c.contaitem, 
						 pe.nomecurto
					FROM solcom s JOIN solcomitem si ON s.idsolcom = si.idsolcom 
					JOIN prodserv p ON p.idprodserv = si.idprodserv
					JOIN prodservcontaitem pc ON pc.idprodserv = p.idprodserv
					JOIN objetovinculo ov ON ov.idobjetovinc = pc.idcontaitem AND ov.tipoobjetovinc = 'contaitem'
					JOIN objetovinculo ov2 ON ov2.idobjetovinc = p.idtipoprodserv AND ov2.tipoobjetovinc = 'contaitemtipoprodserv' AND ov2.idobjeto = $_idcotacao and ov2.tipoobjeto = 'cotacao'
					JOIN contaitem c ON c.idcontaitem = ov.idobjetovinc
					JOIN pessoa pe ON pe.idpessoa = s.idpessoa
					JOIN empresa e ON e.idempresa = s.idempresa
					JOIN empresa e2 ON e2.idempresa = p.idempresa
				   WHERE s.status IN ('APROVADO', 'CONCLUIDO') AND (si.status = 'PENDENTE')
					 AND ov.idobjeto = $_idcotacao and ov.tipoobjeto = 'cotacao'
					 AND p.idempresa = $_idempresa
				GROUP BY si.idprodserv
				ORDER BY c.contaitem";
	$resSolCom = d::b()->query($sqlSolCom) or die("Erro ao consultar Itens Solcom:  <br>Sql: <pre>".mysqli_error(d::b()).$sqlSolCom);

	return $resSolCom;
}
?>