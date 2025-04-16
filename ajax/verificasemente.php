<?
require_once("../inc/php/validaacesso.php");

 $idlote = $_GET['idlote'];
 $status = $_GET['status'];

 $sql = "SELECT si.idsolfabitem, s.status, s.idsolfab, s.idlote, s.idpessoa,l.idlote AS idloteL
            FROM solfabitem si
                JOIN solfab s on s.idsolfab = si.idsolfab
                JOIN lote l ON l.idlote = s.idlote and l.status not in ('APROVADO','QUARENTENA','LIBERADO','CANCELADO','REPROVADO')
            WHERE  s.status not in('CANCELADO','REPROVADO') AND si.idobjeto = $idlote ;";

    $res = d::b()->query($sql) or die("Erro ao verificar semente : Erro: ".mysqli_error(d::b())."\n".$sql);
    $arrRetorno = array();
    $qrow = mysqli_num_rows($res);
    $i = 0;
    while($row = mysqli_fetch_assoc($res)){
        $arrRetorno[$i][$row["idsolfab"]] = $row["idsolfab"];
        $arrRetorno[$i][$row["idsolfab"]] = $row["status"];
        $i++;
    }
  
  echo  json_encode($arrRetorno);

	//if($qrow>=1){
	//	die("Y");
	//}else{
	//	die("N");
	//}
 
?>