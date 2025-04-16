<?
require_once("../../inc/php/functions.php");
$sql="SELECT 
    DISTINCT(IF((`l`.`tipoobjetosolipor` = 'resultado'),
        (SELECT 
                p1.idpessoa
            FROM
                pessoacontato ps
                    JOIN
                pessoa p1 ON (ps.idcontato = p1.idpessoa)
            WHERE
                ps.idpessoa = pe.idpessoa
                    AND p1.idtipopessoa IN (1 , 12)
                    AND p1.status = 'ATIVO'),
        (SELECT 
                p1.idpessoa
            FROM
                pessoacontato ps
                    JOIN
                pessoa p1 ON (ps.idcontato = p1.idpessoa)
            WHERE
                ps.idpessoa = pel.idpessoa
                    AND p1.idtipopessoa IN (1 , 12)
                    AND p1.status = 'ATIVO'))) AS idresponsavel,
    IF((`l`.`tipoobjetosolipor` = 'resultado'),
        (SELECT 
                p1.nome
            FROM
                pessoacontato ps
                    JOIN
                pessoa p1 ON (ps.idcontato = p1.idpessoa)
            WHERE
                ps.idpessoa = pe.idpessoa
                    AND p1.idtipopessoa IN (1 , 12)
                    AND p1.status = 'ATIVO'),
        (SELECT 
                p1.nome
            FROM
                pessoacontato ps
                    JOIN
                pessoa p1 ON (ps.idcontato = p1.idpessoa)
            WHERE
                ps.idpessoa = pel.idpessoa
                    AND p1.idtipopessoa IN (1 , 12)
                    AND p1.status = 'ATIVO')) AS responsavel
FROM
    ((((((((((`lote` `l`
    JOIN `prodserv` `p` ON ((`p`.`idprodserv` = `l`.`idprodserv`)))
    LEFT JOIN `resultado` `r` ON (((`r`.`idresultado` = `l`.`idobjetosolipor`)
        AND (`l`.`tipoobjetosolipor` = 'resultado'))))
    LEFT JOIN `amostra` `a` ON ((`r`.`idamostra` = `a`.`idamostra`)))
    LEFT JOIN `pessoa` `pe` ON ((`pe`.`idpessoa` = `a`.`idpessoa`)))
    LEFT JOIN `especiefinalidade` `ef` ON ((`ef`.`idespeciefinalidade` = `a`.`idespeciefinalidade`)))
    LEFT JOIN `plantel` `pl` ON ((`pl`.`idplantel` = `ef`.`idplantel`)))
    LEFT JOIN `prodservformula` `pf` ON ((`pf`.`idprodservformula` = `l`.`idprodservformula`)))
    LEFT JOIN `plantel` `plf` ON ((`plf`.`idplantel` = `pf`.`idplantel`)))
    LEFT JOIN `pessoa` `pel` ON ((`pel`.`idpessoa` = `l`.`idpessoa`)))
    LEFT JOIN `lotefracao` `lf` ON ((`lf`.`idlote` = `l`.`idlote`)))
    where 1 ".getidempresa('l.idempresa','lote')."
    order by responsavel";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar setor prompt sql=".$sql);
$virg="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		if(!empty($row['idresponsavel'])){
			$json.=$virg.'{"'.$row['idresponsavel'].'":"'.$row['responsavel'].'"}';
			$virg=",";
		}
	}
$json.="]";
echo($json);
?>