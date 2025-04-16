<?
// CONTROLLER
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/sgcargofuncao_query.php");
require_once(__DIR__."/../querys/sgcargo_query.php");
require_once(__DIR__."/../querys/pessoasgfuncao_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/log_query.php");

class SgCargoController extends Controller
{
    public static $tipo = [
        '' => '',
        'ADVOGADO' => 'ADVOGADO',
        'ALMOXARIFE' => 'ALMOXARIFE',
        'ANALISTA' => 'ANALISTA',
        'ASSISTENTE' => 'ASSISTENTE',
        'AUXILIAR' => 'AUXILIAR',
        'COMPRADOR' => 'COMPRADOR',
        'CONSULTOR' => 'CONSULTOR',
        'COORDENADOR' => 'COORDENADOR',
        'COZINHEIRO' => 'COZINHEIRO',
        'DESIGNER' => 'DESIGNER',
        'DIRETOR' => 'DIRETOR',
        'EDITOR' => 'EDITOR',
        'ENCARREGADO' => 'ENCARREGADO',
        'ENGENHEIRO' => 'ENGENHEIRO',
        'ESPECIALISTA' => 'ESPECIALISTA',
        'ESTAGIÁRIO' => 'ESTAGIÁRIO',
        'GERENTE' => 'GERENTE',
        'INSPETOR' => 'INSPETOR',
        'JOVEM APRENDIZ' => 'JOVEM APRENDIZ',
        'LIDER' => 'LIDER',
        'MESTRE DE OBRAS' => 'MESTRE DE OBRAS',
        'MOTORISTA' => 'MOTORISTA',
        'OFICIAL' => 'OFICIAL',
        'PATOLOGISTA' => 'PATOLOGISTA',
        'PEDREIRO' => 'PEDREIRO',
        'PINTOR' => 'PINTOR',
        'PROGRAMADOR' => 'PROGRAMADOR',
        'PROJETISTA' => 'PROJETISTA',
        'STAFF' => 'STAFF',
        'SUPERVISOR' => 'SUPERVISOR',
        'TÉCNICO' => 'TÉCNICO'
    ];

    public static $nivel = [
        '' => '',
        'I' => 'I',
        'II' => 'II',
        'III' => 'III',
        'IV' => 'IV',
        'V' => 'V',
        'VI' => 'VI',
        'VII' => 'VII',
        'VII' => 'VII',
        'IX' => 'IX',
        'X' => 'X',
        'XI' => 'XI',
        'XII' => 'XII',
        'XIII' => 'XIII',
        'XIV' => 'XIV',
        'XV' => 'XV',
        'XVI' => 'XVI',
        'XVII' => 'XVII',
        'XVIII' => 'XVIII',
        'XIX' => 'XIX',
        'XX' => 'XX',
        'XXI' => 'XXI',
        'XXII' => 'XXII'
    ];

    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static function buscarFuncoesPorIdSgcargo($idSgcargo)
    {
        $funcoes = SQL::ini(SgcargoFuncao::buscarFuncoesPorIdSgcargo(), [
            'idsgcargo' => $idSgcargo
        ])::exec();

        if($funcoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $funcoes->errorMessage());
            return [];
        }

        return $funcoes->data;
    }

    public static function listarSetoresDepartamentosAreasVinculadas($idSgcargo)
    {
        $funcoes = SQL::ini(SgcargoFuncao::listarSetoresDepartamentosAreasVinculadas(), [
            'idsgcargo' => $idSgcargo
        ])::exec();

        if($funcoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $funcoes->errorMessage());
            return [];
        }

        return $funcoes->data;
    }


    public static function buscarAreasDepsSetoresDisponiveisParaVinculoPorGetIdEmpresa($idSgcargo)
    {
        $areasDepsSetores = SQL::ini(SgcargoQuery::buscarAreasDepsSetoresDisponiveisParaVinculoPorGetIdEmpresa(), [
            'getidempresasetor' => getidempresa('a.idempresa','sgsetor'),
            'getidempresaarea' => getidempresa('a.idempresa','sgarea'),
            'getidempresadep' => getidempresa('a.idempresa','sgdepartamento'),
            'getidempresacargo' => getidempresa('sc.idempresa','sgsetor'),
            'idsgcargo' => $idSgcargo
        ])::exec();

        if($areasDepsSetores->error()){
            parent::error(__CLASS__, __FUNCTION__, $areasDepsSetores->errorMessage());
            return [];
        }

        return $areasDepsSetores->data;
    }

    public static function atualizarPessoaFuncaoInseridasPorIdSgCargo($idSgcargo)
    {
        //Consulta que verifica se existem funções removidas que estão associadas aos funcionarios do cargo
        $funcoes = SQL::ini(PessoaQuery::buscarFuncoesPorIdSgcargoEGetIdEmpresa(), [
            'idsgcargo' => $idSgcargo,
            'getidempresa' => getidempresa('p.idempresa','pessoa')
        ])::exec();

        //Associa as funções encontradas acima às pessoas
        foreach($funcoes->data as $funcao)
        {
            $dadosPessoaSgfuncao = [
                'idpessoa' => $funcao['idpessoa'],
                'idsgfuncao' => $funcao['idsgfuncao'],
                'status' => 'ATIVO',
                'idempresa' => cb::idempresa(),
                'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
                'criadoem' => 'NOW()',
                'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
                'alteradoem' => 'NOW()'
                
            ];

            $inserindoPessoaSgfuncao = SQL::ini(PessoaSgfuncaoQuery::inserirPessoaSgfuncao(), $dadosPessoaSgfuncao)::exec();
        }
    }

    public static function atualizarPessoaFuncaoRemovidasPorIdSgCargo($idSgcargo)
    {
        // Consulta que verifica se existem funções removidas que estão associadas aos funcionarios do cargo
        $funcoes = SQL::ini(PessoaQuery::buscarFuncoesRemovidasPorIdSgcargoEGetIdEmpresa(), [
            'idsgcargo' => $idSgcargo,
            'getidempresa' => getidempresa('p.idempresa','pessoa')
        ])::exec();

        foreach($funcoes->data as $funcao)
        {
            $deletandoPessoaSgfuncao = SQL::ini(PessoaSgfuncaoQuery::deletarPorIdPessoaSgfuncao(), [
                'idpessoasgfuncao' => $funcao['idpessoasgfuncao']
            ])::exec();
        }
    }

    public static function atualizarPessoaFuncaoDePessoasSemCargo()
    {
        $pessoaFuncao = SQL::ini(PessoaQuery::buscarPessoaFuncaoPorGetIdEmpresa(), [
            'getidempresa' => getidempresa('p.idempresa','pessoa')
        ])::exec();
 
        foreach($pessoaFuncao->data as $item)
        {
            $deletandoPessoaSgfuncao = SQL::ini(PessoaSgfuncaoQuery::deletarPorIdPessoaSgfuncao(), [
                'idpessoasgfuncao' => $item['idpessoasgfuncao']
            ])::exec();
        }
    }
}

?>