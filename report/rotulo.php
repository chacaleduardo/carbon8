<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["idnf"])){
	die("IDNF não enviado");
}else if(empty($_GET["_modulo"])){
	die("Modulo não enviado");
}

$modulo = 'pedido';

$idsnf = explode(',',$_GET["idnf"]);?>
<html>

	<head>
		<title>Impressăo</title>
		<link href="../inc/css/bootstrap/css/bootstrap.css" media="all" rel="stylesheet" type="text/css" />
		<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />

		<style>
			.row {
				margin-left: 0px;
			}
		</style>
	</head>
	  <!-- Fazer teste de layout da etiqueta em http://labelary.com/viewer.html -->
<body >
	<?
	//consulta para trazer a impressora
	 $sql="select ip from tag 
			where varcarbon='_IMPRESSORA_LOGISTICA'
			-- ".getidempresa('idempresa',$modulo)."
			and ip is not null 
			and status=	'ATIVO'";
	$res=d::b()->query($sql) or die("Erro ao buscar impressora logistica: ".mysqli_error(d::b())." SQL: ".$sql);
	$qtd=mysqli_num_rows($res);
	if($qtd<1){
		die("Não encontrada impressora logistica em tags var carbon.");
	}
	$row=mysqli_fetch_assoc($res);
		define("_IP_IMPRESSORA_LOGISTICA",$row['ip']);  // DEFINE VARIAVEL COM IP CADASTRADO NA TAG DA IMPRESSORA

foreach ($idsnf as $idnf) {
		if (!empty($idnf))
		{
			$sqlinfo = "SELECT 
			n.idnf,
			n.nnfe,
			n.idtransportadora,
			n.qvol AS qvol,
			p.nome AS transportadora,
			`c`.`cidade` AS `cidade`,
			`e`.`uf` AS `uf`,
			emp.*,
			IF((`e`.`idendereco` IS NOT NULL),
				CONCAT(IFNULL(NULL, `e`.`logradouro`),
						' ',
						IFNULL(NULL, `e`.`endereco`),
						', ',
						IFNULL(NULL, `e`.`numero`),
						' - ',
						IFNULL(NULL, `e`.`complemento`) ),
				CONCAT(IFNULL(NULL, `e2`.`logradouro`),
						' ',
						IFNULL(NULL, `e2`.`endereco`),
						', ',
						IFNULL(NULL, `e2`.`numero`),
						' - ',
						IFNULL(NULL, `e2`.`complemento`),
						' - ')) AS `enderecototal`,
						e.cep,
						e.bairro,
						e.idpessoa,
						IFNULL(n.idendrotulo,e2.idendereco) as idendereco
			FROM
				(((((`nf` `n`
				JOIN empresa emp ON (emp.idempresa = n.idempresa)
				LEFT JOIN `endereco` `e` ON ((`e`.`idendereco` = `n`.`idendrotulo`)))
				LEFT JOIN `endereco` `e2` ON (((`e2`.`idpessoa` = `n`.`idpessoa`)
					AND (`e2`.`idtipoendereco` = 3)
					AND (`e2`.`status` = 'ATIVO'))))
				LEFT JOIN `nfscidadesiaf` `c` ON ((`e`.`codcidade` = `c`.`codcidade`)))
				LEFT JOIN `nfscidadesiaf` `c2` ON ((`e2`.`codcidade` = `c2`.`codcidade`)))
				LEFT JOIN `pessoa` `p` ON ((`p`.`idpessoa` = `n`.`idtransportadora`)))
			WHERE
				idnf = $idnf
			GROUP BY idnf";
			$rest=mysql_query($sqlinfo) or die("Erro ao buscar os rotulos sql:".$sqlinfo);
			$qtdinfo=mysql_num_rows($rest);


			$sqletiqueta = "SELECT e.*
							  FROM etiquetaobjeto ov JOIN "._DBCARBON."._modulo m ON (m.idmodulo = ov.idobjeto) AND ov.idobjeto = m.idmodulo AND ov.tipoobjeto = 'modulo'
							  JOIN  etiqueta e ON ov.idetiqueta = e.idetiqueta
							 WHERE m.modulo = '".$modulo."'";
			$retqt=mysql_query($sqletiqueta) or die("Erro ao buscar etiqueta:".$sqletiqueta);
			$rowetiqueta = mysqli_fetch_assoc($retqt);
			while ($rowinfo = mysql_fetch_assoc($rest))
			{
				$qvol = $rowinfo["qvol"];
				$transportadora =  $rowinfo["transportadora"];
				$nnfe = $rowinfo["nnfe"];
				$sql1="select e.logradouro,e.cep,e.endereco,e.numero,e.complemento,e.bairro,c.cidade,c.uf,e.obsentrega
							from endereco e left join nfscidadesiaf c on (c.codcidade = e.codcidade )
						where  e.idendereco=".$rowinfo['idendereco'];
				$res1=mysql_query($sql1) or die("Erro ao busca endereco sql:".$sql1);
				$qtd1=mysql_num_rows($res1);
				$row1=mysql_fetch_assoc($res1);
				$_sql = "SELECT logosis
						FROM empresa
						WHERE idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];
				$_res=d::b()->query($_sql) or die("erro ao buscar imagem do relatório da empresa sql=".$_sql);
				$_r=mysqli_fetch_assoc($_res);
				$tamlogo = "<img style='width:70px;' src='".$_r["logosis"]."' border='0'>";
				$q=1;
				while( $q <= $qvol){
					$etiqueta = $rowetiqueta['cod'];
					?>
							<div style="margin-left:30px; margin-right:20px">
							<div class="row" style="width: 320px; height: 490px; border:solid 1px; border-color: #00000; margin-left:15px;margin-top:15px;margin-bottom:15px; padding-right:25px;padding-top:5px; <? if(($q % 2) == 0) {echo 'float:right ;'; } else { echo 'float: left;';}?>">
								<div class="col-md-12" style="padding-left: 5px;padding-right: 0px;">
									<div class="col-md-2" style="padding-left: 4px;padding-right: 0px;">
										<?= $tamlogo ?>
									</div>
								</div>
								<div class="col-md-8" style="margin-left:70px; margin-top: -70px;" >
									<div class="row" style="margin-top: 5px;">Transportadora: <b><?= $transportadora ?></b></div>
									<div class="row" style="margin-top: 3px;">Número do Pedido: <b><?= $nnfe ?></b></div>
									<div class="row" style="margin-top: 3px;"> <u> Número Nota Fiscal: <b><?=  $idnf?></b></u></div>
								</div>
								<div class="row" style="margin-top: 15px;">
									<div class="col-md-11">
										<div class="row" style="margin-top: 5px;"><b>DESTINATÁRIO</b></div>
										<div class="row" style="font-size:13px;"> <b><?= traduzid("pessoa", "idpessoa", "nome", $rowinfo['idpessoa']) ?></b> </div>
										<div class="row" style="font-size:12px;">R. Social: <?= traduzid("pessoa", "idpessoa", "razaosocial", $rowinfo['idpessoa']) ?></div>
										<div class="row" style="font-size:12px;">End: <?=$rowinfo['enderecototal']?></div>
										<div class="row" style="font-size:12px;">
											<div class="col-md-6" style="padding-left: 0px; padding-right:0px;">
												<?if(!empty($rowinfo['bairro'])){?>
													Bairro:
												<?echo($rowinfo['bairro'])?>
												<?}?>
											</div>
										</div>
										<div class="row" style="font-size:12px;">
											<?if(!empty($rowinfo['cep'])){ $cepformatado=formatarCEP($rowinfo['cep'],true); ?>CEP:
											<?echo($cepformatado."       ");} if(!empty($rowinfo['cidade'])){echo  ("  -  ".$rowinfo['cidade']);}if(!empty($rowinfo['uf'])){echo("-".$rowinfo['uf']);}?>
										</div>
									</div>
									<div class="col-md-11">
										<? if(!empty($row1['obsentrega'])){?>
										<div class="row" style="font-size:12px;">
											Obs. Entrega: <b><?= nl2br($row1['obsentrega']) ?></b>
										</div>
										<?}?>
										<div class="row" style="height: 80px;"></div>
									</div>
								</div>
								<div class="col-md-10">
									<div class="row" style="font-size:12px;"> <b>REMETENTE</b> </div>
									<div class="row" style="font-size: 11px;">LAUDO LABORATÓRIO / INATA PRODUTOS BIOLÓGICOS</div>
									<div class="row" style="font-size: 11px;">R. Social: Laudo Laboratório Avícola Uberlândia LTDA</div>
									<div class="row" style="font-size: 11px;">CNPJ: 23.259.427/0001-04</div>
									<div class="row" style="font-size: 11px;">End: Rod. BR 365, Km 615 - S/N - Bairro Alvorada</div>
									<div class="row" style="font-size: 11px;">CEP.: 38.407-180 - Uberlândia - MG</div>
								</div>
								<div class="col-md-11">
									<div class="row" style="font-size:12px; text-align: right">
										<? echo "<br> Volume <b>".$q."</b> de <b>". $qvol."</b>";?>
									</div>
								</div>
							</div>
						</div>
					<?
							if(strpos($etiqueta, "[_MODULO_]") !== false){
								$etiqueta = str_replace("[_MODULO_]",retira_acentos($modulo),$etiqueta);
							}
							if(strpos($etiqueta, "[_LIDNF_]") !== false){
								$etiqueta = str_replace("[_LIDNF_]",retira_acentos($idnf),$etiqueta);
							}
							if(strpos($etiqueta, "[_NFVOLUME_]") !== false){
								$qrcodeContent["_mod"] = "nfvolume";
								$qrcodeContent["_cols"]["idnf"] = $idnf;
								$qrcodeContent["_cols"]["vol"] = $q;
								$qrcodeContent["_cols"]["tvol"] = $qvol;
								$etiqueta = str_replace("[_NFVOLUME_]",retira_acentos(json_encode($qrcodeContent)),$etiqueta);
							}
							if(strpos($etiqueta, "[_TRANPORTADORA_]") !== false){
								(!empty($transportadora)) ? $etiqueta = str_replace("[_TRANPORTADORA_]",retira_acentos($transportadora),$etiqueta) : $etiqueta = str_replace("[_TRANPORTADORA_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_IDNF_]") !== false){
								(!empty($idnf)) ? $etiqueta = str_replace("[_IDNF_]",retira_acentos($idnf),$etiqueta) : $etiqueta = str_replace("[_IDNF_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_NNF_]") !== false){
								(!empty($nnfe)) ? $etiqueta = str_replace("[_NNF_]",retira_acentos($nnfe),$etiqueta) : $etiqueta = str_replace("[_NNF_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_DEST_]") !== false){
								$dest = retira_acentos(traduzid("pessoa", "idpessoa", "nome", $rowinfo['idpessoa']));
								(!empty($dest)) ? $etiqueta = str_replace("[_DEST_]",$dest,$etiqueta) : $etiqueta = str_replace("[_DEST_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_RSOCIAL_]") !== false){
								$rsocial = retira_acentos(traduzid("pessoa", "idpessoa", "razaosocial", $rowinfo['idpessoa']) );
								(!empty($rsocial)) ? $etiqueta = str_replace("[_RSOCIAL_]",$rsocial,$etiqueta) : $etiqueta = str_replace("[_RSOCIAL_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_END_]") !== false){
								if(!empty($row1['logradouro'])){
									$endtotal = $row1['logradouro'].". ";
								}
								$endtotal.= $row1['endereco'].", ".$row1['numero'] ;
								if(!empty($row1['complemento'])){
									$endtotal.=	" - ".$row1['complemento'];
								}
								(!empty($rsocial)) ? $etiqueta = str_replace("[_END_]",retira_acentos($endtotal),$etiqueta) : $etiqueta = str_replace("[_END_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_RSOCIAL_]") !== false){
								if (!empty($row1['bairro'])) {
									$bairro = ($row1['bairro']);
								}
								(!empty($rsocial)) ? $etiqueta = str_replace("[_RSOCIAL_]",retira_acentos($bairro),$etiqueta) : $etiqueta = str_replace("[_RSOCIAL_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_BAIRRO_]") !== false){
								if (!empty($row1['bairro'])) {
									$bairro = ($row1['bairro']);
								}
								(!empty($bairro)) ? $etiqueta = str_replace("[_BAIRRO_]",retira_acentos($bairro),$etiqueta) : $etiqueta = str_replace("[_BAIRRO_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_CEP_]") !== false){
								if (!empty($row1['cep'])) {
									$CEP = formatarCEP($row1['cep'],true);
								}
								(!empty($CEP)) ? $etiqueta = str_replace("[_CEP_]",retira_acentos($CEP),$etiqueta) : $etiqueta = str_replace("[_CEP_]","",$etiqueta);
							}
							if(strpos($etiqueta, "[_CIDADE_]") !== false){
								if(!empty($row1['cidade'])){
									$cidadeuf = $row1['cidade'];
								}
								if(!empty($row1['uf'])){
									$cidadeuf.= "-".$row1['uf'];
								}
								(!empty($cidadeuf)) ? $etiqueta = str_replace("[_CIDADE_]",retira_acentos($cidadeuf),$etiqueta) : $etiqueta = str_replace("[_CIDADE_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_LOGO_]") !== false)) {
								(!empty($rowinfo['zplimg'])) ? $etiqueta = str_replace("[_LOGO_]",$rowinfo['zplimg'],$etiqueta) : $etiqueta = str_replace("[_LOGO_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_EMPRESA_]") !== false)) {
								(!empty($rowinfo['nomefantasia'])) ? $etiqueta = str_replace("[_EMPRESA_]",retira_acentos($rowinfo['nomefantasia']),$etiqueta) : $etiqueta = str_replace("[_EMPRESA_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_CNPJEMP_]") !== false)) {
								(!empty($rowinfo['cnpj'])) ? $etiqueta = str_replace("[_CNPJEMP_]",formatarCPF_CNPJ($rowinfo['cnpj']),$etiqueta) : $etiqueta = str_replace("[_CNPJEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_ENDEMP_]") !== false)) {
								if(!empty($rowinfo['xlgr'])){
									$endemp = retira_acentos($rowinfo['xlgr']);
								}
								if(!empty($row1['uf'])){
									$endemp.= ' - '.retira_acentos($rowinfo['nro']);
								}
								(!empty($endemp)) ? $etiqueta = str_replace("[_ENDEMP_]",$endemp,$etiqueta) : $etiqueta = str_replace("[_ENDEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_BAIRROEMP_]") !== false)) {
								if(!empty($rowinfo['xbairro'])){
									$bairroemp = retira_acentos($rowinfo['xbairro']);
								}
								(!empty($bairroemp)) ? $etiqueta = str_replace("[_BAIRROEMP_]",$bairroemp,$etiqueta) : $etiqueta = str_replace("[_BAIRROEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_CEPEMP_]") !== false)) {
								if(!empty($rowinfo['cep'])){
									$cepemp = retira_acentos($rowinfo['cep']);
								}
								(!empty($cepemp)) ? $etiqueta = str_replace("[_CEPEMP_]",$cepemp,$etiqueta) : $etiqueta = str_replace("[_CEPEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_CIDADEEMP_]") !== false)) {
								if(!empty($rowinfo['xmun'])){
									$cidadeemp = retira_acentos($rowinfo['xmun']);
								}
								(!empty($cidadeemp)) ? $etiqueta = str_replace("[_CIDADEEMP_]",$cidadeemp,$etiqueta) : $etiqueta = str_replace("[_CIDADEEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_UFEMP_]") !== false)) {
								if(!empty($rowinfo['uf'])){
									$ufemp = retira_acentos($rowinfo['uf']);
								}
								(!empty($ufemp)) ? $etiqueta = str_replace("[_UFEMP_]",$ufemp,$etiqueta) : $etiqueta = str_replace("[_UFEMP_]","",$etiqueta);
							}
							if ((strpos($etiqueta, "[_CONT_]") !== false)) {
								$etiqueta = str_replace("[_CONT_]",$q,$etiqueta);
							}
							if ((strpos($etiqueta, "[_TOTAL_]") !== false)) {
								$etiqueta = str_replace("[_TOTAL_]",$qvol,$etiqueta);
							}
							
							echo "<!--". $etiqueta."-->";
							
							if(imprimir($etiqueta)){
								inserirVolume($rowinfo['idempresa'], $idnf, $q);
							}
							$q = $q + 1;
				}
				
			}
		}
}
	
	//if(!empty($row1['idrotulo']))
	
//	echo('<div style="margin-top:35px;"> Os seguintes rótulos foram enviados para impressão, favor aguardar. </div>'); //mensagem de aviso ao usuário para mostrar quais rótulos e suas informações foram enviadas para impressão. 


// // funcao que faz a impressao, CASO FOR NECESSARIO MANUTENCAO NO CODIGO FAVOR COMENTAR A FUNCAO IMPRIMIR POIS MESMO EM AMBIENTE LOCAL  
// // ENVIA PARA O IP CADASTRADO NA IMPRESSORA 



function imprimir($strprint){
	try{
		$fp=pfsockopen(_IP_IMPRESSORA_LOGISTICA,9100);
		fputs($fp,$strprint);
		fclose($fp);
		//echo "$strprint";
		return true;
	}catch (Exception $e){
		echo '[ERRO] Erro na Impressão: ',  $e->getMessage(), "\n";
		return false;
	}
}

function inserirVolume( $idempresa, $idnf, $volume ){
	// inseri volume do pedido com status PENDENTE
	$qr = "INSERT INTO nfvolume (idempresa, idnf, volume, status, criadopor, criadoem, alteradopor, alteradoem)
			VALUES (".$idempresa.", ".$idnf.", ".$volume.", 'P', '".$_SESSION["SESSAO"]["USUARIO"]."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', now());";
	d::b()->query($qr);
}
?>
	</body>
</html>
