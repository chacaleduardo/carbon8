<?
require_once("../inc/php/functions.php");
if(!empty($_GET["reportexport"])){
	ob_start();//não envia nada para o browser antes do termino do processamento
}
require_once("../inc/php/validaacesso.php");

$_1_u_contapagar_idcontapagar =$_GET['idcontapagar'];

$_header="Contas a Receber";
$idpessoa		= $_GET["idpessoa"];
$nome			= $_GET["nome"];
$emissao_1		= $_GET["emissao_1"];
$emissao_2		= $_GET["emissao_2"];
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$valor_1		= trim($_GET["valor_1"]);
$valor_2		= trim($_GET["valor_2"]);
$controle		= $_GET["controle"];
$statuspgto		= $_GET["statuspgto"];
$formapgto		= $_GET["formapgto"];
$nnfe			= $_GET["nnfe"];

// GVT - 01/04/2020 - adicionado a condição para que só apareçam notas caso haja algum filtro de pesquisa
if(!empty($idpessoa) or !empty($nome) or !empty($emissao_1) or !empty($emissao_2) or !empty($vencimento_1) 
	or !empty($vencimento_2) or !empty($valor_1) or !empty($valor_2) or !empty($controle) or !empty($statuspgto) or !empty($formapgto) or !empty($nnfe)){
	$aux = true;
}else{
	$aux = false;
}
$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);
if($flgdiretor<1){
    $clausula .= " visivel = 'S' and ";
}

if(!empty($vencimento_1) and !empty($vencimento_2)  and $vencimento_1!='undefined'  and $vencimento_2!='undefined'){
	$dtvencimento1= validadate($vencimento_1);
	$dtvencimento2 = validadate($vencimento_2);
	// trabalhando a primeira data
	$dtvenc1= strtotime($dtvencimento1);
	// trabalhando a segunda data
	$dtvenc2= strtotime($dtvencimento2);
}

if (!empty($idpessoa) and $idpessoa!='undefined'){
	$clausula .= " idpessoa = " . $idpessoa ." and ";
}
if (!empty($nome)  and $nome!='undefined'){
	$clausula .= " nomefan like '%" . $nome ."%' and ";
}
if(!empty($exercicio) and $exercicio!='undefined'){
	if(is_numeric($exercicio)){
		$clausula .= " exercicio = " . $exercicio ." and ";
	}else{
		die ("O Exerc&iacute;cio informado possui caracteres inv&aacute;lidos: [".$exercicio."]");
	}
}
if (!empty($emissao_1) and !empty($emissao_2)  and $emissao_1!='undefined'  and $emissao_2!='undefined'){
	$dataini = validadate($emissao_1);
	$datafim = validadate($emissao_2);
	if ($dataini and $datafim){
		$clausula .= " (emissao  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
		$subtitulo = "Relat&oacute;rio das Notas Fiscais-Faturas emitidas entre ".$emissao_1." e ".$emissao_2."";
	}else{
		die ("Datas de emiss&atilde;o n&atilde;o V&aacute;lidas!");
	}
}
if (!empty($valor_1) and !empty($valor_2) and $valor_2!='undefined' and $valor_1!='undefined'){
	if (is_numeric($valor_1) and is_numeric($valor_2)){
		$clausula .= " (total BETWEEN " . $valor_1 ." and " .$valor_2 .")  and ";
	}else{
		die ("Os valores de Nota informados [".$valor_1."] e [".$valor_2."] s&atilde;o inv&aacute;lidos!");
	}
}
if (!empty($controle)and $controle!='undefined'){
	$clausula .= " idcontapagar = " . $controle ." and ";
}
if (!empty($nnfe) and $nnfe!='undefined'){
	$clausula .= " nnfe = " . $nnfe ." and ";
}
if (!empty($vencimento_1) or !empty($vencimento_2)){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);
	if ($dataini and $datafim){		
		$clausula .=" datareceb BETWEEN '" . $dataini ."' and '" .$datafim ."' and ";				
	}else{
		die ("Datas de vencimento n&atilde;o V&aacute;lidas!");
	}
}
if (!empty($statuspgto)){
	if($statuspgto == "VENCIDO"){		
		//Calcula se o vencimento eh maior que  NOW()
		$clausula .= "   status = 'PENDENTE' and
			 date(datareceb) < date(now()) and";
	}elseif($statuspgto == "PENDENTE"){
		//Calcula se o vencimento eh maior que  NOW()
		$clausula .= "   status = 'PENDENTE' and";		
	}elseif($statuspgto == "QUITADO"){
		//Calcula se o vencimento eh maior que  NOW()
		$clausula .= "  status = 'QUITADO' and";		
	}
}

if (!empty($formapgto))
{
	//LTM (23-06-2021): Seleciona o tipo de Forma de Pagamento
	$clausula .= "  idformapagamento = '$formapgto' and";		
}

?>
<html>
<head>
<title><?=$_header?></title>
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
<?
$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";


$sqlfig="select logosis from empresa where idempresa =".cb::idempresa();
$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
$figrel=mysqli_fetch_assoc($resfig);

//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
//$figurarelatorio = "../inc/img/repheader.png";
$figurarelatorio = $figrel["logosis"];
if($_GET and !empty($clausula) and $aux){
	//$clausula .= " status in ('FECHADO','CONCLUIDO')  and ";
    $clausula = "where " . substr($clausula,1,strlen($clausula) - 5);
    $sql = "select * from vw_contapagar " . $clausula. " ".getidempresa('idempresa','contapagar')."  order by datareceb asc,cpfcnpj";
    echo "<!--";
    echo $sql;
    echo "-->"; 
	// die($sql);
	$res = d::b()->query($sql) or die("Falha ao pesquisar NF : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$ires = mysqli_num_rows($res);
	$somatotais = 0;
	$totalpendente = 0;
	$servico=0;
	$produto=0;

?>
<table class="tbrepheader">
<tr>
    <td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
    <td class="header">Contas a Receber</td>
</tr>
</table>
<br>
<fieldset class="fldsheader">
  <legend>Início da Impressão <?=$_nomeimpressao?></legend>
</fieldset>
<table class="normal">
    <tr class='header'>
        <td>Nº N.F.</td>   
        <td>Emiss&atilde;o</td>   
        <td>Raz&atilde;o Social</td>           
        <td>CNPJ/CPF</td>       
        <td>Vencimento</td>
        <td>Valor NF</td>
		<td>Forma Pagamento</td>
        <td>Parcela</td>   
        <td>Valor</td> 
        <td>Status</td>
    </tr>
    <tr class='res'>
<?
$conteudoexport;// guarda o conteudo para exportar para csv
$conteudoexport='"Nº N.F.";"Emissão";"Razão Social";"CNPJ/CPF";"Vencimento";"Valor NF";"Forma Pagamento";"Parcela";"Valor";"Status"';
$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV

    $sql = "select * from vw_contapagar " . $clausula. " ".getidempresa('idempresa','contapagar')."  order by datareceb asc, cpfcnpj";
	$res = d::b()->query($sql) or die("Falha ao pesquisar NF : " . mysqli_error(d::b()) . "<p>SQL: $sql");
    $cnpj='';
    while($rowp=mysqli_fetch_assoc($res)){
    $conteudoexport.='"'.$rowp["nnfe"].'";"'.$rowp["emissaoformatado"].'";"'.$rowp["nome"].'";"\''.$rowp['cpfcnpj'].'";"'.dma($rowp['datareceb']).'";"R$'.number_format(tratanumero($rowp['subtotal']), 2, ',', '.').'";"'.$rowp['descricao'].'";"'.$rowp['parcela']."/".$rowp['parcelas'].'";"R$'.number_format(tratanumero($rowp['valor']), 2, ',', '.').'";"'.$rowp['status'].'";';	
    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
?>
 <tr> 
	 <td><?=$rowp["nnfe"]?></td>
	 <td><?=$rowp["emissaoformatado"]?></td>
	 <td>
		 <?=$rowp["nomefan"]?>
	</td>
	<td><?=$rowp['cpfcnpj'];?></td>
	<td><?=dma($rowp["datareceb"])?></td>
	<? /* <td><?=$rowp["numerorps"]?></td> */?>
	<td >R$<?=number_format(tratanumero($rowp['subtotal']), 2, ',', '.');?></td>
	<td><?=$row['descricao'];?></td>
	<td><b><?=$rowp["parcela"]?>/<?=$rowp["parcelas"]?></b></td>
	<td>R$<?=number_format(tratanumero($rowp["valor"]), 2, ',', '.');?></td>
	<td><?=$rowp["status"]?></td>
  </tr>

 <?}//while($rowp=mysqli_fetch_assoc($resp)){				
	?>	
	
    </tr>
</table>
<?}?>

</body>
</html>
<?
if(!empty($_GET["reportexport"])){
	ob_end_clean();//não envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$infilename = empty($_header)?$_rep:$_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	header("Content-type: text/csv; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $conteudoexport;
	
}
?>