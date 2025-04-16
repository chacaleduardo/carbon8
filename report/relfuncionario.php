<?
include_once("../inc/php/validaacesso.php");

?>
<html>
<head>
<title>Funcion&aacute;rio</title>
</head>


<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../inc/js/functions.js"></script>


<body>
<?
if (!empty($_REQUEST['idpessoa'])) {
$id = $_REQUEST['idpessoa'];
}else{
$id = $_SESSION["SESSAO"]["IDPESSOA"];
}

		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('pessoa', 'idpessoa', $id);
		
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
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}
		}
?>
<?
$figurarelatorio = "../inc/img/repheader.png";


$sql=" select * from pessoa where idpessoa = '".$id."';
		";
//echo "<!-- ".$sql." -->";

$res = d::b()->query($sql) or die("Falha ao pesquisar Nota Fiscal : " . mysqli_error(d::b()) . "<p>SQL: $sql");
$row = mysqli_fetch_array($res);
$idpessoa = $row['idpessoa'];
?>
<table>
<tr>

	<td >
	<table class="tbrepheader">
	<tr>
      <td class="header" pre-line>Id:</td>
      <td class="header" pre-line><?=$row['idpessoa'];?></td>
    </tr>
    <tr>
      <td class="header" pre-line>Usuário:</td>
      <td class="header" pre-line><?=$row['usuario'];?></td>
    </tr>
    <tr>
      <td class="header" pre-line>Nome:</td>
      <td class="header " pre-line><?=strtoupper($row['nome']);?></td>
    </tr>
	</table>
	</td>
</tr>
</table>	

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Informações Principais</legend>
</fieldset>	

<table class='normal'>
<tr class="header">
	<td>CPF/CNPJ</td> 
	<td>Email</td> 
	<td>Escolaridade</td> 
	<td>Cargo</td> 
	<!-- <td>LP</td> -->
</tr>
<tr class="res">
	<td><?=$row['cpfcnpj']?></td> 
	<td><?=$row['email']?></td> 
	<td><?=$row['escolaridade']?></td> 
	<td><?=traduzid("sgcargo","idsgcargo","cargo",$row['idsgcargo'])?></td> 
	<!-- <td><?=$row['idlp']?></td> -->
</tr>
<tr class="header">	
	<td>Data Nascimento</td>  
	<td>Data de Contratação</td> 
	<td>Telefone Celular</td>
	<td>Telefone Fixo</td>
	<td>Telefone Comercial</td>
</tr>

<tr class="res">	 
	<td><?=date("d/m/Y", strtotime($row['nasc']))?></td> 
	<td><?=date("d/m/Y", strtotime($row['contratacao']))?></td> 
	<td><?=$row['telfixo']?></td> 
	<td><?=$row['dddcel']?>-<?=$row['telcel']?></td> 
	<td><?=$row['dddcom']?>-<?=$row['telcom']?></td> 
</tr>
</table>
<p>&nbsp;</p>	
	<fieldset style="border: none; border-top: 2px solid silver;">
		<legend>Curriculum</legend>
	</fieldset>	
<table class='normal'>
<?
if(!empty($row['escolaridade']) or !empty($row['formacao'])){
?>
<tr class="header">
	<td>Escolaridade</td><td>Formação</td>
</tr>
<tr class="res">  	 
	<td ><?=nl2br($row['escolaridade'])?></td> 
	<td ><?=nl2br($row['formacao'])?></td> 
</tr>
<?
}
if(!empty($row['experiencia'])){
?>
<tr class="header">
	<td colspan="2">Experiência Profissional</td>
</tr>
<tr class="res">  	 
	<td  colspan="2" stylte="width:100%"><?=nl2br($row['experiencia'])?></td> 
</tr>
<?
}
if(!empty($row['qualificacao'])){
?>
<tr class="header">
	<td  colspan="2">Qualificações e Atividades</td>
</tr>
<tr class="res">  	 
	<td  colspan="2" stylte="width:100%"><?=nl2br($row['qualificacao'])?></td> 
</tr>
<?
}
if(!empty($row['obs'])){
?>
<tr class="header">
	<td  colspan="2" >Informações Adicionais</td>
</tr>
<tr class="res">  	 
	<td  colspan="2" stylte="width:100%"><?=nl2br($row['obs'])?></td> 
</tr>
<?
}
?>
</table>
<p>&nbsp;</p>

<?
$_sql = "SELECT e.idevento,e.evento 
		from evento e 
			join fluxostatuspessoa er on (e.idevento = er.idmodulo AND er.modulo = 'evento') 
			JOIN fluxostatus fs ON er.idfluxostatus = fs.idfluxostatus 
			JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus and s.idstatus = 64
		   where e.ideventotipo = 7 
			and er.tipoobjeto = 'pessoa'
			and er.idobjeto = ".$idpessoa.""
			.getidempresa('e.idempresa','evento');
				
		$_res = mysql_query($_sql) or die("A Consulta dos treinamentos vinculados falhou :".mysql_error()."<br>Sql:".$_sql); 
		$_qtdrows= mysql_num_rows($_res);

		if($_qtdrows > 0){
	?>
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Treinamentos Vinculados</legend>
		</fieldset>			
		<table class="normal"> 
			<tr> 	
				<td class="header">ID</td>
				<td class="header">Titulo</td>			
			</tr>
	<?			
			while($_row = mysql_fetch_array($_res)){		
			?>			
			 <tr class="res">
				<td pre-line >
					<a class="pointer" title="Editar" href="javascript:janelamodal('../?_modulo=evento&_acao=u&idevento=<?=$_row["idevento"]?>')">	
						<?=$_row["idevento"]?>                         
					</a>                    
				</td>
				<td pre-line><?=$_row["evento"]?></td>			
			</tr>	
<?
			}
?>				
		</table>
		<p>&nbsp;</p> 
<?
		}

?>

<?
$sql="SELECT c.idcarrimbo,dma(c.alteradoem) as dataassinatura,c.versao,d.titulo,d.idregistro,d.idsgdoc
            FROM carrimbo c,sgdoc d
           where c.idpessoa = ".$row['idpessoa']."            
            and c.idempresa=d.idempresa
			".getidempresa('c.idempresa','carrimbo')."
            and c.idobjeto =d.idsgdoc
            and c.tipoobjeto like 'documento%'
            and d.idsgdoctipo = 'treinamento'
			and d.status not in ('OBSOLETO')
			and c.status  in ('ATIVO','ASSINADO') order by c.alteradoem desc";
				
			$res = mysql_query($sql) or die("A Consulta dos treinamentos falhou :".mysql_error()."<br>Sql:".$sql); 
			$qtdrows= mysql_num_rows($res);

		if($qtdrows > 0){
	?>
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Treinamentos</legend>
		</fieldset>			
			 <table class="normal"> 
			 <tr> 	
				<td class="header">ID</td>
				<td class="header">Titulo</td>	
				<td class="header right">Data Assinatura</td>			
			</tr>
	<?			
			while($row = mysql_fetch_array($res)){		
			?>			
			 <tr class="res">
                            <td pre-line >
                                <a class="pointer" title="Editar" href="javascript:janelamodal('../?_modulo=documentoimp&_acao=u&idsgdoc=<?=$row["idsgdoc"]?>')">	
                                <?=$row["idregistro"]?>                         
                                </a>                    
                            </td>
                            <td pre-line><?=$row["titulo"]?></td>
                            <td pre-line class="right"><?=$row['dataassinatura']?></td>					
			</tr>	
<?
			}
?>				
			</table>
			<p>&nbsp;</p> 
<?
		}

?>


<?
$sql="SELECT c.idcarrimbo,dma(c.alteradoem) as dataassinatura,c.versao,d.titulo,d.idregistro,d.idsgdoc,d.versao,d.revisao,t.tipodocumento,d.idsgdoctipo
            FROM carrimbo c,sgdoc d,sgdoctipodocumento t,sgdoctipo tp
           where c.idpessoa = ".$idpessoa."       
            and c.idempresa=d.idempresa
            ".getidempresa('c.idempresa','carrimbo')."
            and c.idobjeto =d.idsgdoc
            and c.tipoobjeto like 'documento%' 
            and d.idsgdoctipodocumento = t.idsgdoctipodocumento
            AND c.status  in ('ATIVO','ASSINADO')
			and tp.idsgdoctipo = d.idsgdoctipo
			and d.status not in ('OBSOLETO')
			and tp.status = 'ATIVO'
			and tp.idsgdoctipo not in ('ata','auditoria','rnc','FORMULÁRIO')
			group by d.idsgdoc order by d.idsgdoctipo, c.alteradoem desc ,d.versao desc";
			$res = mysql_query($sql) or die("A Consulta das assinaturas falhou :".mysql_error()."<br>Sql:".$sql); 
			$qtdrows= mysql_num_rows($res);

			if($qtdrows > 0){
	?>
				
			<fieldset style="border: none; border-top: 2px solid silver;">
				<legend>Documentos Assinados</legend>
			</fieldset>		
			 <table class="normal"> 
			 <tr class="res">
	<?			
			$tipo = 'zero';
			while($row = mysql_fetch_array($res)){		
							if($tipo != '' and $tipo != $row["idsgdoctipo"]){?>
								</tr>
								<tr>
									<td colspan="20">
										<p>&nbsp;</p>
										<fieldset style="border: none; border-top: 2px solid silver;">
											<legend><?=$row["idsgdoctipo"]?></legend>
										</fieldset>
										<tr> 
											<td class="header">ID</td>
											<td class="header">Tipo Documento</td>
											<td class="header">Título</td>	
											<td class="header  right">Data Assinatura</td>			
										</tr>
									</td>
								</tr>
								<tr class="res">
							<?}?>
			 
                            <td pre-line >
                                <a class="pointer" title="Editar" href="javascript:janelamodal('../?_modulo=documento&_acao=u&idsgdoc=<?=$row["idsgdoc"]?>')">	
                                	<?=$row["idregistro"]?>                         
                                </a>                    
                            </td>
                            <td pre-line><?=$row["tipodocumento"]?></td>
                            <td pre-line><?=$row["titulo"]?></td>
                            <td pre-line  class="right"><?=$row['dataassinatura']?></td>	
								
								</tr>
<?
		$tipo = $row["idsgdoctipo"];	}
		
?>	
</tr>
		</table>
		<p>&nbsp;</p>
<?
	}
?>

<?
	$sqla = "SELECT w.* FROM webmailassinatura w JOIN pessoa p ON (p.webmailemail = w.email) WHERE p.idpessoa = ".$id." ".getidempresa('p.idempresa','pessoa');
	$resa = d::b()->query($sqla) or die("Erro ao consultar assinatura de email");
	if(mysqli_num_rows($resa) > 0){
		$ra = mysqli_fetch_assoc($resa);
		?>
		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Assinatura de E-mail</legend>
		</fieldset>	
		<table>
			<tr>
				<td>
					<?=$ra["htmlassinatura"]?>
				</td>
			</tr>
		</table>

	<?}
?>


<hr style="background-color: solid silver;">	
<p>&nbsp;</p>	
<?
		if(!empty($_timbradorodape)){?>
			<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="90px" width="100%"></div>
		<?}?>
</body>
</html>


