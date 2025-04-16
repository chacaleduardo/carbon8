<?
require_once("../inc/php/functions.php");
$idpessoa=$_POST['idpessoa']; 

if(empty($idpessoa)){
	die("IDCONTAITEM NAO ENVIADO");
}

?>

<?    
	$res = d::b()->query("select c.idcontaitem,c.contaitem
				from pessoacontaitem i,contaitem c
				where c.idcontaitem = i.idcontaitem
				and c.status='ATIVO'
				and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and i.idpessoa=".$idpessoa." order by c.contaitem") or die("Erro ao retornar contaitem: ".mysql_error());

	while($r = mysqli_fetch_array($res)) {

		echo "<option value='".$r["idcontaitem"]."'>".$r["contaitem"]."</option>";        
   
	}  




/*
$idcontaitem=$_POST['idcontaitem']; 

if(empty($idcontaitem)){
	die("IDCONTAITEM NAO ENVIADO");
}

?>

<?    
	$res = d::b()->query("SELECT c.idcontadesc,c.contadesc
			FROM contadescitem i,contadesc c
			WHERE c.status = 'ATIVO'
			and c.idcontadesc= i.idcontadesc
			 and i.idcontaitem =".$idcontaitem." order by contadesc") or die("Erro ao retornar desc: ".mysql_error());

	while($r = mysqli_fetch_array($res)) {

		echo "<option value='".$r["idcontadesc"]."'>".$r["contadesc"]."</option>";        
   
	}    
 */        
?>

