<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

require_once("../inc/php/laudo.php");

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

?>
<html>
<head>
	<title>Formalização</title>
<style>
@media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
* {
	text-shadow: none !important;
	filter:none !important;
	-ms-filter:none !important;
	font-family: Helvetica, Arial;
	font-size: 14px;
	-webkit-box-sizing: border-box; 
	-moz-box-sizing: border-box;    
	box-sizing: border-box; 
}
html{
	background-color: silver;
}
body {
	line-height: 1.4em;
	background-color: white;
}

@media screen{
	body {
		margin: auto;
		margin-top: 0.2cm;
		margin-bottom: 1cm;
		padding: 3mm 10mm;
		width: 21cm;
	}
	
	.quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 200%;
		margin: 1.5cm -100px;
	}
	
}

@media print{
	html{
		background-color: transparent;
	}
	body {
		margin: 0cm;
	}
	.quebrapagina{
		page-break-before:always;		
	}
}
[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
header{
	 background-color: white;
	 top: 0;
	 height: 1cm;
	 line-height: 1cm;
	 display: table;
}
header + hr{
	margin: 0;
}
.logosup{
	height: inherit;
	line-height: inherit;
	display: table-cell;
}
.logosup img{
	height: 0.5cm;
	vertical-align: middle;
}
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
	text-align: center;
	font-size: 0.5cm;
	font-weight: bold;
}
.row{
	display: table;
	table-layout: fixed;
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px solid #f8f8f8;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
}
.row.grid .col{
	border: 1px solid silver;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo .titulogrupo{
	margin: 0px;
	border-bottom: 1px dotted silver;
	color: silver;
	font-weight: bold;
	margin-bottom: 2mm;
}
.rot{
	color: silver;
	overflow: hidden;
	font-size: 12px;
}
.quebralinha{
	white-space: normal;
}
[class*='margem0.0']{
	margin: 0 0;
}
.hidden{
	display: none;
}
.sublinhado{
	border-bottom: 1px dashed gray;
}

</style>
</head>
<body>
<?
	$sqlp="select p.*,u.flgpotencia
			from prodserv p
				left join unidadevolume u on u.un=p.un
			where p.idprodserv=".$_1_u_lote_idprodserv;

	$resp = d::b()->query($sqlp) or die("A Consulta das informações da prodserv falhou : " . mysql_error() . "<p>SQL: $sqlp");
	$rowp= mysqli_fetch_assoc($resp);
?>

<header class="row margem0.0">
	<div class="logosup col 20"><img src="../inc/img/impcab.png"></div>
	<div class="titulodoc">Formalização</div>
	<div class="col 20"></div>
</header>
	
	<div class="row">
		<div class="col 5 rot">ID:</div>
		<div class="col 15"><?=$_1_u_lote_partida?></div>
		<div class="col 10 rot">Tipo:</div>
		<div class="col 20"><?=$_1_u_lote_tipoform?></div>		
		<div class="col 10 rot">Produto:</div>
		<div class="col 30"><?=traduzid("prodserv ", "idprodserv", "descr", $_1_u_lote_idprodserv)?></div>
		<div class="col 10 rot">Status:</div>
		<div class="col 15"><?=$_1_u_lote_status?></div>
	</div>
	<div class="row">
		<div class="col 5 rot">QTD:</div>
		<div class="col 15"><?=recuperaExpoente($_1_u_lote_qtdprod,$_1_u_lote_qtdprod_exp)?>-<?=traduzid("unidadevolume", "un", "descr", $rowp["un"])?></div>
		<div class="col 10 rot">Cliente:</div>
		<div class="col 70"><?=traduzid("pessoa ", "idpessoa", "nome", $_1_u_lote_idpessoa)?></div>		
	</div>

	
	<?$sql="select c.idlotecons,c.qtdd,c.qtdd_exp,lf.idobjeto,lf.tipoobjeto,l.partida,l.exercicio,l.idlote,p.descr
			from lotecons c,lotefracao lf,lote l,prodserv p
			where c.tipoobjeto='lote' 
			and c.idobjeto=".$_1_u_lote_idlote."
			and c.idlotefracao = lf.idlotefracao
                         and c.qtdd>0
			and lf.idlote = l.idlote
			and l.idprodserv = p.idprodserv order by p.descr,l.partida,l.exercicio";
	
		$res = d::b()->query($sql) or die("A Consulta das informações do consumo dos lotes falhou : " . mysql_error() . "<p>SQL:".$sql);
		$qtdrow= mysqli_num_rows($res);
		
	if($qtdrow>0){
	?>

		<?
		$i=0;
		while($row= mysqli_fetch_assoc($res)){
		?>
			<div class="row grid">
				<div class="col grupo 10 quebralinha">
				<?if($i==0){?><div class="titulogrupo">Qtd.</div><?}?>
							<?=recuperaExpoente($row['qtdd'],$row['qtdd_exp'])?>
				</div>
		
				<div class="col grupo 20 quebralinha">
				<?if($i==0){?><div class="titulogrupo">Partida</div><?}?>
							<?=$row['partida']?>/<?=$row['exercicio']?>
				</div>
			
				<div class="col grupo 70 quebralinha">
				<?if($i==0){?><div class="titulogrupo">Descrição</div><?}?>
							<?=$row['descr']?>
				</div>
			</div>
		
			
		<?
			$i++;
		}
		?>
	<hr>
	<?
	}
	?>

	
	<?
	//if($_1_u_lote_status=='FORMALIZACAO'){
		$y=$i;
		$arrLoAtiv=getLoteAtiv($_1_u_lote_idlote);
	//	$arrPAG=getPrAtivGrupo($_1_u_lote_idprodserv,$_1_u_lote_idlote);
		//print_r($arrPAG);die;
		while(list($idLoteAtiv, $grupoAtiv) = each($arrLoAtiv)){//Grupos de Atividade
		
		  $idLoteAtiv=$grupoAtiv['idloteativ'];
                  $ativ=$grupoAtiv['ativ'];
                  $idPrAtiv=$grupoAtiv['idprativ'];
		  
		//print_r($grupoAtiv['idloteativ']);die();
		  
			//while(list($idPrAtiv, $ativ) = each($grupoAtiv["atividades"])){//Atividades
				$ord=$ord+1;
				$arrS=getAtivObjetoselsala($_1_u_lote_idlote,$idLoteAtiv);//array de objetos selecionados
				//print_r($arrS);
				$y=$y+1;
				
				$sqlla="select dma(la.execucao) as dmaexec,la.* 
					from loteativ la 
					where la.idlote =".$_1_u_lote_idlote." 
					and la.idprativ=".$idPrAtiv;
				$resla= d::b()->query($sqlla) or die("Erro ao buscar atividades do lote : " . mysql_error() . "<p>SQL:".$sqlla);
				$qtdla= mysqli_num_rows($resla);
				$rowla=mysqli_fetch_assoc($resla);
				
				if($rowla['status']=='PENDENTE'){
					$fundodiv="fundovermelhoclaro";
				}else{
					$fundodiv="fundoverdeclaro";
				}
				if($qtdla>0){
					
				$arrPAO=getPrAtivObjetos($idPrAtiv,$_1_u_lote_idlote);//todos os itens
				
				$jsonAtivObj = new Services_JSON();
				$jsonAtiv=$jsonAtivObj->encode($arrPAO);
				
			?>
	<div class="quebrapagina"></div>
		<div class="row">
			<div class="col grupo 100 quebralinha">
				<div class="titulogrupo"><?=$ord?>-<?=$ativ?>
				
						<?
						while(list($id, $obj) = each($arrS)){					
							if($obj["idprativ"]==$idPrAtiv){//SALA		
											echo("SALA :".$obj["tag"]."-".$obj["descricao"]);														
							}							 
						}
		?>
			
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col 10 rot">Execução:</div>
			<div class="col 20"><?=$rowla['dmaexec']?></div>
			<div class="col 10 rot">Status:</div>
			<div class="col 70"><?=$rowla['status']?></div>		
		</div>
	
	
			
<?				//EQUIPAMENTOS
				$arrOSE=getAtivObjetoselequip($_1_u_lote_idlote,$idLoteAtiv);
				if(count($arrOSE)>0){
				$i=0;
					while(list($id, $obj) = each($arrOSE)){	
						?>
						<div class="row grid">
							<div class="col grupo 100 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Equipamento</div><?}?>
							<?
							echo($obj["tag"]."-".$obj["descricao"]);	
							?>
							</div>							
						</div>
<?
						$i++;
					}
?>

<?	
				}
			//TESTES
				$arrOST=getAtivObjetoselteste($_1_u_lote_idlote,$idLoteAtiv);
				if(count($arrOST)>0){
?>

<?					
					$i=0;
					while(list($id, $obj) = each($arrOST)){	
?>					
						<div class="row grid">
							<div class="col grupo 100 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Testes</div><?}?>
							<?
							echo("Reg.[".$obj["idregistro"]."/".$obj["exercicio"]."]-".$obj["codprodserv"]."-".$obj["teste"]);	
							?>
							</div>							
						</div>
<?
						$i++;
					}
?>
					
<?					
				}
?>
<?				//CONTROLE EM PROCESSO
				$arrOST=getAtivObjetoselctrproc($_1_u_lote_idlote,$idLoteAtiv,'prativobj');
				if(count($arrOST)>0){
					$i=0;
					while(list($id, $obj) = each($arrOST)){	
					?>
						<div class="row grid">
							<div class="col grupo 100 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Controle em processo</div><?}?>
							<?
							echo($id."-".$obj["descr"]);	
							?>
							</div>							
						</div>
					<?
						$i++;
						}
					?>
					
<?					
				}
?>
<?				//mateirais e utensilios
				$arrOST=getAtivObjetoselctrproc($_1_u_lote_idlote,$idLoteAtiv,'materiais');
				if(count($arrOST)>0){
					$i=0;
					while(list($id, $obj) = each($arrOST)){	
						?>

						<div class="row grid">
							<div class="col grupo 100 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Materiais e Utensílios</div><?}?>
							<?
							echo($id."-".$obj["descr"]);	
							?>
							</div>							
						</div>
					<?
						$i++;
						}
					?>
					
					<?					
				}
?>
				
	<?
		listaconsumoreport($idLoteAtiv,'loteativ');
		?>
				
				
		<?			
				}
			//}//while(list($idPrAtiv, $ativ) = each($grupoAtiv["atividades"])){
			
			$sqlas="select s.idloteobj,b.idbioensaio, n.nucleo as estexterno,b.partida,b.idregistro,b.exercicio
				from loteobj s 
				left join bioensaio b on(b.idloteativ = s.idloteativ)
						left join nucleo n on(n.idnucleo = b.idnucleo)
				where s.idlote=".$_1_u_lote_idlote."
				and s.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                 
				and s.idloteativ = ".$idLoteAtiv."
				and s.idobjeto = 5 
				and s.tipoobjeto = 'prativopcao'";
			
		
			$resas=d::b()->query($sqlas) or die("3-Falha ao buscar dados na [loteobj-prativopcao] : ".mysqli_error(d::b())."<p>SQL: ".$sqlas);
			$i=0;
			while($rowas= mysqli_fetch_assoc($resas)){
				
		?>	
	
		<div class="row grid">
			<div class="col grupo 100 quebralinha">
			<?if($i==0){?><div class="titulogrupo">Bioensaio</div><?}?>
			<?
			if(!empty($rowas['idregistro'])){
				echo($rowas['idregistro']."/".$rowas['exercicio']." - ".$rowas['estexterno']);
			}else{
			?>
			<br>
			<?
			}
			?>
			</div>							
		</div>
<?	
			$i++;
			}
		}//while(list($idPrGrupoAtiv, $grupoAtiv) = each($arrPAG)){		
	
	?>
	
	
	
	
</body>
</html>


