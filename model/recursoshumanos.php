<?
require_once("../inc/php/functions.php");

class RH 
{
	//Retorna a Data (2020-02-13 - Thursday - Y) enquanto for menor que a data final do Intervalo 
	function getIntervalDate ($data1, $i, $data2)
	{
		$s="SELECT DATE_ADD('".$data1."', INTERVAL ".$i." DAY) AS diabusca,
					    DATE_FORMAT( DATE_ADD('".$data1."', INTERVAL ".$i." DAY),'%W') AS semana,
			  CASE WHEN DATE_ADD('".$data1."', INTERVAL ".$i." DAY) > '".$data2."' THEN 'Y' ELSE 'N' END  AS maior";
		$re = d::b()->query($s) or die("Erro ao buscar os pontos pendentes SQL = ".$s);
		$rw = mysqli_fetch_assoc($re);
		return $rw;
	}
	
	//Retorna as Pessoas que fazem parte do Setor, filtrando se quiser pelo Status e Situaçao do Ponto
	function getPessoasPonto($strjoin, $strin, $groupBy = NULL)
	{
		$s1="SELECT idpessoa, nome 
			  FROM vw_ponto
			 WHERE status='ATIVO'
			   ".$strin."
			   ".getidempresa('idempresa','pessoa')."
		  GROUP BY idpessoa, nome
		  ORDER BY nome";
		$re1 = d::b()->query($s1) or die("Erro ao buscar os funcionarios dos pontos SQL = ".$s1);
		return $re1;
	}
	
	//Retorna o Status que na busca vem com inicial e na busca do Banco aparece o nome completo
	function getStatus($status)
	{
		switch ($status) {
			case 'A':
				$status = 'ATIVO';
			break;
			case 'P':
				$status = 'PRNDENTE';
			break;
		}
		return $status;
	}
	
	//Retorna os Dados do Ponto
	function getDadosPonto($strjoin, $strin, $data1, $data2)
	{
		$data2 = $data2.' 23:59:59';
		$s1="SELECT p.idpessoa,nome,dataponto,idrhevento,idrhtipoevento,hora,semana,statusevento,entsaida,obs
				FROM vw_ponto p ".$strjoin."
				WHERE data between '".$data1."' AND '".$data2."'
				 AND p.statusevento!='INATIVO'
				 ".getidempresa('p.idempresa','pessoa')."
				".$strin."
				ORDER BY nome,hora";
		 echo "<!-- ".$s1." -->";	//echo $_sqlresultado;
		 $re1= d::b()->query($s1) or die("erro ao buscar os pontos  sql=".$s1);

		while($r=mysqli_fetch_assoc($re1))
		{
		   // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhevento']=$r['idrhevento'];  
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['semana']=$r['semana'];
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['hora']=$r['hora'];
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['statusevento']=$r['statusevento'];
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['entsaida']=$r['entsaida'];  
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['obs']=$r['obs'];
			$arrayp[$r['idpessoa']][$r['nome']][$r['dataponto']][$r['idrhevento']]['idrhtipoevento']=$r['idrhtipoevento'];
		}   
		return $arrayp;
	}
	
	function getBonusHorasValor()
	{
		$sqe="SELECT t.tipo,e.idrhevento,t.evento,t.eventocurto,e.valor 
				FROM rhtipoevento t,rhevento e
			   WHERE t.formato='H' AND t.flgponto = 'Y'
				 AND t.flhtotais = 'N' AND t.flhtotaisajust  = 'N' AND t.flhext  = 'N' AND t.flhextcalc  = 'N'
				 AND e.idrhtipoevento=t.idrhtipoevento
				 AND e.status!='INATIVO'
				 AND e.valor is not null
				 AND e.idpessoa = ".$idpessoa." 
				 AND e.dataevento = '".$data."'";
	}
	
	//Retorna as o Setor da Pessoa
	function getPessoaSetor($idpessoa)
	{ 
		$s1="SELECT CASE
						WHEN setor IS NOT NULL THEN setor
						WHEN area IS NOT NULL THEN area
						WHEN departamento IS NOT NULL THEN departamento
					END as setor,
					p.nome
			  FROM pessoa p INNER JOIN pessoaobjeto po ON p.idpessoa = po.idpessoa
			  LEFT JOIN sgsetor s ON po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor'
			  LEFT JOIN sgarea a ON po.idobjeto = a.idsgarea AND po.tipoobjeto = 'sgarea'
			  LEFT JOIN sgdepartamento d ON po.idobjeto = d.idsgdepartamento AND po.tipoobjeto = 'sgdepartamento'
			 WHERE p.idpessoa = $idpessoa";
		$re1 = d::b()->query($s1) or die("Erro ao buscar os funcionarios dos pontos SQL = ".$s1);
		$rw = mysqli_fetch_assoc($re1);
		return $rw;
	}
	
	//Faz a soma da quantidade de Horas Extras do Funcionário, retornando o valor em Horas - LTM (19/06/2020)
	function getHorasExtras($idrhtipoevento, $idpessoa, $dataPonto)
	{
		$sql = "SELECT SUM(e.valor) AS valor
                FROM rhevento e LEFT JOIN rhtipoevento t ON(t.idrhtipoevento = e.idrhtipoevento)
			   WHERE e.idrhtipoevento = ".$idrhtipoevento."
				".getidempresa('e.idempresa','rhevento')."
				 AND e.idpessoa = ".$idpessoa."
				 ".$dataPonto."
				-- AND e.status='PENDENTE'
              ORDER BY e.dataevento,e.hora";
		$res = d::b()->query($sql) or die("Erro ao buscar Horas Extras: ".mysqli_error(d::b()));
		$rw = mysqli_fetch_assoc($res);
		return $rw;
	}
}

?>