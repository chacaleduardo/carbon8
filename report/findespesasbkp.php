<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["periodo_1"];
$vencimento_2 	= $_GET["periodo_2"];
$idagencia 	= $_GET["agencia"];
$pesquisa 	= $_GET["pesquisa"];
$status = $_GET["status"];

//$clausula .= " vencimento > '2009-01-01' and ";

//print_r($_SESSION["post"]);

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulac .= " and (datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"."  ";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}else{
    die("Favor informar o período.");
}


if(!empty($idagencia)){
	$clausulac .= " and cp.idagencia=".$idagencia."  " ;
}
if(!empty($status)){
	$clausulac .= " and cp.status='".$status."'  " ;
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
		
	$sqlgrupo ="select ci.idcontaitem, ci.contaitem,sum(cp.valor) * -1 as somatotal,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
		        from contapagar cp,contaitem ci
		        where cp.tipo = 'D'		       
		        and cp.idcontaitem = ci.idcontaitem 
			".$clausulac."
			group by ci.idcontaitem order by ci.ordem,ci.contaitem";
		        
	
	
	echo "<!--";
	echo $sqlgrupo;
	echo "-->";
	if (!empty($sqlgrupo)){		

		$resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
		##$ires = mysqli_num_rows($res);

		$saldototal = 0;

	}
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<script >

</script>
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
	font-size: 0.4cm;
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
	font-size: 14px;
}
.val{
	font-size: 14px;
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
<title>Despesas</title>

<?
if($_GET){

		?>

<fieldset><legend>Relatório Despesas - <?=dma($dataini)?> á <?=dma($datafim)?></legend>

		
	<?
	$vtotal=0;
	$id=0;
	while ($row = mysqli_fetch_assoc($resgrupo)){
	    $id=$id+1;
		if(!empty($row["idcontaitem"])){
			if($row["somarelatorio"]=="Y"){
				$vtotal=$vtotal+$row["somatotal"];
				$previsao=$previsao+$row["previsao"];
			}
			?>

	<div>
	<div class="row" style=" background-color: #<?=$row["cor"]?>;"  >
		<div class="col 50 val">
		    <a href="/?_modulo=contaitem&_acao=u&idcontaitem=<?=$row["idcontaitem"]?>" target="_blank">
			<?=$row["contaitem"]?>
		    </a>
		  
		</div>
		<div class="col 10 rot">Previsão</div>
		<div class="col 20 val"><?=$row['previsao']?></div>
		<div class="col 10 rot">Total </div>
		<div class="col 15 val"><?=$row["somatotal"]?></div>
		
	</div>
	    <div  class="collapse" id="prodInfo<?=$id?>">
	<?
	if($pesquisa=="detalhe"){
		$sql="select ci.idcontaitem, ci.contaitem,p.idpessoa,p.nome,sum(cp.valor) * -1 as somatotal,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
		        from contapagar cp, pessoa p,contaitem ci
		        where cp.tipo = 'D'			
		        and cp.idpessoa = p.idpessoa
		        and cp.idcontaitem = ci.idcontaitem 
			and cp.idcontaitem = ".$row["idcontaitem"]."
			".$clausulac."
			group by ci.idcontaitem,cp.idpessoa order by p.nome";
	
	
		echo "<!--";
		echo $sql;
		echo "-->";
				
		$res =  d::b()->query($sql) or die("Falha ao pesquisar pessoas de contas: " . mysqli_error() . "<p>SQL: $sql");
	
		while ($row2 = mysqli_fetch_assoc($res)){
	?>	
		<div class="row"  style=" background-color: #<?=$row["cor"]?>;" >
			<div class="col 60 rot"><?=$row2["nome"]?></div>
			<div class="col 20 val"></div>
			<div class="col 10 rot"></div>
			<div class="col 15 val"><?=$row2["somatotal"]?></div>
		</div>
	<?
		}
	}
	?>
		</div>
	  </div>  


<?
		}
	}//while ($row = mysqli_fetch_assoc($res)){

	?>
	
<table class="normal" style="border:1px solid black; background-color: #FFFFFF;">	
	<tr style="height: 5px;"></tr>
	<tr>
		<td colspan='2'><font size="2">Previsão</font></td>
		<td align="right"><font size="2"><?=number_format($previsao, 2, '.','');?></font></td>
	</tr>	
	<tr>
		<td colspan='2'><font size="2">Total</font></td>
		<td align="right"><font size="2"><?=number_format($vtotal, 2, '.','');?></font></td>
	</tr>
</table>

</fieldset>
<?
}//if($_GET){
?>

<script>


//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>