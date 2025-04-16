<?
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/../querys/arearepresentante_query.php");

class AreaRepresentanteController extends Controller {
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static function buscarAreasDisponiveisParaVinculo($idPessoa, $idEmpresa, $toAutocomplete = false) {
        $areas = SQL::ini(AreaRepresentanteQuery::buscarAreasDisponiveisParaVinculo(), [
            'idpessoa' => $idPessoa,
            'idempresa' => $idEmpresa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        if($toAutocomplete) {
            $arrRetorno = [];
            foreach($areas->data as $key => $area) {
                $arrRetorno[$key]['label'] = $area['area'];
                $arrRetorno[$key]['value'] = $area['idarearepresentante'];
            }

            return $arrRetorno;
        }

        return $areas->data;
    }

    public static function buscarAreasVinculadasPorIdPessoa($idPessoa) {
        $areas = SQL::ini(AreaRepresentanteQuery::buscarAreasVinculadasPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        return $areas->data;
    }

    public static function buscarGestoresERepresentantes($idAreaRepresentante, $idEmpresa) {
        $areas = SQL::ini(AreaRepresentanteQuery::buscarGestoresERepresentantes(), [
            'idarearepresentante' => $idAreaRepresentante,
            'idempresa' => $idEmpresa
        ])::exec();

        if($areas->error()){
            parent::error(__CLASS__, __FUNCTION__, $areas->errorMessage());
            return [];
        }

        $arrRetorno = [];

        foreach($areas->data as $area) {
            $arrRetorno[$area['idresponsavel']]['responsavel'] = $area['responsavel'];
            $arrRetorno[$area['idresponsavel']]['idpessoaobjeto'] = $area['idpessoaobjeto'];
            $arrRetorno[$area['idresponsavel']]['idpessoacontato'] = $area['idpessoacontato'];

            if(!$arrRetorno[$area['idresponsavel']]['clientes'])
                $arrRetorno[$area['idresponsavel']]['clientes'] = [];

            if(!in_array($area['idcliente'], array_map(function($item) { return $item['idcliente']; }, $arrRetorno[$area['idresponsavel']]['clientes'])))
                array_push($arrRetorno[$area['idresponsavel']]['clientes'], [
                    'idcliente' => $area['idcliente'],
                    'cliente' => $area['cliente']
                ]);
        }

        return $arrRetorno;
    }
}

?>