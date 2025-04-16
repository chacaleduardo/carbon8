<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
$_acao = $_GET["_acao"];
$alteracliente = $_GET["alteracliente"];
$idpessoa = $_GET["idpessoa"];
$idnotafiscal = $_GET["idnotafiscal"];	

if($_GET['_idempresa']) { $urlIdempresa = '&_idempresa='.$_GET['_idempresa']; } else {$urlIdempresa = '';}

if($_POST){
    require_once("../inc/php/cbpost.php");
}

if (empty($_acao)){
    die("Parâmetro [acao] não informado!");
}
if(!empty($idnotafiscal)){
	$pagvaltabela = "notafiscal";
	$pagvalmodulo=$_GET['_modulo'];
	$pagvalcampos = array(
		"idnotafiscal" => "pk"
	);

	$pagsql = "select * from notafiscal where idnotafiscal = '#pkid'";
	include_once("../inc/php/controlevariaveisgetpost.php");
}

function truncarNumero($numero) {
    return floor($numero * 100) / 100;
}

function getContaItem(){
	global $JSON;

	$sq = getContaItemSelect("and c.tipo='SERVICO'");

	$rq = d::b()->query($sq) or die("Erro ao consultar Tipoprodserv. ".$sq);

	if(mysqli_num_rows($rq) > 0){
		$arr = array(); $i = 0;

		while($r = mysqli_fetch_assoc($rq)){
			$arr[$r["idcontaitem"]]['contaitem'] = $r["contaitem"];
		}
		$arr = $JSON->encode($arr);
	}else{
		$arr = 0;
	}

	return $arr;
}

if(($_acao=='i')or($_acao=='u') and empty($idnotafiscal)){
    if (empty($idpessoa)){
		//die("Parà¢metro [idpessoa] não informado para Insert. Impossà­vel continuar;");
       selecionacliente();
    }else{
		$_idempresa=$_GET['_idempresa'];
		//echo "2 - $idpessoa"; die;
		//Gera ou retorna o número inicial (idnotafiscal) para a nova nota fiscal.
		//O idnotafiscal não é o controle interno, servindo somente para se poder gerar uma nota temporária, onde se inclui ou exclui itens.
		$idnotafiscal = retidnotafiscal($idpessoa,$_idempresa);
		?>
		<script>debugger
			var vUrlNova = alteraParametroGet("idnotafiscal",<?=$idnotafiscal?>,window.location.search);
			vUrlNova = alteraParametroGet("_modulo","nfs",vUrlNova);

			CB.setWindowHistory(vUrlNova);
			document.location.reload(true);
		</script>
		<?
    }
}
/*
if (empty($idnotafiscal)){
    die("[1] - Parâmetro [idnotafiscal] está vazio. Impossível continuar!");
}
 
 */
?>
<script>

CB.preLoadUrl = function(){
    //Como o carregamento é via ajax, os popups ficavam aparecendo apà³s o load
    $(".webui-popover").remove();
}

$(".oEmailorc").webuiPopover({
    trigger: "hover"
    ,placement: "bottom-left"
    ,delay: {
    show: 300,
    hide: 0
    }
});
$(".oNFSe").webuiPopover({
    trigger: "click"
	,placement: "bottom-left"
	,width: 600
    ,delay: {
    show: 300,
    hide: 0
    }
});
$(function(){
    $('.caixa').autosize();
});

</script>
<?
function getClientesnf(){

    $sql= "SELECT
                p.idpessoa,
                if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome,
                CASE p.idtipopessoa
                WHEN 1 THEN 'FUNCIONARIO'
                    WHEN 5 THEN 'FORNECEDOR'
                    WHEN 2 THEN 'CLIENTE'	
                    WHEN 7 THEN 'TERCEIRO'
                    WHEN 12 THEN 'REPRESENTAÇÃO'					
                END as tipo
        FROM pessoa p	
		left join endereco d on(d.idpessoa=p.idpessoa and d.status='ATIVO' and idtipoendereco = 2)			
        WHERE p.status IN ('ATIVO','PENDENTE')
                AND p.idtipopessoa  in (1,2,5,7,12)
               ".getidempresa('p.idempresa','pessoa')."
        ORDER BY p.nome";

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
        $arrret[$r["idpessoa"]]["tipo"]=$r["tipo"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getClientesnf();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);

function getProdServTemp(){
   
         
    $sql="select idprodserv,descr from prodserv where 1  ".getidempresa('idempresa','prodserv')." and venda = 'Y' and tipo='SERVICO' and status='ATIVO' order by descr";
    
    //die($_SESSION["IDPESSOA"]);
    $res = d::b()->query($sql) or die("getProdServTemp: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idprodserv"]]["descr"]=$r["descr"];
    }
	asort($arrret);
	return $arrret;
}

//Recupera os contato as serem selecionados
$arrprodservtemp=getProdServTemp();
//print_r($arrCli); die;
$jprodservtemp=$JSON->encode($arrprodservtemp);


function selecionacliente(){
    global $_acao;
?>
<div class="row">
    <div class="col-md-12" >
		<div class="panel panel-default" >
			<div class="panel-heading">Selecione o Cliente</div>
			<div class="panel-body ">
                            <table>
                                <td align="right">Cliente:</td> 
                                <td> 
                                    <input i name="_1_<?=$_acao?>_notafiscal_idnotafiscal" type="hidden"	value="" >
                                    <input  type="text" name="_1_<?=$_acao?>_notafiscal_idpessoa" vnulo cbvalue="" value="" style="width: 35em;" vnulo>                              
                                </td>
                            </table>
                        </div>
                </div>
    </div>
</div>
<?
}

if(!empty($idnotafiscal)){
?>
<style>
 select:first-child {
        color: gray;
    }
</style>
<div class="row">
    <div class="col-md-12" >
		<div class="panel panel-default" >
			<div class="panel-heading">Resumo Atual da Nota Fiscal</div>
			<div class="panel-body ">
				<?	
				$sql = "select 
						nf.idnotafiscal
						,nf.idpessoa
						,nf.status
						,nf.subtotal
						,nf.total
						,nf.irrf
						,nf.pis
						,nf.cofins
						,nf.comissao
						,nf.csll
						,nf.iss
						,nf.emissao as amdemissao
						,dma(nf.emissao) as emissao
						,dma(nf.vencimento) as vencimento
						,concat(p.nome,' ',ifnull(d.uf,'')) as nome
						,p.cpfcnpj
						,nf.controle
						,nf.nnfe
						,nf.numerorps
						,pf.observacaonf
						,nf.mostraabertos
						,nf.mostramesmocnpj
									,nf.idformapagamento
									,f.formapagamento
									,l.idnfslote
					FROM
						notafiscal nf join
						pessoa p left join preferencia pf on(pf.idpreferencia=p.idpreferencia)
									left join formapagamento f on(f.idformapagamento=nf.idformapagamento)
						left join endereco d on(d.idpessoa=p.idpessoa and d.status='ATIVO' and idtipoendereco = 2)
						left join nfslote l on(nf.idnotafiscal=l.idnotafiscal and l.status in ('PENDENTE','CONSULTANDO','SUCESSO'))
					WHERE
						p.idpessoa = nf.idpessoa
						".getidempresa('nf.idempresa','nfs')." 
						AND nf.idnotafiscal = " . $idnotafiscal;
				// die($sql);
				
				$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
				###############################################################################################Captura o Id do cliente caso seja update
				$row = mysqli_fetch_assoc($res);
				$_numerorps =  $row["numerorps"];
				$_nfstatus =  $row["status"];
				if($_acao=="u"){
					$idpessoa = $row["idpessoa"];
				}
				//verificar se ja foi faturada a nota
				if(!empty($_numerorps)){
					$sqlnfs = "select * from nfslote
						where status = 'SUCESSO'
						and  numerorps = '".$_numerorps."'";
					$rnfs = d::b()->query($sqlnfs) or die("Erro pesquisando lote da NFS: ".mysqli_error(d::b()));
					$qtdsuc = mysqli_num_rows($rnfs);
				}else{
					$qtdsuc =0;
				}	
				?>
				<script>
					<?if(!empty($row["nnfe"]) and $row["status"]=='CONCLUIDO'){?> 
						$("#cbModuloForm").find('input').not('[name*="namecert"],[name*="contapagaritem_valor"],[name*="notafiscal_idpessoa"],[name*="idnotafiscal"],[name*="dsimples"],[name*="nameemaildanfe"],[name*="emaildetalhe"],[name*="nameemailboleto"],[name*="_1_u_notafiscal_intervalo"], [name*="_modalnovaparcelacontapagar_tipo_"], [name*="valornovaparc"],[name*="vencnovaparc"] ').prop( "disabled", true );
						$("#cbModuloForm").find("select").not('[name*="_1_u_notafiscal_qtdparcelas"],[name*="_1_u_notafiscal_idformapagamento"],[name="formapagnovaparc"],[name*="_1_u_notafiscal_geracontapagar"]').prop( "disabled", true );
						$("#cbModuloForm").find("textarea").not('[name*="notafiscal_emailnfe"],[name*="notafiscal_motivoc"],[name*="_notafiscal_obs1"],[name*="nf_infcorrecao"]').prop( "disabled", true );
					<?}?>
				</script>
				<table class="table table-striped planilha">
					<tr>		
						<th  class="nowrap">N&ordm; N.F.</th>
						<th  class="nowrap">N&ordm; RPS</th>
						<th  class="nowrap">N&ordm; Det.</th>
						<th>Cliente</th>	
						<th>SubTotal</th>
						<th>IRRF</th>
						<th>PIS</th>
						<th>COFINS</th>
						<th>CSLL</th>
						<th>ISS</th>
						<th>Total</th>
						<th>Emissão</th>
						<th>Vencimento</th>
						<th>Status</th>
					</tr>
					<tr >		
						<td class="nowrap"><?
							if(empty($row["nnfe"]) and $row["status"]!='CONCLUIDO'){?>
								[Em Aberto]
							<?}else{
								$disabled = "disabled='disabled'";
								$readonly = "readonly='readonly'";
							?>
								<label class="idbox"><?=$row["nnfe"]?></label>
							<?}?>		
						</td>
						<td><?=$row["numerorps"]?></td>
						<td><?=$idnotafiscal?></td>
						<td class="nowrap">
							<? //Alterado para não alterar o nome do Fornecedor quando o status for CONCLUIDO - Lidiane (04-05-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315582?>
							<?if(($_acao=="u")&&($alteracliente!='n') && $row["status"]=="ABERTO"){?>
								<input id="idpessoa"  type="text" name="_1_<?=$_acao?>_notafiscal_idpessoa" vnulo cbvalue="<?=$idpessoa?>" value="<?=$arrCli[$idpessoa]["nome"]?>" style="width: 35em;" vnulo>
							<?}else{
								echo $row["nome"];
							?>
							<input id="idpessoa"  type="hidden" name="_1_<?=$_acao?>_notafiscal_idpessoa" vnulo cbvalue="<?=$idpessoa?>" value="<?=$arrCli[$idpessoa]["nome"]?>" style="width: 35em;" vnulo>
							<?	
							}
							?>
							<a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row["idpessoa"]?>')"></a>
						     &nbsp;  
							   <?$sqlb="select c.idcontrato,c.titulo,dma(c.vigencia) as vigencia,dma(c.vigenciafim) as vigenciafim 
                                from  contratopessoa cp join  contrato c on(c.idcontrato=cp.idcontrato and  c.status='ATIVO' and c.tipo='S') 
                                where cp.idpessoa=".$row["idpessoa"]." ";
                                $resb=d::b()->query($sqlb) or die("erro ao buscar contrato sql=".$sqlb);
                                $existecontrato=mysqli_num_rows($resb);
                                $rowb=mysqli_fetch_assoc($resb);
                                if(!empty($rowb['idcontrato']) and $existecontrato>0){              
                                ?>                
                                    <a  class="fa fa-wpforms pointer hoverazul" title="<?=$rowb['titulo']?> <?=$rowb['vigenciafim']?>"  onclick="janelamodal('?_modulo=contrato&_acao=u&idcontrato=<?=$rowb['idcontrato']?>')"></a>         
                                <?}else{?>
                                    <a  class="fa fa-plus-circle verde pointer hoverazul" title="Novo Contrato"  onclick="janelamodal('?_modulo=contrato&_acao=i')"></a>         
                                <?}?>
                           
						</td>
						<td><?=number_format(tratanumero($row["subtotal"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["irrf"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["pis"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["cofins"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["csll"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["iss"]),2,',','.'); ?></td>
						<td><?=number_format(tratanumero($row["total"]),2,',','.'); ?></td>
						<td><?=$row["emissao"]?></td>
						<td><?=$row["vencimento"]?></td>
						<td>
							<?/*if($row["status"]=="CANCELADO"){
								$strstatus=" select 'CANCELADO','Cancelado'  ";
							}elseif($row["status"]=="FATURADO"){
								$strstatus="select 'FATURADO','Faturado'  ";
							}elseif($row["status"]=="INICIO" ){
								$strstatus="select 'INICIO','Aberto' UNION select 'PENDENTE','Pendente'  ";
							}
							elseif($row["status"]=="PENDENTE"){
								$strstatus="select 'PENDENTE','Pendente' union select 'FECHADO','Fechado' union select 'CANCELADO','Cancelado'  ";
							}else{
								$strstatus="select 'FECHADO','Fechado' union select 'CANCELADO','Cancelado'  ";
							}*/
							
							if($_GET["_modulo"] == 'geranfs'){
								$strstatus="select 'ABERTO','Aberto' UNION select 'PENDENTE','Pendente'  ";
							} else {
								?>
								<span>                               	
									<? $rotulo = getStatusFluxo('notafiscal', 'idnotafiscal', $row["idnotafiscal"])?>                                              
                    				<label class="alert-warning" id="statusButton" title="<?=$row["status"]?>" id="statusButton"><?=$rotulo['rotulo']?> </label>
								</span>
								<?
							}
							
							?>
							
							<?//if($nrows<1){?>
							<input type="hidden" id="idnotafiscal" name="_1_u_notafiscal_idnotafiscal" value="<?=$row["idnotafiscal"]?>">
							<input type="hidden" name="_1_u_notafiscal_controle" value="<?=$row["controle"]?>">
							<input type="hidden" name="_1_u_notafiscal_nnfe" value="<?=$row["nnfe"]?>">
							<input type="hidden" name="_1_u_notafiscal_exercicio" value="<?=date("Y")?>">								
							<?//}?>
							<? if($_GET["_modulo"] == 'geranfs'){ ?>
								<select  name="_1_u_notafiscal_status" >
									<?fillselect($strstatus,$row["status"]);?>		
								</select> 
							<? } else {?>
								<input type="hidden" name="_1_u_notafiscal_status" value="<?=$row["status"]?>">    
							<? } ?>       
						</td>
						<?if($row["status"]!="CANCELADO" and $qtdsuc==0){?>
						<td align="center">
							<a class="fa fa-minus-square vermelho pointer hoverazul" title="Cancelar NFe"  onclick="cancelanota(<?=$idnotafiscal?>)" ></a>	
						</td>
						<?}?>
					</tr>
				</table> 				
				<?
				if(!empty($row["observacaonf"])){
					$arrobs = explode("<n>",str_replace("<n><n>","<n>",str_replace("\r","<n>",str_replace("\n","<n>",$row["observacaonf"]))));

				}//if(!empty($row["observacaonf"])){
				?>
			</div>
		</div>
    </div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default" >
			<div class="panel-heading">Dados Faturamento</div>
			<div class="panel-body ">
				<table>
					<tr>
						<td align="right"><b>Razão Social:</b></td>
						<td><?=traduzid("pessoa","idpessoa","razaosocial",$idpessoa)?></td>
					</tr>
					<tr>
						<td align="right"><b>CPF/CNPJ:</b></td>
						<td>
							<?
							$cnpj= traduzid("pessoa","idpessoa","cpfcnpj",$idpessoa);
							$cnpj=formatarCPF_CNPJ($cnpj,true); 
							$inscrest= traduzid("pessoa","idpessoa","inscrest",$idpessoa);
							?>
							<span  style="color: red;"><b><?=$cnpj?></b></span>
							<?if(!empty($inscrest)){?> / IE:<span  style="color: red;"><b><?}?><?if(!empty($inscrest)){?> <?=$inscrest?><?}?></b></span>
						</td>
					</tr>
					<tr>
						<td align="right" style="vertical-align: top;"><b>Endereço:</b></td>
						<td>
							<?
							$sqlf="select t.tipoendereco,c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf,e.obsentrega
									from nfscidadesiaf c,endereco e,tipoendereco t
									where c.codcidade = e.codcidade
									and t.idtipoendereco = e.idtipoendereco
									and e.idtipoendereco= 2
									and e.status = 'ATIVO'
									and e.idpessoa =".$idpessoa;
							$resf=d::b()->query($sqlf) or die("erro ao buscar informaçàµes do endereço sql=".$sqlf);
							while($rowf=mysqli_fetch_assoc($resf)) 
							{	
								$cep=formatarCEP($rowf["cep"],true);
									
								?>			    
								<li style="display:inline-block;">
									<div class="nowrap">End.: <?=$rowf["logradouro"]?> <?=$rowf["endereco"]?> N.: <?=$rowf["numero"]?> <?=$rowf["complemento"]?></div>				
									<div class="nowrap">Bairro: <?=$rowf["bairro"]?> CEP: <?=$cep?></div>				    
									<div class="nowrap">Cidade: <?=$rowf["cidade"]?> UF: <?=$rowf["uf"]?></div>
								</li>
							<?
							}
							?>
						</td>
					</tr>
					<tr>
						<td align="right" nowrap><b>Comissão:</b></td>
						<td>
							<select class='size5' <?=$disabled?> name="_1_u_notafiscal_comissao" vnulo>
								<?fillselect("select 'Y','Sim' union select 'N','Não'",$row['comissao']);?>
							</select>	
						</td>  
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default" >
			<div class="panel-heading">Observação</div>
			<div class="panel-body">
				<table>
					<tr>		
						<td><div style="word-break: break-word;">
							<font size="2" style="color: red;"><b><?=str_replace(chr(13),"<br>",$row["observacaonf"])?></b></font>
						</div></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?
//Marca o checkbox de mostrar abertos
if($row["mostraabertos"]=="Y"){
    $abertoscheck = "checked";
    $mostrabaertosstatus = ",'ABERTO','PROCESSANDO','CONFERIDO'";
}else{
    $abertoscheck = "";
    $mostrabaertosstatus = "";
}
if($row['mostramesmocnpj']=="Y"){
    $mostramesmocnpjcheck="checked";
	
    $sql = "SELECT r.idamostra AS idamostra,
					r.idresultado,
					p2.nome,
					a.exercicio AS exercicio,
					a.idregistro AS idregistro,
					a.idpessoa AS idpessoa,
					a.idunidade,
					DATE_FORMAT(a.dataamostra, '%d/%m/%Y') AS dataamostra,
					r.idresultado AS idresultado,
					r.idtipoteste AS idtipoteste,
					r.quantidade AS quantidade,
					r.status AS status,
					tt.tipoteste AS tipoteste,
					tt.sigla AS sigla,
					tt.tipogmt AS tipogmt,
					IF(ISNULL(tt.valor), 0, tt.valor) AS valor,
					CASE r.npedido WHEN 'null' THEN '' ELSE r.npedido END AS npedido,
					IF((a.idpessoaresponsavel IS NULL OR a.idpessoaresponsavel = ''), a.responsavel, pc.nome) AS solicitante,
					a.idpessoaresponsavel AS idsolicitante,
					a.idempresa as idempresaamostra
			  FROM pessoa p JOIN pessoa p2 ON p.cpfcnpj = p2.cpfcnpj
			  JOIN amostra a ON a.idpessoa = p2.idpessoa
			  JOIN unidade u ON u.idunidade = a.idunidade
			  JOIN resultado r ON r.idamostra = a.idamostra
			  JOIN vwtipoteste tt ON r.idtipoteste = tt.idtipoteste
		 LEFT JOIN pessoa pc ON pc.idpessoa = a.idpessoaresponsavel
			 WHERE r.status IN ('FECHADO' , 'ASSINADO' $mostrabaertosstatus)
			   AND ((u.idtipounidade = 1 AND r.cobrar = 'Y') OR (u.idtipounidade != 1 AND r.cobrancaobrig = 'Y'))
			   AND	p.idpessoa = '$idpessoa'
			   AND NOT EXISTS( SELECT * FROM notafiscalitens nfi WHERE nfi.idresultado = r.idresultado)
			 ORDER BY a.dataamostra, a.idregistro, tt.tipoteste;";
	
}else{
    $mostramesmocnpjcheck="";
    $sql = "SELECT r.idamostra AS idamostra,
					r.idresultado,
					p.nome,
					a.exercicio AS exercicio,
					a.idregistro AS idregistro,
					a.idunidade,
					a.idpessoa AS idpessoa,
					DATE_FORMAT(a.dataamostra, '%d/%m/%Y') AS dataamostra,
					r.idresultado AS idresultado, 
					r.idtipoteste AS idtipoteste,
					r.quantidade AS quantidade,
					r.status AS status,
					tt.tipoteste AS tipoteste,
					tt.sigla AS sigla,
					tt.tipogmt AS tipogmt,
					IF(ISNULL(tt.valor), 0, tt.valor) AS valor,
					CASE r.npedido WHEN 'null' THEN '' ELSE r.npedido END AS npedido,
					IF((a.idpessoaresponsavel IS NULL OR a.idpessoaresponsavel = ''), a.responsavel, pc.nome) AS solicitante,
					a.idpessoaresponsavel AS idsolicitante,
					a.idempresa as idempresaamostra
			   FROM pessoa p JOIN amostra a ON p.idpessoa = a.idpessoa
			   JOIN unidade u ON u.idunidade = a.idunidade
			   JOIN resultado r ON r.idamostra = a.idamostra
			   JOIN vwtipoteste tt ON r.idtipoteste = tt.idtipoteste
		  LEFT JOIN pessoa pc ON pc.idpessoa = a.idpessoaresponsavel
			  WHERE r.status IN ('FECHADO' , 'ASSINADO' $mostrabaertosstatus)
				AND ((u.idtipounidade = 1 AND r.cobrar = 'Y') OR (u.idtipounidade != 1 AND r.cobrancaobrig = 'Y'))
				AND a.idpessoa = '$idpessoa'
				AND NOT EXISTS( SELECT * FROM notafiscalitens nfi WHERE nfi.idresultado = r.idresultado)
		   ORDER BY a.dataamostra, a.idregistro, tt.tipoteste;";
}

?>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default" >
			<div class="panel-heading"> Resultados Fechados do Cliente </div>
			<div class="panel-body " id="inotafiscalitens">
				<?
				//echo $sql;
				$resi = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
				$itestes = mysqli_num_rows($resi);
				?>
				<input type="hidden" name="idpessoa" value="<?=$idpessoa?>">
				<input type="hidden" name="acaonf" value="incluiritem">
				<input type="hidden" name="idnotafiscal" value="<?=$idnotafiscal?>">
				<table class="normal">
					<tr>
						<td colspan="3" class="nowrap">
							<?if($row["mostraabertos"]=="Y"){?>
								<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altlistateste(<?=$idnotafiscal?>,'N','mostraabertos');" alt="Alterar para Não"></i>
							<?}else{?>	
								<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="altlistateste(<?=$idnotafiscal?>,'Y','mostraabertos');" alt="Alterar para Sim"></i>
							<?}?>	
							Mostrar Abertos.
						</td>
						<td colspan="4"  class="nowrap">
							<?if($row["mostramesmocnpj"]=="Y"){?>
								<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altlistateste(<?=$idnotafiscal?>,'N','mostramesmocnpj');" alt="Alterar para Não"></i>
							<?}else{?>	
								<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="altlistateste(<?=$idnotafiscal?>,'Y','mostramesmocnpj');" alt="Alterar para Sim"></i>
							<?}?>
							Mostrar do Mesmo CNPJ.
						</td>
						<td colspan="2"> &nbsp;&nbsp;&nbsp;&nbsp;Inserir todos os itens selecionados</td>
						<td class="nowrap" align="right">	
							<?if($row["status"]!="CONCLUIDO" and empty($row["idnfslote"])){?>
								<a class="fa fa-fast-forward fa-1x azul hoverazul pointer" title="Inserir todos os itens selecionados" onclick="iinotafiscalitens(this,'inotafiscalitens');"></a>
							<?}?>
						</td>
					</tr>
				</table>					
				<?
				$i = 99999;
				$strdata="";
				$tb=0;
				while ($rowi = mysqli_fetch_array($resi)) {
							
							$sqlpf="select pf.pedidocp 
									from pessoa p join
											preferencia pf on (pf.idpreferencia=p.idpreferencia)
									 where p.idpessoa =".$rowi['idpessoa']."  
									and pedidocp='Y'";
							$respf=d::b()->query($sqlpf) or die("Erro ao buscar preferencia de numero de compra sql=".$sqlpf);
							$qtdpf =mysqli_num_rows($respf);                    
							
					//Buscar contrato com descontos do cliente
					$sqldesc="select  
							d.tipodesconto
							,round(if(d.desconto IS NULL,0,d.desconto),2) as desconto
						from contratopessoa cp,contrato c,desconto d
						where cp.idpessoa = ".$rowi['idpessoa']."
						and  cp.idcontrato = c.idcontrato 
						and c.status = 'ATIVO'
						and d.idtipoteste = ".$rowi['idtipoteste']."
						AND d.idcontrato = c.idcontrato";
					$resdesc = d::b()->query($sqldesc) or die("Erro ao buscar contrato com descontos do cliente sql=".$sqldesc);
					$rowdesc=mysqli_fetch_assoc($resdesc);

					$i++;
					if($rowi["status"]=='INICIO' OR $rowi["status"]=='ABERTO' OR $rowi["status"]=='PROCESSANDO' or $rowi["status"]=='CONFERIDO' OR $rowi["status"]=='FECHADO'){
						$cort="#f0bfbf";
					}else{
						$cort="";
					}

					if($rowdesc["tipodesconto"]=='V' AND !empty($rowdesc["desconto"])){
						$desconto="0";
						$valoritem=$rowdesc["desconto"];
					}elseif($rowdesc["tipodesconto"]=='P' AND !empty($rowdesc["desconto"])){
						$desconto=$rowdesc["desconto"];
						$valoritem=$rowi["valor"];
					}else{
						$desconto="0";
						$valoritem=$rowi["valor"];
					}

					if(empty($strdata)){
						$strdata=$rowi["dataamostra"];
						$cabeca="Y";	
						$inicio=1;							
					}elseif($strdata==$rowi["dataamostra"]){
						$cabeca="N";
						$inicio=2;
					}else{
						$cabeca="Y";
						$inicio=2;
						$strdata=$rowi["dataamostra"];
					}
					if($inicio==2 and $cabeca=="Y"){							
						?>
						</table>	
						<?	
					}
					if($cabeca=="Y"){
					  $tb=$tb+1;	
						?>
						<table id="inotafiscalitens<?=$tb?>" class="table table-striped planilha">
							<?
							// se ja foi enviado nf para prefetura com sucesso não mostra o link para retirar item
							if($itestes > 0 and $qtdsuc==0 or $_nfstatus == "CANCELADO"){
							?>
								<tr>
									<td colspan="6" align="right"> Inserir iten(s) selecionados na NF</td>
									<td align="center">
										<a class="fa fa-forward fa-1x azul hoverazul pointer" title="Inserir Itens desta seleção" onclick="inotafiscalitens(this,'inotafiscalitens<?=$tb?>');"></a>
									</td>
								</tr>
							<?
							}
							?>
							<tr >
								<td align="center">
									<a class="fa fa-caret-down fa-2x azul hoverazul pointer selecttb" checkcontrol="<?=$tb?>" ref="#inotafiscalitens" title="Seleciona tudo / Inverte seleção"></a>
								</td>
								<td nowrap>Nº Reg</td>
								<td nowrap>Solicitante</td>
								<td nowrap>Nº Pedido</td>
								<td nowrap>Data</td>
								<td nowrap>Qt</td>
								<td nowrap>Teste</td>
								<td nowrap>Preço</td>
								<td nowrap>%Desc</td>
							</tr>
					<?
					}
					$moduloAmostra = getModuloAmostraPadrao($rowi["idunidade"]);
					?>
							<tr class="respreto" style="background-color:<?=$cort?>">
								<td>
									<!-- Envia para o banco a descricao do tipoteste -->
									<input type="checkbox"  checkcontrol="<?=$tb?>"  class="changeacao" acao="i" atname="checked[<?=$i?>]" checked style="border:0px">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_idnotafiscal" value="<?=$idnotafiscal?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_idamostra" value="<?=$rowi["idamostra"]?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_idresultado" value="<?=$rowi["idresultado"]?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_idprodserv" value="<?=$rowi["idtipoteste"]?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_valor" value="<?=$valoritem?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_desconto" value="<?=$desconto?>">
									<input type="hidden" name="_<?=$i?>_i_notafiscalitens_quantidade" value="<?=$rowi["quantidade"]?>">
								</td>
								<!-- Mostra somente a sigla do teste na tela -->
								<td nowrap>
									<a title="<?=$rowi['nome']?>" <a href="?_modulo=<?=$moduloAmostra?>&_acao=u&idamostra=<?=$rowi["idamostra"]?>&_idempresa=<?=$rowi["idempresaamostra"]?>" target="_blank"><?=$rowi["idregistro"]?></a>
								</td>
								<td nowrap><?=$rowi['solicitante']?></td>
								<td nowrap>
									<input style="background: <?=$cort?>;" class='size7' type="text" name="npedido" value="<?=$rowi["npedido"]?>" onchange="atualizanpedido(this,<?=$rowi['idresultado']?>)">                            
								</td>
								<td nowrap><?=$rowi["dataamostra"]?></td>
								<td nowrap><?=$rowi["quantidade"]?></td>
								<td nowrap><?=$rowi["sigla"]?></td>
								<td align="center" nowrap><?=$valoritem?></td>
								<td nowrap><?=$desconto?></td>
							</tr>						
				<?							
				}
				if($i>0){
				?>
					</table>
				<?}?>

			</div>
		</div>      
    </div>
	<div class="col-md-6">
		<?
		if(empty($idpessoa)){
			die("Id do Cliente não informado ao finalizar os calculos da Nota Fiscal");
		}

		$sql="SELECT 
			nf.idnotafiscal
			,nf.idpessoa
			,( select round( sum((i.valor - round((i.valor * (i.desconto / 100)),2)) * (i.quantidade)),2)
				from notafiscalitens i where i.idnotafiscal = nf.idnotafiscal )AS subtotal
			, nf.obs
			, nf.obs1
			,nf.informacao
			, nf.controle
			,nf.numerorps
			,nf.nnfe
			,nf.alteracaomanual
			, nf.total
			, nf.csll
			, nf.pis
			, nf.cofins
			, nf.irrf
			,nf.iss
			,nf.status
			, dma(nf.emissao) as emissao
			,nf.emissao as amdemissao
			, dma(nf.vencimento) as vencimento
			, p.cpfcnpj
			, e.CodCidade as codcidadeprest
			, e.aliqissativ
			, e.aliqpis
			, e.aliqcofins
			, e.aliqinss
			, e.aliqir
			, e.aliqcsll
			, en.codcidade as codcidadetom
			, nf.tributacao
			, nf.tiporecolhimento
			,nf.enviaemailnfe
			,nf.enviadanfnfe
			,nf.enviadetalhenfe
			,nf.logemailnfe
			,nf.emailnfe
			,nf.enviaemaildetalhe
			,nf.emaildetalhe
			,nf.alteradopor
			,nf.alteradoem
			,nf.criadoem
			,nf.criadopor
			,nf.motivoc
			,nf.npedido
			,p.flgprodrural
			,f.idagencia
			,nf.qtdparcelas
			,nf.formapgto
			,nf.diasentrada
			,nf.intervalo
			,nf.comissao
			,pf.obsvenda
			,pf.decsimplesn	
			,nf.emaildsimplesnac
			,nf.total
			,nf.emailboleto
			   ,nf.idformapagamento
			,f.formapagamento
			,nf.geracontapagar
			FROM 
				notafiscal nf
				join empresa e	    
				join endereco en
					join pessoa p left join preferencia pf on(pf.idpreferencia=p.idpreferencia and pf.idempresa=nf.idempresa)
					left join formapagamento f on(f.idformapagamento = nf.idformapagamento)
			WHERE  p.idpessoa = nf.idpessoa
				and nf.idempresa = e.idempresa
				and p.idpessoa = en.idpessoa
				and en.idtipoendereco = 2 /* SAC */
				and nf.idnotafiscal =".$idnotafiscal;

		$ress = d::b()->query($sql);

		if(!$ress){
			echo "Erro ao recuperar a soma total da Nota : <br>\n SQL: ". $sql . " <br>\n Erro: " . mysqli_error(d::b());
			die;
		}
		$infitens = mysqli_num_rows($ress);
		$rows = mysqli_fetch_assoc($ress);
		if($infitens > 0){
			//buscar e copiar as preferencias
			$sqlpref="select		
				pf.parcelavenda as qtdparcelas
				,pf.idformapagamento
				,pf.prazopagtovenda as diasentrada
						,f.formapagamento
				,pf.intervalovenda as intervalo
				from pessoa p left join preferencia pf on (pf.idpreferencia = p.idpreferencia)
						left join formapagamento f on(pf.idformapagamento=f.idformapagamento)
						where p.idpessoa=".$rows["idpessoa"];
			$respref=d::b()->query($sqlpref) or die("Erro ao buscar preferencias sql=".$sqlpref);
			$rowpref=mysqli_fetch_assoc($respref);

			if(!empty($rowpref['idformapagamento']) and empty($rows['idformapagamento'])){
			$rows['idformapagamento']=$rowpref['idformapagamento'];
			}
			if(!empty($rowpref['qtdparcelas']) and empty($rows['qtdparcelas'])){
			$rows['qtdparcelas']=$rowpref['qtdparcelas'];
			}
			
			 if(!empty($rowpref['formapagamento']) and empty($rows['formapagamento'])){
			$rows['formapagamento']=$rowpref['formapagamento'];
			}
			/*
			if(!empty($rowpref['formapgto']) and empty($rows['formapgto'])){
			$rows['formapgto']=$rowpref['formapgto'];
			}
			 * 
			 */
			if(!empty($rowpref['diasentrada']) and empty($rows['diasentrada'])){
			$rows['diasentrada']=$rowpref['diasentrada'];
			}
			if(!empty($rowpref['intervalo']) and empty($rows['intervalo'])){
			$rows['intervalo']=$rowpref['intervalo'];
			}
		}else{
			?>   
		   
			<div class="panel panel-default">       
				<div class="panel-body ">
					<label class="alert-warning"> Dados do cliente incompletos e/ou incorretos para Faturamento!!!</label>
				</div>
			</div>   
		<?
		}

		if(($infitens > 0)&&($rows["subtotal"] >= 0)){
			/*
			 ******************************************* CALCULO RODAPE DA NF ******************************************* 
			 */
			$aliqissativ = $rows["aliqissativ"];
			$aliqpis = $rows["aliqpis"];
			$aliqcofins = $rows["aliqcofins"];
			$aliqinss = $rows["aliqinss"];
			$aliqir = $rows["aliqir"];
			$aliqcsll = $rows["aliqcsll"];
			$idoc = strlen($row["cpfcnpj"]);//armazena a quant de caracteres do documento para saber se eh PJ ou PF
			/*
			 * IRRF: Cobrar IRRF somente se o valor for maior que o indice e se for Pessoa Juridica ou não for produtor rural
			 */
			if(($rows["subtotal"] > 666.66) and ($idoc==14) and ($rows["flgprodrural"]=='N')){
				$irrf = round(($rows["subtotal"] * ($aliqir/100)),2);
			}else{										
				$irrf = 0.00;										
			}

			//If($rows["subtotal"] >= 5000){
			//  if(empty($rows["total"])){//retirado hermesp 13-10-2016
			/*
			 * PIS: sempre calcula
			 */
			$pis = round(($rows["subtotal"] * ($aliqpis/100)),2);
			/*
			 * COFINS: sempre calcula
			 */
			$cofins = round(($rows["subtotal"] * ($aliqcofins/100)),2); 
			/*
			 * CSLL: deus sabe
			 */
			$csll = round(($rows["subtotal"] * ($aliqcsll/100)),2);
			//}
			/*
			 *maf190811: A pedido de fabio, os valores serao sempre calculados, mostrando um alerta quando estiverem diferentes
			 */
			$piscomp = round(($rows["subtotal"] * ($aliqpis/100)),2);
			$cofinscomp = round(($rows["subtotal"] * ($aliqcofins/100)),2); 
			$csllcomp = round(($rows["subtotal"] * ($aliqcsll/100)),2);
				
			if(($pis+$cofins+$csll) < 10 or $rows["flgprodrural"]=='S' or $idoc==11){// a pedido do wilker se a soma do pis cofins e csll não passar de 10 reais zerar todos os valores 06/07/2015
				$pis='0.00';
				$cofins='0.00';
				$csll='0.00';
				$piscomp='0.00';
				$cofinscomp='0.00';
				$csllcomp='0.00';
			}
			//}
			/*
			 * ISS: cobrar somente se a cidade do tomador for a mesma do prestador
			 */
			//$iss = 0.00;
			if($rows["codcidadeprest"]==$rows["codcidadetom"] and $rows["tiporecolhimento"]=="R"){	
				$iss = round(truncarNumero($iss + ($rows["subtotal"] * ($aliqissativ/100))),2);
				//tipo recolhimento R retido na fonte
				$_tiporectmp = "R";//armazena para realizar selecao automatica na drop

			}elseif($rows["codcidadeprest"]!= $rows["codcidadetom"] or $rows["tiporecolhimento"]=="A"){	
				//tipo recolhimento A A Receber	
				$_tiporectmp = "A";//armazena para realizar selecao automatica na drop
				$iss = 0.00;
			}


			//variavel decide se vai ser tributado ou nao se a cidade do tomador for = cidade prestador
			if(!empty($rows["tiporecolhimento"])){
				$_tiporecolhimento = $rows["tiporecolhimento"];
			}else{
				$_tiporecolhimento = $_tiporectmp;
			}
			/*
			 * TOTAL PIS+COFINS+CSLL
			 */
			$tpcc = round(($pis + $cofins + $csll + $iss),2);
			//echo $row["csll"]; die;		
			/*
			******************************************* FIM CALCULO RODAPE DA NF ******************************************* 
			*/
			//somar data
			function SomarData($data, $dias, $meses = 0, $ano = 0)
			{
			   //passe a data no formato yyyy-mm-dd
			   $data = explode("-", $data);
			   $newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses, $data[2] + $dias, $data[0] + $ano) );
			   return $newData;
			}	
			?>
			<div class="row">			
				<div class="col-md-6" style="left: 0.5%;margin-right: -1%;">
					<div class="panel panel-default">
						<div class="panel-heading">Detalhamento Nota Fiscal</div>
						<div class="panel-body">
							<table style="width:100%">
								<tr>
									<td  align="center">PDF:&nbsp;
										<a class="fa fa-file-pdf-o vermelho pointer" title="Detalhamento NF PDF" onclick="janelamodal('report/reldetalhenf.php?idnotafiscal=<?=$rows['idnotafiscal']?>&geraarquivo=Y<?=$urlIdempresa?>')"></a>
									</td>
									<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	</td>
									<td  align="center">HTML:&nbsp;<a class="fa fa-file-text pointer" title="Detalhamento NF" onclick="janelamodal('report/reldetalhenf.php?idnotafiscal=<?=$rows['idnotafiscal']?>')"></a></td>
								</tr>
								<tr>
									<td>Remetente:</td>
									<td colspan="5">
										<table>
											<?
											$sqlempresaemail2 = "SELECT * FROM empresaemails WHERE tipoenvio = 'DETALHAMENTO' ".getidempresa('idempresa','empresa');
											$resempresaemail2=d::b()->query($sqlempresaemail2) or die("Erro ao buscar empresaemails sql=".$sqlempresaemail2);
											$qtdempresaemail2=mysqli_num_rows($resempresaemail2);
											if($qtdempresaemail2 == 1){
												$nemails2 = 1;
											}else{
												if($qtdempresaemail2 > 1){
													$nemails2 = 2;
												}else{
													$nemails2 = 0;
												}
											}

											$sqlemailobj2 = "SELECT * FROM empresaemailobjeto WHERE tipoenvio = 'DETALHAMENTO' and tipoobjeto = 'nfs' and idobjeto =".$idnotafiscal." ".getidempresa('idempresa','pessoa')." order by idempresaemailobjeto desc limit 1";
											$resemailobj2=d::b()->query($sqlemailobj2) or die("Erro ao buscar empresaemailobjeto sql=".$sqlemailobj2);
											$rowemailobj2=mysqli_fetch_assoc($resemailobj2);
											$qtdemailobj2=mysqli_num_rows($resemailobj2);

											if($qtdemailobj2 < 1){
												$setemail2 = 1;
											}else{
												$setemail2 = 0;
											}
											if($nemails2 == 1){?>
												<tr>
													<td>
														<?
														$sqldominio2 = "SELECT em.idemailvirtualconf,
																			   em.idempresa,ev.email_original as dominio 
																		FROM empresaemails em
																		JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf) 
																		WHERE em.tipoenvio = 'DETALHAMENTO'
																		AND ev.status = 'ATIVO'".getidempresa('em.idempresa','pessoa');

														$resdominio2=d::b()->query($sqldominio2) or die("Erro ao buscar emails da empresa sql=".$sqldominio2);
														$rowdominio2=mysqli_fetch_assoc($resdominio2)?>

														<input id="emailunico2" type="hidden" value="<?=$rowdominio2["idemailvirtualconf"]?>">
														<input id="idempresaemail2" type="hidden" value="<?=$rowdominio2["idempresa"]?>">
														<label class="alert-warning"><?=$rowdominio2["dominio"]?></label>
													</td>
												</tr>
											<? 
											}else{
												if($nemails2 > 1){
													$sqldominio2 = "SELECT em.idemailvirtualconf,
																		   ev.email_original AS dominio,
																		   em.idempresa 
																	FROM empresaemails em
																	JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf) 
																	WHERE em.tipoenvio = 'DETALHAMENTO' 
																	AND ev.status = 'ATIVO' ".getidempresa('em.idempresa','pessoa');

													$resdominio2=d::b()->query($sqldominio2) or die("Erro ao buscar emails da empresa sql=".$sqldominio2);
													$qtddominio2=mysqli_num_rows($resdominio2);
													if($qtddominio2>0){
														while($rowdominio2=mysqli_fetch_assoc($resdominio2)){
															if($rowdominio2["idemailvirtualconf"] == $rowemailobj2["idemailvirtualconf"]){
																$chk2 = 'checked';
															}else{
																$chk2 = '';
															}?>
															<tr>
																<td>
																	<input class="emailorcamento" title="Email Remetente" type="radio" <?=$chk2?> onclick="altremetenteemail(<?=$idnotafiscal?>,<?=$rowdominio2["idemailvirtualconf"]?>,'DETALHAMENTO',<?=$rowdominio2["idempresa"]?>)">
																	<label class="alert-warning" ><?=$rowdominio2["dominio"]?> </label>
																</td>
															</tr>
														<?
														}
													}
												}
											}
											?>
										</table>
									</td>
								</tr>
								<tr>
									<?if(empty($rows['emaildetalhe'])){
										//$rows['emaildetalhe']='vendas@laudolab.com.br';
										//$rows['emaildetalhe'].=(",".traduzid("pessoa","idpessoa","email",$rows['idpessoa']));
										$rows['emaildetalhe'].=(traduzid("pessoa","idpessoa","email",$rows['idpessoa']));
										//concatenar emailcopia
										$sqlcc = "SELECT p.email as emailcopia
										from pessoacontato c join pessoa p on (c.idcontato = p.idpessoa)
										where c.idpessoa = ".$rows['idpessoa']."
										and p.status='ATIVO'
										and c.emailnfsecc = 'Y'
										and p.idtipopessoa not in (12,1) ".getidempresa('p.idempresa','nfs');

										//$sqlcc="select emailcopia from pessoa where idpessoa=".$rows['idpessoa'];
										$rescc=d::b()->query($sqlcc) or die("erro ao buscar email de copia sql=".$sqlcc);
										if(empty($rows['emaildetalhe'])){
											$virg = "";
										}else{
											$virg = ",";
										}
										if(mysqli_num_rows($rescc) > 0){
											while($rowcc=mysqli_fetch_assoc($rescc)){
												if(!empty($rowcc['emailcopia'])){
													$rows['emaildetalhe'].=$virg.$rowcc['emailcopia'];
													$virg = ",";
												}
											}
										}					
										
									}?>
									<td colspan="6"><textarea  style="height: 60px; font-size:12px; font-weight: bold;"  name="_1_u_notafiscal_emaildetalhe" onchange="this.form.submit()"><?=$rows['emaildetalhe']?></textarea>
										<?
										$existepv0 = strpos($rows['emaildetalhe'], ";");
										if ($existepv0 === false) {
											null;
										}else{
											echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";			
										}
										?>
									</td>
								</tr>
								<tr>
								<?
									if($rows["status"]=="FECHADO"   or $rows["status"]=="PENDENTE"){ //INCLUIDO STATUS INICIO A PEDIDO DO WILKER 23052016?>
									
										<?  					
										if($rows['enviaemaildetalhe']=='Y' or $rows['enviaemaildetalhe']=='A'){
											$classtdemail="amarelo";	
											$emailval='N';			    
										}elseif($rows['enviaemaildetalhe']=='O'){
											$classtdemail="verde";	
											$emailval='N';			    
										}elseif($rows['enviaemaildetalhe']=='E'){
											$classtdemail="vermelho";
											$emailval='N';			    
										}else{
											$classtdemail="cinza";
											$emailval='Y';				    
										}
										?>
										<td class="<?=$classtdemail?>" align="left" nowrap>
											<input id="setemail2" type="hidden" value="<?=$setemail2?>">
											<a class="fa fa-envelope pointer <?=$classtdemail?>" title="Enviar email Detalhamento" onclick="envioemaild(<?=$idnotafiscal?>,'<?=$emailval?>',<?=$nemails2?>);"></a>&nbsp;Enviar Email
											<?echo consultaLogsSmtp('detalhamento',$idnotafiscal,"table");?>
										</td>
										<?
											}
										?>
										<?
										   $sqlemail = "SELECT 
														  m.idmailfila
													   FROM
														  mailfila m
													   WHERE
														  m.tipoobjeto = 'detalhamento'
															 AND m.idobjeto = ".$row["idnotafiscal"]."
															 ".getidempresa('m.idempresa','envioemail')."
													   ORDER BY
														  idmailfila DESC LIMIT 1";
										   $resemail = d::b()->query($sqlemail) or die("Falha na consulta do email: " . mysql_error() . "<p>SQL: ".$sqlemail);
										   $rowemail = mysqli_fetch_assoc($resemail);   
										   $numemail = mysqli_num_rows($resemail);
										   if($numemail > 0){?>
												<td class="<?=$classtdemail?>" align="left" nowrap>
													<a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?=$rowemail['idmailfila']?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
												</td>
										   <?}?>
										

										<?		    
										if(!empty($idnotafiscal)){
											$sqleo="select * from  log 
													 where idobjeto = ".$idnotafiscal."
													   and tipoobjeto = 'notafiscal' 
													   and tipolog = 'EMAILDETALHAMENTO' order by criadoem";
											$reseo=d::b()->query($sqleo) or die("Erro ao buscar emails da nf sql=".$sqleo);
											$qtdeo= mysqli_num_rows($reseo);
											if($qtdeo>0){
												?>
												<div class="oEmailorc">
													<a class="fa fa-search azul pointer hoverazul" title=" Ver Log Email" data-target="webuiPopover0" ></a>
												</div>
												<div class="webui-popover-content">
													<?
													while($roweo= mysqli_fetch_assoc($reseo)){
														?>
														<li><?=$roweo["log"]?> <?=$roweo["status"]?> <?=dmahms($roweo["criadoem"])?></li>
														<?
													}//while($roweo= mysqli_fetch_assoc($reseo)){
													?>
												</div>
											<?
											}//if($qtdeo>0){
										}// if(!empty($idnotafiscal)){
										?>
									</tr>
								
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="panel-heading">Obs. Interna</div> 
						<div class="panel-body">  
							<div class="col-md-12">
								<textarea style="height: 105px; font-size:12px; font-weight: bold;width: 255px;"  name="_1_u_notafiscal_obs1"><?=$rows["obs1"]?></textarea>
							</div>
						</div>
					</div>	
				</div>
				
				<div class="col-md-12" style="width: 99%;">
					<?
					$selorc = "select idorcamento,controle,dma(dataorc) as dmadataorc,total,dataorc
								 from orcamento 
								where idpessoa=".$rows['idpessoa']." 
								  and status ='ABERTO' order by dataorc desc";

					$resorc = d::b()->query($selorc);
					//echo($selorc);
					if(!$resorc){
						echo "Erro ao buscar orçamentos do cliente: <br>\n SQL: ". $selorc . " <br>\n Erro: " . mysqli_error(d::b());
						die;
					}
					$nroorc= mysqli_num_rows($resorc);
					if ($nroorc > 0){?>
						<div class="panel panel-default" >
							<div class="panel-heading">Orçamento do cliente</div>
							<div class="panel-body">
								<table class="table table-striped planilha ">
									<tr >
										<th>Nº Orç.</th>
										<th>Data</th>
										<th style="text-align:center;">Valor</th>
										<th></th>
									</tr>
									<?
									$index = 1;
									while ($campoorc = mysqli_fetch_array($resorc)) {
										?>
										<tr>
											<td><?=$campoorc["controle"]?></td>
											<td><?=$campoorc["dmadataorc"]?></td>
											<td style="text-align:right; padding-right: 98px;"><?=number_format(tratanumero($campoorc["total"]),2,',','.'); ?></td>
											<td>
												<a class="fa fa-bars pointer hoverazul" title="Orçamentos do Cliente" onclick="janelamodal('?_modulo=orcamento&_acao=u&idorcamento=<?=$campoorc["idorcamento"]?>')"></a>
											</td>
										</tr>
										<? 					
									}
									?>												
								</table>
							</div>
						</div>			
					<?
					}
					?>
				</div>
			</div>			
		<?
		}
		?>
		<div class="row">
			<div class="col-md-12"  style="left: 0.5%; width: 98.5%; ">
				<div class="panel panel-default" >
					<div class="panel-heading">Prévia dos Itens da Nota Fiscal</div>
					<div class="panel-body ">
						<input type="hidden" name="acaonf" value="retiraritem">
						<!-- <div>
						<font class="numnfpreto">N&ordm; Nota Fiscal:</font>
						<font class="numnfverm"><?=$idnotafiscal?></font>
						</div> -->
						<table  id="dnotafiscalitens" class="table table-striped planilha">
							<?
							//$sql = "select * from vwnfitens where idnotafiscal = " . $idnotafiscal . " ORDER BY complemento, descricao";
							$sqlc = "SELECT i.idempresa,
											i.idnotafiscal,
											SUM(i.quantidade) AS quantidade,
											i.descricao,
											i.idresultado,
											ROUND(i.valor, 2) AS valorunitario,
											ROUND(SUM((i.valor * i.quantidade)), 2) AS subtotal,
											i.desconto,
											ROUND(((i.valor - ROUND((i.valor * (i.desconto / 100)), 2)) * SUM(i.quantidade)), 2) AS total,
											ISNULL(MAX(i.idresultado)) AS complemento,
											IF(r.status = 'ASSINADO', 2, 1) AS defcor,
											p.comissionado,
											p.idprodserv,
											l.idnfslote,
											GROUP_CONCAT(i.idnotafiscalitens SEPARATOR ',') AS stridnotafiscalitens,
											IF((a.idpessoaresponsavel IS NULL OR a.idpessoaresponsavel = ''), a.responsavel, pc.nome) AS solicitante,
											a.idpessoaresponsavel AS idsolicitante,
											a.idempresa as idempresaamostra
										FROM notafiscalitens i LEFT JOIN resultado r ON (r.idresultado = i.idresultado)
								   LEFT JOIN prodserv p ON (p.idprodserv = r.idtipoteste)
								   LEFT JOIN nfslote l ON (l.idnotafiscal = i.idnotafiscal AND l.status IN ('PENDENTE', 'CONSULTANDO'))
								   LEFT JOIN amostra a ON a.idamostra = r.idamostra
								   LEFT JOIN pessoa pc ON pc.idpessoa = a.idpessoaresponsavel
										WHERE i.idnotafiscal = '$idnotafiscal'
										GROUP BY i.idnotafiscal, i.descricao, i.valor, i.desconto
										ORDER BY complemento, i.descricao, defcor";
							//echo $sql;

							$resc = d::b()->query($sqlc) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlc");
							$iitens = mysqli_num_rows($resc);					

							//se ja foi enviado nf para prefetura com sucesso não mostra o link para retirar item
							if($iitens > 0 and $qtdsuc==0 or $_nfstatus == "CANCELADO"){
								?>
								<tr>
									<td align="center">
										<?if($row["status"]!="CONCLUIDO" AND empty($row["idnfslote"])){?>
											<a class="fa fa-backward fa-1x azul hoverazul pointer" title="Retirar iten(s) selecionados na NF" onclick="iinotafiscalitens(this,'dnotafiscalitens');"></a>
										<?}?>
									</td>
									<td colspan="8" align="left"> Retirar iten(s) selecionados na NF</td>
								</tr>
							<?
							}
							?>
							<tr class="header">
								<th >							    
									<a class="fa fa-caret-down fa-2x azul hoverazul pointer inverteselecao" ref="#dnotafiscalitens" title="Seleciona tudo / Inverte seleção"></a>
								</th>
								<th style="width:5%; text-align:center;">Qtd</th>
								<th class="size30">Descrição</th>
								<th class="size30">Solicitante</th>
								<th style="width:10%; text-align:center;">Valor UN</th>
								<th style="width:10%; text-align:center;">SubTotal</th>
								<th style="width:10%; text-align:center;">%Desc</th>
								<th style="width:10%; text-align:center;">Total</th>					
								<? if( $rows['comissao'] == 'Y') {?>
									<th style="width:10%; text-align:center;">Comissão</th>
								<?}?>
								
							</tr>
							<?						
							while ($rowc = mysqli_fetch_array($resc)) {
								$i++;
								if($rowc['defcor'] == 1 && !empty($rowc['idresultado'])){
									$cordef="#f0bfbf";
								}else{
									$cordef="";
								}								
								?>
								<tr class="respreto" style="background-color: <?=$cordef?>">
									<td>										
										<input type="checkbox" class="changeacao" acao="d" atname="checked[<?=$i?>]" style="border:0px">
										<input type="hidden" name="_<?=$i?>_descricao" value="<?=$rowc["descricao"]?>">
										<input type="hidden" name="_<?=$i?>_idnotafiscal" value="<?=$rowc["idnotafiscal"]?>">
									</td>
									<td  style="text-align:center;"><?=$rowc["quantidade"]?></td>
									<td>
										<?if(!empty($rowc["idprodserv"])){?>
											<a title="Cadastro do serviço" href="?_modulo=prodserv&_acao=u&idprodserv=<?=$rowc["idprodserv"]?>" target="_blank">
												<?=$rowc["descricao"]?>
											</a>
										<?}else{?>
											<?=$rowc["descricao"]?>
										<?}?>
									</td>
									<td  style="text-align:center;"><?=$rowc["solicitante"]?></td>
									<td style="text-align:right;"><?=number_format($rowc["valorunitario"], 2, ',', '.'); ?></td>
									<td style="text-align:right;"><?=number_format($rowc["subtotal"], 2, ',', '.'); ?></td>
									<td style="text-align:right;"><?=number_format($rowc["desconto"], 2, ',', '.'); ?></td>
									<td style="text-align:right;"><?=number_format($rowc["total"], 2, ',', '.'); ?></td>									
									<? if($rows['comissao'] == 'Y'){ ?>
										<td class="nowrap">
											<?$alertcom="";
											$fig="";
											if($rowc['comissionado'] == 'Y' || empty($rowc['idresultado'])){
												$arrnfitem=explode(',',$rowc['stridnotafiscalitens']);
												foreach ($arrnfitem as $idnotafiscalitem) {											
													$sqlco="select c.* 
																from notafiscalitenscomissao c 
																where pcomissao > 0 and c.idnotafiscalitens =".$idnotafiscalitem;
													$resco = d::b()->query($sqlco) or die("A Consulta se tem comissão falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlco");
													$qtdcom=mysqli_num_rows($resco);
													if($qtdcom<1){
															$alertcom="vermelho";
															$fig="<i title='Verificar Comissão' class='fa fa-exclamation-triangle laranja pointer'></i>";
													}
												}	
												?>
												
												<button type="button" class="btn btn-link btn-xs <?=$alertcom?>" 
													onclick="abrecomissao('<?=$rowc['stridnotafiscalitens']?>')">Comissão
												</button> <?=$fig?>	
											
											<?}?>									
										</td>	
									<?}?>								
								</tr>
							<?
							}
							?>
							
							
							<tr class="respreto  cadastrado">
								<td></td>
								<td>
									<input style=" border: 1px solid silver;" id="_9_quantidade" name="_9_quantidade" title="QTD" placeholder="QTD" type="text" class="size6" onchange="liberaservico()" onkeyup="liberaservico()">
								</td>
								<td nowrap>
									<input type="text" style="width: 25em;" id="insidprodserv" name="_9#idprodserv" class="idprodserv hidden" cbvalue="" placeholder="Selecione o Serviço"> 
								</td>
								<td>
									
								</td>
								<td >
									
								</td>
								<td  colspan="4">
									<button name="#botaodescritivo" type="button" class="btn btn-default btn-xs" onclick="inovoitem()" title="Mudar a inserção para item descritivo">
										<i class="fa fa-plus"></i>Descritivo
									</button>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div id="novoitem" style="display: none"> 
			<div class="row">
				<div class="col-md-12">
				<div class="panel panel-default" style="margin-top: 0px !important;">
					<div class="panel-heading">
					<table>
						<tr class="respreto  descritivo">							
							<td colspan="3">										
								<input placeholder="Categoria" name="_grupoes_x" class="size20" cbvalue="" value="" type="text" id="grupoesx">
								&nbsp;&nbsp;&nbsp;
								<select id="autoidtipoitemx" class="size20" name="" >
									<option value="" disabled selected hidden >Tipo</option>								
								</select>
							</td>							
						</tr>
						<tr class="respreto  descritivo2">
							
							<td>
								<input class="size3" type="text" placeholder="QTD" name="compquant" id="compquantx" style="font-size:12px;">
							</td>
							<td nowrap>
								<input  class="size35" type="text" placeholder="Descrição" name="complemento" id="prompt_complementox" style="font-size:12px;" >
							</td>
							<td>
								<input class="size5" type="text" placeholder="Valor" name="valor" id="prompt_valorx"  style="font-size:12px;" vdecimal>
							</td>											
						</tr>
						</table>
						</div>
				</div>
				</div>
			</div>
			</div>
			</div>


			<? if(($infitens > 0)&&($rows["subtotal"] >= 0)){ ?>
				<div class="row">
				<div class="col-md-12"  style="left: 0.5%; width: 98.5%; ">
					<div class="panel panel-default" >
						<div class="panel-heading">Cálculo dos Itens da Nota Fiscal</div>
						<div class="panel-body ">				
							<table class="normal" style="padding:0;">
								<tr class="respreto">
									<td style="padding-right:20px;">Emissão:<br>
										<?	
										if(empty($rows["emissao"])){
											$emissao = date("d/m/Y");
										}else{
											$emissao = $rows["emissao"];
										}
										if(empty($rows["intervalo"])){
											$rows["intervalo"]= 28;
										}								
										if(empty($rows["diasentrada"])){
											$rows["diasentrada"] = 28;
										}

										if(empty($rows['idformapagamento'])){
											$rows["formapagamento"] = 'BOLETO';
										}	

										if(empty($rows["emissao"])){	
											$diasentrada=$rows["diasentrada"];								
											$d=strtotime("+".$diasentrada." days");
											$vencimento= date("d/m/Y", $d);									
										}else{
											/*
											$data_inicial = '2009-01-01';
											$nova_data = SomarData($data_inicial,7);
											*/
											$data_inicial = $rows["amdemissao"];
											$vencimento = SomarData($data_inicial,$rows["diasentrada"]);
										}

										if($rows["formapagamento"] == 'DEPOSITO'){
											$strfmpgto="TRANSFERÊNCIA BANCÁRIA";

											if(!empty($rows["idagencia"])){
												$stragencia="BANCO: ".traduzid("agencia","idagencia","agencia",$rows["idagencia"])." 
												AG.: ".traduzid("agencia","idagencia","nagencia",$rows["idagencia"])." - C.C.: ".traduzid("agencia","idagencia","nconta",$rows["idagencia"])."
												";
											}

										}else{
											$strfmpgto="BOLETO BANCÁRIO";
											$stragencia="";
										}

										if(!empty($rows['npedido'])){
											$strpedido="PEDIDO: ".$rows['npedido']."

											";
										}else{
											$strpedido=" ";
										}
										
										$strvenc="DATA DE VENCIMENTO: ".$vencimento."";
										$sqlcx="select dma(c.datareceb) as dmadatareceb,c.* from nfsconfpagar c where c.idnotafiscal=".$rows["idnotafiscal"];
										$rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
										$qtdconf=mysqli_num_rows($rescx);
										$br='';
										if($qtdconf>0){
											$strvenc='';										
											while($rowcx=mysqli_fetch_assoc($rescx)){
												$strvenc.="DATA DE VENCIMENTO: ".$rowcx['dmadatareceb']."".$br;
												$br="
												";
											}
										}

										if( $rows["geracontapagar"] !='N' and empty($rows["informacao"]) ){																					//if(empty($rows["obs"])){
												$rows["informacao"] =$strpedido." VALOR (C/DEDUÇÕES): R$ ".number_format(tratanumero(round(($rows["subtotal"] - ($irrf + $tpcc)),2)), 2, ',', '.')." PRAZO PGTO: ".$rows["diasentrada"]." DD ".$strvenc." FORMA DE PAGAMENTO: ".$strfmpgto." ".$stragencia;
											//}
										}

										?>
										<input name="dataatual" id="dataatual" type="hidden" value="<?=date("d/m/Y");?>">
										<input name="_1_u_notafiscal_emissao"  class="calendario size8"  id ="fdata" type="text" size ="8" value="<?=$emissao?>"  onchange="CB.post()" vnulo>
									</td>
									<td>
										1&ordm; Vencimento:<br>
										<input  <?=$readonly?> class="size8" type = "text" name = "_1_u_notafiscal_diasentrada" size=1 value="<?=$rows["diasentrada"]?>">&nbsp;dias.
									</td>
									<td>
										QTD Parcelas:<br>
										<select <? if($row["status"]!='CONCLUIDO'){echo $disabled;} ?> class="size6" name="_1_u_notafiscal_qtdparcelas" onchange="liberaAtualizaParcela(this);">
											<?fillselect("select 1,'1x' union select 2,'2x' union select 3,'3x' union select 4,'4x' union select 5,'5x'",$rows["qtdparcelas"]);?>
										</select>
										<?
											$sqlct="select * from contapagar c where c.idobjeto = ".$row["idnotafiscal"]." and c.tipoobjeto='notafiscal' and status='QUITADO'";
											$resct = d::b()->query($sqlct) or die("Falha ao verificar fatura quitada:\n".mysqli_error(d::b())."\n".$sqlct);
											$qtdct = mysqli_num_rows($resct);
											if($qtdct>0){
												$displaycp="display:none;";
											}elseif($row["status"]=='CONCLUIDO'){
												$displaycp='';
											}else{
												$displaycp="display:none;";
											}

										?>

										<div name="libera" id="libera" style="<?=$displaycp?> float: right; padding: 6px;">
											<a class="fa fa-refresh vermelho pointer azul" title="Atualizar Parcelas." onclick="atualizarParcela();"></a>
										</div>
									</td>
									<td>
										Intervalo: <br>
										<input <? if($row["status"]!='CONCLUIDO'){echo $disabled;} ?> class="size8" name="_1_u_notafiscal_intervalo" type="text" value="<?=$rows["intervalo"]?>"> dia(s).
										<!--
										<br>Agência:<br>
										<select <?=$disabled?> class="size15" name="_1_u_notafiscal_idagencia"  id="idagencia" vnulo>
											<?fillselect("select idagencia,agencia from agencia where status = 'ATIVO'   order by ord",$rows["idagencia"]);?>
										</select>
										-->
									</td>
								</tr>
								<tr>
									<td>
										Pagamento:<br>
										<?if(empty($rows["idformapagamento"])){?>
											<select class="size15" <? if($row["status"]!='CONCLUIDO'){echo $disabled;} ?> name="_1_u_notafiscal_idformapagamento" vnulo>
												<option></option>
												<?fillselect("SELECT idformapagamento,descricao 
																from formapagamento f
																where status='ATIVO'
																".getidempresa('idempresa','formapagamento')."
																AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (".getModsUsr('LPS').") AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'formapagamento' and ov.idobjetovinc = f.idformapagamento ) 
																and credito='Y' order by ord,descricao",$rows["idformapagamento"]);?>		
											</select>
                                            <?}else{?>
                                                <label class="alert-warning"><?=traduzid('formapagamento','idformapagamento','descricao',$rows["idformapagamento"])?></label>
                                                <i onclick="mostraInputFormapagamento(this)" class="fa fa-pencil azul"></i>
                                                <select class="size15" style="display: none;" <? if($row["status"]!='CONCLUIDO'){echo $disabled;} ?> name="_1_u_notafiscal_idformapagamento" vnulo>
													<option></option>
													<?fillselect("SELECT idformapagamento,descricao 
																	from formapagamento f
																	where status='ATIVO'
																	".getidempresa('idempresa','formapagamento')."
																	AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (".getModsUsr('LPS').") AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'formapagamento' and ov.idobjetovinc = f.idformapagamento ) 
																	and credito='Y' order by ord,descricao",$rows["idformapagamento"]);?>		
												</select>
                                            <?}?>
										<!--
										<select <?=$disabled?> class="size8" name="_1_u_notafiscal_formapgto">
											<?fillselect("select 'DEPOSITO','Depósito' union select 'BOLETO','Boleto'",$rows["formapgto"]);?>		
										</select>
										-->
										<!-- <select class="size15" <? if($row["status"]!='CONCLUIDO'){echo $disabled;} ?> name="_1_u_notafiscal_idformapagamento" vnulo>
											<option></option>
											<?fillselect("select idformapagamento,descricao 
															from formapagamento 
															where status='ATIVO'
															".getidempresa('idempresa','formapagamento')." 
															and credito='Y' order by ord,descricao",$rows["idformapagamento"]);?>		
										</select> -->
									</td>
									<td colspan="2">Gera Parcela:<br>
                                                <select  class="size15"   name="_1_u_notafiscal_geracontapagar"   vnulo>
                                                    <?fillselect("select 'Y','Sim' union select 'N','Não'",$rows["geracontapagar"]);?>
                                                </select>	
									</td>	
									<td >
										N&ordm; Pedido:<br>
										<input type = "text" class="size15" name = "_1_u_notafiscal_npedido" size="15" value="<?=$rows["npedido"]?>"  >
									</td>
								</tr>
								<tr>
									<td colspan="10"><hr></td>
								</tr>

								<tr>
									<td ></td>
									<td >
										<?if($_1_u_nf_status != 'CONCLUIDO'){?>
										Atualizar Data(s):&nbsp;<a class="fa fa-download btn-lg verde pointer hoverazul" title="Alterar valores" onclick="atualizaconfpagar();"></a>
										<?}?>
									</td>
									<td class="nowrap">Editar Proporção:
									
									<?if($_1_u_notafiscal_proporcional=='Y'){
										$checked='checked';
										$vchecked='N';					
									}else{
										$checked='';
										$vchecked='Y';
									}?>
									</td>
									<td>
										<input <?=$edita?> title="Editar proporções" type="checkbox" <?=$checked?> name="nameproporcional" onclick="altcheck('notafiscal','proporcional',<?=$_1_u_notafiscal_idnotafiscal?>,'<?=$vchecked?>')">
									</td>
								</tr>
                                            <?// Calcula a data daqui 3 dias
                                        
                                            if(!empty( $rows['idnotafiscal']) and !empty($rows["diasentrada"]) and !empty($rows["intervalo"]) and $rows["geracontapagar"]=='Y' ){

												$q=999;
                                                $sqlcx="select dma(c.datareceb) as dmadatareceb,c.* from nfsconfpagar c where c.idnotafiscal=". $rows['idnotafiscal'];
                                                $rescx=d::b()->query($sqlcx) or die("Falha ao buscar configurações das parcelas sql=".$sqlcx);
                                                $qtdpx=mysqli_num_rows($rescx);
                                                // for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {
                                                $v = 0;
                                                $tproporcao=0;
                                                while($rowcx=mysqli_fetch_assoc($rescx)){
                                                    $q++;  
                                                    $i++;                         
                                                    if($v==0){
                                                        $dias = $rows["diasentrada"] - 1;
                                                    }else{
                                                        $dias=$rows["diasentrada"]+($rows["intervalo"]*$v) - 1;
                                                        }
                                                    if(empty($rows["emissao"])){		    	
                                                        $pvdate = date("d/m/Y H:i:s");
                                                    }else{			  
                                                        $pvdate = $rows["emissao"];		    	   
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

                                                    $proporcao=100/ $rows['qtdparcelas'];
                                                    if(empty($rowcx['proporcao'])){
                                                        $rowcx['proporcao']=$proporcao;
                                                    }
                                                    $tproporcao=$tproporcao+$rowcx['proporcao'];

                                                    // Exibe o resultado
                                                    ?> 
                                                    <tr>
                                                        <td align="right"><font color="red"><?echo(($v+1)."º"); // ?>:</font></td>
                                                            <td >  
                                                                <input <?=$edita?> style="width: 100px;" name="_<?=$q?>_u_nfsconfpagar_idnfsconfpagar"  type="hidden"	value="<?=$rowcx['idnfsconfpagar']?>">
                                                                <input  <?=$edita?> class="size7 calendario confscontapagar"  idnfsconfpagar="<?=$rowcx['idnfsconfpagar']?>" datagerada="<?=date('d/m/Y', $timestamp)?>" name="_<?=$q?>_u_nfsconfpagar_datareceb"  type="text"	value="<?=$rowcx['dmadatareceb']?>" autocomplete="off">
                                                                <!-- font color="red"><?echo(date('d/m/Y', $timestamp)); // ?></font -->
                                                                <?if($rowcx['dmadatareceb'] != date('d/m/Y', $timestamp)){?>
                                                                    &nbsp;<?=date('d/m/Y ', $timestamp)?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema" ></i> 
                                                                <?}?>
                                                            </td>
                                                            <?if($_1_u_notafiscal_proporcional=='Y'){?>
                                                            <td align="right" >Proporção:</td>
                                                            <?}?>
                                                            <td class='nowrap'>
                                                            <?if($_1_u_notafiscal_proporcional=='Y'){?>
                                                            <input <?=$edita?> class="size4"  name="_<?=$q?>_u_nfsconfpagar_proporcao"  type="text"	value="<?=round($rowcx['proporcao'],2)?>" onchange="atualizaproporcao(this,<?=$rowcx['idnfsconfpagar']?>)">
                                                            <?}?>
                                                            <?if(empty($rowcx['obs'])){?>
                                                                <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer insobspgt" onclick="nfconfpagar(<?=$rowcx['idnfsconfpagar']?>,<?=$q?>)" title="Inserir observação."></i>
                                                            <?}else{?>
                                                                <i class="fa fa-info-circle fa-1x azul pointer hoverpreto btn-lg tip"  onclick="nfconfpagar(<?=$rowcx['idnfsconfpagar']?>,<?=$q?>)" >
                                                                <span>
                                                                    <ul>
                                                                        <li> Obs: <?=$rowcx['obs']?>
                                                                        <p>
                                                                        <li> Alterado em: <?=dmahms($rowcx['alteradoem'])?>
                                                                        <p>
                                                                        <li> Alterado por: <?=$rowcx['alteradopor']?>
                                                                    </ul>
                                                                </span>
                                                                </i>
                                                            <?}?>
                                                            <div id='<?=$q?>_editarnfconfpagar' class='hide'>
                                                                <table>
                                                                    <tr>
                                                                        <td>
                                                                        <textarea name="<?=$q?>_nfsconfpagar_obs" id="<?=$q?>_nfsconfpagar_obs" style="width: 760px; height: 41px; margin: 0px;"><?=$rowcx['obs']?></textarea>
                                                                        <input id="<?=$q?>_nfsconfpagar_idnfsconfpagar"  name="<?=$q?>_nfsconfpagar_idnfsconfpagar"  type="hidden" value="<?=$rowcx['idnfsconfpagar']?>">
                                                                        </td>                                                
                                                                    </tr>                                       
                                                                    <tr>
                                                                        <td>
                                                                            <div class="row">
                                                                                <div class="col-md-12">
                                                                                    <div class="panel panel-default">		
                                                                                        <div class="panel-body">
                                                                                            <div class="row col-md-12">            
                                                                                                <div class="col-md-2" style="text-align:right">Alterado Por:</div>     
                                                                                                <div class="col-md-4" style="text-align:left"><?=$rowcx['alteradopor']?></div>
                                                                                                <div class="col-md-2" style="text-align:right">Alterado Em:</div>     
                                                                                                <div class="col-md-4" style="text-align:left"><?=dmahms($rowcx['alteradoem']);?></div>       
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> 
                                                                        </td>                                                
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?$v++;}?> 
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td><font color="red"><?=round($tproporcao,2)?></font></td>
                                                </tr>
												<tr>
													<td colspan="10"><hr></td>
												</tr>
<?
									}
?>



								<tr>
									<td colspan="4">
										<table>
											<tr>
												<td>
													Informações:<br>
													<textarea <?=$disabled?>  style="height: 100px; font-size:12px; font-weight: bold;  width: 270px;" name="_1_u_notafiscal_informacao"><?=$rows["informacao"]?></textarea>
													<div class="hidden" style="background: #ddd;padding: 8px;font-size: 10px;border: 1px solid #ccc;border-radius: 4px; width: 280px;"><?=nl2br($rows["informacao"])?></div>
												</td>		
												<td valign="top">									
													Obs adicional NFs:<br>
													<textarea  <?=$disabled?> rows="4" style="height: 100px; font-size:12px; font-weight: bold; width: 270px;"  name="_1_u_notafiscal_obs"><?=$rows["obs"]?></textarea>								
												</td>	
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td style="vertical-align: top;">
										Tributa&ccedil;&atilde;o	<br>
										<select class="size15" <?=$disabled?> name="_1_u_notafiscal_tributacao" style="font-size: 9px;" onchange="this.form.submit()"><?
												fillselect("select 'T', 'Tributável' union
												select 'C', 'Isenta de ISS' union
												select 'E', 'Não Incidência no Municí­pio' union
												select 'F', 'Imune' union
												select 'K', 'Exigibilidd Susp.Dec.J/Proc.A' union
												select 'N', 'Não Tributável' union
												select 'G', 'Tributável Fixo' union
												select 'H', 'Tributável S.N.'",$rows["tributacao"]);			
										?>
										</select>	
									</td>								
									<td>
										<p>Recolhimento<br>
										<select class="size15" <?=$disabled?> name="_1_u_notafiscal_tiporecolhimento" style="font-size: 9px;" onchange="this.form.submit()" >
											<?
											fillselect("select 'A','A Recolher' union
											select 'R','Retido na Fonte' ",$_tiporecolhimento);
											?>
										</select>
									</td>
									<td colspan="2">
										<p><b>SUB-TOTAL</b><br>
										<label class="idbox"><?=$rows["subtotal"]?></label>
										<input type="hidden" name="_1_u_notafiscal_subtotal" value="<?=$rows["subtotal"]?>">
									</td>		
								</tr>
								<tr>
									<td>
									<?if($rows["alteracaomanual"]=='Y'){
										$checkedmanual='N';
										$checkedob="checked";
										$readonly = '';
									}else{
										$checkedmanual='Y';
										$checkedob="";
									}?>
										<input type="checkbox" id="checkboxs" name="_1_u_notafiscal_alteracaomanual" <?=$checkedob?> value="<?=$rows["alteracaomanual"]?>" onclick="flgmanual('<?=$checkedmanual?>',<?=$idnotafiscal?>)" title="alterar impostos manualmente"> <b>Alteração Manual</b>
									</td>
								</tr>
								<tr>
									<td>
										<?if($rows["status"]=="CONCLUIDO" or $rows["alteracaomanual"]=='Y'){
											$irrf=$rows["irrf"];
											$pis=$rows["pis"];
											$cofins=$rows["cofins"];
											$csll=$rows["csll"];
											$iss=$rows["iss"];
										}?>
										<p><b>I.R.R.F. (<?=$aliqir?>%)</b><br>

										<input class="size10" type="text"  <?=$readonly?> name="_1_u_notafiscal_irrf" value="<?=$irrf?>" size="8"   vnulo vdecimal>
									</td>								
									<td>
										<p><b>PIS (<?=$aliqpis?>%)</b><br>
										<?
										$backcolorpis = ($piscomp <> $rows["pis"]) ? "style=\"background-color:rgb(255,155,155);\"" : "";
										?>
										<input class="size10"  type="text"  <?=$readonly?> name="_1_u_notafiscal_pis" value="<?=$pis?>" <?=$backcolorpis?> size="8"   vnulo>
										<?
										//Mostrar o alerta quando o valor calculado for diferente da tabela
										if($piscomp <> $rows["pis"]){
											?>
											&nbsp;<?=$piscomp?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema" ></i> 
											<?
										}
										?>
									</td>								
									<td colspan="2">
										<p><b>Cofins (<?=$aliqcofins?>%)</b><br>
										<?
										$backcolorcofins = ($cofinscomp <> $rows["cofins"]) ? "style=\"background-color:rgb(255,155,155);\"" : "";
										?>
										<input class="size10" type="text"  <?=$readonly?> name="_1_u_notafiscal_cofins" value="<?=$cofins?>" <?=$backcolorcofins?> size="8"  vnulo>
										<?
										//Mostrar o alerta quando o valor calculado for diferente da tabela
										if($cofinscomp <> $rows["cofins"]){
											?>
											&nbsp;<?=$cofinscomp?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema" ></i> 
											<?
										}
										?>
									</td>	
								</tr>
								<tr>	
									<td>
										<p><b>CSLL (<?=$aliqcsll?>%)</b><br>						
										<?
										$backcolorcsll = ($csllcomp <> $rows["csll"]) ? "style=\"background-color:rgb(255,155,155);\"" : "";
										?>
										<input class="size10"  <?=$readonly?> type="text" name="_1_u_notafiscal_csll" value="<?=$csll?>" <?=$backcolorcsll?> size="8"  vnulo>
										<?
										//Mostrar o alerta quando o valor calculado for diferente da tabela
										if($csllcomp <> $rows["csll"]){
											?>
											&nbsp;<?=$csllcomp?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema" ></i> 
											<?
										}
										?>
									</td>								
									<td>
										<p><b>ISS (<?=$aliqissativ?>%)</b><br>
										<input class="size10"  <?=$readonly?> type="text" name="_1_u_notafiscal_iss" value="<?=$iss?>" size="8" autocomplete="off" vnulo>
									</td>							
									<td colspan="2">
										<p><b>TOTAL</b><br>
										<?
										if($rows["status"]=="CONCLUIDO" or  $rows["alteracaomanual"]=='Y'){
											$totalnota=$rows["total"];
										}else{					
											$totalnota = round(($rows["subtotal"] - ($irrf + $tpcc)),2);
										}
										?>
										<!-- <font class="numnfverm"><?=number_format(tratanumero($totalnota),2,',','.'); ?></font> -->
										<input class="size10" type="text" name="_1_u_notafiscal_total" vnulo value="<?=$totalnota?>" size=8 <?=$fundo?> autocomplete="off" style="background-color: rgb(198,255,198); font-size: 14px; font-weight: bold;">
									</td>
								</tr>
								<?

								if(!empty($_numerorps)){
									$sqlnfs = "select idnfslote, numerorps, status, xml, xmlret, loteprefeitura, criadoem 
												from nfslote where numerorps = '".$_numerorps."' and status='SUCESSO' order by criadoem desc";
									echo "<!-- ".$sqlnfs." -->";
									$rnfs = d::b()->query($sqlnfs) or die("Erro pesquisando lote da NFS busca status: ".mysqli_error(d::b()));
									$nrows=mysqli_num_rows($rnfs);
								}
								?>		
							</table>
						</div>
					</div>
				</div>
				</div>
				<div class="row">
				<div class="col-md-12"  style="left: 0.5%; width: 98.5%; ">
					<div class="panel panel-default" >
						<div class="panel-heading">Parcelamento de Nota</div>
						<?
						######################    
						$sqlx="select 'parcela' as tipo,f.agrupado,f.descricao,f.agruppessoa,f.agrupnota,a.boleto,i.* 
								from contapagaritem i 
									join formapagamento f on(f.idformapagamento=i.idformapagamento)
									join agencia a on(f.idagencia=a.idagencia)
									where i.idobjetoorigem=".$idnotafiscal." and i.tipoobjetoorigem like 'notafiscal'
								";
						$qrpx = d::b()->query($sqlx) or die("Erro ao buscar itens do contapagar:".mysql_error());
						$qtdrx= mysqli_num_rows($qrpx);



						//die($sqlx);
						if($qtdrx>0){//tem contapagaritem
							?>
							<div class="panel-body">
								<table class="table table-striped planilha">
									<tr>
										<th>Fatura</th>
										<th>Parcela</th>
										<th>Vencimento</th>
										<th>Valor</th>			
										<th>Status</th>	
										<th>Boleto PDF</th>
										<th>Boleto</th>
										<th>Remessa</th>
										<th>C. Pagar</th>
									</tr>
									<?
									$valorsoma=0;
									while($rowx=mysqli_fetch_assoc($qrpx)){
										if(!empty($rowx['idcontapagar'])){
											$sqlp ="select idcontapagar,parcela,parcelas,intervalo,formapagto,tipo,status,dma(datapagto) as datapagto,dma(datareceb) as datareceb,valor,obs,boletopdf
													  from contapagar 
													 where idcontapagar=".$rowx['idcontapagar'];

											$qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota pela contapagaritem:".mysql_error());
											$rowp = mysqli_fetch_assoc($qrp);

											$sql="select i.idremessaitem,
														 i.idremessa,
														 i.idcontapagar,
														 i.status as remessa,
														 r.dataenvio,
														 r.status,
														 a.boleto
													from remessaitem i,remessa r,agencia a
												   where i.idremessa = r.idremessa 
												    and a.idagencia=r.idagencia
													 and r.status in ('GERADO','ENVIADO','CONCLUIDO')
													 and i.status in('C','P')
													 and i.idcontapagar = ".$rowx['idcontapagar'];
											$res=d::b()->query($sql) or die("Erro ao buscar boleto sql=".$sql);
											$boleto=mysqli_num_rows($res);
											$row=mysqli_fetch_assoc($res);
											if($rowp["boletopdf"]=='Y'){
												$checked='checked';
												$vchecked='N';					
											}else{
												$checked='';
												$vchecked='Y';
											}
										}
										$valorsoma=$valorsoma+$rowx["valor"];
										?>
										<tr>
											<td><? echo($rowp["parcela"]); ?></td>
											<td class="nowrap"><?=$rowx['parcela']?> de <?=$rowx['parcelas']?> </td>          
											<td> 
												<input type="hidden" class="datarecebparc" value="<?=$rowp["datareceb"]?>" statusCI="<?=$rowp["status"]?>" parcela="<?=$rowx['parcela']?> - Fatura">
												<?=$rowp["datareceb"]?>
											</td>
											<td>  
												<?
												if($rowx["status"]!="QUITADO"){
												?>
														<input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowx["valor"]?>" onchange="atualizavlitem(this,<?=$rowx["idcontapagaritem"]?>)">
												<?    
												}else{
												?>                                   
														<?=number_format(tratanumero($rowx["valor"]), 2, ',', '.');?>
												<?
												}
												?>          
											</td>
											<td ><?=$rowp["status"]?></td>
											<td>
												<? if($boleto>0){?>
													<a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$rowx['boleto']?>.php?idcontapagar=<?=$rowp['idcontapagar']?>')"></a>
												<?}?>
											</td>
											<td>
												<? if($boleto>0){?>
													<input title="Boleto PDF" type="checkbox" <?=$checked?> name="namecert" onclick="boletopdf(<?=$rowp['idcontapagar']?>,'<?=$vchecked?>')">
												<?}?>
											</td>
											<td>
												<? if($boleto>0){?>
													<a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')"><?=$row['idremessa']?></a>
												<?}?>
											</td>				
											<td >
												<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp["idcontapagar"]?><?=$urlIdempresa?>');"></a>
											</td>
										</tr>
									<?
									}
									if($totalnota > $valorsoma){
										$dif=$totalnota-$valorsoma;
									?>
										<tr>
											<Td>
											
												<input  value="<?=$qtdrx+1?>" id="parcela_parcelas" type='hidden' name="parcela_parcelas">
												<a class="fa fa-plus-circle verde pointer hoverazul" title="Nova Parcela" onclick="showModal('<?=$dif?>')"></a>
										
											</Td>
										</tr>		
									
									<?
									}
									$sqlx="SELECT 
											'imposto' as tipo,f.agrupado, f.descricao as configuracao,f.descricao,f.agruppessoa,i.valor as total,f.agrupnota,i.*
											FROM																		
												contapagaritem i 
													JOIN
												contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
												JOIN 
													formapagamento f ON(c.idformapagamento=f.idformapagamento)
													JOIN
												pessoa p ON (p.idpessoa = i.idpessoa)
											WHERE
												i.idobjetoorigem =".$idnotafiscal."
												and i.status!='INATIVO'
													AND i.tipoobjetoorigem LIKE 'notafiscal'
												
											UNION
											SELECT 
												'imposto' as tipo,f.agrupado, f.descricao as configuracao,f.descricao,f.agruppessoa,i.valor as total,f.agrupnota,i.*
											FROM
											contapagaritem ci                       
												JOIN contapagaritem i ON (ci.idcontapagar = i.idobjetoorigem  AND i.tipoobjetoorigem = 'contapagar' and i.status!='INATIVO')   
												JOIN  contapagar cp ON (ci.idcontapagar = cp.idcontapagar and cp.tipoespecifico='AGRUPAMENTO')
												JOIN  contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
												JOIN formapagamento f ON(c.idformapagamento=f.idformapagamento)
												JOIN pessoa p ON (p.idpessoa = i.idpessoa)
											WHERE
												ci.idobjetoorigem =".$idnotafiscal."
												and ci.status!='INATIVO'
													AND ci.tipoobjetoorigem LIKE 'notafiscal'
											UNION
											SELECT
												'imposto' as tipo,f.agrupado,c.configuracao,f.descricao,f.agruppessoa,i.total,f.agrupnota,ci.*
											FROM 
												nfitem i JOIN nf n on (n.idnf= i.idnf)
												JOIN confcontapagar c on(c.idconfcontapagar=i.idconfcontapagar)
												 JOIN contapagaritem ci on(ci.idobjetoorigem = n.idnf and ci.tipoobjetoorigem = 'nf' and ci.ajuste ='N' and ci.status!='INATIVO')
												 JOIN formapagamento f on(f.idformapagamento=ci.idformapagamento)
											WHERE i.tipoobjetoitem = 'notafiscal' and idobjetoitem =".$idnotafiscal;

									$qrpx = d::b()->query($sqlx) or die("Erro ao buscar itens de contas e guias:".mysql_error());
									$qtdix= mysqli_num_rows($qrpx);
									if($qtdix>0){
									?>
									<tr style="height: 30px;">
										<th colspan="10" style="border-bottom: 1px solid #808080a6; vertical-align: bottom;">
											Guias de Imposto
										</th>
									</tr>
									<!--tr>
										<th>Fatura</th>
										<th>Parcela</th>
										<th>Vencimento</th>
										<th>Valor</th>			
										<th>Status</th>	
										<th>Boleto PDF</th>
										<th>Boleto</th>
										<th>Remessa</th>
										<th>C. Pagar</th>					
									</tr -->
									<?
									while($rowx=mysqli_fetch_assoc($qrpx)){
										if(!empty($rowx['idcontapagar'])){
											$sqlp ="select idcontapagar,parcela,parcelas,intervalo,formapagto,tipo,status,dma(datapagto) as datapagto,dma(datareceb) as datareceb,valor,obs,boletopdf
														from contapagar 
														where idcontapagar=".$rowx['idcontapagar'];

											$qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota pela contapagaritem:".mysql_error());
											$rowp = mysqli_fetch_assoc($qrp);

											$sql="select i.idremessaitem,
															i.idremessa,
															i.idcontapagar,
															i.status as remessa,
															r.dataenvio,
															r.status,
															a.boleto
													from remessaitem i,remessa r,agencia a
													where i.idremessa = r.idremessa 
													and a.idagencia=r.idagencia
														and r.status in ('GERADO','ENVIADO','CONCLUIDO')
														and i.status in('C','P')
														and i.idcontapagar = ".$rowx['idcontapagar'];
											$res=d::b()->query($sql) or die("Erro ao buscar boleto sql=".$sql);
											$boleto=mysqli_num_rows($res);
											$row=mysqli_fetch_assoc($res);
											if($rowp["boletopdf"]=='Y'){
												$checked='checked';
												$vchecked='N';					
											}else{
												$checked='';
												$vchecked='Y';
											}
										}
										?>
										<tr>
											<td><? echo($rowx["configuracao"]); ?></td>
											<td class="nowrap"><?=$rowx['parcela']?> de <?=$rowx['parcelas']?> </td>          
											<td > 
												<input type="hidden" class="datarecebparc" value="<?=$rowp["datareceb"]?>" statusCI="<?=$rowp["status"]?>" parcela="<?=$rowx['parcela']?> - <?=$rowx["configuracao"]?>">
												<?=$rowp["datareceb"]?></td>
											<td> 											                                  
												<?=number_format(tratanumero($rowx["total"]), 2, ',', '.');?>											         
											</td>
											<td ><?=$rowp["status"]?></td>
											<td>
												<? if($boleto>0){?>
													<a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$row['boleto']?>.php?idcontapagar=<?=$rowp['idcontapagar']?>')"></a>
												<?}?>
											</td>
											<td>
												<? if($boleto>0){?>
													<input title="Boleto PDF" type="checkbox" <?=$checked?> name="namecert" onclick="boletopdf(<?=$rowp['idcontapagar']?>,'<?=$vchecked?>')">
												<?}?>
											</td>
											<td>
												<? if($boleto>0){?>
													<a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')"><?=$row['idremessa']?></a>
												<?}?>
											</td>				
											<td >
												<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp["idcontapagar"]?><?=$urlIdempresa?>');"></a>
											</td>
										</tr>
									<?
									}

								}
																
								$sqlc="select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
									from contapagar ci
										join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
										 join contapagar c on (c.idcontapagar = i.idcontapagar and c.tipoespecifico='REPRESENTACAO')
										join pessoa  p on(p.idpessoa = i.idpessoa	)
									where ci.idobjeto =".$idnotafiscal."
										and not exists(select 1 from contapagaritem ii where ii.idcontapagar=ci.idcontapagar)
										and ci.tipoobjeto like ('notafiscal')  
									union
								select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
									from contapagaritem ci  
										join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
										join contapagar c on (c.idcontapagar = i.idcontapagar and c.tipoespecifico='REPRESENTACAO')
										join pessoa  p on(p.idpessoa = i.idpessoa	)
								where ci.idobjetoorigem=".$idnotafiscal." and ci.tipoobjetoorigem like 'notafiscal' order by parcela";
								$rescom = d::b()->query($sqlc) or die("Falha ao buscar comissões da nota:".mysqli_error(d::b()));
								$qrcom=mysqli_num_rows($rescom);
								if($qrcom>0){?>
									<tr style="height: 30px;">
										<th colspan="10" style="border-bottom: 1px solid #808080a6; vertical-align: bottom;">
											Comissões
										</th>
									</tr>
								<?
								$qtdrx=0;
								while ($rowp2 = mysqli_fetch_assoc($rescom)){
									$qtdrx++;
									?>	
									<tr>

										<td><?=$rowp2["nome"]?></td>
										<td class="nowrap"><?=$rowp2['parcela']?> de <?=$rowp2['parcelas']?> </td>          
										<td >
											<input type="hidden" class="datarecebparc" value="<?=$rowp2["datareceb"]?>" statusCI="<?=$rowp2["status"]?>" parcela="<?=$rowp2['parcela']?> - <?=$rowp2["nome"]?>"> 
											<?=dma($rowp2["datareceb"])?></td>
										<td>  
											<?
											if($rowp2["status"]!="QUITADO"){
											?>
												<input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowp2["valor"]?>" onchange="atualizavlitem(this, <?=$rowp2["idcontapagaritem"]?>)">
											<?    
											}else{
											?>                                   
												<?=number_format(tratanumero($rowp2["valor"]), 2, ',', '.');?>
											<?
											}
											?>          
										</td>
										<td ><?=$rowp2["status"]?></td>
										<td>
											
										</td>
										<td>
											
										</td>
										<td>
											
										</td>
										<td >
											<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp2["idcontapagar"]?><?=$urlIdempresa?>');"></a>
										</td>

									</tr>
								<?}//while ($rowp2 = mysqli_fetch_array($qrp2)){?>
									
									
								<?}elseif($row['comissao']=='Y' and ( $qtdrx>0)){// if($qtdp2>0){?>
									<tr>
										<td colspan="10"><font color="red">Não gerou comissão!!!</font></td>
									</tr>
										             
								<?
									}//if($qtdp2>0){									
								?>
								</table>            
							</div>
						<? } else { ?>
							<div class="panel-body">
								<? 
								$selparcel = "select idcontapagar,idobjeto,parcela,parcelas,valor,dma(datapagto) as vencimento,status,boletopdf from contapagar where tipoobjeto = 'notafiscal' and idobjeto = ".$idnotafiscal;
								$result = d::b()->query($selparcel);
								//echo($selparcel);
								if(!$result){
									echo "Erro ao recuperar as parcelas da Nota : <br>\n SQL: ". $selparcel . " <br>\n Erro: " . mysqli_error(d::b());
									die;
								}
								$nroparcelas = mysqli_num_rows($result);
								if($nroparcelas > 0){
									?>
									<table class="table table-striped planilha">
										<tr>
											<th>Nº Parcela</th>			
											<th>Vencimento</th>
											<th>Valor</th>
											<th>Status</th>	
											<th>C. Pagar</th>
											<th>Boleto</th>
											<th>Boleto PDF</th>
											<th>Remessa</th>								
										</tr>
										<?
										$index = 1;
										while ($campo = mysqli_fetch_array($result)) {
											$sql="select i.idremessaitem,
														 i.idremessa,
														 i.idcontapagar,
														 i.status as remessa,
														 r.dataenvio,
														 r.status,
														 a.boleto
													from remessaitem i,remessa r, agencia a
												   where i.idremessa = r.idremessa 
												   and a.idagencia = r.idagencia
													 and r.status in ('GERADO','ENVIADO','CONCLUIDO')
													 and i.status in('C','P')
													 and i.idcontapagar =".$campo['idcontapagar'];
											$res=d::b()->query($sql) or die("Erro ao buscar boleto sql=".$sql);
											$boleto=mysqli_num_rows($res);
											$row=mysqli_fetch_assoc($res);
											if($campo["boletopdf"]=='Y'){
												$checked='checked';
												$vchecked='N';					
											}else{
												$checked='';
												$vchecked='Y';
											}
											?>
											<tr class="respreto">									
												<td>
													<?=$campo["parcela"]; echo "/".$nroparcelas; ?>
												</td>			
												<td><?=$campo["vencimento"]?></td>
												<td><?=number_format(tratanumero($campo["valor"]), 2, ',', '.')?></td>
												<td><?=$campo["status"]?></td>	
												<td >
													<a class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$campo["idcontapagar"]?>');"></a>
												</td>
												<td>
													<? if($boleto>0){?>
														<a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$row['boleto']?>.php?idcontapagar=<?=$campo['idcontapagar']?>')"></a>
													<?}?>
												</td>
												<td>
													<? if($boleto>0){?>
														<input title="Boleto PDF" type="checkbox" <?=$checked?> name="namecert" onclick="boletopdf(<?=$campo['idcontapagar']?>,'<?=$vchecked?>')">
													<?}?>
												</td>
												<td>
													<? if($boleto>0){?>
														<a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')"><?=$row['idremessa']?></a>
													<?}?>
												</td>

											</tr>
										<? 	
										} 










								$sqlci="select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
									from contapagar ci
										join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
											join contapagar c on (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
										join pessoa  p on(p.idpessoa = i.idpessoa	)
									where ci.idobjeto =".$idnotafiscal."
										and not exists(select 1 from contapagaritem ii where ii.idcontapagar=ci.idcontapagar)
										and ci.tipoobjeto like ('notafiscal')  
									union
								select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
									from contapagaritem ci  
										join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
										join contapagar c on (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
										join pessoa  p on(p.idpessoa = i.idpessoa	)
								where ci.idobjetoorigem=".$idnotafiscal." and ci.tipoobjetoorigem like 'notafiscal' order by parcela";
								$rescomi = d::b()->query($sqlci) or die("Falha ao buscar comissões da nota:".mysqli_error(d::b()));
								$qrcomim=mysqli_num_rows($rescomi);		


								$sqlx="select 'imposto' as tipo,f.agrupado,c.configuracao,f.descricao,f.agruppessoa,f.agrupnota,ci.*
										from nfitem i join nf n on (n.idnf= i.idnf)
										join confcontapagar c on(c.idconfcontapagar=i.idconfcontapagar)
										left join contapagaritem ci on(ci.idobjetoorigem = n.idnf and ci.tipoobjetoorigem = 'nf')
										left join formapagamento f on(f.idformapagamento=ci.idformapagamento)
										where i.tipoobjetoitem = 'notafiscal' and idobjetoitem =".$idnotafiscal;
								$qrpx = d::b()->query($sqlx) or die("Erro ao buscar itens de contas e guias:".mysql_error());
								$qtdrx= mysqli_num_rows($qrpx);
								if($qtdrx>0 or $qrcomim>0){
								?>
								<tr style="height: 30px;">
									<th colspan="10" style="border-bottom: 1px solid #808080a6; vertical-align: bottom;">
										Guias de Imposto
									</th>
								</tr>
								<!--tr>
									<th>Fatura</th>
									<th>Parcela</th>
									<th>Vencimento</th>
									<th>Valor</th>			
									<th>Status</th>	
									<th>Boleto PDF</th>
									<th>Boleto</th>
									<th>Remessa</th>
									<th>C. Pagar</th>					
								</tr -->
								<?
								while($rowx=mysqli_fetch_assoc($qrpx)){
									if(!empty($rowx['idcontapagar'])){
										$sqlp ="select idcontapagar,parcela,parcelas,intervalo,formapagto,tipo,status,dma(datapagto) as datapagto,dma(datareceb) as datareceb,valor,obs,boletopdf
													from contapagar 
													where idcontapagar=".$rowx['idcontapagar'];

										$qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota pela contapagaritem:".mysql_error());
										$rowp = mysqli_fetch_assoc($qrp);

										$sql="select i.idremessaitem,
														i.idremessa,
														i.idcontapagar,
														i.status as remessa,
														r.dataenvio,
														r.status
												from remessaitem i,remessa r ,agencia a
												where i.idremessa = r.idremessa 
												and a.idagencia=r.idagencia
													and r.status in ('GERADO','ENVIADO','CONCLUIDO')
													and i.status in('C','P')
													and i.idcontapagar = ".$rowx['idcontapagar'];
										$res=d::b()->query($sql) or die("Erro ao buscar boleto sql=".$sql);
										$boleto=mysqli_num_rows($res);
										$row=mysqli_fetch_assoc($res);
										if($rowp["boletopdf"]=='Y'){
											$checked='checked';
											$vchecked='N';					
										}else{
											$checked='';
											$vchecked='Y';
										}
									}
									?>
									<tr>
										<td><? echo($rowx["configuracao"]); ?></td>
										<td class="nowrap"><?=$rowx['parcela']?> de <?=$rowx['parcelas']?> </td>          
										<td > 
											<input type="hidden" class="datarecebparc" value="<?=$rowp["datareceb"]?>" statusCI="<?=$rowp["status"]?>" parcela="<?=$rowx['parcela']?> - <?=$rowx["configuracao"]?>"> 
											<?=$rowp["datareceb"]?></td>
										<td>  
											<?
											if($rowx["status"]!="QUITADO"){
											?>
													<input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowx["valor"]?>" onchange="atualizavlitem(this,<?=$rowx["idcontapagaritem"]?>)">
											<?    
											}else{
											?>                                   
													<?=number_format(tratanumero($rowx["valor"]), 2, ',', '.');?>
											<?
											}
											?>          
										</td>
										<td ><?=$rowp["status"]?></td>
										<td>
											<? if($boleto>0){?>
												<a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$row['boleto']?>.php?idcontapagar=<?=$rowp['idcontapagar']?>')"></a>
											<?}?>
										</td>
										<td>
											<? if($boleto>0){?>
												<input title="Boleto PDF" type="checkbox" <?=$checked?> name="namecert" onclick="boletopdf(<?=$rowp['idcontapagar']?>,'<?=$vchecked?>')">
											<?}?>
										</td>
										<td>
											<? if($boleto>0){?>
												<a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')"><?=$row['idremessa']?></a>
											<?}?>
										</td>				
										<td >
											<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp["idcontapagar"]?><?=$urlIdempresa?>');"></a>
										</td>
									</tr>
								<?
								}

								while ($rowp2 = mysqli_fetch_assoc($rescomi)){
									$qtdrx++;
									?>	
									<tr>

										<td><?=$rowp2["nome"]?></td>
										<td class="nowrap"><?=$rowp2['parcela']?> de <?=$rowp2['parcelas']?> </td>          
										<td >
											<input type="hidden" class="datarecebparc" value="<?=$rowp2["datareceb"]?>" statusCI="<?=$rowp2["status"]?>" parcela="<?=$rowp2['parcela']?> - <?=$rowp2["configuracao"]?>"> 
											<?=dma($rowp2["datareceb"])?></td>
										<td>  
											<?
											if($rowp2["status"]!="QUITADO"){
											?>
													<input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowp2["valor"]?>" onchange="atualizavlitem(this,<?=$rowp2["idcontapagaritem"]?>)">
											<?    
											}else{
											?>                                   
													<?=number_format(tratanumero($rowp2["valor"]), 2, ',', '.');?>
											<?
											}
											?>          
										</td>
										<td ><?=$rowp2["status"]?></td>
										<td>
											
										</td>
										<td>
											
										</td>
										<td>
											
										</td>
										<td >
											<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp2["idcontapagar"]?><?=$urlIdempresa?>');"></a>
										</td>

									</tr>
								<?}


							}
															
							$sqlc="select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
							from contapagar ci
								join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
								left join contapagar c on (c.idcontapagar = i.idcontapagar)
								join pessoa  p on(p.idpessoa = i.idpessoa	)
							where ci.idobjeto =".$idnotafiscal."
								and not exists(select 1 from contapagaritem ii where ii.idcontapagar=ci.idcontapagar)
								and ci.tipoobjeto like ('notafiscal')  
							union
						select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,i.status, c.parcelas,c.parcela
							from contapagaritem ci  
								join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
								left join contapagar c on (c.idcontapagar = i.idcontapagar)
								join pessoa  p on(p.idpessoa = i.idpessoa	)
						where ci.idobjetoorigem=".$idnotafiscal." and ci.tipoobjetoorigem like 'notafiscal' order by parcela";
						$rescom = d::b()->query($sqlc) or die("Falha ao buscar comissões da nota:".mysqli_error(d::b()));
						$qrcom=mysqli_num_rows($rescom);
						if($qrcom>0){?>
							<tr style="height: 30px;">
								<th colspan="10" style="border-bottom: 1px solid #808080a6; vertical-align: bottom;">
									Comissões
								</th>
							</tr>
						<?while ($rowp2 = mysqli_fetch_assoc($rescom)){?>	
							<tr>

								<td><?=$rowp2["nome"]?></td>
								<td class="nowrap"><?=$rowp2['parcela']?> de <?=$rowp2['parcelas']?> </td>          
								<td > 
								<input type="hidden" class="datarecebparc" value="<?=$rowp2["datareceb"]?>" statusCI="<?=$rowp2["status"]?>" parcela="<?=$rowp2['parcela']?> - <?=$rowp2["configuracao"]?>"> 
									<?=dma($rowp2["datareceb"])?>
								</td>
								<td>  
									<?
									if($rowp2["status"]!="QUITADO"){
									?>
											<input name="contapagaritem_valor" class="size10" type="text" value="<?=$rowp2["valor"]?>" onchange="atualizavlitem(this,<?=$rowp2["idcontapagaritem"]?>)">
									<?    
									}else{
									?>                                   
											<?=number_format(tratanumero($rowp2["valor"]), 2, ',', '.');?>
									<?
									}
									?>          
								</td>
								<td ><?=$rowp2["status"]?></td>
								<td>
									
								</td>
								<td>
									
								</td>
								<td>
									
								</td>
								<td >
									<a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp2["idcontapagar"]?><?=$urlIdempresa?>');"></a>
								</td>

							</tr>
						<?}//while ($rowp2 = mysqli_fetch_array($qrp2)){?>
									
							
						<?}elseif($row['comissao']=='Y' and ( $qtdrx>0)){// if($qtdp2>0){?>
							<tr>
								<td colspan="10"><font color="red">Não gerou comissão!!!</font></td>
							</tr>
											 
						<?
							}//if($qtdp2>0){									
						?>
																					
									</table>
								<?
								}else{ 
									?>
									<table>
										<tr><td align="center"><font style="font-size: 16px;"  color="red">NF não gerou parcela(s)!!!</font></td></tr>
									</table>
								<?		
								}//if ($nroparcelas > 0){?>
							</div>
						<? 
						}
						?>
					</div>
				</div>
				</div>

				<div class="row">
				<div class="col-md-12"  style="left: 0.5%; width: 98.5%; ">
					<div class="panel panel-default" >
						<div class="panel-heading">NFSe</div>
						<div class="panel-body">					
							<table>
								<tr><td><font color="red"><?=nl2br($rows['obsvenda']);?></font></td></tr>
							</table>		
							<table>
								<? $bloquearEmail = (!empty($rows['nnfe']) && $rows['status'] == 'CANCELADO') ? 'disabled' : ''; ?>
								<tr>
									<td align="right">Danfe:</td>
									<td>
										<a title="Imprimir Danfe" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/geradanfse.php?idnotafiscal=<?=$idnotafiscal?>&_idempresa=<?=cb::idempresa()?>')"></a>
									</td>
									<td align="right">RPS:</td>
									<td>
										<a title="Imprimir RPS" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/gerarpsnfse.php?idnotafiscal=<?=$idnotafiscal?>&gravaarquivo=Y&_idempresa=<?=cb::idempresa()?>')"></a>
									</td>
									<? if(!empty($rows['nnfe']) && $rows['status'] != 'CANCELADO') { ?>
										<td align="right">Detalhamento:</td>
										<td>
											<a class="fa fa-file-text pointer" title="Detalhamento NF" onclick="janelamodal('report/reldetalhenf.php?idnotafiscal=<?=$idnotafiscal?>')"></a>
										</td>	
									<? } ?>
									<td align="right" nowrap>Email Danfe?
										<?if($rows['enviadanfnfe']=='E'){?>
											<i class="fa fa-exclamation-triangle laranja" title="Houve um problema ao gerar o arquivo DANFE. Entre em Contato com o Administrador."></i>
										<?}?>
									</td>
									<td class="tdbr" align="right">
										<?
										if($rows['enviadanfnfe']=='Y'){
											$checked='checked';
											$vchecked='N';					
										}else{
											$checked='';
											$vchecked='Y';
										}

										?>
										<input title="Email Danfe" type="checkbox" <?=$bloquearEmail?> <?=$checked?> name="nameemaildanfe" onclick="emaildanfe(<?=$idnotafiscal?>,'<?=$vchecked?>')">
									</td>
									<td align="right" nowrap>Email Detalhamento?
										<?if($rows['enviadetalhenfe']=='E'){?>
											<i class="fa fa-exclamation-triangle laranja" <?=$bloquearEmail?> title="Houve um problema ao gerar o arquivo DETALHAMENTO. Entre em Contato com o Administrador."></i>
										<?}?>
									</td>
									<td class="tdbr" align="right">
										<?
										if($rows['enviadetalhenfe']=='Y'){
											$checked='checked';
											$vchecked='N';					
										}else{
											$checked='';
											$vchecked='Y';
										}
										?>
										<input title="Email Detalhamento" type="checkbox" <?=$bloquearEmail?> <?=$checked?> name="emaildetalhe" onclick="emaildetalhe(<?=$idnotafiscal?>,'<?=$vchecked?>',<?=$rows['nnfe']?>)">
									</td>							
									<?
									$sqlp ="select *
												from contapagar 
												where idobjeto =".$idnotafiscal."
												and boletopdf='Y'
												and tipoobjeto in ('notafiscal') ";

									$qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota para gerar boleto:".mysqli_error(d::b()));
									$qtdrowsp= mysqli_num_rows($qrp);
									if($qtdrowsp>0){
										if($rows['emailboleto']=='Y'){
											$checked='checked';
											$vchecked='N';					
										}else{
											$checked='';
											$vchecked='Y';
										}				
										?>
										<td align="right" nowrap>Email Boleto?
											<?if($rows['emailboleto']=='E'){?>
												<i class="fa fa-exclamation-triangle laranja" title="Houve um problema ao gerar um arquivo de Boleto. Entre em Contato com o Administrador."></i>
											<?}?>
										</td>
										<td class="tdbr" align="right">
											<input title="Email Boleto" type="checkbox" <?=$checked?> name="nameemailboleto" onclick="emailbol(<?=$idnotafiscal?>,'<?=$vchecked?>')">
										</td>
										<?
									}//if($qtdrowsp>0){
									if($rows['decsimplesn']=='Y'){
										?>
										<td align="right" nowrap>Email Dcl. S. Nacional?</td>
										<td class="tdbr" align="right">
											<?
											if($rows['emaildsimplesnac']=='Y'){
												$checked='checked';
												$vchecked='N';					
											}else{
												$checked='';
												$vchecked='Y';
											}
											?>
											<input title="Email Simples" type="checkbox" <?=$checked?> name="dsimples" onclick="altdsimples(<?=$idnotafiscal?>,'<?=$vchecked?>')">
										</td>
									<?							
									}//if($rows['decsimplesn']=='Y'){
									?>	
															
								</tr>
								<tr>
									<td align="right">Remetente:</td>
									<td colspan="10" >
										<table>
											<?
											$sqlempresaemail = "SELECT * FROM empresaemails WHERE tipoenvio = 'NFS' ".getidempresa('idempresa','empresa');
											$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar empresaemails sql=".$sqlempresaemail);
											$qtdempresaemail=mysqli_num_rows($resempresaemail);
											if($qtdempresaemail == 1){
												$nemails = 1;
											}else{
												if($qtdempresaemail > 1){
													$nemails = 2;
												}else{
													$nemails = 0;
												}
											}

											$sqlemailobj = "SELECT * FROM empresaemailobjeto WHERE tipoenvio = 'NFS' and tipoobjeto = 'nfs' and idobjeto =".$idnotafiscal." ".getidempresa('idempresa','pessoa')." order by idempresaemailobjeto desc limit 1";
											$resemailobj=d::b()->query($sqlemailobj) or die("Erro ao buscar empresaemailobjeto sql=".$sqlemailobj);
											$rowemailobj=mysqli_fetch_assoc($resemailobj);
											$qtdemailobj=mysqli_num_rows($resemailobj);

											if($qtdemailobj < 1){
												$setemail = 1;
											}else{
												$setemail = 0;
											}

											if($nemails == 1){?>
												<tr>
													<td>
														<?
														$sqldominio = "SELECT em.idemailvirtualconf,
																			  em.idempresa,ev.email_original as dominio 
																		FROM empresaemails em
																		JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf) 
																		WHERE em.tipoenvio = 'NFS' 
																		AND ev.status = 'ATIVO' ".getidempresa('em.idempresa','pessoa');

														$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
														$rowdominio=mysqli_fetch_assoc($resdominio)?>

														<input id="emailunico" type="hidden" value="<?=$rowdominio["idemailvirtualconf"]?>">
														<input id="idempresaemail" type="hidden" value="<?=$rowdominio["idempresa"]?>">
														<label class="alert-warning"><?=$rowdominio["dominio"]?></label>
													</td>
												</tr>
											<?}else{
												if($nemails > 1){
													$sqldominio = "SELECT em.idemailvirtualconf,
																		  ev.email_original as dominio,
																		  em.idempresa 
																	FROM empresaemails em
																	JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
																	WHERE em.tipoenvio = 'NFS' 
																	AND ev.status = 'ATIVO' ".getidempresa('em.idempresa','pessoa');

													$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
													$qtddominio=mysqli_num_rows($resdominio);
													if($qtddominio>0){
														while($rowdominio=mysqli_fetch_assoc($resdominio)){
															if($rowdominio["idemailvirtualconf"] == $rowemailobj["idemailvirtualconf"]){
																$chk = 'checked';
															}else{
																$chk = '';
															}?>
															<tr>
																<td>
																	<input class="emailorcamento" title="Email Remetente" type="radio" <?=$chk?> onclick="altremetenteemail(<?=$idnotafiscal?>,<?=$rowdominio["idemailvirtualconf"]?>,'NFS',<?=$rowdominio["idempresa"]?>)">
																	<label class="alert-warning" ><?=$rowdominio["dominio"]?> </label>
																</td>
															</tr>
															<?
														}
													}
												}
											}
											?>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="10">
										<table>
											<tr>												
												<td  align="right">Email(s):</td>
												<? if(empty($rows['emailnfe'])){
													//$rows['emailnfe']='vendas@laudolab.com.br';
													//$rows['emailnfe'].=",".traduzid("pessoa","idpessoa","email",$rows['idpessoa']);
													$rows['emailnfe'].=(traduzid("pessoa","idpessoa","email",$rows['idpessoa']));
													//concatenar emailcopia
													$sqlcc="select emailcopia from pessoa where idpessoa=".$rows['idpessoa'];
													$sqlcc = "SELECT p.email as emailcopia
													from pessoacontato c join pessoa p on (c.idcontato = p.idpessoa)
													where c.idpessoa = ".$rows['idpessoa']."
													and p.status='ATIVO'
													and c.emailnfsecc = 'Y'
													and p.idtipopessoa not in (12,1) ".getidempresa('p.idempresa','nfs');

													//$sqlcc="select emailcopia from pessoa where idpessoa=".$rows['idpessoa'];

													echo "<!-- $sqlcc -->";
													$rescc=d::b()->query($sqlcc) or die("erro ao buscar email de copia sql=".$sqlcc);
													if(empty($rows['emailnfe'])){
														$virg = "";
													}else{
														$virg = ",";
													}
													if(mysqli_num_rows($rescc) > 0){
														while($rowcc=mysqli_fetch_assoc($rescc)){
															if(!empty($rowcc['emailcopia'])){
																$rows['emailnfe'].=$virg.$rowcc['emailcopia'];
																$virg = ",";
															}
														}
													}
												}
												?>
												<td colspan="6">
													<textarea  style="width: 375px; height: 50px; font-size:12px; font-weight: bold;"  name="_1_u_notafiscal_emailnfe" onchange="atualizaemail(this,<?=$idnotafiscal?>)"><?=$rows['emailnfe']?></textarea>
													<?
													$existepv0 = strpos($rows['emailnfe'], ";");
													if ($existepv0 === false) {
														null;
													}else{
														echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";			
													}
													?>
												</td>
												<?
												if(!empty($rows['nnfe']) and $rows['status']=='CONCLUIDO'){
													if($rows['enviaemailnfe']=='Y' or $rows['enviaemailnfe']=='A'){
														$classtdemail="amarelo";	
														$emailval='N';			    
													}elseif($rows['enviaemailnfe']=='O'){
														$classtdemail="verde";	
														$emailval='N';			    
													}elseif($rows['enviaemailnfe']=='E'){
														$classtdemail="vermelho";
														$emailval='N';			    
													}else{
														$classtdemail="cinza";
														$emailval='Y';				    
													}
													?>
													<td class="<?=$classtdemail?>" align="right" nowrap>Enviar Email</td>
													<td class="<?=$classtdemail?>" align="right" nowrap>
														<a class="fa fa-envelope pointer <?=$classtdemail?>" title="Enviar email NFe" onclick="envioemail(<?=$idnotafiscal?>,'<?=$emailval?>');"></i>
													</td>
													

													<td>							
														<?	
														/*	    
														if(!empty($idnotafiscal)){
															$sqleo="select * from  log 
																	 where idobjeto = ".$idnotafiscal."
																	   and tipoobjeto = 'notafiscal' 
																	   and tipolog = 'EMAILNFSE' order by criadoem";
															$reseo=d::b()->query($sqleo) or die("Erro ao buscar emails da nf sql=".$sqleo);
															$qtdeo= mysqli_num_rows($reseo);
															if($qtdeo>0){
																?>
																<div class="oEmailorc">
																	<a class="fa fa-search azul pointer hoverazul" title=" Ver Log Email" data-target="webuiPopover0" ></a>
																</div>
																<div class="webui-popover-content">
																	<?
																		while($roweo= mysqli_fetch_assoc($reseo)){
																			?>
																			<li><?=$roweo["log"]?> <?=$roweo["status"]?> <?=dmahms($roweo["criadoem"])?></li>
																			<?
																		}//while($roweo= mysqli_fetch_assoc($reseo)){
																	?>
																</div>
															<?
															}//if($qtdeo>0){
														}// if(!empty($idnotafiscal)){
														*/
														?>
													</td>
													
												<?
												}//if(!empty($rows['nnfe']) and $rows['status']=='CONCLUIDO'){
												?>
											</tr>
											<?
											$sqlemail = "SELECT 
															m.idmailfila
														FROM
															mailfila m
														WHERE
															m.tipoobjeto = 'nfs'
																AND m.idobjeto = ".$idnotafiscal."																	
														ORDER BY
															idmailfila DESC LIMIT 1";
											$resemail = d::b()->query($sqlemail) or die("Falha na consulta do email: " . mysql_error() . "<p>SQL: ".$sqlemail);
											$rowemail = mysqli_fetch_assoc($resemail);   
											$numemail = mysqli_num_rows($resemail);
												   										
											
											if($numemail > 0){?>
											<tr class="<?=$obslist?>" id='emaildadosnfe'> 
												<td align="right">
													<a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?=$rowemail['idmailfila']?>')">
													Log de Email(s):
													</a>
												</td>
												<td colspan="8" style="word-break:break-word">
													<?//maf: consultar diretamente dos logs de SMTP //nl2br($_1_u_nf_emaildadosnfe)
														echo consultaLogsSmtp('nfs',$idnotafiscal,"table");
													?>
												</td>
												</td>
											</tr>
										
											
											<?}
											?>
												

									</table>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				</div>
			
				<div class="row">
				<div class="col-md-12"  style="left: 0.5%; width: 98.5%; ">
					<div class="panel panel-default" >
						<div class="panel-heading">Status NFSe Municipal</div>
						<div class="panel-body">
							<?
							if(!empty($_numerorps)){

								$sqlnfs = "select idnfslote, numerorps, status, xml, xmlret, loteprefeitura, criadoem from "._DBNFE.".nfslote where numerorps = '".$_numerorps."' ".getidempresa('idempresa', 'nfs')." order by criadoem desc";
								echo "<!-- ".$sqlnfs." -->";
								$rnfs = d::b()->query($sqlnfs) or die("Erro pesquisando lote da NFS: ".mysqli_error(d::b()));
								$infs = mysqli_num_rows($rnfs);
								//	if ($infs > 0){
								?>
								<table class="normal" style="width: 100%;">
									<tr class="header">
										<td>Data</td>
										<td>Lote</td>
										<td>Status</td>
										<td></td>
									</tr>
									<?
									while ($lnfs = mysqli_fetch_array($rnfs)) {
										if($lnfs["status"]=='SUCESSO'){
											$sucesso = 1;
										}
										?>
										<tr class="respreto">
											<td><?=$lnfs["criadoem"]?></td>
											<td><?=$lnfs["loteprefeitura"];?></td>
											<td><?=$lnfs["status"]?></td>
											<?
											if(!empty($lnfs["idnfslote"])){
												?>					
												<td>
												<td>							
													<?		    
													if(!empty($idnotafiscal)){
														$sqleo="select * from nfslote where idnfslote =  ".$lnfs["idnfslote"];
														$reseo=d::b()->query($sqleo) or die("Erro ao buscar log  da nfse sql=".$sqleo);
														$qtdeo= mysqli_num_rows($reseo);
														if($qtdeo>0){
															?>
															<div class="oNFSe">
																<a class="fa fa-search azul pointer hoverazul" title=" Ver Log NFSe" data-target="webuiPopover0" ></a>
															</div>
															<div class="webui-popover-content">
															<?
																while($roweo= mysqli_fetch_assoc($reseo)){
																	?>
																	<li>Envio:<?=$roweo["xmlret"]?></li>
																	<li>Retorno:<?=$roweo["xmlretconsult"]?></li>
																	<?
																}
																?>
															</div>
															<?
														}//if($qtdeo>0){
													}//if(!empty($idnotafiscal)){
													?>
												</td>
												<? 
											} else { echo"<td></td>"; }//if(!empty($lnfs["idnfslote"])){
											
											if($lnfs["status"]=='SUCESSO'){
												?>
												<td>
													<a class="fa fa-minus-square vermelho pointer hoverazul" title="Cancelar NFe" onclick="cancelanfe(<?=$idnotafiscal?>);"></a>
												</td>
												<?	
											}
											if($lnfs["status"]=='CONSULTANDO' or $lnfs["status"]=='SUCESSO' or $lnfs["status"]=='ERRO'){
												?>		
												<td>
													<a class="fa fa-refresh vermelho pointer azul" title="Voltar status para nova consulta." onclick="statusnfslote(<?=$lnfs['idnfslote']?>);"></a>
												</td>
												<?
											}if($lnfs["status"]=='PENDENTE'){
												?>
												<td>
													<a class="fa fa-cloud-download pointer hoverazul" title="Processar Notas Pendentes" onClick="processapendentes();"></a>
												</td>
												<?
											}
											?>
										</tr>
												<?
									}//while ($lnfs = mysqli_fetch_array($rnfs)) {
									if($sucesso == 1 or !empty($rows['motivoc'])){
										?>		
										<tr>
											<td colspan="7">
												<input type="hidden" name="_1_u_notafiscal_idnotafiscal" value="<?=$rows["idnotafiscal"]?>">									
												<textarea placeholder="PREENCHER NO CASO DE CANCELAMENTO" class="caixa" style="width: 100%; height: 20px; font-size:12px"  name="_1_u_notafiscal_motivoc" onchange="CB.post(this);"><?=$rows['motivoc']?></textarea>
											</td>
										</tr>
									<?
									}// if($sucesso == 1 or !empty($rows['motivoc'])){
									?>							
								</table>
							<?
							}//if(!empty($_numerorps)){
							?> 
							<div>
								<?if($sucesso!=1 and $rows['status']=='FECHADO'){//so envia para prefeitura se tiver gerado parcela?>
									Enviar NF  <a class="fa fa-cloud-upload pointer hoverazul" title="Enviar NF" onclick="envionfse();"></a>								    
								<?}?>
							</div>
						</div>
					</div>
				</div>
				</div>
			<? 
			}
			?>				
		</div>		
	</div>
</div>
<div class="container-fluid">
	<?$tabaud = "notafiscal";?>
</div>
<div id="novaparcela" style="display: none;">
    <div class="row">
        <div class="col-md-12">
        <table style="margin-left: 26%;margin-bottom: 10px;">
            <tr> 
                    <td style="width: 100px !important; "><input type="radio" id="checkcredito" name="_modalnovaparcelacontapagar_tipo_" value="C"checked="yes" style="margin-right: 5px;"> Crédito </td>  
                    <td style="width: 100px !important; " ><input type="radio" id="checkdebito" name="_modalnovaparcelacontapagar_tipo_" value="D" style="margin-right: 5px;"> Débito  </td> 
                </tr> 
            </table>
            <table>              
                <tr>
                    <td align="right">Forma de Pagamento:</td>
                    <td>
                        <select id="formapagnovaparc" name="formapagnovaparc">
                            <option></option>
                            <?fillselect("select idformapagamento,descricao 
                                            from formapagamento 
                                            where status='ATIVO' 
                                            and credito='Y'  and agrupnota='Y' ".getidempresa('idempresa','formapagamento')."  order by descricao desc",$rows["idformapagamento"]);?>		
                        </select>
                    </td> 
                    <td align="right">Valor:</td>
                    <td><input type="text" id="valornovaparc" name="valornovaparc" value="valorparc"></td>
                </tr>
                <tr>
                    <td align="right">Vencimento:</td>
                    <td><input type="date" id="vencnovaparc" name="vencnovaparc" placeholder="Ex: 00/00/0000" ></td> 
                </tr>
            </table>
        </div>
    </div>
</div>
<?
}
if(!empty($idnotafiscal)){
    $sql = "select p.idpessoa
                ,p.nome 
                ,CASE
                    WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE
                    WHEN c.status ='ATIVO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
            from carrimbo c ,pessoa p 
            where c.idpessoa = p.idpessoa
            and c.status IN ('ATIVO','PENDENTE')
            and c.tipoobjeto in('nfs')
            and c.idobjeto =".$idnotafiscal."  order by nome";

    $res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
    $existe = mysqli_num_rows($res);
    if($existe > 0){
		?>
		<div class="panel panel-default" >
			<div class="panel-heading">Assinaturas</div>
			<div class="panel-body">
				<table class="planilha grade compacto">
					<tr>
						<th >Funcionários</th>
						<th >Data Assinatura</th>
						<th >Status</th>	
					</tr>			
					<?			
					while($row = mysqli_fetch_assoc($res)){			
						?>	
						<tr class="res">
							<td nowrap><?=$row["nome"]?></td>
							<td nowrap><?=$row["dataassinatura"]?></td>
							<td nowrap><?=$row["status"]?></td>
						</tr>				
						<?							
					}
					?>	
				</table>
			</div>
		</div>
	<?
	}
}

$tabaud = "notafiscal"; 
require 'viewCriadoAlterado.php';
?>

<script>
<?
if(!empty($idnotafiscal)){
    $sqla="select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$idnotafiscal." 
	    and tipoobjeto in ('nfs')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda= mysqli_num_rows($resa);
    if($qtda > 0){
		$rowa=mysqli_fetch_assoc($resa);
		?>    
			botaoAssinar(<?=$rowa['idcarrimbo']?>);  
		<?	    
    }// if($qtda>0){
}
?>


jCli=<?=$jCli?>;// autocomplete cliente
//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id}
});
//autocomplete de clientes update
$("[name*=u_notafiscal_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,select: function(event, ui){
        // $("[name=_1_u_notafiscal_idpessoa]").val("").cbval("");	
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

//autocomplete de clientes insert
$("[name*=i_notafiscal_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,select: function(event, ui){
        salvacliente(ui.item.value);	
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});


function salvacliente(idpessoa){
	
   

    if(confirm('Gerar nota para o cliente?')){	    
	CB.post({
	    objetos: "_1_i_notafiscal_idnotafiscal=&_1_i_notafiscal_idpessoa="+idpessoa 
	    ,parcial:true
            ,posPost: function(data, textStatus, jqXHR){
               document.location.reload(true);
            }
	});
    }
}

// FIM autocomplete cliente
<?if(!empty($idnotafiscal)){?> 

jprodservtemp=<?=$jprodservtemp?>;// autocomplete jTipoProdServ
//mapear autocomplete de jTipoProdServ
jprodservtemp = jQuery.map(jprodservtemp, function(o, id) {
    return {"label": o.descr, value:id+""}
});


$("#insidprodserv").autocomplete({
    source: jprodservtemp
    ,delay: 0  
    ,select: function(event, ui){
            
      inseriritempr(ui.item.value);
    }
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
         return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }    
});    

function inseriritempr(idprodserv){   
    var quantidade= $("#_9_quantidade").val();
    var idnotafiscal=$("#idnotafiscal").val();
    CB.post({
	    objetos: "_ps_i_notafiscalitens_idnotafiscal="+idnotafiscal+"&_ps_i_notafiscalitens_quantidade="+quantidade+"&_ps_i_notafiscalitens_idprodserv="+idprodserv
	    ,parcial:true
	});
}
    
    
//Função para Atualizar Parcela quando o Status da Nota for "CONCLUIDO". Lidiane (07-05-2020) - Início - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315896
function liberaAtualizaParcela(vthis)
{
	//$("[name=_1_u_notafiscal_qtdparcelas]").val($("[name=_1_u_notafiscal_qtdparcelas]").val());
	document.getElementById('libera').style.display="";	

	CB.post({
		objetos: "_parc_u_notafiscal_idnotafiscal="+$("[name=_1_u_notafiscal_idnotafiscal]").val()+"&_parc_u_notafiscal_qtdparcelas="+$(vthis).val()
		,parcial: true        
	}) 
}

function atualizarParcela()
{	
	$idpessoa= $("[name=idpessoa]").val();
    $idnotafiscal=$("#idnotafiscal").val();
    $qtdparcelas=$("[name=_1_u_notafiscal_qtdparcelas]").val();
    $diasentrada=$("[name=_1_u_notafiscal_diasentrada]").val();
    $total=$("[name=_1_u_notafiscal_total]").val();
    $emissao=$("[name=_1_u_notafiscal_emissao]").val();
    $intervalo=$("[name=_1_u_notafiscal_intervalo]").val();
    $idformapagamento=$("[name=_1_u_notafiscal_idformapagamento]").val();
    $npedido=$("[name=_1_u_notafiscal_npedido]").val();
    $informacao=$("[name=_1_u_notafiscal_informacao]").val();
	$geracontapagar=$("[name=_1_u_notafiscal_geracontapagar]").val();
	
	var txt;
	var r = confirm("Confirmar alteração da quantidade de Parcelas!");

	if(r == true) {
		CB.post({
			objetos: "_1_u_notafiscal_idnotafiscal=<?=$idnotafiscal?>"
					 +"&_1_u_notafiscal_npedido="+$npedido
					 +"&_1_u_notafiscal_informacao="+$informacao
					 +"&_1_u_notafiscal_idpessoa="+$idpessoa
					 +"&_1_u_notafiscal_qtdparcelas="+$qtdparcelas
					 +"&_1_u_notafiscal_diasentrada="+$diasentrada
					 +"&_1_u_notafiscal_total="+$total
					 +"&_1_u_notafiscal_emissao="+$emissao
					 +"&_1_u_notafiscal_intervalo="+$intervalo
					 +"&_1_u_notafiscal_idformapagamento="+$idformapagamento
					 +"&_1_u_notafiscal_geracontapagar="+$geracontapagar
					 +"&envionfs=Y" 
		});	
		//alert("Parcelas Alteradas!");
	}
}
//Função para Atualizar Parcela quando o Status da Nota for "CONCLUIDO". Lidiane (07-05-2020) - Fim

function botaoAssinar(inidcarrimbo){
    $bteditar = $("#btAssina");
    if($bteditar.length==0){
		CB.novoBotaoUsuario({
			id:"btAssina"
			,rotulo:"Assinar"
			,class:"verde"
			,icone:"fa fa-pencil"
			,onclick:function(){
				CB.post({
					objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_status=ATIVO"
					,parcial:true 
					,posPost: function(data, textStatus, jqXHR){
						escondebotao();  
					}
				});
			}
				
		});
    }
}

function escondebotao(){
    $('#btAssina').hide();
   // document.location.reload(); 
}




 
    
function check(inobj, incheckname) {
    if (inobj.parentNode.nodeName == "TD") {

        objtbl = inobj.parentNode.offsetParent;
        colobj = objtbl.getElementsByTagName("INPUT");
        try {
            for (i = 0; i < colobj.length; i++) {
                vobj = colobj[i];
                if (vobj.type == "checkbox") {
                    if (vobj.getAttribute('atname').indexOf(incheckname)  >= 0) {
                        if (vobj.checked) {
                            vobj.checked = false;
                        } else {
                            vobj.checked = true;
                        }
                    }
                }
            }
        } catch (err) {
            window.status = "Falhou ao percorrer os items CHECKBOX;";
        }

    } else {
        window.status = "Nenhum <TD> encontrado para [inobj.parentNode.nodeName]; Verifique se o objeto imediatamente superior = <TD>;";
    }
}

function inotafiscalitens(vthis,inid){    
    //pega todos os inputs checkados 		
    var inputprenchido= $("#"+inid).children().find("input:checkbox:checked");
    //pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
    var vsubmit= decodeURIComponent($(inputprenchido).parent().parent().find("input:text, input:hidden").serialize());
    vsubmit=vsubmit.concat("&tipo="+inid); 
    //insere no banco de dados via submitajax
    //CB.post(vsubmit);
    CB.post({
		objetos: vsubmit		
		,parcial:true
    })
}


function iinotafiscalitens(vthis,inid){    
    //pega todos os inputs checkados 		
    var inputprenchido= $("#"+inid).children().find("input:checkbox:checked");
	var objt = {}
	inputprenchido.each((k,v)=>{
		$(v).siblings().each((i,j)=>{
			objt[$(j).attr("name")] = $(j).val();
		});
	});

	if(Object.keys(objt).length > 0){
		objt.tipo = inid;
		CB.post({
			objetos: objt
			,parcial: true
		});
	}else{
		alert("É necessário marcar pelo menos 1 item");
	}
}

function inseriritem(inidnf,inqtd,indesc,invalor,idcontaitem,idtipoprodserv){
	vPost = "";
	vPost = vPost + "_x_i_notafiscalitens_idnotafiscal="+inidnf;
	vPost = vPost + "&_x_i_notafiscalitens_quantidade="+inqtd;
	vPost = vPost + "&_x_i_notafiscalitens_valor="+invalor;
	vPost = vPost + "&_x_i_notafiscalitens_descricao="+indesc;
	vPost = vPost + "&_x_i_notafiscalitens_idcontaitem="+idcontaitem;
	vPost = vPost + "&_x_i_notafiscalitens_idtipoprodserv="+idtipoprodserv;
	vPost = vPost + "&tipo=idescritivo";	
	CB.post({
	    objetos: vPost
	    ,parcial:true
		,posPost: function(resp,status,ajax){
			if(status="success"){
				$("#cbModalCorpo").html("");
				$('#cbModal').modal('hide');                    
			}else{
				alert(resp);
			}
		}
	});
}

function salvaitem(){
	vobjquant = document.getElementById("compquant");
	vobjcompl = document.getElementById("prompt_complemento");
	vobjvalor = document.getElementById("prompt_valor");
	idcontaitem=$("#grupoes").attr('cbvalue');
	idtipoprodserv=$("#autoidtipoitem").val();
	
	

	if(idcontaitem == ""){
	   // alertacampo(vobjquant,"Este campo é obrigatório, e precisa ser informado!");
           alert("A Categoria é obrigatória, e precisa ser informada!");
	    return false;
	}
	if(idtipoprodserv == ""){
	   // alertacampo(vobjquant,"Este campo é obrigatório, e precisa ser informado!");
           alert("A Subcategoria é obrigatória, e precisa ser informada!");
	    return false;
	}
	if(vobjquant.value == ""){
	   // alertacampo(vobjquant,"Este campo é obrigatório, e precisa ser informado!");
           alert("O campo quantidade é obrigatório, e precisa ser informado!");
	    return false;
	}
	if(vobjcompl.value == ""){
	   // alertacampo(vobjcompl,"Este campo é obrigatório, e precisa ser informado!");
            alert("O campo descrição é obrigatório, e precisa ser informado!");
	    return false;
	}
	if(vobjvalor.value == ""){
	   // alertacampo(vobjvalor,"Este campo é obrigatório, e precisa ser informado!");
           alert("O campo valor é obrigatório, e precisa ser informado!");
	    return false;
	}
	inseriritem(<?=$idnotafiscal?>,vobjquant.value,vobjcompl.value,vobjvalor.value,idcontaitem,idtipoprodserv);
	//janelamodal(vurl);
	//alert("MAF: Alterar insert utilizando CB.post()");
}

function alteracliente(){
	
    $idpessoa=$("#idpessoa").val();
    $idnotafiscal=$("#idnotafiscal").val();

    if(confirm('Alterar Cliente?')){	    
	CB.post({
	    objetos: "_x_u_notafiscal_idnotafiscal="+$idnotafiscal+"&_x_u_notafiscal_idpessoa="+$idpessoa 
	    ,parcial:true
	});
    }
}


function envionfse(){
 
    $idpessoa= $("[name=idpessoa]").val();
    $idnotafiscal=$("#idnotafiscal").val();
    $qtdparcelas=$("[name=_1_u_notafiscal_qtdparcelas]").val();
    $diasentrada=$("[name=_1_u_notafiscal_diasentrada]").val();
    $total=$("[name=_1_u_notafiscal_total]").val();
    $emissao=$("[name=_1_u_notafiscal_emissao]").val();
    $formapgto=$("[name=_1_u_notafiscal_formapgto]").val();
    $intervalo=$("[name=_1_u_notafiscal_intervalo]").val();
    $idformapagamento=$("[name=_1_u_notafiscal_idformapagamento]").val();
    $npedido=$("[name=_1_u_notafiscal_npedido]").val();
    $informacao=$("[name=_1_u_notafiscal_informacao]").val();
	$geracontapagar=$("[name=_1_u_notafiscal_geracontapagar]").val();
    
    
    var data_1=$("#dataatual").val();
    var data_2 =$("#fdata").val();

    var Compara01 = parseInt(data_1.split("/")[2].toString() + data_1.split("/")[1].toString() + data_1.split("/")[0].toString());
    var Compara02 = parseInt(data_2.split("/")[2].toString() + data_2.split("/")[1].toString() + data_2.split("/")[0].toString());

    if (Compara01 > Compara02) {	  
		var mensg="DATA DE EMISSÃO MENOR QUE A DATA ATUAL!!!! \n\nDESEJA ENVIAR A NOTA FISCAL PARA A PREFEITURA MESMO ASSIM? \n\nNenhuma alteracao posterior sera permitida!";
    }else{
		var mensg="Deseja enviar a Nota Fiscal para a Prefeitura?\nNenhuma alteracao posterior sera permitida!";
    }	
	
    if(confirm(mensg)){
		CB.post({
			objetos: "_1_u_notafiscal_idnotafiscal=<?=$idnotafiscal?>&_1_u_notafiscal_npedido="+$npedido+"&_1_u_notafiscal_informacao="+$informacao+"&_1_u_notafiscal_idpessoa="+$idpessoa+"&_1_u_notafiscal_qtdparcelas="+$qtdparcelas+"&_1_u_notafiscal_diasentrada="+$diasentrada+"&_1_u_notafiscal_total="+$total+"&_1_u_notafiscal_emissao="+$emissao+"&_1_u_notafiscal_intervalo="+$intervalo+"&_1_u_notafiscal_idformapagamento="+$idformapagamento+"&envionfs=Y&_1_u_notafiscal_geracontapagar="+$geracontapagar
			,parcial:true
			,posPost: function(){		
				vurl = "../inc/nfe/nfsenvionovo.php?idnotafiscal=<?=$idnotafiscal?>";
				$.ajax({
					type: "post",
					url : vurl,
					success: function(data){
						alert(data);
						document.location.reload();
					},
					error: function(objxmlreq){
						alert('Erro:\n'+objxmlreq.status); 
					}
				})//$.ajax		
			}
		});
    }
}

function processapendentes(){

	vurl = "../cron/processapendentes.php";
	$.ajax({
		type: "post",
		url : vurl,
		success: function(data){
			document.location.reload();
		},
		error: function(objxmlreq){
			alert('Erro:\n'+objxmlreq.status); 
		}
	})//$.ajax	
}

/*

function envionfse(){	
//	alert($("#numerorps").val());

    vurl = "../inc/nfe/nfsenvio.php?idnotafiscal=<?=$idnotafiscal?>";

    var data_1=$("#dataatual").val();
    var data_2 =$("#fdata").val();

    var Compara01 = parseInt(data_1.split("/")[2].toString() + data_1.split("/")[1].toString() + data_1.split("/")[0].toString());
    var Compara02 = parseInt(data_2.split("/")[2].toString() + data_2.split("/")[1].toString() + data_2.split("/")[0].toString());

    if (Compara01 > Compara02) {	  
		var mensg="DATA DE EMISSÃO MENOR QUE A DATA ATUAL!!!! \n\nDESEJA ENVIAR A NOTA FISCAL PARA A PREFEITURA MESMO ASSIM? \n\nNenhuma alteracao posterior sera permitida!";
    }else{
		var mensg="Deseja enviar a Nota Fiscal para a Prefeitura?\nNenhuma alteracao posterior sera permitida!";
    }	
	
    if(confirm(mensg)){		
	$.ajax({
	    type: "get",
	    url : vurl,
	    success: function(data){
			alert(data);
	    },
	    error: function(objxmlreq){
			alert('Erro:\n'+objxmlreq.status); 
	    }
	})//$.ajax
    }
}
*/

function cancelanota(inid){ 
    CB.post({
        objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_status=CANCELADO"                 
    });  
}

function cancelanfe(inidnotafiscal){	
/*
    if(confirm('NFe já foi cancelada na Prefeitura? Deseja realmente Cancelar a NF?')){		
	CB.post({
	    objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_status=CANCELADO"                 
	});    
    }
*/
	vurl = "inc/nfe/nfscancela.php?idnotafiscal=<?=$idnotafiscal?>";
	
	if(confirm("Deseja cancelar a Nota Fiscal da Prefeitura?\nNenhuma alteracao posterior sera permitida!")){
		
		$.ajax({
			type: "get",
			url : vurl,
			success: function(data){
				alert(data);
			},
			error: function(objxmlreq){
				alert('Erro:\n'+objxmlreq.status); 
			}
		})//$.ajax
	}

}
function confirmanf(){
    if((window.opener)){
		if(confirm('FECHAR NOTA FISCAL?')){
			return true;
		}else{
			return false;
		}
    }
}
function emaildanfe(inid,vcheck){
    CB.post({
        objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_enviadanfnfe="+vcheck   
		,parcial:true
		,posPost: function(){
			if(vcheck=='Y'){
				if (getUrlParameter("_idempresa") != "") {
					vurl = "form/geradanfse.php?idnotafiscal="+inid+"&gravaarquivo=Y&_idempresa="+getUrlParameter("_idempresa");
				} else {
					vurl = "form/geradanfse.php?idnotafiscal="+inid+"&gravaarquivo=Y";
				}
				
				document.body.style.cursor = 'wait';		
				$.get(vurl, 
					function(resposta){
						$("#resp").html(resposta);
						if(resposta=="OK"){
							//$('#frm').submit();
							//  document.location.reload(true);
						}else{
							alert(resposta);
						}					
					}
				);
				document.body.style.cursor = '';
			}
		}
    });
}

function emaildetalhe(inid,vcheck,nnfe){
	//alertAtencao('Serviço Indisponível Temporariamente.');
    CB.post({
        objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_enviadetalhenfe="+vcheck   
	,parcial:true
	,posPost: function(){
	    if(vcheck=='Y'){
		vurl = "report/reldetalhenf.php?nnfe="+nnfe+"&geraarquivo=Y&gravaarquivo=Y";
		document.body.style.cursor = 'wait';		
		$.get(vurl, 
		    function(resposta){
			$("#resp").html(resposta);
			if(resposta=="OK"){
			    //$('#frm').submit();
			  //  document.location.reload(true);
			}else{
			    alert(resposta);
			}					
		    }
		);
		document.body.style.cursor = '';
	    }
	}
    });
}

function envioemail(inidnf,inval)
{    
    if(inval!='N'){
		var fqdt="Deseja realmente enviar o email?";
    }else{
		var fqdt="Não enviar o email?";
    }
    
    if(confirm(fqdt)) {
        CB.post({
            objetos: "_x_u_notafiscal_idnotafiscal="+inidnf+"&_x_u_notafiscal_enviaemailnfe="+inval
	    ,parcial:true
        });
    }    
}

function emailbol(inidnf,inval)
{
    CB.post({
		objetos: "_x_u_notafiscal_idnotafiscal="+inidnf+"&_x_u_notafiscal_emailboleto="+inval
		,parcial:true
    });
}

function boletopdf(inidcontapagar,vcheck){
    
   str1="_x_u_contapagar_idcontapagar="+inidcontapagar+"&_x_u_contapagar_boletopdf="+vcheck; 
   str2="&_y_u_notafiscal_idnotafiscal="+<?=$idnotafiscal?>+"&_y_u_notafiscal_emailboleto=Y";
   
   if(vcheck=='Y'){
     str = str1+ str2;
   }else{
      str = str1;  
   }
   
    CB.post({
        objetos: str
		,parcial:true
		,posPost: function(){
			if(vcheck=='Y'){
				vurl = "inc/boletophp/boleto_itau.php?idcontapagar="+inidcontapagar+"&geraarquivo=Y&gravaarquivo=Y";
				document.body.style.cursor = 'wait';		
				$.get(vurl, 
					function(resposta){
						$("#resp").html(resposta);
						if(resposta=="OK"){
							//$('#frm').submit();
							document.location.reload(true);
						}else{
							alert(resposta);
						}					
					}
				);
				document.body.style.cursor = '';
			}
		}
    });
}

function envioemaild(inidnotafiscal,vcheck,flag){
	
	if(flag == 1){
		var idemailvirtualconf = $("#emailunico2").val();
		var idempresa = $("#idempresaemail2").val();
		
		CB.post({
			objetos: "_x_u_notafiscal_idnotafiscal="+inidnotafiscal+"&_x_u_notafiscal_enviaemaildetalhe="+vcheck   
			,parcial:true
			,posPost: function(){
				if(vcheck=='Y'){
				vurl = "report/reldetalhenf.php?idnotafiscal="+inidnotafiscal+"&geraarquivo=Y&gravaarquivo=Y";
				document.body.style.cursor = 'wait';		
				$.get(vurl, 
					function(resposta){
					$("#resp").html(resposta);
					if(resposta=="OK"){
						//$('#frm').submit();
					   // document.location.reload(true);
					}else{
						alert(resposta);
					}					
					}
				);
				document.body.style.cursor = '';
				altremetenteemail(inidnotafiscal,idemailvirtualconf,'DETALHAMENTO',idempresa);
				}
			}
		});
	}else{
		if(flag == 2){
			var setemail = $("#setemail2").val();
			if(setemail == "1"){
				alert("É necessário escolher um remetente para o envio");
			}else{
				CB.post({
					objetos: "_x_u_notafiscal_idnotafiscal="+inidnotafiscal+"&_x_u_notafiscal_enviaemaildetalhe="+vcheck   
					,parcial:true
					,posPost: function(){
						if(vcheck=='Y'){
						vurl = "report/reldetalhenf.php?idnotafiscal="+inidnotafiscal+"&geraarquivo=Y&gravaarquivo=Y";
						document.body.style.cursor = 'wait';		
						$.get(vurl, 
							function(resposta){
							$("#resp").html(resposta);
							if(resposta=="OK"){
								//$('#frm').submit();
							   // document.location.reload(true);
							}else{
								alert(resposta);
							}					
							}
						);
						document.body.style.cursor = '';
						}
					}
				});
			}
		}
	}
}

function mostraInputFormapagamento(vthis){
	$(vthis).hide();
	$(vthis).siblings("label").hide()
	$(vthis).siblings("select").css("display","block");
}

function altdsimples(inidnf,inval){
    CB.post({
		objetos: "_x_u_notafiscal_idnotafiscal="+inidnf+"&_x_u_notafiscal_emaildsimplesnac="+inval
		,parcial:true
    });
}

function popalerta(){
	<?
	while (list($kobs, $vobs) = each($arrobs)) {
	?>
        alert(`<?=$vobs?>`);
	<?
	}
	?>
}//function popalerta(){

function mostraabertos(inchecked){
    if(inchecked){
		window.location = '?<?=$_SERVER["QUERY_STRING"]?>&mostraabertos=Y';
    }else{
		window.location = "?<?=str_replace('&mostraabertos=Y','',$_SERVER['QUERY_STRING'])?>";
    }
}

function mostramesmocnpj(inchecked){
    if(inchecked){
		window.location = '?<?=$_SERVER["QUERY_STRING"]?>&mostramesmocnpj=Y';
    }else{
		window.location = "?<?=str_replace('&mostramesmocnpj=Y','',$_SERVER['QUERY_STRING'])?>";
    }
}


function atualizaemail(vthis,inid){
    var email=$(vthis).val();
    CB.post({
        objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_emailnfe="+email
		,parcial:true
    });   
}
$(function(){
    $('.caixa').autosize();
});

function altlistateste(inid,inval,incampo){
	CB.post({
		objetos: "_x_u_notafiscal_idnotafiscal="+inid+"&_x_u_notafiscal_"+incampo+"="+inval
		,parcial:true
	});
}

function statusnfslote(inidnfslote){
    CB.post({
		objetos: "_x_u_nfslote_idnfslote="+inidnfslote+"&_x_u_nfslote_status=PENDENTE"
		,parcial:true
    });
}

function atualizanpedido(vthis,inidresultado){
	CB.post({
		objetos: "_x_u_resultado_idresultado="+inidresultado+"&_x_u_resultado_npedido="+$(vthis).val()
		,parcial:true
    });
}

if( $("[name=_1_u_notafiscal_idnotafiscal]").val() ){
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_notafiscal_idnotafiscal]").val()
		,tipoObjeto: 'notafiscal'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
	});
}

function altremetenteemail(idnotafiscal,idemailvirtualconf,tipoenvio,idempresa){
	CB.post({
        objetos: "_w_i_empresaemailobjeto_idempresa="+idempresa+"&_w_i_empresaemailobjeto_idemailvirtualconf="+idemailvirtualconf+"&_w_i_empresaemailobjeto_tipoenvio="+tipoenvio+"&_w_i_empresaemailobjeto_tipoobjeto=nfs&_w_i_empresaemailobjeto_idobjeto="+idnotafiscal
        ,parcial: true
    }) 
}

function alterainput(esconde,mostra){
	if(mostra=='descritivo2'){
		alert("Favor preencher também os campos quantidade, descrição e valor.");
	}

	if(mostra=='descritivo'){
		$("#grupoes").attr('cbvalue','');
		$("#grupoes").val('');
		$("#autoidtipoitem").html("<option value='' disabled selected hidden >Tipo</option>");
	}
    $("."+mostra).removeClass("hidden");
    $("."+esconde).addClass("hidden");
    
}

function liberaservico(){
      $("#insidprodserv").removeClass("hidden");
}
function flgmanual(vthis,idnotafiscal){
	CB.post({
        objetos: "_1_u_notafiscal_alteracaomanual="+vthis+"&_1_u_notafiscal_idnotafiscal="+idnotafiscal	
		,parcial: true
    }); 
}
function atualizavlitem(vthis,inidcontapagar){
    CB.post({
        objetos: "_atitem_u_contapagaritem_idcontapagaritem="+inidcontapagar+"&_atitem_u_contapagaritem_valor="+$(vthis).val() 
	,parcial: true	
    }); 
}

$(".changeacao").click(function(){   //mudanca de acao para enviar pelo cbpost de acordo com a acao feita no checkbox. - ALBT - 2021-05-07
	var vthis = $(this);
	var acao = vthis.attr("acao")
	if(vthis.is(":checked")){
		vthis.siblings().each((k,v) => {
			let num = $(v).attr('name').split("_")[1];
			let campo = $(v).attr('name').split("_")[2];
			$(v).attr('name',`_${num}_${acao}_notafiscalitens_${campo}`)
		});
	}else{
		vthis.siblings().each((k,v) => {
			let num = $(v).attr('name').split("_")[1];
			let campo = $(v).attr('name').split("_")[4];
			$(v).attr('name',`_${num}_${campo}`)
		});
	}
});

$(".selecttb").click(function(){
	var ref = $(this).attr("ref");
	let checkcontrol = $(this).attr("checkcontrol");
	var itens = $(`[checkcontrol=${checkcontrol}]`);
	
	itens.each((k,v)=>{
		var acao = $(v).attr('acao')
		if($(v).is(":checked")){
			$(v).prop('checked', false);
			$(v).siblings().each((i,j) => {
				let num = $(j).attr('name').split("_")[1];
				let campo = $(j).attr('name').split("_")[4];
				$(j).attr('name',`_${num}_${campo}`)
			});
		}else{
			$(v).prop('checked', true);
			$(v).siblings().each((i,j) => {
				let num = $(j).attr('name').split("_")[1];
				let campo = $(j).attr('name').split("_")[2];
				$(j).attr('name',`_${num}_${acao}_notafiscalitens_${campo}`)
			});
		}
	});
})


$(".inverteselecao").click(function(){    // cuida da inversao da selecao e trata o nome do arquivo para nao enviar por cbpost, mesmo quando salvar - ALBT 2021-05-07.
	var ref = $(this).attr("ref")
	var itens = $(ref).children().find("input:checkbox");
	itens.each((k,v)=>{
		var acao = $(v).attr('acao')
		if($(v).is(":checked")){
			$(v).prop('checked', false);
			$(v).siblings().each((i,j) => {
				let num = $(j).attr('name').split("_")[1];
				let campo = $(j).attr('name').split("_")[4];
				$(j).attr('name',`_${num}_${campo}`)
			});
		}else{
			$(v).prop('checked', true);
			$(v).siblings().each((i,j) => {
				let num = $(j).attr('name').split("_")[1];
				let campo = $(j).attr('name').split("_")[2];
				$(j).attr('name',`_${num}_${acao}_notafiscalitens_${campo}`)
			});
		}
	});
})


function preencheti(){
	$("#autoidtipoitem").empty();
	$.ajax({
                type: "get",
                url : "ajax/buscacontaitem.php",
                data: { idcontaitem : $("#grupoes").attr('cbvalue') },

                success: function(data){
						//$("#autoidtipoitem").empty();
                        $("#autoidtipoitem").html(data);
                },

                error: function(objxmlreq){
                        alert('Erro:<br>'+objxmlreq.status); 

                }
        })//$.ajax
    
}

var jContaItem = <?=getContaItem()?> || 0;
jContaItem = jQuery.map(jContaItem, function(o, id) {
    return {"label": o.contaitem, value:id+""}
});


/*if(jContaItem != 0){
	$("#grupoes").autocomplete({
		source: jContaItem
		,delay: 0    
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}    
		,select: function(event, ui){
			debugger			
			preencheti();				
		}
	});
}*/

function inovoitem(){
	var strCabecalho = "</strong>NOVO ITEM <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='salvaitem();'><i class='fa fa-circle'></i>Salvar</button></strong>";
	var  htmloriginal =$("#novoitem").html();
	var objfrm= $(htmloriginal);
			
	objfrm.find("#grupoesx").attr("name", "_grupoes_");
	objfrm.find("#grupoesx").attr("id", "grupoes");
	objfrm.find("#autoidtipoitemx").attr("id", "autoidtipoitem");

	objfrm.find("#compquantx").attr("id", "compquant");
	objfrm.find("#prompt_complementox").attr("id", "prompt_complemento");
	objfrm.find("#prompt_valorx").attr("id", "prompt_valor");
         
	
	CB.modal({
		titulo: strCabecalho,
		corpo: [objfrm],
		aoAbrir: function(vthis){
			criaAutocompleteTipo();
		}
	});
}


function criaAutocompleteTipo(){
	$("#grupoes").autocomplete({
		source: jContaItem
		,delay: 0    
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}    
		,select: function(event, ui){		
			preencheti();				
		}
	});
}
 <?}?>

 function abrecomissao(vinidnfitem){

	var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';
	CB.modal({
		url:"?_modulo=nfitemcomissao&_acao=u&vidnotafiscalitens=" + vinidnfitem + idempresa,
		header:"Comissão do(s) Iten(s)"
	});
}


function showModal(inval){
            var strCabecalho = "<strong>Nova Parcela <button id='cbSalvar' type='button'  style='margin-left:370px' class='btn btn-danger btn-xs' onclick='geracontapagar();'><i class='fa fa-circle'></i>Salvar</button></strong> ";
            $("#cbModalTitulo").html((strCabecalho));
           
            var  htmloriginal =$("#novaparcela").html();
            var objfrm= $(htmloriginal);

            objfrm.find("#formapagnovaparc").attr("name", "_modalnovaparcelacontapagar_idformapagamento_");
            objfrm.find("#valornovaparc").attr("name", "_modalnovaparcelacontapagar_valor_");
			objfrm.find("#valornovaparc").attr("value", inval);
			
            objfrm.find("#vencnovaparc").attr("name", "_modalnovaparcelacontapagar_datapagto_");
            
            // $("#cbModalCorpo").html(objfrm.html());
            //$('#cbModal').modal('show');

            CB.modal({
                corpo: objfrm.html(),
                titulo: strCabecalho
            })
        }
     

    function geracontapagar(){
		debugger;
    let valTipo = $("[name='_modalnovaparcelacontapagar_tipo_']:checked").val() || "C";
    let idpessoa = $("[name=_1_u_notafiscal_idpessoa]").attr('cbvalue');

     var str="_x9_i_contapagaritem_idformapagamento="+$("[name=_modalnovaparcelacontapagar_idformapagamento_]").val()+
            "&_x9_i_contapagaritem_status=PENDENTE&_x9_i_contapagaritem_parcela="+$('#parcela_parcelas').val()+"&_x9_i_contapagaritem_parcelas="+$('#parcela_parcelas').val()+"&_x9_i_contapagaritem_valor="+$("[name=_modalnovaparcelacontapagar_valor_]").val()+
             "&_x9_i_contapagaritem_datapagto="+$("[name=_modalnovaparcelacontapagar_datapagto_]").val()+
             "&_x9_i_contapagaritem_tipo="+valTipo+"&_x9_i_contapagaritem_visivel=S&_x9_i_contapagaritem_idpessoa="+idpessoa+
             "&_x9_i_contapagaritem_tipoobjetoorigem=notafiscal&_x9_i_contapagaritem_idobjetoorigem="+$("[name=_1_u_notafiscal_idnotafiscal]").val();
      
       CB.post({
               objetos: str
               ,parcial:true
               ,posPost: function(resp,status,ajax){
                   if(status="success"){
                       $("#cbModalCorpo").html("");
                       $('#cbModal').modal('hide');
                   }else{
                       alert(resp);
                   }
               }
           });
   }

   
   function atualizaconfpagar(){
            debugger;

            var strcbpost='';
            var i =0;
            $(".confscontapagar").each(function() {
                i++;
                console.log( $( this ).attr('datagerada'));
                $( this ).val($( this ).attr('datagerada'));
                strcbpost=strcbpost.concat("&_"+i+"_u_nfsconfpagar_datareceb="+$( this ).attr('datagerada')+"&_"+i+"_u_nfsconfpagar_idnfsconfpagar="+$( this ).attr('idnfsconfpagar'));

            });

            console.log(strcbpost);
            
            CB.post({
                objetos: strcbpost
                ,parcial:true  
                ,refresh:false
                ,posPost: function(resp,status,ajax){
                    CB.post();
                }
            });
            
	}


function altcheck(vtab,vcampo,vid,vcheck){
	CB.post({
		objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck   	
		,parcial:true
	}); 
}

function atualizaproporcao(vthis,vidnfconfpagar){
            var valor = 0;
                $(":input[name*=nfsconfpagar_proporcao]").each(function(){
                var string1 = $(this).val(); 
                var numero = parseFloat(string1.replace(',', '.'));
                valor=valor+numero;
            });
            console.log(valor);
            if(valor > 100){
                alert("A soma das proporções não deve passar de 100.");
                $(vthis).val('');
            }else{   
                CB.post({
                    objetos: "_pr_u_nfsconfpagar_idnfsconfpagar="+vidnfconfpagar+"&_pr_u_nfsconfpagar_proporcao="+$(vthis).val()
                    ,parcial: true        
                }) 
            } 
        }


		function nfconfpagar(inidnfconfpagar,li){
            var strCabecalho = 
            $("#cbModalTitulo").html((strCabecalho));

            var  htmloriginal =$("#"+li+"_editarnfconfpagar").html();
            var objfrm= $(htmloriginal);

            objfrm.find("#"+li+"_nfsconfpagar_idnfsconfpagar").attr("name", "_999_u_nfsconfpagar_idnfsconfpagar");
            objfrm.find("#"+li+"_nfsconfpagar_obs").attr("name", "_999_u_nfsconfpagar_obs");



            CB.modal({
                titulo: "</strong>Observações para pagamento <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='salvanfconfpagar();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                corpo: objfrm.html(),
                classe: 'cinquenta'
            });

        }
        function salvanfconfpagar(){

            var strcbpost="_999_u_nfsconfpagar_idnfsconfpagar="+$("[name=_999_u_nfsconfpagar_idnfsconfpagar]").val()+"&_999_u_nfsconfpagar_obs="+$("[name=_999_u_nfsconfpagar_obs]").val();

            console.log(strcbpost);
            CB.post({
                objetos: strcbpost
                ,parcial:true  
                ,msgSalvo:"Salvo"
                ,posPost: function(resp,status,ajax){
                    if(status="success"){
                        $("#cbModalCorpo").html("");
                        $('#cbModal').modal('hide');
                    }else{
                        alert(resp);
                    }
                }
            });

        }

	CB.on('prePost', function(){
        let alerta = false;
        let parcela, parcelaArray = '';
        hoje = new Date();
        virgula = '';
        const status = ['ABERTO', 'FECHADO', 'PENDENTE'];
        $(".datarecebparc").each((i, event)=> {
            dataCadastro = $(event).attr('value').split('/');
			parcela = $(event).attr('parcela');
            statusCI = $(event).attr('statusCI');
            datareceb = new Date(dataCadastro[2], dataCadastro[1] - 1, dataCadastro[0], 23,59,59);
            if((datareceb < hoje ? true : false) && status.indexOf(statusCI) >= 0){
                alerta = true;
				parcelaArray += virgula + parcela;
                virgula = ', ';
            }
        });
               
        if(alerta){
            if(!confirm(`A data de Recebimento da(s) parcela(s) ${parcelaArray} está(ão) \nanterior(es) a data atual.\n\n Deseja continuar?`)){
                return {objetos:{},msgSalvo:false,refresh:false}
            }
        }
    });
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
