<?
if($_POST["_old_rhtipoevento_valor"] != $_POST["_1_u_rhtipoevento_valor"] AND $_POST["_1_u_rhtipoevento_idrhtipoevento"] == 21){

    $vOld = str_replace(",", ".", $_POST["_old_rhtipoevento_valor"]);;
    $vNew = floatval(str_replace(",", ".", $_POST["_1_u_rhtipoevento_valor"]));

    $qr = "SELECT idrheventopessoa, valor
            FROM rheventopessoa
            WHERE status = 'ATIVO' AND
            idrhtipoevento = 21";
    $rs = d::b()->query($qr);

    while($rw = mysqli_fetch_assoc($rs)){
        $qtdDependentes = $rw["valor"] / $vOld;

        $newVal = $vNew * $qtdDependentes;

        d::b()->query("UPDATE rheventopessoa SET valor = ".$newVal." WHERE idrheventopessoa = ".$rw["idrheventopessoa"]);
    }
}
?>