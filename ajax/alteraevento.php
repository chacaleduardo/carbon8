<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");
require_once("../model/evento.php");

$idevento	= $_GET["idevento"];
$ideventotipo	= $_GET["ideventotipo"];

$eventoclass = new EVENTO();

if (empty($idevento) or empty($ideventotipo)){
	die("evento vazio");
}

$sql = "SELECT mf.idfluxostatus as tokeninicial,
			   e.idempresa,			   
			   s.statustipo
		  FROM evento e JOIN fluxo ms ON ms.idobjeto = ".$ideventotipo." AND ms.tipoobjeto = 'ideventotipo' AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
		  JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo 
		  JOIN "._DBCARBON."._status s on s.idstatus = mf.idstatus AND s.statustipo = 'INICIO'
		 WHERE e.idevento = '".$idevento."'";

$res = d::b()->query($sql);
$r = mysqli_fetch_assoc($res);
$tokeninicial = $r['tokeninicial'];
$idempresa = $r['idempresa'];
$statustipo = $r['statustipo'];


	$sql="UPDATE evento e JOIN fluxo ms ON ".$ideventotipo." = ms.idobjeto AND ms.tipoobjeto = 'ideventotipo' AND ms.modulo = 'evento'
			JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo
			JOIN "._DBCARBON."._status s on 	s.idstatus = mf.idstatus AND s.statustipo = 'INICIO'
			JOIN fluxostatuspessoa r ON r.idmodulo = e.idevento AND r.modulo = 'evento'		
			SET e.ideventotipo = ".$ideventotipo.", e.idfluxostatus = mf.idfluxostatus, r.idfluxostatus =  mf.idfluxostatus, oculto = 0  
		WHERE e.idevento = '".$idevento."'";
$res = d::b()->query($sql) or die("ERRO atualizar evento: ".mysqli_error(d::b())."\n SQL: ".$sql);

if(!$res){			
	die("1-Erro ao atualizar evento: " . mysql_exrror() . "<p>SQL: ".$sql);
}

$sql="DELETE FROM fluxostatuspessoa WHERE idobjeto in (10238) AND idmodulo = ".$idevento." AND modulo = 'evento'";
$res = d::b()->query($sql) or die("ERRO apagar idobjeto evento: ".mysql_error(d::b())."\n SQL: ".$sql);

$sql="DELETE FROM fluxostatuspessoa WHERE idobjetoext in (10238)  AND idmodulo = ".$idevento." AND modulo = 'evento'";
$res = d::b()->query($sql) or die("ERRO apagar idimgrupo evento: ".mysql_error(d::b())."\n SQL: ".$sql);


if ($statustipo != 'CANCELADO' OR $statustipo != 'FIM' OR $statustipo != 'CONCLUIDO'){
	$eventoclass->insereParticipantes($idevento, $ideventotipo, $tokeninicial, $_SESSION["SESSAO"]["IDPESSOA"]);
	$eventoclass->atualizaparticipantes($idevento, $tokeninicial);
}
?>	