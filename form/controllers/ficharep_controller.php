<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__ . "/../querys/tag_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/sgdoc_query.php");
require_once(__DIR__ . "/../querys/nucleo_query.php");
require_once(__DIR__ . "/../querys/pessoa_query.php");
require_once(__DIR__ . "/../querys/analise_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/unidade_query.php");
require_once(__DIR__ . "/../querys/sequence_query.php");
require_once(__DIR__ . "/../querys/lotecons_query.php");
require_once(__DIR__ . "/../querys/ficharep_query.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/resultado_query.php");
require_once(__DIR__ . "/../querys/bioensaio_query.php");
require_once(__DIR__ . "/../querys/lotefracao_query.php");
require_once(__DIR__ . "/../querys/localensaio_query.php");
require_once(__DIR__ . "/../querys/formalizacao_query.php");
require_once(__DIR__ . "/../querys/servicoensaio_query.php");
require_once(__DIR__ . "/../querys/bioterioanalise_query.php");
require_once(__DIR__ . "/../querys/especiefinalidade_query.php");
require_once(__DIR__."/bioensaio_controller.php");


class FicharepController extends Controller{

    public static function buscarClientesParaEstudo( $getidempresa ){
        return BioensaioController::buscarClientesParaEstudo($getidempresa);
    }

    public static function buscarLotesParaUsoNaFicha( $idunidadepadrao,$idplantel,$getidempresa ){
        $results = SQL::ini(LoteQuery::buscarLotesParaUsoNaFicha(),[
            'getidempresa' => $getidempresa,
            'idplantel' => $idplantel,
            'idunidadepadrao' => $idunidadepadrao
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret1 = array();
            foreach($results->data as $k => $r){
                $arrret1[$r["idlote"]]["descr"]=$r["descr"];
            }
            return $arrret1;
        }
    }

    public static function listarEspecieFinalidadePorUnidade( $idunidadepadrao ){
        $results = SQL::ini(EspecieFinalidadeQuery::listarEspecieFinalidadePorUnidade(),[
            'idunidade' => $idunidadepadrao
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarModuloPorUnidade( $idunidadepadrao,$modulo ){
        return BioensaioController::buscarModuloPorUnidade($idunidadepadrao,$modulo);
    }

    public static function buscarDescrDoLoteFicharep( $idlote ){
        $results = SQL::ini(LoteQuery::buscarDescrDoLoteFicharep(),[
            'idlote' => $idlote
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLotePorIdObjetosoliporTipoobjetosolipor( $idobjetosolipor,$tipoobjetosolipor ){
        $results = SQL::ini(LoteQuery::buscarLotePorIdObjetosoliporTipoobjetosolipor(),[
            'idobjetosolipor' => $idobjetosolipor,
            'tipoobjetosolipor' => $tipoobjetosolipor,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarBioensaiosDaFichadeRep( $idficharep){
        $results = SQL::ini(FicharepQuery::buscarBioensaiosPorFicharep(),[
            'idficharep' => $idficharep
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        $arrRetorno = [];
        $idRegistro = '';

        foreach($results->data as $key => $item) {
            if($idRegistro != $item['idregistro']) {
                $idRegistro = $item['idregistro'];

                $arrRetorno[$idRegistro]['idbioensaio'] = $item['idbioensaio'];
                $arrRetorno[$idRegistro]['idregistro'] = $item['idregistro' ];
                $arrRetorno[$idRegistro]['exercicio'] = $item['exercicio' ];
            }

            $arrRetorno[$idRegistro]['bioensaios'][$key] = $item;
        }

        return $arrRetorno;
    }

    public static function buscarLoteUsadoNaFicharep( $idlote ){
        $results = SQL::ini(LoteFracaoQuery::buscarPorChavePrimaria(),[
            'pkval' => $idlote
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarProdutoParaFicharep( $idunidadepadrao,$idplantel ){
        $results = SQL::ini(ProdservQuery::buscarProdutoParaFicharep(),[
            'idunidadepadrao' => $idunidadepadrao,
            'idplantel' => $idplantel,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

}