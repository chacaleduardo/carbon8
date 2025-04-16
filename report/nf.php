<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];

//error_reporting(E_ALL);

//die();

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nf";
$pagvalcampos = array(
	"idnf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".nf where idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
ob_start();

?>

<html>
<head>
<title>Impressão</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<?
if($_1_u_nf_tiponf=="V"){
	$strtitulo = "Nota Fiscal de Venda";
                                          
	if($_1_u_nf_status == 'ORCAMENTO'){		
        $strid="Orçamento";
	} else{
		$strid="Pedido";
	}
	$controle = $_1_u_nf_idnf;
}
if($_1_u_nf_tiponf=="P"){
	$strtitulo ="Pedido de Venda";
	$strid="Pedido";
}if($_1_u_nf_tiponf=="O"){
	$strtitulo ="Orçamento de Venda";
	$strid="Orçamento";
	$controle = $_1_u_nf_idnf;
	if($_1_u_nf_status=='ABERTA'){
		$_1_u_nf_status='EM ORÇAMENTO';
	}elseif($_1_u_nf_status=='CONCLUIDA'){
		$_1_u_nf_status='APROVADO';
	}
	
}
if($_1_u_nf_tiponf=="C"){
	$strtitulo ="Nota Fiscal de Entrada";
	$strid="Compra";
}
?>
<style>
	html {
    width: 760px;
}
.rotulo{
font-weight: bold;
font-size: 11px;
color:#848587;
}
.rotulob{
font-weight: bold;
font-size: 9px;
color:black;
}
.texto{
font-size: 11px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}

<?php if($_1_u_nf_idempresa == 2) { ?>
#_timbradocabecalho img
{
	height: 120px;
}

<?php } ?>

@media print{
	#rodapengeraarquivo{
		position: fixed;
		bottom: 0;
	}
}
</style>

</head>
<body style="max-width:1100px;">
<?
	$_timbradoheader = 'HEADERPEDIDO';
	if($geraarquivo == 'Y'){
		include("../form/timbrado.php");
	}else{
		//$timbradoidempresa = getImagemRelatorio('nf', 'idnf', $_1_u_nf_idnf);
		?>
		<table style="width: 100%;">
			<tr>
				<td>
					<?
						$_sqltimbrado="select * from empresaimagem where idempresa = ".$_1_u_nf_idempresa." and tipoimagem = '".$_timbradoheader."'";
						$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
						$_figtimbrado=mysql_fetch_assoc($_restimbrado);
						$_timbradocabecalho = $_figtimbrado["caminho"];
						if(!empty($_timbradocabecalho)){?>
							<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="120px" width="100%"></div>
						<?}
					?>
				</td>
			<tr>
		</table>
	<?}?>
    <div>
		<?
		$sqlf="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
		from nfscidadesiaf c,endereco e
		where c.codcidade = e.codcidade 
		and e.idendereco =".$_1_u_nf_idenderecofat;	 
		$resf=d::b()->query($sqlf) or die("erro ao buscar informações do endereço sql=".$sqlf);
		$rowf=mysqli_fetch_assoc($resf);
		?>
		
		<table>
			<tr>	
				<td style="width: 465px;"></td>	
				<td>	
					<?if(!empty($_1_u_nf_data)){?>	
						<div align="right" style="font-size: 11px;" ><font style="font-weight: bold;color:#939499">Data:</font> <?=$_1_u_nf_data?></div>
					<?}?>
					<td style="width: 40px;"></td>	
					<td>
						<div align="right" style="font-size: 11px;"><font style="font-weight: bold;color:#939499"> N&#186; <?=$strid?>:</font> <?=$controle?> </div>
					</td>	
								

					<?if(!empty($_1_u_nf_pedidoext)){?>	
					<div align="right" style="font-size: 11px;"><font style="font-weight: bold;">O.C.(Cliente):</font> <?=$_1_u_nf_pedidoext?></div>
					<?}?>		
				</td>
			</tr>
		</table>
		<hr>
		<table>
			<td align="left" class="rotulo">CLIENTE:</td> 
			<td nowrap class="texto"><?=traduzid("pessoa","idpessoa","nome",$_1_u_nf_idpessoa)?></font></td>	
		</table>
		<table>
			<?if(!empty($_1_u_nf_telefone)){?>	 
				<td align="left"class="rotulo">FONE:</td> 
				<td nowrap class="texto" style="min-width: 115px;"><?=$_1_u_nf_telefone?></td> 
			<?}?>
			<td ></td>
			<td>
				<?if(!empty($_1_u_nf_emailorc)){?>
					<td align="right" class="rotulo">EMAIL:</td>
					<td class="texto" nowrap ><?=$_1_u_nf_emailorc?></td>
				<?}?>
			</td>
		</table>
		<table>
			<td nowrap align="left" class="rotulo">A/C:</td> 
				<?if(!empty($_1_u_nf_aoscuidados)){?>
					<td class="texto" nowrap><?=$_1_u_pedido_aoscuidados?></td> 		
			<?}elseif(!empty($_1_u_nf_idcontato)){?> 		
					<td nowrap class="texto"><?=traduzid("pessoa","idpessoa","nome",$_1_u_nf_idcontato)?></font></td> 
			<?}?>	
		</table>
		
		<hr>
		<table>
			<td align="left" nowrap class="rotulo">RAZÃO SOCIAL:</td>
			<td  class="texto" nowrap><?=traduzid("pessoa","idpessoa","razaosocial",$_1_u_nf_idpessoafat)?></td>
		</table>
		<table>
		<td align="left" class="rotulo">CNPJ:</td>
			<td class="texto" style="min-width: 278px;"><?
				$cnpj=traduzid("pessoa","idpessoa","cpfcnpj",$_1_u_nf_idpessoafat);
				$cnpj=formatarCPF_CNPJ($cnpj,true);
				echo($cnpj);?>
			</td>
			<?$inscrest=traduzid("pessoa","idpessoa","inscrest",$_1_u_nf_idpessoafat);
			if($inscrest){?>
				<td class="rotulo">IE:</td>
			<td class="texto"><?echo($inscrest);}?>
			<td> <div style="width: 100px;"></div></td>
		</td>
		</table>
		<table>
			<td align="left" class="rotulo" nowrap>IND. IE:</td>
			<td class="texto nowrap" style="min-width: 300px;"><?
				$indie=traduzid("pessoa","idpessoa","indiedest",$_1_u_nf_idpessoafat);
				if($indie==1){
					$strindie="Contribuinte de ICMS";
				}elseif($indie==2){
					$strindie="Contribuinte isento";
				}elseif($indie==9){
					$strindie="Não Contribuinte";
				}
				echo($strindie);?>
			</td>
		</table>
		<table>
			<td align="left" class="rotulo" nowrap style="vertical-align: top;" >ENDEREÇO:</td>
			<td class="texto" style="min-width: 493px;" colspan=""><?=$rowf["logradouro"]?> <?=$rowf["endereco"]?> </td>
			<td class="rotulo">Nº:</td> 
			<td class="texto"><?=$rowf["numero"]?></td>
		</table>
		<table>
			<?if(!empty($rowf["complemento"])){?>
				<tr>
					<td align="left" class="rotulo" nowrap style="vertical-align: top;" >COMPLEMTO:</td>
					<td class="texto" colspan=""><?=$rowf["complemento"]?></td>				
				</tr>
			<?}?>
		</table>
		<table>
			<td align="left" class="rotulo">BAIRRO:</td>
			<td class="texto" style="min-width: 500px;"><?=traduzid("endereco","idendereco","bairro",$_1_u_nf_idenderecofat)?></td>
			<?$cep=traduzid("endereco","idendereco","cep",$_1_u_nf_idenderecofat);
			if($cep){?>
				<td class="rotulo">CEP:</td>
				<td class="texto"><?$cep=formatarCEP($cep,true);
					echo($cep);?>
				</td>
			<?}?>
		</table>
		<table>
			<td align="left" class="rotulo">CIDADE:</td>
				<td class="texto" style="min-width: 260px;"><?=$rowf['cidade']?> </td>
				<td class="rotulo">UF:</td> 
				<td class="texto"><?=traduzid("endereco","idendereco","uf",$_1_u_nf_idenderecofat)?></td>
			</tr>
		</table>
		<table>
		<td align="left" class="rotulo" nowrap>EMAIL NFE:</td>
			<td colspan="5" class="texto" nowrap>
				<?
					$emailxmlnfe = "";
					$sqlem="SELECT p.emailxmlnfe as emailxmlnfe
						from pessoa p
						where p.idpessoa = ".$_1_u_nf_idpessoa."
						and p.status='ATIVO'";
					$resem=d::b()->query($sqlem) or die("Erro ao buscar email sql=".$sqlem);
					if(mysqli_num_rows($resem) > 0){
						$virg = "";
						while($rowem=mysqli_fetch_assoc($resem)){
							if(!empty($rowem['emailxmlnfe'])){
								$emailxmlnfe .= $virg.$rowem['emailxmlnfe'];
								$virg = ",";
							}
						}
					}
					echo $emailxmlnfe;
				?>
			</td>
		</table>
		<table>
			<tr>
				<td style="vertical-align: top; max-width: 570px;">		
				
				</td>
				<?
				$sqle="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf,e.obsentrega
				from nfscidadesiaf c,endereco e
				where c.codcidade = e.codcidade 
				and e.status = 'ATIVO'
				and e.idtipoendereco = 3
				and e.idendereco =".$_1_u_nf_idendrotulo;	 
				$rese=d::b()->query($sqle) or die("erro ao buscar informações do endereço de entrega sql=".$sqle);
				$rowe=mysqli_fetch_assoc($rese);
				$qtde=mysqli_num_rows($rese);
				if($qtde>0){?>		
					<tr>
						<td style="vertical-align: top; max-width: 370px;">		
							<fieldset>
							<legend style="font-size: 11px;">Endere&ccedil;o de Entrega</legend>
							<table>
								<tr>
									<td align="left" class="rotulo" nowrap >Endere&ccedil;o:</td>
									<td class="texto" colspan="5"><?=$rowe["logradouro"]?> <?=$rowe["endereco"]?> N&deg;<?=$rowe["numero"]?></td>
											
								</tr>
							</table>
							<table>
								<?if(!empty($rowe["complemento"])){?>
									<tr>
										<td align="right" class="rotulo" nowrap >Complemento:</td>
										<td class="texto" colspan="5"><?=$rowe["complemento"]?></td>
									</tr>
								<?}?>
							</table>
							<table>
								<tr>
									<td align="left" class="rotulo">Bairro:</td>
									<td class="texto" style="min-width: 120px;"><?=$rowe["bairro"]?></td>
									<td align="left" class="rotulo">CEP:</td>
									<td class="texto"><?=$rowe["cep"]?></td>
								</tr>
							</table>
							<table>
								<tr>
									<td align="left" class="rotulo">Cidade:</td>
									<td class="texto" style="min-width: 118px;"><?=$rowe['cidade']?></td>
									<td align="right" class="rotulo">UF:</td>
									<td class="texto"><?=$rowe["uf"]?></td>
								</tr>
							</table>
								<table>
									<tr>			
										<td class="textoitem"  colspan="4">
											<?if(!empty($rowe['obsentrega'])){?>
												<b>OBS:</b> <?=$rowe['obsentrega']?>
											<?}?>
										</td>	
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				<?}?>		
			</tr>
		</table>		
		<? 
		//buscar uf para caso de divisao de ICMS
		$uf=traduzid("endereco","idendereco","uf",$_1_u_nf_idendereco);

		$sqlo = "SELECT *
				FROM nfitem i
				where i.idnf =".$_1_u_nf_idnf."
				and i.obs is not null
					and i.nfe='Y'
				and i.obs <> ''";	
		
		$qro = d::b()->query($sqlo) or die("Erro ao verificar se item tem observação:".mysqli_error());
		$qtdrowso= mysqli_num_rows($qro);
		
		$sqldiv = "SELECT *
				FROM nfitem i
				where i.idnf =".$_1_u_nf_idnf."
						and i.nfe='Y'
				and i.indiedest=9";
		
		$qrdiv = d::b()->query($sqldiv) or die("Erro ao verificar se item tem para divisão de icms:".mysqli_error());
		$qtdrowdiv= mysqli_num_rows($qrdiv);
		

		if($qtdrowdiv>0 and $uf!="MG"){
			$icmsufdest_ufremet="Y";
		}
		
		$sql = "SELECT p.descr,concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo,p.codprodserv,p.un,p.local,p.vlrvenda,p.ncm as ncmp,i.*
				FROM prodserv p,nfitem i left join prodservformula f on(f.idprodservformula = i.idprodservformula)
				where p.idprodserv = i.idprodserv
					and i.nfe='Y'
				and i.idnf =".$_1_u_nf_idnf." order by p.descr";	
		$qr = d::b()->query($sql) or die("Erro ao buscar itens da nota:".mysqli_error());
		$qtdrows= mysqli_num_rows($qr);
		if($qtdrows>0)
		{					
			?>
			<hr>
			<div style="max-width:100%;">
				<table style="width: 100%;">
					<tr style="background-color: #939499">
						<td align="center" class="rotulob"><font style="vertical-align: top;">Qtd</font></td>	
						<td align="center" class="rotulob"><font style="vertical-align: top;">UN</font></td>
						<td align="center" class="rotulob"><font style="vertical-align: top; max-width:50px">Descrição</font></td>
						<?if($_1_u_nf_moeda=='REAL'){?>			
							<td align="center" class="rotulob"><font style="vertical-align: top;">NCM</font></td>
							<td align="center" class="rotulob"><font style="vertical-align: top;">CFOP</font></td>
							<td align="center" class="rotulob"><font style="vertical-align: top;">Aliq<br>ICMS (%)</font></td>
							<td align="center" class="rotulob"><font style="vertical-align: top;">Red BC<br>ICMS (%)</font></td>
							<td align="center" class="rotulob"><font style="font-size: 10px;">IPI<br>(%)</font></td>
						<?}?>                      
						<td align="center" class="rotulob"><font style="vertical-align: top;">Vlr Unit <br>(<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>)</font></td>
						<td align="center" class="rotulob"><font style="vertical-align: top;">Desc Unit <br>(<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>)</font></td>
						<?if($_1_u_nf_moeda=='REAL'){?>
							<td align="center" class="rotulob"><font style="vertical-align: top;">Deson <br>ICMS 100/97<br>(BRL)</font></td>
							<td align="center" class="rotulob"><font style="font-size: 10px;">ICMS<br>(%)</font></td>
							<?
							if($icmsufdest_ufremet=="Y"){	
								?>			
								<td class="rotulob" align="center" style="font-size: 10px;">ICMS-Dest <br> GNRE<br>(%)</td>			
								<?
							}
						}
						?>
						<td align="center" class="rotulob"><font style="vertical-align: top;">Vlr Unit <br>Líquido<br>(<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>)</font></td>				
						<?//<td align="center"><font style="font-weight: bold;">ICMS INCLUSO</td>?>		
						<td align="center" class="rotulob"><font style="vertical-align: top;"><?if($_1_u_nf_tipoorc=="P"){?>Total Líquido<?}else{?>Vlr Total<?}?><br> (<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>)</font></td>	
						<?if($qtdrowso>0){?> 
							<td align="center" class="rotulob" ><font style="vertical-align: top;">Obs</font></td>	
						<?}?>			
					</tr>

					<?	
					$i=1;
					$vsubtotal=0.00;
					$vtotalicms=0.00;
					$vlrbrutofalso=0.00;
					$troca="S";
					while ($row = mysqli_fetch_array($qr))
					{
						$i = $i+1;
						$vtotalicms = $vtotalicms + $row["valicms"];
						$vsubtotal= $vsubtotal + $row["total"];
						$vlritemcdes =  $row["vlritem"] - $row["vicmsdeson"] / $row["qtd"];	
						//$vlritemcdes =  $row["vlritem"] - $row["des"] / $row["qtd"];
						$vtotalipi = $vtotalipi + $row["valipi"];
						$vseg=$vseg+$row["vseg"];
						$voutro=$voutro+$row["voutro"];
						$vlrbrutofalso=$vlrbrutofalso+$row["vlritem"]*$row["qtd"];
						$vtotaldeson = $vtotaldeson + $row["vicmsdeson"];
						$vtotaldes = $vtotaldes + $row["des"];
						
						$vicmsufdest=$vicmsufdest+$row['icmsufdest'];
						$vicmsufremet=$vicmsufremet+$row['icmsufremet'];
						
						//mudar a cor da linha
						if($troca=="S"){
								$cortr = "#FFFFFF";
								$troca="N";
							}else{
								$cortr = "#E8E8E8";
								$troca="S";
							} 
							
						if(!empty($row['rotulo'])){
							$descri=$row["descr"]." - ".$row['rotulo'];
						}else{
							$descri=$row["descr"];
						}
			 
						?>
 
						<tr style="background-color: <?=$cortr?>">
							<td align="center" class="rotulo"><font style="font-weight: bold;"><?=number_format($row["qtd"], 0, '', '.');?></font></td>	
							<td align="center" class="textoitem" ><?=$row["un"]?></td>
							<td align="left" class="textoitem" style="max-width: 50%;"><?=$descri?></td>
							<?if($_1_u_nf_moeda=='REAL'){?>	
								<td align="center" class="textoitem" ><?=$row["ncmp"]?></td>
								<td align="center" class="textoitem" ><?=$row["cfop"]?></td>
								<td align="center" class="textoitem" ><?=number_format(tratanumero($row["aliqicms"]), 2, ',', '.');?></td>
								<td align="center" class="textoitem" ><?=number_format(tratanumero($row["aliqbasecal"]), 2, ',', '.');?></td>
								<td align="center" class="textoitem" ><?=number_format(tratanumero($row["aliqipi"]), 2, ',', '.');?></td>
							<?}?>
							<?if($_1_u_nf_moeda!='REAL'){?>	
								<td align="right" class="textoitem" ><?=number_format(tratanumero($row["vlritemext"]), 4, ',', '.');?></td><?
							}else{?>
								<td align="right" class="textoitem" ><?=number_format(tratanumero($row["vlritem"]), 4, ',', '.');?></td>
							<?}?>
							<td align="right" class="textoitem" ><?=number_format(tratanumero($row["des"]), 4, ',', '.');?></td>
							<?if($_1_u_nf_moeda=='REAL'){?>	
								<td align="center" class="textoitem" ><?=number_format(tratanumero($row["vicmsdeson"]/$row["qtd"]), 2, ',', '.');?></td>                        
								<td align="center" class="textoitem" ><?=number_format(tratanumero($row["valicms"]), 2, ',', '.');?></td>
								<?
								if($icmsufdest_ufremet=="Y"){	
								?>
									<td class="textoitem"   align="center"><? if($row['indiedest']==9){?><?=number_format(tratanumero($row['icmsufdest']), 2, ',','.');?><?}else{echo("0,00");}?></td>
								<?
								}
								?>
							<?}?>
							<?if($_1_u_nf_moeda!='REAL'){?>	
								<td align="right" class="texto" ><?=number_format(tratanumero($row["totalext"]/$row["qtd"]),2,',','.'); ?></td>
								<td align="right" class="texto" ><?=number_format(tratanumero($row["totalext"]),2,',','.'); ?></td>
							<?}else{?>
								<td align="right" class="texto" ><?=number_format(tratanumero($row["total"]/$row["qtd"]),2,',','.'); ?></td>
								<td align="right" class="texto" ><?=number_format(tratanumero($row["total"]),2,',','.'); ?></td>
							<?}?>
							<?//<td align="center"><?=$row["valicms"];</TD>?>		 
							<?if(!empty($row["obs"])){?>
								<td style="min-width:150px" class="textoitem" nowrap><?=nl2br($row["obs"])?></td>
							<?}?>
						</tr> 	
					<? 	
					}
					$fvtotaliten=round($vsubtotal,2);
					?>

					<tr>
						<?if($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="S"){?>
							<td colspan="3"></td>
						<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="S"){?>
							<td colspan="1"></td>
						<?}elseif($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="P"){
							if($icmsufdest_ufremet=="Y"){
								$colspan="9";
							}else{
								$colspan="8";
							}
							?>
							<?if($_1_u_nf_moeda!='REAL'){$colspan="0";}?>	
							<td colspan="<?=$colspan?>"></td>
						<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="P"){
							if($icmsufdest_ufremet=="Y"){
								$colspan="7";
							}else{
								$colspan="6";
							}			
							?>
							<?if($_1_u_nf_moeda!='REAL'){$colspan="0";}?>
							<td colspan="<?=$colspan?>"></td>
						<?}?>
						<?if($_1_u_nf_moeda=='REAL'){?>
							<td colspan="5" align="right" class="rotulo" nowrap >SUB-TOTAL (BRL):</td>	
							<?//<td align="right" class="rotulo"><?=number_format(tratanumero($_1_u_nf_subtotal), 2, ',', '.');</td>?>			
							<td align="right" class="texto"><?=number_format(tratanumero($vlrbrutofalso), 2, ',', '.');?></td>
						<?}?>						
					</tr>
					<?if($_1_u_nf_moeda=='REAL'){?>                
						<tr>
							<td colspan="<?=$colspan?>"></td>
							<td colspan="5" align="right" class="rotulo" nowrap>DESONERAÇÃO (BRL)*:</td> 
							<td align="right"  class="texto"><?=number_format($vtotaldeson, 2, ',', '.'); ?></td>				
						</tr>
					<?}?>

					<?			
					if(!empty($_1_u_nf_frete) and $_1_u_nf_frete!=0 and $_1_u_nf_tipoorc=='P'){
						?>				
						<tr>
							<?if($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="S"){?>
								<td colspan="4"></td>
							<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="S"){?>
								<td colspan="1"></td>
							<?}elseif($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="P"){?>
								<td colspan="<?=$colspan?>"></td>
							<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="P"){?>
								<td colspan="<?=$colspan?>"></td>
							<?}?>	
							<td colspan="5" align="right"  class="rotulo">FRETE (<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>):</td> 
							<td align="right"  class="texto"><?=number_format(tratanumero($_1_u_nf_frete), 2, ',', '.'); ?></td>
						</tr>	
					<?
					}
					?>

					<?if($vtotalipi > 0 && $_1_u_nf_moeda=='REAL'){?>
						<tr>
							<td colspan="<?=$colspan?>"></td>
							<td colspan="5" align="right"  class="rotulo">IPI:</td> 
							<td align="right" class="texto">
								<?=number_format(tratanumero($vtotalipi), 2, ',', '.');?>
							</td>                
						</tr>
					<?}
					if(!empty($voutro)){
					?>
						<tr>
							<td colspan="<?=$colspan?>"></td>
							<td colspan="5" align="right"  class="rotulo">Outras Despesas:</td> 
							<td align="right" class="texto">
								<?=number_format(tratanumero($voutro), 2, ',', '.');?>
							</td>
							<td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						</tr>
					<?}
					if(!empty($voutro)){
						?>
						<tr>
							<td colspan="<?=$colspan?>"></td>
							<td colspan="5" align="right"  class="rotulo">Seguro:</td> 
							<td align="right" class="texto">
								<?=number_format(tratanumero($vseg), 2, ',', '.');?>
							</td>                
						</tr>
					<?}?>
					<tr>
						<?if($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="S"){?>
							<td colspan="4" style="background-color: #939499"></td>
						<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="S"){?>
							<td colspan="1" style="background-color: #939499"></td>
						<?}elseif($_1_u_nf_verdesc=="Y" and $_1_u_nf_tipoorc=="P"){?>
							<td colspan="<?=$colspan?>" style="background-color: #939499"></td>
						<?}elseif($_1_u_nf_verdesc=="N" and $_1_u_nf_tipoorc=="P"){?>
							<td colspan="<?=$colspan?>" style="background-color: #939499"></td>
						<?}?>	
						<td colspan="5" align="right" style="background-color: #939499; " class="rotulob">TOTAL (<?if($_1_u_nf_moeda == 'REAL'){echo "BRL";}else{echo $_1_u_nf_moeda;} ?>):</td>
						<?if($_1_u_nf_moeda=='REAL'){?>
							<td align="right" style="background-color: #B5B5B5; font-weight: bold;" class="texto" ><?=number_format(tratanumero($_1_u_nf_total), 2, ',', '.'); ?></font></td>
						<?}else{?>
							<td align="right" style="background-color: #B5B5B5; font-weight: bold"  ><b><?=number_format(tratanumero($_1_u_nf_totalext), 2, ',', '.');?></b></font></td>
						<?}?>
					</tr>
				</table>
			</div>
		<?}?>			
		<p style="font-size: 10px;color: #939499"> * BRL: Real Brasileiro / USD: Dólar dos Estados Unidos / EUR: Zona Euro</p> 
		<hr>
		<table id="tabelaenvio">
			<tbody>
				<!-- Envio -->
				<tr style="width: 125px;" class="rotulo" align="center" colspan="2" >
					<td>ENVIO</td>
				</tr>
				<?if(!empty($_1_u_nf_envio) and $_1_u_nf_tipoorc=='P'){?>						
					<tr>
						<td><span class="rotulo">PREVISÂO:</span><span class="texto"> <?=$_1_u_nf_envio?></span></td>
					</tr>
				<?}
				if(!empty($_1_u_nf_obsenvio) and $_1_u_nf_tipoorc=='P'){?>					
					<tr>
						<td><span class="rotulo">PREVISÃO ENTREGA:</span><span class="texto"> <?=$_1_u_nf_obsenvio?></span></td>
					</tr>
				<?}
				
				if( $_1_u_nf_tipoorc=='P'){
					if($_1_u_nf_modfrete==9){
						$strtipofrete = "Sem Frete";
					}elseif($_1_u_nf_modfrete==1){
						$strtipofrete = "FOB";
					}elseif($_1_u_nf_modfrete==3){
						$strtipofrete = "FOB PAGO";
					}elseif($_1_u_nf_modfrete==0){
						$strtipofrete = "CIF";
					}else{
						$strtipofrete = "Outros";
					}?>				
					<tr>
						<td><span class="rotulo">FRETE:</span><span class="texto"> <?=$strtipofrete?></span></td>								
					</tr>
					<?if(!empty($_1_u_nf_inffrete)){?>
						<tr>
							<td><span class="rotulo">INF. FRETE:</span><span class="texto"> <?=$_1_u_nf_inffrete?></span></td>
						</tr>
					<?}
				}
				if(!empty($_1_u_nf_idtransportadora)){?>
					<tr>
						<td><span class="rotulo">VIA:</span><span nowrap class="texto"> <?=traduzid("pessoa","idpessoa","nome",$_1_u_nf_idtransportadora)?></span></td> 
					</tr>
				<?}

				$sqlend="select * from endereco where idendereco = ".$_1_u_nf_idendrotulo;
				$resend=d::b()->query($sqlend) or die("Erro ao buscar obsentrega 2 sql=".$sqlend);
				$rowend=mysqli_fetch_assoc($resend);
				if(!empty($rowend['obsentrega'])){?>
					<tr>
						<td><span class="rotulo">OBS. VIA:</span><span colspan="2" class="textoitem"> <?=$rowend['obsentrega']?></span></td>
					</tr>		
				<?}?>
				<tr><td><br /></td></tr>
				<!-- Pagamento -->
				<tr colspan="2" align="center" style="width: 125px;" class="rotulo" style="margin-top: 50px;">
					<td>PAGAMENTO</td>
				</tr>
				<?if($_1_u_nf_tiponf=='V'){?>				
					<tr> 
						<td><span align="left" class="rotulo">EMISSÃO:</span><span class="texto" align="left"> <?=substr($_1_u_nf_dtemissao, 0, 10);?></span></td> 
					</tr>
				<?}	?>
				<?if($_1_u_nf_tiponf=='O'){?>				
					<tr>
						<td><span align="left" class="rotulo">VALIDADE PROPOSTA:</span><span class="texto"> <?=$_1_u_nf_validade?> dia(s)</span></td>
					</tr>
				<?}?>
				<?if(!empty($_1_u_nf_diasentrada)){?>				
					<tr>
						<td><span align="left" class="rotulo">1º VENCIMENTO:</span><span class="texto"> <?=$_1_u_nf_diasentrada?> dia(s)</span></td>
					</tr>
				<?}?>
				<?if(!empty($_1_u_nf_formapgto)){?>				
					<tr>	 
						<td><span align="left" class="rotulo">PAGAMENTO:</span><span class="texto"> <?=$_1_u_nf_formapgto?></span></td> 
					</tr> 
				<?}?>
				<?if(!empty($_1_u_nf_parcelas)){?>				
					<tr> 	 
						<td><span align="left" class="rotulo">PARCELA(S):</span><span class="texto"> <?=$_1_u_nf_parcelas?></span></td>
					</tr>
				<?}?>
				<?if(!empty($_1_u_nf_intervalo) and $_1_u_nf_parcelas > 1){?>				
					<tr> 
						<td><span align="left" class="rotulo">INTERVALO PARCELA:</span><span class="texto"> <?=$_1_u_nf_intervalo?> dia(s)</span></td> 
					</tr>
				<?}?>
			</tbody>
		</table>
		<table>
		<?
			// Calcula a data daqui 3 dias
			if(!empty($_1_u_nf_diasentrada) and  !empty($_1_u_nf_parcelas) and !empty($_1_u_nf_intervalo) and !empty($_1_u_nf_dtemissao) and $_1_u_nf_tiponf=='V'){
				/*
				$q=0;
				for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {
					$q++;
					if($v==0){
						$dias = $_1_u_nf_diasentrada - 1;
					}else{
						$dias=$_1_u_nf_diasentrada+($_1_u_nf_intervalo*$v) - 1;
					}
						
					$pvdate = $_1_u_nf_dtemissao;
					$pvdate = str_replace('/', '-', $pvdate);
					//echo date('Y-m-d', strtotime($pvdate));
					$timestamp = strtotime(date('Y-m-d', strtotime($pvdate))."+".$dias." days");
						
					//verificar se a data e sabado ou domingo
					$sqldia="SELECT DAYOFWEEK('".date('Y-m-d', $timestamp)."') as diasemana;";
					$resdia=d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
					$rowdia=mysqli_fetch_assoc($resdia);
						
					if($rowdia['diasemana']==1){//Se for domingo aumenta 1 dia
						$timestamp = strtotime(date('Y-m-d', $timestamp)."+1 days");
					}elseif($rowdia['diasemana']==7){//Se for sabado aumenta 2 dias
						$timestamp = strtotime(date('Y-m-d', $timestamp)."+2 days");
					}
					// Exibe o resultado
					?><tr>
						<td align="right" class="rotulo"><?echo(($v+1)."º Parcela :")?></td>
						<td class="texto"><?echo(date('d/m/Y ', $timestamp)); // ?></td>
					</tr>
				<?}
				*/
				$q=999;
				$sqlcx="select dma(c.datareceb) as dmadatareceb,c.* from nfconfpagar c where c.idnf=".$_1_u_nf_idnf;
				$rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
				$qtdpx=mysqli_num_rows($rescx);
				// for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {
				$v = 0;
				$tproporcao=0;
				while($rowcx=mysqli_fetch_assoc($rescx)){
					$q++;  
					$i++;  
					                      
					if($v==0){
						$dias = $_1_u_nf_diasentrada - 1;
					}else{
						$dias=$_1_u_nf_diasentrada+($_1_u_nf_intervalo*$v) - 1;
						}
					if(empty($_1_u_nf_dtemissao)){		    	
						$pvdate = date("d/m/Y H:i:s");
					}else{			  
						$pvdate = $_1_u_nf_dtemissao;		    	   
					}
					$pvdate = str_replace('/', '-', $pvdate);
					//echo date('Y-m-d', strtotime($pvdate));
					$timestamp = strtotime(date('Y-m-d', strtotime($pvdate))."+".$dias." days");

					//verificar se a data e sabado ou domingo
					$sqldia="SELECT DAYOFWEEK('".date('Y-m-d', $timestamp)."') as diasemana;";
					$resdia=d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
					$rowdia=mysqli_fetch_assoc($resdia);

					if($rowdia['diasemana']==1){//Se for domingo aumenta 1 dia
						$timestamp = strtotime(date('Y-m-d', $timestamp)."+1 days");
					}elseif($rowdia['diasemana']==7){//Se for sabado aumenta 2 dias
						$timestamp = strtotime(date('Y-m-d', $timestamp)."+2 days");
					}

					if(empty($rowcx['dmadatareceb'])){
						$rowcx['dmadatareceb']=date('d/m/Y', $timestamp);
					}

					// Exibe o resultado
					?> 
					<tr>
						<td align="right" class="rotulo"><?echo(($v+1)."º Parcela :")?></td>
						<td class="texto"><?=$rowcx['dmadatareceb']?></td>
					</tr>
			<?
				$v++; 
				}				
			}?>
		</table>
		<?if(!empty($_1_u_nf_obs)){?>
			<hr>	
			<table style="font-size: 12px">
				<tr>
					<td colspan="11"><font style="font-size:12;color:#939499"><b>OBSERVAÇÃO:</b></font></td>
				</tr
				
				<tr>
					<td colspan="50" style="max-width:550px;color:#939499">
					<?// if($_1_u_nf_envionfe=='PENDENTE'){
						echo(nl2br($_1_u_nf_obs));
						echo("<br>".nl2br($_1_u_nf_obspartilha));
					/*/	
					}elseif($_1_u_nf_tiponf=='V' and !empty($_1_u_nf_infcpl)){
						echo(nl2br($_1_u_nf_infcpl));
						
					}else{
						echo(nl2br($_1_u_nf_obs));
						echo("<br>".nl2br($_1_u_nf_obspartilha));
						
					}
					*/
					?>
					</td>
				</tr>
			</table>	
		<?}?>
				
		<br>
	</div>
	<?
	if($geraarquivo!='Y'){?>
		<table id="rodapengeraarquivo" style="width: 100%;">
			<tr>
				<td>
					<?
					//$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('nf', 'idnf', $_1_u_nf_idnf);

					$_sqltimbrado2="select * from empresaimagem where idempresa = ".$_1_u_nf_idempresa." and tipoimagem = 'IMAGEMRODAPE'";
					$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
					$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
					
					$_timbradorodape = $_figtimbrado2["caminho"];
					
					if(!empty($_timbradorodape)){?>
						<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="80px" width="100%"></div>
					<?}
					?>
				</td>
			<tr>
		</table>
	<?} ?>
	<script>
		$(document).ready(function(){
			let tamanho = '-'+$('#tabelaenvio').height()+'px';
			$('#tabelapaga').css('margin-top',tamanho);
		});
	</script>
</body>
</html>

<?
if($geraarquivo=='Y'){

	$html = ob_get_contents();

	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();

	//echo($html);die;


	// Incluímos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");
	 
	

	// Instanciamos a classe
	$dompdf = new DOMPDF();
	 
	// Passamos o conteúdo que será convertido para PDF
	//Alterado para mb_convert_encoding, pois não estava aparecendo os caracteres especiais - Comentada a linha abaixo "preg_match" (LTM - 24-08-2020 - 369143)
	//$html=preg_match("//u", $html)?utf8_decode($html):$html; //MAF060519: Converter para ISO8859-1. @todo: executar upgrade no dompdf	
	$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
	$dompdf->load_html($html);
	
	//$dompdf->option('defaultFont', 'Times New Roman?');

	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	$dompdf->render();// Acompanhar a quantidade de paginas geradas no pelo DomPdf
	$grupo = rstr(8);

	if($gravaarquivo=='Y'){
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
		$text = file_put_contents("/var/www/laudo/tmp/nfe/Orcamento_prod_".$_1_u_nf_idnf.".pdf",$output);
		if($text == false) {
			$err = error_get_last();
			echo("Não foi possível salvar o arquivo: ".$err['message']);
		} else {
			echo("OK");
		}    	
	}else{
//		$dompdf->render();
//die;
		// e exibido para o usuário
		$dompdf->stream("Orcamento_prod_".$_1_u_nf_idnf.".pdf");
	}
}

?>
