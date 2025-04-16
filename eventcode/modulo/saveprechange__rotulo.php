<?
//print_r($_POST['idpedidocp']); DIE;

$idrotulo=$_SESSION['arrpostbuffer']['1']['u']['rotulo']['idrotulo'];

if(!empty($_POST['idpedidocp']) and !empty($idrotulo)){
    $idpedido=$_POST['idpedidocp'];
    
    $sql=" select idpessoa,idcontato,idendereco,idempresa,idpedido from pedido where idpedido=".$idpedido;
    $resi= d::b()->query($sql) or die("Erro ao recuperar pedido: \n".mysqli_error(d::b())."\n".$sql);
    $row=mysqli_fetch_assoc($resi);
    $i=9999;
   


                // montar o item para insert
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idrotulo']=$idrotulo;
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idpessoa']=$row['idpessoa'];
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idcontato']=$row['idcontato'];
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idendereco']=$row['idendereco'];
                $_SESSION['arrpostbuffer'][$i]['i']['rotuloresultado']['idpedido']=$row['idpedido'];
              
              
} 

/*
 
$idpedido 	= $_GET["idpedido"];
$idrotulo= $_GET["idrotulo"];
if(empty($idpedido) or empty($idrotulo)){
	die("Erro, não foram enviados os parâmetros necessários criação do rotulo.");
	

}

		$sqli="INSERT INTO `rotuloresultado`
			(
			`idrotulo`,
			`idpessoa`,
			`idcontato`,
			`idendereco`,
			`idempresa`,
			`idpedido`
			)
			(select ".$idrotulo.",idpessoa,idcontato,idendereco,idempresa,idpedido from pedido where idpedido=".$idpedido.")";;
		$resi= mysql_query($sqli);
		if(!$resi){
			
			die("1-Falha ao gerar rotulo: " . mysql_error() . "<p>SQL: ".$sqli);
		}

		