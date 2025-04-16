<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/servicobioterio_query.php");
require_once(__DIR__ . "/../querys/servicobioterioconf_query.php");
require_once(__DIR__ . "/../querys/bioterioanaliseteste_query.php");

require_once(__DIR__ . "/../controllers/tipoprodserv_controller.php");



class BioterioAnaliseController extends Controller{

    public static function buscarConfiguracaoDoServico( $idbioterioanalise ){
        $results = SQL::ini(ServicoBioterioConfQuery::buscarConfiguracaoDoServico(),[
                'idbioterioanalise' => $idbioterioanalise
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTesteDaConfiguracao( $idservicobioterioconf ){
        $results = SQL::ini(BioterioAnaliseTesteQuery::buscarTestesDaConfiguracao(),[
                'idservicobioterioconf' => $idservicobioterioconf
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarServicosParaConf( $getidempresa ){
        $results = SQL::ini(ProdservQuery::buscarServicosParaEnsaio(),[
                'getidempresa' => $getidempresa,
                'idunidade' => "2,4,9",
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function listarTipoProdservTipoProdServ($idempresa)
    {
        $arrTipoProdserv = [];
        $tipoProdserv = TipoProdServController::listarTipoProdservTipoProdServ($idempresa);
        foreach ($tipoProdserv as $_dadostipoProdserv) {
            $arrTipoProdserv[$_dadostipoProdserv["idtipoprodserv"]] = $_dadostipoProdserv["tipoprodserv"];
        }
        return $arrTipoProdserv;
    }

    public static function buscarPorIdEspecieFinalidadeEIdEmpresa($idEspecieFinalidade, $idEmpresa = 0) {
        if(!$idEmpresa) $idEmpresa = cb::idempresa();

        $results = SQL::ini(BioterioAnaliseQuery::buscarPorIdEspecieFinalidade(),[
            'idespeciefinalidade' => $idEspecieFinalidade,
            'idempresa' => $idEmpresa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        
        return $results->data[0];
    }
    

    public static $ArrayVias = array('INTRA-MUSCULAR-COXA' => 'Int.-Musc. Coxa',
                                    'INTRA-MUSCULAR-PEITO' => 'Int. Musc. Peito' ,
                                    'SUBCUTANEA' => 'Subcutânea' ,
                                    'INTRA-PERITONEAL' => 'Int. Peritoneal',
                                    'INTRA-MUSCULAR' => 'Int. Muscular',
                                    'INTRA-OCULAR' => 'Int. Ocular',
                                    'INTRA-OVO' => 'Int. Ovo',
                                    'INTRA-VENOSA' => 'Int. Venosa',
                                    'ORAL' => 'Oral',
                                    'NASAL' => 'Nasal',
                                    'USO-TOPICO' => 'Uso Tópico');
}