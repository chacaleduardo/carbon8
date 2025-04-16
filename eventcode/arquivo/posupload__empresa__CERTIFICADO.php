<?
//echo $arq_final."\n";

$caminhoaux = "../inc/nfe/sefaz4/certs/".$arq_nome;
$caminhocertempresa = "../certs/".$arq_nome;
$verifica = copy($arq_final,$caminhoaux);
//echo $caminhoaux."\n";
if($verifica){
	$_sql = "UPDATE arquivo SET caminho = '".$caminhoaux."' WHERE tipoarquivo = 'CERTIFICADO' and tipoobjeto = 'empresa' and idobjeto = ".$_idobjeto;
	$_res = d::b()->query($_sql) or die("Erro ao atualizar caminho da tabela arquivo. SQL: ".$_sql." ".mysqli_error(d::b()));
	$_sql1 = "UPDATE empresa SET certificado = '".$caminhocertempresa."' WHERE idempresa = ".$_idobjeto;
	$_res1 = d::b()->query($_sql1) or die("Erro ao atualizar caminho da tabela empresa. SQL: ".$_sql1." ".mysqli_error(d::b()));
}else{
	die("Erro ao copiar o arquivo ".$arq_nome." para o caminho ".$caminhoaux);
}
?>