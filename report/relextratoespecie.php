<?
error_reporting(E_ALL);
require_once("../inc/php/validaacesso.php");
if(!empty($_GET["reportexport"])){
    session_cache_expire(1);
    session_cache_limiter("private");
    ob_start();//não envia nada para o browser antes do termino do processamento
}
//ini_set("display_errors","1");
//error_reporting(E_ALL);

################################################## Atribuindo o resultado do metodo GET

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idagencia 	= $_GET["idagencia"];

if(empty($_GET["_idempresa"])){
	$idempresa = $_SESSION['SESSAO']['IDEMPRESA'];
} else {
	$idempresa = $_GET["_idempresa"];
}

//$clausula .= " vencimento > '2009-01-01' and ";

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$month = date("m",strtotime($vencimento_1));
	
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulad .= " and (cp.datareceb between '" . $dataini ."' and '" .$datafim ."') ";
            //$clausulad .= " (c.datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}
/*
 * colocar condição para executar select
 */
 if($_GET and !empty($clausulad)){


            $data=date("Y/m/d");
  

?>
<html>
<head>
<title>Sislaudo - Recebimentos</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

</head>

<link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<style>
    
a.btbr20{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr20:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr20:hover
{
    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr20:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
</style>

<style data-cke-temp="1" type="text/css" media="screen">

a.btbr20{
	display: block;
}

</style>
<body>

<?

$agencia=traduzid("agencia","idagencia","agencia",$idagencia);
?>

<table class="tbrepheader">
	
	<tr>
		<td rowspan="3" style="width:200;">
                    <!--
                    <img src="../img/repheader.png">
                    -->
                </td>
		<td class="header">Relatório Contas Recebidas - (<?=$agencia?>)</td>
		<td></td>
	</tr>
	<tr>
		<td class="subheader">(Período entre <?=$vencimento_1?> e <?=$vencimento_2?>)</td>
	</tr>	
</table>
<br>
<table class="normal" style="font-size: 10px;">
	<tr class="header">
            <td align="center">Espécie</td>
            <td align="center">Valor</td>
      	</tr>
	<?
        $sqlx="select * from plantel where status='ATIVO' and idempresa = ".$idempresa." and prodserv='Y' order by plantel";
        $resx =  d::b()->query($sqlx) or die("Falha ao pesquisar especies: " . mysqli_error() . "<p>SQL:". $sqlx);
        while($rowx=mysqli_fetch_assoc($resx)){
            
            $sql="select sum(valor) as valor from (
                        SELECT 
                              sum(cp.valor) as valor
                            FROM
                               `nf` `n`
                                JOIN `natop` `np` ON (`np`.`idnatop` = `n`.`idnatop` and np.natop like('%VENDA%') and np.natop not like('%DEVOLU%'))
                                join contapagaritem i on(  i.tipoobjetoorigem='nf' and i.idobjetoorigem=n.idnf)
                                join contapagar cp on(cp.idcontapagar = i.idcontapagar )
                            WHERE
                                (`n`.`tiponf` = 'V')
                              and cp.tipo='C'
                              ".$clausulad."                       
                            and cp.tipoespecifico='AGRUPAMENTO'
                            and cp.status='QUITADO'
                            and cp.idempresa = ".$idempresa."
                            and cp.idagencia=".$idagencia."
                            and exists ( select 1 from  `nfitem` `ni` , `prodservformula` `psf`, `plantel` `plt` where `ni`.`idnf` = `n`.`idnf` and ni.vlritem > 0          
                                        and `psf`.`idprodservformula` = `ni`.`idprodservformula`
                                        and `plt`.`idplantel` = `psf`.`idplantel` and plt.idplantel= ".$rowx['idplantel'].") 
		UNION
                        SELECT
                            sum(cp.valor) as valor 
                            FROM
                                `nf` `n`
                                         JOIN `natop` `np` ON (`np`.`idnatop` = `n`.`idnatop` and np.natop like('%VENDA%') and np.natop not like('%DEVOLU%'))
                                  join contapagar cp on(cp.idobjeto =n.idnf and cp.tipoobjeto='nf')
                            WHERE
                                (`n`.`tiponf` = 'V')
                                and cp.tipo='C'
                                 ".$clausulad."
                                and cp.status='QUITADO'
                                and cp.tipoespecifico='NORMAL'
                                and cp.idempresa = ".$idempresa."
                                and cp.idagencia=".$idagencia."
                                and exists ( select 1 from  `nfitem` `ni` , `prodservformula` `psf`, `plantel` `plt` where `ni`.`idnf` = `n`.`idnf` and ni.vlritem > 0          
                                                and `psf`.`idprodservformula` = `ni`.`idprodservformula`
                                                and `plt`.`idplantel` = `psf`.`idplantel` and plt.idplantel= ".$rowx['idplantel'].")
                     ) as u";


            echo "<!--";
            echo $sql;
            echo "-->";
 

            $res =  d::b()->query($sql) or die("Falha ao pesquisar contas: " . mysqli_error() . "<p>SQL: $sql");
            $ires = mysqli_num_rows($res);

            while ($row = mysqli_fetch_array($res)){

?>	
	
	<tr class="respreto">
            <td align="center"><?=$rowx["plantel"]?></td>
            <td align="right"><?=$bold?><?=number_format(tratanumero($row["valor"]), 2, ',', '.')?> <?=$boldf?></td>
	</tr>
<?	
            }//while ($row = mysqli_fetch_array($res)){
	
        }
?>
								
</table>

	<?
        $sqlx="select * from plantel where status='ATIVO' and idempresa = ".$idempresa." and prodserv='Y' order by plantel";
        $resx =  d::b()->query($sqlx) or die("Falha ao pesquisar especies: " . mysqli_error() . "<p>SQL:". $sqlx);
        while($rowx=mysqli_fetch_assoc($resx)){
       
            $sql="select sum(valor) as valor, idvendedor from (
                SELECT 
                      sum(cp.valor) as valor,n.idvendedor
                    FROM
                       `nf` `n`
                        JOIN `natop` `np` ON (`np`.`idnatop` = `n`.`idnatop` and np.natop like('%VENDA%') and np.natop not like('%DEVOLU%'))
                        join contapagaritem i on(  i.tipoobjetoorigem='nf' and i.idobjetoorigem=n.idnf)
                        join contapagar cp on(cp.idcontapagar = i.idcontapagar )
                    WHERE
                        (`n`.`tiponf` = 'V')
                      and cp.tipo='C'
                      and n.comissao='Y'
                      ".$clausulad."                        
                    and cp.tipoespecifico='AGRUPAMENTO'
                    and cp.status='QUITADO'
                    and cp.idempresa = ".$idempresa."
                    and cp.idagencia=".$idagencia."
                    and exists ( select 1 from  `nfitem` `ni` , `prodservformula` `psf`, `plantel` `plt` where `ni`.`idnf` = `n`.`idnf` and ni.vlritem > 0          
                                and `psf`.`idprodservformula` = `ni`.`idprodservformula`
                                and `plt`.`idplantel` = `psf`.`idplantel` and plt.idplantel= ".$rowx['idplantel'].") 
                                 group by n.idvendedor
                UNION
                SELECT
                    sum(cp.valor) as valor,n.idvendedor
                    FROM
                        `nf` `n`
                                 JOIN `natop` `np` ON (`np`.`idnatop` = `n`.`idnatop` and np.natop like('%VENDA%') and np.natop not like('%DEVOLU%'))
                          join contapagar cp on(cp.idobjeto =n.idnf and cp.tipoobjeto='nf')
                    WHERE
                        (`n`.`tiponf` = 'V')
                        and cp.tipo='C'
                        and n.comissao='Y'
                        ".$clausulad." 
                        and cp.status='QUITADO'
                        and cp.tipoespecifico='NORMAL'
                        and cp.idempresa = ".$idempresa."
                        and cp.idagencia=".$idagencia."
                        and exists ( select 1 from  `nfitem` `ni` , `prodservformula` `psf`, `plantel` `plt` where `ni`.`idnf` = `n`.`idnf` and ni.vlritem > 0          
                                        and `psf`.`idprodservformula` = `ni`.`idprodservformula`
                                        and `plt`.`idplantel` = `psf`.`idplantel` and plt.idplantel= ".$rowx['idplantel'].")
                                        group by n.idvendedor
             ) as u   group by u.idvendedor";


            echo "<!--";
            echo $sql;
            echo "-->";
 

            $res =  d::b()->query($sql) or die("Falha 2 ao pesquisar contas por vendedor: " . mysqli_error() . "<p>SQL: $sql");
            $ires = mysqli_num_rows($res);

            if($ires>0){
                ?>        
<p>&nbsp;</p>
<br>
<table class="normal" style="font-size: 10px;">
        <tr class="header">
            <td align="center" colspan="2"><?=$rowx["plantel"]?></td>
      	</tr>
	    <tr class="header">
            <td align="center">Responsável</td>
            <td align="center">Valor</td>
      	</tr>
<?   

            while ($row = mysqli_fetch_array($res)){
?>	
            <tr class="respreto">
                <td align="center"><?=traduzid("pessoa","idpessoa","nome",$row['idvendedor'])?></td>
                <td align="right"><?=$bold?><?=number_format(tratanumero($row["valor"]), 2, ',', '.')?> <?=$boldf?></td>            
            </tr>
<?	
            }//while ($row = mysqli_fetch_array($res)){
                ?>
								
                </table>
                <p>&nbsp;</p>
<?	
        }
    }

}////if($_GET){
?>
</body>
</html>