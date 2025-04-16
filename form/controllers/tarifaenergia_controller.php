<?
require_once(__DIR__ . "/_controller.php");

// QUERYS

require_once(__DIR__ . "/../querys/tarifaenergia_query.php");


class PrecoEnergiaController extends Controller
{

    public static function buscarStatusTarifa($idtarifaenergiapadrao)
    {
        $results = SQL::ini(PrecoEnergiaQuery::buscarStatusTarifa(), [
            'idtarifaenergiapadrao' => $idtarifaenergiapadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
        }
    }

    public static function buscarValordePico($idtarifaenergiapadrao)
    {
        $results = SQL::ini(PrecoEnergiaQuery::buscarValordePico(), [
            'idtarifaenergiapadrao' => $idtarifaenergiapadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarIntervalosExistentes($idtarifaenergiapadrao)
    {
        $results = SQL::ini(PrecoEnergiaQuery::buscarIntervalosExistentes(), [
            'idtarifaenergiapadrao' => $idtarifaenergiapadrao,
            
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            return $dados;
        }
    }

    public static function BuscarTarifaAtivo($idtarifaenergiapadrao)
    {
        $results = SQL::ini(PrecoEnergiaQuery::BuscarTarifaAtivoParaVinculo(), [
            'idtarifaenergiapadrao' => $idtarifaenergiapadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function BuscarTarifaAtivoParaVinculo()
    {
        $results = SQL::ini(PrecoEnergiaQuery::BuscarTarifaAtivoParaVinculo(), [

        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

}
