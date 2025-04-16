<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
}
$year = date('Y');
$yearEnd =  strtotime($year. '-12-31');
$year2 = date('Y-m-d');
$yearNow =  strtotime($year2);

$atual = date('Y-m-d');
$ultimodia= date("Y-m-t"); 
$mês = date('m');
/*
echo($atual. "<br>");
echo($ultimodia. "<br>");
echo($mês.'<br>');
die();
*/




$grupo = rstr(8);

re::dis()->hMSet('cron:geraspedinventario',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'geraspedinventario', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

if($atual==$ultimodia){ 
	echo "Ultimo dia do mês";

$sql="select * from empresa where status='ATIVO'";
$res=d::b()->query($sql)or die("Erro ao buscar EMPRESAS sql:".$sql);
while($row=mysqli_fetch_assoc($res)){



    $sqlu="update spedh010 set status= 'INATIVO' where ano=year(now()) and mes='".$mês."' and status='ATIVO' and idempresa = ".$row['idempresa']."";
    $resp=d::b()->query($sqlu)or die("Erro ao buscar produtos sql:".$sqlu);

/*
            "select  0 as idprodservformula ,p.idprodserv,
                        sum(f.qtd) as qtdf, 
                      	round(ifnull((l.vlrlote)*(sum(f.qtd)),0),2) as vlritem
                        ,rtrim(ltrim(p.codprodserv)) as codprodserv, 
                        round(ifnull((l.vlrlote)*(sum(f.qtd)),0),2) as vlrcompra
						,p.descr,p.un
 					from prodserv p join lote l join lotefracao f join unidade u left join nfitem ni on(ni.idnfitem = l.idnfitem)
 					where l.status ='APROVADO'
					and l.idprodserv = p.idprodserv
 					 and p.un is not null
 					and p.tipo = 'PRODUTO'
					and p.idempresa = ".$row['idempresa']."
					-- and exists (select 1 from prodservformulains i join prodservformula f on(f.idprodservformula =i.idprodservformula and f.status='ATIVO') where i.idprodserv=p.idprodserv)
					and p.comprado = 'Y'				
                    and u.idtipounidade = 3
                    and f.idunidade = u.idunidade
                    and f.status='DISPONIVEL'
                    and f.idlote =l.idlote
                    and f.qtd >0				
 					group by p.idprodserv
					UNION
                    select  l.idprodservformula,
							p.idprodserv,
							ROUND(sum(f.qtd),2) as qtdf,
							ROUND(sum((f.qtd) * (ifnull(l.vlrlote,0))),2) as vlritem,
							rtrim(ltrim(p.codprodserv)) as codprodserv,
							ifnull(l.vlrlote,0) as vlrcompra,
							p.descr,p.un
 					from prodserv p join lote l join prodservformula v join lotefracao f join unidade u
 					where v.idprodservformula = l.idprodservformula
					and l.status ='APROVADO'
					and l.idprodserv = p.idprodserv
                    and u.idtipounidade in(3,5,21)
                    and f.idunidade = u.idunidade
                    and f.status='DISPONIVEL'
                    AND f.idlote =l.idlote
                    and f.qtd >0
					-- and p.un is not null
 					and p.idempresa = ".$row['idempresa']."
					and (p.venda = 'Y' or p.fabricado = 'Y')
					and p.tipo = 'PRODUTO'
					group by  l.idprodservformula,p.idprodserv";
*/

		$sqlp="select  0 as idprodservformula ,p.idprodserv,
				sum(f.qtd) as qtdf, 
				round(ifnull((l.vlrlote)*(sum(f.qtd)),0),2) as vlritem
				,rtrim(ltrim(p.codprodserv)) as codprodserv, 
				round(ifnull((l.vlrlote),0),2) as vlrcompra
				,p.descr,p.un
				from prodserv p 
				join lote l on(l.status ='APROVADO'	and l.idprodserv = p.idprodserv and l.idprodservformula is null)
				join lotefracao f on ( f.status='DISPONIVEL' and f.idlote =l.idlote and f.qtd >0 and f.idunidade=p.idunidadeest)            
				where  p.tipo = 'PRODUTO'
				and p.idempresa = ".$row['idempresa']."							
				group by p.idprodserv
				UNION
				select  l.idprodservformula,
					p.idprodserv,
					ROUND(sum(f.qtd),2) as qtdf,
					ROUND(sum((f.qtd) * (ifnull(l.vlrlote,0))),2) as vlritem,
					rtrim(ltrim(p.codprodserv)) as codprodserv,
					ifnull(l.vlrlote,0) as vlrcompra,
					p.descr,p.un
				from prodserv p 
				join lote l on ( l.status ='APROVADO' and l.idprodserv = p.idprodserv)
				join prodservformula v on(v.idprodservformula = l.idprodservformula)
				join lotefracao f on( f.idunidade = p.idunidadeest
								and f.status='DISPONIVEL'
								AND f.idlote =l.idlote
								and f.qtd >0)

				where 
				p.idempresa =   ".$row['idempresa']."
				and p.tipo = 'PRODUTO'
				group by  l.idprodservformula,p.idprodserv";

		
 			$resp=d::b()->query($sqlp)or die("Erro ao buscar produtos sql:".$sqlp);
 			$H10=0;
 			while($rowp=mysqli_fetch_assoc($resp)){
 				$H10=$H10+1;
 				//"|REG|COD_ITEM|UNID|QTD|VL_UNIT|VL_ITEM|IND_PROD|COD_PART|TXT_COMPL|COD_CTA|
 				//$_SESSION['H010'][$H10]="|H010|".$rowp['codprodserv']."|".$rowp['un']."|".number_format($rowp['qtdf'], 2, ',','')."|".number_format($rowp['vlrcompra'], 2, ',','')."|".number_format($rowp['vlritem'], 2, ',','')."|0||".$row['descr']."|0511|".number_format($rowp['vlritem'], 2, ',','')."|\n";
                
                $sqli="INSERT INTO spedh010
						(idempresa,idprodservformula,idprodserv,ano,mes,codprodserv,un,qtd,
						vlrcompra,vlritem,descr,vlritemir,criadopor,criadoem,alteradopor,alteradoem)
                	VALUES
						(".$row['idempresa'].",'".$rowp['idprodservformula']."','".$rowp['idprodserv']."','".$year."','".$mês."','".$rowp['codprodserv']."','".$rowp['un']."','".number_format($rowp['qtdf'], 2, '.','')."',
						'".number_format($rowp['vlrcompra'], 2, '.','')."','".number_format($rowp['vlritem'], 2, '.','')."','".$row['descr']."','".number_format($rowp['vlritem'], 2, '.','')."','crontab',now(),'crontab',now())";
                
				$resi=d::b()->query($sqli)or die("Erro ao inserir registro spedh010  sql:".$sqli);		
 			} 
}//while($row=mysqli_fetch_assoc($res)){

}
re::dis()->hMSet('cron:geraspedinventario',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
                            VALUES ('1', '".$grupo ."','cron', 'geraspedinventario', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

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