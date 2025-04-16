<?

require_once("../inc/php/validaacesso.php");
if (isset($_GET['_idresultado'])) {
    $idResultado = "and idresultado in (" . $_GET['_idresultado'] . ")";
}

$sql = "select idresultado, idtipoteste, jsonconfig, jsonresultado from resultado where jsonconfig is not null $idResultado order by idresultado desc";
$res = d::b()->query($sql);


$listaProdservsErradas = "";
$listaResultadosAlterados = "";
while ($row = mysql_fetch_assoc($res)) {

    $jConf = json_decode($row["jsonconfig"]);
    $jResult = json_decode($row["jsonresultado"]);
    $controle = 0;

    if ($jConf->unidadeBloco['0']->personalizados['0']->titulo == 'TIPO DA AMOSTRA') {


        echo "idprodserv :" . $row["idtipoteste"] . " -" . " idresultado: " . $row["idresultado"] . " -- ";
        echo $jConf->unidadeBloco['0']->personalizados['0']->titulo . " => ";

        //seta valor correto
        $jConf->unidadeBloco['0']->personalizados['0']->titulo = 'TIPO DE AMOSTRA';


        $jConfEncode = json_encode($jConf);

        $sqlUpd = "UPDATE `laudo`.`resultado` SET `jsonconfig` = '" . $jConfEncode . "' WHERE `idresultado` = " . $row["idresultado"];
        echo "<!--$sqlUpd-->";
        d::b()->query($sqlUpd) or die('deu pau essa merda - jsonconfig');
    
        echo $jConf->unidadeBloco['0']->personalizados['0']->titulo . "<br>";

    }

    if ($jResult->INDIVIDUAL) {

        foreach ($jResult->INDIVIDUAL as $key => $value) {
            if ($value->titulo == 'TIPO DA AMOSTRA') {
                $jResult->INDIVIDUAL[$key]->titulo = 'TIPO DE AMOSTRA';
                $controle++;
            }
        }

        if($controle > 0){

            $jResultEncode = json_encode($jResult);            
            $sqlUpd = "UPDATE `laudo`.`resultado` SET  `jsonresultado` = '" . $jResultEncode . "' WHERE `idresultado` = " . $row["idresultado"];
            echo "<!--$sqlUpd-->";
            d::b()->query($sqlUpd) or die('deu pau essa merda - jsonresultado');    
        
            $listaProdservsErradas .= $row["idtipoteste"] . ",";
            $listaResultadosAlterados .= $row["idresultado"] . ",";
        }


    }




}

echo "<hr><br><br>LISTA DE PRODSERVS ERRADAS:   $listaProdservsErradas";
echo "<hr><br><br>LISTA DE RESULTADOS ALTERADOS:   $listaResultadosAlterados";
