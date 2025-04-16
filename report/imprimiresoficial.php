<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
<?
include_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);

if($_POST){
    require_once("../inc/php/cbpost.php");
}

$exercicio 	= $_GET["exercicio"];
$idregistro_1 	= $_GET["idregistro_1"];
$idregistro_2	= $_GET["idregistro_2"];
$status= $_GET['status'];
$dataamostra_1 	= $_GET["dataamostra_1"];
$dataamostra_2 	= $_GET["dataamostra_2"];
$nome=$_GET['_fts'];

$arrdata=explode('-', $_GET['_fds']) ;

if (!empty($arrdata['0']) or !empty($arrdata['1'])){
    $dataini = validadate($arrdata['0']);
    $datafim = validadate($arrdata['1']);

    if ($dataini and $datafim){
        $clausulad .= " and (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."') ";
        $clausulacli .= " and (n.emissao  BETWEEN '" . $dataini ."' and '" .$datafim ."') ";
    }else{
            die ("Datas n&atilde;o V&aacute;lidas!");
    }
}else{
    die("Favor informar o periodo para pesquisa.");
}

if(!empty($idregistro_1) and !empty($idregistro_2)){
    $clausulad .=" and a.idregistro BETWEEN ".$idregistro_1." AND ".$idregistro_2;

}

if(!empty($exercicio)){
    $clausulad .=" and a.exercicio ='".$exercicio."'";
}

if(!empty($nome)){
    $clausulad .=" and p.nome like ('%".$nome."%') ";
    $clausulacli .= " and p.nome like ('%".$nome."%')";
}

$clausuladexiste =" not exists ";

if($status=="ABERTO"){
	$clausuladstatus=" ";
}else{
	$clausuladstatus=" NOT  ";	
}

/*
 * colocar condição para executar select
 */
if($_GET and !empty($clausulad)){
    
    $sql = "
select  
 sb.idpessoa,sb.nome,sb.idsecretaria,sb.secretaria,sb.idnucleo,sb.dataamostra,sb.nucleoamostra,sb.lote,sb.exercicio,sb.idresultado, sum(sb.qtdresultado) as qtdresultado
from 
(
select 	
	 a.idpessoa,p.nome,s.idpessoa as idsecretaria,s.nome as secretaria,a.idnucleo,a.dataamostra,a.nucleoamostra,a.lote,a.exercicio,r.idresultado,a.idregistro
	,(select count(*) from resultado rr where rr.idresultado = r.idresultado) as qtdresultado
	from 		
	(resultado r 
	,amostra a
	,pessoa p
	,pessoa s
	) 					
	where p.idpessoa = a.idpessoa	
		and s.impresultado = 'S'	
		and s.idpessoa = r.idsecretaria
		and ".$clausuladexiste." (select 1 from controleimpressaoitem  ce where ce.status ='ATIVO' and via >= 1 and ce.oficial = 'S'  and ce.idresultado = r.idresultado)
		and r.status = 'ASSINADO'
		and r.idamostra = a.idamostra 
				".$clausulad."
		and r.idsecretaria != ''
		and a.idempresa = ".cb::idempresa()." 
		
) as sb
where   ".$clausuladstatus."  exists  (SELECT 1
                            FROM
                                    resultado rr , amostra aa
                            WHERE
                                rr.idsecretaria != ''
                                AND rr.idamostra = aa.idamostra
                                and aa.idnucleo = sb.idnucleo
                                and aa.dataamostra < DATE_ADD(sb.dataamostra, INTERVAL 4 DAY)
                                AND rr.status IN ('FECHADO' , 'ABERTO', 'PROCESSANDO', 'AGUARDANDO')
                            )
		group by sb.idpessoa,sb.idsecretaria order by sb.nome,sb.secretaria";
    echo "<!--";
    echo $sql;
    echo "-->";
    if (!empty($sql)){
        $res =d::b()->query($sql) or die("Falha ao pesquisar resultados: " . mysqli_error(d::b()) . "<p>SQL: $sql");
        $ires = mysqli_num_rows($res);	
    }
}//if($_GET and !empty($clausulad)){
?>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />

  <title>Sislaudo - Envio Emails Oficiais</title>      
<style type="text/css">
   table { page-break-inside:auto; width:100% }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
	@media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }
	footer {
    position: fixed;
    bottom: 0;
  }
}
footer {
  font-size: 9px;
  color: #f00;
  text-align: center;
}
.impressora {

    padding: 4px !important;
    cursor: pointer;
}
</style>

<script language="javascript">


</script>

<body>
<?
if(empty($exercicio)){
    $exercicio = date('Y');
}

if($_GET){
?>
<fieldset ><legend>Resultados para impressão (OFICIAIS) </legend>

<table class="normal" style="font-size: 10px;" id="inftable">
    <tr class="header" >
        <td align="center">Qtd. Res.</td>
        <td align="center">Cliente</td>	
        <td align="center">Secretaria</td>
        <td align="center">Imprimir</td>	
    </tr>
<?while($row=mysqli_fetch_assoc($res)){?>	
    <tr class="respreto" style="height: 20px;">
        <td align="center"><?=$row['qtdresultado']?></td>	
        <td align="center"><?=$row['nome']?></td>	
        <td align="center"><?=$row['secretaria']?></td>	
        <td align="center">	
        <?if($status!="SUCESSO"){?>	
            <a class="fa fa-print fa-lg cinza pointer hoverazul"  target="blank" title="Imprimir Res. Oficial"  href="emissaoresultado.php?exercicio=<?=$row['exercicio']?>&idsecretaria=<?=$row['idsecretaria']?>&idpessoa=<?=$row['idpessoa']?>&mostracabecalho=N&impoficial=Y"></a>
        <?}?>
        </td>	
    </tr>

<?}?>				
</table>
</fieldset>
<?
}//if($_GET){


                
	if(!empty($nome)){
            
	}	
	

        
        if(!empty($_SESSION["SESSAO"]["IDEMPRESA"])){
		
		$clausulacli .= "and n.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
		
	}
	
	$sql="select c.idempresa
                    ,c.exercicio
                    ,c.idnotafiscal
                    ,c.numerorps
                    ,c.nnfe
                    ,c.idpessoa
                    ,c.oficial
                    ,c.nome
                    ,c.qcontroleimp 
                    ,c.quanttestes
                    ,CASE 
                            WHEN c.qcontroleimp = 0 THEN
                                    '#FF6347' -- vermelho
                            WHEN  c.qcontroleimp < c.quanttestes THEN
                                    '#FFFF00' -- amarelo
                            WHEN  c.qcontroleimp = c.quanttestes THEN
                                    '#1E90FF' -- azul
                            WHEN  c.qcontroleimp > c.quanttestes THEN
                                    '#1E90FF' -- azul
                    END as cor
                    ,c.emissao
                    ,c.dmaemissao
            from(	select 
                            b.*
                            ,CASE	b.oficial
                                    WHEN 'N' THEN
                                            (select count(*) 
                                                    from notafiscal n, notafiscalitens i, resultado r 
                                                    where i.idnotafiscal = n.idnotafiscal 
                                                            and r.idresultado = i.idresultado 
                                                            and n.numerorps = b.numerorps)
                                    WHEN 'S' THEN
                                            (select count(*) 
                                                    from notafiscal n, notafiscalitens i, resultado r 
                                                    where i.idnotafiscal = n.idnotafiscal 
                                                            and r.idresultado = i.idresultado 
                                                            and n.numerorps = b.numerorps
                                                            and if((ifnull(r.idsecretaria,'') = ''),'N','S') = 'S')

                            END as quanttestes


                    from (
                            select 
                                    a.*
                                    ,
                                    (select count(*) 
                                            from notafiscal n,notafiscalitens ni,controleimpressaoitem ci
                                            where ci.status = 'ATIVO'
                                            and ci.oficial = convert(a.oficial USING latin1)
                                            and ci.idresultado = ni.idresultado
                                            and ni.idnotafiscal = n.idnotafiscal 
                                            and n.numerorps = a.numerorps) as qcontroleimp 

                            from (									
                                            select n.idempresa AS idempresa
                                                    ,n.exercicio AS exercicio
                                                    ,n.idnotafiscal AS idnotafiscal
                                                    ,n.numerorps AS numerorps
                                                    ,n.nnfe
                                                    ,n.idpessoa AS idpessoa
                                                    ,'N' AS oficial
                                                    ,p.nome AS nome
                                                    ,n.emissao AS emissao
                                                    ,dma(n.emissao) AS dmaemissao
                                            from 
                                                    notafiscal n 
                                                    join notafiscalitens ni 
                                                    join resultado r 
                                                    join pessoa p
                                            where p.impresultado='S'
                                                    and  p.idpessoa = n.idpessoa 
                                                    and r.status = 'ASSINADO'
                                                    and r.idresultado = ni.idresultado
                                                    and ni.idnotafiscal = n.idnotafiscal
                                                    ".$clausulacli."						

                            group by  idempresa,exercicio,idnotafiscal,numerorps,idpessoa,oficial,nome,emissao,dmaemissao									

                            ) a
                    )b
            )c  order by numerorps";
if($_GET){
    //die($sql);
    if (!empty($sql)){
        $res =d::b()->query($sql) or die("Falha ao pesquisar resultados para impressão aos clientes: " . mysqli_error(d::b()) . "<p>SQL: $sql");
        $ires = mysqli_num_rows($res);	
    }
?>
    <p><br></p>
<fieldset ><legend>Resultados para impressão (CLIENTES) </legend>

<table class="normal" style="font-size: 10px;" id="inftable">
    			
    <tr class="header" >
        <td align="center">Nº Nfe</td>
        <td align="center">Oficial</td>	
        <td align="center">Cliente</td>
        <td align="center">Impressos</td>
        <td align="center">Resultados</td>
        <td align="center">Emissão</td>
        <td align="center">Listagem</td>
        <td align="center">Imprimir</td>
        <td align="center">Danfe</td>
        <td align="center">Detalhamento</td>
    </tr>
<?while($row=mysqli_fetch_assoc($res)){?>	
    <tr class="respreto" style="height: 20px; background-color: <?=$row["cor"]?>">
        <td align="center"><?=$row['nnfe']?></td>	
        <td align="center"><?=$row['oficial']?></td>	
        <td align="center"><?=$row['nome']?></td>
        <td align="center"><?=$row['qcontroleimp']?></td>
        <td align="center"><?=$row['quanttestes']?></td>
        <td align="center"><?=$row['dmaemissao']?></td>
        <td align="center">      
            <a class="fa fa-bars fa-lg cinza pointer hoverazul"  target="blank" title="Listagem de Resultados"  href="../form/controleimpresultado.php?numerorps=<?=$row['numerorps']?>&oficial=<?=$row['oficial']?>"></a>
        </td>
        <td align="center">      
            <a class="fa fa-print fa-lg cinza pointer hoverazul"  target="blank" title="Imprimir Resultado"  href="emissaoresultado.php?controle=<?=$row['numerorps']?>&chkoficial=<?=$row['oficial']?>&mostracabecalho=N"></a>
        </td>
         <td align="center">      
            <a class="fa fa-print fa-lg cinza pointer hoverazul"  target="blank" title="Imprimir Danfe" href="reldetalhenf.php?idnotafiscal=<?=$row['idnotafiscal']?>"></a>
        </td>
         <td align="center">      
            <a class="fa fa-print fa-lg cinza pointer hoverazul"  target="blank" title="Imprimir Detalhamento" href="../form/geradanfse.php?idnotafiscal=<?=$row['idnotafiscal']?>&amp;_idempresa=1"></a>
        </td>
    </tr>

<?}?>				
</table>

</fieldset>
    <br>
    <p>
    <div style="width: 300px;">
    <table class="normal" style="font-size: 10px;">
        <tr class="header" >
            <td align="center">LEGENDA</td>
        </tr>
        <tr class="respreto" style="background-color:#FF6347">
            <td align="center">Nenhum resultado impresso</td>
        </tr>
        <tr class="respreto" style="background-color:#FFFF00">
            <td align="center">Ainda possui resultado a ser impresso</td>
        </tr>
        <tr class="respreto" style="background-color:#1E90FF">
            <td align="center">Todos os resultados foram impressos</td>
        </tr>
    </table> 
    </div>
<?
}
?>
<br>
</body>
</html>

