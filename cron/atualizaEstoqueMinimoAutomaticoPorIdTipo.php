<?
session_start();
$sessionid = session_id(); //PEGA A SESSÃO

ini_set("display_errors", "1");
error_reporting(E_ALL);

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
	require_once("/var/www/carbon8/inc/php/functions.php");
} else { //se estiver sendo executado via requisicao http
	require_once("../inc/php/functions.php");
}

$_inspecionar_sql = ($_GET["_inspecionar_sql"]=="Y") ? true : false;
$idprodserv = $_GET['idprodserv'];

$grupo = rstr(8);
//re::dis()->hMSet('cron:atualizaestoqueminimoautomatico',['inicio'  = > Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
		 		VALUES ('1', '".$grupo."', 'cron', 'atualizaestoqueminimoautomatico', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

//atualizar o estoque dos produtos
$sqlp = "SELECT idprodserv, nqtdest FROM(SELECT p.idprodserv, p.idunidadeest, ifnull(sum(f.qtd), 0) as nqtdest, ifnull(p.qtdest, 0) as qtdest
										   FROM prodserv p LEFT JOIN lote l ON (p.idprodserv = l.idprodserv AND l.status IN ('APROVADO','QUARENTENA'))
										   LEFT JOIN lotefracao f ON(f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND f.idunidade = p.idunidadeest )
										   WHERE p.idunidadeest IS NOT NULL
										   AND p.status = 'ATIVO'
										   AND p.tipo = 'PRODUTO'
										   -- AND (p.comprado = 'Y' or fabricado = 'Y')
										   GROUP BY p.idprodserv,p.idunidadeest) AS u 
		WHERE u.qtdest!= u.nqtdest";
$resp = d::b()->query($sqlp) or die("erro ao buscar produto para atualizar estoque: ".mysqli_error(d::b())."<br>".$sqlp);
while ($rowp = mysqli_fetch_assoc($resp)) 
{
	$sqlp1 = " UPDATE prodserv SET qtdest = ".$rowp['nqtdest']." WHERE idprodserv  = ".$rowp['idprodserv'].";";
	$resp1 = d::b()->query($sqlp1) or die("erro ao atualizar quantidade em estoque: ".mysqli_error(d::b())."<br>".$sqlp1);
}

date_default_timezone_set('America/Sao_Paulo');
$horaAtual = date('H:i', time());
//Atualizar Produtos Inativos
if ($horaAtual !=  '23:00') {
	$condicaoAtualizarSomenteProdutosAtivos = " AND p.status = 'ATIVO' ";
}

echo "########################### NÃO FORMULADOS ###########################<br><br>";
if($_GET['tipo'] == 'NAOFORMULADOS' && !empty($idprodserv))
{
	// RODA PARA OS NÃO FORMULADOS
	$sql = "SELECT 
				p.idprodserv,
				p.idempresa,
				p.estoqueseguranca,
				p.temporeposicao,
				p.tempocompra,
				p.idunidadeest,
				p.valconv,
				p.consumodiaslote as consumodias,
				p.estmin,
				p.tempoconsrateio,
				ifnull(sum(f.qtd),0) as qtdest
			FROM prodserv p 
			LEFT JOIN  lote l ON(l.idprodserv = p.idprodserv AND l.status IN ('APROVADO','QUARENTENA'))
			LEFT JOIN lotefracao f ON(f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND  f.idunidade = p.idunidadeest)
			WHERE p.comprado = 'Y' 
			AND p.tipo = 'PRODUTO' 
			$condicaoAtualizarSomenteProdutosAtivos
			AND p.idprodserv IN($idprodserv)
			GROUP BY p.idprodserv";

	if($_inspecionar_sql){
		echo "<pre>".$sql."</pre>";
	}

	$res = d::b()->query($sql) or die("erro ao buscar média diária dos produtos: ".mysqli_error(d::b())."<br>".$sql);
	$su = "";
	$ins = "";
	$su1 = ""; // somente para print das querys
	$minimoauto = 0;

	while ($row = mysqli_fetch_assoc($res)) 
	{
		$tqtdd = 0;
		$tqtdc = 0;

		(!empty($row["temporeposicao"])) ? $temporeposicao = $row["temporeposicao"] : $temporeposicao = 0;
		(!empty($row["estoqueseguranca"])) ? $estoqueseguranca = $row["estoqueseguranca"] : $estoqueseguranca = 0;
		(!empty($row["tempocompra"])) ? $tempocompra = $row["tempocompra"] : $tempocompra = 0;
		(!empty($row["qtdest"])) ? $qtdest = $row["qtdest"] : $qtdest = 0;

		($row["valconv"] ==  0) ? $auxconv = 1 : $auxconv = $row["valconv"];

		$sql_pe = "SELECT ifnull(sum(i.qtd * if(pf.valconv>0, pf.valconv,1)),0) as qtdpa		
					FROM nfitem i JOIN nf n ON(n.idnf = i.idnf AND n.tiponf!= 'V' AND status IN ('APROVADO', 'CONFERIDO'))
					LEFT JOIN prodservforn pf ON(pf.idprodservforn = i.idprodservforn)
					WHERE nfe = 'Y' AND i.idprodserv = ".$row["idprodserv"];
		$_respe = d::b()->query($sql_pe) or die("Erro ao consultar pedidos em andamento do produto:".mysqli_error(d::b()));
		$_rowpa = mysqli_fetch_assoc($_respe);

		$_sqlaux0 = "SELECT c.idlotefracao, c.qtdd, c.qtdc,c.status
					FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)						
					JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 or c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
					WHERE lf.idunidade = ".$row["idunidadeest"]."
					AND l.idprodserv = ".$row["idprodserv"]."
					AND l.status not IN ('CANCELADO','CANCELADA')
					AND l.idprodservformula is null
					AND c.criadoem > DATE_SUB(now(), INTERVAL 60 DAY)";
		$_resaux0 = d::b()->query($_sqlaux0) or die("Erro ao consultar histórico de consumo do produto:".mysqli_error(d::b()));
		while ($_rowaux0 = mysqli_fetch_assoc($_resaux0)) 
		{
			$sqlu = "SELECT u.convestoque 
					FROM lotefracao l 
					JOIN unidade u ON (l.idunidade = u.idunidade) 
					WHERE l.idlotefracao = ".$_rowaux0["idlotefracao"];
			$resu = d::b()->query($sqlu) or die("Erro ao consultar idlotefracao:".$sqlu." - ".$row["idprodserv"]);
			$rowu = mysqli_fetch_assoc($resu);
			if ($rowu["convestoque"] ==  "Y") {
				$aqtdd = $_rowaux0["qtdd"] / $auxconv;
				$aqtdc = $_rowaux0["qtdc"] / $auxconv;
			} else {
				$aqtdd = $_rowaux0["qtdd"];
				$aqtdc = $_rowaux0["qtdc"];
			}
			if ($_rowaux0['status'] !=  'INATIVO' && $_rowaux0['status'] !=  'DEVOLUCAO') {
				$tqtdd +=  $aqtdd;
				$tqtdc +=  $aqtdc;
			}
		}
		//$mediadiaria = ($tqtdd - $tqtdc)/60;
		///$mediadiaria = ($tqtdd)/$row['consumodias'];
		$mediadiaria = ($tqtdd) / 60;
		if ($mediadiaria < 0) {
			$mediadiaria *=  -1;
		}
		$minimoauto = (($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca));
		$pedidoauto = (($mediadiaria * $tempocompra));
		$pedido_auto = ($temporeposicao * $mediadiaria) + ($minimoauto - $qtdest) - ($_rowpa['qtdpa']);
		echo ("IDprodserv = ".$row["idprodserv"]." calculo -> ".$pedido_auto." = (".$temporeposicao."*".$mediadiaria.")+(".$minimoauto."-".$qtdest.")-(".$_rowpa['qtdpa'].")<br>");

		$sugestaoCompra2 = (($mediadiaria * $tempocompra) + ($row['estmin'] - $row['qtdest'])) - $_rowpa['qtdpa'];

		if ($qtdest > 0 && $mediadiaria > 0) {
			$diasestoque = $qtdest / $mediadiaria;
			echo ("calculo =  ".$qtdest."/".$mediadiaria." = ".$diasestoque."<br>");
		} else {
			$diasestoque = 0;
		}

		if ($pedido_auto < 0) {
			$pedido_auto = 0;
		}
		if ($diasestoque < 0 || !is_numeric($diasestoque)) {
			$diasestoque = 0;
		}

		$sqlultimoOrcamento = "SELECT DISTINCT(c.idcotacao) AS idcotacao
								FROM nfitem i JOIN nf n ON (i.idnf = n.idnf AND n.tipoobjetosolipor = 'cotacao' ".getidempresa('n.idempresa', 'nf')." AND n.tiponf !=  'V'
								AND n.status IN ('ABERTO', 'APROVADO', 'ENVIADO', 'INICIO', 'RECEBIDO', 'RESPONDIDO', 'AUTORIZADO', 'AUTORIZADA'))
								JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
								JOIN fluxostatus f ON f.idfluxostatus = n.idfluxostatus
								JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
								WHERE i.nfe = 'Y' AND i.idprodserv = ".$row["idprodserv"]."
								GROUP BY i.idprodserv";
		$resultimoOrcamento = d::b()->query($sqlultimoOrcamento) or die("Erro ao buscar ultimo Orçamento Não Formulados:".mysqli_error(d::b()));
		$rowultimoOrcamento = mysqli_fetch_assoc($resultimoOrcamento);
		if($_inspecionar_sql){
			echo "<pre>".$sqlultimoOrcamento."</pre>";
		}

		$ultimoOrcamento = empty($rowultimoOrcamento['idcotacao']) ? 'NULL' : $rowultimoOrcamento['idcotacao'];
		//@523299 - MÓDULO FILTRO PESQUISA: CALCULO DE ESTOQUE
		//adicionado mediadiaria na prodserv para o modulo filtro calculoestoque
		$suPrdTodos =  "UPDATE prodserv  
				SET qtdest = ".$qtdest.",
					destoque = ".$diasestoque.",
					mediadiaria = ".$mediadiaria.", 
					estminautomatico = ".$minimoauto.", 
					pedidoautomatico = ".$pedidoauto.", 
					pedido_automatico = ".$pedido_auto.", 
					sugestaocompra2 = ".$sugestaoCompra2.", 
					ultimoorcamento = $ultimoOrcamento
				WHERE idprodserv = ".$row["idprodserv"].";";
		d::b()->query($suPrdTodos) or die("Erro atualizar linha 164. sql: ".$ins);
		if($_inspecionar_sql){
			echo "<pre>".$suPrdTodos."</pre>";
		}

		$suProdLim =  "UPDATE prodserv 
					SET destoque = ".$diasestoque.", 
					estminautomatico = ".$minimoauto.", 
					pedidoautomatico = ".$pedidoauto.",
					pedido_automatico = ".$pedido_auto." 
					WHERE idprodserv = ".$row["idprodserv"].";";
		d::b()->query($suProdLim) or die("Erro atualizar linha 178. sql: ".$ins);
		if($_inspecionar_sql){
			echo "<pre>".$suProdLim."</pre>";
		}

		$sqlprod = "SELECT count(*) as nlinhas FROM prodcomprar WHERE idprodserv = ".$row["idprodserv"]." AND status = 'ATIVO'";
		$resprod = d::b()->query($sqlprod) or die("Erro ao consultar prodcomprar:".$sqlprod." - ".$row["idprodserv"]);
		$rowprod = mysql_fetch_assoc($resprod);

		$sqlp = 'SELECT CASE WHEN p.estmin > 0 THEN "Y" ELSE "N" END as controlaestoq 
				FROM prodserv p 
				WHERE p.idprodserv = '.$row["idprodserv"];
		$resp = d::b()->query($sqlp) or die("Erro ao consultar controle de estoque do produto: ".mysqli_error(d::b()));
		$np = mysql_num_rows($resp);

		if ($np > 0) 
		{
			$rowp = mysql_fetch_assoc($resp);
			if ($rowp["controlaestoq"] ==  'Y') 
			{
				$sqll = "SELECT IF(p.estmin >=  ifnull(SUM(lf.qtd),0), 'Y', 'N') as controle ,COUNT(pc.idprodcomprar) as quantidade 
						FROM prodserv p
						LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.status IN ('APROVADO' , 'QUARENTENA'))
						LEFT JOIN lotefracao lf ON(lf.idlote = l.idlote AND lf.idunidade = p.idunidadeest AND lf.status = 'DISPONIVEL')
						LEFT JOIN prodcomprar pc ON (pc.idprodserv = p.idprodserv AND pc.status = 'ATIVO')
						WHERE p.idprodserv = ".$row["idprodserv"]." AND p.idunidadeest is not null
						GROUP BY p.idprodserv";
				$resl = d::b()->query($sqll) or die("Erro ao consultar controle de estoque do produto: ".mysqli_error(d::b()));

				$rowl = mysql_fetch_assoc($resl);
				$prtaux = "";
				($verify) ? $prtaux = "true" : $prtaux = "false";
				echo "<br>******************************************************************************<br>";
				echo "<pre>".$sqll."</pre><br>";
				echo "Controle: ".$rowl["controle"]."<br>";
				echo "Quantidade: ".$rowl["quantidade"]."<br>";
				echo "Controlaestoq: ".$rowp["controlaestoq"]."<br>";
				echo "Verify: ".$prtaux."<br>";
				echo "Idempresa: ".$row["idempresa"]."<br>";
				echo "Idprodserv: ".$row["idprodserv"]."<br>";
				echo "Idprodservformula: 0<br>";
				echo "Pedido_Auto: ".$pedido_auto."<br>";
				echo "Dias de Estoque: ".$diasestoque."<br>";
				echo "PedidoAuto: ".$pedidoauto."<br>";
				echo "Nlinhas: ".$rowprod["nlinhas"]."<br>";
				echo "Média Diária: ".$mediadiaria."<br>";
				echo "Tempo Reposição: ".$temporeposicao."<br>";
				echo "Estoque Segurança: ".$estoqueseguranca."<br>";
				echo "Tempo Compra: ".$tempocompra."<br>";
				echo "Qtdest: ".$qtdest."<br>";
				echo "Mínimo Automático: ".$minimoauto."<br>";
				if ((($rowl["controle"] ==  'Y' && $rowl["quantidade"] ==  0) || (empty($rowl["controle"]) && $rowp["controlaestoq"] ==  'Y')) || (($pedido_auto > $pedidoauto) && ($rowprod["nlinhas"] ==  0))) 
				{
					$ins =  "INSERT INTO prodcomprar (idempresa,idprodserv,idprodservformula,idlote,criadoem,criadopor,alteradoem,alteradopor) VALUES (".$row["idempresa"].",".$row["idprodserv"].",0,0,sysdate(),'sislaudo',sysdate(),'sislaudo');";
					$ins1 =  "INSERT INTO prodcomprar (idempresa,idprodserv,idprodservformula,idlote,criadoem,criadopor,alteradoem,alteradopor) VALUES (".$row["idempresa"].",".$row["idprodserv"].",0,0,sysdate(),'sislaudo',sysdate(),'sislaudo');";
					d::b()->query($ins) or die("Erro ao inserir prodcomprar linha 246. sql: ".$ins);
					d::b()->query($ins1) or die("Erro ao inserir prodcomprar linha 247. sql: ".$ins1);
					if($_inspecionar_sql){
						echo "<pre>".$ins."</pre>";
						echo "<pre>".$ins1."</pre>";
					}
				} else {
					if (($rowl["controle"] ==  'N' && $rowl["quantidade"] > 0) && ($pedido_auto <=  $pedidoauto)) 
					{
						$su =  "UPDATE prodcomprar SET status = 'INATIVO' WHERE idprodserv = ".$row["idprodserv"]." AND idprodservformula = 0 AND status = 'ATIVO';";
						$su1 =  "UPDATE prodcomprar SET status = 'INATIVO' WHERE idprodserv = ".$row["idprodserv"]." AND idprodservformula = 0 AND status = 'ATIVO';";
						d::b()->query($su) or die("Erro ao atualizar prodcomprar linha 257. sql: ".$su);
						d::b()->query($su1) or die("Erro ao atualizar prodcomprar linha 257. sql: ".$su1);
						if($_inspecionar_sql){
							echo "<pre>".$su."</pre>";
							echo "<pre>".$su1."</pre>";
						}
					}
				}
			}
		}

		echo "AtualizaRateio: ".$row["idprodserv"]." - ".$row["idunidadeest"]." - ".$row["tempoconsrateio"]."<br>";
		if (!empty($row["idprodserv"]) && !empty($row["idunidadeest"])) 
		{
			atualizaRateio($row["idprodserv"], $row["idunidadeest"], 180, $row['tempoconsrateio']);
		}
	}
}

if($_GET['tipo'] == 'FORMULADOS' && !empty($idprodserv))
{
	// RODA PARA OS FORMULADOS
	$sql1 = "SELECT f.idprodservformula,
					f.idprodserv,
					f.idempresa,
					f.estoqueseguranca,
					f.temporeposicao,
					f.tempocompra,
					f.idunidadeest,
					p.valconv,
					f.estmin,
					f.consumodias,
					ifnull(SUM(lf.qtd),0) as qtdest
			FROM prodserv p JOIN  prodservformula f ON (f.idprodserv  = p.idprodserv AND f.status = 'ATIVO' AND f.idunidadeest is not null)
			LEFT JOIN lote l ON(l.idprodserv = p.idprodserv AND l.idprodservformula = f.idprodservformula AND l.status IN ('APROVADO', 'LIBERADO')) AND l.piloto = 'N'
			LEFT JOIN lotefracao lf ON(lf.idlote = l.idlote AND lf.idunidade = f.idunidadeest AND lf.status = 'DISPONIVEL')
			WHERE p.fabricado = 'Y'
			AND p.tipo = 'PRODUTO'
			$condicaoAtualizarSomenteProdutosAtivos
			AND p.idprodserv IN($idprodserv)
			GROUP BY  f.idprodservformula";

	echo "<br><br>########################### FORMULADOS ###########################<br><br>";
	if($_inspecionar_sql){
		echo "<pre>".$sql1."</pre>";
	}
	$res1 = d::b()->query($sql1) or die("erro ao buscar média diária das formulas: ".mysqli_error(d::b())."<br>".$sql1);
	$minimoauto = 0;

	while ($row1 = mysqli_fetch_assoc($res1)) 
	{
		$tqtdd = 0;
		$tqtdc = 0;

		(!empty($row1["temporeposicao"])) ? $temporeposicao = $row1["temporeposicao"] : $temporeposicao = 0;
		(!empty($row1["estoqueseguranca"])) ? $estoqueseguranca = $row1["estoqueseguranca"] : $estoqueseguranca = 0;
		(!empty($row1["tempocompra"])) ? $tempocompra = $row1["tempocompra"] : $tempocompra = 0;
		(!empty($row1["qtdest"])) ? $qtdest = $row1["qtdest"] : $qtdest = 0;

		($row1["valconv"] ==  0) ? $auxconv = 1 : $auxconv = $row1["valconv"];

		$sql_pe = " SELECT  ifnull(sum(l.qtdpedida),0) as qtdpa
					FROM lote l 
					WHERE l.status IN('AGUARDANDO','FORMALIZACAO','PROCESSANDO','ABERTO','QUARENTENA','TRIAGEM')                                    
					AND l.idprodserv = ".$row1["idprodserv"]." 
					AND l.idprodservformula = ".$row1["idprodservformula"];
		$_respe = d::b()->query($sql_pe) or die("Erro ao consultar produções em andamento do produto:".mysqli_error(d::b()));
		$_rowpa = mysqli_fetch_assoc($_respe);
		if($_inspecionar_sql){
			echo "<pre>".$sql_pe."</pre>";
		}

		$_sqlaux1 = "SELECT c.idlotefracao, c.qtdd, c.qtdc,c.status
					FROM lotefracao lf 
					JOIN lote l ON (lf.idlote = l.idlote)						
					JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
					WHERE lf.idunidade = ".$row1["idunidadeest"]."
					AND l.idprodserv = ".$row1["idprodserv"]." 
					AND l.status not IN ('CANCELADO','CANCELADA')
					AND l.idprodservformula = ".$row1["idprodservformula"]." 
					AND c.criadoem > DATE_SUB(now(), INTERVAL 60 DAY)";
		$_resaux1 = d::b()->query($_sqlaux1) or die("Erro ao consultar histórico de consumo do produto e formula:".mysqli_error(d::b())." sql = ".$_sqlaux1);
		if($_inspecionar_sql){
			echo "<pre>".$_sqlaux1."</pre>";
		}

		while ($_rowaux1 = mysqli_fetch_assoc($_resaux1)) 
		{
			$aqtdd = $_rowaux1["qtdd"];
			$aqtdc = $_rowaux1["qtdc"];
			if ($_rowaux1['status'] !=  'INATIVO' && $_rowaux1['status'] !=  'DEVOLUCAO') 
			{
				$tqtdd +=  $aqtdd;
				$tqtdc +=  $aqtdc;
			}
		}

		$mediadiaria = ($tqtdd) / 60;
		$minimoauto = (($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca));
		$pedidoauto = (($mediadiaria * $tempocompra));
		$pedido_auto = ($temporeposicao * $mediadiaria) + ($minimoauto - $qtdest) - ($_rowpa['qtdpa']);
		$sugestaoCompra2 = (($mediadiaria * $tempocompra) + ($row1['estmin'] - $row1['qtdest'])) - $_rowpa['qtdpa'];

		if ($qtdest > 0 && $mediadiaria > 0) 
		{
			$diasestoque = $qtdest / $mediadiaria;
			echo ("calculo =  ".$qtdest."/".$mediadiaria." = ".$diasestoque."<br>");
		} else {
			$diasestoque = 0;
		}

		if ($pedido_auto < 0) 
		{
			$pedido_auto = 0;
		}

		if ($diasestoque < 0 || !is_numeric($diasestoque)) 
		{
			$diasestoque = 0;
		}

		//@523299 - MÓDULO FILTRO PESQUISA: CALCULO DE ESTOQUE
		//adicionado mediadiaria na prodserv para o modulo filtro calculoestoque	
		$atualzaProdserv1 =  "UPDATE prodservformula 
							SET qtdest = ".$qtdest.", 
								destoque = ".$diasestoque.",
								mediadiaria = ".$mediadiaria.",
								estminautomatico = ".(($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca)).", 
								pedidoautomatico = ".(($mediadiaria * $tempocompra)).", 
								pedido_automatico = ".(($temporeposicao * $mediadiaria) + (((($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca))) - $qtdest) - ($_rowpa['qtdpa'])).", 
								sugestaocompra2 = ".$sugestaoCompra2."
							WHERE idprodservformula = ".$row1["idprodservformula"].";";

		$atualzaProdserv2 =  "UPDATE prodservformula 
							SET destoque = ".$diasestoque.",
								estminautomatico = ".(($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca)).", 
								pedidoautomatico = ".(($mediadiaria * $tempocompra)).", 
								pedido_automatico = ".(($temporeposicao * $mediadiaria) + (((($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca))) - $qtdest) - ($_rowpa['qtdpa']))." 
							WHERE idprodservformula = ".$row1["idprodservformula"].";";		
		if($_inspecionar_sql){
			echo "<pre>".$atualzaProdserv1."</pre>";
			echo "<pre>".$atualzaProdserv2."</pre>";
		}
		d::b()->query($atualzaProdserv1) or die("Erro ao atualizar prodcomprar linha 379. sql: ".$atualzaProdserv1);
		d::b()->query($atualzaProdserv2) or die("Erro ao atualizar prodcomprar linha 389. sql: ".$atualzaProdserv2);

		$sqlprod1 = "SELECT count(*) as nlinhas FROM prodcomprar WHERE idprodserv = ".$row1["idprodserv"]." AND status = 'ATIVO' AND idprodservformula = ".$row1["idprodservformula"];
		$resprod1 = d::b()->query($sqlprod1) or die("Erro ao consultar prodcomprar por formula:".$sqlprod1." - ".$row1["idprodserv"]);
		$rowprod1 = mysql_fetch_assoc($resprod1);

		$sqlp1 = 'SELECT CASE WHEN p.estmin > 0 THEN "Y"  ELSE "N" END as controlaestoq 
				FROM prodservformula p 
				WHERE p.idprodservformula =  '.$row1["idprodservformula"];
		$resp1 = d::b()->query($sqlp1) or die("Erro ao consultar controle de estoque do produto: ".mysqli_error(d::b()));
		$np1 = mysql_num_rows($resp1);

		if ($np1 > 0) 
		{
			$rowp1 = mysql_fetch_assoc($resp1);
			if ($rowp1["controlaestoq"] ==  'Y') 
			{
				$sqll1 = "SELECT IF(pf.estmin >=  ifnull(SUM(f.qtd),0), 'Y', 'N') as controle, COUNT(pc.idprodcomprar) as quantidade
						FROM prodserv p JOIN prodservformula pf ON (pf.idprodserv = p.idprodserv AND pf.estmin IS NOT NULL AND pf.estmin !=  0.00 AND pf.status = 'ATIVO')
						LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.status IN ('APROVADO' , 'QUARENTENA') AND l.idloteorigem is null AND l.idprodservformula = pf.idprodservformula)
						LEFT JOIN lotefracao f ON( f.idlote = l.idlote AND f.idunidade =  pf.idunidadeest AND f.status = 'DISPONIVEL')  
						LEFT JOIN prodcomprar pc ON (pc.idprodserv = p.idprodserv AND pf.idprodservformula = pc.idprodservformula AND pc.status = 'ATIVO')
						WHERE p.idprodserv = ".$row1["idprodserv"]."
						AND pf.idprodservformula = ".$row1["idprodservformula"]."
						GROUP BY p.idprodserv";
				$resl1 = d::b()->query($sqll1) or die("Erro ao consultar controle de estoque do produto: ".mysqli_error(d::b()));

				$rowl1 = mysql_fetch_assoc($resl1);
				$prtaux = "";
				($verify) ? $prtaux = "true" : $prtaux = "false";
				echo "<br>******************************************************************************<br>";
				echo "<pre>".$sqll1."</pre><br>";
				echo "Controle: ".$rowl1["controle"]."<br>";
				echo "Quantidade: ".$rowl1["quantidade"]."<br>";
				echo "Controlaestoq: ".$rowp1["controlaestoq"]."<br>";
				echo "Verify: ".$prtaux."<br>";
				echo "Idempresa: ".$row1["idempresa"]."<br>";
				echo "Idprodserv: ".$row1["idprodserv"]."<br>";
				echo "Idprodservformula: ".$row1["idprodservformula"]."<br>";
				echo "Pedido_Auto: ".$pedido_auto."<br>";
				echo "PedidoAuto: ".$pedidoauto."<br>";
				echo "Dias de Estoque: ".$diasestoque."<br>";
				echo "Nlinhas: ".$rowprod1["nlinhas"]."<br>";
				echo "Média Diária: ".$mediadiaria."<br>";
				echo "Tempo Reposição: ".$temporeposicao."<br>";
				echo "Estoque Segurança: ".$estoqueseguranca."<br>";
				echo "Tempo Compra: ".$tempocompra."<br>";
				echo "Qtdest: ".$qtdest."<br>";
				echo "Mínimo Automático: ".$minimoauto."<br>";
				if ((($rowl1["controle"] ==  'Y' && $rowl1["quantidade"] ==  0) || (empty($rowl1["controle"]) && $rowp1["controlaestoq"] ==  'Y')) || (($pedido_auto > $pedidoauto) && ($rowprod1["nlinhas"] ==  0))) 
				{
					$ins3 =  "INSERT INTO prodcomprar (idempresa,idprodserv,idprodservformula,idlote,criadoem,criadopor,alteradoem,alteradopor) VALUES (".$row1["idempresa"].",".$row1["idprodserv"].",".$row1["idprodservformula"].",0,sysdate(),'sislaudo',sysdate(),'sislaudo');";
					$ins4 =  "INSERT INTO prodcomprar (idempresa,idprodserv,idprodservformula,idlote,criadoem,criadopor,alteradoem,alteradopor) VALUES (".$row1["idempresa"].",".$row1["idprodserv"].",".$row1["idprodservformula"].",0,sysdate(),'sislaudo',sysdate(),'sislaudo');";
					d::b()->query($ins3) or die("Erro ao atualizar prodcomprar linha 451. sql: ".$ins3);
					d::b()->query($ins4) or die("Erro ao atualizar prodcomprar linha 452. sql: ".$ins4);
					if($_inspecionar_sql){
						echo "<pre>".$ins3."</pre>";
						echo "<pre>".$ins4."</pre>";
					}
				} else {
					if (($rowl1["controle"] ==  'N' && $rowl1["quantidade"] > 0) && ($pedido_auto <=  $pedidoauto)) 
					{
						$su3 =  "UPDATE prodcomprar SET status = 'INATIVO' WHERE idprodserv = ".$row1["idprodserv"]." AND idprodservformula = ".$row1["idprodservformula"]." AND status = 'ATIVO';";
						$su4 =  "UPDATE prodcomprar SET status = 'INATIVO' WHERE idprodserv = ".$row1["idprodserv"]." AND idprodservformula = ".$row1["idprodservformula"]." AND status = 'ATIVO';";
						d::b()->query($su3) or die("Erro ao atualizar prodcomprar linha 462. sql: ".$su3);
						d::b()->query($su4) or die("Erro ao atualizar prodcomprar linha 463. sql: ".$su4);
						if($_inspecionar_sql){
							echo "<pre>".$su3."</pre>";
							echo "<pre>".$su4."</pre>";
						}
					}
				}
			}
		}
	}
}
echo "<br><br>--------------------------------------------------------------------------------------------------------------------------------------------<br><br>";
$sqlLog = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
			VALUES ('1', '".$grupo."', 'cron', 'atualizaestoqueminimoautomatico', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'));";
$res0aux = d::b()->query($sqlLog) or die("atualizaestoqueminimoautomatico: ". mysqli_error(d::b())." sql = ".$ins.$su);
echo $sqlLog."<br>";
echo 'call = atualiza finalizado '.date('d/m/Y H:i:s').'<br>';

function atualizaRateio($idprodserv, $idunidadeest, $consumodiaslote, $tempoconsrateio)
{
	$sql = "SELECT * FROM prodservrateio WHERE idprodserv  = ".$idprodserv;
	$res = d::b()->query($sql);
	$qtd = mysqli_num_rows($res);

	if ($qtd < 1) 
	{
		$sqlmeio = "SELECT u.idlotecons,u.idlote,u.partida,u.exercicio,u.qtdd,u.qtdc,u.idunidade,u.unidade,u.criadoem,u.criadopor,u.unpadrao,u.idsolcomitem
						FROM (
						-- fabricar e transferir
						SELECT sm.idlotecons,
								lp.idlote,
								l.partida,
								l.exercicio, 
								round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2) as qtdd, 
								'' as qtdc,
								ufc.idunidade,
								ufc.unidade,
								sm.criadoem,
								sm.criadopor,
								sm.obs,
								si.idsolmat,
								l.unpadrao,
								sc.idsolcomitem
							FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.tipoobjeto = 'lote'  AND cm.status = 'ABERTO')
								JOIN lote lp ON(lp.idlote = cm.idobjeto)
								JOIN lotecons sm ON(sm.idlote = cm.idobjeto AND sm.qtdd>0 AND sm.tipoobjeto  = 'lotefracao'  AND sm.status = 'ABERTO' )
								JOIN lotefracao lfc ON(lfc.idlotefracao = sm.idobjeto)																			
								JOIN unidade ufc ON(ufc.idunidade = lfc.idunidade  AND ufc.cd  = 'N')
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
							WHERE l.idprodserv =   ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."		
								AND c.tipoobjeto = 'lotefracao'
								AND sm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons,sm.idlotecons
						UNION
							-- fabricar e vender
						SELECT sm.idlotecons,
								lp.idlote,
								l.partida,
								l.exercicio,
								round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2) as qtdd,
								'' as qtdc,
								ufc.idunidade,
								ufc.unidade,
								sm.criadoem,
								sm.criadopor,
								'Pedido venda',
								'' as idsolmat,
								l.unpadrao,
								sc.idsolcomitem						
							FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.tipoobjeto = 'lote'  AND cm.status = 'ABERTO')
								JOIN lote lp ON(lp.idlote = cm.idobjeto)
								JOIN lotecons sm ON(sm.idlote = cm.idobjeto AND sm.qtdd>0 AND sm.tipoobjeto  = 'nfitem'  AND sm.status = 'ABERTO' )
								JOIN nfitem ni ON(ni.idnfitem = sm.idobjeto)
								JOIN nf n ON(n.idnf = ni.idnf)
								JOIN unidade ufc ON( ufc.idtipounidade = 21 AND n.idempresa = ufc.idempresa AND ufc.status = 'ATIVO')	
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)					
								WHERE l.idprodserv =    ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.tipoobjeto = 'lotefracao'
								AND sm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons,sm.idlotecons
						UNION 
						-- receber e transferiu como estava
						SELECT cm.idlotecons,
								l.idlote,
								l.partida,
								l.exercicio,
								cm.qtdd,
								'' as qtdc,
								um.idunidade,
								um.unidade,
								cm.criadoem,
								cm.criadopor,
								cm.obs,
								si.idsolmat,
								l.unpadrao,
								sc.idsolcomitem						
						FROM lotefracao lf 
							JOIN lote l ON (lf.idlote = l.idlote)
							JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
							JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
							JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
							JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.tipoobjeto = 'lotefracao'  AND cm.status = 'ABERTO')
							JOIN lotefracao lfc ON(lfc.idlotefracao = cm.idobjeto)																			
							JOIN unidade ufc ON(ufc.idunidade = lfc.idunidade  AND ufc.cd = 'N')
							LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
							LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	    
							WHERE l.idprodserv =   ".$idprodserv."
							AND l.status not IN ('CANCELADO','CANCELADA')
							AND lf.idunidade = ".$idunidadeest."
							AND c.tipoobjeto = 'lotefracao'
							AND cm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
							GROUP BY cm.idlotecons
						UNION
						-- receber e jogar fora
						SELECT 
						cm.idlotecons,l.idlote,l.partida,l.exercicio,cm.qtdd,'' as qtdc,um.idunidade,um.unidade,cm.criadoem,cm.criadopor,cm.obs,'' as idsolmat,l.unpadrao,sc.idsolcomitem
									FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.idobjeto is null AND cm.status = 'ABERTO') 
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	                                                       
								WHERE l.idprodserv =    ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.tipoobjeto = 'lotefracao'
								AND cm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons
						UNION 
						-- fabricar e jogar fora                                                    
						SELECT 
						sm.idlotecons,lp.idlote,l.partida,l.exercicio,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2) as qtdd,'' as qtdc,um.idunidade,um.unidade,sm.criadoem,sm.criadopor,sm.obs,'' as idsolmat,l.unpadrao,sc.idsolcomitem
								FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.tipoobjeto = 'lote'  AND cm.status = 'ABERTO')
								JOIN lote lp ON(lp.idlote = cm.idobjeto)
								JOIN lotecons sm ON(sm.idlote = cm.idobjeto AND sm.qtdd>0 AND sm.idobjeto is null  AND sm.status = 'ABERTO' )
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
								WHERE l.idprodserv =   ".$idprodserv." 
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.tipoobjeto = 'lotefracao'
								AND sm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons,sm.idlotecons
						UNION
						-- fabricado tranferiu devolvendo
						SELECT
							sm.idlotecons,lp.idlote,l.partida,l.exercicio,'' as qtdd,round(((cm.qtdd*sm.qtdc)/lp.qtdprod),2) as qtdc,ufc.idunidade,ufc.unidade,sm.criadoem,sm.criadopor,sm.obs, '' as idsolmat,l.unpadrao,null as idsolcomitem
								FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdd>0 AND cm.tipoobjeto = 'lote'  AND cm.status = 'ABERTO')
								JOIN lote lp ON(lp.idlote = cm.idobjeto)
								JOIN lotecons sm ON(sm.idlote = cm.idobjeto AND sm.qtdc>0 AND sm.tipoobjeto  = 'lotefracao'  AND sm.status = 'ABERTO' )
								JOIN lotefracao lfc ON(lfc.idlotefracao = sm.idobjeto)																			
								JOIN unidade ufc ON(ufc.idunidade = lfc.idunidade  AND ufc.cd  = 'N')				
								WHERE l.idprodserv =    ".$idprodserv." 
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.tipoobjeto = 'lotefracao'
								AND sm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons,sm.idlotecons
						UNION  
						-- devolvido da mesma forma que recebeu   
						SELECT 
						cm.idlotecons,l.idlote,l.partida,l.exercicio,cm.qtdd,cm.qtdc,um.idunidade,um.unidade,cm.criadoem,cm.criadopor,cm.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
								FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd>0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')	
								JOIN lotefracao lm ON(lm.idlotefracao = c.idobjeto)
								JOIN unidade um ON(um.idunidade = lm.idunidade AND um.cd = 'Y')
								JOIN lotecons cm ON(cm.idlotefracao = lm.idlotefracao AND cm.qtdc>0 AND cm.tipoobjeto = 'lotefracao'  AND cm.status = 'ABERTO')
								JOIN lotefracao lfc ON(lfc.idlotefracao = cm.idobjeto)																			
								JOIN unidade ufc ON(ufc.idunidade = lfc.idunidade  AND ufc.cd = 'N')
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
								WHERE l.idprodserv =    ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.tipoobjeto = 'lotefracao'
								AND cm.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
								GROUP BY cm.idlotecons	
							UNION   
							-- daqui para baixo consumos para CD = N
					SELECT c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
						FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'lote' AND c.status = 'ABERTO')
								JOIN lote lp ON(lp.idlote = c.idobjeto)
								JOIN unidade u ON(u.idunidade = lp.idunidade AND u.cd = 'N')	
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)		
						WHERE l.idprodserv = ".$idprodserv."
						AND l.status not IN ('CANCELADO','CANCELADA')
						AND lf.idunidade = ".$idunidadeest."
						AND c.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
						UNION
					SELECT c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
						FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'lotefracao' AND c.status = 'ABERTO')
								JOIN lotefracao lp ON(lp.idlotefracao = c.idobjeto)
								JOIN unidade u ON(u.idunidade = lp.idunidade  AND u.cd = 'N')
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
							WHERE l.idprodserv = ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
					UNION 
					SELECT c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
						FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'nfitem' AND c.status = 'ABERTO')
								JOIN nfitem i  ON(i.idnfitem = c.idobjeto)
								JOIN nf n ON( n.idnf = i.idnf AND n.status  != 'CANCELADO' )
								JOIN unidade u ON( u.idtipounidade = 21 AND n.idempresa = u.idempresa AND u.status = 'ATIVO')	
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')	
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
							WHERE l.idprodserv = ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
					UNION
					SELECT c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
						FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'resultado' AND c.status = 'ABERTO')
								JOIN resultado i  ON(i.idresultado = c.idobjeto)
								JOIN amostra n ON( n.idamostra = i.idamostra  )
								JOIN unidade u ON(  n.idunidade = u.idunidade AND u.cd = 'N' )			
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')	
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
							WHERE l.idprodserv = ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
					UNION        
					SELECT c.idlotecons,l.idlote,l.partida,l.exercicio,c.qtdd,c.qtdc,u.idunidade,u.unidade,c.criadoem,c.criadopor,c.obs,si.idsolmat,l.unpadrao,sc.idsolcomitem
						FROM lotefracao lf 
								JOIN lote l ON (lf.idlote = l.idlote)
								JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd>0 or c.qtdc>0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto is null AND c.idobjeto is null AND c.status = 'ABERTO')
								JOIN unidade u ON(  lf.idunidade = u.idunidade AND u.cd = 'N' )	
								LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
								LEFT JOIN solcomitem sc ON(si.idsolmatitem = sc.idsolmatitem)	
							WHERE l.idprodserv = ".$idprodserv."
								AND l.status not IN ('CANCELADO','CANCELADA')
								AND lf.idunidade = ".$idunidadeest."
								AND c.criadoem between NOW() AND DATE_SUB(NOW(), INTERVAL $tempoconsrateio DAY)
						) as u WHERE u.idsolcomitem is null order by u.partida,u.criadoem,u.unidade";

		$_reslotemeio = d::b()->query($sqlmeio) or die("Erro ao buscar lotes: ".$sqlmeio);
		$numrowmeio = mysqli_num_rows($_reslotemeio);

		if ($numrowmeio > 0) 
		{
			$totalqtddrateio = 0;
			$consumounidade = array();
			$linhar = 0;
			$arrlotesrateio = array();
			while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) 
			{

				if ($rowmi['qtdd'] > 0) {
					$totalqtddrateio = $totalqtddrateio + $rowmi['qtdd'];
					$consumounidade[$rowmi['idunidade']] = $consumounidade[$rowmi['idunidade']] + $rowmi['qtdd'];
				} else {
					$totalqtddrateio = $totalqtddrateio - $rowmi['qtdc'];
					$consumounidade[$rowmi['idunidade']] = $consumounidade[$rowmi['idunidade']] - $rowmi['qtdc'];
				}
			} //while ($rowmi = mysqli_fetch_assoc($_reslotemeio)) {
		} //if ($numrowmeio > 0) {

		if (count($consumounidade) > 0) 
		{

			$ins = '';
			foreach ($consumounidade as $idunidade  => $valor) 
			{
				$idempresa = traduzid("unidade", "idunidade", "idempresa", $idunidade);
				$perc = ($valor / ($totalqtddrateio)) * 100;

				$ins = "INSERT INTO prodservrateio (idempresa,tipo,idprodserv,idunidade,qtd,percentual,criadoem,criadopor,alteradoem,alteradopor) 
							VALUES (".$idempresa.",30,".$idprodserv.",".$idunidade.",'".$valor."','".$perc."',sysdate(),'sislaudo',sysdate(),'sislaudo');";

				$resulta = d::b()->query($ins) or die("Erro ao inserir assinatura contapagar. sql: ".$ins);
			}

			//$res0aux = d::b()->multi_query($ins) or die("atualizaestoqueminimoautomatico - prodservRateio: ".  mysqli_error(d::b())." sql = ".$ins);
		}
	}
}//atualizaRateio
