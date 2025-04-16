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
$pagsql = "select * from lote where idlote = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];



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
</style>
<title>Rep / Inc</title>
</head>
<body style="max-width:1100px;">
<?
	$_timbradoheader = 'HEADERPRODUTO';
	require_once("timbrado.php");
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
    
<table class="tbrepheader">
<tr>	
    <td class="header" colspan="2" nowrap>Certificado de Análise</td>      
</tr>
</table>
<HR>

<?
$infprod=traduzid("prodserv","idprodserv","infprod",$_1_u_lote_idprodserv);
if(!empty($infprod)){
?>
    
<table class="normal"> 
<tr class="header">
	<td >Informações do Produto</td>
</tr>
<tr class="res">
	<td style="width:630px;" ><?=nl2br($infprod)?></td>
</tr>
</table>
<p>&nbsp;</p>
<?}?>
<table class='normal'>
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

$sql="SELECT
			c.idpessoa,  DATE_FORMAT(c.alteradoem,'%d/%m/%Y') as alteradoem
		FROM
			carrimbo c join pessoa p on (c.idpessoa = p.idpessoa and p.assinateste='Y' and p.status ='ATIVO')
		WHERE
			c.idobjeto 	= '".$_1_u_lote_idlote."' and
			c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem') and
			c.status = 'ATIVO'
			limit 1
			";
	$res = d::b()->query($sql) or die("A Consulta das questões falhou :".mysqli_error()."<br>Sql:".$sql); 
	$retorno = mysqli_num_rows($res);
	while($row = mysqli_fetch_assoc($res)){
		$idassinadopor = $row['idpessoa'];
		$alteradoem = $row['alteradoem'];
	}

	if(empty($idassinadopor)){
		$idassinadopor=$_1_u_lote_idassinadopor;
		$alteradoem=$_1_u_lote_dataanalise;
	}
        
        
	$sql = "SELECT p.descr AS qst,
    			   pa.especificacao,
				   r.conformidade AS resultado,
				   r.resultadocertanalise AS resultadocert,
				   pa.ordem
			  FROM loteativ l JOIN objetovinculo ov ON ov.idobjetovinc = l.idloteativ AND ov.tipoobjetovinc = 'loteativ'
			  JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
			  JOIN amostra a ON a.idamostra = r.idamostra
			  JOIN prodserv p ON p.idprodserv = r.idtipoteste
			  JOIN lote lt ON lt.idlote = l.idlote
			  JOIN analiseqst pa ON pa.idprodserv = lt.idprodserv AND r.idtipoteste = pa.idtipoteste
			 WHERE l.idlote = '$_1_u_lote_idlote'
		  ORDER BY pa.ordem";
	$res = d::b()->query($sql) or die("A Consulta das questões falhou :".mysqli_error()."<br>Sql:".$sql); 
	$existe = mysqli_num_rows($res);
	
		    $sqlb="select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(n.nucleo,'-',p.descr) as qst,r.conformidade as resultado,r.resultadocertanalise as resultadocert,r.status,p.idprodserv,pa.especificacao,pa.ordem
			    from loteativ la,bioensaio b,analise a,servicoensaio s,nucleo n,resultado r join prodserv p join lote lt
			    join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste)
		    where b.idloteativ=la.idloteativ
			    and s.idobjeto = a.idanalise
                             and b.idnucleo = n.idnucleo
			    and s.tipoobjeto = 'analise'
                            and a.objeto='bioensaio'
                            and a.idobjeto = b.idbioensaio
			    and r.idservicoensaio = s.idservicoensaio
			    and r.idtipoteste= p.idprodserv
			    and la.idlote=lt.idlote 
			    and lt.idlote = ".$_1_u_lote_idlote."
		    union 
		    select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(n.nucleo,'-',p.descr) as qst,r.conformidade as resultado,r.resultadocertanalise as resultadocert,r.status,p.idprodserv,pa.especificacao,pa.ordem
			    from loteativ la,bioensaiodes d,bioensaio b2,bioensaio b,servicoensaio s,nucleo n,resultado r join prodserv p join lote lt
			    join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste)
		    where d.idbioensaio=b2.idbioensaio
			    and b2.idloteativ=la.idloteativ
                             and b.idnucleo = n.idnucleo
			    and b.idbioensaio= d.idbioensaioc
			    and s.idobjeto = b.idbioensaio
			    and s.tipoobjeto = 'bioensaio'
			    and r.idservicoensaio = s.idservicoensaio
			    and r.idtipoteste= p.idprodserv
			    and la.idlote=lt.idlote 
			    and lt.idlote =".$_1_u_lote_idlote."
                    union 
		    select r.idresultado,s.dia,b.idregistro,b.exercicio,concat(n.nucleo,'-',p.descr) as qst,r.conformidade as resultado,r.resultadocertanalise as resultadocert,r.status,p.idprodserv,pa.especificacao,pa.ordem
			    from loteativ la,bioensaiodes d,
                            bioensaio b2,
                            nucleo n,
                            analise a,
                            bioensaio b,
                            servicoensaio s,
                            resultado r
                            join prodserv p join lote lt
			    join analiseqst pa on( pa.idprodserv =lt.idprodserv and r.idtipoteste=pa.idtipoteste)
		    where d.idbioensaio=b2.idbioensaio
			    and b2.idloteativ=la.idloteativ
                            and b.idnucleo = n.idnucleo
			    and b.idbioensaio= d.idbioensaioc
                            and a.objeto ='bioensaio'
                            and a.idobjeto = b.idbioensaio
			    and s.idobjeto = a.idanalise
			    and s.tipoobjeto = 'analise'
			    and r.idservicoensaio = s.idservicoensaio
			    and r.idtipoteste= p.idprodserv
			    and la.idlote=lt.idlote 
			    and lt.idlote =".$_1_u_lote_idlote." order by dia";
	    $resb= d::b()->query($sqlb) or die("Erro ao buscar informações dos bioensaio sql=".$sqlb);
	    $qtdb=mysqli_num_rows($resb);
	
	if($existe<1){
	    $sql="Select q.qst,l.resultado as resultadocert,l.resultado,q.especificacao,q.esperado,q.ordem
			from analiselote l,analiseqst q
			where q.idanaliseqst=l.idanaliseqst
			and l.idlote =".$_1_u_lote_idlote." order by q.ordem";
	
	$res = d::b()->query($sql) or die("A Consulta das questões falhou :".mysqli_error()."<br>Sql:".$sql); 
	$existe = mysqli_num_rows($res);
	}
	if($existe>0 or $qtdb>0){
	
?>

<table class='normal'>
<tr class="header"> 
		<td>Testes</td>
		<td>Especificações</td>
		<td  align="center">Resultado</td>
                <td  align="center">Conclusão</td>
</tr>
<?			
		while($row = mysqli_fetch_assoc($res)){
			if($row["resultadocert"]!='NAO SE APLICA' and $row["resultadocert"]!='NAOSEAPLICA' and !empty($row["resultadocert"])){
?>	
<tr class="res">  	
	<td style="min-width:350px;" ><?=$row["qst"]?></td> 
	<td style="min-width:100px;" ><?=$row["especificacao"]?></td> 	
	<td align="center" style="min-width:130px;" ><?=$row["resultadocert"]?></td>
        <td align="center" style="min-width:130px;" ><?=$row["resultado"]?></td>
</tr>
<?
			}
		}
		//resultados do bioterio
		while($rowb = mysqli_fetch_assoc($resb)){
			if($rowb["resultado"]!='NAO SE APLICA' and $rowb["resultado"]!='NAOSEAPLICA' and !empty($rowb["resultado"])){
?>	
<tr class="res">  	
	<td style="min-width:350px;" ><?=$rowb["qst"]?> <?if($rowb['dia']){echo " D".$rowb['dia'];}else{echo " D0";}?></td> 
	<td style="min-width:100px;" ><?=$rowb["especificacao"]?></td> 	
	<td align="center" style="min-width:130px;" ><?=$rowb["resultadocert"]?></td>
        <td align="center" style="min-width:130px;" ><?=$rowb["resultado"]?></td>
</tr>
<?
			}
		}
?>
</table>
<p>&nbsp;</p>
<?
	}
?>

<?if(!empty($_1_u_lote_obsanaliseqst)){?>
<HR>
<table  class='normal'>
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
	<td align="right" >Técnico Qualificado:</td>
	<td style="width:260px;">
	<?

		        echo "<div align='right' style='margin:0px;padding:0px;border:none;'> \n";
		      ?>  
		       <table <?if(empty($geraarquivo)){?> class='tabass' <?}?>> 
		        
		        <?

		        echo "<tr> \n";
		        if(!empty($idassinadopor)){
			    $sqlass = "SELECT idpessoa
				    FROM pessoa
				    WHERE  assinateste = 'Y'
				    and idpessoa = '" . $idassinadopor."'";

			    $resass = d::b()->query($sqlass) or die("Erro ao recuperar assinaturas: ".mysqli_error(d::b()));
			    $qtdrowss= mysqli_num_rows($resass);
			    
			    while($rowass = mysqli_fetch_array($resass)){
		
						$nomresp="";
						$crmvresp="";
						 $respidpessoa=$rowass["idpessoa"];
						//troca dados do responsavel via hardcode
						switch ($rowass["idpessoa"]) {
							case 782://edison
								$nomresp = "EDISON ROSSI";
								$crmvresp = "CRMV - MG N&ordm; 1626";
							;
							break;
							case 1484://edison
								$nomresp = "EDISON ROSSI";
								$crmvresp = "CRMV - MG N&ordm; 1626";
							;
							break;
							case 797://marcio
								$nomresp = "MARCIO BOTREL";
								$crmvresp = "CRMV - MG N&ordm; 1454";
							;
							break;
							case 1483://marcio
								$nomresp = "MARCIO BOTREL";
								$crmvresp = "CRMV - MG N&ordm; 1454";
							;
							break;
							default:
							null;
							break;
						}
			    
			    }
			
			}else{
				//ELSE COMENTADO 14/05/2019 POR MCC. QUAL A RAZàO DE FIXAR A ASSINATURA?
			   /* $respidpessoa=797;
			    $nomresp = "MARCIO BOTREL";
			    $crmvresp = "MG - 1454"; */
			}
						
						//troca dados do responsavel via hardcode
						
						if($nomresp){
				            echo "<td nowrap='nowrap'>";
				            echo "<img src='../inc/img/sig".strtolower(trim($respidpessoa)).".gif'> \n";
				            echo "<br><label class='lbresp'>".$nomresp."</label> \n";
				            echo "<br><label class='lb6'>Respons&aacute;vel T&eacute;cnico / ".$crmvresp."<br>Assinatura Digital: ".md5(trim($respidpessoa))."</label> \n";
				            echo "</td>";
						}
		   
		        echo "</tr> \n";
		        echo "</table> \n";
		        echo "</div> \n";
		
	
	?>
	
	</td>
	<td align="right">Data:</td>
	<td style="width:10px;"><?=$alteradoem;?></td>
</tr>
</table>
	</td>
</tr>

</table>
<p>&nbsp;</p>
<hr>	
<p>&nbsp;</p>

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
	$html=preg_match("//u", $html)?utf8_decode($html):$html;
	$dompdf->load_html($html);
	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	$dompdf->render();

	if($gravaarquivo=='Y'){
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
    	file_put_contents("/var/www/laudo/tmp/nfe/Certificado_".$row['codprodserv']."-part(".$row['npart'].").pdf",$output);
    	echo("OK");
	}else{
		// e exibido para o usuário
		$dompdf->stream("Certificado".$_1_u_lote_idlote.".pdf");
	}
 
 


}

?>

