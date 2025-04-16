<?require_once("../inc/php/functions.php");

$sql="select r.idresultado,r.idamostra,a.estexterno,r.quantidade,p.descr,r.alerta,r.positividade,fnStripTags(r.descritivo) as descritivo,r.interfrase,r.gmt,r.idt
 , IF(r.idsecretaria>0,'Y','N') as oficial,ps.nome as assinadopor
 from resultado r,amostra a,prodserv p,resultadoassinatura ass,pessoa ps
 where r.idamostra = a.idamostra
 and p.idprodserv = r.idtipoteste
 and ass.idresultado = r.idresultado
 and ass.idpessoa = ps.idpessoa
 and r.status = 'ASSINADO'
and ass.criadoem between '2018-10-01 01:00:00' and '2018-10-30 23:50:00'
 and a.idpessoa in (17,3993,4529,3995,3992) group by r.idresultado";
$resa=d::b()->query($sql) or die("Erro ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sql);

$indesejaveis= array("-", "\"", ",", ";","
");


while($row=mysqli_fetch_assoc($resa)){
 //strip_tags(str_replace('<br>','',$row['descritivo']))   
$conteudoexport.=($row['idresultado'].",".$row['idamostra'].",".$row['quantidade'].",".$row['descr'].",".$row['alerta'].",".$row['positividade'].",".str_replace($indesejaveis, "", $row['descritivo']).",".$row['interfrase'].",".$row['gmt'].",".$row['idt'].",".$row['oficial'].",".$row['assinadopor'].'
    '); 

}

ob_end_clean();//não envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$infilename = 'resultados';
	//gera o csv
	header("Content-type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo($conteudoexport);
        
/*
select a.idamostra,a.exercicio,a.idregistro,dataamostra,subtipoamostra as tipoamostra,nroamostra,lote,nucleoamostra,idade,lacre,galpao,regoficial,
responsavel,responsavelof,responsavelofcrmv,datacoleta
from resultado r,amostra a,subtipoamostra st,resultadoassinatura ass
 where st.idsubtipoamostra = a.idsubtipoamostra
 and a.idpessoa in (17,3993,4529,3995,3992)
 and r.idamostra = a.idamostra
 and r.status='ASSINADO'
 and r.idresultado = ass.idresultado
 and ass.criadoem between '2018-07-01 01:00:00' and '2018-07-31 23:50:00' group by a.idamostra;
 
 
 select a.idamostra,re.idresultado,re.nome,re.sp,re.titer 
from resultadoelisa re,resultado r,amostra a ,resultadoassinatura ass
where re.idresultado = r.idresultado
and re.nome in('CV','GMN','MAX','MIM','SD')
 and r.status = 'ASSINADO'
  and r.idresultado = ass.idresultado
 and ass.criadoem between '2018-07-01 01:00:00' and '2018-07-31 23:50:00' 
 and  r.idamostra = a.idamostra
 and a.idpessoa in (17,3993,4529,3995,3992) group by r.idresultado;
 */