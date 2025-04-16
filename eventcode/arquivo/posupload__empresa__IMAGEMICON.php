<?
//echo $arq_final."\n";

$caminhoaux = "../upload/imagenssistema/".$arq_nome;
$caminhoaux1 = "../inc/img/".$arq_nome;

$verifica = copy($arq_final,$caminhoaux);
$verifica1 = copy($arq_final,$caminhoaux1);

//echo $caminhoaux."\n";
if($verifica and $verifica1){
	$sd1 = "SELECT idempresaimagem
    FROM empresaimagem 
    WHERE tipoimagem='IMAGEMICON' 
        AND idempresa = ".$_idobjeto;

    $qd1 = d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #1: ".mysqli_error(d::b()));

    while($r1= mysqli_fetch_assoc($qd1)){
        unlink($r1["caminho"]);
        $sd1="DELETE FROM empresaimagem WHERE idempresaimagem=".$r1["idempresaimagem"];
        d::b()->query($sd1) or die(getNomeArquivo(__FILE__)." #2: ".mysqli_error(d::b()));
    }

    $sqlcert = "INSERT INTO empresaimagem (idempresa,tipoimagem,caminho,criadopor,criadoem,alteradopor,alteradoem)
        VALUES(".$_idobjeto.",'IMAGEMICON','".$caminhoaux."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
    $booins = mysql_query($sqlcert);

    $_sql = "UPDATE empresa SET iconemodal = '".$caminhoaux1."' WHERE idempresa = ".$_idobjeto;
    $booins1 = mysql_query($_sql);
}else{
	die("Erro ao copiar o arquivo ".$arq_nome." para o caminho ".$caminhoaux);
}
?>