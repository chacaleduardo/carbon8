<?
require_once("../inc/php/functions.php");

if(empty($_GET["idpessoa"])){
	die("Id do Cliente não enviado via GET");
}

$idpessoa = $_GET["idpessoa"];

if($_GET["tipo"] == 'responsavel') 
{
	//Busca os responsáveis que possuem o mesmo  CRM do estado correspondente ao do cliente cadastrado no endereço de faturamento deste.
	$sqlc = "SELECT DISTINCT * FROM (SELECT pr.idpessoa AS id,
											pr.idpessoa AS idcontato,
											pr.nome,
											CONCAT(pr.dddfixo, '-', pr.telfixo) AS tel1,
											CONCAT(pr.dddcel, '-', pr.telcel) AS tel2,
											pr.email
									   FROM pessoa p JOIN pessoacontato c ON (p.idpessoa = c.idcontato)
									   JOIN pessoacontato cr ON (p.idpessoa = cr.idpessoa)
									   JOIN pessoa pr ON (cr.idcontato = pr.idpessoa AND pr.status = 'ATIVO' AND pr.idtipopessoa = 15)
									   JOIN endereco e ON e.idpessoa = pr.idpessoa AND e.idtipoendereco = 2
									   JOIN pessoacrmv pc ON pc.idpessoa = pr.idpessoa AND pc.uf = e.uf
									  WHERE p.status = 'ATIVO'
									    AND p.idtipopessoa IN (12)
										AND c.idpessoa = $idpessoa 
									UNION 
									 SELECT c.idpessoa AS id,
											c.idcontato,
											nome,
											CONCAT(dddfixo, '-', telfixo) AS tel1,
											CONCAT(dddcel, '-', telcel) AS tel2,
											email
									   FROM pessoa p JOIN pessoacontato c ON p.idpessoa = c.idcontato 
									   JOIN endereco e ON e.idpessoa = c.idpessoa  AND e.idtipoendereco = 2
									   JOIN pessoacrmv pc ON pc.idpessoa = c.idcontato AND pc.uf = e.uf
									  WHERE p.status = 'ATIVO' 
									    AND p.idtipopessoa NOT IN (12, 1)
									    AND c.idpessoa = $idpessoa 
									UNION 
									 SELECT c.idpessoa AS id,
											c.idcontato,
											nome,
											CONCAT(dddfixo, '-', telfixo) AS tel1,
											CONCAT(dddcel, '-', telcel) AS tel2,
											email
									   FROM pessoa p JOIN pessoacontato c ON p.idpessoa = c.idcontato
									   JOIN endereco e ON e.idpessoa = c.idpessoa AND e.idtipoendereco = 2
									   JOIN pessoacrmv pc ON pc.idpessoa = p.idpessoa AND pc.uf = e.uf
									  WHERE p.status = 'ATIVO'
									    AND p.idtipopessoa = 1
										AND p.flagobrigatoriocontato = 'Y'
										AND c.idpessoa = $idpessoa 
									UNION 
									 SELECT p.idpessoa AS id,
											p.idpessoa AS idcontato,
											p.nome,
											CONCAT(p.dddfixo, '-', p.telfixo) AS tel1,
											CONCAT(p.dddcel, '-', p.telcel) AS tel2,
											p.email
									   FROM plantelobjeto po JOIN divisaoplantel dp ON (dp.idplantel = po.idplantel)
									   JOIN pessoa p2 ON p2.idpessoa = po.idobjeto AND po.tipoobjeto = 'pessoa'
									   JOIN divisao d ON (d.iddivisao = dp.iddivisao)
									   JOIN pessoa p ON (p.idpessoa = d.idpessoa)
									   JOIN endereco e ON e.idpessoa = p2.idpessoa AND e.idtipoendereco = 2 
									   JOIN pessoacrmv pc ON pc.idpessoa = p2.idpessoa AND pc.uf = e.uf
									  WHERE po.tipoobjeto = 'pessoa'
									    AND po.idobjeto = $idpessoa 
								 	UNION 
									 SELECT p1.idpessoa AS id,
											p1.idpessoa AS idcontato,
											p1.nome,
											CONCAT(p1.dddfixo, '-', p1.telfixo) AS tel1,
											CONCAT(p1.dddcel, '-', p1.telcel) AS tel2,
											p1.email
									   FROM pessoa p JOIN endereco e ON (p.idpessoa = e.idpessoa)
									   JOIN pessoacrmv pc ON (pc.uf = e.uf)
									   JOIN pessoa p1 ON (p1.idpessoa = pc.idpessoa)
									  WHERE p1.idtipopessoa IN (15 , 16, 1)
									    AND p.status = 'ATIVO'
										AND p1.status = 'ATIVO'
										AND p.idpessoa = $idpessoa
								   GROUP BY pc.idpessoa) a
					ORDER BY nome;";
	$res = d::b()->query($sqlc) or die("Erro ao buscar Clientes sql=".$sql);
	$qtdrows = mysqli_num_rows($res);

	//Caso não tenha nenhum, executará a busca anterior
	if($qtdrows == 0){
		$sqlc = "SELECT DISTINCT * FROM (SELECT pr.idpessoa AS id,
												pr.idpessoa AS idcontato,
												pr.nome,
												CONCAT(pr.dddfixo, '-', pr.telfixo) AS tel1,
												CONCAT(pr.dddcel, '-', pr.telcel) AS tel2,
												pr.email
										  FROM pessoa p JOIN pessoacontato c ON (p.idpessoa = c.idcontato)
										  JOIN pessoacontato cr ON (p.idpessoa = cr.idpessoa)
										  JOIN pessoa pr ON (cr.idcontato = pr.idpessoa	AND pr.status = 'ATIVO' AND pr.idtipopessoa = 15)
										 WHERE p.status = 'ATIVO' 
										  AND p.idtipopessoa IN (12)
										  AND c.idpessoa = $idpessoa
									UNION 
										 SELECT	c.idpessoa AS id,
												c.idcontato,
												nome,
												CONCAT(dddfixo, '-', telfixo) AS tel1,
												CONCAT(dddcel, '-', telcel) AS tel2,
												email 
										   FROM	pessoa p JOIN pessoacontato c ON p.idpessoa = c.idcontato
										  WHERE p.status = 'ATIVO'
										    AND p.idtipopessoa NOT IN (12 , 1)
											AND c.idpessoa = $idpessoa
									UNION 
										 SELECT c.idpessoa AS id,
												c.idcontato,
												nome,
												CONCAT(dddfixo, '-', telfixo) AS tel1,
												CONCAT(dddcel, '-', telcel) AS tel2,
												email
										   FROM pessoa p JOIN pessoacontato c ON p.idpessoa = c.idcontato
										  WHERE	p.status = 'ATIVO'
										    AND p.idtipopessoa = 1
											AND p.flagobrigatoriocontato = 'Y'
											AND c.idpessoa = $idpessoa 
									UNION 
										 SELECT p.idpessoa AS id,
												p.idpessoa AS idcontato,
												p.nome,
												CONCAT(dddfixo, '-', telfixo) AS tel1,
												CONCAT(dddcel, '-', telcel) AS tel2,
												email
										   FROM	plantelobjeto po JOIN divisaoplantel dp ON (dp.idplantel = po.idplantel)
										   JOIN divisao d ON (d.iddivisao = dp.iddivisao)
										   JOIN pessoa p ON (p.idpessoa = d.idpessoa)
										  WHERE	po.tipoobjeto = 'pessoa' AND po.idobjeto = $idpessoa
									UNION 
										 SELECT p1.idpessoa AS id,
												p1.idpessoa AS idcontato,
												p1.nome,
												CONCAT(p1.dddfixo, '-', p1.telfixo) AS tel1,
												CONCAT(p1.dddcel, '-', p1.telcel) AS tel2,
												p1.email
										   FROM	pessoa p JOIN endereco e ON (p.idpessoa = e.idpessoa)
										   JOIN pessoacrmv pc ON (pc.uf = e.uf)
										   JOIN pessoa p1 ON (p1.idpessoa = pc.idpessoa)
										  WHERE	p1.idtipopessoa IN (15 , 16, 1)
										    AND p.status = 'ATIVO'
											AND p1.status = 'ATIVO'
											AND p.idpessoa = $idpessoa) a
							ORDER BY nome;";

		$res = d::b()->query($sqlc) or die("Erro ao recuperar Responsável: ".mysqli_error(d::b()));
	}

	$arrTmp = array();

	$arrColunas = mysqli_fetch_fields($res);

	while($r = mysqli_fetch_assoc($res)) {
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			$arrTmp[$r["nome"]][$col->name] = $r[$col->name];
		}
	}

} elseif($_GET["tipo"] == 'crmv'){

	$vIdPessoaCliente = $_GET["vIdPessoaCliente"];
	$sqlc = "SELECT pc.idpessoacrmv AS id,
					pc.crmv,
					pc.uf
				FROM pessoa p JOIN endereco e ON (p.idpessoa = e.idpessoa)
				JOIN pessoacrmv pc ON pc.uf = e.uf
			   WHERE pc.status = 'ATIVO'
				 AND pc.idpessoa = $idpessoa
				 AND p.idpessoa = '$vIdPessoaCliente'";
	$res = d::b()->query($sqlc) or die("Erro ao recuperar Responsável: ".mysqli_error(d::b()));
	$qtdrows = mysqli_num_rows($res);

	//Caso não tenha nenhum, executará a busca anterior
	if($qtdrows == 0){
		$sqlc = "SELECT idpessoacrmv AS id,
						crmv,
						uf
					FROM pessoacrmv
				   WHERE status = 'ATIVO'
					 AND idpessoa = ".$idpessoa;
		$res = d::b()->query($sqlc) or die("Erro ao recuperar Responsável: ".mysqli_error(d::b()));
	}

	$arrTmp = array();

	$arrColunas = mysqli_fetch_fields($res);

	while($r = mysqli_fetch_assoc($res)) {
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			$arrTmp[$r["crmv"]][$col->name] = $r[$col->name];
		}
	}
}


echo json_encode($arrTmp);

?>