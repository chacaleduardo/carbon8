<?
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/nfvolume_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodservformula_query.php");
require_once(__DIR__."/../querys/prodservformulains_query.php");

class ProdservformulaController  extends Controller
{
    public static function buscarPorChavePrimaria($id)
    {
        $prodServFormula = SQL::ini(ProdservFormulaQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if($prodServFormula->error()){
            parent::error(__CLASS__, __FUNCTION__, $prodServFormula->errorMessage());
            return [];
        }

        return $prodServFormula->data[0];
    }

    public static function buscarValorVendaFormula($idprodservformula){
        $results = SQL::ini(ProdservformulaQuery::buscarValorVendaFormula(), [          
            "idprodservformula"=>$idprodservformula           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarFormulaPorProdserv($idprodserv){
        $results = SQL::ini(ProdservformulaQuery::buscarFormulaPorProdserv(), [          
            "idprodserv"=>$idprodserv           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idprodservformula']] = $_valor['rotulo'];                
            }
            return $lista;
        }
    }

    public static function buscarFormulaAtivaPorProdserv($idprodserv){
        $results = SQL::ini(ProdservformulaQuery::buscarFormulaAtivaPorProdserv(), [          
            "idprodserv"=>$idprodserv           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idprodservformula']] = $_valor['rotulo'];                
            }
            return $lista;
        }
    }

    public static function buscarRotuloFormulaPorId($idprodservformula){
        $results = SQL::ini(ProdservformulaQuery::buscarRotuloFormulaPorId(), [          
            "idprodservformula"=>$idprodservformula           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function listarProdservFormulaPlantel($idprodserv, $idprodservformula = NULL)
    {
        if(!empty($idprodservformula))
        {
            $condicaoWhere = " AND f.idprodservformula = ".$idprodservformula;
        }
        
        $results = SQL::ini(ProdservformulaQuery::listarProdservFormulaPlantel(), [          
            "idprodserv" => $idprodserv,
            "condicaoWhere" => $condicaoWhere          
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret = [];
            foreach ($results->data as $_formula) 
            {
                foreach($_formula as $_formulacol => $_formulaval)
			    {
                    $arrret[$_formula['idprodservformula']][$_formulacol] = $_formulaval;
                }

                $arrret[$_formula['idprodservformula']]["prodservformulains"] = self::listarProdservFormulaIns($_formula['idprodservformula']);
            }

            return $arrret;
        }
    }

    public static function listarProdservFormulaIns($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::listarProdservFormulaIns(), [          
            "idprodservformula" => $idprodservformula        
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrret = [];
            foreach ($results->data as $_formulains) 
            {
                foreach($_formulains as $_formulaInscol => $_formulaInsval)
			    {
                    $arrret[$_formulains['idprodservformulains']][$_formulaInscol] = $_formulaInsval;
                }
            }

            return $arrret;
        }
    }

    public static function listarProdservFormulaInsComprados($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::listarProdservFormulaInsComprados(), [          
            "idprodservformula" => $idprodservformula       
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function listarFormulas($idprodserv)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::listarFormulas(), [          
            "idprodserv" => $idprodserv       
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarIdprodservFormula($idprodserv)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarIdprodservFormula(), [          
            "idprodserv" => $idprodserv       
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarProdServFormulaPorIdProdServEStatus($idProdServ, $status, $toFillSelect = false)
    {
        $arrRetorno = [];

        $prodServFormula = SQL::ini(ProdservFormulaQuery::buscarProdServFormulaPorIdProdServEStatus(), [
            'idprodserv' => $idProdServ,
            'status' => $status
        ])::exec();

        if($prodServFormula->error()){
            parent::error(__CLASS__, __FUNCTION__, $prodServFormula->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            foreach($prodServFormula->data as $item)
            {
                $arrRetorno[$item['idprodservformula']] = $item['rotulo'];
            }

            return $arrRetorno;
        }

        return $prodServFormula->data;
    }

    public static function buscarDadosProdservFormulaInsPorIdProdservFormula($idprodservformula, $status)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::buscarDadosProdservFormulaInsPorIdProdservFormula(), [          
            "idprodservformula" => $idprodservformula,
            "status" => $status
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarFilhosProdservFormulaInsPorIdProdservFormula($idprodservformulanova, $idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::buscarFilhosProdservFormulaInsPorIdProdservFormula(), [          
            "idprodservformulanova" => $idprodservformulanova,
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function atualizarCustoArvoreProdservFormula($vlrcusto, $idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::atualizarCustoArvoreProdservFormula(), [          
            "vlrcusto" => $vlrcusto,
            "idprodservformula" => $idprodservformula    
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarArvoreProdservFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::atualizarArvoreProdservFormula(), [          
            "idprodservformula" => $idprodservformula    
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirProdservFormulaComSelect($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::inserirProdservFormulaComSelect(), [          
            "idprodservformula" => $idprodservformula    
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirProservFormulaIns($arrIsumos)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::inserirProservFormulaIns(), $arrIsumos)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarProdservDeFormulaEFormulaInsPorStatusEIdProdservFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarProdservDeFormulaEFormulaInsPorStatusEIdProdservFormula(), [          
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarVolumeEQtdProdservFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarVolumeEQtdProdservFormula(), [          
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLotePorProdservFormula($idprodservformula, $idpessoa, $idsolfab)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::buscarLotePorProdservFormula(), [          
            "idprodservformula" => $idprodservformula,
            "idpessoa" => $idpessoa,
            "idsolfab" => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }
}