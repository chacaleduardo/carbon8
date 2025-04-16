<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
	require_once("../inc/php/cbpost.php");
}

//ini_set("display_errors","1");
//error_reporting(E_ALL);
################################################## Atribuindo o resultado do metodo GET
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
//PHOL - 03/08/2021: Adicionado campo de pesquisa por divisao/plantel e campo de pesquisa cpfcnpj
$cpfcnpj        = $_GET['cpfcnpj'];
$plantel        = $_GET['idplantel'];

if(empty($_GET["idempresa"])){
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["idempresa"];
}

// GVT - 01/04/2020 - adicionado a condição para que só apareçam notas caso haja algum filtro de pesquisa
if(!empty($idpessoa) or !empty($nome) or !empty($emissao_1) or !empty($emissao_2) or !empty($vencimento_1) 
	or !empty($vencimento_2) or !empty($valor_1) or !empty($valor_2) or !empty($controle) or !empty($statuspgto) or !empty($formapgto) or !empty($nnfe) or !empty($idempresa)){
	$aux = true;
}else{
	$aux = false;
}

//$clausula .= " vencimento > '2009-01-01' and ";
//print_r($_SESSION["post"]);
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
	$clausula .= " ( nomefan like '%" . $nome ."%' or nome like '%" . $nome ."%' or  nomefanfat like '%" . $nome ."%' or nomefat like '%" . $nome ."%')  and ";
}
if (!empty($plantel)  and $plantel!='undefined'){
	$clausula .= "  idplantel = ".$plantel."  and ";
}
if (!empty($idempresa)  and $idempresa!='undefined'){
	$clausula .= "  idempresa = ".$idempresa."  and ";
}

if (!empty($cpfcnpj)  and $cpfcnpj!='undefined'){
	$cpfcnpj = formatarCPF_CNPJ($cpfcnpj,false);
	$clausula .= "  cpfcnpj = ".$cpfcnpj."  and ";
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

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<script >
var reloadpage = true;//Utilizado para informar à  req.xml para efetuar refresh APàS a respota
var xmlonreadystate = "xmldocU=xmldoc.toUpperCase();if(xmldocU.indexOf('ERR')>0){alert(xmldoc);}";


/*
 * Funcao para preencher automaticamente valores de campos "gemeos" ex: data_1 e data_2
 */
function fill_2(inobj){
	//Confirma se o objeto possui a identificacao correta (nomecampo_1) para gemeos
	if(inobj.id.indexOf("_2") > -1) {		
		var strnome_1 = inobj.id.replace("_2","_1");
		var obj_1 = document.getElementById(strnome_1);
		if(inobj != null && inobj.value == ""){
			inobj.value = obj_1.value;
			inobj.select();
		}
		//if(inobj.value != "" and inobj.value != undefined){			
		//}
	}
}

</script>
<style>
@media screen{
    .print{
            display: none !important;
    }
    

}

@media print {
	a {
		text-decoration: none;
	}
    .screen{
        display: none !important;
    }

	tr.header {
		background-color: gray;
	}
	td.esconde{
		display: none;
	}
tr:nth-child(even) {
	background-color: #f2f2f2 !important;
	}

    html, body {
        height: auto;
        font-size: 10pt; /* changing to 10pt has no impact */
    }


}
</style>

<div class="row screen">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem de Notas Fiscais</div>
        <div class="panel-body" >
	<table>
		<tr>
			<td class="rotulo">Empresa</td>
			<td></td>
			<td><select id="_empresa" name="_empresa" onchange="atualizapagamento(this)">
					<?
					$sql ='SELECT e.idempresa,e.nomefantasia from empresa e where exists (select 1 from matrizconf m where m.idmatriz ='.cb::idempresa().' and m.idempresa=e.idempresa) and e.status="ATIVO"
					UNION
					SELECT idempresa,nomefantasia from empresa where idempresa ='.cb::idempresa().';';
					fillselect($sql,$idempresa);
					?>				
				</select></td>	
		</tr>
		<tr>
			<td class="rotulo">Conta</td>
			<td></td>
			<td><input type="text" name="controle" vpar="" id="controle" value="<?=$controle?>" autocomplete="off" class="input10"></td>	
		</tr>
		<tr> 
			<td class="rotulo">Nome Fantasia</td>
			<td></td>
			<td><input type="text" name="nome" vpar="" value="<?=$nome?>" autocomplete="off" class="input10"></td>
		</tr>
		<tr> 
			<td class="rotulo">CPF/CNPJ</td>
			<td></td>
			<td><input type="text" name="cpfcnpj" vpar="" value="<?=formatarCPF_CNPJ($cpfcnpj)?>" autocomplete="off" class="input10"></td>
		</tr>
		<tr> 
			<td class="rotulo">Data Emiss&atilde;o</td>
			<td><font class="9graybold">entre</font></td>
			<td>
				<input name="emissao_1" class="calendario"  vpar="" id="emissao_1" value="<?=$emissao_1?>" autocomplete="off" type="text"  class="input10" onchange="this.focus()">			
			</td>
			<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
			<td><input name="emissao_2"  class="calendario"  vpar="" id="emissao_2" value="<?=$emissao_2?>" autocomplete="off" type="text"  class="input10" onchange="this.focus()">
					
			</td>				
		</tr>
		<tr>
			<td class="rotulo">Data Vencimento</td>
			<td><font class="9graybold">entre</font></td>
			<td><input name="vencimento_1"  class="calendario" vpar="" id="vencimento_1" value="<?=$vencimento_1?>" autocomplete="off" type="text"  class="input10" onchange="this.focus()" vnulo>
				
			</td>
			<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
			<td>
				<input name="vencimento_2"  class="calendario" vpar="" id="vencimento_2" value="<?=$vencimento_2?>" autocomplete="off" type="text"  class="input10" onfocus="fill_2(this)" vnulo>
				
			</td>
		</tr>
		<tr>
			<td class="rotulo">Valor da Nota</td>
			<td><font class="9graybold">entre</font></td>
			<td><input name="valor_1" vpar="" value="<?=$valor_1?>" autocomplete="off" type="text"  class="input10"></td>
			<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
			<td><input name="valor_2" vpar="" value="<?=$valor_2?>" autocomplete="off" type="text"  class="input10"></td>
		</tr>
<?	/*/	
		<tr> 
			<td class="rotulo">N&ordm;&nbsp;RPS</td>
			<td></td>
			<td><input type="text" name="numerorps" vpar="" value="<?=$nrps?>" autocomplete="off" class="input10"></td>
		</tr>
	*/
?>		
		<tr> 
			<td class="rotulo">N&ordm;&nbsp;NFs</td>
			<td></td>
			<td><input type="text" name="nnfe" vpar="" value="<?=$nnfe?>" autocomplete="off" class="input10"></td>
		</tr>
		<tr> 
			<td class="rotulo">Status</td>
			<td></td>
			<td>
				<select name="statuspgto">				
					<?
					fillselect("SELECT 'PENDENTE','A Vencer' UNION SELECT 'QUITADO','Quitado' UNION SELECT 'VENCIDO','Vencido' ",$statuspgto);
					?>
					<option value=""></option>
				</select>				
			</td>
		</tr>
		<tr> 
			<td class="rotulo">Forma Pagamento</td>
			<td></td>
			<td>
				<select name="formapgto">
					<option value=""></option>
					<?=getPagamentoFiltro($formapgto,$idempresa)?>				
				</select>
			</td>
		</tr>
		<tr> 
			<td class="rotulo">Divisão</td>
			<td></td>
			<td>
				<select name="plantel">
					<option value=""></option>
					<?
					fillselect("SELECT idplantel, plantel
								FROM plantel 
								WHERE status = 'ATIVO' ".getidempresa('idempresa','formapagamento')." ORDER BY plantel", $plantel);
					?>				
				</select>
			</td>
		</tr>
	</table>
	<div class="row"> 
	    <div class="col-md-8">
		
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
                <i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer" onclick="imprimir()" title="Imprimir"></i>
				<a href="report/recontareceber.php?controle=<?=$_GET["controle"]?>&nome=<?=$_GET["nome"]?>&emissao_1=<?=$_GET["emissao_1"]?>&emisaao_2=<?=$_GET["emissao_2"]?>&vencimento_1=<?=$_GET["vencimento_1"]?>&vencimento_2=<?=$_GET["vencimento_2"]?>&valor_1=<?=$_GET["valor_1"]?>&valor_2=<?=$_GET["valor_2"]?>&nnfe=<?=$_GET["nnfe"]?>&statuspgto=<?=$_GET["statuspgto"]?>&formapgto=<?=$_GET["formapgto"]?>&_idempresa=<?=$idempresa?>&reportexport=csv" target="_blank">
					<i class="fa fa-file-excel-o  fade pointer fa-2x hoververde btn-lg"  title="Exportar .csv"></i>
				</a>
	    </div>	   
	</div>
	</div>
    </div>
    </div>
</div>



<?
if($_GET['idempresa'] and !empty($clausula) and $aux and $_GET['pesquisar']=='Y'){
	//$clausula .= " status in ('FECHADO','CONCLUIDO')  and ";
    $clausula = "where " . substr($clausula,1,strlen($clausula) - 5);
    $sql = "select * from vw_contapagar " . $clausula. " order by datareceb asc,cpfcnpj";
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
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Administra&ccedil;&atilde;o de Notas Fiscais (<?=$ires?> Notas)</div>
        <div class="panel-body">

	<table style="width: 100%;">
	<tr class="header screen">	
	    <td nowrap>N&ordm; N.F.</td>
	    <td>Emiss&atilde;o</td>
	    <td class="esconde">Nome Fantasia</td>
	    <td>CNPJ/CPF</td>
	    <? /* <td>RPS</td>*/?>
	    <td>Vencimento</td>
	    <td>Valor NF</td>
		<td align="center">Forma Pagamento</td>
	    <td>Parcela</td>
	    <td>Valor</td>		
	    <td>Status</td>
	    <td class="screen" >Obs. Cobrança</td>
	</tr>
<?
	$resoe = "resodd";
	$cnpj = '';
	while ($row = mysqli_fetch_array($res)){
            //if($row["parcela"]=='1'){
                $somatotais = $somatotais + $row["total"];
                if($row["tipoobjeto"]=='notafiscal'){
                        $servico=$servico+$row["valor"];	//somar servicos
                }elseif($row["tipoobjeto"]=='nf'){
                        $produto=$produto+$row["valor"];	//somar produtos
                }
            //}
		$totalpendente=$totalpendente+$row["valor"];
                $totalsub=$totalsub+$row["subtotal"];
		if($row["tipoobjeto"]=='notafiscal'){
			
			$linknf = "janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=".$row['idnotafiscal']."');";
				
		}elseif($row["tipoobjeto"]=='nf'){
			
			//ajustar o link
			$sqln="select tiponf from nf where idnf=".$row["idnotafiscal"];
			$resn=d::b()->query($sqln) or die("erro ao buscar o tipo da NF sql= ".$sqln);
			$rown=mysqli_fetch_assoc($resn);
                        if($rown['tiponf']=="V"){
                            $linknf = "janelamodal('?_modulo=pedido&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }elseif($rown['tiponf']=='R'){
                             $linknf = "janelamodal('?_modulo=comprasrh&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }elseif($rown['tiponf']=='D'){
                             $linknf = "janelamodal('?_modulo=comprassocios&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }else{
                            $linknf = "janelamodal('?_modulo=nfentrada&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }
		}	
?>
 <tr class="respreto screen"> 
	 <td><a onclick="<?=$linknf?>"><?=$row["nnfe"]?></a></td>
	 <td><?=$row["emissaoformatado"]?></td>
	 <td class="esconde">
		 <a  class="hoverazul pointer" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')"><?=$row["nomefan"]?></a>
		</td>
		<td align="left"><?=$row['cpfcnpj'];?></td>
		<td><?=dma($row["datareceb"])?></td>
   <? /* <td><?=$row["numerorps"]?></td> */?>
  	<td align="center"><?=$row['subtotal'];?></td>
	<td style="border-left: 0px;border-right: 0px;" align="center"><?=$row['descricao'];?></td>
	<td width="30" align="center"><b><?=$row["parcela"]?>/<?=$row["parcelas"]?></b></td>
	<td><a title="Editar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$row["idcontapagar"]?>');"><?=number_format(tratanumero($row["valor"]), 2, ',', '.');?></a></td>
	<td><?=$row["status"]?></td>
	<td colspan="5" class="screen">
		<!-- <input name="_99_u_contapagar_idcontapagar" type="hidden" value="<?//=$row["idcontapagar"]?>"> -->
		<textarea class="caixa screen "  name="obscobranca"  style="width: 260px; height: 20px;" onchange="preencheobs(this,<?=$row["idcontapagar"]?>);" ><?=$row["obscobranca"];?></textarea>
                <div class='print'><?= nl2br($row["obscobranca"])?></div>
	</td>
  </tr>
<?
	}//while ($row = mysqli_fetch_array($res)){
?>  
<tr class="respreto screen">
	<td colspan="5"></td>
	<td align="right"><b><?=number_format(tratanumero($totalsub), 2, ',', '.');?></b></td>
	<td colspan='1'></td>
	<td align="right"><b><?=number_format(tratanumero($totalpendente), 2, ',', '.');?></b></td>
	<td colspan='8'></td>
</tr>
</table>
<table border="1" style="width: 100%;" class="print">
<tr class="header print">	
	    <th nowrap>N&ordm; N.F.</th>
	    <th>Emiss&atilde;o</th>
	    <th class="print">Raz&atilde;o Social</th>
	    <th>CNPJ/CPF</th>
	    <?/* <td>RPS</td>*/?>
	    <th>Vencimento</th>
	    <th>Valor NF</th>
		<th align="center">Forma Pagamento</th>
	    <th>Parcela</th>
	    <th>Valor</th>		
	    <th>Status</th>
	</tr>
<?	
	$total = 0;
	$sql = "select * from vw_contapagar " . $clausula. " ".getidempresa('idempresa','contapagar')."  order by cpfcnpj, datareceb asc";
	$res = d::b()->query($sql) or die("Falha ao pesquisar NF : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$cnpj='';
	while ($row = mysqli_fetch_array($res)){
		

		if($cnpj == ''){
			$cnpj = $row['cpfcnpj'];
		}elseif ($cnpj !=$row['cpfcnpj']) {
			$cnpj = $row['cpfcnpj']
			?>
			<tr>
				<td style="border-left: 0px;border-right: 0px;" colspan="6"></td>
				<td style="border-left: 0px;border-right: 0px;" colspan="1"><b>Total: </b></td>
				<td style="border-left: 0px;" colspan="4"><b><?=number_format(tratanumero($total), 2, ',', '.');?></b></td>
			</tr>
			<?
			$total= 0;
		}
		$total = $total + $row['valor'];
		if($row["tipoobjeto"]=='notafiscal'){
			
			$linknf = "janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=".$row['idnotafiscal']."');";
				
		}elseif($row["tipoobjeto"]=='nf'){
			
			//ajustar o link
			$sqln="select tiponf from nf where idnf=".$row["idnotafiscal"];
			$resn=d::b()->query($sqln) or die("erro ao buscar o tipo da NF sql= ".$sqln);
			$rown=mysqli_fetch_assoc($resn);
                        if($rown['tiponf']=="V"){
                            $linknf = "janelamodal('?_modulo=pedido&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }elseif($rown['tiponf']=='R'){
                             $linknf = "janelamodal('?_modulo=comprasrh&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }elseif($rown['tiponf']=='D'){
                             $linknf = "janelamodal('?_modulo=comprassocios&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }else{
                            $linknf = "janelamodal('?_modulo=nfentrada&_acao=u&idnf=".$row["idnotafiscal"]."');";
                        }
		}
?>
 <tr class="print"> 
	 <td style="border-left: 0px;border-right: 0px;"><?=$row["nnfe"]?></td>
	 <td style="border-left: 0px;border-right: 0px;"><?=$row["emissaoformatado"]?></td>
	 <td style="border-left: 0px;border-right: 0px;" class="print">
		 <?=$row["nomefat"]?>
	</td>
	<td style="border-left: 0px;border-right: 0px;" align="left"><?=$row['cpfcnpj'];?></td>
	<td style="border-left: 0px;border-right: 0px;"><?=dma($row["datareceb"])?></td>
		<? /* <td><?=$row["numerorps"]?></td> */?>
	<td style="border-left: 0px;border-right: 0px;" align="center"><?=$row['subtotal'];?></td>
	<td style="border-left: 0px;border-right: 0px;" align="center"><?=$row['descricao'];?></td>
	<td style="border-left: 0px;border-right: 0px;" width="30" align="center"><b><?=$row["parcela"]?>/<?=$row["parcelas"]?></b></td>
	<td style="border-left: 0px;border-right: 0px;"><?=number_format(tratanumero($row["valor"]), 2, ',', '.');?></td>
	<td style="border-left: 0px;" ><?=$row["status"]?></td>
  </tr>
 
<?
	}//while ($row = mysqli_fetch_array($res)){
?>
  <tr>
	<td style="border-left: 0px;border-right: 0px;" colspan="6"></td>
	<td style="border-left: 0px;border-right: 0px;" colspan="1"><b>Total: </b></td>
	<td style="border-left: 0px;" colspan="4"><b><?=number_format(tratanumero($total), 2, ',', '.');?></b></td>
  </tr>
  <tr>
      <td style="border-left: 0px;border-right: 0px;" colspan="5"></td>
      <td style="border-left: 0px;border-right: 0px;"><b><?=number_format(tratanumero($totalsub), 2, ',', '.');?></b></td>
      <td style="border-left: 0px;border-right: 0px;" colspan='1'></td>
      <td style="border-left: 0px;border-right: 0px;"><b><?=number_format(tratanumero($totalpendente), 2, ',', '.');?></b></td>
	  <td style="border-left: 0px;" colspan='8'></td>
  </tr>
</table>
<br>
<table class="table table-striped planilha" >
	<tr >
		<th>Qt. notas</th>
		<!-- th>Total</th>
		<th>Total Parcelas</th !-->
		<th>Produto</th>
		<th>Serviço</th>
	</tr>
	<tr > 
		<td><?=$ires ?></td>
		<!-- td><?=number_format(tratanumero($somatotais), 2, ',', '.');?> </td>
		<td bgcolor="#FFA500"> <?=number_format(tratanumero($totalpendente), 2, ',', '.');?></td -->	
		<td><?=number_format(tratanumero($produto), 2, ',', '.');?></td>
		<td><?=number_format(tratanumero($servico), 2, ',', '.');?></td>			
	</tr>
</table>
	</div>
    </div>
    </div>
</div>
<?
}//if($_GET){ 
?>

<script>
function pesquisar(){
    var controle = $("[name=controle]").val();   
    var nome = $("[name=nome]").val();
    var emissao_1 = $("[name=emissao_1]").val();
    var emissao_2 = $("[name=emissao_2]").val();
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var valor_1 = $("[name=valor_1]").val();
    var valor_2 = $("[name=valor_2]").val();    
    var nnfe = $("[name=nnfe]").val();
    var statuspgto = $("[name=statuspgto]").val();
	var formapgto = $("[name=formapgto]").val();
	var cpfcnpj = $("[name=cpfcnpj]").val();
	var plantel = $("[name=plantel]").val();
	var empresa = $("[name=_empresa]").val();
  
    var str="controle="+controle+"&nome="+nome+"&idempresa="+empresa+"&pesquisar=Y&cpfcnpj="+cpfcnpj+"&idplantel="+plantel+"&emissao_1="+emissao_1+"&emissao_2="+emissao_2+"&vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&valor_1="+valor_1+"&valor_2="+valor_2+"&nnfe="+nnfe+"&statuspgto="+statuspgto+"&formapgto="+formapgto;
    CB.go(str);
}
$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

function preencheobs(vthis,inidcontapagar){
    CB.post({
	    "objetos":"_x_u_contapagar_idcontapagar="+inidcontapagar+"&_x_u_contapagar_obscobranca="+$(vthis).val()
	    ,parcial:true
    });
}

function imprimir(){
    
    window.print();
  
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape


function atualizapagamento(valor)
{
	var idempresa = $("[name=_empresa]").val();

	var str="idempresa="+idempresa;
	CB.go(str);
}

</script>