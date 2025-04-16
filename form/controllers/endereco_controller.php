<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/endereco_query.php");


class EnderecoController extends Controller{

    // ----- FUNÇÕES -----
	public static function buscarCodcidadeCidade($uf)
	{
		$results = SQL::ini(EnderecoQuery::buscarCodcidadeCidade(), [
            'uf' => $uf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
           

            foreach($results->data as $_CodcidadeCidade)
            {	
                $listarCodCidade[$_CodcidadeCidade['codcidade']] = $_CodcidadeCidade['cidade'];                
            }
            return $listarCodCidade;
        }
	}

    public static function buscarTipoEndereco()
	{
		$results = SQL::ini(EnderecoQuery::buscarTipoEndereco())::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_tipoendereco)
            {	
                $listarTipoEndereco[$_tipoendereco['idtipoendereco']] = $_tipoendereco['tipoendereco'];                
            }
            return $listarTipoEndereco;
        }
	}


    public static function buscarEnderecoPessoa($idpessoa)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoPessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEnderecoPessoaPorIdEndereco($idendereco)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoPessoaPorIdEndereco(), [
            "idendereco" => $idendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEnderecoPorIdEndereco($idendereco)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoPorIdEndereco(), [
            "idendereco" => $idendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0]['endereco'];
        }
    }

    public static function buscarEnderecoPorIdpessoa($idpessoa)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoPorIdpessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static $ufBr = array(''=>'','AC' =>'AC','AL' =>'AL','AM' =>'AM','AP'=>'AP','BA' =>'BA','CE'=>'CE','DF' => 'DF','ES'=>'ES','GO' =>'GO','MA' =>'MA','MG' =>'MG',
    'MS' => 'MS', 'MT' => 'MT', 'PA' => 'PA', 'PB' => 'PB', 'PE' => 'PE', 'PI' => 'PI', 'PR' => 'PR', 'RJ' => 'RJ', 'RN' => 'RN', 'RO' => 'RO', 'RR' => 'RR', 'RS' => 'RS','SC' => 'SC','SE' => 'SE',
    'SP' => 'SP', 'TO' => 'TO', 'EX' => 'EX');



    public static function buscarEnderecoFaturamentoPorPessoa($idpessoa)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoFaturamentoPorPessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach($results->data as $_endereco)
            {	
                $listarEndereco[$_endereco['idendereco']] = $_endereco['endereco'];                
            }
            return $listarEndereco;
        }
    }

    public static function listarEnderecoFaturamentoPorPessoa($idpessoa)
	{
        $results = SQL::ini(EnderecoQuery::listarEnderecoFaturamentoPorPessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function listarEnderecoFaturamentoPorId($idendereco)
	{
        $results = SQL::ini(EnderecoQuery::listarEnderecoFaturamentoPorId(), [
            "idendereco" => $idendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function  listarEnderecoPessoaPorTipo($idpessoa,$idtipoendereco)
    {
        $results = SQL::ini(EnderecoQuery::listarEnderecoPessoaPorTipo(), [
            "idpessoa" => $idpessoa,
            "idtipoendereco" => $idtipoendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach($results->data as $_endereco)
            {	
                $listarEndereco[$_endereco['idendereco']] = $_endereco['endereco'];                
            }
            return $listarEndereco;
        }
    }

    
    public static function bucarEnderecoPorId($idendereco)
	{
        $results = SQL::ini(EnderecoQuery::bucarEnderecoPorId(), [
            "idendereco" => $idendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEnderecoPessoaPorTipo($idpessoa,$idtipoendereco)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoPessoaPorTipo(), [
            "idtipoendereco" => $idtipoendereco,
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEnderecoFaturamentoPorId($idendereco)
	{
        $results = SQL::ini(EnderecoQuery::buscarEnderecoFaturamentoPorId(), [
            "idendereco" => $idendereco
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }
    public static function buscarRotaPorEndereco($idendereco,$idpessoa)
    {
        $results = SQL::ini(EnderecoQuery::buscarRotaPorEndereco(), [
            "idendereco" => $idendereco,
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

}