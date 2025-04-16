<?
require_once("../../php/functions.php");

// CONTROLLERS
require_once(__DIR__."/../../../form/controllers/empresa_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo "Erro: Não autorizado.";
    die;
}

$token = $jwt["token"];

if(empty($_POST["tab"])){
    echo "Erro: Informe a tabela do módulo.";
    die;
}

if(!empty($_POST["verificaFiltro"]) and $_POST["verificaFiltro"] == 'Y'){
    $arrbypassempresa = retbypassidempresa();
    if(in_array($_POST["tab"],$arrbypassempresa)){
        echo "{\"message\":false}";
        die;
    }else{
        echo "{\"message\":true}";
        die;
    }
}

if($_POST["modulo"]){
    $clausulaEmpresaModulo="-- CONDICAO PARA FILTRAR EMPRESAS DE ACORDO COM A CONFIGURAÇÃO MODULO   
                        AND EXISTS( SELECT 
                            1
                        FROM

                        "._DBCARBON."._modulo m 
                        JOIN
                        objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
                        WHERE
                        m.modulo = '".$_POST["modulo"]."'
                        and oe.empresa = e.idempresa) ";
}

if($token->idtipopessoa != 3){

$clausulaEmpresaUsuario = " -- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
                        AND EXISTS
                        (
                            select 1 from objempresa oe where oe.objeto = 'pessoa' 
                            and oe.idobjeto = ".$token->idpessoa."
                            and oe.empresa = e.idempresa
                        ) ";

}
// $sql = "SELECT e.idempresa, e.nomefantasia, e.sigla 
//         FROM ( 
//             SELECT e.idempresa, e.nomefantasia, e.sigla
//             FROM empresa e
//             WHERE status = 'ATIVO'
//             and idempresa = ".cb::idempresa()."
//             AND e.sigla <> ''
//             UNION
//             SELECT e.idempresa, e.nomefantasia, e.sigla
//             from empresa e
//             where e.idempresa in  (select c.idempresa from matrizconf c where c.idmatriz = ".cb::idempresa().")
//             ".$clausulaEmpresaModulo."
//             ".$clausulaEmpresaUsuario."
//             ) as e";

// $res = d::b()->query($sql) or die("Erro: ao consultar sigla das empresas.");

$empresas = EmpresaController::buscarEmpresaPorIdEmpresaEClausulaModuloEUsuario(cb::idempresa(), $clausulaEmpresaModulo, $clausulaEmpresaUsuario);

if(!$empresas)
{
    echo "Erro: Nenhuma sigla de empresa encontrada.";
    die;
}

// echo json_encode($empresas);

echo "[";
// while($r = mysql_fetch_array($res)){
foreach($empresas as $empresa)
{
    echo $virg."{\"".$empresa['idempresa']."\":\"".jsonTrataValor($empresa['nomefantasia'])."\",\"sigla\":\"".trim($empresa['sigla'])."\"}";
    $virg=",";
}
echo "]";


?>