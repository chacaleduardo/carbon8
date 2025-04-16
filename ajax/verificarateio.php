<?
require_once("../inc/php/functions.php");

$_idnf= $_GET['idnf']; 



if(empty($_idnf) ){
	die("Nota não informada");
}

$sqlr="select i.idnfitem,ifnull(p.descr,i.prodservdescr) as descr,i.total as rateio,ri.idrateioitem,ifnull(rd.valor,100) as valorateio,rd.*
from nfitem i 
    left join prodserv p on(p.idprodserv=i.idprodserv)
    left join rateioitem ri on(ri.idobjeto = i.idnfitem and ri.tipoobjeto = 'nfitem' )
    left join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
    join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')
where  i.idpessoa is null and i.idnf=".$_idnf;

$resr= d::b()->query($sqlr) or die("Erro ao buscar rateio pendente :".mysqli_error(d::b())."<br>Sql:".$sqlr); 
$qtdrateio=mysqli_num_rows($resr);
$rowr=mysqli_fetch_assoc($resr);  
if(empty($rowr['idrateioitemdest']) and !empty($rowr['idnfitem'])){
    $rateio='PENDENTE';
    $color="red";
}else{
    $rateio='CONCLUIDO';
    $color="green";
}  

$str = "<a  style='color:".$color."' class='pointer'>".$rateio."</a>";
echo($str);
?>

 