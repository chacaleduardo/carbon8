<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


if($_POST){
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "mailfila";
$pagvalcampos = array(
	"idmailfila" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from mailfila where idmailfila = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$_sql = "select ifnull(usuario,nome) as usuario from pessoa where idpessoa = ".$_1_u_mailfila_idpessoa." and status = 'ATIVO' limit 1";
$_res = d::b()->query($_sql) or die("erro ao buscar usuario de envio de email: ".$_sql);
//die($_sql);
$_r = mysql_fetch_assoc($_res);

switch($_1_u_mailfila_tipoobjeto){
    case "cotacao": $tipoobjeto = "Cotação"; break;
    case "cotacaoaprovada": $tipoobjeto = "Cotação Aprovada"; break;
    case "detalhamento": $tipoobjeto = "Detalhamento"; break;
    case "nfp": $tipoobjeto = "Nota Fiscal Produto"; break;
    case "nfs": $tipoobjeto = "Nota Fiscal Serviço"; break;
    case "orcamentoprod": $tipoobjeto = "Orçamento Produto"; break;
    case "orcamentoserv": $tipoobjeto = "Orçamento Serviço"; break;
    case "recuperasenha": $tipoobjeto = "Recuperar Senha"; break;
    case "comunicacaoext": $tipoobjeto = "Resultado Oficial"; break;
	default: $tipoobjeto = ""; break;
}

function getJsonEmailTipoObjeto($idobjeto,$tipoobjeto,$idmailfila,$idsubtipoobjeto,$comparador,$strtipoobjeto){
	global $JSON;

	if($tipoobjeto == 'cotacao' || $tipoobjeto == 'cotacaoaprovada'){
		$select = ", o.idobjetojson, o.versaoobjeto";
		$join = "left join objetojson o on (m.idsubtipoobjeto = o.idsubtipoobjeto and o.subtipoobjeto = 'nf' and o.tipoobjeto = '".$tipoobjeto."' and o.idobjetoext = m.idenvio and tipoobjetoext = 'mailfila')";
	}else{
		$select = "";
		$join = "";
	}
	

	$_xql = "select m.*,p.usuario,date_format(m.criadoem,'%d/%m/%Y %H:%i:%s') as criadoem1, m.criadoem ".$select."
			from mailfila m JOIN pessoa p ON m.idpessoa = p.idpessoa ".$join."
			where m.idobjeto = ".$idobjeto." 
				and m.tipoobjeto = '".$tipoobjeto."' 
				and m.idsubtipoobjeto ".$comparador." '".$idsubtipoobjeto."'
				and m.idmailfila != ".$idmailfila." 
				and m.remover = 'N'
				".getidempresa('m.idempresa','envioemail')."
			order by m.criadoem asc";
	$_xes = d::b()->query($_xql) or die("erro ao buscar emails relacionados: ".$_xql);
	
	$nres = mysql_num_rows($_xes);
	//echo "<!-- \n".$sql."\n -->";
	if($nres == 0){
		return 0;
	}else{
		$arrtmp=array();
		$i=0;
		while($_x = mysql_fetch_assoc($_xes))
		{
			$_sql = "select message from mailfilalog where queueid = '".trim($_x["queueid"])."' and datetime >= '".$_x["criadoem"]."' and destinatario = '".trim($_x["destinatario"])."' order by idmailfilalog desc limit 1";
			$_res = mysql_query($_sql) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sql);
			$_nres = mysql_num_rows($_res);
			$_row=mysqli_fetch_assoc($_res);
			
			if($_nres == 0 or empty($_row["message"])){
				$respostaservidor = "Não há mensegem de resposta";
			}else{
				$respostaservidor = str_replace('"',"",$_row["message"]);
				$respostaservidor = str_replace("'","",$respostaservidor);
			}
			
			switch($_x["status"]){
				case 'EM FILA': $cor = "style = 'background-color: #a0a2a1;border-left:3px solid #6f7170;border-right:3px solid #6f7170;'"; break;
				case 'ADIADO': $cor = "style = 'background-color: #ffe8a1;border-left:3px solid #ffcb29;border-right:3px solid #ffcb29;'"; break;
				case 'NAO ENVIADO': $cor = "style = 'background-color: #f1b0b7;border-left:3px solid #e35f6d;border-right:3px solid #e35f6d;'"; break;
				case 'ENVIADO': $cor = "style = 'background-color: #8fd19e;border-left:3px solid #3b914f;border-right:3px solid #3b914f;'"; break;
				default: $cor = ""; break;
			}

			$arrtmp[$i]["idmailfila"]=$_x["idmailfila"];
			$arrtmp[$i]["remetente"]=$_x["remetente"];
			$arrtmp[$i]["destinatario"]= trim($_x["destinatario"]);
			$arrtmp[$i]["usuario"]= $_x["usuario"];
			$arrtmp[$i]["queueid"]= $_x["queueid"];
			$arrtmp[$i]["status"]=$_x["status"];
			$arrtmp[$i]["criadoem"]= $_x["criadoem1"];
			$arrtmp[$i]["criadoemoriginal"]= $_x["criadoem"];
			$arrtmp[$i]["tipoobjeto"]= $strtipoobjeto;
			$arrtmp[$i]["cor"]= $cor;
			$arrtmp[$i]["respostaservidor"]= $respostaservidor;
			if($tipoobjeto == 'cotacao' || $tipoobjeto == 'cotacaoaprovada'){
				$arrtmp[$i]["idobjetojson"]= $_x["idobjetojson"];
				$arrtmp[$i]["versaoobjeto"]= $_x["versaoobjeto"];
			}
			$i++;
		}
		return $JSON->encode($arrtmp);
	}
}

$jEmailEnvio = getJsonEmailTipoObjeto($_1_u_mailfila_idobjeto,$_1_u_mailfila_tipoobjeto,$_1_u_mailfila_idmailfila,$_1_u_mailfila_idsubtipoobjeto,"=",$tipoobjeto);
?>
<style>
	i{
		cursor:pointer;
	}
	.popover{
			max-width: 95%;
			border-color: gray;
	}
</style>
<div class="row">
	<div class="col-lg-12">
	<?
		switch($_1_u_mailfila_status){
			case 'EM FILA': $cor = "style = 'background-color: #a0a2a1;border-left:3px solid #6f7170;border-right:3px solid #6f7170;'"; break;
			case 'ADIADO': $cor = "style = 'background-color: #ffe8a1;border-left:3px solid #ffcb29;border-right:3px solid #ffcb29;'"; break;
			case 'ENVIADO': $cor = "style = 'background-color: #8fd19e;border-left:3px solid #3b914f;border-right:3px solid #3b914f;'"; break;
			case 'NAO ENVIADO': $cor = "style = 'background-color: #f1b0b7;border-left:3px solid #e35f6d;border-right:3px solid #e35f6d;'"; break;
			default: $cor = ""; break;
		}
	?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<input name="_1_u_mailfila_idobjeto" type="hidden"	value="<?=$_1_u_mailfila_idobjeto?>">
					<div class="col-sm-12">
						<div class="col-sm-3">
							Id: <strong><label class="alert-warning"><?=$_1_u_mailfila_idmailfila?><?if($_1_u_mailfila_link != 'N'){?><i class="fa fa-bars fa-1x hoverazul btn-sm pointer" onclick="janelamodal('<?=$_1_u_mailfila_link?>');"></i><?}?></label></strong>
						</div>
						<div class="col-sm-3">
							Identificador de Envio: <strong><label class="alert-warning"><?=$_1_u_mailfila_idenvio?></label></strong>
						</div>
						<div class="col-sm-2"></div>
						<div class="col-sm-2"><?if($_1_u_mailfila_remover == 'Y'){?><strong><label class="alert-danger" style="padding: 4px;border-radius: 3px;box-shadow: 2px 2px 1px rgba(0,0,0,.05);">Email Cancelado!</label></strong><?}?></div>
						<div class="col-sm-2" style="text-align: end;">
							<?if(!empty($_1_u_mailfila_conteudoemail)){$auxconteudoemail = 1;?><i title="Ver conteúdo do email" class="fa fa-print fa-1x hoverazul btn-sm pointer" onclick="exibeconteudoemail();"></i><?}else{$auxconteudoemail = 0;}?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-body" style="padding-top: 10px !important;">
				<table class="table" id="table_principal">
					<thead>
						<tr>
							<td><b>Criado em</b></td>
							<td><b>Remetente</b></td>
							<td><b>Destinatário</b></td>
							<td><b>Enviado Por</b></td>
							<td><b>QueueID</b></td>	
							<td><b>Reenviar</b></td>
							<td><b>Cancelar</b></td>
							<td><b>Tipo Envio</b></td>
							<?if($_1_u_mailfila_tipoobjeto == "cotacao" or $_1_u_mailfila_tipoobjeto == "cotacaoaprovada"){?>
								<td><b>Anexo</b></td>
							<?}?>
							<td><b>Status</b></td>	
						</tr>
					</thead>
					<tbody id="emailrelenvio">
					<?
						$_sqlx = "select message from mailfilalog where queueid = '".trim($_1_u_mailfila_queueid)."' and datetime >= date_format(str_to_date('".$_1_u_mailfila_criadoem."', '%d/%m/%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s') and destinatario = '".trim($_1_u_mailfila_destinatario)."' order by idmailfilalog desc limit 1";
						$_resx = mysql_query($_sqlx) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sqlx);
						$_nresx = mysql_num_rows($_resx);
						$_rowx=mysqli_fetch_assoc($_resx);

						echo "<!--";
						echo $_sqlx;
						echo "-->";
						
						if($_nresx == 0 or empty($_rowx["message"])){
							$respostaservidor = "Não há mensegem de resposta";
						}else{
							$respostaservidor = str_replace('"',"",$_rowx["message"]);
							$respostaservidor = str_replace("'","",$respostaservidor);
						}

					?>
						<tr class='idmailfila principal' <?=$cor?> >
							<td class='criadoem' attrdate="<?=$finalData?>"><b><?=$_1_u_mailfila_criadoem?></b></td>
							<td><?=$_1_u_mailfila_remetente?></td>
							<td><?=$_1_u_mailfila_destinatario?></td>
							<td><?=$_r["usuario"]?></td>
							<td data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='<?=$respostaservidor?>'>
								<?=$_1_u_mailfila_queueid?>
							</td>
							<td style="width:1%; text-align: center;"><?if(($_1_u_mailfila_status != "EM FILA") and (!empty($_1_u_mailfila_conteudoemail))){?><i class='fa fa-envelope' title='Reenviar Email' onclick='reenviaremail(<?=$_1_u_mailfila_idmailfila?>)'><?}else{?><span>-</span><?}?></td>
							<td style="width:1%; text-align: center;"><?if(($_1_u_mailfila_status == "NAO ENVIADO" or $_1_u_mailfila_status == "ADIADO") and $_1_u_mailfila_remover == 'N'){?><i class="fa fa-trash" title="Cancelar" onclick="removerdalista(<?=$_1_u_mailfila_idmailfila?>)"><?}else{?><span>-</span><?}?></td>
							<?
								$data = explode(" ", $_1_u_mailfila_criadoem);
								$newData = implode('-', array_reverse(explode('/', $data[0])));
								$finalData = $newData . " " . $data[1];
							?>
							
							<td><b><?=$tipoobjeto?></b></td>
							<?if($_1_u_mailfila_tipoobjeto == 'cotacao' || $_1_u_mailfila_tipoobjeto == 'cotacaoaprovada'){
								$qr = "SELECT idobjetojson, versaoobjeto 
										 FROM objetojson 
										WHERE idsubtipoobjeto = ".$_1_u_mailfila_idsubtipoobjeto." AND subtipoobjeto = 'nf' 
										  AND tipoobjeto = '".$_1_u_mailfila_tipoobjeto."' AND idobjetoext = '".$_1_u_mailfila_idenvio."' AND tipoobjetoext = 'mailfila'";
								$rs = d::b()->query($qr);
								if(mysqli_num_rows($rs) > 0){
									$rw = mysqli_fetch_assoc($rs);
									?>
									<td align="center">
										<i class="fa fa-paperclip pointer" onclick="mostrarCotacao('cotacao', <?=$rw['idobjetojson']?>, <?=$rw['versaoobjeto']?>, <?=$_1_u_mailfila_idobjeto?>, <?=$_1_u_mailfila_idsubtipoobjeto?>)"></i>
									</td>
								<?}else{?>
									<td align="center">
										-
									</td>
								<?}?>
								
							<?}?>
							
							<td><b><?=$_1_u_mailfila_status?></b></td>
						</tr>

						<?
						if($_1_u_mailfila_tipoobjeto == "cotacao" or $_1_u_mailfila_tipoobjeto == "cotacaoaprovada"){
							$rel = ($_1_u_mailfila_tipoobjeto == "cotacao")? "cotacaoaprovada" : "cotacao";
							$reltitle = ($_1_u_mailfila_tipoobjeto == "cotacao")? "Cotação Aprovada" : "Cotação";
							$sqlrel = "SELECT m.idmailfila,m.remetente,m.destinatario,m.queueid,m.status,m.criadoem, if(p.usuario is null, p.nomecurto, p.usuario) as usuario, m.remover,m.conteudoemail,
							m.idobjeto,m.idsubtipoobjeto,m.idenvio,m.criadoem,o.idobjetojson,o.versaoobjeto, m.tipoobjeto
											FROM mailfila m JOIN pessoa p ON (m.idpessoa = p.idpessoa)
											LEFT JOIN objetojson o on (m.idsubtipoobjeto = o.idsubtipoobjeto and o.subtipoobjeto = 'nf' and o.tipoobjeto = 'cotacaoaprovada' and o.idobjetoext = m.idenvio)
											WHERE m.tipoobjeto = '".$rel."' AND m.subtipoobjeto = '".$_1_u_mailfila_subtipoobjeto."'
											AND m.idsubtipoobjeto = ".$_1_u_mailfila_idsubtipoobjeto." and m.idobjeto = ".$_1_u_mailfila_idobjeto."
											ORDER BY m.criadoem ASC";
							$resrel = d::b()->query($sqlrel) or die("Falha ao buscar emails relacionados");
							while($rrel = mysqli_fetch_assoc($resrel)){
								
								switch($rrel["status"]){
									case 'EM FILA': $cor = "style = 'background-color: #a0a2a1;border-left:3px solid #6f7170;border-right:3px solid #6f7170;'"; break;
									case 'ADIADO': $cor = "style = 'background-color: #ffe8a1;border-left:3px solid #ffcb29;border-right:3px solid #ffcb29;'"; break;
									case 'ENVIADO': $cor = "style = 'background-color: #8fd19e;border-left:3px solid #3b914f;border-right:3px solid #3b914f;'"; break;
									case 'NAO ENVIADO': $cor = "style = 'background-color: #f1b0b7;border-left:3px solid #e35f6d;border-right:3px solid #e35f6d;'"; break;
									default: $cor = ""; break;

									
								$_sqlx = "select message from mailfilalog where queueid = '".trim($rrel["queueid"])."' and datetime >= date_format(str_to_date('".$rrel["criadoem"]."', '%d/%m/%Y %H:%i:%s'), '%Y-%m-%d %H:%i:%s') and destinatario = '".trim($rrel["destinatario"])."' order by idmailfilalog desc limit 1";
								$_resx = mysql_query($_sqlx) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sqlx);
								$_nresx = mysql_num_rows($_resx);
								$_rowx=mysqli_fetch_assoc($_resx);

								echo "<!--";
								echo $_sqlx;
								echo "-->";
								
								if($_nresx == 0 or empty($_rowx["message"])){
									$respostaservidor = "Não há mensegem de resposta";
								}else{
									$respostaservidor = str_replace('"',"",$_rowx["message"]);
									$respostaservidor = str_replace("'","",$respostaservidor);
								}
								
								}
								?>
								<tr class="idmailfila" <?=$cor?>>
									<td class="criadoem" attrdate = "<?=$rrel["criadoem"]?>"><?=dmahms($rrel["criadoem"])?></td>
									<td><?=$rrel["remetente"]?></td>
									<td><?=$rrel["destinatario"]?></td>
									<td><?=$rrel["usuario"]?></td>
									<td  data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='<?=$respostaservidor?>'>
										<a href="?_modulo=envioemail&_acao=u&idmailfila=<?=$rrel["idmailfila"]?>" target="_blank"><?=$rrel["queueid"]?></a>
									</td>
									<td style="width:1%; text-align: center;"><?if(($rrel["status"] != "EM FILA") and (!empty($rrel["conteudoemail"]))){?><i class='fa fa-envelope' title='Reenviar Email' onclick='reenviaremail(<?=$rrel["idmailfila"]?>)'><?}else{?><span>-</span><?}?></td>
									<td style="width:1%; text-align: center;"><?if(($rrel["status"] == "NAO ENVIADO" or $rrel["status"] == "ADIADO") and $rrel["remover"] == 'N'){?><i class="fa fa-trash" title="Cancelar" onclick="removerdalista(<?=$rrel["idmailfila"]?>)"><?}else{?><span>-</span><?}?></td>
									<td><b><?=$reltitle?></b></td>
									<?if(!empty($rrel["idobjetojson"])){?>
										<td style="width:1%; text-align: center;">
											<i class="fa fa-paperclip pointer" onclick="mostrarCotacao('cotacao', <?=$rrel['idobjetojson']?>, <?=$rrel['versaoobjeto']?>, <?=$_1_u_mailfila_idobjeto?>, <?=$_1_u_mailfila_idsubtipoobjeto?>)"></i>
										</td>
									<?}else{?>
										<td style="width:1%; text-align: center;">
											-
										</td>
									<?}?>
									
									<td><b><?=$rrel["status"]?><b></td>
								</tr>
							<?}
						}?>
					</tbody>
				</table>
				<?if(!empty($_1_u_mailfila_conteudoemail)){?>
				<table>
					<tr>
						<td><i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="mostramodalemail();" title="Novo Email"></i></td>
					</tr>
				</table>
				<?}?>
			</div>
		</div>
	</div>


	<?if($_1_u_mailfila_tipoobjeto == "cotacao" or $_1_u_mailfila_tipoobjeto == "cotacaoaprovada"){
		$sqleo = "SELECT u.*, o.idobjetojson, o.versaoobjeto FROM (SELECT h.criadoem,s.statustipo as log, 'Cotação' as tipo, h.idmodulo as idnf from fluxostatushist h 
		join fluxostatus fs on (h.idfluxostatus = fs.idfluxostatus) 
		join "._DBCARBON."._status s on (s.idstatus = fs.idstatus)
		where h.idmodulo = ".$_1_u_mailfila_idsubtipoobjeto." 
		and h.modulo = 'nfentrada' and s.statustipo = 'RESPONDIDO'
		union all
		select n.visualizadoem as criadoem, 'VISUALIZADO' as log, 'Cotação Fornecedor' as tipo, n.idnf
		from nf n where n.visualizadoem is not null and idnf = ".$_1_u_mailfila_idsubtipoobjeto.") as u 
		left join objetojson o on (o.idsubtipoobjeto = u.idnf and o.subtipoobjeto = 'nf' and o.tipoobjeto = 'cotacaoforn' and (LEFT(o.criadoem,16) = LEFT(u.criadoem,16)))
		order by u.criadoem asc";
		$reseo=d::b()->query($sqleo) or die("Erro ao buscar emails de cotação APROVADA sql=".$sqleo);
		$qtdeo= mysqli_num_rows($reseo);
		if($qtdeo>0){
			$arrRespForn = array();
			$i = 0;
			while($roweo= mysqli_fetch_assoc($reseo)){
				$arrRespForn[$i]["log"] = $roweo["log"];
				$arrRespForn[$i]["criadoem"] = $roweo["criadoem"];
				$arrRespForn[$i]["criadoemdmahms"] = dmahms($roweo["criadoem"]);
				$arrRespForn[$i]["tipo"] = $roweo["tipo"];
				$arrRespForn[$i]["idobjetojson"] = $roweo["idobjetojson"];
				$arrRespForn[$i]["versaoobjeto"] = $roweo["versaoobjeto"];
				$i++;
				?>	  
				<!--a href="javascript:janelamodal('?_modulo=cotacaoforn&_acao=u&idnf=<?=$_1_u_mailfila_idsubtipoobjeto?>')"><?=$roweo["log"]?></a-->
			<?}
			$arrRespForn = $JSON->encode($arrRespForn);
		}else{
			$arrRespForn = 0;
		}?>
	<?}?>

	<div class="col-lg-12">
		<?
		if(!empty($_1_u_mailfila_idmailfila)){// trocar p/ cada tela a tabela e o id da tabela
			$_idModuloParaAssinatura = $_1_u_mailfila_idmailfila; // trocar p/ cada tela o id da tabela
			require 'viewAssinaturas.php';
		}
			$tabaud = "mailfila"; //pegar a tabela do criado/alterado em antigo
			require 'viewCriadoAlterado.php';
		?>
	</div>
</div>
<div style="display:none;" id="conteudoemail">
	<?
		$rc = unserialize(base64_decode($_1_u_mailfila_conteudoemail));
		$assunto = $rc["assunto"];
		$corpo = $rc["mensagem"];
	?>
	<div class="col-sm-12">
		<table id="emailadd" style="width:100%;">
			<tr>
				<td>
					<h4><b>Novo Email:</b></h4>
				</td>
			<tr>
			<tr>
				<td>
					<b>De:</b>
				</td>
			</tr>
			<tr>
				<td>
					<?=$_1_u_mailfila_remetente?>
				</td>
			</tr>
			<tr>
				<td>
					<b>Para:</b>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="destinatarioadd" value="" placeholder="Exemplo: email@dominio.com">
				</td>
				<td style="width: 1%;">
					<i class="fa fa-paper-plane fa-2x cinzaclaro hoverazul pointer" title="Enviar novo email" aria-hidden="true" onclick="reenviaremail(<?=$_1_u_mailfila_idmailfila?>,'destinatarioadd')"></i>
				</td>
			</tr>
			<tr>
				<td colspan="2"><hr></td>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<td>
					<h4><b>Assunto: <?=$assunto?></b></h4>
					<hr>
				</td>
			</tr>
			<tr>
				<td>
					<?=$corpo?>
				</td>
			</tr>
			<?if(array_key_exists("anexos",$rc)){?>
			<tr>
				<td>
					<hr>
					<h4><b>Anexo(s):</b></h4>
				</td>
			</tr>
			<tr>
				<td>
					
					<ul class="list-group">
						<?foreach ($rc["anexos"] as $key => $value) {
							$arq = explode("/", $value);
							if($_1_u_mailfila_tipoobjeto =='comunicacaoext'){
								$caminho = str_replace("/var/www/carbon8", "..", $value);
							}else{
								$caminho = str_replace("/var/www/laudo/tmp", "../laudotmp", $value);
							}

						?>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<a href="<?=$caminho?>" target="_blank"><?=end($arq)?></a>
								<span><i class="fa fa-file-text" aria-hidden="true"></i></span>
							</li>
						<?}?>
					</ul>
				</td>
			</tr>
			<?}?>
		</table>
	</div>
</div>

<script>
	$("#cbSalvar").addClass("disabled");
	
	
	<?if(!empty($_1_u_mailfila_idmailfila)){?>
	
		var jEmailEnvio = <?=$jEmailEnvio?>;
		
		if(jEmailEnvio !== 0)
		{
			construirTabela(jEmailEnvio,'emailrelenvio',<?=$auxconteudoemail?>);	
			<?if($_1_u_mailfila_tipoobjeto == "cotacao" or $_1_u_mailfila_tipoobjeto == "cotacaoaprovada"){?>
			construirRespostaforn();
			<?}?>
		}

		<?if($_1_u_mailfila_tipoobjeto == "cotacao" or $_1_u_mailfila_tipoobjeto == "cotacaoaprovada"){?>
			function construirRespostaforn(){
				var jArrRespForn = <?=$arrRespForn?>;
				if(jArrRespForn != 0){
					let auxlist = [...jArrRespForn];
					for (var oResp of jArrRespForn) {
						$(".idmailfila .criadoem").each((i,o) => {
							let data1 = new Date($(o).attr('attrdate'));
							let data2 = new Date(oResp.criadoem);
							if(data2 < data1){
								let anexo = "-";
								if(oResp.idobjetojson){
									anexo = `<i class="fa fa-paperclip pointer" onclick="mostrarCotacao('cotacaoforn', ${oResp.idobjetojson}, ${oResp.versaoobjeto}, <?=$_1_u_mailfila_idobjeto?>, <?=$_1_u_mailfila_idsubtipoobjeto?>)"></i>`
								}
								let $obj = `<tr>
												<td>${oResp.criadoemdmahms}</td>
												<td>-</td>
												<td>-</td>
												<td>-</td>
												<td>-</td>
												<td style="width:1%; text-align: center;">-</td>
												<td style="width:1%; text-align: center;">-</td>
												<td><b>${oResp.tipo}</b></td>
												<td align="center">${anexo}</td>
												<td><a href="javascript:janelamodal('?_modulo=cotacaoforn&_acao=u&idnf=<?=$_1_u_mailfila_idsubtipoobjeto?>')"><b>${oResp.log}</b></a></td>
											</tr>`;
								$($obj).insertBefore($(o).parent());
								auxlist.shift();
								return false;
							}
						});
					}
					
					if(auxlist.length > 0){
						for (let oResp of auxlist) {
							let anexo = "-";
							if(oResp.idobjetojson){
								anexo = `<i class="fa fa-paperclip pointer" onclick="mostrarCotacao('cotacaoforn', ${oResp.idobjetojson}, ${oResp.versaoobjeto}, <?=$_1_u_mailfila_idobjeto?>, <?=$_1_u_mailfila_idsubtipoobjeto?>)"></i>`
							}
							let $obj = `<tr>
											<td>${oResp.criadoemdmahms}</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td style="width:1%; text-align: center;">-</td>
											<td style="width:1%; text-align: center;">-</td>
											<td><b>${oResp.tipo}</b></td>
											<td align="center">${anexo}</td>
											<td><a href="javascript:janelamodal('?_modulo=cotacaoforn&_acao=u&idnf=<?=$_1_u_mailfila_idsubtipoobjeto?>')"><b>${oResp.log}</b></a></td>
										</tr>`;
							$("#emailrelenvio").append($obj);
						}
					}
				}
			}
			
		<?}?>
		
		function construirTabela(objeto,identificador,auxconteudoemail,ordenacao = null,ordem = null){
			if(ordenacao && ordem){
				$("#"+identificador).html("");
				objeto.sort(propComparator(ordenacao,ordem));
			}
			var tabela="";
			var acrescentatd = "";
			$.each(objeto, function(i, item) {							
				let reenviar = "";
				let cancelar = "";
				let anexo = "";
				if(item.status != "EM FILA" && auxconteudoemail == 1){
					reenviar += "<td style='width:1%; text-align: center;'><i class='fa fa-envelope' title='Reenviar Email' onclick='reenviaremail("+item.idmailfila+")'></td>";
				}else{
					reenviar += "<td style='width:1%; text-align: center;'><span>-</span></td>";
				}
				
				if(item.status == "NAO ENVIADO" || item.status == "ADIADO"){
					cancelar +="<td style='width:1%; text-align: center;'><i class='fa fa-trash' title='Cancelar' onclick='removerdalista("+item.idmailfila+")'></td></tr>";
				}else{
					cancelar += "<td style='width:1%; text-align: center;'><span>-</span></td>";
				}

				if(item.tipoobjeto == "Cotação" || item.tipoobjeto == "Cotação Aprovada"){
					if(item.idobjetojson){
						anexo += `<td align='center'><i class='fa fa-paperclip pointer' onclick="mostrarCotacao('cotacao', ${item.idobjetojson}, ${item.versaoobjeto}, <?=$_1_u_mailfila_idobjeto?>, <?=$_1_u_mailfila_idsubtipoobjeto?>)"></i></td>`;
					}else{
						anexo += "<td>-</td>";
					}
				}

				tabela += `<tr class='idmailfila' ${item.cor}>
							<td class='criadoem' attrdate='${item.criadoemoriginal}'>${item.criadoem}</td>
							<td>${item.remetente}</td>
							<td>${item.destinatario}</td>
							<td>${item.usuario}</td>
							<td data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='${item.respostaservidor}'><a href='?_modulo=envioemail&_acao=u&idmailfila=${item.idmailfila}' target='_blank'>${item.queueid}</a></td>
							${reenviar}
							${cancelar}
							<td>${item.tipoobjeto}</td>
							${anexo}
							<td>${item.status}</td>
						</tr>`;
				
			});
			$(tabela).insertAfter($(".principal"));
		}
		
		function propComparator(prop,ordem) {
			switch(ordem){
				case 'asc':
					return function(a, b) {
						if (a[prop] > b[prop]) {
							return 1;
						}
						if (a[prop] < b[prop]) {
							return -1;
						}
						return 0;
					}
				case 'desc':
					return function(a, b) {
						if (a[prop] < b[prop]) {
							return 1;
						}
						if (a[prop] > b[prop]) {
							return -1;
						}
						return 0;
					}
				default:
					return function(a, b) {
						if (a[prop] > b[prop]) {
							return 1;
						}
						if (a[prop] < b[prop]) {
							return -1;
						}
						return 0;
					}
			}
		}
			
		function removerdalista(idmailfila){
			if(idmailfila != 0){
				if(confirm("Deseja realmente remover o email?")){
					CB.post({
						objetos: "_x_u_mailfila_idmailfila="+idmailfila+"&_x_u_mailfila_remover=Y"
						,parcial:true
					});
				}
			}else{
				alertErro("Não foi possível remover esse email");
				console.warn("Parâmetros da função 'removerdalista' inválidos");
			}
		}
		
		function reenviaremail(idmailfila,destinatario = null){
			if(confirm("Deseja realmente reenviar o email?")){
				if(!destinatario){
					$.ajax({
						type: "get",
						url : "ajax/reenvioemail.php",
						data: { 
							idmailfila: idmailfila,
							usuario: "<?=$_SESSION["SESSAO"]["USUARIO"]?>",
							idusuario: <?=$_SESSION["SESSAO"]["IDPESSOA"]?>
						},
						success: function(data, status, jqXHR){
							switch(data){
								case "-1":
									alertAtencao("Destinatário do email está vazio");
									break;
								case "0":
									alertAtencao("Não há registro do conteúdo do email para reenvio");
									break;
								case "1":
									alertAzul("O email será reenviado em breve", "", 1000)
									break;
								case "2":
									alertAtencao("Parâmetros para reenvio inválidos")
									break;
								default:
									alertErro("Houve um problema inesperado com o reenvio,<br> favor entrar em contato com um responsável");
									break;
							}
						},
						error: function(objxmlreq){
							alertErro('Erro no reenvio do email:<br>'+objxmlreq.status);
						}
					});
				}else{
					var aux = $("input[name='destinatarioadd']")[1];
					var destinatarioenvio = $(aux).val();
					if(destinatarioenvio != null && destinatarioenvio != undefined && destinatarioenvio != ""){
						$.ajax({
							type: "get",
							url : "ajax/reenvioemail.php",
							data: { 
								idmailfilareferencia: idmailfila,
								destinatario: destinatarioenvio,
								usuario: "<?=$_SESSION["SESSAO"]["USUARIO"]?>",
								idusuario: <?=$_SESSION["SESSAO"]["IDPESSOA"]?>
							},
							success: function(data, status, jqXHR){
								switch(data){
									case "-1":
										alertAtencao("Destinatário do email está vazio");
										break;
									case "0":
										alertAtencao("Não há registro do conteúdo do email para reenvio");
										break;
									case "1":
										alertAzul("O email será reenviado em breve", "", 1000);
										break;
									case "2":
										alertAtencao("Parâmetros para reenvio inválidos");
										break;
									default:
										alertErro("Houve um problema inesperado com o reenvio,<br> favor entrar em contato com um responsável");
										break;
								}
								$(aux).val("");
							},
							error: function(objxmlreq){
								alertErro('Erro no reenvio do email:<br>'+objxmlreq.status);
							}
						});
					}else{
						alertAtencao("Insira um endereço de email");
					}
				}
			}
		}
			
		function exibeconteudoemail(identificador = null){
			if(identificador){
				CB.modal({
					header:"Conteúdo do email",
					titulo: "Conteúdo do email",
					corpo:$("#"+identificador).html(),
					classe: 'cinquenta'
				});
			}else{
				$("#emailadd").css("display","none");
				CB.modal({
					header:"Conteúdo do email",
					titulo: "Conteúdo do email",
					corpo:$("#conteudoemail").html(),
					classe: 'cinquenta'
				});
			}
		}
		
		function mostrarCotacao(tipoobjeto, idobjetojson, versaoobjeto, orcamento, nf)
		{
			let cotacaotabela, tipo;

			if(tipoobjeto == 'cotacao'){
				tipo = "Enviada";
			}else{
				tipo = "Recebida";
			}
			cotacaotabela = `<div class="webui-popover-content">`;
			cotacaotabela += `<table>`;
			cotacaotabela += `<tr><td>`;
			cotacaotabela += `<a class="pointer" onclick="janelamodal('report/${tipoobjeto}.php?idobjetojson=${idobjetojson}');">Orçamento ${orcamento} (Cotação ${nf}): ${tipo} - Versão ${versaoobjeto}</a>`;
			cotacaotabela += `</tr></td>`;
			cotacaotabela += `</table>`;

			CB.modal({
				header:"Cotação",
				titulo: "Cotação",
				corpo:$(cotacaotabela).html(),
				classe: 'trinta'
			});
		}
		
		function mostramodalemail(){
			$("#emailadd").css("display","");
			CB.modal({
				header:"Envio email individual",
				titulo: "Envio email individual",
				corpo:$("#conteudoemail").html(),
				classe: 'cinquenta'
			});
		}
		
		$(document).ready(function(){
			$('[data-toggle="popover"]').popover({container: 'body'});   
		});
	<?}?>
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
<?
/*if(!empty($_1_u_tagtipo_idtagtipo)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_tagtipo_idtagtipo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "tagtipo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';*/
?>