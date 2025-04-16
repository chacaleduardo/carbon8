<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["idcontrato"])){
	die("Ficha não enviada");
}

$sql="select *  from contrato where idcontrato=".$_GET["idcontrato"];
$res= d::b()->query($sql) or die("Erro ao buscar contrato : " . mysqli_error() . "<p>SQL:".$sql);
echo "<!--";
echo $sql;
echo "-->";
$r=mysqli_fetch_assoc($res);
?>
<html>
<head>
<style>
@media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
* {
	text-shadow: none !important;
	filter:none !important;
	-ms-filter:none !important;
	font-family: Helvetica, Arial;
	font-size: 10px;
	-webkit-box-sizing: border-box; 
	-moz-box-sizing: border-box;    
	box-sizing: border-box; 
}
html{
	background-color: silver;
}
body {
	line-height: 1.4em;
	background-color: white;
}

@media screen{
	body {
		margin: auto;
		margin-top: 0.2cm;
		margin-bottom: 1cm;
		padding: 3mm 10mm;
		width: 21cm;
	}
	.quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 120%;
		margin: 1.5cm -1.5cm;
	}
	.rot{
		color: gray;
	}
}

@media print{
	html{
		background-color: transparent;
	}
	body {
		margin: 0cm;
	}
	.quebrapagina{
		page-break-before:always;
	}
	.rot{
		color: #777777;
	}
}

.ordContainer{
	display: flex;
	flex-direction: column;
}
.ord1{order: 1;}
.ord2{order: 2;}
.ord3{order: 3;}
.ord4{order: 4;}
.ord5{order: 5;}
.ord6{order: 6;}
.ord7{order: 7;}
.ord8{order: 8;}
.ord9{order: 9;}
.ord10{order: 10;}
.ord11{order: 11;}
.ord12{order: 12;}
.ord13{order: 13;}
.ord14{order: 14;}
.ord15{order: 15;}
.ord16{order: 16;}
.ord17{order: 17;}
.ord18{order: 18;}
.ord19{order: 19;}
.ord20{order: 20;}
.ord21{order: 21;}
.ord22{order: 22;}
.ord23{order: 23;}
.ord24{order: 24;}
.ord25{order: 25;}
.ord26{order: 26;}
.ord27{order: 27;}
.ord28{order: 28;}
.ord29{order: 29;}
.ord30{order: 30;}
.ord31{order: 31;}
.ord32{order: 32;}
.ord33{order: 33;}
.ord34{order: 34;}
.ord35{order: 35;}
.ord36{order: 36;}
.ord37{order: 37;}
.ord38{order: 38;}
.ord39{order: 39;}
.ord40{order: 40;}
.ord41{order: 41;}
.ord42{order: 42;}
.ord43{order: 43;}
.ord44{order: 44;}
.ord45{order: 45;}
.ord46{order: 46;}
.ord47{order: 47;}
.ord48{order: 48;}
.ord49{order: 49;}
.ord50{order: 50;}
.ord51{order: 51;}
.ord52{order: 52;}
.ord53{order: 53;}
.ord54{order: 54;}
.ord55{order: 55;}
.ord56{order: 56;}
.ord57{order: 57;}
.ord58{order: 58;}
.ord59{order: 59;}
.ord60{order: 60;}
.ord61{order: 61;}
.ord62{order: 62;}
.ord63{order: 63;}
.ord64{order: 64;}
.ord65{order: 65;}
.ord66{order: 66;}
.ord67{order: 67;}
.ord68{order: 68;}
.ord69{order: 69;}
.ord70{order: 70;}
.ord71{order: 71;}
.ord72{order: 72;}
.ord73{order: 73;}
.ord74{order: 74;}
.ord75{order: 75;}
.ord76{order: 76;}
.ord77{order: 77;}
.ord78{order: 78;}
.ord79{order: 79;}
.ord80{order: 80;}
.ord81{order: 81;}
.ord82{order: 82;}
.ord83{order: 83;}
.ord84{order: 84;}
.ord85{order: 85;}
.ord86{order: 86;}
.ord87{order: 87;}
.ord88{order: 88;}
.ord89{order: 89;}
.ord90{order: 90;}
.ord91{order: 91;}
.ord92{order: 92;}
.ord93{order: 93;}
.ord94{order: 94;}
.ord95{order: 95;}
.ord96{order: 96;}
.ord97{order: 97;}
.ord98{order: 98;}
.ord99{order: 99;}
.ord100{order: 100;}


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
hr{
	margin: 0;
}
.logosup{
	width:20%;
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
	font-size: 0.5cm;
	font-weight: bold;
}
.row{
	display: table;
	table-layout: fixed;
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px dashed gray;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
}
.row.grid .col{
	border: 1px solid silver;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo {}
.col.grupo .titulogrupo{
	margin: 0px;
	border-bottom: 1px solid silver;
	color: #777777;
	font-weight: bold;
	Xmargin-bottom: 2mm;
}
.rot{
	overflow: hidden;
	font-size: 10px;
}
.quebralinha{
	white-space: normal;
}
[class*='margem0.0']{
	margin: 0 0;
}
.hidden{
	display: none;
}
.sublinhado{
	border-bottom: 1px dashed gray;
}
.fonte8{
	font-size: 8px;
}
.resultadodescritivo{
	margin: 0 0;
}
.resultadodescritivo p{
	margin: 0 0;	
}
</style>
</head>
<body >
 		<pagina class="ordContainer">
		<header class="row margem0.0">
			<div class="logosup col 20">
			<?
			$_idempresa = $_GET["_idempresa"] != ''? "and idempresa = ".$_GET["_idempresa"]:getidempresa('idempresa','empresa');
			// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
			$sqlfig="select * from empresaimagem where 1 ".$_idempresa." and tipoimagem = 'HEADERPRODUTO'";
			$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
			$figrel=mysqli_fetch_assoc($resfig);

			//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
			//$figurarelatorio = "../inc/img/repheader.png";
			$figurarelatorio = $figrel["caminho"];
			
		?>
		<img src="<?=$figurarelatorio?>" style="width: 160px;height: 20px;"></div>
			<div class="titulodoc">
			Contrato - <?=$r["titulo"]?>  
			</div>
			<div class="col 10"></div>
		</header>
		<div class="row">
			<div class="col 15 rot">N&ordm; Contrato:</div>
			<div class="col 35"><?=$r['numero']?></div>
			
			<div class="col 15 rot">Vigência:</div>
			<div class="col 35"><?=dma($r["vigencia"])?></div>
			<div class="col 15 rot">à</div>
			<div class="col 35"><?=dma($r["vigenciafim"])?></div>
		</div>

 			<?
				if($r['tipo']=='S'){
			?>
			
			
			<?
					$sql = "
					SELECT
					  d.iddesconto,
					  d.idtipoteste,
					  t.tipoteste,
					round(t.valor,2) as valor,
			      	  round(d.desconto,2) as desconto,
					d.tipodesconto,
					p.idportaria,p.codigo,
			      	  t.sigla
					FROM
					  desconto d,
					  vwtipoteste t left join portaria p on(t.idportaria=p.idportaria)
					where
					  d.idtipoteste = t.idtipoteste
					   AND d.idcontrato = " .$r['idcontrato']. "
					order by
					  t.tipoteste";
			
					$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error() . "<p>SQL: $sql");
					$qtd = mysqli_num_rows($res);
			
				?>
					
				 <table align="center" width="100%" border="1">
				
				<?if($qtd>0){?>
				  <tr style="background-color: #B5B5B5">
				    <td class="rotulo">Sigla</td>
				    <td class="rotulo">Teste</td>
				    <!--td class="rotulo">Valor Tabela</td>
				    <td class="rotulo">Tipo desconto</td>
				    <td class="rotulo">Valor desconto</td -->
				    <td class="rotulo">Valor Final</td>
				   
				  </tr>
				    <?
				    $i = 1;
				    $troca="S";
				    while ($row = mysqli_fetch_array($res)) {
				    	$i++;
					
					if(!empty($row['idportaria'])){
						$legportaria="S";
						$inidportaria=$inidportaria.$virg.$row['idportaria'];
						$virg=",";				
					}
				    	//mudar a cor da linha
				    	if($troca=="S"){
				    		$cortr = "#FFFFFF";
				    		$troca="N";
				    	}else{
				    		$cortr = "#E8E8E8";
				    		$troca="S";
				    	}
					?>
					  <tr style="background-color: <?=$cortr?>"> 
					    <td align="center" class="textoitem"><?=$row["sigla"]?></td>
					    <td align="center" class="textoitem"><?=$row["codigo"]?> <?=$row["tipoteste"]?></td>
					    <!-- td align="center" class="textoitem"><?=$row["valor"]?></td>
					    <td align="center" class="textoitem"> 
				         <?
				          if($row["tipodesconto"]=="P"){echo("%");}else{echo("R$");}							
						  ?>
				        		      	
				      	 </td>
				      	  <td align="center" class="textoitem"><?=$row["desconto"]?></td -->
				      	  <td align="center" class="textoitem">
						<?if($row["tipodesconto"]=='P'){
							$valor=$row["desconto"]/100*$row["valor"];
							$valor = $row["valor"]-$valor;
							$valor=number_format(tratanumero($valor), 2, ',', '.');
							echo($valor);
						}else{
							echo($row["desconto"]);
						}?>
						</td>
					   </tr>
					  <?
					  	}
					  }
					  //buscar os demais serviços para a lista alem dos do contrato
					  /*
					  $sql="select 
							p.idprodserv,
							p.descr,
							p.vlrvenda,
							p.codprodserv
							from prodserv p 
							where p.tipo= 'SERVICO'
							and p.status ='ATIVO'
							and not exists (select 1 from  desconto d where d.idtipoteste = p.idprodserv and d.idcontrato = " .$r['idcontrato']. ")
							AND p.vlrvenda is not null order by p.descr";
					  $res=d::b()->query($sql) or die("Erro ao buscar serviços do cadastro sql".$sql);
					  while($row=mysqli_fetch_assoc($res)){
							$i++;
							if($troca=="S"){
								$cortr = "#FFFFFF";
								$troca="N";
							}else{
								$cortr = "#E8E8E8";
								$troca="S";
							}
					  ?>
					  <tr style="background-color: <?=$cortr?>"> 
					    <td align="center" class="textoitem"><?=$row["codprodserv"]?></td>
					    <td align="center" class="textoitem"><?=$row["descr"]?></td>
					    <td align="center" class="textoitem"><?=$row["vlrvenda"]?></td>
					    <td align="center" class="textoitem"> </td>
				      	  <td align="center" class="textoitem"></td>
				      	  <td align="center" class="textoitem"><?=$row["vlrvenda"]?></td>
					   </tr>
					  <?
						if($i==45){
							$i=0;
						?>	
							</table>
							<br>
							<div style="page-break-before: always;">
							
								 <table align="center" width="100%" border="1">				
								  <tr style="background-color: #B5B5B5">
								    <td class="rotulo">Sigla</td>
								    <td class="rotulo">Teste</td>
								    <td class="rotulo">Valor Tabela</td>
								    <td class="rotulo">Tipo desconto</td>
								    <td class="rotulo">Valor desconto</td>
								    <td class="rotulo">Valor Final</td>				   
								  </tr>
						<?	
						}

						}
						*/
						?>
					 
					  
					  
					</table>
<?
					if($legportaria=="S"){
					    $sqlport="select idportaria,portaria,codigo,referencia,tipo from portaria where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and idportaria in(".$inidportaria.")";
					    $resport= d::b()->query($sqlport) or die("Erro ao buscar portaria sql=".$sqlport);
					    $qtdport=mysqli_num_rows($resport);
					    if($qtdport>0){
					    ?>
		    <p>
					    <table align="center" width="100%" border="1">
						 <tr style="background-color: #B5B5B5">
						    <td>Legenda</td>
						</tr>
						    <?
						    while($rowport=mysqli_fetch_assoc($resport)){
							    if($troca=="S"){
								    $cortr = "#FFFFFF";
								    $troca="N";
							    }else{
								    $cortr = "#E8E8E8";
								    $troca="S";
							    }
			    ?>
						    <tr >
							    <th>
							    <?echo($rowport['codigo']." ".$rowport['tipo']." MAPA N&#186;. ".$rowport['portaria'].", de ".$rowport['referencia']);?>
							    </th>
						    </tr>	

			    <?
						    }
			    ?>
					    </table>					 
			    <?
					    }// if($qtdport>0){
					}//if($legportaria=="S"){
?>
			<?}elseif($r['tipo']=='P'){
			?>
			
			
			<?

				$sql="select ps.descr,ps.descrcurta,concat(p.plantel,' - ',f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,c.*
				from desconto d join 
				contratoprodservformula c on(d.iddesconto = c.iddesconto) join prodservformula f on(f.idprodservformula=c.idprodservformula)
				join prodserv ps on(ps.idprodserv=f.idprodserv)
				join plantel p on(p.idplantel=f.idplantel)
				where d.idcontrato=".$r['idcontrato']." order by rotulo";			
			
					$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error() . "<p>SQL: $sql");
					$qtd = mysqli_num_rows($res);
				?>
				 <table align="center" width="100%" border="1">
				 <?if($qtd>0){?>
				  <tr style="background-color: #B5B5B5">				    
						<td class="rotulo">Produto</td> 
						<td align="right"  class="rotulo">Quantidade</td>
						<td align="right" class="rotulo">Valor  <br> R$</td>                                       
				  </tr>
				    <?
				    $i = 1;
				    $troca="S";
				    while ($row = mysqli_fetch_array($res)) {

				    	$i++;
				    	if($troca=="S"){
				    		$cortr = "#FFFFFF";
				    		$troca="N";
				    	}else{
				    		$cortr = "#E8E8E8";
				    		$troca="S";
				    	}
					?>
					  <tr   style="background-color: <?=$cortr?>"> 					   
							<td align="left" class="textoitem">
								<?if(empty($row["descrcurta"])){echo $row["descr"];}else{ echo $row["descrcurta"]; }?>
								<?=$row["rotulo"]?>				      	
							</td>
							<td align="right" class="textoitem"><?=round($row["qtd"],2)?></td>
							<td align="right" class="textoitem"><?=round($row["valor"],2)?></td>
					  </tr>
					  <?
					  	}
					}
					  ?>
			
						
					</table>				
			<?}?>
 
	

	</pagina>
<pagina>
    <div class="quebrapagina"></div>
	<header class="row margem0.0">
		<div class="logosup col 20"><img src="<?=$figurarelatorio?>" style="width: 160px;height: 20px;"></div>
		<div class="titulodoc">Análise Crítica do Contrato</div>
		<div class="col 20"></div>
	</header>
	
			 <table align="center" width="100%" border="1">
			    <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(1)-Estrutura das instalações.</td>
				<td align="center" class="textoitem"><?=$r['espfis']=='Y'?'Sim':'Não'?></td>
			    </tr>
			    <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(2)-Capacitação técnica.</td>
				<td align="center" class="textoitem"><?=$r['captec']=='Y'?'Sim':'Não'?></td>
			    </tr>
			    <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(3)-Capacidade operacional.</td>
				<td align="center" class="textoitem"><?=$r['capoper']=='Y'?'Sim':'Não'?></td>
			    </tr>
			    <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(4)-Ensaio consta no escopo.</td>
				<td align="center" class="textoitem"><?=$r['ensaio']=='Y'?'Sim':'Não'?></td>
			    </tr>
			     <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(5)-Cumprimento de prazo.</td>
				<td align="center" class="textoitem"><?=$r['prazo']=='Y'?'Sim':'Não'?></td>
			    </tr>
			     <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(6)-Subcontratação do ensaio.</td>
				<td align="center" class="textoitem"><?=$r['ensaiosub']=='Y'?'Sim':'Não'?></td>
			    </tr>
			     <tr style="background-color: <?=$cortr?>"> 
				<td align="center" class="textoitem">(7)-Divergências entre a proposta e o contrato.</td>
				<td align="center" class="textoitem"><?=$r['divergencia']=='Y'?'Sim':'Não'?></td>
			    </tr>	
			</table>
			<table>
			    <tr>
				<td>Responsável:</td>
				<td><?=traduzid('pessoa', 'idpessoa', 'nome', $r['idpessoa'])?>
				    
				</td>
			    </tr>
			</table>
</pagina>
</body>
</html>