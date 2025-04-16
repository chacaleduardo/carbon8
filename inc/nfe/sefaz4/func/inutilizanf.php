<?
include_once("../../../php/functions.php");
require_once "../vendor/autoload.php";
require_once("../../../php/cmd.php");
require_once("../../../../form/controllers/fluxo_controller.php");

$inicio     = $_POST["ini"];
$justify    = $_POST["jus"];
$max        = $_POST["max"];
$_idempresa        = $_POST["_idempresa"];

$filial = traduzid('empresa', 'idempresa', 'filial', $_idempresa);

if($filial=='Y'){
    $campo_impresa='idempresafat';
}else{
    $campo_impresa='idempresa';
}


if(empty($inicio) or empty($justify) or empty($max)){

    cbSetPostHeader("0","vazio");
    echo "Parâmetros vazios";
    die;

}

if($inicio > $max){

    cbSetPostHeader("0","inexistente");
    echo "Número de NFe Inválido";
    die;

}

if(strlen($justify) < 15){

    cbSetPostHeader("0","justify");
    echo "Justificativa muito curta";
    die;

}

$sql = "select * from nf where tiponf  = 'V' and envionfe='CONCLUIDA' AND nnfe = convert( lpad('".$inicio."', '9', '0') using latin1 ) AND ".$campo_impresa." = ".$_idempresa;
$res = d::b()->query($sql) or die("Erro ao consultar verificação de nnfe concluída. sql: ".$sql);

if(mysql_num_rows($res) > 0){
    $rownf=mysqli_fetch_assoc($res);
    cbSetPostHeader("0","concluida");
    echo "NFe já concluída idnf: ".$rownf['idnf'];
    die;

}

try {
    $sqln = "select idnf,nnfe from nf where tiponf  = 'V' and envionfe != 'CONCLUIDA' and ".$campo_impresa." = ".$_idempresa." AND nnfe = convert( lpad('".$inicio."', '9', '0') using latin1 )";
    $resn = d::b()->query($sqln) or die("Erro ao consultar nnfe. Sql: ".$sqln);
    $rown = mysql_fetch_assoc($resn);
    if(mysql_num_rows($resn) == 0){
        cbSetPostHeader("0","nfe not found");
        echo "NFe não encontrada";
        die;
    }

    $_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
    $_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa. Sql: ".$_sql);
    $_row=mysqli_fetch_assoc($_res);

    if(mysql_num_rows($_res) == 0){
        cbSetPostHeader("0","empresa not found");
        echo "Dados da empresa não encontrados";
        die;
    }

    $config = [
        "atualizacao" => $_row["nfatt"],
        "tpAmb" => 1, // Se deixar o tpAmb como 2 você emitirá a nota em ambiente de homologação(teste) e as notas fiscais aqui não tem valor fiscal
        "razaosocial" => $_row["nfrazaosocial"],
        "siglaUF" => $_row["nfsiglaUF"],
        "cnpj" => $_row["nfcnpj"],
        "schemes" => $_row["nfschemes"], 
        "versao" => $_row["nfversao"],
        "tokenIBPT" => $_row["nftokenIBPT"]
    ];

    $configJson = json_encode($config);

    $sqli1 = "INSERT INTO `log` (`idempresa`,`tipoobjeto`,`idobjeto`,`tipolog`,`log`,`info`,`status`,`criadoem`) 
                        VALUES (".$_idempresa.", 'nf', ".$rown["idnf"].", 'INUTILIZARNF', 'Dados para inutilizar NF: ".addslashes($configJson)."', '$justify', 'SUCESSO', now())";
    d::b()->query($sqli1) or die("Erro ao inserir na tabela nfinutilizada. Sql: ".$sqli1);

    $certificadoDigital = file_get_contents($_row["certificado"]);

    $tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));

    $nSerie = '1';
    $nIni = $inicio;
    $nFin = $inicio;
    $xJust = $justify;

    $response = $tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust, $config["tpAmb"]);

    $sqli2 = "INSERT INTO `log` (`idempresa`,`tipoobjeto`,`idobjeto`,`tipolog`,`log`,`info`,`status`,`criadoem`) 
                        VALUES (".$_idempresa.", 'nf', ".$rown["idnf"].", 'INUTILIZARNF', 'Retorno Sefaz: ".addslashes(json_encode($response))."', '$justify', 'SUCESSO', now())";
    d::b()->query($sqli2) or die("Erro ao inserir na tabela nfinutilizada. Sql: ".$sqli2);

    //você pode padronizar os dados de retorno atraves da classe abaixo
    //de forma a facilitar a extração dos dados do XML
    //NOTA: mas lembre-se que esse XML muitas vezes será necessário, 
    //      quando houver a necessidade de protocolos
    $stdCl = new NFePHP\NFe\Common\Standardize();
    //nesse caso $std irá conter uma representação em stdClass do XML
    $std = $stdCl->toStd($response);

	//print_r($std);
	if ($std->infInut->cStat == 241) {
	    exit($std->infInut->xMotivo);
    }


    //nesse caso o $arr irá conter uma representação em array do XML
    $arr = $stdCl->toArray();
    //nesse caso o $json irá conter uma representação em JSON do XML
    $json = $stdCl->toJson();

    //echo($std);
    
    
    $sqli3 = "INSERT INTO `log` (`idempresa`,`tipoobjeto`,`idobjeto`,`tipolog`,`log`,`info`,`status`,`criadoem`) 
                        VALUES (".$_idempresa.", 'nf', ".$rown["idnf"].", 'INUTILIZARNF', 'Retorno Json: ".addslashes($json)."', '$justify', 'SUCESSO', now())";
    d::b()->query($sqli3) or die("Erro ao inserir na tabela nfinutilizada. Sql: ".$sqli3);

   

    $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'INUTILIZADO');
    $_sql = "UPDATE nf SET idfluxostatus = '$idfluxostatus', status = 'INUTILIZADO' WHERE idnf = ".$rown["idnf"];
    d::b()->query($_sql) or die("Erro ao atualizar status NF Inutilizada: ".mysqli_error(d::b())); 

    FluxoController::inserirFluxoStatusHist('pedido', $rown["idnf"], $idfluxostatus, 'INUTILIZADO');

    $sqli4 = "INSERT INTO `log` (`idempresa`,`tipoobjeto`,`idobjeto`,`tipolog`,`log`,`info`,`status`,`criadoem`) 
                        VALUES (".$_idempresa.", 'nf', ".$rown["idnf"].", 'INUTILIZARNF', 'Atualiza Fluxo: ".addslashes($_sql)."', '$justify', 'SUCESSO', now())";
    d::b()->query($sqli4) or die("Erro ao inserir na tabela nfinutilizada. Sql: ".$sqli4);

    /*
    $_CMD = new CMD();
    $arr = array(
        "_inutilizar_i_log_idempresa" => $_SESSION["SESSAO"]["IDEMPRESA"]
        ,"_inutilizar_i_log_tipoobjeto" => 'nf'
        ,"_inutilizar_i_log_idobjeto" => $rown["idnf"]
        ,"_inutilizar_i_log_tipolog" => 'INUTILIZARNF'
        ,"_inutilizar_i_log_log" => 'NFe: ".$inicio." inutilizada.'
        ,"_inutilizar_i_log_info" => '".$justify."'
        ,"_inutilizar_i_log_status" => 'SUCESSO'
        ,"_inutilizar_i_log_criadoem" => 'now()'
    );

    $res = $_CMD->save($arr);

    if(!$res){
        die($_CMD->erro);
    }
    */

    $sqli = "INSERT INTO `log` (`idempresa`,`tipoobjeto`,`idobjeto`,`tipolog`,`log`,`info`,`status`,`criadoem`) 
                        VALUES (".$_idempresa.", 'nf', ".$rown["idnf"].", 'INUTILIZARNF', 'NFe: ".$rown["nnfe"]." inutilizada.', '".$justify."', 'SUCESSO', now())";
    $resi = d::b()->query($sqli) or die("Erro ao inserir na tabela nfinutilizada. Sql: ".$sqli);

    cbSetPostHeader("1","sucesso");
    echo $json;
    die("SUCESSO");
    
} catch (Exception $e) {
    cbSetPostHeader("0","inutilizar");
    echo "Erro ao Inutilizar NFe, veja o log de erro:";
    echo $e->getMessage();
}

    
    
?>

