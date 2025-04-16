<?


$_idcontato = $_SESSION['arrpostbuffer']['1']['i']['atendimento']['idcontato'];



//se for um insert, o tipo de meio tiver sido informado e o lote estiver vazio
if((!empty($_idcontato))){

	$email=$_SESSION['arrpostbuffer']['1']['i']['atendimento']['email'];
	$ddd1=$_SESSION['arrpostbuffer']['1']['i']['atendimento']['ddd1'];
	$telefone1=$_SESSION['arrpostbuffer']['1']['i']['atendimento']['telefone1'];
	$ddd2=$_SESSION['arrpostbuffer']['1']['i']['atendimento']['ddd2'];
	$telefone2=$_SESSION['arrpostbuffer']['1']['i']['atendimento']['telefone2'];
	
	
	  $sql = "update pessoa set email='".$email."',dddfixo ='".$ddd1."',telfixo ='".$telefone1."',dddcel ='".$ddd2."',telcel='".$telefone2."'            
            where idpessoa=".$_idcontato;

    $res = d::b()->query($sql) or die("Erro ao atulizar contato :".mysqli_error(d::b())."<br>Sql:".$sql); 
	
	
	//print_r($_SESSION["arrpostbuffer"]); die;

}

?>

