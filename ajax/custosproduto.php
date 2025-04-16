<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/prodservcusto_controller.php");


$jwt = validaToken();

// if($jwt["sucesso"] !== true){
//     echo JSON_ENCODE([
//         'error' => "Erro: Não autorizado."
//     ]);
//     die;
// }

if(!empty($_GET['idprodserv'])){
    $custos = ProdservCustoController::buscarCustosPorIdprodserv($_GET['idprodserv'])[0];

    if(empty($custos)){?>
        <div class="col-md-12 d-flex justify-content-center align-items-center">
            <div class="btn btn-success"
                onclick="criaCustoProdserv(<?=$_GET['idprodserv']?>,this)"
                hx-get="/ajax/custosproduto.php?idprodserv=<?=$_GET['idprodserv']?>"
                hx-trigger="click delay:1000ms" hx-target="#dados<?=$_GET['idprodserv']?>"
                hx-swap="innerHTML"  id="controle<?=$_GET['idprodserv']?>">
                Configurar custos
            </div>
        </div>
    <?}else{?>
        <div class="col-md-12 d-flex justify-content-between align-items-center" idprodserv="<?=$custos['idprodservcusto']?>">
            <div class="col-md-3">
                Custo de operação (%)
            </div>
            <div class="col-md-3">
                <input type="hidden" idprodservcusto="<?=$custos['idprodservcusto']?>" name="_<?=$custos['idprodservcusto']?>_u_prodservcusto_idprodservcusto" value="<?=$custos['idprodservcusto']?>">
                <input type="number" vdecimal min="0.00" max="100" idprodservcusto="<?=$custos['idprodservcusto']?>" name="_<?=$custos['idprodservcusto']?>_u_prodservcusto_custooperacao" value="<?=$custos['custooperacao']?>">
            </div>
            <div class="col-md-3">
                Custo de Mão de obra (%)
            </div>
            <div class="col-md-3">
                <input type="number" vdecimal min="0.00" max="100" idprodservcusto="<?=$custos['idprodservcusto']?>" name="_<?=$custos['idprodservcusto']?>_u_prodservcusto_customaodeobra" value="<?=$custos['customaodeobra']?>">
            </div>
        </div>
        <div class="col-md-12 d-flex justify-content-end align-items-center">
            <button
                class="btn btn-success"
                type="button"
                style="margin-right: 0.5%;"
                onclick="salvarBloco(<?=$custos['idprodservcusto']?>)"
                >
                Salvar
            </button>
        </div>
    <?}
}

?>