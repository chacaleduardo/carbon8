<?
require_once("../inc/php/functions.php");

$ano= $_GET['ano']; 

if(empty($ano)){
	die("Ano  NAO ENVIADO");
}

$ano=$_GET["ano"];

$idempresa=$_GET["idempresa"];

$sqlu="update spedh010 set status= 'INATIVO' where ano='".$ano."' and status='ATIVO' and idempresa = ".$idempresa."";
$resp=d::b()->query($sqlu)or die("Erro ao buscar produtos sql:".$sqlu);


             $sqlp="select  0 as idprodservformula ,p.idprodserv,
                        sum(f.qtd) as qtdf, 
                        round(ifnull(l.vlrlote*(sum(f.qtd)),0),2) as vlritem
                        ,rtrim(ltrim(p.codprodserv)) as codprodserv, 
                        round(ifnull(l.vlrlote,0),2) as vlrcompra,p.descr,p.un
 					from prodserv p join lote l join lotefracao f join unidade u left join nfitem ni on(ni.idnfitem = l.idnfitem)
 					where l.status ='APROVADO'
					and l.idprodserv = p.idprodserv
 					and p.un is not null
 					and p.tipo = 'PRODUTO'
					and p.idempresa = ".$idempresa."
					and exists (select 1 from prodservformulains i join prodservformula f on(f.idprodservformula =i.idprodservformula and f.status='ATIVO') where i.idprodserv=p.idprodserv)
					and p.comprado = 'Y'				
                    and u.idtipounidade = 3
                    and f.idunidade = u.idunidade
                    and f.status='DISPONIVEL'
                    and f.idlote =l.idlote
                    and f.qtd >0				
 					group by p.idprodserv
					UNION
                    select l.idprodservformula,p.idprodserv
                        ,ROUND(sum(f.qtd),2) as qtdf,
                        ROUND(sum((f.qtd) * (ifnull((f.vlrcusto/f.qtdpadraof),0))),2) as vlritem,
                        rtrim(ltrim(p.codprodserv)) as codprodserv,
                        ifnull((f.vlrcusto/f.qtdpadraof),0) as vlrcompra,
                        p.descr,
                        p.un
 					from prodserv p join lote l join prodservformula v join lotefracao f join unidade u
 					where v.idprodservformula = l.idprodservformula
					and l.status ='APROVADO'
					and l.idprodserv = p.idprodserv
                    and u.idtipounidade in(3,5)
                    and f.idunidade = u.idunidade
                    and f.status='DISPONIVEL'
                    AND f.idlote =l.idlote
                    and f.qtd >0
					and p.un is not null
 					and p.idempresa = ".$idempresa."
					and (p.venda = 'Y' or p.fabricado = 'Y')
					and p.tipo = 'PRODUTO'
					group by  l.idprodservformula,p.idprodserv";
 			$resp=d::b()->query($sqlp)or die("Erro ao buscar produtos sql:".$sqlp);
 			$H10=0;
 			while($rowp=mysqli_fetch_assoc($resp)){
 				$H10=$H10+1;
 				//"|REG|COD_ITEM|UNID|QTD|VL_UNIT|VL_ITEM|IND_PROD|COD_PART|TXT_COMPL|COD_CTA|
 				$_SESSION['H010'][$H10]="|H010|".$rowp['codprodserv']."|".$rowp['un']."|".number_format($rowp['qtdf'], 2, ',','')."|".number_format($rowp['vlrcompra'], 2, ',','')."|".number_format($rowp['vlritem'], 2, ',','')."|0||".$row['descr']."|0511|".number_format($rowp['vlritem'], 2, ',','')."|\n";
                
                $sqli="INSERT INTO spedh010
						(idempresa,idprodservformula,idprodserv,ano,codprodserv,un,qtd,
						vlrcompra,vlritem,descr,vlritemir,criadopor,criadoem,alteradopor,alteradoem)
                	VALUES
						(".$idempresa.",'".$rowp['idprodservformula']."','".$rowp['idprodserv']."','".$ano."','".$rowp['codprodserv']."','".$rowp['un']."','".number_format($rowp['qtdf'], 2, '.','')."',
						'".number_format($rowp['vlrcompra'], 2, '.','')."','".number_format($rowp['vlritem'], 2, '.','')."','".$row['descr']."','".number_format($rowp['vlritem'], 2, '.','')."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                
				$resi=d::b()->query($sqli)or die("Erro ao inserir registro spedh010  sql:".$sqli);		
 			} 

			 /*
			 select p.idprodserv,p.descr,
CASE
    WHEN p.venda='Y' and p.fabricado='Y' THEN "PRODUTO ACABADO"
    WHEN p.venda='N' and p.fabricado='Y' THEN "INSUMOS"
	WHEN p.fabricado='N' and p.comprado='Y' THEN "MATERIA PRIMA"
    ELSE "INSUMOS"
END as tipo,s.ano,s.codprodserv,s.un,s.qtd,s.vlritem,s.vlritemir 
from spedh010 s join prodserv p on(p.idprodserv=s.idprodserv)
where s.ano  =2022 
and s.status='ATIVO' and s.idempresa=1;
			 */
?>