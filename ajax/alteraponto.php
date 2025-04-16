<?
require_once("../inc/php/functions.php");

$idrhevento = $_POST["idrhevento"];
$status = $_POST["status"];

if(empty($idrhevento) or empty($status)){
	die("Erro enviado valor vazio para o update no ponto");
}else{
    
    if($status=='A'){
	$sql = "update rhevento set status = 'ATIVO'
                ,alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."'
                ,alteradoem = now()
                where idrhevento=".$idrhevento;
		
        $res =  d::b()->query($sql) or die("Error2 ao alterar ponto para ATIVO".$sql);
		//die($sql);
        
    }else{
        $sql = "update rhevento set entsaida = '".$status."'
                ,alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."'
                ,alteradoem = now()
                where idrhevento=".$idrhevento;
		
        $res =  d::b()->query($sql) or die("Error2 ao alterar ponto".$sql);
    }
    echo "ok";	

}


?>