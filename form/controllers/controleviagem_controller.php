<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/controleviagem_query.php");

class ControleViagemController extends Controller {
    public static function buscarViagem($idControleViagem) {
        $viagem = SQL::ini(ControleViagemQuery::buscarViagem(), [
            'idcontroleviagem' => $idControleViagem
        ])::exec();

        if($viagem->error()){
            parent::error(__CLASS__, __FUNCTION__, $viagem->errorMessage());
            return '';
        }

        return $viagem->data[0];
    }

    public static function existeViagemEmAndamentoPorUsuario($usuario) {
        $viagem = SQL::ini(ControleViagemQuery::existeViagemEmAndamentoPorUsuario(), [
            'usuario' => $usuario
        ])::exec();

        if($viagem->error()){
            parent::error(__CLASS__, __FUNCTION__, $viagem->errorMessage());
            return true;
        }

        return $viagem->numRows() > 0;
    }
}
?>