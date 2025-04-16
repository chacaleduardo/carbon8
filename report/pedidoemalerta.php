﻿<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../model/prodserv.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();


$ord = 'ordem,ordem2,descr';

//Recuperar a unidade padrão conforme módulo pré-configurado
$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
$_modulo = $_GET["_modulo"];

$v_ordem = $_GET["ordem"];

$novoorcamentolink = "./?_modulo=cotacao&_acao=i";
$titulo = "Novo Orçamento de Compra";
$titulomodulo = "Orçamento de Compra";

if ($_GET['ordenacao']){
switch($_GET['ordenacao']){
    case 'atual':
        $ord = 'ordem,ordem2, total';
        break;
    case 'atuald':
        $ord = 'ordem,ordem2, total desc';
        break;
    case 'minimo':
        $ord = 'ordem,ordem2, u2.estmin';
        break;
    case 'minimod':
        $ord = 'ordem,ordem2, estmin desc';
        break;
    case 'maximo':
        $ord = 'ordem,ordem2, pedido';
        break;
    case 'maximod':
        $ord = 'ordem,ordem2, pedido desc';
        break;
    case 'data':
        $ord = 'ordem,ordem2, ultimoconsumo';
        break;
    case 'datad':
        $ord = 'ordem,ordem2, ultimoconsumo desc';
        break;
    case 'prazo':
        $ord = 'ordem,ordem2, prazo';
        break;
    case 'prazod':
        $ord = 'ordem,ordem2, prazo desc';
        break;
    case 'status':
        $ord = 'ordem,ordem2, statusorc';
        break;
    case 'statusd':
        $ord = 'ordem,ordem2, statusorc desc';
        break;
    default:
        $ord = 'ordem,ordem2,descr';
    }
}
// chama o model prodserv para buscar o sql
$sql=$prodservclass->getProdservAlerta($idunidadepadrao,'PEDIDO');
$sql.=$ord;
echo '<!--'.$sql.'-->';
$res= d::b()->query($sql) or die("Erro ao buscar SF : " . mysql_error() . "<p>SQL:".$sql);
$qtdr=mysqli_num_rows($res);
?>


<style>
@media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
@media screen{
	
	.quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 200%;
		margin: 1.5cm -100px;
	}
	.rot{
		color: gray;
	}
	
}

[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
header{
	 background-color: white;
	 top: 0;
	 height: 1cm;
	 line-height: 1cm;
	 display: table;
}
header + hr{
	margin: 0;
}
.logosup{
	height: inherit;
	line-height: inherit;
	display: table-cell;
}
.logosup img{
	height: 0.5cm;
	vertical-align: middle;
}
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
	text-align: center;
	font-size: 0.3cm;
	font-weight: bold;
}
.row{
	display: table;
	table-layout: fixed;
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px solid #f8f8f8;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
}
.row.grid .col{
	border: 1px solid #777777;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo .titulogrupo{
	margin: 0px;
	border-bottom: 1px dotted silver;
	color: #777777;
	font-weight: bold;
	margin-bottom: 2mm;
    background: rgba(255,255,255,0.1);
    padding: 0px;
    margin: 0px;
    height: 30px;
    padding-top: 7px;
    margin-bottom: 30px;
}
.rot{
	color: #777777;
	overflow: hidden;
	font-size: 12px;
}
.quebralinha{
	white-space: normal;
}
[class*='margem0.0']{
	margin: 0 0;
}
.sublinhado{
	border-bottom: 1px dashed gray;
}
</style>

	<div class="row margem 0.0">
		
		<div class="titulodoc nowrap">PEDIDO EM ALERTA - <span id="qtd"></span></div>
		
	</div>
<div id='resumo'>
    
</div>

       <?
$i=0;
$j=0;
$alerta=0;

while ($row=mysqli_fetch_assoc($res)){
    if($row["ordem"] == '5' or $row["ordem"] == '9' ){  
    	
	//Alterado o SQL para pegar os produtos que não estão na ProdServ, mas que precisa ser alertado para compra - Lidiane (18/06/2020)
	if($row["entrada"] == 'MANUAL'){
            
		//SQL para pegar os dados da cotação. Não tem ProdServ - (LTM - 18/06/2020)
		$sqlcot="select distinct(c.idcotacao) as idcotacao,DATE_FORMAT( c.prazo,'%Y-%m-%d') as prazo,DATE_FORMAT( n.previsaoentrega,'%Y-%m-%d') as previsaoentrega,		    
			c.status as statusorc,n.status as statuscot
			from nfitem i, nf n,cotacao c
			where n.idobjetosolipor ='".$row["idprodserv"]."'
			and i.idnf = n.idnf and i.nfe='Y'
			and n.idobjetosolipor = c.idcotacao 
			and n.tipoobjetosolipor = 'cotacao' 
			and n.status not in('CONCLUIDO','CANCELADO','REPROVADO')";
	} else {
            
		$sqlcot="select distinct(c.idcotacao) as idcotacao,DATE_FORMAT(c.prazo,'%Y-%m-%d') as prazo,DATE_FORMAT( n.previsaoentrega,'%Y-%m-%d') as previsaoentrega,
                                c.status as statusorc,n.status as statuscot
			from nfitem i, nf n,cotacao c
			where i.idprodserv ='".$row["idprodserv"]."'
			and i.idnf = n.idnf
			and i.nfe='Y'
			and n.idobjetosolipor = c.idcotacao 
			and n.tipoobjetosolipor = 'cotacao' 
			and n.status not in('CONCLUIDO','CANCELADO','REPROVADO')";
	}
    $rescot=d::b()->query($sqlcot) or die("Erro ao buscar cotação existente sql".$sqlcot);
    $rowcot=mysqli_fetch_assoc($rescot);
    $quarentena='N';
    
    if( $row['ordem']==1 or  $row['ordem']==7  or  $row['ordem']==2 or  $row['ordem']==3 or $row['ordem']==5 or $row['ordem']==6  ){
        $cortr='#FF8491';//vermelho
          $alerta=$alerta+1;
    }elseif($row['ordem']==10){
        $cortr='#FF8491';//vermelho
          $alerta=$alerta+1;
    }else{
        $cortr='#9DDBFF';//azul
    }
    if($row["ordem"] == '1'){ $var1=$var1+1;}
    elseif($row["ordem"] == '7'){ $var0=$var0+1;}
    elseif($row["ordem"] == '2'){ $var2=$var2+1;}
    elseif($row["ordem"] == '3'){ $var3=$var3+1;}
    elseif($row["ordem"] == '4'){ $var4=$var4+1;}
    elseif($row["ordem"] == '5'){ $var5=$var5+1;}           
    elseif($row["ordem"] == '8'){ $var8=$var8+1;}
    elseif($row["ordem"] == '9'){ $var9=$var9+1;}
    elseif($row["ordem"] == '10'){ $var10=$var10+1;}
    
    if($row['ordem']!=$ordem){
        $i=0;
        $ordem=$row['ordem'];
    }
?>


<?
        if($i==0){
            if($j >1){ ?>
            <br>
            <?}$j++;?>  
            
            <?
                if ($divordem != $row["ordem"]){
                    if($j > 1){
                        echo '</div></div>';
                    }
                    echo ' <div class="panel panel-default  ';
                    if ($v_ordem > 0 and $v_ordem != $ordem){ echo "hide";}
                    echo '" style="margin-top:30px;">';
                    ?>
	<div class="panel-heading">
    <?if($row["ordem"] == '2'){echo("Produto com Orçamento insuficiente - <span id='var2'></span>"); }
            elseif($row["ordem"] == '1'){echo("Produto sem Orçamento - <span id='var1'></span>"); }
            elseif($row["ordem"] == '7'){echo("Produto sem Orçamento - <span id='var0'></span>"); }
            elseif($row["ordem"] == '3'){echo("Produto com Orçamento Atrasado - <span id='var3'></span>"); }
            elseif($row["ordem"] == '4'){echo("Produto com Orçamento Andamento - <span id='var4'></span>"); }
            elseif($row["ordem"] == '5'){echo("Produto com Pedido Atrasado - <span id='var5'></span>"); }
            elseif($row["ordem"] == '6'){echo("Produto Manual com Pedido Atrasado - <span id='var6'></span>"); }           
            elseif($row["ordem"] == '9'){echo("Produto à Receber - <span id='var9'></span>"); }
            elseif($row["ordem"] == '10'){echo("Verificar Orçamento no sistema - <span id='var10'></span>"); }
            
            ?>
  </div>
                    <div class="panel-body">

                <?
                    $divordem = $row["ordem"];
                }
        }

        if($i % 2 == 0){
           $opacity = '1';
       } else {
            $opacity = '0.9';
       }

        ?>

<div class="row" style="background-color: <?=$cortr?>;width: 100%;opacity:<?=$opacity;?>">
		<!-- div class="col grupo 15 quebralinha">
<?if($i==0){?><div class="titulogrupo">Sigla</div><?}?>
	    <?=$row['codprodserv']?>
		</div -->
		<div class="col grupo 50 quebralinha">
<?if($i==0){?><div class="titulogrupo">Descrição</div><?}?>
		<?if($row['entrada'] == 'MANUAL'){
			echo $row['descr'];
		} else { ?>
			<a title="Cadastro do produto" target="_blank" href="./?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv']?>"><?=$row['descr']?></a>
		<? } ?>
		</div>                                                  
		<div class="col grupo 20 quebralinha" style="text-align:center;">
                    <?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'atual'){
                                                                                                            echo 'atuald';
                                                                                                       }else{
                                                                                                           echo 'atual';
                                                                                                           
                                                                                                       }?>">Estoque<? if ($_GET['ordenacao'] == 'atual'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'atuald'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>                             
			<?=number_format(tratanumero($row["total"]), 2, ',', '.');?>
		</div>
		<div class="col grupo 20 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'minimo'){echo 'minimod';}else{echo 'minimo';}?>">Estoque Mínimo <? if ($_GET['ordenacao'] == 'minimo'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'minimod'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
			
            <?if(empty($row["estmin_exp"])){
 				echo number_format(tratanumero($row["estmin"]), 2, ',', '.');
			}else{
				echo recuperaExpoente(tratanumero($row["estmin"]),$row["estmin_exp"]);
			}
			
			?>
		</div>
		<div class="col grupo 20 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'maximo'){echo 'maximod';}else{echo 'maximo';}?>">Pedido <? if ($_GET['ordenacao'] == 'maximo'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'maximod'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
			<?=number_format(tratanumero($row["pedido"]), 2, ',', '.');?>
		</div>
		<div class="col grupo 40 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'data'){echo 'datad';}else{echo 'data';}?>">Data Entrada Item<? if ($_GET['ordenacao'] == 'data'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'datad'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
			<?=dma($row["ultimoconsumo"])?>
		</div>
		<div class="col grupo 40 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'status'){echo 'statusd';}else{echo 'status';}?>">Status Pedido<? if ($_GET['ordenacao'] == 'status'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'statusd'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
<?  
            if(!empty($row["idnf"])){
      
?>
		    <a title="<?=$titulomodulo?>" target="_blank" href="./?_modulo=nfentrada&_acao=u&idnf=<?=$row["idnf"]?>"><?=$row["idnf"]?>-<?=$row["status"]?></a>
  <?
            }else{
?>
		    <a title="<?$titulomodulo?>" target="_blank" href="./?_modulo=cotacao&_acao=u&idcotacao=<?=$rowcot["idcotacao"]?>"><?=$rowcot["idcotacao"]?>-<?=$row["statusorc"]?></a>
<?
            }
?>
		</div>
		<div class="col grupo 25 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'prazo'){echo 'prazod';}else{echo 'prazo';}?>">Prazo<? if ($_GET['ordenacao'] == 'prazo'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'prazod'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
        <?=dma($rowcot["previsaoentrega"]);?>
		</div>
	</div>
				
	 
<?
	$i++;
        }
}
/*
 //@todo: Trocar o bloco abaixo pela programação redis
 $sql= "UPDATE dashboard SET 
                            card_value='".$alerta."', 
                            card_color = if(".$alerta." > 0,'danger','success'), 
                            card_border_color = if(".$alerta." > 0,'danger','success')
                    WHERE iddashcard = '1369'";

 d::b()->query($sql) or die("Falha ao Atualizar Dashboard 1369: ".mysqli_error(d::b())."\n".$sql); */
?></div>
</div>
	<br>
        <br>
        <div class='hidden' >
            <div id="valalerta"><?=$alerta?></div>
            <div id="valvar0"><?=$var0;?></div>
            <div id="valvar1"><?=$var1;?></div>
            <div id="valvar2"><?=$var2;?></div>
            <div id="valvar3"><?=$var3;?></div>
            <div id="valvar4"><?=$var4;?></div>
            <div id="valvar5"><?=$var5;?></div>
            <div id="valvar8"><?=$var8;?></div>
            <div id="valvar9"><?=$var9;?></div>
            <div id="valvar10"><?=$var10;?></div>
        </div>        
    <script>
 // copiar o texto no cabecalho
$(document).ready(function(){     
    
    var valalerta=$("#valalerta").html(); 
    $("#qtd").html(valalerta);

    var valvar0=$("#valvar0").html(); 
    $("#var0").html(valvar0);
    
    var valvar1=$("#valvar1").html(); 
    $("#var1").html(valvar1);
    
    var valvar2=$("#valvar2").html(); 
    $("#var2").html(valvar2);
    
    var valvar3=$("#valvar3").html(); 
    $("#var3").html(valvar3);
    
    var valvar4 =$("#valvar4").html(); 
    $("#var4").html(valvar4);
    
    var valvar5=$("#valvar5").html(); 
    $("#var5").html(valvar5);
    
    var valvar8=$("#valvar8").html(); 
    $("#var8").html(valvar8);
    
    var valvar9=$("#valvar9").html(); 
    $("#var9").html(valvar9);

    var valvar10=$("#valvar10").html(); 
    $("#var10").html(valvar10);
  
});

        //Montar legenda para o usuário
//CB.montaLegenda({"#FF8491": "Produto estoque mínimo sem orçamento ou atrasado.", "#9DDBFF": "Produto estoque mínimo com orçamento em Andamento.", "#FFFFFF": "Produto estoque mínimo com orçamento Concluido.", "#FF8C00": "Produto recebido mas em Quarentena."});
//CB.oPanelLegenda.css( "zIndex", 901);

    </script>