<?
if(!empty($_GET["idresultado"])){

require("../inc/php/functions.php");


$sqlassinatura = "SELECT ra.idresultado, ra.idempresa, ra.idpessoa, ra.criadopor, dma(ra.criadoem) as criadoem, ra.alteradopor, ra.alteradoem, p.nomecurto from resultadoassinatura ra
join pessoa p on p.idpessoa = ra.idpessoa
where ra.idresultado = '".$_GET["idresultado"]."'
order by 
ra.criadoem desc";

//die($sqlCarrimbo);
$resassinatura = d::b()->query($sqlassinatura) or die("Erro ao recuperar assinaturas: ".mysqli_error(d::b()));
$qtdassinatura = mysqli_num_rows($resassinatura);
if($qtdassinatura > 0){
    ?>
    
    <table style="width:800px">
<tr>
    <td>ID</td>
    <td>Assinado Em</td>
    <td>Id Pessoa</td>
    <td>Pessoa</td>
    <td>Chave</td>

</tr>


<?
	while ($r = mysql_fetch_assoc($resassinatura)){

 
   $arrAss = assinaturaDigitalA1($r["idresultado"].$r["criadoem"].$r["idpessoa"],'');
?>
<tr>
    <td><?=$r["idresultado"]?></td>
    <td><?=$r["criadoem"]?></td>
    <td><?=$r["idpessoa"]?></td>
    <td><?=$r["nomecurto"]?></td>
    <td><?=$arrAss["assinatura"];?></td>


</tr>


<?
}
?></table><?
}else{
?>
<table ><tr><td style=""><div class="titulogrupotd">&nbsp;</div><br>&nbsp;Falha ao pesquisar banco de dados. Entre em contato com o administrador.</td></tr></table>
<?
}

}?>

