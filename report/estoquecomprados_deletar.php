﻿﻿<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$ord = 'ordem,descr';

//Recuperar a unidade padrão conforme módulo pré-configurado
$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
$_modulo = $_GET["_modulo"];

$vendas = "AND p.comprado = 'Y'";
$joinun=" join unidadeobjeto o on( o.idunidade =".$idunidadepadrao." and o.idobjeto = p.idprodserv and o.tipoobjeto = 'prodserv') "; 
$novoorcamentolink = "./?_modulo=cotacao&_acao=i";
$titulo = "Novo Orçamento de Compra";
$titulomodulo = "Orçamento de Compra";

if ($_GET['ordenacao']){
switch($_GET['ordenacao']){
    case 'atual':
        $ord = 'ordem, total';
        break;
    case 'atuald':
        $ord = 'ordem, total desc';
        break;
    case 'minimo':
        $ord = 'ordem, u2.estmin';
        break;
    case 'minimod':
        $ord = 'ordem, estmin desc';
        break;
    case 'maximo':
        $ord = 'ordem, pedido';
        break;
    case 'maximod':
        $ord = 'ordem, pedido desc';
        break;
    case 'data':
        $ord = 'ordem, ultimoconsumo';
        break;
    case 'datad':
        $ord = 'ordem, ultimoconsumo desc';
        break;
    case 'prazo':
        $ord = 'ordem, prazo';
        break;
    case 'prazod':
        $ord = 'ordem, prazo desc';
        break;
    case 'status':
        $ord = 'ordem, statusorc';
        break;
    case 'statusd':
        $ord = 'ordem, statusorc desc';
        break;
    default:
        $ord = 'ordem,descr';
    }
}

//Alterado o SQL para pegar os produtos que não estão na ProdServ, mas que precisa ser alertado para compra - Lidiane (18/06/2020)
//Foi retirado o u2 dos $ord por causa do UNION
    $sql="SELECT c.idcotacao AS idprodserv,
				'' AS codprodserv,
				prodservdescr AS descr,
				'' AS estmin,
				'' AS pedido,
				'' AS total,
				'' AS quar,
				'MANUAL' as prioridadecompra,
				'4' AS ordem,
				c.status AS statusorc,
				'MANUAL' AS tipo,
				c.alteradoem AS ultimoconsumo
			FROM nfitem i FORCE INDEX(PRAZO) JOIN nf n ON i.idnf = n.idnf JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
			WHERE idprodserv IS NULL AND n.status NOT IN ('DIVERGENCIA' , 'CONCLUIDO', 'CANCELADO', 'REPROVADO')
			".getidempresa('c.idempresa','cotacao')."
			AND n.tipoobjetosolipor = 'cotacao' 
		UNION 
		SELECT 
			u2.*,
			u3.statusorc,
			'' AS tipo,
			(SELECT 
					criadoem
				FROM
					prodcomprar
				WHERE
					status = 'ATIVO'
						AND idprodserv = u2.idprodserv) AS ultimoconsumo
		FROM
			(SELECT 
				idprodserv,
					codprodserv,
					descr,
					estmin,
					pedido,
					SUM(total) AS total,
					SUM(quar) AS quar,
					prioridadecompra,
					ordem
			FROM
				(SELECT 
				p.idprodserv,
					p.codprodserv,
					p.descr,
					p.estmin,
					p.pedido,
					IFNULL(f.qtd, 0) AS total,
					(SELECT 
							IFNULL(SUM(q.qtdprod), 0)
						FROM
							lote q
						WHERE
							q.idprodserv = p.idprodserv
								AND q.status = 'QUARENTENA') AS quar,
					p.prioridadecompra,
					CASE p.prioridadecompra
						WHEN 'ALTA' THEN 1
						WHEN 'MEDIA' THEN 2
						WHEN 'BAIXA' THEN 3
						ELSE 4
					END AS ordem
			FROM
				prodserv p  
                                ".$joinun."                                    
			LEFT JOIN lote l ON (l.idprodserv = p.idprodserv 
                        AND l.status IN ('APROVADO' , 'QUARENTENA'))
                        LEFT JOIN lotefracao f on(f.idlote=l.idlote";
                        if($idunidadepadrao > 0){
				$sql .= " AND f.idunidade = ".$idunidadepadrao;
			}
                           $sql .= " and f.status='DISPONIVEL'
                            )
			WHERE p.tipo = 'PRODUTO'
				".getidempresa('p.idempresa','prodserv')."
					AND p.status = 'ATIVO'
					AND p.estmin IS NOT NULL
					AND p.estmin != 0.00
					-- AND p.estideal != 0.00
					".$vendas.") AS u
			GROUP BY u.idprodserv) u2
				LEFT JOIN
			(SELECT 
				MAX(c.prazo) AS prazo, c.status AS statusorc, i.idprodserv
			FROM
				cotacao c
			JOIN nf n -- FORCE INDEX (NOVOPRAZO) 
			ON n.idobjetosolipor = c.idcotacao
			JOIN nfitem i FORCE INDEX (PRAZO) ON n.tipoobjetosolipor = 'cotacao'
				AND i.idnf = n.idnf
				AND n.status NOT IN ('DIVERGENCIA' , 'CONCLUIDO', 'CANCELADO', 'REPROVADO')
			GROUP BY i.idprodserv) u3 ON u3.idprodserv = u2.idprodserv
		WHERE
			u2.estmin >= u2.total
        order by ".$ord;
		
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
		
		<div class="titulodoc">PRODUTOS COM ESTOQUE ABAIXO DO IDEAL - QTD: <?=$qtdr?></div>
		
	</div>
<div id='resumo'>
    
</div>
<div class="panel panel-default" style="margin-top:30px;">
    <!--<?=$sql?>-->
    <?
$i=0;
$j=0;
$alerta=0;
$alertamanual=0;
$aberto=0;
$respondido=0;
$enviado=0;
$aprovado=0;
$atrasado=0;
$varquarentena=0;
while ($row=mysqli_fetch_assoc($res)){
    if($rowcot["statuscot"]=="ABERTO" ){
        $aberto=$aberto+1;
    }
    if($rowcot["statuscot"]=="ENVIADO" ){
        $enviado=$enviado+1;
    }
    if($rowcot["statuscot"]=="RESPONDIDO"){
       $respondido=$respondido+1; 
    }
    if($rowcot['statuscot']=='APROVADO'){
        $aprovado=$aprovado+1;
    }
    
	/* Foi comentado esse select pois não está sendo usado em nenhum lugar - LTM (22/06/2020)
	$sqlc="select max(c.alteradoem) as ultimoconsumo 
	from lote l ,lotecons c
	where l.idprodserv=".$row["idprodserv"]."
	and l.idlote = c.idlote";
    $resc=d::b()->query($sqlc) or die("Erro ao buscar consumos existente sql".$sqlc);
    $rowc=mysqli_fetch_assoc($resc);*/
	
	//Alterado o SQL para pegar os produtos que não estão na ProdServ, mas que precisa ser alertado para compra - Lidiane (18/06/2020)
	if($row["tipo"] == 'MANUAL'){
            $alertamanual=$alertamanual+1;
		//SQL para pegar os dados da cotação. Não tem ProdServ - (LTM - 18/06/2020)
		$sqlcot="select distinct(c.idcotacao) as idcotacao,DATE_FORMAT(ifnull(i.previsaoentrega, c.prazo),'%Y-%m-%d') as prazo,
		if(DATE_FORMAT(ifnull(n.dtemissao, c.prazo),'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V') as validadeprazo       
			,c.status as statusorc,n.status as statuscot
			from nfitem i, nf n,cotacao c
			where n.idobjetosolipor ='".$row["idprodserv"]."'
			and i.idnf = n.idnf and i.nfe='Y'
			and n.idobjetosolipor = c.idcotacao 
			and n.tipoobjetosolipor = 'cotacao' 
			and n.status not in('DIVERGENCIA','CONCLUIDO','CANCELADO','REPROVADO')";
	} else {
            $alerta=$alerta+1;
		$sqlcot="select distinct(c.idcotacao) as idcotacao,DATE_FORMAT(ifnull(n.dtemissao, c.prazo),'%Y-%m-%d') as prazo,
		if(DATE_FORMAT(ifnull(n.dtemissao, c.prazo),'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V') as validadeprazo       
			,c.status as statusorc,n.status as statuscot
			from nfitem i, nf n,cotacao c
			where i.idprodserv ='".$row["idprodserv"]."'
			and i.idnf = n.idnf
					and i.nfe='Y'
			and n.idobjetosolipor = c.idcotacao 
			and n.tipoobjetosolipor = 'cotacao' 
			and n.status not in('DIVERGENCIA','CONCLUIDO','CANCELADO','REPROVADO')";
	}
    $rescot=d::b()->query($sqlcot) or die("Erro ao buscar cotação existente sql".$sqlcot);
    $rowcot=mysqli_fetch_assoc($rescot);
    $quarentena='N';
	//Todos os prazos vencidos ficarão vermelho independente do status - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327836 - LTM (22/06/2020)
    if(empty($rowcot['idcotacao']) AND ($row['total']+$row['quar']<= $row['estmin']) OR $rowcot['prazo'] < date('Y-m-d')){
        $cortr='#FF8491';//vermelho
    }else{
	if($rowcot["statuscot"]=="ABERTO" OR $rowcot["statuscot"]=="ENVIADO" OR $rowcot["statuscot"]=="RESPONDIDO"){
            $cortr='#9DDBFF';//azul
            
        }elseif($rowcot['statuscot']=='APROVADO' and $rowcot['validadeprazo']=='V'){
            $cortr='#FF8491';//vermelho
            $atrasado=$atrasado+1;
          
        }elseif(/*$rowcot['statusorc']=='CONCLUIDA' or*/ $rowcot['statuscot']=='APROVADO'){
            
            $cortr="#FFFFFF";//branco
        }elseif($row['total']+$row['quar']> $row['estmin']){
            $cortr="#FF8C00";//laranja
            $quarentena='Y';
            $varquarentena=$varquarentena+1;
        }else{            
            $cortr='yellow';//amarelo
        }	
    }
    
    if($row['prioridadecompra']!=$prioridade){
        $i=0;
        $prioridade=$row['prioridadecompra'];
    }
?>

<?if($i==0){
	if($j > 0){?>
	<br>
	<?}$j++;?>    
            
	<div class="panel-heading">
		PRIORIDADE: <?=$row['prioridadecompra']?>
				</div>
      
<?}?>

	<div class="row" style="background-color: <?=$cortr?>;width: 100%;">
		<!-- div class="col grupo 15 quebralinha">
<?if($i==0){?><div class="titulogrupo">Sigla</div><?}?>
	    <?=$row['codprodserv']?>
		</div -->
		<div class="col grupo 50 quebralinha">
<?if($i==0){?><div class="titulogrupo">Descrição</div><?}?>
		<?if($row['tipo'] == 'MANUAL'){
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
			<?=number_format(tratanumero($row["estmin"]), 2, ',', '.');?>
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
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'status'){echo 'statusd';}else{echo 'status';}?>">Status Orçamento<? if ($_GET['ordenacao'] == 'status'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'statusd'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
<?
                if($quarentena=="Y"){
                    
                    $s="select * from lote where status='QUARENTENA' AND idprodserv = ".$row['idprodserv']." limit 1";
                    $re=d::b()->query($s) or die("Erro ao buscar lote na quarentena sql".$s);
                    $rw=mysqli_fetch_assoc($re);
?>                
                    <a title="Lote em Quarentena" target="_blank" href="./?_modulo=lotealmoxarifado&_acao=u&idlote=<?=$rw["idlote"]?>"><?=$rw["qtdprod"]?>-QUARENTENA</a>
            
<?
                }elseif($rowcot["idcotacao"]){
?>
		    <a title="<?$titulomodulo?>" target="_blank" href="./?_modulo=cotacao&_acao=u&idcotacao=<?=$rowcot["idcotacao"]?>"><?=$rowcot["idcotacao"]?>-<?=$rowcot["statuscot"]?></a>
<?
		}else{
?>
		    <a title="<?=$titulo?>" target="_blank" class="fa fa-plus-circle fa-1x verde pointer" href=<?=$novoorcamentolink?>></a>
<?
		}
?>
		</div>
		<div class="col grupo 25 quebralinha" style="text-align:center;">
<?if($i==0){?><div class="titulogrupo"><a href="./?_modulo=<?=$_modulo?>&ordenacao=<? if ($_GET['ordenacao'] == 'prazo'){echo 'prazod';}else{echo 'prazo';}?>">Prazo<? if ($_GET['ordenacao'] == 'prazo'){echo '<i class="fa fa-sort-down"></i>';}elseif ($_GET['ordenacao'] == 'prazod'){echo '<i class="fa fa-sort-up"></i>';}?></a></div><?}?>
		    <?=dma($rowcot["prazo"])?>
		</div>
	</div>
				
	 
<?
	$i++;
}
?></div>
	<br>
        <br>
        <div class='hidden' id='resumodados'>
        <div class="row" style="border: 1px solid darkgray;
    border-radius: 5px;
    text-align: center;
    padding: 5px;
    width: 100%;">
		<div class="col grupo 15 quebralinha">
                    <div class="titulogrupo">Alerta</div>
			<?=$alerta?>
		</div>
		<div class="col grupo 15 quebralinha">
                <div class="titulogrupo">Alerta Manual</div>
			<?=$alertamanual?>
		</div>
                <div class="col grupo 15 quebralinha">
                <div class="titulogrupo">Aberto</div>
                        <?=$aberto?>			
                </div>
		<div class="col grupo 15 quebralinha">
                <div class="titulogrupo">Enviado</div>
                        <?=$respondido?>			
                </div>
		<div class="col grupo 10 quebralinha">
                <div class="titulogrupo">Respondido</div>
			<?=$enviado?>
		</div>
		<div class="col grupo 20 quebralinha">
                <div class="titulogrupo nowrap">Aprovado</div>
			<?=$aprovado?>
                </div>
                <div class="col grupo 20 quebralinha">
                <div class="titulogrupo nowrap">Quarentena</div>
			<?=$varquarentena?>
		</div>
                <div class="col grupo 20 quebralinha">
                <div class="titulogrupo nowrap">Atrasado</div>
			<?=$atrasado?>
		</div>
               
	</div>				
        </div>
        
    <script>
 // copiar o texto no cabecalho
$(document).ready(function(){     
    
    var texto=$("#resumodados").html(); 
     $("#resumo").html(texto);
  
});

        //Montar legenda para o usuário
CB.montaLegenda({"#FF8491": "Produto estoque mínimo sem orçamento ou atrasado.", "#9DDBFF": "Produto estoque mínimo com orçamento em Andamento.", "#FFFFFF": "Produto estoque mínimo com orçamento Concluido.", "#FF8C00": "Produto recebido mas em Quarentena."});
CB.oPanelLegenda.css( "zIndex", 901);

    </script>