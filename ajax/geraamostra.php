<?
//echo('entrou \n');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");

require_once("../model/evento.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");
require_once(__DIR__."/../form/controllers/formalizacao_controller.php");
require_once(__DIR__."/../form/controllers/lote_controller.php");

$idlote=$_GET['idlote'];

if(empty($idlote)){ die("Lote não informado");}

$confamostra = LoteController::buscarAmostraProdservlote($idlote); 

if(sizeof($confamostra) > 0)
{
    $unidade = UnidadeController::buscarUnidadePorIdtipoIdempresa(7, $confamostra['idempresa']);
    $idunidade = $unidade['idunidade'];
    if(empty($idunidade)){
        die("A unidade de CQ da empresa não esta configurada para empresa.");
    }

    //LTM - 13-04-2021: Retorna o Idfluxo ContaPagar
    $idfluxostatusAmostra = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);
    $idfluxostatusResultado = FluxoController::getIdFluxoStatus('resultado', 'ABERTO', $idunidade);



    if(empty($_SESSIONconfamostra["idsubtipoamostra"])){
        $idsubtipoamostra = 49;
    }else{
        $idsubtipoamostra = $confamostra["idsubtipoamostra"];
    } 
    if(empty($confamostra["idpessoa"])){
        die("Configurar no cadastro de empresa a empresa de ordem de produção.");
    }

    //Gerar nova amostra
    $arrReg = geraIdregistro($confamostra['idempresa'],$idunidade);
    $arrayAmostra = [
        "idpessoa" => $confamostra["idpessoa"], //INATA 3019
        "descricao" => $confamostra["descricao"],
        "idunidade" => $idunidade,
        "status" => 'ABERTO',
        "idfluxostatus" => $idfluxostatusAmostra,
        "dataamostra" => sysdate(),
        "idsubtipoamostra" => $idsubtipoamostra,
        "lote" =>  $confamostra['partida']."/". $confamostra['exercicio'],
        "idempresa" =>  $confamostra['idempresa'],
        "exercicio" => $arrReg["exercicio"],
        "idregistro" => $arrReg["idregistro"],
        "usuario" => $_SESSION["SESSAO"]["USUARIO"]
    ];
    $idamostraOrig = FormalizacaoController::inserirAmostraFormalizacao($arrayAmostra);

    //LTM - 13-04-2021: Insere FluxoHist Amostra
    $moduloAmostra = getModuloPadrao('amostra', $idunidade); 
    FluxoController::inserirFluxoStatusHist($moduloAmostra, $idamostraOrig, $idfluxostatusAmostra, 'PENDENTE');
      
    $arrprodserv = LoteController::buscarTestesProdservlote($idlote);

   

    //Prodserv: testes que foram marcados na formalização mas não existem na tabela de resultados
    foreach($arrprodserv as $prodserv)
    {
       

        //Gerar novo resultado
        $arrayResulado = [
            "idamostra" => $idamostraOrig, 
            "idtipoteste" => $prodserv['idprodserv'],
            "quantidade" => 1,
            "idempresa" => $confamostra['idempresa'],
            "idfluxostatus" => $idfluxostatusAmostra,
            "status" => 'ABERTO',
            "idfluxostatus" => $idfluxostatusResultado,
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];

        $idresultado = FormalizacaoController::inserirResultadoFormalizacao($arrayResulado);

       
        //LTM - 13-04-2021: Insere FluxoHist Resultado
        $moduloResultado = getModuloPadrao('resultado', $idunidade);

        FluxoController::inserirFluxoStatusHist($moduloResultado, $idresultado, $idfluxostatusResultado, 'PENDENTE');

       
    }

        //Insere as atividades no ObjetoVinculo com os Resultados
        $arrayObjetoVinculo = [
            "idobjeto" => $idamostraOrig, 
            "tipoobjeto" => 'amostra',
            "idobjetovinc" => $idlote,
            "tipoobjetovinc" => 'lote',
            "idfluxostatus" => $idfluxostatusAmostra,
            "criadopor" => $_SESSION["SESSAO"]["USUARIO"],
            "criadoem" => Date('Y-m-d H:i:s'),
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
            "alteradoem" => Date('Y-m-d H:i:s')
        ];

        // print_r($arrayObjetoVinculo);
        FormalizacaoController::inserirObjetoVinculo($arrayObjetoVinculo);

    echo('ok');
    
}
?>