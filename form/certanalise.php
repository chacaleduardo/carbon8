<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
 
if($_POST){
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lote";
$pagvalcampos = array(
	"idlote" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from lote where idlote = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

if($_1_u_lote_idprodserv){
    $convestoque=traduzid("unidade","idunidade","convestoque",$idunidadepadrao);
    
    if($convestoque=='N'){
        $unidade=traduzid("prodserv","idprodserv","un",$_1_u_lote_idprodserv); 
    }else{
        $unidade=traduzid("prodserv","idprodserv","unconv",$_1_u_lote_idprodserv);
        if(empty($unidade)){
            $unidade=traduzid("prodserv","idprodserv","un",$_1_u_lote_idprodserv);
        }
    }
}

if($_GET["gerarautomatico"] == 'Y'){
	$grupo = rstr(8);
	$sqlog = "INSERT INTO log (idempresa,sessao,tipoobjeto,idobjeto,tipolog,log,info,criadoem) 
				VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$grupo ."','lote',".$_GET["idlote"].",'CERTAUTOMATICO',
				'Log para verificar a chamada do gerador de PDF automático de certificado de análise','inicio',now())";
	d::b()->query($sqlog);
}

ob_start();
?>
<html>
<head>
<style>
.rotulo{
font-weight: bold;
font-size: 12px;
}
.texto{
font-size: 12px;
}
.textoitem{
font-size: 10px;
}

.tabass{
	border:0px;
	font-size:11;
	padding:0px; 
	margin:0px;
	display:inline;
}
.tabass td{
	padding:0px;
	margin:0px;
	padding-right:8px;
}

.tabass .lb6{/* label inferior à  imagem da assinatura */
	padding:0px;
	margin:0px;
	font-size:6pt;
}

.tabass .lbresp{/* label inferior à  imagem da assinatura */
	padding:0px;
	margin:0px;
	font-size:9px;
}

html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 11px;
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
#_cabecalho{
	font-size:20px;
}
</style>
<title>Rep / Inc</title>
</head>
<body style="max-width:1100px;">
<table class="tbrepheader">
<tr>	      
	<td class="header" style="width:35%;">
<?
	$_timbradoheader = 'HEADERPRODUTO';
	if($geraarquivo=='Y'){
		$_sqltimbrado="select tipoimagem,caminho from empresaimagem where idempresa=".$_1_u_lote_idempresa." and tipoimagem in ('".$_timbradoheader."')";
		//$_sqltimbrado="select tipoimagem,caminho from empresaimagem where 1 and idempresa = 3 and tipoimagem in ('".$_timbradoheader."')";
		$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
		$_figtimbrado=mysql_fetch_assoc($_restimbrado);
		
		echo '<img src="'.$_figtimbrado["caminho"].'" height="25px" width="140px">';
	}else{
		$_sqltimbrado="select tipoimagem,caminho from empresaimagem where  idempresa=".$_1_u_lote_idempresa."  and tipoimagem in ('".$_timbradoheader."')";
		//$_sqltimbrado="select tipoimagem,caminho from empresaimagem where 1 and idempresa = 3 and tipoimagem in ('".$_timbradoheader."')";
		$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
		$_figtimbrado=mysql_fetch_assoc($_restimbrado);
		
		echo '<img src="'.$_figtimbrado["caminho"].'" width="75%">';
	}
?>

<?

	if(traduzid("prodserv","idprodserv","fabricado",$_1_u_lote_idprodserv)=='Y'){
		$strpartida='Partida';
		$loteinterno='N';
	}else{
		$strpartida='Lote do Fornecedor';
		$loteinterno='S';
	}
	
	
?>
</td>
<td class="header" id="_cabecalho" nowrap>Certificado de Análise</td>
</tr>
</table>
<HR>

<?if(!empty($_1_u_lote_infprod)){
?>
    
<table class="normal" style="width: 100%;"> 
<tr class="header">
	<td >Informações do Produto</td>
</tr>
<tr class="res">
	<td style="width:630px;" ><?=nl2br($_1_u_lote_infprod)?></td>
</tr>
</table>
<p>&nbsp;</p>
<?}?>
<table class='normal' style="width: 100%;">
<tr class="header">
	<td colspan="4"  style="width:630px;">Dados do lote</td>
</tr>
<tr class="res" >
	<td align="right">Nome:</td> 
	<td style="min-width:250px;" nowrap><?=traduzid("prodserv","idprodserv","descr",$_1_u_lote_idprodserv)?></td>
	<td align="right" nowrap><?=$strpartida?>:</td>
	<td style="min-width:150px;" nowrap><?=$_1_u_lote_partidaext?></td>
</tr>
<tr class="res">
    <?if(!empty($_1_u_lote_idprodservformula)){?>
	<td align="right" nowrap>Apresentação:</td>
	<td nowrap><?=traduzid("prodservformula","idprodservformula","rotulo",$_1_u_lote_idprodservformula)?></td>
    <?}else{?>
        <td align="right" nowrap>Quantidade:</td>
	<td nowrap><?=$_1_u_lote_qtdprod?>-<?=traduzid("prodserv","idprodserv","un",$_1_u_lote_idprodserv)?></td>
    <?}?>
	<td align="right" nowrap>Código Interno:</td>
	<td nowrap><?=traduzid("prodserv","idprodserv","codprodserv",$_1_u_lote_idprodserv)?></td>
	
</tr>
<tr class="res">
    <td align="right" nowrap>Qtd Produzida:</td>
	<td nowrap><?=tratanumero(recuperaExpoente($_1_u_lote_qtdprod,$_1_u_lote_qtdprod_exp))?>-<?=traduzid("prodserv","idprodserv","un",$_1_u_lote_idprodserv)?></td>
	<td align="right" nowrap>Unidade Medida:</td>
	<td nowrap><?=$unidade?></td>

</tr>
<?if(traduzid("prodserv","idprodserv","comprado",$_1_u_lote_idprodserv)=='Y'){?>
<tr class="res">	
	<td align="right" nowrap>Fornecedor:</td>
	<td nowrap><?=$_1_u_lote_fabricante?></td>	
	<td align="right" nowrap>Lote Interno:</td>
	<td nowrap><?if($loteinterno=='N'){?>-------<?}else{?><?=traduzid("lote","idlote","partida",$_1_u_lote_idlote)?>/<?=traduzid("lote","idlote","exercicio",$_1_u_lote_idlote)?><?}?></td>
</tr>
<?}?>
<tr class="res">	
	<td align="right" nowrap>Data de Fabricação:</td>
	<td nowrap><?=$_1_u_lote_fabricacao?></td>
	<td align="right" nowrap>Data de Validade:</td>
	<td nowrap ><?=$_1_u_lote_vencimento?></td>
</tr>
<?if(!empty($_1_u_lote_infanalise) and !empty($_1_u_lote_tituloanalise)){?>
	<tr class="res"> 
		<td align="right" nowrap><?=$_1_u_lote_tituloanalise?>:</td>
		<td colspan="3" ><?=nl2br($_1_u_lote_infanalise)?></td>
	</tr>
<?}?>	
</table>	
	
<p>&nbsp;</p>	
	
<p>&nbsp;</p>	
<?	
			$sqla="select p.idprodservloteservico,ps.descr,ps.codprodserv,ps.idprodserv,
						a.idregistro
						,a.exercicio
						,r.idamostra
						,r.idresultado
						,r.resultadocertanalise as resultado
						,r.conformidade as conclusao
						,r.status
					from lote l join prodservloteservico p on(p.idprodserv=l.idprodserv  and p.status!='INATIVO') 
					join prodservloteservicoins i on (i.idprodservloteservico=p.idprodservloteservico and i.status='ATIVO')
					join prodserv ps on(ps.idprodserv=i.idprodserv)
					left join  objetovinculo ov ON (ov.idobjetovinc = l.idlote AND ov.tipoobjetovinc = 'lote')
					left join amostra a ON (a.idamostra = ov.idobjeto AND ov.tipoobjeto = 'amostra'  )
					left join resultado r on (a.idamostra = r.idamostra and r.idtipoteste=i.idprodserv)  
					where l.idlote=".$_1_u_lote_idlote;
			echo"<!--$sqla-->";
		    $resa = d::b()->query($sqla)or die("Erro ao selecionar item 3 :".mysqli_error());
		    $qtdan = mysqli_num_rows($resa);
			if($qtdan>0){
?>
		
		<table class='normal' style="width: 100%;">
				<tr class="header" >
					<th  class="col-md-1">Registro</th>
					<th class="col-md-6">Teste</th>
					<th  class="col-md-2">Status</th>
					<th  class="col-md-2">Resultado</th>
					<th  class="col-md-1">Conclusão</th>
				</tr>

<?				$i=$y;
		    while ($rowb = mysqli_fetch_assoc($resa)){
				$i++;
				
				?>
				<tr class="res 1">  
					<td class=" nowrap" align="center">					
						<a onclick="janelamodal('?_modulo=resultprod&_acao=u&idresultado=<?=$rowb['idresultado']?>')"><?=$rowb['idregistro']?> / <?=$rowb['exercicio']?></a>
					</td>
					<td class="texto2"><?=$rowb['descr']?></td>
					<td><?=$rowb['status']?></td>
					<td class="texto2" align="center"><?=$rowb['resultado']?></td>
					<td class="texto2" align="center"><?=$rowb['conclusao']?></td>				
				</tr>
			<?
			
			}
		?>
			</table>
			<p>&nbsp;</p>
		<?
		}

	    $sql="SELECT q.qst,
					 al.conclusao as resultadocert,
					 al.conclusao,
					 al.resultado,
					 q.especificacao,
					 q.esperado,
					 q.ordem
				FROM analiselote al JOIN analiseqst q ON q.idanaliseqst = al.idanaliseqst
				JOIN lote l ON l.idlote = al.idlote AND l.idprodserv = q.idprodserv AND (l.idprodservforn = q.idprodservforn OR l.idprodservforn IS NULL)
			   WHERE q.status = 'ATIVO'
				 AND l.idlote = $_1_u_lote_idlote
		    ORDER BY q.ordem";
	
	$res = d::b()->query($sql) or die("A Consulta das questões falhou :".mysql_error()."<br>Sql:".$sql); 
	$existe = mysqli_num_rows($res);
	
	
	//LTM - 09-10-2020: 376642 - Adicionado conforme listado no Lote
	
	// GVT - 20/10/2020: Comentada a consulta pois já é realizada na linha 389
	/*
	$sqlam="SELECT
			lt.idprodserv
			,a.idregistro
			,a.exercicio
			,r.idamostra
			,r.idresultado
			,p.descr as qst
			,pa.especificacao
			,r.conformidade as resultado
			,r.status
			,pa.ordem
		    FROM
                       
			loteativ l,		    
			prodserv p,
			amostra a,
			resultado r  join
			     lote lt   join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste)
		    WHERE
			lt.idlote = l.idlote			
                        AND l.idlote = ".$_1_u_lote_idlote."                       
			AND a.idobjetosolipor = l.idloteativ 
			AND a.tipoobjetosolipor = 'loteativ'
			AND a.idamostra = r.idamostra
			AND p.idprodserv = r.idtipoteste order by pa.ordem";

	    $resam= d::b()->query($sqlam) or die("Erro ao buscar informações dos resultados sql=".$sqlam);
	    $qtdan=mysqli_num_rows($resam);
		*/

		if($existe>0 or $qtdb>0){
	
?>

<table class='normal' style="width: 100%;">
<tr class="header" >
<?echo "<!--$sql-->";?>
		<td>Análise</td>
		<td  align="center">Especificação</td>
		<td  align="center">Resultado</td>
		<td  align="center">Conclusão</td>
</tr>
<?			
		while($row = mysqli_fetch_assoc($res)){
			if($row["resultadocert"]!='NAO SE APLICA' and $row["resultadocert"]!='NAOSEAPLICA' and !empty($row["resultadocert"])){
?>	
<tr class="res 1">  	
	<td style="min-width:350px;" ><?=$row["qst"]?></td> 
	<td  align="center" style="min-width:130px;" ><?=$row['especificacao']?></td> 
	<td align="center" style="min-width:130px;" ><?=$row["resultado"]?></td>
	<?if ($row["conclusao"] == "NAOCONFORME") {
		?><td align="center" style="min-width:130px;" >NÃO CONFORME</td><?
	}else {
		?><td align="center" style="min-width:130px;" ><?=$row["conclusao"]?></td><?
	}?>
</tr>
<?
			}
		}
		//resultados do bioterio
		while($rowb = mysqli_fetch_assoc($resb)){
			if($rowb["conclusao"]!='NAO SE APLICA' and $rowb["conclusao"]!='NAOSEAPLICA' and !empty($rowb["resultado"])){
?>	
<tr class="res 2">  	
	<td style="min-width:350px;" ><?=$rowb["qst"]?> <?if($rowb['dia']){echo " D".$rowb['dia'];}else{echo " D0";}?></td> 
	<td align="center" style="min-width:130px;" ><?=$rowb['especificacao']?></td> 
	<td align="center" style="min-width:130px;" ><?=$rowb["resultado"]?></td>
	<td align="center" style="min-width:130px;" ><?=$rowb["conclusao"]?></td>
</tr>
<?
			}
		}
?>
<? //loop resultados da amostra
// GVT - 20/10/2020: Comentada a consulta pois já é realizada na linha 389
/*
				while($rowam=mysqli_fetch_assoc($resam)){
					?>		
					<tr class="res 3">  
						<td nowrap><?=$rowam['qst']?></td>
						<td	align="center" nowrap><?=$rowam['especificacao']?></td>
						<td align="center" nowrap><?=$rowam['resultado']?></td>
						<td nowrap><?=$rowam['status']?></td>
					</tr>
					<?
				}	*/		
				?>
</table>
<p>&nbsp;</p>
<?
	}
?>

<?if(!empty($_1_u_lote_obsanaliseqst)){?>
<HR>
<table  class='normal' style="width: 100%;">
	<tr class="header">
		<td align="center">Observação</td>
	</tr>
	
	<tr class="res"> 
		<td colspan="5" style="width:450px;" ><?=nl2br($_1_u_lote_obsanaliseqst)?></td>
	</tr>
	
</table>
<p>&nbsp;</p>
<?}?>
<?
/*
if(empty($_1_u_lote_obsanaliseqst)){?>		
	<br>	
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
<?}else{?>		
	<table style="font-size: 12px;"> 
	<tr>
		<td colspan="5" style="width:450px;" ><?=nl2br($_1_u_lote_obsanaliseqst)?></td>
	</tr>
	</table>
<?
}*/
?>	





<br>
<HR>
<table class='normal'>
<?
	$straceito="";
	$strrecusado="";
	if($_1_u_lote_analise=="ACEITO"){
		$straceito = "X";
	}elseif($_1_u_lote_analise=="RECUSADO"){
		$strrecusado = "X";
	}
	
	
	?>
<tr class="header">
	<td colspan="2">Resultado</td>
	
</tr>
<tr class="res">
	<td align="right">Aceito:</td>
	<td style="width:10px;"><?=$straceito?></td>
</tr>
<tr class="res">
	<td align="right">Recusado:</td>
	<td style="width:10px;"><?=$strrecusado?></td>
</tr>

</table>
<p>&nbsp;</p>



<table  style="font-size: 11px;">
<tr>
	<td align="right" >Técnico(s) Qualificado(s):</td>
	<?

		      
$sql="SELECT
			c.idpessoa,  c.alteradoem
			FROM
			carrimbo c join pessoa p on (c.idpessoa = p.idpessoa  and p.assinateste='Y')
			WHERE
			c.idobjeto 	= '".$_1_u_lote_idlote."' and
			c.tipoobjeto in ('lotealmoxarifado','lotemeios','lotecq','lotelogistica','lotediagnostico','lotediagnosticoautogenas','lotesproducaobacterias','lotesproducaofungos','loteproducao','loteretem','lote') and
			c.status in ('ATIVO','ASSINADO')
			order by alteradoem	
			";
		$res = d::b()->query($sql) or die("A Consulta das questões falhou :".mysqli_error()."<br>Sql:".$sql); 
		$retorno = mysqli_num_rows($res);
		while($row = mysqli_fetch_assoc($res)){
			echo '<td style="width:260px;">';
			 echo "<div align='right' style='margin:0px;padding:0px;border:none;'> \n";
		      ?>  
		       <table <?if(empty($geraarquivo)){?> class='tabass' <?}?>> 
		        
		        <?
				$data = new DateTIme($row['alteradoem']);
				$formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
				$alteradoem =  $formatter->format($data);
		        echo "<tr> \n";
				echo 	"<td align='center'><b>Data de Emissão do Certificado</b></td>";
		        echo "</tr> \n";
		        echo "<tr> \n";
				echo 	"<td align='center'>$alteradoem</td>";
		        echo "</tr> \n";
		        
				$idassinadopor = $row['idpessoa'];
				
			    $sqlass = "SELECT p.idpessoa, p.nome, pc.crmv
				FROM pessoa p join pessoacrmv pc on (pc.idpessoa = p.idpessoa)
				WHERE  p.assinateste = 'Y'
				and p.idpessoa = '" . $idassinadopor."'";
				
			    $resass = d::b()->query($sqlass) or die("Erro ao recuperar assinaturas: ".mysqli_error(d::b()));
			    $qtdrowss= mysqli_num_rows($resass);
			    if ($qtdrowss > 0) {
					$rowass = mysqli_fetch_array($resass);
					
					$nomresp=$rowass['nome'];
					$crmvresp=$rowass['crmv'];
					$respidpessoa=$rowass["idpessoa"];
							echo "<tr> \n";
								echo "<td nowrap='nowrap'>";
									echo "<img src='../inc/img/sig".strtolower(trim($respidpessoa)).".gif'> \n";
									echo "<br><label class='lbresp'>".$nomresp."</label> \n";
									echo "<br><label class='lb6'>Respons&aacute;vel T&eacute;cnico / CRMV: ".$crmvresp."<br>Assinatura Digital: ".md5(trim($respidpessoa))."</label> \n";
								echo "</td>";
							echo "</tr> \n";
						}
				echo "</table> \n";
						
		echo "</div> \n";
		echo '</td>';
		}?>
	
</tr>
</table>
	
<p>&nbsp;</p>
<hr>	
<p>&nbsp;</p>
<?

if(traduzid("prodserv",'idprodserv','imagemcert',$_1_u_lote_idprodserv,false) == "Y"){

	$sqlx="select la.idlote 
	from lote lpt join loteativ la on(la.idlote=lpt.idlote)
	where lpt.partida='".$_1_u_lote_partida."' and lpt.exercicio='".$_1_u_lote_exercicio."' limit 1";
	$resx= d::b()->query($sqlx) or die("erro ao busca se assina partida sql".$sqlx);
	$rowx=mysqli_fetch_assoc($resx);

	if(!empty($rowx['idlote'])){
		$idlotev=$rowx['idlote'];
	}else{
		$sqlx="Select a.idlote from lote lpt join  analiselote a  on(a.idlote=lpt.idlote)
				where  lpt.partida='".$_1_u_lote_partida."' and lpt.exercicio='".$_1_u_lote_exercicio."' limit 1";
		$resx= d::b()->query($sqlx) or die("erro ao busca se assina partida sql".$sqlx);
		$rowx=mysqli_fetch_assoc($resx);
		if(!empty($rowx['idlote'])){
			$idlotev=$rowx['idlote'];
		}else{
			$idlotev=$_1_u_lote_idlote; 
		}
	}

	$sqli = "SELECT 
				r.idresultado, ps.descr AS qst, ar.*
			FROM
				lote l
					JOIN
				prodservloteservico p ON (p.idprodserv = l.idprodserv
					AND p.status != 'INATIVO')
					JOIN
				prodservloteservicoins i ON (i.idprodservloteservico = p.idprodservloteservico
					AND i.status = 'ATIVO')
					JOIN
				prodserv ps ON (ps.idprodserv = i.idprodserv)
					JOIN
				objetovinculo ov ON (ov.idobjetovinc = l.idlote
					AND ov.tipoobjetovinc = 'lote')
					JOIN
				amostra a ON (a.idamostra = ov.idobjeto
					AND ov.tipoobjeto = 'amostra')
					JOIN
				resultado r ON (a.idamostra = r.idamostra
					AND r.idtipoteste = i.idprodserv)
					JOIN
				arquivo ar ON (ar.idobjeto = r.idresultado
					AND ar.tipoobjeto LIKE 'result%')
			WHERE
				l.idlote =$idlotev
				UNION 
			SELECT 
				r.idresultado, p.descr AS qst, ar.*
			FROM
				loteativ l
					JOIN
				objetovinculo ov ON ov.idobjetovinc = l.idloteativ
					AND ov.tipoobjetovinc = 'loteativ'
					JOIN
				resultado r ON r.idresultado = ov.idobjeto
					AND ov.tipoobjeto = 'resultado'
					JOIN
				amostra a ON a.idamostra = r.idamostra
					JOIN
				prodserv p ON p.idprodserv = r.idtipoteste
					JOIN
				lote lt ON lt.idlote = l.idlote
					JOIN
				analiseqst pa ON pa.idprodserv = lt.idprodserv
					AND pa.status = 'ATIVO'
					JOIN
				analiseteste te ON te.idanaliseqst = pa.idanaliseqst
					AND r.idtipoteste = te.idprodserv
					JOIN
				arquivo ar ON (ar.idobjeto = r.idresultado
					AND ar.tipoobjeto LIKE 'result%')
			WHERE
				l.idlote = $idlotev
					AND a.idamostra = r.idamostra";
		$resi= d::b()->query($sqli) or die("erro ao busca se assina partida sql".$sqli);
		?>
		<table  style="font-size: 11px;">
			<tr>
				<td align="left" colspan="2" >
					<div>
						<b>Imagens Anexo:</b>
						<br>
						<br>
					</div>
			</td>
			</tr>
		<?
		$trnovo = true;
		$c = 0;
		$idresultado = null;
		while($rowi = mysqli_fetch_assoc($resi)){
			
			if($rowi['idresultado'] != $idresultado){
				$idresultado = $rowi['idresultado'];
				$trnovo = true;
			}
			if($trnovo){
				$trnovo = false;
				if($c > 0){
					echo "</tr>";
				}
				echo "<tr>";
			}
			echo "<td align='center'>
					<div><img width='300px' height='500px' src='".$rowi['caminho']."'></div>		
					<div align='center'>".$rowi['qst']."</div>
					<br>
				</td>";
			$c ++;
		}
		if(!$trnovo && $c > 0){
			echo "</tr>";
		}
		?>
		</table>

		<?}?>

</body>
</html>
<?
if($geraarquivo=='Y'){

	$sql="select  REPLACE(concat(convert(lpad(replace(l.partida,p.codprodserv,''),'3', '0')using latin1),'-',l.exercicio), '/', '.') as npart,p.codprodserv
	from lote l,prodserv p where l.idprodserv = p.idprodserv and l.idlote=".$_1_u_lote_idlote;
	$res=d::b()->query($sql) or die("Erro ao buscar partida sql=".$sql);
	$row = mysqli_fetch_assoc($res);

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	//echo($html);die;

	//Inclusão da biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");

	// Instanciamos a classe
	$dompdf = new DOMPDF();

	// Passamos o conteúdo que será convertido para PDF
/*	$html=preg_match("//u", $html)?utf8_decode($html):$html;
	$dompdf->load_html($html);*/

	$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
	$dompdf->load_html($html);
	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	$dompdf->render();

	if($gravaarquivo=='Y'){
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
    	file_put_contents("/var/www/carbon8/upload/nfe/Certificado_".$row['codprodserv']."-part".$row['npart'].".pdf",$output);
    	echo("OK");
	}else{
		// e exibido para o usuário
		$dompdf->stream("Certificado".$_1_u_lote_idlote.".pdf");
	}
}

if($_GET["gerarautomatico"] == 'Y'){
	$sqlog = "INSERT INTO log (idempresa,sessao,tipoobjeto,idobjeto,tipolog,log,info,criadoem) 
				VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$grupo ."','lote',".$_GET["idlote"].",'CERTAUTOMATICO',
				'Log para verificar a chamada do gerador de PDF automático de certificado de análise','fim',now())";
	d::b()->query($sqlog);
}
?>