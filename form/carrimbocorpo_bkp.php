<?
require_once("../inc/php/validaacesso.php");

$acao =$_GET["acao"];

$exercicio	= $_GET["exercicio"];
$nome	= $_GET["cliente"];
$registro	= $_GET["registro"];
$status	= $_GET["status"];

$controleass = $_SESSION[$struniqueid]["controleass"];

IF(!empty($exercicio)){
    $clausula=" and ta.exercicio =".$exercicio." ";
}else{
    $year  = ( date("Y"));
     $clausula=" and ta.exercicio =".$year." ";
}

if(!empty($registro)){
    $clausula.=" and ta.idregistro =".$registro." ";
}
if(!empty($nome)){
    $clausula.=" and p.nome like ('%".$nome."%') ";
}

if($status=='ASSINADO'){
    $exists=" exists "; 
    $vstatus='ASSINADO';
}else{
    $vstatus='DEVOLVIDO';
    $exists=" not exists "; 
}

if($acao=="ini"){
	$controleass=1;//vai para o primeiro registro	
	// Executa a consulta completa somente 1 vez para recuperar a quantidade total de registros
	$booexeccount = true;
	//Atualiza o Uniqueid da pagina para guardar o ultima pagina utilizada
	//$_SESSION[$struniqueid]["uniqueid"] = $struniqueid;	
}else{
	if($acao=="prox"){
		$controleass=intval($controleass)+1;	
	}elseif($acao=="ant" and $controleass > 1){
		$controleass=intval($controleass)-1;
	}
}
//Apos o incremento da variavel, atribui para a ssesion o valor do proximo registro a ser chamado
$_SESSION[$struniqueid]["controleass"] = $controleass;


/*
 * TRATAMENTO E CONCATENACAO DO SQL PRINCIPAL
 */

	if($booexeccount==true){
		$sqlcount = "select ta.idamostra 
				from amostra ta,pessoa p
			    where ta.idunidade = 9
			    and ta.idpessoa = p.idpessoa
			    ".$clausula."
			    and ta.statustra='".$vstatus."'
			    and not exists (select 1 from resultado r where r.idamostra=ta.idamostra and r.status in('ABERTO','PROCESSANDO'))
			    -- and ".$exists." (select 1 from carrimbo c where c.idobjeto = ta.idamostra and c.tipoobjeto='amostra' and c.status='ASSINADO')";
		//echo($sqlcount.'<br>');
		$rescount = mysql_query($sqlcount) or die("Falha no Relatório de Testescount: " . mysql_error() . "<p>SQL: $sqlcount");
		$qtdcount = mysql_num_rows($rescount);
		
		if(empty($qtdcount)){
			echo '<br><br><br><div align="center">Não existem mais registros.</div> <br> <div align="center">ou <div><br> <div align="center">Não há nenhum registro para os parâmetros informados!</div>';
			echo "<!-- ".$sqlcount." -->";
			die;
		}
		
		$arridresultado = array();
		$iarr = 0;
		//while para gravar todos os resultados para se poder navegar entre eles
		while($rowqtd = mysql_fetch_array($rescount)){
			$iarr++;
			$arridresultado[$iarr]=$rowqtd["idamostra"];			
			$booexeccount = false;
		}		
		//total de registros da consulta
		$_SESSION[$vargetsess]["qtdreg"] = $iarr;
		//grava todos dos os ids de resultados da consulta
		$_SESSION[$vargetsess]["arridres"] = $arridresultado;
	}
	$arridresultado=$_SESSION[$vargetsess]["arridres"];
	
	
	
	$qtdcount = $_SESSION[$vargetsess]["qtdreg"];	
	
if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">Não existem mais registros.</div>';
	die;
}



if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">Não existem mais registros.</div>';
	die;
}

$oAm=getAmostra($arridresultado[$controleass]);
//$idtipoamostra=$oAm["idtipoamostra"];
$idsubtipoamostra=$oAm["idsubtipoamostra"];

$oRes=getResultados($arridresultado[$controleass]);

$oAAm=getAgenteAmostra($arridresultado[$controleass]);

//Verificação de quais inputs serão mostrados no TRA
$arrConfInputs = getAmostraConfInputs(9);

//Verificação de quais inputs serão mostrados no LDA
$arrConfInputsLDA = getAmostraConfInputs(6);

//Mostrar ou esconder divs conforme configuração
function hide($inCol){
	global $arrConfInputs,$idsubtipoamostra;
	
	if($arrConfInputs["arrcoluna"]["TRA"][$inCol][$idsubtipoamostra]){
		return "";
	}else{
		return "hidden";
	}
}


function getAgenteAmostra($inidamostra){
	$sqla="select r.idresultado,l.partida,l.exercicio,l.idlote,l.status,p.descr
			from lote l,resultado r,prodserv p,amostra a
			where l.tipoobjetosolipor='resultado' 
			and l.idobjetosolipor=r.idresultado
			and p.idprodserv = l.idprodserv
			and r.idamostra=a.idamostra
			and a.idamostratra = ".$inidamostra." order by r.ord";
	$resa=d::b()->query($sqla) or die("Erro ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sqla);
	
	$qtdresa=mysqli_num_rows($resa);
	if($qtdresa<1){
		$sqla="select r.idresultado,l.partida,l.exercicio,l.idlote,l.status,p.descr
			from lote l,resultado r,prodserv p,amostra a
			where l.tipoobjetosolipor='resultado' 
			and l.idobjetosolipor=r.idresultado
			and p.idprodserv = l.idprodserv
			and r.idamostra=a.idamostra
			and a.idamostratra = ".$inidamostra." order by r.ord";
		$resa=d::b()->query($sqla) or die("Erro 2 ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sqla);
	}

	$arrColunas = mysqli_fetch_fields($resa);
	$i=0;
	$arrret=array();
	while($r = mysqli_fetch_assoc($resa)){
		$i=$i+1;
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			//$arrret[$i][$col->name]=$robj[$col->name];
			$arrret[$r["idresultado"]][$r["idlote"]][$col->name]=$r[$col->name];//alterado para agrupar pelo resultado, para facilitar os loops
		}
	}
	return $arrret;
}

//@todo: realizar a verificação através de Classes
function validaDataExame($ascriadoem,$dataamostra){
	if($ascriadoem<$dataamostra){
		die("<h1>Erro: Data Final Exame [".$ascriadoem."] < Data Início Exame [".$dataamostra."]</h1>");
	}
}

function buscacarrimbo($inidamostra){
    $sqla="select * from carrimbo c 
	where c.idobjeto = ".$inidamostra."
	and c.tipoobjeto ='amostra' 
	and c.status='ASSINADO'";
    $resa=d::b()->query($sqla) or die("Erro ao buscar carrimbo da amostra : " . mysql_error() . "<p>SQL:".$sqla);
    $qtdca= mysqli_num_rows($resa);
    return $qtdca;
}
	
?>	


<?
$titulo="";
if($oAm["statustra"]=="ABERTO" or $oAm["statustra"]=="ENVIADO"){
	$titulo="Termo de Envio de Amostra";
	$sub="TEA";
}elseif($oAm["statustra"]=="DEVOLVIDO" or $oAm["statustra"]=="ASSINADO"){
	$titulo="Termo de Recepção de Amostra";
	$sub="TRA";
}
?>
<div style="display:table-cell; width: 100%; height: 100%;">
	<pagina class="ordContainer">
		<header class="row margem0.0">
			<div class="logosup col 20"><img src="../inc/img/Logo PB Inata.jpg"></div>
			<div class="titulodoc">
<?=$titulo?>
			</div>
			<div class="col 20"></div>
		</header>
		<div class="row">
			<div class="col 15 rot">N&ordm; <?=$sub?>:</div>
			<div class="col 35">
			    <a title="" href="javascript:janelamodal('../?_modulo=amostratra&_acao=u&idamostra=<?=$oAm["idamostra"]?>')"><?=$oAm["idregistro"]?>/<?=$oAm["exercicio"]?></a>
			
			</div>
			<div class="col 15 rot">Data Registro:</div>
			<div class="col 35"><?=dmahms($oAm["dataamostrah"], true)?></div>
		</div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados do Cliente</div>
			</div>
		</div>
		<div class="row">
			<div class="col 15 rot">Cliente:</div>
			<div class="col 85 quebralinha"><?=$oAm["razaosocial"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Propriedade/Granja:</div>
			<div class="col 85 quebralinha"><?=$oAm["nome"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Endereço:</div>
			<div class="col 85 quebralinha" title="<?=$oAm["idpessoa"]?>">
<?
			if(empty($oAm["enderecosacado"])){
?>
			    <div class="alert alert-warning">
				<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
			    </div>		    
<?
			}else{
			    echo($oAm["enderecosacado"]);
			}
?>
			</div>
		</div>
		<div class="row">
			<div class="col 15 rot">Cnpj:</div>
			<div class="col 35"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></div>
			<div class="col 15 rot">Inscr. Estadual:</div>
			<div class="col 35"><?=$oAm["inscrest"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("responsavelof")?> col 15 rot">Respons. oficial:</div>
			<div class="<?=hide("responsavelof")?> col 35 quebralinha"><?=$oAm["responsavelof"]?></div>
			<div class="<?=hide("responsavelofcrmv")?> col 10 rot">CRMV:</div>
			<div class="<?=hide("responsavelofcrmv")?> col 15"><?=$oAm["responsavelofcrmv"]?></div>
			<div class="<?=hide("responsaveloftel")?> col 10 rot">Tel:</div>
			<div class="<?=hide("responsaveloftel")?> col 15"><?=$oAm["responsaveloftel"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("numeroanimais")?> col 15 rot">Nº de animais:</div>
			<div class="<?=hide("numeroanimais")?> col 85 quebralinha"><?=$oAm["numeroanimais"]?></div>
		</div>		
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados da Amostra</div>
			</div>
		</div>
		<div class="row">
			<div class="<?=hide("idespeciefinalidade")?> col 15 rot">Espécie/Finalidade:</div>
			<div class="<?=hide("idespeciefinalidade")?> col 85 quebralinha"><?=$oAm["especietipofinalidade"]?></div>
		</div>
		<div class="row">
			<div class="col 15 rot">Material colhido:</div>
			<div class="col 20 quebralinha"><?=$oAm["subtipoamostra"]?></div>
			<div class="<?=hide("nroamostra")?> col 15 rot">Quantidade:</div>
			<div class="<?=hide("nroamostra")?> col 20 quebralinha"><?=$oAm["nroamostra"]?></div>
			<div class="<?=hide("datacoleta")?> col 15 rot">Data Coleta:</div>
			<div class="<?=hide("datacoleta")?> col 15"><?=dma($oAm["datacoleta"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("descricao")?> col 15 rot">Descrição:</div>
			<div class="<?=hide("descricao")?> col 85 quebralinha"><?=$oAm["descricao"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("idnucleo")?> col 15 rot">Núcleo:</div>
			<div class="<?=hide("idnucleo")?> col 85"><?=$oAm["nucleo"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("idade")?> col 15 rot">Idade:</div>
			<div class="<?=hide("idade")?> col 85"><?=$oAm["idade"]." ".$oAm["tipoidade"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("lote")?> col 15 rot">Lote:</div>
			<div class="<?=hide("lote")?> col 85 quebralinha"><?=$oAm["lote"]?></div>
		</div>		
		<div class="row">
			<div class="<?=hide("linha")?> col 15 rot">Linha:</div>
			<div class="<?=hide("linha")?> col 35 quebralinha"><?=$oAm["linha"]?></div>
			<div class="<?=hide("regoficial")?> col 15 rot">Nº Registro oficial:</div>
			<div class="<?=hide("regoficial")?> col 35 quebralinha"><?=$oAm["regoficial"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("formaarmazen")?> col 15 rot">Forma de armaz.:</div>
			<div class="<?=hide("formaarmazen")?> col 20"><?=$oAm["formaarmazen"]?></div>
			<div class="<?=hide("meiotransp")?> col 15 rot">Meio de transp.:</div>
			<div class="<?=hide("meiotransp")?> col 50"><?=$oAm["meiotransp"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("condconservacao")?> col 15 rot">Cond. conservação:</div>
			<div class="<?=hide("condconservacao")?> col 85 quebralinha"><?=nl2br($oAm["condconservacao"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("sexo")?> col 15 rot">Sexo:</div>
			<div class="<?=hide("sexo")?> col 20"><?=$oAm["sexo"]?></div>
			<div class="<?=hide("clienteterceiro")?> col 15 rot">Cliente 3&ordm;:</div>
			<div class="<?=hide("clienteterceiro")?> col 50"><?=$oAm["clienteterceiro"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("nucleoorigem")?> col 15 rot">Núcleo origem:</div>
			<div class="<?=hide("nucleoorigem")?> col 20 quebralinha"><?=$oAm["nucleoorigem"]?></div>
			<div class="<?=hide("tipo")?> col 15 rot">Tipo:</div>
			<div class="<?=hide("tipo")?> col 50"><?=$oAm["tipo"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("especificacao")?> col 15 rot">Especificações:</div>
			<div class="<?=hide("especificacao")?> col 20 quebralinha"><?=$oAm["especificacao"]?></div>
			<div class="<?=hide("partida")?> col 15 rot">Partida:</div>
			<div class="<?=hide("partida")?> col 15 quebralinha"><?=$oAm["partida"]?></div>
			<div class="<?=hide("fornecedor")?> col 15 rot">Fornecedor:</div>
			<div class="<?=hide("fornecedor")?> col 20 quebralinha"><?=$oAm["fornecedor"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("datafabricacao")?> col 15 rot">Data fabricação:</div>
			<div class="<?=hide("datafabricacao")?> col 20 quebralinha"><?=$oAm["datafabricacao"]?></div>
			<div class="<?=hide("identificacaochip")?> col 15 rot">Chip/Identif.:</div>
			<div class="<?=hide("identificacaochip")?> col 15 quebralinha"><?=$oAm["identificacaochip"]?></div>
			<div class="<?=hide("diluicoes")?> col 15 rot">Diluições:</div>
			<div class="<?=hide("diluicoes")?> col 20 quebralinha"><?=$oAm["diluicoes"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("nroplacas")?> col 15 rot">Nº Placas:</div>
			<div class="<?=hide("nroplacas")?> col 20 quebralinha"><?=$oAm["nroplacas"]?></div>
			<div class="<?=hide("nrodoses")?> col 15 rot">Nº Doses:</div>
			<div class="<?=hide("nrodoses")?> col 15 quebralinha"><?=$oAm["nrodoses"]?></div>
			<div class="<?=hide("semana")?> col 15 rot">Semana:</div>
			<div class="<?=hide("semana")?> col 20 quebralinha"><?=$oAm["semana"]?></div>
		</div>	
		<div class="row">
			<div class="<?=hide("notafiscal")?> col 15 rot">Nota Fiscal:</div>
			<div class="<?=hide("notafiscal")?> col 20 quebralinha"><?=$oAm["notafiscal"]?></div>
			<div class="<?=hide("vencimento")?> col 15 rot">Vencimento:</div>
			<div class="<?=hide("vencimento")?> col 15 quebralinha"><?=$oAm["vencimento"]?></div>
			<div class="<?=hide("fabricante")?> col 15 rot">Fabricante:</div>
			<div class="<?=hide("fabricante")?> col 20 quebralinha"><?=$oAm["fabricante"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("sexadores")?> col 15 rot">Sexadores:</div>
			<div class="<?=hide("sexadores")?> col 20 quebralinha"><?=$oAm["sexadores"]?></div>
			<div class="<?=hide("localexp")?> col 15 rot">Local específico:</div>
			<div class="<?=hide("localexp")?> col 50"><?=$oAm["localexp"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("lacre")?> col 15 rot">Lacre:</div>
			<div class="<?=hide("lacre")?> col 20 quebralinha"><?=$oAm["lacre"]?></div>
			<div class="<?=hide("tc")?> col 15 rot">Termo de coleta:</div>
			<div class="<?=hide("tc")?> col 50"><?=$oAm["tc"]?></div>
		</div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Dados Epidemiológicos</div>
			</div>
		</div>
		<div class="row">
			<div class="<?=hide("sinaisclinicosinicio")?> col 15 rot">Início sinais clínicos:</div>
			<div class="<?=hide("sinaisclinicosinicio")?> col 85 quebralinha"><?=$oAm["sinaisclinicosinicio"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("sinaisclinicos")?> col 15 rot">Sinais clínicos:</div>
			<div class="<?=hide("sinaisclinicos")?> col 85 quebralinha"><?=nl2br($oAm["sinaisclinicos"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("achadosnecropsia")?> col 15 rot">Achados necrópsia:</div>
			<div class="<?=hide("achadosnecropsia")?> col 85 quebralinha"><?=nl2br($oAm["achadosnecropsia"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("suspclinicas")?> col 15 rot">Suspeitas clínicas:</div>
			<div class="<?=hide("suspclinicas")?> col 85 quebralinha"><?=nl2br($oAm["suspclinicas"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("histproblema")?> col 15 rot">Histórico problema:</div>
			<div class="<?=hide("histproblema")?> col 85 quebralinha"><?=nl2br($oAm["histproblema"])?></div>
		</div>
		<div class="row">
			<div class="<?=hide("morbidade")?>col 15 rot">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
			<div class="<?=hide("morbidade")?>col 20"><?=$oAm["morbidade"]?></div>
			<div class="<?=hide("letalidade")?>col 15 rot">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
			<div class="<?=hide("letalidade")?>col 20"><?=$oAm["letalidade"]?></div>
			<div class="<?=hide("mortalidade")?>col 15 rot">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</div>
			<div class="<?=hide("mortalidade")?>col 15 quebralinha"><?=$oAm["mortalidade"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("usomedicamentos")?> col 15 rot">Uso medicamentos:</div>
			<div class="<?=hide("usomedicamentos")?> col 35 quebralinha"><?=$oAm["usomedicamentos"]?></div>
			<div class="<?=hide("usovacinas")?> col 15 rot">Uso de vacinas:</div>
			<div class="<?=hide("usovacinas")?> col 35 quebralinha"><?=$oAm["usovacinas"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("localcoleta")?> col 15 rot">Local coleta:</div>
			<div class="<?=hide("localcoleta")?> col 85 quebralinha"><?=$oAm["localcoleta"]?></div>
		</div>
		<div class="row">
			<div class="<?=hide("responsavel")?> col 15 rot">Respons. coleta:</div>
			<div class="<?=hide("responsavel")?> col 85 quebralinha"><?=$oAm["responsavel"]?></div>
		</div>
		<div class="rows">
			<div class="<?=hide("observacao")?> col 15 rot">Observação:</div>
			<div class="<?=hide("observacao")?> col 85 quebralinha"><?=nl2br($oAm["observacao"])?></div>
		</div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo">Exames Solicitados</div>
			</div>
		</div>
	<?
	$i=0;
	while (list($k, $v) = each($oRes)){
	?>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<a title="" href="javascript:janelamodal('../?_modulo=resultsuinos&_acao=u&idresultado=<?=$v["idresultado"]?>')"><?echo("LDA: ".$v["idresultado"]." - ".$v["descr"]);?></a>
			</div>
		</div>
	<?
		$i++;
	}
	?>
		<!-- <div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo"></div>
			</div>
		</div> -->
		<hr>
		<br>

	</pagina>
	
<?
$qtdca=buscacarrimbo($arridresultado[$controleass]);
if($qtdca>0){
	$clsfooter="clsFootera";
}else{
	$clsfooter="clsFooterf";
}

?>
	

<div id="Footer" class="<?=$clsfooter?>">
<table width="100%">
<tr>
	<td style="font-size:12px;padding-left:15px;width:150px;"><?echo"Resultado ".$controleass." de ".$qtdcount;?></td>
	<td align="center">		
		<table align="center">
		<tr>
<?
						$stralerta = "";
						if($row["alerta"]=="Y"){
							$stralerta = "checked";
						}
						if(empty($row["idsecretaria"])){
							$varoficial = "N";
						}elseif(!empty($row["idsecretaria"])){
							$varoficial = "Y";
						}
?>		
			<td>
				&nbsp;&nbsp;&nbsp;
				<input type="button" tabindex="2" value="Assinar" id="btassina" class="btassina" onfocus="this.className='btassinafoco';" onblur="this.className='btassina';" onClick="carrimbo(<?=$arridresultado[$controleass]?>,'inserir','assinatura');">
				<input type="button" tabindex="3" value="Retirar" id="btretira" class="btretira" onfocus="this.className='btretirafoco';" onblur="this.className='btretira';" onClick="carrimbo(<?=$arridresultado[$controleass]?>,'retirar','assinatura');">
				&nbsp;&nbsp;&nbsp;
				
			</td>
		</tr>
		</table>
	</td>
	<td style="width:150px;"></td>
</tr>
</table>
</div>
</div>
