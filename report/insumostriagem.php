﻿﻿<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

//Recuperar a unidade padrão conforme módulo pré-configurado
$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

if ($_REQUEST['_fds']){
			//echo 'aqui';
			$data = explode('-',$_REQUEST['_fds']);
			$data1 = $data[0];
			$data2 = $data[1];
						
		
                        $dataini = validadate($data1);
                        $datafim = validadate($data2);

                    if ($dataini and $datafim){
                        $clausulad = "and (l.producao BETWEEN '" . $dataini ."' and '" .$datafim ."')";
                    }else{
                        die ("Datas n&atilde;o V&aacute;lidas!");
                    }
			
                }else{
                    $clausulad="";
                }

    $sql="select i.idprodserv as idprodservinsumo,l.idlote,l.idprodserv,p.un,i.un as uninsumo,l.qtdajust,l.qtdajust_exp,p.descr as produto,l.partida,l.exercicio,l.qtdpedida, 
                concat(f.rotulo,'-',ifnull(f.dose,'--'),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo,
                    l.qtdpedida_exp,f.qtdpadraof,f.qtdpadraof_exp,i.descr as insumo,fi.qtdi,
                    fi.qtdi_exp,fi.qtdpd,fi.qtdpd_exp,f.qtdpadraof
            from lote l 
            join prodserv p on(p.idprodserv=l.idprodserv)
            join prodservformula f on(f.idprodservformula=l.idprodservformula)
            join prodservformulains fi on(fi.idprodservformula = f.idprodservformula)
            join prodserv i on(i.idprodserv=fi.idprodserv)
            where l.status='TRIAGEM' and fi.status='ATIVO'
             ".$clausulad."
            and l.idunidade in (".$idunidadepadrao.") ".getidempresa('l.idempresa','prodserv')." order by insumo,idprodservinsumo";
  
echo '<!--'.$sql.'-->';
$res= d::b()->query($sql) or die("Falha ao buscar Insumos : " . mysql_error() . "<p>SQL:".$sql);
$qtdr=mysqli_num_rows($res);


// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	$figurarelatorio = $figrel["logosis"];
?>

<html>
<head>
<title>Insumos da Triagem</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<style type="text/css">
   table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
</style>
</head>
<body>
    <table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
		<td class="header"><?=$_header?></td>
		<td></td>
	</tr>
	<tr>
		<td class="subheader nowrap"><h2>Insumos da Triagem</h2></td>
	</tr>
	</table>
	<br>
	<fieldset class="fldsheader">
	  <legend>Início da Impressão </legend>
	</fieldset>
	<table class="normal" style="width: 100%;">
            <tr class='header'>
                <td class='header'>Insumo</td>
                <td class='header'>Qtd</td>
                <td class='header'>Un</td>
            </tr>
 
    <?
$i=0;
$j=0;
$arr =array();
 $tvalor=0;
while ($row=mysqli_fetch_assoc($res)){
    
    if(empty($idprodserv)){$idprodserv=$row['idprodservinsumo'];}
    
     
     
    if($row['idprodservinsumo']!=$idprodserv){
        if($tvalor>0){
            $arr[$idprodserv]=$tvalor;
        }
        $idprodserv=$row['idprodservinsumo'];
        $tvalor=0;
    }
    if($row['qtdajust']>0){
        $qtdprod=$row['qtdajust'];
    }else{
        $qtdprod=$row['qtdpedida'];
    }
    $pedFilho=($qtdprod * $row['qtdi']) / $row['qtdpadraof'];
    $tvalor=$tvalor+$pedFilho;
     
}
 $arr[$idprodserv]=$tvalor;
 //print_r($arr);
 while (list($idprodserv,$value ) = each($arr)){ 
 ?>
    <tr class="res" >

        <td>
      
            <?

            echo traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
            ?>
			
        </td>
        <td>
           <?

            echo number_format(tratanumero($value), 2, ',', '.');
            ?>
        </td>
        <td>
                <? echo traduzid('prodserv', 'idprodserv', 'un', $idprodserv);?>
        </td>
    </tr>
<?   
$i++;
 } 
?>
 
        </table>
        <?
        if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
?>
	<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?=$varfooter?></legend>
	</fieldset>
</body>
</html>
	
