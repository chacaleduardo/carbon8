<?
	include_once("../inc/php/validaacesso.php");
	$listaacao =$_GET["listaacao"];
?>
<html>
	<head>
		<title>Tag</title>
		<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
		<link href="..\inc\css\carbon.css" rel="stylesheet">
		<!-- <link href="..\inc\css\bootstrap\css\bootstrap.css" rel="stylesheet"> -->
		<link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">
		<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
		<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>
		<script src="../inc/js/carbon.js"></script>
		<script src="../inc/js/functions.js"></script>
		<script src="../inc/js/notifications/smart.js"></script>
		<link href="../inc/js/notifications/smart.css" media="all" rel="stylesheet" type="text/css" />
	</head>
	<style>
		.res{
			text-transform: uppercase;
		}
	</style>
	<body>
	<?
		// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('tag', 'idtag', $_GET['idtag']);
		
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
				
		
		//$figurarelatorio = "../inc/img/repheader.png";
		if (!empty($_REQUEST['idtag'])) {
			$id = $_REQUEST['idtag'];
		}
		function getTagsContido($inidTag){

        
			$sql = "SELECT * From (
							SELECT  t.tag,
									concat(e.sigla,'-',t.tag) as tagcomsigla,
									concat(etri.sigla,'-',tori.tag) as tagoriginal,
									fs.ordem,
									concat(e.sigla,'-',t.descricao) as descricao,
									ifnull(t.nserie, '-') as nserie
							from tag t
							   	left join tagtipo tt on (tt.idtagtipo=t.idtagtipo )
								left join tagclass tc on (tc.idtagclass=t.idtagclass and tc.status = 'ATIVO')
								join tagsala s on (s.idtag=t.idtag and s.idtagpai = $inidTag)
								join empresa e on (e.idempresa = t.idempresa)
								left join tagreserva tr on (tr.idobjeto = t.idtag and tr.objeto = 'tag')
								left join tag tori on (tori.idtag = tr.idtag)
								left join empresa etri on (etri.idempresa = tori.idempresa)
								JOIN fluxostatus fs ON fs.idfluxostatus = t.idfluxostatus
								JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus
							WHERE t.status = 'ATIVO'
							UNION 
							SELECT
								t.tag,
								concat(e.sigla,'-',t.tag) as tagcomsigla,
								concat(etri.sigla,'-',tori.tag) as tagoriginal,
								fs.ordem,
								concat(e.sigla,'-',t.descricao) as descricao,
								ifnull(t.nserie, '-') as nserie
							from tag t
								left join tagtipo tt on (tt.idtagtipo=t.idtagtipo )
								left join tagclass tc on (tc.idtagclass=t.idtagclass and tc.status = 'ATIVO')
								join empresa e on (e.idempresa = t.idempresa)
								left join tagreserva tr on (tr.idobjeto = t.idtag and tr.objeto = 'tag')
								left join tag tori on (tori.idtag = tr.idtag)
								left join empresa etri on (etri.idempresa = tori.idempresa)
								JOIN fluxostatus fs ON fs.idfluxostatus = t.idfluxostatus
								JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus
							WHERE t.idtag = $inidTag
								  ) a
								  order by
								  tag";
				
			$res = d::b()->query($sql) or die("getTagsContido: Erro ao recuperar tags: ".mysqli_error(d::b()));
		
			$ordenedByFluxoStatusArr = [];
			$i=0;
			while($item = mysql_fetch_assoc($res))
			{
				$ordenedByFluxoStatusArr[$i] = $item;
				$i++;
			}
		
			return $ordenedByFluxoStatusArr;
		}
		
			

		$sql=" SELECT 
					t.idtag,
					t.tag,
					t.status,
					t.descricao,
					t.fabricante,
					t.modelo,
					t.numnfe,
					t.nserie,
					t.exatidao,
					t.padraotempmin,
					t.padraotempmax,
					t.status,
					t.exatidao,
					t.padraotempmin,
					t.padraotempmax,
					t.obs,
					tc.tagclass,
					tt.tagtipo,
					concat(e.sigla, '-', t.tag) as sigla
				from 
					tag t 
				left join 
					tagclass tc on tc.idtagclass = t.idtagclass 
				left join 
					empresa e on e.idempresa = t.idempresa
				left join
					tagtipo tt on tt.idtagtipo = t.idtagtipo
				where 
					t.idtag = '".$id."';
				";
		//echo "<!-- ".$sql." -->";

		$res = d::b()->query($sql) or die("Falha ao pesquisar Tag : " . mysqli_error(d::b()) . "<p>SQL: $sql");
		$row = mysqli_fetch_array($res);
		$idtag = $row['idtag'];
		?>
		<table>
			<tr>
				<td >
					<table class="tbrepheader">
						<tr>
							<td class="header" pre-line>Tag:</td>
							<td class="header" pre-line><?=$row['sigla'];?> </td>
						</tr>
						<tr>
							<td class="header" pre-line>Classificação:</td>
							<td class="header" pre-line><?=strtoupper($row['tagclass']);?></td>
						</tr>
						<tr>
							<td class="header" pre-line>Descrição:</td>
							<td pre-line><strong><?=strtoupper($row['descricao'])?></strong></td>
						</tr>
						<tr>
							<td class="header" pre-line>Status:</td>
							<td class="header " pre-line><?=strtoupper($row['status']);?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>	

		<fieldset style="border: none; border-top: 2px solid silver;">
			<legend>Informações Principais</legend>
		</fieldset>	
		<?
		$sqlu="SELECT 
					GROUP_CONCAT(u.unidade SEPARATOR ', ') AS unidade
				from 
					unidade u 
				join 
					unidadeobjeto p on( u.idunidade = p.idunidade	and p.idobjeto = ".$idtag." and p.tipoobjeto = 'tag')
				where 
					u.idempresa = ".cb::idempresa()." $inunidade 
				order by 
					u.unidade";
		$resu=d::b()->query($sqlu) or die("Erro ao buscar as unidades sql=".$sqlu);
		$rowu = mysqli_fetch_array($resu);

		?>				

		<table class='normal'>
			<tr class="header">
				<td>UNIDADE(S)</td> 	
				<!-- <td>LP</td> -->
			</tr>
			<tr class="res">
				<td colspan="5"><?=$rowu['unidade'];?></td> 
			</tr>
			<tr class="res">
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr class="header">	
				<td>TIPO</td>  
				<td>FABRICANTE</td> 
				<td>MODELO</td>
				<td>NFE.</td>
				<td>N° SÉRIE</td>
			</tr>
			<tr class="res">
				<td><?=$row['tagtipo']?></td>
				<td><?=$row['fabricante']?></td> 
				<td><?=$row['modelo']?></td> 
				<td><?=$row['numnfe']?></td>  
				<td><?=$row['nserie']?></td>
			</tr>
			<tr class="res">
				<td colspan="5">&nbsp;</td>
			</tr>
			<?
			$sqlu="select GROUP_CONCAT(t.tag SEPARATOR ', ') AS tagsala from tagsala ts join tag t on t.idtag = ts.idtagpai where ts.idtag = ".$idtag.";";
			$resu=d::b()->query($sqlu) or die("Erro ao buscar as unidades sql=".$sqlu);
			$rowu = mysqli_fetch_array($resu);
			?>
			<tr class="header">	
				<td>LOCALIZAÇÃO</td>  
				<td>EXATIDÃO REQUERIDA</td> 
				<td>PARÂMETRO MÍNIMO</td>
				<td colspan="2">PARÂMETRO MÁXIMO</td>
			</tr>
			<tr class="res">	 
				<td><?=$rowu['tagsala']?></td> 
				<td><?=$row['exatidao']?></td> 
				<td><?=$row['padraotempmin']?></td> 
				<td colspan="2"><?=$row['padraotempmax']?></td>  
			</tr>
			<tr class="res">
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr class="header">	
				<td colspan="5">Obs</td> 
			</tr>
			<tr class="res">
				<td colspan="5"><?=$row['obs'];?></td> 
			</tr>	
		</table>
		<?//Manutenções>?>
		<?$arrTagsContido = getTagsContido($idtag);?>
		<div style="width: 60%;">
		<div class="panel panel-default">
				<div class="panel-heading">POSSUI (ATUALIZAÇÃO ETIQUETA TAG)</div>
				<div class="panel-body">
    				<table class="table table-striped planilha">
    					<tr>
    						<th align="left">LOCADO</th>
    						<th align="left">ATIVO</th>
							<th align="left">DESCRIÇÃO</th>
							<th align="left">N° Série</th>
    					</tr>
    						<? foreach($arrTagsContido as $row){ ?>
    					<tr>
    						<td align="left">
								<?if($row['tagoriginal']){?>
    						    <div style="font-weight: bold;">
									<?=$row['tagoriginal'];?>
    				            </div>
								<?}else{?>
									-
								<?}?>
    						 </td>
    						 <td align="left">
    						    <div style="font-weight: bold;">
									<?=$row['tag'];?>
								</div>
    						 </td>
							 <td align="left">
    						    <div style="font-weight: bold;">
									<?=$row['descricao'];?>
								</div>
    						 </td>
							 <td align="left">
    						    <div style="font-weight: bold;">
									<?=$row['nserie'];?>
								</div>
    						 </td>
    					</tr>
                        <? } ?>
    				</table>

				</div>
		</div>
		<input type="hidden" value="<?=$idtag?>" id="_idtag_">
	</div>
	</body>
</html>


