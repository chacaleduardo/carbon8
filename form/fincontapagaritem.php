<?
require_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);

if($_POST){
    require_once("../inc/php/cbpost.php");
}
//ini_set("display_errors","1");
//error_reporting(E_ALL);
################################################## Atribuindo o resultado do metodo GET
function verificaData($data){
	//cria um array
	$array = explode('/', $data);
	
	//garante que o array possue tres elementos (dia, mes e ano)
	if(count($array) == 3){
		$dia = (int)$array[0];
		$mes = (int)$array[1];
		$ano = (int)$array[2];
	
		//testa se a data é válida
		if(checkdate($mes, $dia, $ano)){
			return true;
		}else{
		   return false;
		}
	}else{
		return false;
	}
}
	
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$nome 	= $_GET["nome"];
$status =$_GET["status"];
$idcontapagar =$_GET["idcontapagar"];
$nnfe=$_GET["nnfe"];
$idformapagamento = $_GET['idformapagamento'];
if ($_GET['_fds']){
		$data = explode('-',$_GET['_fds']);
		$data1 = $data[0];
		$data2 = $data[1];
		if (verificaData($data2)){
			 $data2 = $data2.' 23:59:59';
			
		}
	if ($data1 and $data2){
			//echo '<br>';
			$clausulad .=" and  dtemissao between (".validadate($data1). " and " .validadate($data2). ")";	
	}	
}

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
	    $clausulad .= " and prazo  BETWEEN ('" . $dataini ."' and '" .$datafim ."') ";
	}else{
	    die ("Datas n&atilde;o V&aacute;lidas!");
	}
}//if (!empty($vencimento_1) or !empty($vencimento_2)){
if(!empty($nome)){
    $clausulad .=" and nome like('%".$nome."%')  ";
}
if(!empty($nnfe)){
    $clausulad.=" and nnfe like('%".$nnfe."%') ";    
}

if(!empty($status)){
    $clausulad .=" and status ='".$status."'  ";
}else{
    $clausulad .=" and status = 'PENDENTE,ABERTO,FECHADO,QUITADO' ";
}
if(!empty($idcontapagar)){
    $clausulad .=" and idcontapagar ='".$idcontapagar."'  ";    
}
if(!empty($idformapagamento)){
    $clausulad .=" and idformapagamento ='".$idformapagamento."'  ";    
}
/*
 * colocar condição para executar select
 */
if($_GET){
	if ($clausulad ==""){
		$clausulad = 1;
	}
    
    $sql = "SELECT * FROM vwcontapagaritem where 1 ". $clausulad ." ". getidempresa('idempresa','envioemail')." order by datapagto asc";
					

    if (!empty($sql)){
	
	$res = d::b()->query($sql) or die("Falha ao pesquisar contas: " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$ires = mysqli_num_rows($res);
    }
}
?>
<html>
<head>
<title>Contas de Agrupamento(s)</title>
<link href="../css/rep.css" media="all" rel="stylesheet" type="text/css" />
<style>
html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 11px;
	margin:0px;
	padding:0px;
}

body{
	margin:0px;
	padding:0px;
}
.tbrepheader{
	border: 0px;
	width: 100%;
}
.tbrepheader .header{
	font-size: 13px;
	font-weight: bold;
}

.tbrepheader .subheader{
	font-size: 10px;
	color: gray;
}
.tbrepheader .titulo{
	font-size: 18px;
	font-weight: bold;
}
.tbrepheader .res{
	font-size: 18px;
}
.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}

.normal td{
	border: 1px solid silver;
	padding: 0px 3px 0px 3px;
}

.normal .header{
	font-size: 10px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
}
.normal .res{
	font-size: 11px;
}
.normal .res .link{
	background-color:#FFFFFF;
	cursor:pointer;
}
.normal .res .tot{
	background-color:#E8E8E8;
	font-weight: bold;	
	text-align: center;
}
.normal .res .inv{
	border: 0px;
}
.normal .tdcounter{
	border:1px dotted rgb(222,222,222);
	background-color:white;
	color:silver;
	font-size:8px;
}
.newreppage{
	page-break-before: always;
}
.fldsheader{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	padding-bottom: 5px;
	padding-left:5px;
}
.fldsheader legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
.fldsfooter{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	margin-top: 5px;
	padding-left:5px;
}
.fldsfooter legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
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
a.btbr20{
	display: block;
}

</style>
</head>
<body>
<?
/*
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	    <tr>
		<td class="rotulo">Emissão</td>
		<td><font class="9graybold">entre</font></td>
		<td><input name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>"></td>
	    </tr>
	    <tr>
		<td align="right">Cte:</td>
		<td colspan="10"><input name="nnfe" id="nnfe" type="text" size="10" style="width: 180px;" value="<?=$nnfe?>"></td>
	    </tr>
	    <tr>
		<td align="right">Conta:</td>
		<td colspan="10"><input name="idcontapagar" id="idcontapagar" type="text" size="10" style="width: 180px;" value="<?=$idcontapagar?>"></td>
	    </tr>
	    <tr>
		<td align="right">Nome:</td>
		<td colspan="10"><input name="nome" id="nome" type="text" size="10" style="width: 180px;" value="<?=$nome?>"></td>
	    </tr>
	    <tr>
		<td align="right">Status:</td> 
		<td colspan="10">
		    <select name="status"  id="status" vnulo>
			<option value=""></option>
			<?fillselect("select 'PENDENTE','Pendente'  union select 'PAGAR','Pagar' union select 'QUITADO','Quitado' union select 'INATIVO','Inativo'",$status);?>
		    </select>	
		</td>
	    </tr>	
	</table>	
	<div class="row"> 
	    <div class="col-md-8">
		    <a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=contapagar&_acao=i');">

			<font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Nova conta</font>
		    </a>	
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	   
	</div>
        </div>
    </div>
    </div>
</div>
<?
*/
echo "<!--";
echo $sqlant;
echo "-->";
echo "<!--";
echo $sql;
echo "-->";
if($_GET and $ires >0){
?>
	<div class="panel-heading">Conta(s) Item</div>
	<table class="normal" >
	<tr class="header">
	    <td align="center">NFe</td>
	    <td align="center">Nome</td>
	    <td align="center">Emissão</td>
	    <td align="center">Forma Pagto.</td>
	    <td align="center">Valor</td>
	     <td align="center">Status</td>	     
	    <td align="center">Conta</td>	    	
	    <td align="center">Pagamento</td>
	</tr>
<?
    while ($row = mysqli_fetch_array($res)){
	if($row['nnfe']){
	    $nnfe=$row['nnfe'];
	}else{
	    $nnfe=$row['idnf'];
	}

?>
	<tr class="res">
	    
	    
	    
	    <td align="center">
		<a target="_blank" href="/?_modulo=nfentrada&_acao=u&idnf=<?=$row['idobjetoorigem']?>"><?=$nnfe?></a>
	   </td>
	    <td align="center"><?=$row['nome']?></td>
	    <td align="center"><?=dma($row['dtemissao'])?></td>	
	    <td align="center"><?=$row['formapgto']?></td>
	    <td align="center"><?=$row['valor']?></td>
	    <td align="center">
		<?=$row['status']?>
	    </td>
	    <td align="center">
		<?if($row['idcontapagar']){?>
		<a target="_blank" href="/?_modulo=contapagar&_acao=u&idcontapagar=<?=$row['idcontapagar']?>"><?=$row['idcontapagar']?></a>
		<?}?>
	    </td>
	    <td align="center"><?=dma($row['datareceb'])?></td>	
	</tr>

<? 
    }//while ($row = mysqli_fetch_array($res)){
?>
	</table>
    
    <p> <p> <p> <p> <p> <p> <p> <p> <p> <p>
<? 
}//if($_GET and $ires >0){
?>
<script>
function altstatus(inid,vthis){	
	
    CB.post({
	    objetos: "_x_u_contapagaritem_idcontapagaritem="+inid+"&_x_u_contapagaritem_status="+$(vthis).val()
	    ,parcial:true
	    ,reload:false
    });

}
function pesquisar(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var nome = $("[name=nome]").val();
    var status = $("[name=status]").val();
    var idcontapagar= $("[name=idcontapagar]").val();
    var nnfe=$("[name=nnfe]").val();
    var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&nome="+nome+"&status="+status+"&nnfe="+nnfe+"&idcontapagar="+idcontapagar;
    CB.go(str);
}
$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>