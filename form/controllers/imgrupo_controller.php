<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/emailvirtualconf_query.php");
require_once(__DIR__."/../querys/emailvirtualconfimgrupo_query.php");
require_once(__DIR__."/../querys/sgareagrupo_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/fluxoobjeto_query.php");
require_once(__DIR__."/../querys/imgrupopessoa_query.php");
require_once(__DIR__."/../querys/_lpgrupo_query.php");
require_once(__DIR__ . "/../querys/log_query.php");
require_once(__DIR__ . "/../querys/immsgconfdest_query.php");
require_once(__DIR__ . "/../querys/imgrupo_query.php");


// CONTROLLERS
require_once(__DIR__."/_controller.php");

class ImGrupoController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static $tiposObjeto = [
        'manual' => 'manual',
        'clone' => 'pessoagrupo',
        'grupogrupo' => 'grupogrupo',
        'sgsetor' => 'setor',
        'sgdepartamento' => 'departamento',
        'sgarea' => 'area',
        'sgconselho' => 'conselho',
        'tipopessoa' => 'tipopessoa',
        '_lp' => 'LP'
    ];

    public static $simNao = [
        'Y' => 'Sim',
        'N' => 'Não'
    ];

    public static function buscarEmailsVirtuaisDisponiveisParaVinculoPorIdImGrupo($idImGrupo)
    {
        $emailsVirtuais = SQL::ini(EmailVirtualConfQuery::buscarEmailsVirtuaisDisponiveisParaVinculoPorIdImGrupoEGetIdEmpresa(), [
            'idimgrupo' => $idImGrupo,
            'getidempresa' => getidempresa('e.idempresa','imgrupo'),
            'getidempresa2' => getidempresa('i.idempresa','imgrupo')
        ])::exec();

        if($emailsVirtuais->error()){
            parent::error(__CLASS__, __FUNCTION__, $emailsVirtuais->errorMessage());
            return [];
        }

        return $emailsVirtuais->data;
    }

    public static function buscarFluxosVinculadosPorIdImGrupo($idImGrupo)
    {
        $fluxos = SQL::ini(FluxoObjetoQuery::buscarPorIdobjetoETipoObjeto(), [
            'idobjeto' => $idImGrupo,
            'tipoobjeto' => 'imgrupo'
        ])::exec();

        if($fluxos->error()){
            parent::error(__CLASS__, __FUNCTION__, $fluxos->errorMessage());
            return [];
        }

        return $fluxos->data;
    }

    public static function buscarPessoasVinculadasPorIdImGrupo($idImGrupo)
    {
        $pessoas = SQL::ini(ImGrupoPessoaQuery::buscarPessoasPorIdImGrupo(), [
            'idimgrupo' => $idImGrupo
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function buscarGruposDeAreasDepsSetoresPorIdImGrupo($idImGrupo)
    {
        $grupos = SQL::ini(ObjetoVinculoQuery::buscarGruposDeAreasDepsSetoresPorIdImGrupo(), [
            'idimgrupo' => $idImGrupo
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarGruposDeAreasDepsSetoresDisponiveisParaVinculoPorIdImGrupo($idImGrupo)
    {
        $grupos = SQL::ini(ObjetoVinculoQuery::buscarGruposDeAreasDepsSetoresDisponiveisParaVinculoPorIdImGrupo(), [
            'idimgrupo' => $idImGrupo
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarPessoasDisponiveisParaVinculoPorIdImGrupoEIdEmpresa($idImGrupo, $idEmpresa)
    {
        $pessoas = SQL::ini(PessoaQuery::buscarPessoasDisponiveisParaVinculoPorIdImGrupoEIdEmpresa(), [
            'idimgrupo' => $idImGrupo,
            'idempresa' => $idEmpresa
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function buscarEmailsVirtuaisPorIdImGrupo($idImGrupo)
    {
        $emails = SQL::ini(EmailVirtualConfImGrupoQuery::buscarEmailsVirtuaisPorIdImGrupoEGetIdEmpresa(), [
            'idimgrupo' => $idImGrupo,
            'getidempresa' => getidempresa('e.idempresa','imgrupo')
        ])::exec();

        if($emails->error()){
            parent::error(__CLASS__, __FUNCTION__, $emails->errorMessage());
            return [];
        }

        return $emails->data;
    }

    public static function buscarLpGrupoPorIdLp($idLp)
    {
        $lpGrupo = SQL::ini(_LpGrupoQuery::buscarLpGrupoPorIdLp(), [
            'idlp' => $idLp
        ])::exec();

        if($lpGrupo->error()){
            parent::error(__CLASS__, __FUNCTION__, $lpGrupo->errorMessage());
            return [];
        }

        return $lpGrupo->data;
    }

    public static function atualizarGruposSetores()
    {
        $atualizarGruposDeSetoresVinculados = SQL::ini(ImGrupoQuery::atualizarGruposDeSetoresVinculados())::exec();
        $atualizarGruposAtivos = SQL::ini(ImGrupoQuery::atualizarGruposAtivosDeSetores())::exec();
        $atualizarGruposInativos = SQL::ini(ImGrupoQuery::atualizarGruposInativosDeSetores())::exec();
    }

    public static function buscarGruposDisponiveisParaVinculoNoEvento($idEvento, $getIdEmpresa, $autocomplete = false)
    {
        $grupos = SQL::ini(ImGrupoQuery::buscarGruposDisponiveisParaVinculoNoEvento(), [
            'getidempresa' => $getIdEmpresa,
            'idevento' => $idEvento
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        if($autocomplete)
		{
			$arrRetorno = [];

            foreach($grupos->data as $key => $grupo)
            {
                $arrRetorno[$key]['label'] = $grupo['grupo'];
                $arrRetorno[$key]['value'] = $grupo['idimgrupo'];
            }

            return $arrRetorno;
		}

        return $grupos->data;
    }

    public static function deletarPessoasQueNaoFacamParteDoGrupoDeConfiguracaoDeAlerta()
    {
        $imMsgConfDest = SQL::ini(ImMsgConfDestQuery::buscarPessoasQueNaoFacamParteDoGrupoDeConfiguracaoDeAlerta())::exec();

        $vimms = "";
        $vaimms = "";

        // while($rimms = mysqli_fetch_assoc($resimms)){
        foreach($imMsgConfDest->data as $imMsg)
        {
            $vaimms .= $vimms . $imMsg["id"];
            $vimms = ",";
        }

        if($vaimms)
        {
            $deletandoPorChavePrimaria = SQL::ini(ImMsgConfDestQuery::deletarPorChavePrimaria(), [
                'id' => $vaimms
            ])::exec();   

            echo $deletandoPorChavePrimaria->sql();
        }
    }

    public static function buscarGrupoPorIdObjetoExtETipoObjetoExt($idObjetoExt, $tipoObjetoExt)
    {
        $grupo = SQL::ini(ImGrupoQuery::buscarGruposPorIdObjetoExtETipoObjetoExt(), [
            'idobjetoext' => $idObjetoExt,
            'tipoobjetoext' => $tipoObjetoExt
        ])::exec();

        if($grupo->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupo->errorMessage());
            return [];
        }

        return $grupo->data[0];
    }

    public static function buscarGruposPorIdObjetoVincETipoObjetoVinc($idObjetoVinc, $tipoObjetoVinc)
    {
        $grupos = SQL::ini(ObjetoVinculoQuery::buscarGruposPorIdObjetoVincETipoObjetoVinc(), [
            'idobjetovinc' => $idObjetoVinc,
            'tipoobjetovinc' => $tipoObjetoVinc
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa($idobjetoVinc, $tipoObjetoVinc)
    {
        $gruposDisponiveisParaVinculo = SQL::ini(ImGrupoQuery::buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa(), [
            'idobjetovinc' => $idobjetoVinc,
            'tipoobjetovinc' => $tipoObjetoVinc,
            'getidempresa' => getidempresa('g.idempresa','imgrupo')
        ])::exec();

        if($gruposDisponiveisParaVinculo->error()){
            parent::error(__CLASS__, __FUNCTION__, $gruposDisponiveisParaVinculo->errorMessage());
            return [];
        }

        return $gruposDisponiveisParaVinculo->data;
    }

    public static function buscarFluxoEvento($idobjeto, $tipoobjeto)
    {
        $fluxoEvento = SQL::ini(FluxoObjetoQuery::buscarFluxoEvento(), [
            'tipoobjeto' => $tipoobjeto,
            'idobjeto' => $idobjeto
        ])::exec();

        if($fluxoEvento->error()){
            parent::error(__CLASS__, __FUNCTION__, $fluxoEvento->errorMessage());
            return [];
        }

        return $fluxoEvento->data;
    }

    public static function buscarCargosVinculadosAoSetor($idsgsetor){
        $cargos = SQL::ini(ObjetoVinculoQuery::buscarCargosVinculadosAoSetor(), [
            'idsgsetor' => $idsgsetor
        ])::exec();
        if($cargos->error()){
            parent::error(__CLASS__, __FUNCTION__, $cargos->errorMessage());
            return [];
        }   
        
        return $cargos->data;
    }
}

?>