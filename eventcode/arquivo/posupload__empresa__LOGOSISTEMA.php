<?
//echo $arq_final."\n";

$caminhoaux = "../upload/imagenssistema/".$arq_nome;
$verifica = copy($arq_final,$caminhoaux);
//echo $caminhoaux."\n";
if($verifica){
	$sd1 = "SELECT idempresaimagem
	FROM empresaimagem 
	WHERE tipoimagem='LOGOSISTEMA' 
		AND idempresa = ".$_idobjeto;

	$qd1 = d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #1: ".mysqli_error(d::b()));

	while($r1= mysqli_fetch_assoc($qd1)){
		unlink($r1["caminho"]);
		$sd1="DELETE FROM empresaimagem WHERE idempresaimagem=".$r1["idempresaimagem"]." LIMIT 1";
		d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #2: ".mysqli_error(d::b()));
	}
	
	$_sql = "UPDATE arquivo SET caminho = '".$caminhoaux."' WHERE tipoarquivo = 'LOGOSISTEMA' and tipoobjeto = 'empresa' and idobjeto = ".$_idobjeto;
	$_res = d::b()->query($_sql) or die("Erro ao atualizar caminho da tabela arquivo. SQL: ".$_sql." ".mysqli_error(d::b()));
	$_sql1 = "UPDATE empresa SET logosis = '".$caminhoaux."' WHERE idempresa = ".$_idobjeto;
	$_res1 = d::b()->query($_sql1) or die("Erro ao atualizar caminho da tabela empresa. SQL: ".$_sql1." ".mysqli_error(d::b()));
	
	$_sql2 = "INSERT INTO empresaimagem (idempresa,tipoimagem,caminho,criadopor,criadoem,alteradopor,alteradoem) VALUES (".$_idobjeto.",'LOGOSISTEMA','".$caminhoaux."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
	$_res2 = d::b()->query($_sql2) or die("Erro ao inserir caminho da tabela empresaimagem. SQL: ".$_sql2." ".mysqli_error(d::b()));
}else{
	die("Erro ao copiar o arquivo ".$arq_nome." para o caminho ".$caminhoaux);
}
?>