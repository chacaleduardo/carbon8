<?
require_once("../inc/php/functions.php");

$nnfe=trim($_GET['nnfe']);
$idpessoa=$_GET['idpessoa'];
$idnf=$_GET['idnf'];

if(!empty($idpessoa) and !empty($nnfe)){
	if(!empty($idnf)){
		$strin=" and idnf!=".$idnf." ";
	}else{
		$strin="";
	}
	
	// GVT - 17/02/2020 - Adicionada função para retirada dos zeros a esquerda das nnfe
	//PHOL - 10/08/2021 - Removida função para retirada dos zeros a esquerdas nnfe
	//$nnfe = ltrim($nnfe, '0');
	
	//PHOL - 10/08/2021 - alteração no select para comparar cpf/cnpj juntamente com o idpessoa
	/*
	$sql="select (
        (select count(*) from pessoa p join pessoa pcpf ON (pcpf.cpfcnpj = p.cpfcnpj and pcpf.idpessoa != p.idpessoa) join nf n on(n.idpessoa = pcpf.idpessoa) where n.status!='CANCELADO' and p.idpessoa=".$idpessoa." and n.nnfe= '".$nnfe."' ".$strin.")
        +
        (select count(*) from pessoa p join pessoa pcpf ON (pcpf.cpfcnpj = p.cpfcnpj and pcpf.idpessoa = p.idpessoa) join nf n on(n.idpessoa = pcpf.idpessoa) where n.status!='CANCELADO' and p.idpessoa=".$idpessoa." and n.nnfe= '".$nnfe."' ".$strin.")) as quant";
	*/
		$sql="select n.idnf from pessoa p join pessoa pcpf ON (pcpf.cpfcnpj = p.cpfcnpj and pcpf.idpessoa != p.idpessoa) join nf n on(n.idpessoa = pcpf.idpessoa) 
			where n.status!='CANCELADO' and p.idpessoa=".$idpessoa." and n.nnfe= '".$nnfe."' ".$strin."
			union
			select n.idnf  from pessoa p join pessoa pcpf ON (pcpf.cpfcnpj = p.cpfcnpj and pcpf.idpessoa = p.idpessoa) 
			join nf n on(n.idpessoa = pcpf.idpessoa) where n.status!='CANCELADO' and p.idpessoa=".$idpessoa." and n.nnfe= '".$nnfe."' ".$strin." ";
	$res = d::b()->query($sql) or die("Falha ao pesquisar exitencia de nnfe para o fornecedor : " . mysqli_error(d::b()) . "<p>SQL: $sql");

	$qtd =mysqli_num_rows($res);
	
	
	if($qtd>=1){
		$row = mysqli_fetch_assoc($res);
		die("NNFE já existente ID:".$row['idnf']);
	}elseif($qtd==0){
		die("OK");
	}else{
		die("ERRO");
	}
}else{
	die("Erro: Parametro NNFe ou idpessoa nao enviado corretamente!");
}

?>