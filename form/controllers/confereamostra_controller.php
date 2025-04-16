<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__ . "/../querys/amostra_query.php");
require_once(__DIR__ . "/../querys/amostracampos_query.php");
require_once(__DIR__ . "/../querys/modulocom_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/empresa_query.php");


class ConfereAmostraController extends Controller{

    public static function buscarAmostrasParaConferencia($registro_1,$registro_2,$registrop_1,$registrop_2,$nome,$idunidade,$flgoficial,$status,$exercicio){

        if(!empty($exercicio)){
            $clausula=" and a.exercicio =".$exercicio." ";
        }else{
            $year  = ( date("Y"));
            $clausula=" and a.exercicio =".$year." ";
        }
        
        if(!empty($registro_1) and !empty($registro_2)){
            $clausula.=" and a.idregistro between ".$registro_1." and ".$registro_2." ";
        }
        
        if(!empty($registrop_1) and !empty($registrop_2)){
            $clausula.=" and a.idregistroprovisorio between ".$registrop_1." and ".$registrop_2." ";
        }
        if(!empty($nome)){
            $clausula.=" and p.nome like ('%".$nome."%') ";
        }
        if(!empty($idunidade)){
            $clausula.=" and a.idunidade =".$idunidade." ";
        }
        
        if (!empty($flgoficial)){
             $clausular = " and IF(((IFNULL( `r`.`idsecretaria` , '') = '') OR (IFNULL(`r`.`idsecretaria`, '') = 0)), 'N', 'Y')  = '".$flgoficial."' ";
        }

        if($status=='CONFERIDO'){
            $exists=" exists "; 
        }else{
            $exists=" not exists "; 
        }

        $results = SQL::ini(AmostraQuery::buscarCorpoConferenciaAmostra(),[
            "clausula" => $clausula,
            "clausular" => $clausular,
            "exists" => $exists,
            ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            return $results;
        }
    }

    public static function buscarConfResultados($idamostra){
        $results = SQL::ini(AmostraQuery::buscarConfResultadoPorIdamostra(),[
            "idamostra" => $idamostra
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            $arrret=array();
            foreach($results->data as $k => $r){
                //para cada coluna resultante do select cria-se um item no array
                foreach ($r as $k1 => $col) {
                    $arrret[$r["idresultado"]][$k1]=$r[$k1];
                }
            }
            return $arrret;
        }
    }

    public static function buscarCamposVisiveis($idunidade,$idsubtipoamostra,$inCol){
        $results = SQL::ini(AmostraCamposQuery::buscarPorIdunidadeESubtipoAmostra(),[
            "idunidade" => $idunidade,
            "idsubtipoamostra" => $idsubtipoamostra,
            "inCol" => $inCol,
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            
            return $results->data[0];
        }
    }

    public static function buscarConferenciaAmostra($idamostra){
        $results = SQL::ini(CarimboQuery::buscarConferenciaAmostra(),[
            "idamostra" => $idamostra
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            
            return $results->numRows();
        }
    }

    public static function buscarFigRelatorio($idempresa){
        $results = SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresa(),[
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            
            return $results->data[0]['figrelatorioprod'];
        }
    }

    public static function buscarAmostraEPreferencia($idamostra){
        $results = SQL::ini(AmostraQuery::buscarAmostraEPreferencia(),[
            "idamostra" => $idamostra
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarComentariosCoferenciaDeAmostra($idamostra){
        $results = SQL::ini(ModulocomQuery::buscarComentariosCoferenciaDeAmostra(),[
            "idamostra" => $idamostra
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{
            return $results->data;
        }
    }

    public static function insereComentarioConferencia($idamostra,$comentario,$idempresa,$modulo,$usuario){
    
        $results = SQL::ini(ModulocomQuery::inserir(),[
            'idempresa' => $idempresa,
            'idmodulo' => $idamostra,
            'modulo' => $modulo,
            'descricao' => htmlentities($comentario),
            'status' => 'ATIVO',
            'criadopor' => $usuario,
            'criadoem' => 'now()',
            'alteradopor' => $usuario,
            'alteradoem' => 'now()'
        ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
        }else{

            $results1 = SQL::ini(ModulocomQuery::buscarPorChavePrimaria(),[
                "pkval" => $results->lastInsertId()
            ])::exec();

            if($results1->error()){
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return [];
            }else{
                $arr = array();
                foreach($results1->data as $key => $value){
                    if($key=='criadoem'){
                        $value=dmahms($value);
                    }
                    $arr[$key] = $value;
                }
                return parent::toJson($arr);
            }
        }
    }

    public static function desativarComentario($idmodulocom){
    
        $results = SQL::ini(ModulocomQuery::desativarComentario(),["idmodulocom" => $idmodulocom])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return false;
        }else{
            return true;
        }
    }

    public static function atualizarComentario($idmodulocom,$descricao,$usuario){
    
        $results = SQL::ini(ModulocomQuery::atualizarComentario(),[
            "idmodulocom" => $idmodulocom,
            "descricao" => $descricao,
            "usuario" => $usuario,
            ])::exec();

        if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return "";
        }else{
            $results1 = SQL::ini(ModulocomQuery::buscarPorChavePrimaria(),[
                "pkval" => $idmodulocom
            ])::exec();

            if($results1->error()){
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return [];
            }else{
                $arr = array();
                foreach($results1->data as $key => $value){
                    if($key=='criadoem'){
                        $value=dmahms($value);
                    }
                    $arr[$key] = $value;
                }
                return parent::toJson($arr);
            }
        }
    }
}
?>