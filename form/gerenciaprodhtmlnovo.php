<?
require_once("../inc/php/validaacesso.php");
if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$idpessoa = $_GET["idpessoa"];
$idprodserv = $_GET["idprodserv"];
$status = $_GET["status"];
$idplantel = $_GET['idplantel'];
$validacao = $_GET['validacao'];

$acao = $_GET["acao"];

if (!empty($idpessoa)) {
	// $clausulalote .= " and l.idpessoa =".$idpessoa." ";
	$clausulaloten .= " and a.idpessoa =".$idpessoa." ";
}

if (!empty($idprodserv)) {
	//  $clausulad .= " and p.idprodserv =  ".$idprodserv."";
	$clausulaloten .= " and p.idprodserv =  ".$idprodserv."";
}


if ($idplantel) {
	//	$strplantel=" and f.idplantel=".$idplantel;
	$clausulaloten .= " and e.idplantel=".$idplantel;
	//$strinplantel=" and l.idprodservformula=f.idprodservformula and f.status='ATIVO' and f.idplantel = ".$idplantel." ";

} else {
	//	$strplantel='';
	//$strinplantel=" and l.idprodservformula=f.idprodservformula and f.status='ATIVO' ";

}

if ($validacao == 'P') {
	$clausulaloten .= " and l.status='ABERTO' ";
	/*
	$strvalidacao2="and exists (select 1 from prodservformulains i ,prodserv ps,lote s,resultado r,amostra a,lotefracao lf
	where i.idprodservformula = f.idprodservformula 
		and i.idprodserv = ps.idprodserv 
		and ps.especial ='Y' 
		and s.idprodserv=i.idprodserv 
		and s.tipoobjetosolipor ='resultado' 
		AND s.status='INICIO' 
		and lf.idlote=s.idlote
        and lf.status='DISPONIVEL'
		and s.idobjetosolipor =r.idresultado 
		and a.idamostra = r.idamostra 
		and a.idpessoa = l.idpessoa)
		";

	
	$strvalidacao2=" and exists(
							select 1	
						from  prodservformula fx 
								join prodservformulains ic on(ic.idprodservformula= fx.idprodservformula)
								join prodserv ps on (ps.idprodserv = ic.idprodserv and ps.especial='Y' )
								join lote ls on(ls.idprodserv = ic.idprodserv and ls.tipoobjetosolipor='resultado' 
								and ls.situacao ='PENDENTE'
								and ls.status not in ('CANCELADO','ESGOTADO') )
									join resultado r on(r.idresultado =ls.idobjetosolipor)			    
								join amostra a on(a.idamostra=r.idamostra and l.idpessoa =a.idpessoa)
								where fx.idprodserv=l.idprodserv 
						)                    
                        ";
*/
} elseif ($validacao == 'NA') {
	$clausulaloten .= " and l.status='NAO AUTORIZADA' ";
} elseif ($validacao == 'A') {
	$clausulaloten .= " and l.status='AUTORIZADA' ";
} elseif ($validacao == 'O') {
	$clausulaloten .= " and l.status='APROVADO' ";
} else {
	$semstatus = '';
	$strvalidacao = " ";
}


$controleass = $_SESSION[$struniqueid]["controleass"];
if ($acao == "ini") {
	$controleass = 1; //vai para o primeiro registro	
	// Executa a consulta completa somente 1 vez para recuperar a quantidade total de registros
	$booexeccount = true;
	//Atualiza o Uniqueid da pagina para guardar o ultima pagina utilizada
	//$_SESSION[$struniqueid]["uniqueid"] = $struniqueid;	
} else {
	if ($acao == "prox") {
		$controleass = intval($controleass) + 1;
	} elseif ($acao == "ant" and $controleass > 1) {
		$controleass = intval($controleass) - 1;
	}
}
//Apos o incremento da variavel, atribui para a ssesion o valor do proximo registro a ser chamado
$_SESSION[$struniqueid]["controleass"] = $controleass;


if ($_GET and (!empty($idpessoa) or !empty($idprodserv) or !empty($idplantel) or $validacao == 'V' or $validacao == 'O')) {
	if ($booexeccount == true) {
		/*	
		$sql="select p.idprodserv,l.idpessoa,p.codprodserv,p.descr 
			from prodserv p ,lote l , prodservformula f 
			where p.status='ATIVO' 
			AND p.tipo='PRODUTO' 
			and p.venda ='N' 
			and p.fabricado ='Y'
			and p.especial='Y'
			
			".$strinplantel."
			".$clausulad."
			".$clausulalote."         
			and l.idprodserv = p.idprodserv 
                            
			and exists (select 1 from prodservformulains i ,prodserv ps,lote s,resultado r,amostra a,lotefracao lf
									where i.idprodservformula = f.idprodservformula 
									and s.idlote=lf.idlote
											and lf.status='DISPONIVEL'
										and i.idprodserv = ps.idprodserv and ps.especial ='Y' and s.idprodserv=i.idprodserv 
										and s.tipoobjetosolipor ='resultado' ".$semstatus." and s.idobjetosolipor =r.idresultado and a.idamostra = r.idamostra and a.idpessoa = l.idpessoa)

			
			and l.idpessoa is not null
		group by p.idprodserv 			
		order by p.descr";
		*/
		$sql = "select p.idprodserv,a.idpessoa,p.codprodserv,p.descr 
			from lote l join resultado r on(r.idresultado=l.idobjetosolipor)
			join amostra a on(a.idamostra =r.idamostra )
			join especiefinalidade e on(e.idespeciefinalidade = a.idespeciefinalidade )
			join prodserv p on(p.idprodserv=l.idprodserv)
			join lotefracao lf on(lf.idlote=l.idlote 
			-- and lf.status='DISPONIVEL'
			)
			where l.tipoobjetosolipor='resultado' 			
			".$clausulaloten."
			".getidempresa('l.idempresa', 'prodserv')." 
			 and r.status = 'ASSINADO'
			 group by p.idprodserv,a.idpessoa		
			order by p.descr,p.idprodserv,a.idpessoa";
		?>
		<!-- Sementes inicial <?= $sql ?>-->
		<?
		$res = d::b()->query($sql) or die("Erro ao buscar PRODUTOS sql=".$sql);
		$arridresultado = array();
		$arridrespes = array();
		$iarr = 0;

		//while para gravar todos os resultados para se poder navegar entre eles
		while ($row = mysqli_fetch_assoc($res)) {
			/*
			if($row["idprodserv"]!=$vidprodserv){
				$iarr++;
				$vidprodserv=$row["idprodserv"];
			}
			*/
			$iarr++;
			$arridresultado[$iarr]['idprodserv'] = $row["idprodserv"];
			$arridresultado[$iarr]['idpessoa'] = $row["idpessoa"];
			$arridrespes[$row["idprodserv"]][$row["idpessoa"]] = $row["idpessoa"];
			$booexeccount = false;
		}

		//total de registros da consulta
		$_SESSION[$vargetsess]["qtdreg"] = $iarr;
		//grava todos dos os ids de resultados da consulta
		$_SESSION[$vargetsess]["arridres"] = $arridresultado;

		$_SESSION["arridprodidpes"] = $arridrespes;
	} //if($booexeccount==true){
	$arridresultado = $_SESSION[$vargetsess]["arridres"];
	$arridrespes = $_SESSION["arridprodidpes"];

	//($arridrespes);

	$qtdcount = $_SESSION[$vargetsess]["qtdreg"];
	if ($qtdcount < $controleass) {
		echo '<br><br><br><div align="center">Não existem mais produtos.</div>';
		die;
	}


	$sql = "select p.idprodserv,p.codprodserv,p.descr 
	from prodserv p 
	where p.idprodserv=".$arridresultado[$controleass]['idprodserv'];
	?>
	<!-- <?= $sql ?>-->
	<?
	$res = d::b()->query($sql) or die("Erro ao buscar PRODUTOS sql=".$sql);

	while ($row = mysqli_fetch_assoc($res)) 
	{
		$produto = $row["descr"];
		$procurar = array("SEMENTE", "AUTOGENA", "AUTÓGENA", "-");
		$subistuirpor   = array("");
		$nnvoprod = str_replace($procurar, $subistuirpor, $produto);
		/*
		   $sqlv="select p.idprodserv,a.idpessoa,c.nome,p.codprodserv,p.descr 
					from lote l join resultado r on(r.idresultado=l.idobjetosolipor)
					join amostra a on(a.idamostra =r.idamostra )
					join especiefinalidade e on(e.idespeciefinalidade = a.idespeciefinalidade )
					join prodserv p on(p.idprodserv=l.idprodserv)
					join pessoa c on(c.idpessoa=a.idpessoa)
					join lotefracao lf on(lf.idlote=l.idlote and lf.status='DISPONIVEL')
					where l.tipoobjetosolipor='resultado'					
					and l.idprodserv=".$row['idprodserv']." 
					".getidempresa('l.idempresa','prodserv')."  
					".$clausulaloten."
					group by l.idprodserv,c.idpessoa order by c.nome";
		*/
		/*
		$sqlv="select l.idprodserv,p.nome,p.idpessoa,l.idprodservformula, concat(f.rotulo,'-',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose
			
				from lote l 
					join pessoa p on(l.idpessoa=p.idpessoa  ".$clausulalote.")
					join prodservformula f on (l.idprodservformula=f.idprodservformula and f.status='ATIVO' ".$strplantel." )
											
				where l.idprodserv=".$row['idprodserv']." 
				".$strvalidacao2."
				group by l.idprodserv,p.idpessoa,l.idprodservformula order by p.nome";
		*/
		//	$resv = d::b()->query($sqlv) or die("erro ao buscar partidas: ".mysqli_error(d::b())."<p>SQL: ".$sqlv);
		//	$qtdprodform= mysqli_num_rows($resv);
		?>
		<!-- concetrado e pessoa <?= $sqlv ?>-->
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-1 nowrap">

							</div>
							<div class="col-md-11 nowrap">
								<h3>
									<i class="fa fa-copy fa-1x fade pointer hoverazul" title="Copiar link deste item" onclick="copiaLink()"></i>
									<b>Semente <?= $controleass ?> de <?= $qtdcount ?> - <a href="?_modulo=prodserv&_acao=u&idprodserv=<?= $row['idprodserv'] ?>" target="_blank" style="color: inherit;">
										<?= $nnvoprod ?></a>
									</b>
								</h3>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?
		$qtdprodform = count($arridrespes[$row["idprodserv"]]);
		if ($qtdprodform > 0) 
		{
			$qtdlista = 0;
			//	while($r= mysqli_fetch_assoc($resv)){
			//$arridrespes[$row["idprodserv"]][$row["idpessoa"]]
			$vidpessoa = $arridresultado[$controleass]['idpessoa'];

			for ($il = 1; $il <= 1; $il++) 
			{
				//foreach ($arridresultado[$controleass]['idpessoa'] as $vidpessoa) {

				$qtdlista = $qtdlista + 1;
				$ocultar = 'Y';
				/*					
				$sqx="select ps.idprodserv,ps.descr,ls.partida,ls.exercicio,ls.vencimento,ls.idlote,ls.status,ls.situacao,ls.observacao
							,case when ls.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,
							a.idregistro,a.exercicio,ls.tipificacao,ls.alerta,ls.orgao,ls.idprodserv as idprodservsemente
						from  prodservformula f 
						join prodservformulains ic on(ic.idprodservformula= f.idprodservformula)
						join prodserv ps on (ps.idprodserv = ic.idprodserv and ps.especial='Y' )
						join lote ls on(ls.idprodserv = ic.idprodserv and ls.tipoobjetosolipor='resultado' and ls.status not in ('CANCELADO','ESGOTADO') )
						join lotefracao lf on(lf.idlote=ls.idlote and lf.status='DISPONIVEL')
						join resultado r on(r.idresultado =ls.idobjetosolipor)
						join amostra a on(a.idamostra=r.idamostra and a.idpessoa =".$r['idpessoa'].")
				where f.idprodserv= ".$r['idprodserv']." ".$strplantel." 
				and not exists (select 1 from lotepool lp where  lp.idlote=ls.idlote and lp.status='ATIVO') 
				group by ls.idlote order by ps.idprodserv,ls.status,ls.npartida asc";
				*/
				$sqx = "SELECT l.idprodserv,
							l.partida,
							l.exercicio,
							l.flgalerta,
							l.vencimento,
							l.idlote,
							l.status,
							l.situacao,
							l.observacao,
							case
								when l.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d'))
									then 'Y'
								else 'N'
							end as vencido,
							a.idregistro,
							l.tipificacao,
							l.alerta,
							l.orgao,
							l.idprodserv as idprodservsemente,
							-- concat(rj.value, ' - ', rj.value3) as hemolise,
							r.idresultado
					from lote l join resultado r on(r.idresultado=l.idobjetosolipor)                    
						join lotefracao lf on(lf.idlote=l.idlote 
						 and lf.status='DISPONIVEL'
						)
						join amostra a on(a.idamostra =r.idamostra )
						-- left join vw8resultadojson rj on (rj.idresultado = r.idresultado and rj.titulo3 = 'HEMÓLISE' and rj.value3 <> '' and rj.value=l.orgao)
						where l.tipoobjetosolipor='resultado'                    
						and not exists (select 1 from lotepool lp where  lp.idlote=l.idlote and lp.status='ATIVO')
						and l.vencimento >= CURDATE()
						and l.idprodserv=".$row['idprodserv']."
						and r.status = 'ASSINADO'
						".getidempresa('l.idempresa', 'prodserv').
						($vidpessoa ? " and a.idpessoa = $vidpessoa" : '')
						." 
						
					group by l.idlote
					order by l.idprodserv,l.status,l.npartida asc";
				?>
				<!-- sementes <?= $sqx ?>-->
				<?
				$resx = d::b()->query($sqx) or die("erro ao buscar sementes: ".mysqli_error(d::b())."<p>SQL: ".$sqx);
				$qtdx = mysqli_num_rows($resx);

				?>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading ">
								<?
								$procurar = array("SEMENTE", "AUTOGENA", "AUTÓGENA", "-");
								$subistuirpor   = array("");
								$nvoprod = str_replace($procurar, $subistuirpor, $produto);
								?>
								<div class="row nowrap" style="width: 1100px;">
									<div class="col-md-11">

										<h3><b><?= traduzid('pessoa', 'idpessoa', 'nome', $vidpessoa); ?> </b></h3>

									</div>
									<div class="col-md-1 nowrap">
										<font class="cinza"><?= $qtdlista ?>/<?= $qtdprodform ?></font>
									</div>
								</div>

							</div>
							<div class="panel-body" style="padding-top:0px !important;">
								<?
								//if($qtdx>0){
								?>
								<table class="table table-striped planilha">
									<tr>
										<td>
											<div class="row">
												<!--<?= $sqx ?>-->

												<div class="col-md-5">
													<?
													$idprodserv = '';
													if ($qtdx > 0) 
													{
														?>
														<div style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;">
															<?
															while ($rx = mysqli_fetch_assoc($resx)) {
																$rx['hemolise']='';

																$sqlhm="select concat(rj.value, ' - ', rj.value3) as hemolise
																       from 
																           vw8resultadojson rj
																		 where rj.idresultado =".$rx['idresultado']." and rj.titulo3 = 'HEMÓLISE' and rj.value3 <> '' and rj.value='".$rx['orgao']."'";

																$reshm = d::b()->query($sqlhm) or die("erro ao buscar hemolise: ".mysqli_error(d::b())."<p>SQL: ".$sqlhm);
																$rowhm=mysqli_fetch_assoc($reshm);
																if(!empty($rowhm['hemolise'])){
																	$rx['hemolise']=$rowhm['hemolise'];
																}														

																$idprodservsemente = $rx['idprodservsemente'];
																
																if ($rx['status'] == 'ABERTO') {
																	$botao = 'btn-secondary';
																} elseif ($rx['status'] == 'AUTORIZADA') {
																	$botao = 'btn-primary';
																} elseif ($rx['status'] == 'APROVADO') {
																	$botao = 'btn-success';
																} else {

																	$sqlus = "SELECT si.idsolfabitem, s.status, s.idsolfab, s.idlote, s.idpessoa,l.idlote AS idloteL
																			FROM solfabitem si
																				JOIN solfab s on s.idsolfab = si.idsolfab
																				JOIN lote l ON l.idsolfab = s.idsolfab and l.status not in ('APROVADO','QUARENTENA','LIBERADO','CANCELADO','REPROVADO')
																			WHERE  s.status not in('CANCELADO','REPROVADO') AND si.idobjeto =". $rx['idlote'] ;
																
																	$resus = d::b()->query($sqlus) or die("Erro ao verificar semente em uso: Erro: ".mysqli_error(d::b())."\n".$sqlus);
																	$qrowus = mysqli_num_rows($resus);

																	if($qrowus>0){
																		$botao = 'btn-warning';
																	}else{
																		$botao = 'btn-danger';
																	}

																	
																}

																if ($rx['status'] == 'APROVADO' or $rx['status'] == 'ABERTO' or $rx['status'] == 'CANCELADO' or $rx['status'] == 'AUTORIZADA' or $rx['status'] == 'NAO AUTORIZADA') {
																	$funcionalt = "alterast(this,".$rx['idlote'].")";
																} else {
																	$funcionalt = "";
																}

																if (empty($rx['orgao'])) {
																	$rx['orgao'] = $rx['observacao'];
																}
																if (!empty($rx['alerta'])) {
																	//$rxalerta=' * '.$rx['alerta'];
																	$rxalerta = '';
																} else {
																	$rxalerta = '';
																}
																if ($rx['status'] != 'ABERTO') {
																	$statuslote = $rx['status'];
																} else {
																	$statuslote = 'PENDENTE';
																}


																$tempool = 'N';
																$classdrag = "dragInsumo";
																?>

																<div class="panel-body " style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6; padding-top:5px !important">
																	<table class='tblotes'>
																		<tr class="dragInsumo" idloteins="<?= $rx['idlote'] ?>">
																			<td style="width: 5%;">
																				<a class="fa fa-bars fa-2x hoverazul pointer fade" href="?_modulo=semente&_acao=u&idlote=<?= $rx['idlote'] ?>" target="_blank" title="Lote"></a>
																			</td>
																			<td style="width: 90%;" class="nowrap">
																				<button title="<?= $rx['subtipoamostra'] ?> <?= $rx['tipificacao'] ?> <?= $rx['orgao'] ?> <?= $rxalerta ?>" status="<?= $rx['status'] ?>" id="<?= $rx['idlote'] ?>" situacao="<?= $rx['situacao'] ?>" type="button" class="btn <?= $botao ?> btn-xs" onclick="<?= $funcionalt ?>">
																					<? if ($rx['vencido'] == 'Y') { ?><i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vencida"></i><? } ?>
																					<?= $rx['partida'] ?>/<?= $rx['exercicio'] ?> <span id="span_<?= $rx['idlote'] ?>"><?= $statuslote ?></span> - <?= dma($rx['vencimento']) ?>
																				</button>
																				<? if (!empty($rx['hemolise'])) { ?>
																					<i class="fa fa-info-circle azul bold fa-1x btn-lg pointer" onclick="janelamodal('?_acao=u&_modulo=resultsuinos&idresultado=<?= $rx['idresultado'] ?>')" data-toggle="popover" href="#modal_<?= $rx['idresultado'] ?>" data-trigger="hover"></i>
																					<div id="modal_<?= $rx['idresultado'] ?>" class="hidden">
																						<table>
																							<tr>
																								<td>
																									<?= $rx['hemolise'] ?>
																								</td>
																							</tr>
																						</table>
																					</div>
																				<? } ?>
																				<? if ($rx['flgalerta'] == 'P') { ?>
																					<i class="fa fa-star preto bold fa-1x btn-lg" title=" " ;=""></i>
																				<? } elseif ($rx['flgalerta'] == 'A') { ?>
																					<i class="fa fa-star azul bold fa-1x btn-lg" title=" " ;=""></i>
																				<? } elseif ($rx['flgalerta'] == 'R') { ?>
																					<i class="fa fa-star roxo bold fa-1x btn-lg" title=" " ;=""></i>
																				<? } ?>
																			</td>
																			<td title="Arrastar para um Poll." align="right">
																				<i class="fa fa-arrows fa-2x hoverazul cinzaclaro hover move"></i>
																			</td>
																		</tr>
																	</table>
																</div>
															<?
															} //while($rx= mysqli_fetch_assoc($resx)){
															?>
														</div>
													<? } ?>
												</div>
												<div class="col-md-7 <?= $r['idpessoa'] ?>_<?= $r['idprodserv'] ?>">
													<? for ($ord = 1; $ord <= 6; $ord++) {
														$mostrar = 'Y';
														
														/*
														$sqx="select po.ord,ps.idprodserv,ps.descr,ls.partida,ls.exercicio,ls.vencimento,ls.idlote,ls.status,ls.situacao,lp.idpool,lp.idlotepool,ls.observacao
														,case when ls.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,
														a.idregistro,a.exercicio,ls.tipificacao,ls.alerta,ls.orgao,ls.idprodserv as idprodservsemente
														from  prodservformula f 
														join prodservformulains ic on(ic.idprodservformula= f.idprodservformula)
														join prodserv ps on (ps.idprodserv = ic.idprodserv and ps.especial='Y' )
														join lote ls on(ls.idprodserv = ic.idprodserv and ls.tipoobjetosolipor='resultado' and ls.status not in ('CANCELADO','ESGOTADO') )
														join lotefracao lf on(lf.idlote=ls.idlote and lf.status='DISPONIVEL')
														join resultado r on(r.idresultado =ls.idobjetosolipor)
														join amostra a on(a.idamostra=r.idamostra and a.idpessoa =".$r['idpessoa'].")
														join lotepool lp on(lp.idlote=ls.idlote and lp.status='ATIVO')
														join pool po on(po.idpool=lp.idpool and po.ord=".$ord.")
														where f.idprodserv= ".$r['idprodserv']." ".$strplantel."
															group by ls.idlote order by po.ord,ps.idprodserv,lp.idpool,ls.status,ls.npartida asc";
														*/
														$sqx = "select po.ord,l.idprodserv,l.partida,l.flgalerta,l.exercicio,l.vencimento,l.idlote,l.status,l.situacao,lp.idpool,lp.idlotepool,l.observacao,l.prioridade
																,case when l.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,
																a.idregistro,l.tipificacao,l.alerta,l.orgao,l.idprodserv as idprodservsemente,
																po.criadoem, po.criadopor, po.alteradoem, po.alteradopor
																from lote l join resultado r on(r.idresultado=l.idobjetosolipor)                    
																join lotefracao lf on(lf.idlote=l.idlote
																-- and lf.status='DISPONIVEL'
																)
																join amostra a on(a.idamostra =r.idamostra )
																join lotepool lp on(lp.idlote=l.idlote and lp.status='ATIVO')
																join pool po on(po.idpool=lp.idpool and po.ord=".$ord.")
																where l.tipoobjetosolipor='resultado'                    
																and l.idprodserv=".$row['idprodserv']."
																".getidempresa('l.idempresa', 'prodserv').
																($vidpessoa ? " and a.idpessoa = $vidpessoa" : '')." 
																group by l.idlote order by po.ord,l.idprodserv,lp.idpool,l.status,l.npartida asc";
														?>
														<!-- sementes <?= $sqx ?>-->
														<?
														$resx = d::b()->query($sqx) or die("erro ao buscar sementes: ".mysqli_error(d::b())."<p>SQL: ".$sqx);
														$qtdx2 = mysqli_num_rows($resx);
														if ($qtdx2 < 1) { ?>

															<div class="row">
																<div class="panel-body" style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6; padding-top:5px !important;">
																	<div class="col-md-3 soltavel dragInsumo ui-sortable-handle ui-droppable" idpool="<?= $rx['idpool'] ?>" ord="<?= $ord ?>">
																		<div class="papel hover container4" id="formSF">
																			<h1>
																				<p><?= $ord ?></p>
																			</h1>
																		</div>
																	</div>
																</div>
															</div>

														<? } else { //if($qtdx<1){

															while ($rx = mysqli_fetch_assoc($resx)) {
																$idprodservsemente = $rx['idprodservsemente'];
																$ocultar = 'N';

																if ($rx['status'] == 'ABERTO') {
																	$botao = 'btn-secondary';
																} elseif ($rx['status'] == 'AUTORIZADA') {
																	$botao = 'btn-primary';
																} elseif ($rx['status'] == 'APROVADO') {
																	$botao = 'btn-success';
																} else {

																	$sqlus = "SELECT si.idsolfabitem, s.status, s.idsolfab, s.idlote, s.idpessoa,l.idlote AS idloteL
																			FROM solfabitem si
																				JOIN solfab s on s.idsolfab = si.idsolfab
																				JOIN lote l ON l.idsolfab = s.idsolfab and l.status not in ('APROVADO','QUARENTENA','LIBERADO','CANCELADO','REPROVADO')
																			WHERE  s.status not in('CANCELADO','REPROVADO') AND si.idobjeto =". $rx['idlote'] ;
																
																	$resus = d::b()->query($sqlus) or die("Erro ao verificar semente em uso: Erro: ".mysqli_error(d::b())."\n".$sqlus);
																	$qrowus = mysqli_num_rows($resus);

																	if($qrowus>0){
																		$botao = 'btn-warning';
																	}else{
																		$botao = 'btn-danger';
																	}

																	
																}


																if (empty($rx['orgao'])) {
																	$rx['orgao'] = $rx['observacao'];
																}
																if (!empty($rx['alerta'])) {
																	//$rxalerta=' * '.$rx['alerta'];
																	$rxalerta = '';
																} else {
																	$rxalerta = '';
																}
																if($qrowus > 0 and $rx['status']=='CANCELADO'){
																	$statuslote ='EM CANCELAMENTO';
																}elseif ($rx['status'] != 'ABERTO') {
																	$statuslote = $rx['status'];
																} else {
																	$statuslote = 'PENDENTE';
																}
																if ($rx['status'] == 'APROVADO' or $rx['status'] == 'ABERTO' or $rx['status'] == 'AUTORIZADA' or $rx['status'] == 'NAO AUTORIZADA' or $rx['status'] == 'CANCELADO' or $rx['status'] == 'APROVADO') {
																	$funcionalt = "alterast(this,".$rx['idlote'].")";
																} else {
																	$funcionalt = "";
																}

																if ($rx['prioridade'] != 'ALTA') {
																	$corpriori ='cinzaclaro hoververde';
																	$prioriant='NORMAL';
																	$prioridest='ALTA';
																	$titlepriori="Guardar";
																} else {
																	$corpriori ='verde hovercinza';
																	$prioriant='ALTA';
																	$prioridest='NORMAL';
																	$titlepriori="Não Guardar";
																}

																$tempool = 'Y';
																$classdrag = "soltavel";
																$existepoll = 'Y';
																if ($mostrar == 'Y') {
																	$mostrar = 'N';
																	?>
																	<div class="row">
																		<div class="panel-body" style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;  padding-top:5px !important;">																			
																		<div class="historicoLotePool" style="float: right;">
																			<i class="fa btn-sm fa-info-circle azul pointer hoverazul" style="padding: 0px !important;" data-target="webuiPopover0"></i>
																		</div>
																		<div class="webui-popover-content">
																			<br />
																			<table class="table table-striped planilha">
																				<tr>
																					<th>Criado Por</th>
																					<th>Criado Em</th>
																					<th>Alterado Por</th>
																					<th>Alterado Em
																					<th>
																				</tr>																		
																				<tr>
																					<td><?=traduzid("pessoa", "usuario", "nomecurto", $rx['criadopor'])?></td>
																					<td><?=dmahms($rx['criadoem']) ?></td>
																					<td><?=traduzid("pessoa", "usuario", "nomecurto", $rx['alteradopor'])?></td>
																					<td><?=dmahms($rx['alteradoem']) ?></td>
																				</tr>
																			</table>
																		</div>	
																		<div class="col-md-1 soltavel dragInsumo ui-sortable-handle ui-droppable" idpool="<?= $rx['idpool'] ?>" ord="<?= $ord ?>">
																				<div class="papel hover container4" id="formSF">
																					<h1>
																						<p><?= $ord ?></p>
																					</h1>

																				</div>
																			</div>
																			<div class="col-md-11">
																			<?
																}
																?>

																<div class="col-md-12">
																	<!-- table class="tblotes">								
																		<tbody class="ui-sortable"><tr class="" >
																			<td >
																				<a class="fa fa-bars fa-2x  hoverazul pointer fade" href="?_modulo=semente&_acao=u&idlote=<?= $rx['idlote'] ?>" target="_blank" title="Lote"></a>
																			</td>
																			<td>
																				<button  title="<?= $rx['subtipoamostra'] ?> <?= $rx['tipificacao'] ?> <?= $rx['orgao'] ?> <?= $rxalerta ?>" status="<?= $rx['status'] ?>" id="<?= $rx['idlote'] ?>" situacao="<?= $rx['situacao'] ?>"  type="button" class="btn <?= $botao ?> btn-xs" onclick="<?= $funcionalt ?>"> 
																					<? if ($rx['vencido'] == 'Y') { ?><i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vencida"></i><? } ?>	
																					<?= $rx['partida'] ?>/<?= $rx['exercicio'] ?> <span id="span_<?= $rx['idlote'] ?>" ><?= $statuslote ?></span> - <?= dma($rx['vencimento']) ?> 
																				</button>
																					
																			</td>
																			<td>
																				<i class="fa fa-trash fa-1x  cinzaclaro hoververmelho pointer" onclick="inativapool(<?= $rx['idlotepool'] ?>)" title="Retirar do Pool "></i>	
																			</td>									
																	</tbody>
																	</table !-->


																	<div class="panel-body " style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6; padding-top:5px !important">
																		<table class='tblotes'>
																			<tr class="dragInsumo" idloteins="<?= $rx['idlote'] ?>" idlotepool="<?= $rx['idlotepool'] ?>">
																				<td title="Arrastar para um Poll." align="right">
																					<i class="fa fa-arrows fa-2x hoverazul cinzaclaro hover move"></i>
																				</td>
																				<td style="width: 5%;">
																					<a class="fa fa-bars fa-2x hoverazul pointer fade" href="?_modulo=semente&_acao=u&idlote=<?= $rx['idlote'] ?>" target="_blank" title="Lote"></a>
																				</td>
																				<td style="width: 90%;" class="anterior nowrap">
																					<button title="<?= $rx['subtipoamostra'] ?> <?= $rx['tipificacao'] ?> <?= $rx['orgao'] ?> <?= $rxalerta ?>" status="<?= $rx['status'] ?>" id="<?= $rx['idlote'] ?>" situacao="<?= $rx['situacao'] ?>" type="button" class="btn <?= $botao ?> btn-xs" onclick="<?= $funcionalt ?>">
																						<? if ($rx['vencido'] == 'Y') { ?><i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vencida"></i><? } ?>
																						<?= $rx['partida'] ?>/<?= $rx['exercicio'] ?> <span id="span_<?= $rx['idlote'] ?>"><?= $statuslote ?></span> - <?= dma($rx['vencimento']) ?>
																					</button>
																					<? if ($rx['flgalerta'] == 'P') { ?>
																						<i class="fa fa-star preto bold fa-1x btn-lg" title=" " ;=""></i>
																					<? } elseif ($rx['flgalerta'] == 'A') { ?>
																						<i class="fa fa-star azul bold fa-1x btn-lg" title=" " ;=""></i>
																					<? } elseif ($rx['flgalerta'] == 'R') { ?>
																						<i class="fa fa-star roxo bold fa-1x btn-lg" title=" " ;=""></i>
																					<? } ?>
																				</td>
																				<td>
																					<div class="historicoLotePool">
																						<i class="fa btn-sm fa-info-circle azul pointer hoverazul" style="padding: 0px !important;" data-target="webuiPopover0"></i>
																					</div>
																					<div class="webui-popover-content">
																						<br />
																						<table class="table table-striped planilha">
																							<tr>
																								<th>Criado Por</th>
																								<th>Criado Em</th>
																								<th>Alterado Por</th>
																								<th>Alterado Em</th>
																							</tr>
																							<?
																							$sqlHistorico = "SELECT p.nomecurto AS nomecurto_alterado, 
																												    lp.alteradoem AS alteradoem_alterado,
																													p2.nomecurto AS nomecurto_criado, 
																												    lp.criadoem AS criadoem_criado
																											FROM lotepool lp JOIN pessoa p ON p.usuario = lp.alteradopor
																											JOIN pessoa p2 ON p2.usuario = lp.criadopor
																										   WHERE lp.idlote = ".$rx['idlote']."
																										ORDER BY lp.alteradoem DESC;";
																							$reshistorico = d::b()->query($sqlHistorico) or die("erro ao buscar Histórico: ".mysqli_error(d::b())."<p>SQL: ".$sqx);

																							while ($rowHistorico = mysqli_fetch_assoc($reshistorico)) {
																							?>
																								<tr>
																									<td><?= $rowHistorico['nomecurto_criado'] ?></td>
																									<td><?= dmahms($rowHistorico['criadoem_criado']) ?></td>
																									<td><?= $rowHistorico['nomecurto_alterado'] ?></td>
																									<td><?= dmahms($rowHistorico['alteradoem_alterado']) ?></td>
																								</tr>
																							<?
																							}
																							?>
																						</table> 
																					</div>
																				</td>
																				<td>																				
																					<i class="fa fa-download fa-2x  <?=$corpriori?>" prioriant='<?=$prioriant?>' prioridest='<?=$prioridest?>' style="margin:3px;"  title="<?=$titlepriori?>"  onclick="alterarprioridade(this,<?=$rx['idlote']?>)"></i>
																				</td>
																				<td>
																					<?
																					$ban = 'ban';
																					$bantitle = 'title="Esta semente não pode ser excluída, pois há um solfab em andamento."';
																					$banonclick = '';
																					if(empty($qrowus)){
																						$ban = 'trash';
																						$bantitle = 'title="Retirar do Pool"';
																						$banonclick = 'onclick="inativapool(' . $rx['idlotepool'] . ', ' . $rx['idlote'] . ')"';
																					}?>
																					<i class="fa fa-<?=$ban?> fa-1x  cinzaclaro hoververmelho pointer" <?=$banonclick?> <?=$bantitle?>></i>
																				</td>
																			</tr>
																		</table>
																	</div>
																</div>
															<? } ?>
															</div>															
														</div>
													</div>

													<? 
													} //if($qtdx<1){
												} ?>
											</div>
											<?
											if ($qtdx < 1 and $ocultar == 'Y') {
											?>
												<div class='ocultar' id='<?= $r['idpessoa'] ?>_<?= $r['idprodserv'] ?>'>
												</div>
												<div class="col-md-5">
													<h3><b>Não Possui semente.</b></h3>
												</div>
											<?
											}
											?>
										</div>
							</div>
						</div>
					</td>
				</tr>
				</table>
				<?
				//}else{

						?>
					</div>
				</div>
				</div>
				</div>
		<?
			} //while($r= mysqli_fetch_assoc($resv)){
		} else {
			echo ("<br>Este produto não possui formulação com as caracteristicas da pesquisa.</br>");
		}
		?>

	<?
		//print_r($arrvisita);
	} // while($row=mysql_fetch_assoc($res)){ 


	?>
	</div>
	</div>
	</div>
	</div>
<?
} //if($_GET and !empty($clausulad)){
?>
<script>
	$(document).ready(function() {


		var arrayOfIds = $.map($(".ocultar"), function(n, i) {
			return n.id;
		});

		jQuery.each(arrayOfIds, function(i, val) {
			$("." + val).hide();
		});

	});
	$(function() {
		$('[data-toggle="popover"]').popover({
			html: true,
			content: function() {
				let ModalPopoverId = $(this).attr("href").replaceAll("#", "")
				return $("#" + ModalPopoverId).html();
			}
		});
	});
	//Permitir ordenar/arrastar os TR de insumos
	$(".tblotes tbody").sortable({
		update: function(event, objUi) {
			ordenaInsumos();
		}
	});

	//Permitir dropar o insumo
	$(".soltavel").droppable({
		drop: function(event, ui) {
			$this = $(this); //TR
			var idlote = ui.draggable.attr("idloteins");
			var idlotepool = ui.draggable.attr("idlotepool");
			var idpool = $this.attr("idpool");
			var ord = $this.attr("ord");
			$(this).css('cursor', 'hand');
			//var idlote2=$this.attr("idloteins");

			if (idpool == "") {
				if (idlotepool) {
					criapoold(idlote, idlote, ord, idlotepool);
				} else {
					criapool(idlote, idlote, ord);
				}

			} else {
				if (idlotepool) {
					geralotepoold(idpool, idlote, idlotepool);
				} else {
					geralotepool(idpool, idlote);
				}
			}
		}
	});

	var ulrpadrao = "_modulo=gerenciaprodcorpo&idprodserv=" + <?= $arridresultado[$controleass] ?>;


	function copiaLink() {
		var sLink = window.location.search;

		const input = document.createElement('input');
		document.body.appendChild(input);
		input.value = "<?= $_SERVER['SERVER_NAME'] ?>" + sLink;
		input.select();
		const isSuccessful = document.execCommand('copy');
		input.style.display = 'none';
		if (!isSuccessful) {
			console.error('Failed to copy text.');
		} else {
			alertAzul("Link Copiado", "", 1000);
		}
		document.body.removeChild(input);
	}


	$(".historicoLotePool").each(function(index, elemento) {
		$(this).webuiPopover({
			trigger: "hover",
			placement: "bottom-left",
			width: 600,
			delay: {
				show: 300,
				hide: 0
			}
		});
	});

	function alterarprioridade(vthis,inidlote){
		debugger;
    // alert(inidrhevento);
    var prioriant = $(vthis).attr('prioriant');
	var prioridest = $(vthis).attr('prioridest');
    var nova_st;
    var ant_bt;
    var bt;
   
    if(prioridest=='ALTA'){
        nprioriant='ALTA';
		nprioridest="NORMAL";
        bt="verde hovercinza";
		ant_bt="cinzaclaro hoververde";
		title='Não Guardar';

    }else{
		nprioriant='NORMAL';
		nprioridest="ALTA";
        bt="cinzaclaro hoververde";
		ant_bt="verde hovercinza";
		title='Guardar';
	}
  
    CB.post({
        objetos: "_x_u_lote_idlote="+inidlote+"&_x_u_lote_prioridade="+prioridest        
		,parcial:true
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){
			$(vthis).attr('prioriant',nprioriant);
			$(vthis).attr('prioridest',nprioridest);
			$(vthis).attr('title',title);
            $(vthis).removeClass( ant_bt ).addClass( bt );
		}
    });

	}
</script>