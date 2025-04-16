<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}



$idsgdoc = $_GET["idsgdoc"];

$_SESSION["arrpostbuffer"]=array();

if(empty($idsgdoc)){
	die("IDSGDOC não enviado!");
}


$sqlpag = "select
			    d.idsgdoc
			    ,d.titulo
			    ,d.versao
			    ,d.revisao
			    ,d.idequipamento
				,d.idsgtipodoc
			    ,ps1.nome as elaborador
			    ,ps2.nome as aprovador
			    ,td.tipodocumento
			    ,tds.subtipodoc
			    ,s.des as statusdocumento
			    ,p.idsgdocpag
			    ,p.pagina
			    ,p.conteudo
			from 
			    sgdoc d 
					inner join sgtipodoc td on d.idsgtipodoc = td.idsgtipodoc
          left join sgtipodocsub tds on d.idsgtipodocsub = tds.idsgtipodocsub
          inner join sgdocstatus s on d.status = s.status
          left join sgdocpag p on d.idsgdoc = p.idsgdoc
					inner join pessoa ps1 on ps1.usuario = d.criadopor
					inner join pessoa ps2 on ps2.usuario = d.alteradopor
			where
				d.idsgdoc = ".$idsgdoc." order by p.pagina ASC";

$respag = d::b()->query($sqlpag) or die("Erro ao recuperar Páginas do Documento: ".mysqli_error()."\n<br>SQL: ".$sqlpag);

$numpaginas = mysqli_num_rows($respag);

?>
<link
	href="../functions/wysiwyg/ckeditor/contents.css"
	rel="stylesheet" type="text/css">
<style data-cke-temp="1" type="text/css" media="all">
html {
	height: 100% !important;
}

img:-moz-broken {
	-moz-force-broken-image-icon: 1;
	width: 24px;
	height: 24px;
}
p.pagebreak{
	height: 0px;
	width: 0px;
	margin: 0px;
	padding: 0px;
	border: none;
	page-break-before:always;
}
.tablecabecalho{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
}
.tablecabecalho td{
	border-collapse: collapse;
	border: 1px solid silver;
	margin: 0px;
	padding-left: 6px;
	font-size:12px;
}
.bold{
	font-weight: bold;
}
.copiancontrolada{
	border: none;
	position:fixed;
	top: 50%;
	left:80px;
	z-index:-100;
}
.tablecabecalho .label{
	font-size: 10px;
	font-weight: bold;
	text-align: right;
}
.tablerodape{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
    position: fixed;
    width: auto;
    bottom: 0px;
}
.tablerodape td{
	border-collapse: collapse;
	border: 1px solid silver;
	margin: 0px;
	padding-left: 6px;
	padding-right: 6px;
	font-size:12px;
}
.tablerodape .label{
	font-size: 10px;
	font-weight: bold;
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


a.btbr10{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr10:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:10px;
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
a.btbr10:hover
{
    font-weight: bold;
    font-size:10px;
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
a.btbr10:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
</style>

<style data-cke-temp="1" type="text/css" media="screen">
body{
	padding-bottom: 30px; /* *Este valor deve ser maior que a altura do rodapé. Margem de seguranca para mostrar o final do texto antes de imprimir. Sem isso o texto ficará oculto atrás do rodapé. */
}
.escondetab{
	display: none;
}
p.pagebreak{
	border-bottom: 1px solid silver;
	width: 100%;
	
}
a.btbr20{
	display: block;
}
a.btbr10{
	display: block;
}
.tablerodape{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
    position: fixed;
    width: auto;
    bottom: 0px;
    background-color: white;
}
.copiancontrolada{
	display: none;
}
</style>

<?if(traduzid("sgdoc","idsgdoc","idsgtipodoc",$idsgdoc)!=51){?>
<?
	if(traduzid("sgdoc","idsgdoc","idequipamento",$idsgdoc)==9999){?>
	<img class="copiancontrolada" border="0" src="../inc/img/copiancontrolada.gif">
	<?}else{?>
	<img class="copiancontrolada" border="0" src="../inc/img/copiacontrolada.gif">
	<?}
}
?>
<a class="btbr20" href="" onclick="janelamodal('?_modulo=sgdoc&_acao=u&idsgdoc=<?=$idsgdoc?>')" >Editar Documento</a><br>


<?
$pbreak = false;
$escondetab = "";
while($rpag=mysqli_fetch_assoc($respag)){
	if($pbreak===true){
?>
	<p class="pagebreak"></p>
<?
	}
	
	$tipodocumento = (empty($rpag["subtipodoc"]))? $rpag["tipodocumento"] : $rpag["tipodocumento"] . " / " .$rpag["subtipodoc"];
	
?>
	<TABLE class="tablecabecalho <?=$escondetab?>" spacing="0">
		<TR>
		<?// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	$figurarelatorio = $figrel["logosis"];
	?>
			<TD ROWSPAN=7 align="center" style="width:74px;padding:4px;"><img src="<?=$figurarelatorio?>" border="0"></TD>
			<TD ROWSPAN=2 class="bold"><?=$tipodocumento?></TD>
			
			<TD class="label">Cód.</TD>
			<TD colspan="2"><?=$rpag["idsgdoc"]?><?if($rpag['idsgtipodoc']==55){?> <a class="btbr10" href="../ajax/copiardocumento.php?idsgdoc=<?=$idsgdoc?>" target="_blank">Copiar Documento</a><?}?></TD>
			
		</TR>
		<TR>
			<TD class="label">Rev.</TD>
			<TD colspan="2"><?=$rpag["versao"].".".$rpag["revisao"]?></TD>
		</TR>
		
		<TR>
			<TD rowspan="3" class="bold"><?=$rpag["titulo"]?></TD>
			<TD class="label">Local</TD>
			<TD colspan="2">
<?
			if(!empty($rpag["idequipamento"])){
				if($rpag["idequipamento"]==9999){
					echo('Sislaudo');
				}else{
						echo(traduzid("equipamento","idequipamento","equipamento",$rpag["idequipamento"]));
				}		
			}
?>
			</TD>
		</TR>
		<TR>
			<TD class="label">Status</TD>
			<TD colspan="2"><?=$rpag["statusdocumento"]?></TD>
		</TR>
		<TR>
			<TD class="label">Pág.</TD>
			<TD colspan="2"><?=$rpag["pagina"]?> de <?=$numpaginas?></TD>
		</TR>
					
<style>
.listaitens{
	border: none;
	margin: 5px;
	padding: 0px;
}
.listaitens{
	font-size: 11px;
	list-style: none outside none;
}
.listaitens .cab{/* cabecalho para liste de itens*/
	color: gray;
	font-size:9px;
	list-style: none outside none;
}

</style>
<tr>
<?
	$sqlv="SELECT s.titulo,s.idsgdoc,v.idsgdocvinc
		FROM `sgdocvinc` v,sgdoc s  
		where s.idsgdoc=v.iddocvinc 
			and v.idsgdoc = ".$idsgdoc." order by titulo";
	$resv = d::b()->query($sqlv) or die("A Consulta dos documentos vinculados falhou :".mysqli_error()."<br>Sql:".$sqlv);
	$qtdrows1= mysqli_num_rows($resv);

	if($qtdrows1>0  and $rpag["pagina"]==1){
?>
<td colspan="4">
				<ul class="listaitens">
					<li class="cab">Documentos vinculados:</li>
<?		while($rdvinc = mysqli_fetch_array($resv)){?>

					<li><a target="_blank" href="sgdocprint.php?acao=u&idsgdoc=<?=$rdvinc["idsgdoc"]?>"><?=$rdvinc["idsgdoc"]?> - <?=$rdvinc["titulo"]?></a></li>

<?		}
	}
?>
	</tr>
	<tr>
<?
		$sqlarq = "select a.*, dmahms(criadoem) as datacriacao 
					from arquivo a 
					where 
						a.tipoobjeto = 'sgdoc' 
						and a.idobjeto = ".$idsgdoc." 
						and tipoarquivo = 'ANEXO' 
					order by idarquivo asc";
	
		//echo $sqlarq."<br>";
		$res = d::b()->query($sqlarq) or die("Erro ao pesquisar arquivos:".mysqli_error());
		$numarq= mysqli_num_rows($res);

	if($numarq>0  and $rpag["pagina"]==1){
?>

	<td colspan="4">
		<ul class="listaitens">
			<li class="cab">Arquivos Anexos (<?=$numarq?>)</li>
<?		while ($row = mysqli_fetch_array($res)) {?>
			<li><a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
<?		}
?>
		</ul>
	</td>

<?
	}
?>	

	</tr>
	
		
	</TABLE>


<!-- INICIO IMPRESSAO CONTEUDO PAGINA <?=$rpag["pagina"]?> -->
	<?=$rpag["conteudo"]?>
<!-- TERMINO IMPRESSAO CONTEUDO PAGINA <?=$rpag["pagina"]?> -->
		<p>	
	<TABLE class="tablerodape <?=$escondetab?>" spacing="0">
		<TR>
			<TD nowrap><label class="label">Elaborador:</label><br><?=$rpag["elaborador"]?></TD>
			<TD style="width:100%;"></TD>
			<TD nowrap><label class="label">Aprovador:</label><br><?=$rpag["aprovador"]?></TD>
		</TR>
	</TABLE>
<?
	$pbreak=true;
	$escondetab = "escondetab";
}
?>
<?
		$sqlalt="select dma(a.alteradoem) as dmadata,a.* from sgdocupd a
				where a.idsgdoc = ".$idsgdoc." order by a.idsgdocupd desc";
			$resalt = d::b()->query($sqlalt) or die("A Consulta do relatório de versões falhou :".mysqli_error()."<br>Sql:".$sqlalt);
			$qtdrowa2= mysqli_num_rows($resalt);		
			
				if($qtdrowa2>0){							
	?>	
				<TABLE class="tablecabecalho">
				<TR>
					<TD class="bold" align="center"  colspan="3">Histórico do Documento</TD>
				</TR>
				<TR>	
					<TD class="bold" align="center">Versão</TD>
					<TD class="bold" align="center">Data</TD>
					<TD class="bold">Descrição</TD>
				</TR>				
	<?			
				while($rowalt = mysqli_fetch_array($resalt)){	
	?>
				<TR>
					<td align="center"><?=$rowalt["versao"]?>.<?=$rowalt["revisao"]?></td>
					<td align="center"><?=$rowalt["dmadata"]?></td> 
					<td style="width:100%"><?=nl2br($rowalt["acompversao"])?></td> 					
				</tr>
	<?
				}
	?>
			</table>
	<?
			}
	?>		
