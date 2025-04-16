<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:atualizastatusemail',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'atualizastatusemail', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

$sql = "SELECT status,destinatario,queueid,date_format(criadoem,'%Y-%m-%d %H:%i:%s') as criadoem 
        FROM mailfila 
        WHERE status in ('EM FILA','ADIADO') and remover ='N' and criadoem > DATE_SUB(now(), INTERVAL 30 DAY)";

$res=d::b()->query($sql) or die("erro ao buscar emails em fila: ".mysqli_error(d::b())."\n".$sql);

$sSqlUpdateEmail="";

while($row=mysqli_fetch_assoc($res)){
    $row["queueid"] = trim($row["queueid"]);
	$row["destinatario"] = trim($row["destinatario"]);
    $sql2= "SELECT queueid,status 
            FROM mailfilalog 
            WHERE queueid = '".$row["queueid"]."'
            and datetime >= '".$row["criadoem"]."' 
            and destinatario = '".$row["destinatario"]."' 
            ORDER BY datetime DESC limit 1";
    

	if($_GET['inspecionar'] == 'Y'){
		echo $sql2;
	}else{
		$res2=d::b()->query($sql2) or die("erro ao buscar status dos emails em fila: ".mysqli_error(d::b())."\n".$sql2);
		$row2=mysqli_fetch_assoc($res2);
		$row2["queueid"] = trim($row2["queueid"]);
		switch($row2["status"]){
			case 'sent':
				$sSqlUpdateEmail .= " UPDATE mailfila set status = 'ENVIADO' where queueid = '".$row2["queueid"]."';";
				echo "<br><span style='color:green'>".$row2["queueid"]." ENVIADO</span><br>";
				break;
			case 'bounced':
				$sSqlUpdateEmail .= " UPDATE mailfila set status = 'NAO ENVIADO' where queueid = '".$row2["queueid"]."';";
				echo "<br><span style='color:red'>".$row2["queueid"]." NÃO ENVIADO</span><br>";
				break;
			case 'deferred':
				$sSqlUpdateEmail .= " UPDATE mailfila set status = 'ADIADO' where queueid = '".$row2["queueid"]."';";
				echo "<br><span style='color:yellow'>".$row2["queueid"]." ADIADO</span><br>";
				break;
			default:
				break;
		}
	}
}

$sql1 = "SELECT status,destinatario,queueid,idmailfila
        FROM mailfila 
        WHERE status in ('EM FILA','ADIADO') and remover ='N' and criadoem < DATE_SUB(now(), INTERVAL 30 DAY)";

$res1=d::b()->query($sql1) or die("erro ao buscar emails em fila: ".mysqli_error(d::b())."\n".$sql1);


while($row1=mysqli_fetch_assoc($res1)){
    $sSqlUpdateEmail .= " UPDATE mailfila set status = 'ENVIADO' where idmailfila = '".$row1["idmailfila"]."';";
}

if(!empty($sSqlUpdateEmail)){
	$_res = d::b()->multi_query($sSqlUpdateEmail) or die("atualizastatusemail: ".  mysqli_error(d::b()));
}

re::dis()->hMSet('cron:atualizastatusemail',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'atualizastatusemail', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
?>