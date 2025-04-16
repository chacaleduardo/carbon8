<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/prodserv_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$idUnidadePadrao = getUnidadePadraoModulo($_GET["_modulo"]);
$exames = ProdServController::buscarExamesPorIdUnidade($idUnidadePadrao, $_SESSION['SESSAO']['IDPESSOA'], true);

?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
<!-- Filtros -->
<div class="w-9/12 mx-auto mt-5">
    <div class="w-full border rounded overflow-hidden">
        <h3 class="py-3 text-center w-full bg-[#178B94] text-white uppercase font-bold">Portifólio</h3>
        <div class="px-4 py-3 flex flex-between items-end bg-[#F5F5F5]">
            <div class="w-10/12 pe-5">
                <label for="">Buscar exames</label>
                <input type="text" id="exames" class="h-[45px] w-full rounded border" />
            </div>
            <button id="btn-search" class="w-2/12 flex items-center h-[45px] justify-center uppercase text-white bg-[#178B94] rounded gap-3">
                <i class="fa fa-search text-xl"></i>
                <span class="fs-3 font-bold">Pesquisar</span>
            </button>
        </div>
    </div>
    <table class="w-full mt-5 mb-[5rem] rounded">
        <thead class="bg-slate-300">
            <tr>
                <th class="text-left py-1 px-2">Preço tabelado</th>
                <th class="text-left py-1 px-2">Preço com desconto</th>
                <th class="text-left py-1 px-2">Servico</th>
            </tr>
        </thead>
        <tbody id="tabela-exames">
            <? foreach ($exames as $exame) { ?>
                <tr data-id="<?= $exame['idprodserv'] ?>" class="odd:bg-white even:bg-slate-50 hover:bg-slate-300">
                    <td class="px-2"><?= is_numeric($exame['valor']) && $exame['valor'] > 0 ? aplicaMascara('MOEDA', floatval($exame['valor'])) : '-' ?></td>
                    <td class="px-2"><?= is_numeric($exame['valorFinal']) && $exame['valorFinal'] > 0 ? aplicaMascara('MOEDA', floatval($exame['valorFinal'])) : '-' ?></td>
                    <td class="px-2 descricao"><?= $exame['descr'] ?></td>
                </tr>
            <? } ?>
        </tbody>
    </table>
</div>
<? require_once(__DIR__ . '/js/portifolio_js.php'); ?>
<script>
    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>