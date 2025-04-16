<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/resultado_query.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/prodservtipoopcao_query.php");
require_once(__DIR__."/../querys/resultadoindividual_query.php");
require_once(__DIR__."/../querys/carimbo_query.php");


class TraResultadosController extends Controller{

    // ----- Variaveis -----
    public static $arrJsonResultado = [];



    // ----- FUNÇÕES -----
    public static function buscarDadosResultadosAmostra ($idamostra,$dataAmostra){

        $dataAmostra = strtotime($dataAmostra);
        $dataDoUltimoRegistroIdamostratra = strtotime('2019-09-30 17:00:15');

        if($dataDoUltimoRegistroIdamostratra > $dataAmostra){
            $colPk='idamostratra';
        } else {
            $colPk='idamostra';
        }

        $results = SQL::ini(AmostraQuery::buscarDadosResultadosAmostra(), [
            'idamostra' => $idamostra,
            'colIdAmostra' => $colPk
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{

            foreach ($results->data as $key => $value) {
                if($value["jresultado"]!=''){
                    $jsonResultado = unserialize(base64_decode($value["jresultado"]));
                    $results->data[$key]['jsonResultado'] = $jsonResultado;
                }
            }

            return  $results->data;
        }
    }


    public static function criarArrayDadosJsonAmostra($arrJsonResultado){
        foreach ($arrJsonResultado as $chave => $dados) {
            foreach ($dados['res'] as $key => $value) {

                if($key === "status"){
                    $row[$chave.$key] = $value;
                } else {
                    if(gettype($value) == 'array'){
                        $row[$chave] =  $dados['res'];
                    } else {
                        $row[$key] =  $value;
                    }
                }   
            }
        }

        self::$arrJsonResultado = $row;
        return $row;
    }



    public static function buscarDadosAmostra($idamostra){

        $results = SQL::ini(AmostraQuery::buscarDadosAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            if(empty($results->data[0]['enderecosacado'])){
                $results->data[0]['enderecosacado'] = '<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>';
            }
            return  $results->data[0];
        }
    }

    public static function buscarCaminhoArquivoUploadElisa($idresultado){

        $results = SQL::ini(ResultadoQuery::buscarCaminhoArquivoResultadoElisa($idresultado), [
            'idresultado' => $idresultado
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            if($results->data[0]){
                $link = '<a href="' . $results->data[0]['caminho'] . '" target="_blank"><img src="../inc/img/pdf-icon2.png"  style="position: absolute;right: 8px;top: 28px;"></a>';

            }
            return  $link;
        }
    }

    public static function buscarTextoInclusaoDeResultado($idtipoteste){

        $results = SQL::ini(ProdservQuery::buscarTextoInclusaoDeResultado($idtipoteste), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            if($results->data[0]){
                $textoInclusaoResultado = $results->data[0]['textoinclusaores'];
            }
            return  $textoInclusaoResultado;
        }
    }

    public static function buscarValorProdservTipoOpcao($idtipoteste){

        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarValorProdservTipoOpcao($idtipoteste), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data;
        }
    }

    public static function buscarIdentificacaoResultado($idresultado){

        $results = SQL::ini(ResultadoindividualQuery::buscarIdentificacaoResultado($idresultado), [
            'idresultado' => $idresultado
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data;
        }
    }

    public static function buscarAssinaturaTraResultado($idamostra){

        $results = SQL::ini(CarimboQuery::buscarAssinaturaTraResultado($idamostra), [
            'idamostra' => $idamostra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data;
        }
    }
    
}
?>