<?
// QUERY
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/eventotipocampos_query.php");
require_once(__DIR__."/../../form/querys/tagtipo_query.php");
require_once(__DIR__."/../../form/querys/eventosla_query.php");
require_once(__DIR__."/../../form/querys/eventorelacionamento_query.php");
require_once(__DIR__."/../../form/querys/tipopessoa_query.php");
require_once(__DIR__."/../../form/querys/eventotipoadd_query.php");
require_once(__DIR__."/../../form/querys/sgdoctipo_query.php");
require_once(__DIR__."/../../form/querys/unidade_query.php");
require_once(__DIR__."/../../form/querys/log_query.php");
require_once(__DIR__."/../../form/querys/eventotipo_query.php");
require_once(__DIR__."/../../form/querys/empresa_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class EventoTipoController extends Controller
{
    public static function buscarPorChavePrimaria($valorChavePrimaria)
    {
        $eventoTipo = SQL::ini(EventoTipoQuery::buscarPorChavePrimaria(), [
            'pkval' => $valorChavePrimaria
        ])::exec();

        if($eventoTipo->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());
            return [];
        }

        return $eventoTipo->data[0];
    }

    public static function inserirNovosCamposPorIdEventoTipo($idEventoTipo)
    {
        $inserirNovosCampos = SQL::ini(EventoTipoCamposQuery::inserirNovosCamposPorIdEventoTipo(), [
            'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
            'usuario' => $_SESSION["SESSAO"]["USUARIO"],
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($inserirNovosCampos->error()){
            parent::error(__CLASS__, __FUNCTION__, $inserirNovosCampos->errorMessage());
            return [];
        }

        return $inserirNovosCampos;
    }

    public static function inserirNovoCampoEventoTipoAddPorIdEventoTipoAdd($idEventoTipoAdd)
    {
        $inserindoNovoCampo = SQL::ini(EventoTipoCamposQuery::inserirNovoCampoEventoTipoAddPorIdEventoTipoAdd(), [
            'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
            'usuario' => $_SESSION["SESSAO"]["USUARIO"],
            'ideventotipoadd' => $idEventoTipoAdd
        ])::exec();

        if($inserindoNovoCampo->error()){
            parent::error(__CLASS__, __FUNCTION__, $inserindoNovoCampo->errorMessage());
            return [];
        }

        return $inserindoNovoCampo;
    }

    public static function buscarCamposDisponiveisPorIdEventoTipo($idEventoTipo)
    {
        $campos =  SQL::ini(EventoTipoCamposQuery::buscarEventoTipoCamposPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data;
    }

    public static function buscarCampoPorIdEventoTipoCampo($idEventoTipo,$campo)
    {
        $campos =  SQL::ini(EventoTipoCamposQuery::buscarCampoPorIdEventoTipoCampo(), [
            'ideventotipo' => $idEventoTipo,
            'campo' => $campo
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data[0];
    }

    public static function buscarEventoTipoCamposPorIdEventoTipoOpcao($idEventoTipo)
    {
        $campos =  SQL::ini(EventoTipoCamposQuery::buscarEventoTipoCamposPorIdEventoTipoOpcao(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data;
    }

    public static function buscarCodeIdEventoTipoCampos($ideventotipocampos)
    {
        $campos =  SQL::ini(EventoTipoCamposQuery::buscarCodeIdEventoTipoCampos(), [
            'ideventotipocampos' => $ideventotipocampos
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data[0];
    }

    public static function buscarEventoTipoCamposPorIdEventoTipoAdd($idEventoTipoAdd)
    {
        $campos = SQL::ini(EventoTipoCamposQuery::buscarEventoTipoCamposPorIdEventoTipoAdd(), [
            'ideventotipoadd' => $idEventoTipoAdd
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data;
    }

    public static function buscarCamposVisiveisPorIdEventoTipo($idEventoTipo)
    {
        $camposVisiveis = SQL::ini(EventoTipoCamposQuery::buscarEventoTipoCamposVisiveisPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($camposVisiveis->error()){
            parent::error(__CLASS__, __FUNCTION__, $camposVisiveis->errorMessage());
            return [];
        }

        return $camposVisiveis->data;
    }

    public static function buscarTodosTipoPessoa()
    {
        $tipoPessoa = SQL::ini(TipoPessoaQuery::buscarTodosTipoPessoa())::exec();

        if($tipoPessoa->error()){
            parent::error(__CLASS__, __FUNCTION__, $tipoPessoa->errorMessage());
            return [];
        }

        return $tipoPessoa->data;
    }

    public static function buscarTodosSgDocTipo()
    {
        $tipoSgDoc = SQL::ini(SgDocTipoQuery::buscarTodosSgDocTipo())::exec();

        if($tipoSgDoc->error()){
            parent::error(__CLASS__, __FUNCTION__, $tipoSgDoc->errorMessage());
            return [];
        }

        return $tipoSgDoc->data;
    }

    public static function buscarTodosTagTipo()
    {
        $tagTipo = SQL::ini(TagTipoQuery::buscarTodosTagTipo())::exec();

        if($tagTipo->error()){
            parent::error(__CLASS__, __FUNCTION__, $tagTipo->errorMessage());
            return [];
        }

        return $tagTipo->data;
    }

    public static function buscarEmpresasVinculadasAUmaAreaDepartamentoOuSetor($tabela)
    {
        $empresas = SQL::ini(EmpresaQuery::buscarEmpresasVinculadasAUmaAreaDepartamentoOuSetor(), [
            'tabela' => $tabela
        ])::exec();
        
        if($empresas->error()){
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function buscarCamposPrazoEDataPorIdEventoTipo($idEventoTipo)
    {
        $campos = SQL::ini(EventoTipoCamposQuery::buscarCamposPrazoEDataPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($campos->error()){
            parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
            return [];
        }

        return $campos->data;
    }

    public static function buscarEventoSlaPorIdEventoTipo($idEventoTipo, $status = '', $toFillSelect = false, $coluna = 'sla')
    {
        if($status)
        {
            $status = "AND status = '$status'";
        }

        $eventos = SQL::ini(EventoSlaQuery::buscarEventoSlaPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo,
            'status' => $status
        ])::exec();

        if($eventos->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($eventos->data as $evento)
            {
                $arrRetorno[$evento[$coluna]] = $evento[$coluna];
            }

            return $arrRetorno;
        }

        return $eventos->data;
    }

    public static function buscarEventoRelacionamentoPorIdEventoTipo($idEventoTipo)
    {
        $eventos = SQL::ini(EventoRelacionamentoQuery::buscarEventoRelacionamentoPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($eventos->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
            return [];
        } else {
            return $eventos->data;
        }
    }

    public static function buscarEventoTipoAddPorIdEventoTipo($idEventoTipo)
    {
        $eventoTipoAdd = SQL::ini(EventoTipoAddQuery::buscarEventoTipoAddPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($eventoTipoAdd->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipoAdd->errorMessage());
            return [];
        }

        return $eventoTipoAdd->data;
    }

    public static function buscarUnidadePorIdEventoTipo($idEventoTipo)
    {
        $unidades = SQL::ini(UnidadeQuery::buscarUnidadePorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo,
            'idempresa' => cb::idempresa()
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function buscarUnidadesDisponiveisParaVinculoPorIdEventoTipo($idEventoTipo)
    {
        $unidades = SQL::ini(UnidadeQuery::buscarUnidadesDisponiveisPorUnidadeObjetoSemVincComIdObjeto(), [
            'idobjeto' => $idEventoTipo,
            'tipoobjeto' => 'ideventotipo',
            'idempresa' => cb::idempresa()
        ])::exec();

        if($unidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $unidades->errorMessage());
            return [];
        }

        return $unidades->data;
    }

    public static function atualizarVersoesDeEventosQueEstejamNoStatusInicial($idEventoTipo)
    {
        $eventoTipo = SQL::ini(EventoTipoQuery::buscarTokenInicialEUltimaVersaoDoEventoTipoPorIdEventoTipo(), [
            'ideventotipo' => $idEventoTipo
        ])::exec();

        if($eventoTipo->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());

            return [];
        }

        $atualizandoEventos = SQL::ini(EventoTipoQuery::atualizarVersaoDeEventosQueEstejamNoStatusInicial(), [
            'ideventotipo' => $idEventoTipo,
            'tokeninicial' => $eventoTipo->data[0]['tokeninicial'],
            'versao' => $eventoTipo->data[0]['versao']
        ]);
    }

    public static function buscarCodigoPorConsulta($consulta)
    {
        $arrReturn = [];
        $result = SQL::ini($consulta)::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }

        foreach($result->data as $key => $item)
        {
            $i = 0;

            foreach($item as $value)
            {
                if($i == 0)
                {
                    $arrReturn[$key]['id'] = $value;

                    $i++;
                    if(count($item) > 1)
                        continue;    
                }
                
                $arrReturn[$key]['value'] = $value;
            }
        }

        return $arrReturn;
    }

    public static function carregarEventoTipoPorTipoEIdPessoa($tipo, $idpessoa)
    {
        $eventoTipos = SQL::ini(EventoTipoQuery::buscarEventoTipoPorTipoEIdPessoa(), [
            'tipo' => $tipo,
            'idpessoa' => $idpessoa
        ])::exec();

        if($eventoTipos->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipos->errorMessage());
            return [];
        }

        return $eventoTipos->data;
    }

    public static function buscarTiposPorIdEmpresa($idEmpresa, $toFillSelect = false)
    {
        $eventoTipos = SQL::ini(EventoTipoQuery::buscarEventoTipoPorIdEmpresa(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if($eventoTipos->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipos->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($eventoTipos->data as $tipo)
            {
                $arrRetorno[$tipo['ideventotipo']] = $tipo['eventotipo'];
            }

            return $arrRetorno;
        }

        return $eventoTipos->data;
    }

    public static function buscarEventoTipoPorIdEvento($idEvento)
    {
        $eventoTipo = SQL::ini(EventoTipoQuery::buscarEventoTipoPorIdEvento(), [
            'idevento' => $idEvento
        ])::exec();

        if($eventoTipo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());
            return [];
        }

        if(!$eventoTipo->numRows())
        {
            return [];
        }

        return $eventoTipo->data[0];
    }

    public static function buscarColIdEventoTipoCampos($ideventotipo, $ideventotipocampos)
    {
        $eventoTipo = SQL::ini(EventoTipoCamposQuery::buscarColIdEventoTipoCampos(), [
            'ideventotipo' => $ideventotipo,
            'ideventotipocampos' => $ideventotipocampos
        ])::exec();

        if($eventoTipo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());
            return [];
        }

        $arrayEventoTipo = [];
        foreach($eventoTipo->data as $_eventoTipo)
        {
            array_push($arrayEventoTipo, $_eventoTipo['col']);
        }

        return implode($arrayEventoTipo, ",");
    }
}

?>