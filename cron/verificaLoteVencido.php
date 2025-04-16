<?
require_once(__DIR__ . '/../form/controllers/lote_controller.php');


$atualizandoLotes = LoteController::atualizarLotesVencidos();

if(!$atualizandoLotes) {
    echo 'Erro ao atualizar lotes vencidos';
    return;
} 

echo 'Lotes vencidos atualizados';

$atualizandoLotes = LoteController::revalidarLotesAVencer();

echo '<br> Lotes a vencer atualizados.';
