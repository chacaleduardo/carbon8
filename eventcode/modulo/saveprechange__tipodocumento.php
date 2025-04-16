<?
$rotulodescritivo = $_SESSION['arrpostbuffer']['xr']['i']['sgdoctipodocumentoopcao']['rotulodescritivo'];

if(!empty($rotulodescritivo))
{
    $_SESSION['arrpostbuffer']['xr']['i']['sgdoctipodocumentoopcao']['rotulo'] = strtolower(str_replace(" ", "_", retira_acentos($rotulodescritivo)));
}

if($_POST['att'] && $_POST['new_pagina'] && $_POST['old_pagina'] && $_SESSION['arrpostbuffer']['1']['u']['sgdoctipodocumento']['idsgdoctipodocumento']){

    $sql = "SELECT sp.idsgdocpag,sp.pagina from sgdoc s JOIN sgdocpag sp
            where 
            s.idsgdoctipodocumento=".$_SESSION['arrpostbuffer']['1']['u']['sgdoctipodocumento']['idsgdoctipodocumento']."
            and sp.pagina=".$_POST['old_pagina'];

    $r = d::b()->query($sql)or die("Erro ao atualizar docs <br> ".$sql);
    $i = 0;
    while($row = mysqli_fetch_assoc($r)){

        $_SESSION['arrpostbuffer']['xx'.$i]['u']['sgdocpag']['idsgdocpag'] = $row['idsgdocpag'];
        $_SESSION['arrpostbuffer']['xx'.$i]['u']['sgdocpag']['pagina'] = $_POST['new_pagina'];

        $i++;
    }

}
?>