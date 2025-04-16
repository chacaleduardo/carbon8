<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["dataemissao_1"];
$vencimento_2 	= $_GET["dataemissao_2"];
$dataenvio_1 	= $_GET["dataenvio_1"];
$dataenvio_2 	= $_GET["dataenvio_2"];
$especie 	= $_GET["especie"];
$tipo 	= $_GET["tipo"];
$cliente =    $_GET["cliente"];
$produto =    $_GET["produto"];

//$clausula .= " vencimento > '2009-01-01' and ";

//print_r($_SESSION["post"]);

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulac .= " and (n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:00')"."  ";		
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}elseif(!empty($dataenvio_1) or !empty($dataenvio_2)){
	$dataini = validadate($dataenvio_1);
	$datafim = validadate($dataenvio_2);

	if ($dataini and $datafim){
		$clausulac .= " and (n.prazo  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:00')"."  ";		
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}else{
    die("Favor informar o período.");
}


if(!empty($cliente)){
	$clausulac .= " and p.nome like('%".$cliente."%') " ;
}
if(!empty($produto)){
	$clausulac .= " and s.descr like ('%".$produto."%')  " ;
}

/*
if($pesquisa=='detalhe'){
    $strdet=",cp.idpessoa";
}else{
    $strdet="";
}
 * */
 

/*
 * colocar condição para executar select
 */
if($_GET){
    
    if($tipo=='AGRUPADO'){// agrupar por CNPJ , quanto foi faturado pelo cnpj
        $sql="select p.idpessoa,i.idprodserv,n.idempresa,n.dtemissao,p.razaosocial as nome,p.cpfcnpj,i.vlritem,sum(i.qtd) as qtd, sum(i.total) as total
		    from nf n,pessoa p,nfitem i,prodserv s
		    where n.tiponf = 'V' 
		    and p.idpessoa = n.idpessoa
		    and n.status not in ('REPROVADO','CANCELADO','ORCAMENTO')
		    and n.natop like('%VENDA%')
		    and n.idnf = i.idnf
                    ".$clausulac."
		    and i.vlritem > 0
		    and i.idprodserv=s.idprodserv group by p.cpfcnpj order by nome";
        $colspantotal=0;
		
    }elseif($tipo=='PRODUTO'){// Agrupar por produto, quanto se faturou para cada produto
        $sql="	select i.idprodserv,n.idempresa,n.dtemissao,s.descr,i.vlritem,sum(i.qtd) as qtd, sum(i.total) as total
                from nf n,nfitem i,prodserv s
                where n.tiponf = 'V' 
                and  n.idpessoa is not null
                and n.status not in ('REPROVADO','CANCELADO','ORCAMENTO')
                and n.natop like('%VENDA%')
                and n.idnf = i.idnf
                ".$clausulac."
                and i.vlritem > 0
                and i.idprodserv=s.idprodserv group by i.idprodserv order by descr";
        
        $colspantotal=0;
    }elseif($tipo=='ESPECIE'){
        $sql="select i.idprodserv,n.idempresa,n.dtemissao,s.descr,i.vlritem,sum(i.qtd) as qtd, sum(i.total) as total,f.especie
                from nf n,nfitem i,prodserv s,lotecons c,lote l,prodservformula f
                where n.tiponf = 'V' 
                and  n.idpessoa is not null
                and n.status not in ('REPROVADO','CANCELADO','ORCAMENTO')
                -- and n.natop like('%VENDA%')
                and n.idnf = i.idnf
                and f.idprodservformula = l.idprodservformula
                and c.qtdd > 0
                and l.idlote = c.idlote
                and c.idobjeto = i.idnfitem
                and c.tipoobjeto ='nfitem'
               	".$clausulac."
                and i.vlritem > 0
                and i.idprodserv=s.idprodserv group by i.idprodserv,f.especie order by especie,descr";
        $colspantotal=2; 
    }else{//agrupado por cliente e produto
	$sql ="select p.idpessoa,i.idprodserv,n.idempresa,n.dtemissao,p.nome,s.descr,i.vlritem,sum(i.qtd) as qtd, sum(i.total) as total
		    from nf n,pessoa p,nfitem i,prodserv s
		    where n.tiponf = 'V' 
		    and p.idpessoa = n.idpessoa
		    and n.status not in ('REPROVADO','CANCELADO','ORCAMENTO')
		    and n.natop like('%VENDA%')
		    and n.idnf = i.idnf
		    ".$clausulac."
		    and i.vlritem > 0
		    and i.idprodserv=s.idprodserv group by p.idpessoa,i.idprodserv order by nome,descr";
		 $colspantotal=2;       
    }
	
	echo "<!--";
	echo $sql;
	echo "-->";
	if (!empty($sql)){		

		$res =  d::b()->query($sql) or die("Falha ao pesquisar vendas: " . mysqli_error() . "<p>SQL: $sql");
		##$ires = mysqli_num_rows($res);

		$saldototal = 0;

	}
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->
<link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<script >

</script>
<style>
</style>
<title>Vendas</title>

<?
if($_GET){

		?>


    <table class="normal">
		
	<thead>
	    <tr>
		<th colspan="4">Relatório Vendas - <?=dma($dataini)?> á <?=dma($datafim)?></th>
	    </tr>
            <tr class='header'>
                <?if($tipo!='PRODUTO' AND $tipo!='ESPECIE'){?>
                <th>Cliente</th>
                <?
                }
                if($tipo!='AGRUPADO'){?>
                <th>Produto</th>
                <?}                
                if($tipo=='ESPECIE'){?>
                <th>Espécie</th>
                <?}?>
                <th>Qtd.</th>
                <th>Valor</th>
            </tr>
	</thead>	
	<?
	$vtotal=0;
	$id=0;
	while ($row = mysqli_fetch_assoc($res)){
	    $id=$id+1;
            
            if($tipo=='ESPECIE'){
                if(empty($especie)){
                    $especie=$row['especie'];
                }
                
                if($especie!=$row['especie']){
                   
?>
        <tr>
	    <td colspan='<?=$colspantotal?>'><b>Total <?=$especie?></b></td>
            <td align="right"><font size="2"><b><?=number_format($vqtd, 2, '.','');?></b></font></td>
		<td align="right"><font size="2"><b><?=number_format($vtotal, 2, '.','');?></b></font></td>
	</tr>
  
<?                  $vtotal=0;$vqtd=0;
                    $especie=$row['especie'];
                }
            }

	    $vtotal=$vtotal+$row["total"];
            $vqtd=$vqtd+$row["qtd"];
?>
	
	<tr class="res">
             <?if($tipo!='PRODUTO' AND $tipo!='ESPECIE'){?>
	    <td class="nowrap"><?=$row["nome"]?></td>
            <?
             }
            if($tipo!='AGRUPADO'){?>
            <td><?=$row['descr']?></td>
            <?}  
            if($tipo=='ESPECIE'){?>
            <td><?=$row['especie']?></td>
            <?}?>
	    <td align="right"><?=$row['qtd']?></td>
	    <td align="right"><?=$row["total"]?></td>
		
	</tr>

	


<?

	}//while ($row = mysqli_fetch_assoc($res)){

	?>
	
	<tr></tr>
	<tr>
	    <td colspan='<?=$colspantotal?>'><b>Total <?=$especie?></b></td>
            <td align="right"><font size="2"><b><?=number_format($vqtd, 2, '.','');?></b></font></td>
		<td align="right"><font size="2"><b><?=number_format($vtotal, 2, '.','');?></b></font></td>
	</tr>
</table>
<p>
    <br>
<p>
<?
}//if($_GET){
?>

<script>


//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>