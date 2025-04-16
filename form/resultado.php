<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/prodserv_controller.php");
require_once(__DIR__ . "/controllers/amostra_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$idUnidadePadrao = getUnidadePadraoModulo($_GET["_modulo"]);
$resultados = [];

$resultados = AmostraController::buscarResultadosCliente($_SESSION['SESSAO']['IDPESSOA'], $idUnidadePadrao);

?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
<!-- Filtros -->
<div class="w-10/12 mx-auto mt-5">
    <div class="w-full border rounded overflow-hidden">
        <h3 class="py-3 text-center w-full bg-[#178B94] text-white uppercase font-bold">Resultados</h3>
        <div class="px-4 py-3 flex flex-between items-end bg-[#F5F5F5]">
            <div class="w-3/12 pe-5">
                <label for="">Animal</label>
                <input name="" id="animal" class="h-[45px] w-full rounded border bg-white px-2" placeholder="Escolher animal" />
            </div>
            <div class="w-3/12 pe-5">
                <label for="">Tutor</label>
                <input name="" id="tutor" class="h-[45px] w-full rounded border bg-white px-2" placeholder="Escolher tutor" />
            </div>
            <div class="w-3/12 pe-5">
                <label for="">Período</label>
                <input name="" id="periodo" class="h-[45px] w-full rounded border bg-white px-2" placeholder="Escolher um período" />
            </div>
            <button id="btn-search" class="w-2/12 ml-auto flex items-center h-[45px] justify-center uppercase text-white bg-[#178B94] rounded gap-3">
                <i class="fa fa-search text-xl"></i>
                <span class="fs-3 font-bold">Pesquisar</span>
            </button>
        </div>
    </div>
    <table class="w-full mt-5 mb-[5rem] rounded">
        <thead class="bg-slate-300">
            <tr class="uppercase">
                <th class="text-center py-1 px-2">ID Amostra</th>
                <th class="text-center py-1 px-2">Data registro</th>
                <th class="text-center py-1 px-2">Cliente</th>
                <th class="text-center py-1 px-2">Tutor</th>
                <th class="text-center py-1 px-2">Paciente</th>
                <th class="text-center py-1 px-2">Espécie</th>
                <!-- <th class="text-center py-1 px-2">Raça</th> -->
                <th class="text-center py-1 px-2">Sexagem</th>
                <th class="text-center py-1 px-2">Idade</th>
                <th class="text-center py-1 px-2">Tipo de amostra</th>
                <th class="text-center py-1 px-2">Status</th>
            </tr>
        </thead>
        <tbody id="tabela-resultados">
            <? foreach ($resultados as $resultado) { ?>
                <tr data-periodo="<?= $resultado['dataamostra'] ?>" data-id="<?= $resultado['idprodserv'] ?>" class="odd:bg-white even:bg-slate-50 hover:bg-slate-300 cursro-pointer" onclick="abrirPdf(<?= $resultado['idresultado'] ?>)">
                    <td class="text-center py-3 px-2"><?= $resultado['idamostra'] ?></td>
                    <td class="text-center py-3 px-2 dataamostra"><?= dma($resultado['dataamostra']) ?></td>
                    <td class="text-center py-3 px-2"><?= $resultado['nome'] ?></td>
                    <td class="text-center tutor py-3 px-2"><?= $resultado['tutor'] ?></td>
                    <td class="text-center paciente py-3 px-2"><?= $resultado['paciente'] ?></td>
                    <td class="text-center py-3 px-2"><?= $resultado['especie'] ?></td>
                    <!-- <td class="text-center py-3 px-2"><?= $resultado['raca'] ?? 'Sem raça definida' ?></td> -->
                    <td class="text-center py-3 px-2"><?= $resultado['sexo'] ?></td>
                    <td class="text-center py-3 px-2"><?= $resultado['idade'] ?> <?= $resultado['tipoidade'] ?></td>
                    <td class="text-center py-3 px-2"><?= $resultado['tipoamostra'] ?></td>
                    <td class="text-center py-3 px-2"><?= $resultado['status'] ?></td>
                </tr>
            <? } ?>
        </tbody>
    </table>
</div>
<? require_once(__DIR__ . '/js/resultado_js.php'); ?>
<script>
    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>