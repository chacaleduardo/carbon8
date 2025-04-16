<?
require_once("../inc/php/functions.php");

$cpfcnpj = $_GET["cpfcnjpj"];

if(!empty($cpfcnpj)){
	$cpfcnpj = trim($cpfcnpj);
	$sql = "SELECT 
				count(*) as quant
			FROM 
				pessoa
			WHERE 
				TRIM(cpfcnpj) = '".$cpfcnpj."'
            		and
				idtipopessoa <> 1
					and
				status!='INATIVO'".getidempresa('idempresa','pessoa');

	$res = d::b()->query($sql) or die("Falha ao pesquisar exitencia de cpf/cnpj : " . mysqli_error(d::b()) . "<p>SQL: $sql");

	$row = mysqli_fetch_array($res);
	
	if($row["quant"]>=1){
		echo 1;
	}elseif($row["quant"]==0){
		echo 0;
	}else{
		echo -1;
	}
}else{
	die("Erro: Parâmetro CPF/CNPJ não enviado corretamente!");
}

?>