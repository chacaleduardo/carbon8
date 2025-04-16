<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


if(empty($_GET["idamostra"])){
	die("É necessario informar o ID da amostra para a impressão");
	
}

$sql = "select a.*,p.* 
		from amostra a,pessoa p 
		where a.idpessoa = p.idpessoa
		and a.idamostra= ".$_GET["idamostra"];


	
$res = d::b()->query($sql) or die("Erro ao buscar dados da amostra e do cliente:".mysqli_error());
$row=mysqli_fetch_assoc($res);

?>
<html>
<head>
<title>Impressão</title>



<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
<style>

.divborda {border: 1px solid silver; width:100%;}

table.bordasimples {border-collapse: collapse; min-width:100%;}
table.bordasimples tr td {border:1px solid black; }

.tdnegrito{font-weight:bold;}

table.item tr td {font-weight:bold;}

.divitem{display: inline-block;}
.negrito{font-weight:bold;};


</style>
</head>
<body style='width: 20cm;  margin: 0.5cm; margin-top:0.5cm;'>
	<?
			// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
			$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
			$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('amostra', 'idamostra', $_GET["idamostra"]);
			
			if($_timbrado != 'N'){
		
				$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERSERVICO'";
				$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
				$_figtimbrado=mysql_fetch_assoc($_restimbrado);

				$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
				$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
				$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

				$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
				$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
				$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
				
				$_timbradocabecalho = $_figtimbrado["caminho"];
				$_timbradomarcadagua = $_figtimbrado1["caminho"];
				$_timbradorodape = $_figtimbrado2["caminho"];
				
				if(!empty($_timbradocabecalho)){?>
					<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="80px" width="100%"></div>
				<?}
			}
			
		?>
<table>	
	<tr>
		<td></td>
		<td style="vertical-align: top; width: 100%;">		
			<div align="left" class="texto">N&#186; Registro: <font style="font-weight: bold;"><?=$row["idregistro"]?></font>&nbsp;&nbsp;&nbsp;&nbsp; Exercicio: <font style="font-weight: bold;"><?=$row["exercicio"]?></font></div>
		</td>
	</tr>
</table>

<div class='divborda'>
<div align='center' style='font-size: 14px; font-weight: bold;background-color: #E8E8E8;'>DADOS DO CLIENTE</div>
		<table style='font-size: 14px; '>
	
			<tr>
				<td class="texto"><b>R. Social:</b> <?=$row['razaosocial']?></td>
			</tr>
			<tr>
				<td class="texto"><b>Unidade:</b> <?=$row['nome']?></td>
			</tr>
			<tr>
				<td class="texto"><b>CNPJ:</b> <?=formatarCPF_CNPJ($row['cpfcnpj'],true); ?></td>
			</tr>	
			<tr>
				<td class="texto">
				<?if(!empty($rowe['dddfixo']) and !empty($rowe['telfixo'])){?>
				<b>Tel:</b> <?=$row['dddfixo']?>-<?=$row['telfixo']?>
				<?}?>
				</td>
			</tr>
			<?
			 $sqlend="select c.cidade,c.uf,e.logradouro,e.endereco,e.complemento,e.numero,e.bairro,e.cep
				from endereco e,nfscidadesiaf c
				 where e.idtipoendereco = 2 
				and c.codcidade = e.codcidade
				and e.idpessoa=".$row['idpessoa'];
			 $rese=d::b()->query($sqlend) or die("Erro ao buscar endereco sql=".$sqlend);
			 $rowe=mysqli_fetch_assoc($rese);
			?>
			<tr>
				<td class="texto">
				<?if(!empty($rowe['logradouro'])){?>
				<b><?=$rowe['logradouro']?></b>:&nbsp;
				<?}?>
				<?=$rowe['endereco']?> &nbsp;
				<?=$rowe['numero']?>&nbsp;<?=$rowe['complemento']?>
				<?if(!empty($rowe['bairro'])){?>
				,&nbsp;<b>Bairro:</b> <?=$rowe['bairro']?>
				<?}?>
				
			</td>
			</tr>
			<tr>
				<td class="texto">	<?if(!empty($rowe['cep'])){?>
										<b>CEP:</b> <?= formatarCEP($rowe['cep'],true); ?>&nbsp;
									<?}?> 
									<?if(!empty($rowe['cidade'])){?>
										<b>CIDADE:</b> <?=$rowe['cidade']?>
									<?}?> 
									<?if(!empty($rowe['uf'])){?>
										- <?=$rowe['uf']?>
									<?}?> 
									
									</td>
			</tr>	
	</table>	
</div>
<br>
<div class='divborda'>
<div align='center' style='font-size: 14px; font-weight: bold;background-color: #E8E8E8;'>DADOS DA AMOSTRA</div>

		<table style='font-size: 14px;'>
		<tr>	
		<?if(!empty($row["datacoleta"])){?>
			<td align="right" class="rotulo"><b>Data Coleta:</b></td> 
			<td class="texto" nowrap><?=dma($row["datacoleta"])?></td>
			<?}?>
		<?if(!empty($row["localcoleta"])){?>	 
			<td align="right" class="rotulo"><b>Local Coleta:</b></td> 
			<td class="texto" nowrap><?=$row["localcoleta"]?></td> 
		<?}?>	
		</tr>
		<tr>
		<?
		if(!empty($row["responsavel"])){
		?>		
			<td align="right" class="rotulo"><b>Responsável:</b></td>
			<td class="texto" nowrap><?=$row["responsavel"]?></td>
		<?
		}
		?>			
		</tr>
		<tr>
			<td colspan="50" style="max-width:550px;" class="textoitem" ><?=nl2br($rowf['observacao'])?></td>
		</tr>
		</table>
		
</div>
<? 
	$sqlr="select p.descr,codprodserv,r.quantidade from resultado r,prodserv p
			where r.idamostra=".$row['idamostra']."
			and p.idprodserv = r.idtipoteste";
	$resr=d::b()->query($sqlr) or die("Erro ao buscar testes sql=".$sqlr);
	$qtdrows= mysqli_num_rows($resr);
	if($qtdrows>0){
	
?>
<br>
<div class='divborda' >
		<div  style=" background-color:#E8E8E8">
			<div class='divitem' style='width:2cm;'><font class='negrito'>Qtd.</font></div>
			<div class='divitem' style='width:5cm;'><font class='negrito'>Código</font></div>
			<div class='divitem' style='width:12cm;'><font class='negrito'>Descrição</font></div>
			
		</div>

<?	
		$i=1;
		
		$troca="S";
		while ($rowr = mysqli_fetch_assoc($resr)){
		 $i = $i+1;
		//mudar a cor da linha
		 if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>

		 <div style=" background-color:<?=$cortr?>">
			<div class='divitem' style='width:2cm;'><?=$rowr["quantidade"]?></div>
			<div class='divitem' style='width:5cm;'><?=$rowr["codprodserv"]?></div>	
			<div class='divitem' style='width:12cm;'><?=$rowr["descr"]?></div>	
		 	
		</div>	

		 
		 
<? 	
		}
		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>

		

	</div>
	<br>
<?		
	}
?>		
	<div class='divborda'>
	<div align='center' style='font-size: 14px; font-weight: bold;background-color: #E8E8E8;'>OBSERVAÇÕES</div>		
		<table style='font-size: 14px;'>			
			<tr>
				<td colspan="50" style="max-width:700px" class="textoitem" >
<p><span>- </span><strong>Resultado</strong><span> em nome do cliente; </span><strong>Cobran&ccedil;a</strong><span> em nome do Laudo Laborat&oacute;rio.</span><br /><span>- Para cada registro, </span><strong>um resultado separado</strong><span>, mesmo que seja a mesma unidade.</span><br /><span>- D&uacute;vidas: Ivan ou Mateus: (34) 3222-5700 ou material@laudolab.com.br.</span></p>
				</td>
			</tr>
		</table>	
	</div>	
	<br>
	</div>
	<div class='divborda'>
		<div align='center' style='font-size: 14px; font-weight: bold;background-color: #E8E8E8;'>PARÂMETROS</div>		
			<table style='font-size: 14px;'>			
				<tr>
					<td colspan="50" style="max-width:700px" class="textoitem" >
<strong>AMOSTRAS DE &Aacute;GUA</strong>
<br><br><strong>PADR&Atilde;O PAR&Acirc;METROS F&Iacute;SICO-QU&Iacute;MICO E MICROBIOL&Oacute;GICO</strong>
	<br/>
<br>Segue abaixo a rela&ccedil;&atilde;o dos par&acirc;metros a serem realizados nas amostras de &aacute;gua, sendo a refer&ecirc;ncia o Programa de Avalia&ccedil;&atilde;o de Conformidade de Padr&otilde;es F&iacute;sico-Qu&iacute;micos e Microbiol&oacute;gicos de Produtos de Origem&nbsp;Animal Comest&iacute;veis e &Aacute;gua de Abastecimento, estipulado pelo Minist&eacute;rio da Agricultura, Pecu&aacute;ria e Abastecimento (MAPA), atrav&eacute;s da NORMA INTERNA SDA N&ordm; 4/2013*:
<br/><br/>
<table width="501">
<tbody>
<tr>
<td colspan="2" width="501">
<p><strong>F&Iacute;SICO-QU&Iacute;MICO** </strong>- 16 Par&acirc;metros</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Alum&iacute;nio&nbsp;</p>
</td>
<td width="251">
<p>- Nitrato</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Am&ocirc;nia (como NH<sub>3</sub>)&nbsp;</p>
</td>
<td width="251">
<p>- Nitrito</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Aspecto</p>
</td>
<td width="251">
<p>- Nitrog&ecirc;nio amoniacal</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Cloro residual livre</p>
</td>
<td width="251">
<p>- Odor</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Cor aparente</p>
</td>
<td width="251">
<p>- pH</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Dureza total</p>
</td>
<td width="251">
<p>- Sabor</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Ferro</p>
</td>
<td width="251">
<p>- S&oacute;lidos dissolvidos totais</p>
</td>
</tr>
<tr>
<td width="251">
<p>- Mat&eacute;ria org&acirc;nica</p>
</td>
<td width="251">
<p>- Turbidez</p>
</td>
</tr>
<tr>
<td width="251">&nbsp;</td>
<td width="251">&nbsp;</td>
</tr>
<tr>
<td colspan="2" width="501">
<p><strong>MICROBIOL&Oacute;GICO** </strong>- 3 Par&acirc;metros</p>
</td>
</tr>
<tr>
<td colspan="2" width="501">
<p>- Coliformes termotolerantes ou <em>Escherichia coli </em></p>
</td>
</tr>
<tr>
<td colspan="2" width="501">
<p>- Coliformes totais</p>
</td>
</tr>
<tr>
<td colspan="2" width="501">
<p>- Contagem padr&atilde;o de mes&oacute;filos aer&oacute;bios</p>
</td>
</tr>
</tbody>
</table>
					</td>
				</tr>
			</table>	
		</div>
	</div>
	<?
		if(!empty($_timbradorodape)){?>
		<br>
			<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="80px" width="100%"></div>
		<?}?>
</body>
</html>
