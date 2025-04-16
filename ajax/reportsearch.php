<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["relatorio"]) || empty($_GET["_idempresa"])){
    echo '{"erro":"Parâmetro enviado é inválido"}';
    die;
} else {
    $parameter=$_GET["relatorio"];
    $_idempresa=$_GET["_idempresa"];
}

    $qr = "SELECT 
        mr.idrep, r.rep, mr.modulo, r.url, r.tipograph, r.titlebutton
        FROM
        carbonnovo._modulorep mr
            JOIN
        carbonnovo._rep r ON (mr.idrep = r.idrep)
        WHERE
        r.rep LIKE '%".$parameter."%'
            AND EXISTS( SELECT 
                1
            FROM
                carbonnovo._lprep ep
            WHERE
                ep.idlp IN (".getModsUsr("LPS").")
                    AND ep.idrep = r.idrep)
            AND EXISTS( SELECT 
                1
            FROM
                carbonnovo._modulo m
            WHERE
                EXISTS( SELECT 
                        1
                    FROM
                        objempresa oe
                    WHERE
                        oe.objeto = 'modulo'
                            AND oe.idobjeto = m.idmodulo
                            AND oe.empresa = ".$_idempresa.")
                    AND m.modulo = mr.modulo)
            AND r.tab <> ''
            AND r.status = 'ATIVO'
        GROUP BY mr.idrep";

    $rs = d::b()->query($qr) or die('{"erro":"Na procura dos relatórios"}');
    if(mysqli_num_rows($rs) > 0){
    $i = 0;
    $arrtmp = array();
    while($rw = mysqli_fetch_assoc($rs)){
        $arrtmp[$i]["idrep"] = $rw["idrep"];
        $arrtmp[$i]["rep"] = $rw["rep"];
        $arrtmp[$i]["modulo"] = $rw["modulo"];
        $arrtmp[$i]["url"] = $rw["url"];
        $arrtmp[$i]["tipograph"] = $rw["tipograph"];
        $arrtmp[$i]["titlebutton"] = $rw["titlebutton"];
        $i++;
    }
    echo json_encode($arrtmp, true);
    }else{
    echo "[]";
    }
