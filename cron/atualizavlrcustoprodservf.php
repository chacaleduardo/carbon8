<?//maf270320: Colocar a reconfiguracao da arvore de protuso e insumos em modo background

ini_set("display_errors","1");
error_reporting(E_ALL);
 
if(defined('STDIN')){//se estiver sendo executao em linhade comando
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/inc/php/laudo.php");
}
else{//se estiver sendo executado via requisicao htt
	include_once("../inc/php/functions.php");
	include_once("../inc/php/laudo.php");
}

// Variavel para forçar a atualização de todas as fórmulas
$atualizaf=d::b()->real_escape_string($_REQUEST['atualizaf']);
// Variavel para forçar a atualização de uma formula específica
$idprodserv=d::b()->real_escape_string($_REQUEST['idprodserv']);
$idprodservformula=d::b()->real_escape_string($_REQUEST['idprodservformula']);
// Variavel para forçar a atualização de uma empresa específica
$idempresa=d::b()->real_escape_string($_REQUEST['idempresa']);

// Se for o ultimo dia ou dia 15 do mês ele deve atualizar todas as formulas
$atual = date('Y-m-d');
$ultimodia = date("Y-m-t");

// Verificar se é o último dia do mês ou o dia 15
if($atual == $ultimodia || date('d', strtotime($atual)) == 15) {
	$atualizaf = 'Y'; // Define $atualizaf como 'Y' se for o último dia do mês ou o dia 15
}

// Log de execução da cron
$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`)
			VALUES ('1', '".$grupo ."', 'cron', 'atualizavlrcustoprodserv', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'));";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

// Atualizar fórmulas de produtos e serviços
if($atualizaf=='Y'){
	$sql = "SELECT idprodservformula, idprodserv from prodservformula WHERE status not in ('INATIVO')";
	
	if($idprodserv){
		$sql .= " and idprodserv in (".$idprodserv.")";
	}
	if($idprodservformula){
		$sql .= " and idprodservformula in (".$idprodservformula.")";
	}
	if($idempresa){
		$sql .= " and idempresa in (".$idempresa.")";
	}

	echo "<pre>".$sql."</pre>";
	$res= d::b()->query($sql);
	$sqlupdf = "";

	if(mysqli_num_rows($res)){
		while($row=mysqli_fetch_assoc($res)){
			$valoritem=0;
			$vlr=buscavalorprodformula($row["idprodservformula"],1,'N');
			$vlr = str_replace(".", "", $vlr);
			$vlr = str_replace(",", ".", $vlr);
			$sqlux="update prodservformula set vlrcusto = '".$vlr."',atualizaarvore='N' WHERE idprodservformula = ".$row["idprodservformula"];
			echo "<pre>".$sqlux."</pre>";
			$resx=d::b()->query($sqlux) or die($sqlux.mysqli_error(d::b()));
			$sqld="delete from prodservformulaitem where idprodservformula=".$row["idprodservformula"];
			d::b()->query($sqld);

			$valoritem=0;
			$vlr=prodformulaitem($row["idprodservformula"],$row["idprodservformula"],1,'N');
		}
	}
}

// Buscar os lotes produzidos
$sql = "select l.idlote,ifnull(l.qtdprod,1) as qtdprod, p.fabricado
		from lote l
		join prodserv p on p.idprodserv = l.idprodserv
		where l.idprodservformula is not null
		and l.vlrlote is null and l.exercicio >= year(DATE_SUB(now(), INTERVAL 1 year))
		and l.status in ('APROVADO','LIBERADO','QUARENTENA','CANCELADO','REPROVADO')
		order by l.alteradoem asc
		";

echo "<pre>".$sql."</pre>";
$res= d::b()->query($sql);
$sqlupd = "";

while($row=mysqli_fetch_assoc($res)){
	
	// Remove os itens dos lotes
	$sqld="delete from loteitem where idlote=".$row["idlote"];
	d::b()->query($sqld);
	
	// Busca (recalcula) o valor do lote e refaz os itens
	$valorlote=buscavalorlote($row["idlote"],1,'Y',$row["idlote"]);
	if($row['fabricado']=='Y'){
		$valorlote += buscavalortestes($row["idlote"]);
		$valorlote += buscarateios($row["idlote"]);
	}
	$vlr=$valorlote/$row['qtdprod'];
	$sqlupd .="update lote set vlrlote = '".$vlr."',vlrlotetotal='".$valorlote."' WHERE idlote = ".$row["idlote"].";\n";
	
	//atualiza o lotes que usaram o lote
	atualizaloteproduzido($row["idlote"]);
}

echo "<pre>".$sqlupd."</pre>";
// Atualiza os lotes
$resupd = d::b()->multi_query($sqlupd) or die();

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
			VALUES ('1', '".$grupo ."', 'cron', 'atualizavlrcustoprodserv', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'));";

echo "<pre>".$sqli."</pre>";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

function atualizaloteproduzido($idlote){
	$sql1="select l.idlote,ifnull(l.qtdprod,1) as qtdprod
					from lotecons c 
					join lote l on(l.idlote =c.idobjeto)
					where c.idlote =".$idlote." 
					and tipoobjeto = 'lote' 
					and c.qtdd>0 group by c.idobjeto";


	echo "<pre>".$sql1."</pre>";
	$res1= d::b()->query($sql1);
	//$sqlupd = "";
	
	while($row1=mysqli_fetch_assoc($res1)){		
			$sqld1="delete from loteitem where idlote=".$row1["idlote"];
			d::b()->query($sqld1);
			
			$valorlote=buscavalorlote($row1["idlote"],1,'Y',$row1["idlote"]);
			$vlr=$valorlote/$row1['qtdprod'];
			$sqlupd1 ="update lote set vlrlote = '".$vlr."',vlrlotetotal='".$valorlote."' WHERE idlote = ".$row1["idlote"].";\n";
			$resup1= d::b()->query($sqlupd1);
	}
}

function buscavalorlote($inidlote,$percentual,$zerar,$idlotepai, $lvl = 0){
	if($zerar=='Y'){
			global  $valor;
			$valor = 0;
	}else{
		 global $valor;
	}

	$sql="select
			idloteinsumo as idlote,
			idempresa,
			idprodserv,
			qtdd,
			qtdd_exp,
			qtdprod,
			vlrlote,
			vlrlotetotal,
			qtdproduzido,
			idprodservformula,
			comprado,
			fabricado,
			descr,
			unpadrao,
			partida,
			descr
		from vw8LoteConsInsumo
		where idlote=".$inidlote;
			
	$res= d::b()->query($sql);
	$valorc=0;

	while($row=mysqli_fetch_assoc($res)){
		if($row['fabricado']=='Y'){
			$percentualcon=$row['qtdd']/$row['qtdproduzido'];
			$percent=$percentual*$percentualcon;

			
			if($row['vlrlotetotal']>0){
				$valorcpf=(($row['vlrlotetotal']/$row['qtdprod'])*$row['qtdd'])*$percentual; 
			}else{
				$valorcpf=($row['vlrlote']*$row['qtdd'])*$percentual; 
			}
			
			$qtdcons = ($row['qtdd']);

			$sqli="INSERT INTO loteitem
							(idempresa,idlote,idloteins,idprodserv,qtd,qtd_exp,un,valorun,valortotal,partida,descr,nivel,fabricado)
							VALUES
							(".$row['idempresa'].",".$idlotepai.",".$row['idlote'].",".$row['idprodserv'].",'".$qtdcons."','".$row['qtdd_exp']."','".$row['unpadrao']."','".$row['vlrlote']."','".$valorcpf."','".$row['partida']."','".$row['descr']."','".$lvl ."','Y');";

			echo "<pre>".$sqli."<pre>";
			$resf=d::b()->query($sqli);
			$valorform=buscavalorlote($row['idlote'],$percent,'N',$idlotepai,$lvl + 1);

		}elseif($row['fabricado']=='N' and $row['vlrlote']>0){
			$valorcp=($row['vlrlote']*$row['qtdd'])*$percentual;
			$valorc=$valorc+$valorcp;
			$qtdcons = ($row['qtdd'] * $percentual);

			$sqli="INSERT INTO loteitem (idempresa,idlote,idloteins,idprodserv,qtd,qtd_exp,un,valorun,valortotal,partida,descr,nivel,fabricado)
					VALUES (".$row['idempresa'].",".$idlotepai.",".$row['idlote'].",".$row['idprodserv'].",'".$qtdcons."','".$row['qtdd_exp']."','".$row['unpadrao']."','".$row['vlrlote']."','".$valorcp."','".$row['partida']."','".$row['descr']."','".$lvl ."','N');";
			
			echo "<pre>".$sqli."<pre>";
			$resf=d::b()->query($sqli);
		}
	}//while($row=mysqli_fetch_assoc($res)){
	
	$valor=$valor+($valorc);
	
	return $valor;

}//function buscarvalorform($inidprodservformula,$inidplantel){

function prodformulaitem($inidprodservformulapai,$inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0)
{
	global $excel;

	if ($lvl > 0) {
		$m = $lvl * 15;
		$margin = "margin-left:".$m."px;";
	} else {
		$margin = "";
	}
	
	global $valoritem;
	if($lvl == 0){
		$valoritem = 0;
	}
	
	$sql = "SELECT * FROM (SELECT p.idempresa,
									i.idprodservformulains,
									i.qtdi,
									i.qtdi_exp,
									i.idprodserv,
									p.fabricado,
									p.descr,
									CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
									p.un,
									fi.idprodservformula,
									IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
								FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
								JOIN prodserv p ON (p.idprodserv = i.idprodserv)
								JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
							WHERE f.idprodservformula = '$inidprodservformula' 
						UNION SELECT p.idempresa,
									i.idprodservformulains,
									i.qtdi,
									i.qtdi_exp,
									i.idprodserv,
									p.fabricado,
									p.descr,
									CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
									p.un,
									fi.idprodservformula,
									IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
								FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
								JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
								JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
							WHERE f.idprodservformula = '$inidprodservformula'
								AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
						UNION SELECT p.idempresa,
									i.idprodservformulains,
									i.qtdi,
									i.qtdi_exp,
									i.idprodserv,
									p.fabricado,
									p.descr,
									'' AS rotulo,
									p.un,
									NULL,
									IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc
								FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
								JOIN prodserv p ON (p.idprodserv = i.idprodserv)
							WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
							GROUP BY idprodservformulains
							ORDER BY fabricado";

	$res = d::b()->query($sql);
	
	while ($row = mysqli_fetch_assoc($res)) {
		$linha = $linha + 1;

		// Concatena os contadores dos níveis para formar $nivel
		if($lvl == 0){
			cb::$session["nivel_old"] = $nivel;
			$nivel = $nivel + 1;
		} else {
			$arrayNivel = explode('.', $nivel);
			$contador = count($arrayNivel);
			if($lvl_old <> $lvl){
				$nivel = $nivel.'.1';
			} else {
				$arrayNivel[$contador - 1]++;
				$nivel = implode('.', $arrayNivel);
			}
		}

		if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {

			$valorQtd = tratanumero($row['qtdi'] * $percentagem);
								

			$sqli="INSERT INTO prodservformulaitem 
			(idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal )
			VALUES
			(".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','Y',0,0);";
			echo("<pre>".$sqli."<pre>");
			$resf=d::b()->query($sqli);

			$lvl_old = $lvl;
			prodformulaitem($inidprodservformulapai,$row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old);
			
		} elseif ($row['fabricado'] == 'N') {
			$valor = buscavaloritem($row['idprodserv'], $row['qtdi']);

			$valorun = buscavalorloteprod($row['idprodserv'],1);
			$valor = $valor * $percentagem;
				
			$valorQtd = tratanumero($row['qtdi'] * $percentagem);

			$sqli="INSERT INTO prodservformulaitem 
			(idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal)
			VALUES
			(".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','N','". $valorun ."','". $valor ."');";
			echo("<pre>".$sqli."<pre>");
			$resf=d::b()->query($sqli);

			$lvl_old = $lvl;
		}
	} //while($row=mysqli_fetch_assoc($res)){

	return  number_format(tratanumero($valoritem), 4, ',', '.');
} //function buscarvalorform($inidprodservformula,$inidplantel){


function buscavaloritem($inidprodserv, $qtdi)
{
	$sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
	from lote l 
	where l.idprodserv = ".$inidprodserv." 
	and vlrlote > 0
	and l.status!='CANCELADO'
	order by idlote desc limit 1";
	$res = d::b()->query($sql);
	$row = mysqli_fetch_assoc($res);
	$valor = round(($qtdi * $row['valoritem']), 4);
	return $valor;
}

function  buscavalorloteprod($inidprodserv,$qtdi=1)
{

		$sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
		from lote l 
		where l.idprodserv = ".$inidprodserv."  and vlrlote > 0   order by idlote desc limit 1";
		$res = d::b()->query($sql);
		$row = mysqli_fetch_assoc($res);
		$vlri=$row['valoritem']*$qtdi;
		$valor = round(($vlri), 4);
		return $valor;
}

function buscavalorprodformula($inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0, $insumosAvoNeto = false)
{
		global $excel, $insumosAvoNeto, $arrayProduto;

		if ($lvl > 0) {
				$m = $lvl * 15;
				$margin = "margin-left:".$m."px;";
		} else {
				$margin = "";
		}
		global $valoritem;
		if($lvl == 0){
				$valoritem = 0;
		}
		
		$sql = "SELECT * FROM (
													SELECT i.idprodservformulains,
																	i.qtdi,
																	i.qtdi_exp,
																	i.idprodserv,
																	p.fabricado,
																	p.descr,
																	CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
																	p.un,
																	fi.idprodservformula,
																	IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
																	f.idprodserv as idprodservpai
															FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
															JOIN prodserv p ON (p.idprodserv = i.idprodserv)
															JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
														WHERE f.idprodservformula = '$inidprodservformula' 
													UNION SELECT i.idprodservformulains,
																	i.qtdi,
																	i.qtdi_exp,
																	i.idprodserv,
																	p.fabricado,
																	p.descr,
																	CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
																	p.un,
																	fi.idprodservformula,
																	IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc,
																	f.idprodserv as idprodservpai
														FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
														JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
														JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
														WHERE f.idprodservformula = '$inidprodservformula'
															AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
													UNION SELECT i.idprodservformulains,
																	i.qtdi,
																	i.qtdi_exp,
																	i.idprodserv,
																	p.fabricado,
																	p.descr,
																	'' AS rotulo,
																	p.un,
																	NULL,
																	IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc,
																	f.idprodserv as idprodservpai
														FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
														JOIN prodserv p ON (p.idprodserv = i.idprodserv)
														WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
													GROUP BY idprodservformulains
													ORDER BY fabricado";

		$res = d::b()->query($sql);
		
		while ($row = mysqli_fetch_assoc($res)) {
				$linha = $linha + 1;

				// Concatena os contadores dos níveis para formar $nivel
				if($lvl == 0){
						cb::$session["nivel_old"] = $nivel;
						$nivel = $nivel + 1;
						$negritoInicial = '<b>';
						$negritoFinal = '</b>';
				} else {
						$arrayNivel = explode('.', $nivel);
						$contador = count($arrayNivel);
						if($lvl_old <> $lvl){ 
								$nivel = $nivel.'.1';
						} else {
								$arrayNivel[$contador - 1]++;
								$nivel = implode('.', $arrayNivel);
						}
				}

				if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
						if ($detalhado == "Y") {
								?>
								<div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
										<div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
										<div class="col-md-2">
												<span href="#collapse-vallote-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="pointer">
														<i class="fa fa-angle-right" style="padding: 5px 10px;"></i>
												</span>
												<? if (empty($row['qtdi_exp'])) {
														$valorQtd = number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.');
														$valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
												} else {
														$valorQtd = recuperaExpoente(tratanumero($row['qtdi'] * $percentagem), $row['qtdi_exp']);
														$valorQtdValue = number_format(tratanumero($row['qtdi'] * $percentagem), 4, '.', '');
												}
												?> 
												<span class="qtdun-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$valorQtdValue;?>"><?=$valorQtd." ".$row['un'] ?></span>
										</div>
										<div class="col-md-6">
												<a class="pointer" onclick="janelamodal('?_modulo=formulaprocesso&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>

										</div>
										<div class="col-md-1 valloteunacumulado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteunacumulado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>
										<div class="col-md-2 valloteacumulado-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" idlotecons-valloteacumulado="<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=$row['idprodservformulains'] ?>_<?=$linha ?>"></div>
								</div>
								<div id="collapse-vallote-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" class="hidden">
								<?

								$excel .= '<tr>
														<td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
														<td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
														<td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.')." ".$row['un'].$negritoFinal.'</td>
														<td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
														<td style="width: 50px;" class="idlotecons-valloteunacumulado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
														<td style="width: 50px;" class="idlotecons-valloteacumulado-table-'.$row['idprodservformulains'].'_'.$linha.'">'.$negritoInicial.$negritoFinal.'</td>
												</tr>';
						}

						$lvl_old = $lvl;

						if(strlen($lvl) == 1){
								cb::$session['arvore'][$lvl][ 'idprodserv'][] = $row['idprodserv'];
						}
						
						//Não deixa entrar me loop infinito quando o pai tem como filho o próprio pai
						if(in_array($_GET['idprodserv'], cb::$session['arvore'][1]['idprodserv'])){
								$insumosAvoNeto = true;
								$arrayProduto['idprodserv'] = $row['idprodservpai'];
						} else {
									buscavalorprodformula($row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old, $insumosAvoNeto);
						}
						if ($detalhado == "Y") {
								?>
								</div>
						<?
						}
				} elseif ($row['fabricado'] == 'N') {
						$valor = buscavaloritem($row['idprodserv'], $row['qtdi']);

						$valorlote = buscavalorloteprod($row['idprodserv'],$row['qtdi']);
						$valorun = buscavalorloteprod($row['idprodserv'],1);
						$valor = $valor * $percentagem;

						if ($detalhado == "Y") { ?>
								<div class="col-md-12" style="border-bottom: 1px solid #cec8c8b3;<?=$margin ?>" lvl="<?=$lvl ?>">
								<div class="col-md-1"><?if($principal == 0){ echo $linha; }?></div>
										<div class="col-md-2">
												<span>
														<i class="fa" style="padding: 5px 12px;"></i>
												</span>
												<? $valorQtd = tratanumero($row['qtdi'] * $percentagem) ?>
												<span class="qtdun-<?=$row['idprodservformulains'] ?>_<?=$linha ?>" value="<?=number_format(tratanumero($row['qtdi']), 4, ',', '.');?>"><?=number_format(tratanumero($row['qtdi']), 4, '.', ',')." ".$row['un'] ?></span>
										</div>
										<div class="col-md-6">
												<a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>')"><?=$row['descr'] ?> <?=$row['rotulo'] ?></a>
										</div>
										<div class="col-md-1" style="text-align: right;" valloteun="<?=$valorun?>">R$ <?=number_format(tratanumero($valorun), 4, ',', '.')?></div>
										<div class="col-md-2" vallote="<?=$valor ?>">
												<? //echo('('.$row['vlrlote'].'*'.$row['qtdd'].')*'.$percent.')= ')?>
												<span style="float:right" title="R$: <?=$valor ?> / Valor Lote R$: <?=$valorun ?>">R$ <?=number_format(tratanumero($valor), 4, ',', '.') ?></span>

												<? //=$valor
												?>
										</div>
								</div>
								
								<?
								$excel .= '<tr>
																<td style="text-align: right; width: 50px;">'.$negritoInicial.$nivel.$negritoFinal.'</td>
																<td style="text-align: right; width: 50px;">'.$row['idprodserv'].'</td>
																<td style="width: 50px;">'.$negritoInicial.number_format(tratanumero($row['qtdi'] * $percentagem), 4, ',', '.')." ".$row['un'].$negritoFinal.'</td>
																<td style="width: 200px;">'.$negritoInicial.$row['descr']." ".$row['rotulo'].$negritoFinal.'</td>
																<td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valorun), 4, ',', '.').$negritoFinal.'</td>
																<td style="width: 50px;">R$ '.$negritoInicial.number_format(tratanumero($valor), 4, ',', '.').$negritoFinal.'</td>
														</tr>';
						}

						$valoritem = $valoritem + $valor;
						$lvl_old = $lvl;
				}
		} //while($row=mysqli_fetch_assoc($res)){
		
		cb::$session['arvore'] = NULL;

		return  number_format(tratanumero($valoritem), 4, ',', '.');
} //function buscarvalorform($inidprodservformula,$inidplantel){

function buscavalortestes($inidlote){
	$sql = "
			SELECT round(sum(valor),4) as valor
				from (
				SELECT round(sum(c.qtdd*l.vlrlote*r.quantidade),4) as valor
				FROM loteativ a
				JOIN objetovinculo o ON o.tipoobjetovinc  = 'loteativ' AND o.idobjetovinc = a.idloteativ
				JOIN lotecons c ON c.idobjeto=o.idobjeto AND c.tipoobjeto='resultado' AND c.qtdd >0 AND c.status='ABERTO'
				JOIN lote l ON l.idlote=c.idlote and l.status!='CANCELADO'
				JOIN resultado r ON r.idresultado=c.idobjeto
				JOIN amostra am ON am.idamostra=r.idamostra
				JOIN prodserv p ON p.idprodserv=r.idtipoteste
				WHERE  a.idlote='{$inidlote}'
				GROUP BY idregistro
				UNION
				select round(ifnull(r.custo,0)*r.quantidade,4) as valor
				from loteativ at 
				join  bioensaio b on(b.idloteativ) =at.idloteativ
				join analise a on(a.idobjeto = b.idbioensaio AND a.objeto = 'bioensaio')
				join servicoensaio s on( s.idobjeto = a.idanalise AND s.tipoobjeto ='analise')
				join resultado r on(r.idservicoensaio = s.idservicoensaio and r.status != 'CANCELADO')
				join prodserv p on(p.idprodserv = r.idtipoteste)
				join amostra am on(am.idamostra = r.idamostra)
				where at.idlote='{$inidlote}'
			) as testes
        ";
    
	$res = d::b()->query($sql);
		
	if(mysqli_num_rows($res)){
		$row = mysqli_fetch_assoc($res);
		if($row['valor']){
			return $row['valor'];
		}
	}
	
	return 0;
}

function buscarateios($inidlote){
	$sql = "
		SELECT
		lc.idlotecusto,
		lc.idlote,
		lc.idrateiocusto,
		lc.idempresa,
		lc.idobjeto AS idunidade,
		lc.criadoem,
		lc.valor,
		e.sigla,
		e.empresa,
		u.unidade
		,rc.datainicio
		,rc.datafim
	FROM
		lotecusto lc
		JOIN empresa e ON e.idempresa = lc.idempresa
		JOIN unidade u ON u.idunidade = lc.idobjeto AND lc.tipoobjeto = 'unidade'
		JOIN rateiocusto rc ON rc.idrateiocusto = lc.idrateiocusto
	WHERE
		lc.idlote = '{$inidlote}'
		";
	
	$res = d::b()->query($sql);

	$valortotal = 0;
	if(mysqli_num_rows($res)){
		while ($row = mysqli_fetch_assoc($res)){
			$valortotal += $row['valor'];
		}
	}
	
	return $valortotal;
}