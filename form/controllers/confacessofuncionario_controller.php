<?

require_once("_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/tag_query.php");
require_once(__DIR__."/../querys/dominio_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");
require_once(__DIR__."/../querys/sgconselho_query.php");
require_once(__DIR__."/../querys/sgarea_query.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/sgsetor_query.php");
require_once(__DIR__."/../querys/immsgconf_query.php");
require_once(__DIR__."/../querys/immsgconfdest_query.php");
require_once(__DIR__."/../querys/fluxoobjeto_query.php");
require_once(__DIR__."/../querys/emailvirtualconf_query.php");
require_once(__DIR__."/../querys/assinaturaemailcampos_query.php");
require_once(__DIR__."/../querys/webmailassinatura_query.php");
require_once(__DIR__."/../querys/webmailassinaturaobjeto_query.php");
require_once(__DIR__."/../querys/webmailassinaturatemplate_query.php");
require_once(__DIR__."/../querys/novocertificadodigital_query.php");

class ConfAcessoFuncionarioController extends Controller
{
    public static function buscarAlertasPorIdPessoa($idPessoa)
    {
        $alertas = SQL::ini(ImMsgConfDestQuery::buscarImMsgConfDestPorIdobjeto(), [
            'objeto' => 'pessoa',
            'idobjeto' => $idPessoa
        ])::exec();

        if($alertas->error()){
            parent::error(__CLASS__, __FUNCTION__, $alertas->errorMessage());
            return [];
        }

        return $alertas->data;
    }

    public static function buscarFluxosPorIdPessoa($idPessoa)
    {
        $fluxos = SQL::ini(FluxoObjetoQuery::buscarPorIdobjetoETipoObjeto(),[
            'idobjeto' => $idPessoa,
            'tipoobjeto' => 'pessoa'
        ])::exec();

        if($fluxos->error()){
            parent::error(__CLASS__, __FUNCTION__, $fluxos->errorMessage());
            return [];
        }

        return $fluxos->data;
    }

    public static function buscarEmailVirtualPorIdPessoa($idPessoa)
    {
        $emails = SQL::ini(EmailVirtualConfQuery::buscarEmailVirtualConfPorIdPessoaEmail(), [
            "getidempresa" => getidempresa('idempresa', 'emailvirtualconf'),
            "idpessoaemail" => $idPessoa
        ])::exec();

        if($emails->error()){
            parent::error(__CLASS__, __FUNCTION__, $emails->errorMessage());
            return [];
        }

        return $emails->data;
    }

    public static function buscarGrupoDeAssinatura($idPessoa, $emailDestino)
    {
        $grupoDeAssinatura = SQL::ini(EmailVirtualConfQuery::buscarGrupoDeAssinaturaPorEmailDestinoComNotExistsPorIdPessoa(), [
            'idpessoa' => $idPessoa,
            'emaildestino' => $emailDestino
        ])::exec();

        if($grupoDeAssinatura->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupoDeAssinatura->errorMessage());
            return [];
        }

        return $grupoDeAssinatura->data;
    }

    public static function buscarWebmailAssinaturaFuncionario($idPessoa, $webmail)
    {
        $webmailAssinatura = SQL::ini(WebmailAssinaturaQuery::buscarWebmailAssinaturaPorWebMail(), [
            'idpessoa' => $idPessoa,
            'webmail' => $webmail
        ])::exec();

        if($webmailAssinatura->error()){
            parent::error(__CLASS__, __FUNCTION__, $webmailAssinatura->errorMessage());
            return [];
        }

        return $webmailAssinatura->data;
    }

    public static function buscarOutrasAssinaturasDosFuncionarios($idPessoa, $webMail)
    {
        $assinaturas = SQL::ini(WebmailAssinaturaTemplateQuery::buscarWebmailAssinaturaTemplateSemVincPorIdPessoa(), [
            'idpessoa' => $idPessoa,
            'webmail' => $webMail
        ])::exec();

        
        if($assinaturas->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturas->errorMessage());
            return [];
        }

        return $assinaturas->data;
    }

    public static function buscarEmailVirtual($idPessoa, $emailVirtual, $usuario)
    {
        $arrRetorno = [];
        $emailVirtual = SELF::buscarEmailVirtualPorIdPessoa($idPessoa);
    
        $email_principal = explode("@", $emailVirtual);

        $queryAuxiliar = "AND dominio NOT IN ('{$email_principal[1]}','";


        foreach($emailVirtual as $email) {
            $auxiliar = explode("@", $email["email_original"]);
            $queryAuxiliar .= $auxiliar[1] . "','";
        }
    
        $queryAuxiliar = substr($queryAuxiliar, 0, -1);
        $queryAuxiliar = substr($queryAuxiliar, 0, -1);
        $queryAuxiliar .= ")";
    
        $result = SQL::ini(DominioQuery::buscarDominiosComExcesaoDeEmails(), [
            'getidempresa' => getidempresa('idempresa', 'dominio'),
            'queryauxiliar' => $queryAuxiliar
        ])::exec()->data;

        foreach($result as $key => $item)
        {
            $arrRetorno[$key]["label"] = "$usuario@{$item['dominio']}";
        }

        return $arrRetorno;
    }

    public static function buscarNovoCertificadoDigitalPorIdPessoa($idPessoa)
    {
        $certificadoDigital = SQL::ini(NovoCertificadoDigitalQuery::buscarNovoCertificadoDigitalPorIdObjeto(), [
            'idobjeto' => $idPessoa,
            'objeto'   => 'pessoa'
        ])::exec();

        if($certificadoDigital->error()){
            parent::error(__CLASS__, __FUNCTION__, $certificadoDigital->errorMessage());
            return [];
        }

        if(!$certificadoDigital->numRows())
        {
            return [];
        }
        
        $certificadoDigital->data[0]['id'] = $certificadoDigital->data[0]['idnovocertificadodigital'];

        return $certificadoDigital->data[0];
    }

    public static function buscarHistoricoPorIdPessoa($idPessoa)
    {
        $historico = SQL::ini(PessoaQuery::buscarHistoricoConselhoAreaDepartamentoSetorPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($historico->error()){
            parent::error(__CLASS__, __FUNCTION__, $historico->errorMessage());
            return [];
        }

        return $historico->data;
    }

    public static function  buscarImMsgConf()
    {
        $imMsgConf = SQL::ini(ImMsgConfQuery::buscarImMsgConfComTipoDiferenteDe(), [
            'tipo' => "'E','EP','ET'"
        ])::exec();

        if($imMsgConf->error()){
            parent::error(__CLASS__, __FUNCTION__, $imMsgConf->errorMessage());
            return [];
        }

        return $imMsgConf->data;
    }

    public static function buscarAreasDeAtuacaoPorIdPessoa($idPessoa)
    {
        $arrRetorno = [];
        $i = 0;

        $areasDeAtuacao = SQL::ini(PessoaobjetoQuery::buscarPessoaObjetoPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($areasDeAtuacao->error()){
            parent::error(__CLASS__, __FUNCTION__, $areasDeAtuacao->errorMessage());
            return [];
        }

        foreach($areasDeAtuacao->data as $key => $area)
        {
            //Valida qual tipo Objeto, buscando o nome e id correspondente
            // Conselho
            if($area['tipoobjeto'] == 'sgconselho')
            {
                $sgConselho = SQL::ini(SgConselhoQuery::buscarSgConselhoPorIdSgConselho(), [
                    'idsgconselho' => $area['idobjeto']
                ])::exec();

                if($sgConselho->numRows())
                {
                    $arrRetorno[$i] = [
                        'idpessoaobjeto' => $area['idpessoaobjeto'],
                        'descricao' => $sgConselho->data[0]['conselho'],
                        'link' => "sgconselho&_acao=u&idsgconselho={$sgConselho->data[0]['idsgconselho']}",
                        'objeto' => 'CONSELHO'
                    ];

                    $i++;
                }
            }

            // Area
            if($area['tipoobjeto'] == 'sgarea')
            {
                $sgArea = SQL::ini(SgAreaQuery::buscarPorChavePrimaria(), [
                    'pkval' => $area['idobjeto']
                ])::exec();

                if($sgArea->numRows())
                {
                    $arrRetorno[$i] = [
                        'idpessoaobjeto' => $area['idpessoaobjeto'],
                        'descricao' => $sgArea->data[0]['area'],
                        'link' => "sgarea&_acao=u&idsgarea={$sgArea->data[0]['idsgarea']}",
                        'objeto' => 'ÁREA'
                    ];

                    $i++;
                }
            }

            // Departamento
            if($area['tipoobjeto'] == 'sgdepartamento')
            {
                $sgDepartamento = SQL::ini(SgDepartamentoQuery::buscarSgDepartamentoPorIdSgDepartamento(), [
                    'idsgdepartamento' => $area['idobjeto']
                ])::exec();

                if($sgDepartamento->numRows())
                {
                    $arrRetorno[$i] = [
                        'idpessoaobjeto' => $$area['idpessoaobjeto'],
                        'descricao' => $sgDepartamento->data[0]['departamento'],
                        'link' => "sgdepartamento&_acao=u&idsgdepartamento={$sgDepartamento->data[0]['idsgdepartamento']}",
                        'objeto' => 'DEPARTAMENTO'
                    ];

                    $i++;
                }
            }

            // Setor
            if($area['tipoobjeto'] == 'sgsetor')
            {
                $sgSetor = SQL::ini(SgsetorQuery::buscarPorChavePrimaria(), [
                    'table' => 'sgsetor',
                    'pk' => 'idsgsetor',
                    'pkval' => $area['idobjeto'],
                    'status' => 'ATIVO'
                ])::exec();

                if($sgSetor->numRows())
                {
                    $arrRetorno[$i] = [
                        'idpessoaobjeto' => $area['idpessoaobjeto'],
                        'descricao' => $sgSetor->data[0]['setor'],
                        'link' => "sgsetor&_acao=u&idsgsetor={$sgSetor->data[0]['idsgsetor']}",
                        'objeto' => 'SETOR'
                    ];

                    $i++;
                }
            }
        }

        return $arrRetorno;
    }

    public static function buscarAssinaturaEmailCamposPorIdPessoa($idPessoa)
    {
        $assinaturaEmailCampos = SQL::ini(AssinaturaEmailCamposQuery::buscarAssinaturaEmailCamposPorIdObjeto(), [
            'idobjeto' => $idPessoa,
            'tipoobjeto' => 'COLABORADOR'
        ])::exec();

        if($assinaturaEmailCampos->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturaEmailCampos->errorMessage());
            return [];
        }

        return $assinaturaEmailCampos->data;
    }

    public static function buscarEmailTemplateDoTipoColaborador($idPessoa, $webMailEmail)
    {
        $templates = SQL::ini(WebmailAssinaturaQuery::buscarTemplatesColaboradorPorIdPessoaEWebMailEmail(), [
            'idpessoa' => $idPessoa,
            'webmailemail' => $webMailEmail
        ])::exec();

        if($templates->error()){
            parent::error(__CLASS__, __FUNCTION__, $templates->errorMessage());
            return [];
        }

        return $templates->data;
    }

    public static function buscarDominios()
    {
        $dominios = SQL::ini(DominioQuery::buscarDominioPorGetIdEmpresa(), [
            'getidempresa' => getidempresa('idempresa', 'dominio')
        ])::exec();

        if($dominios->error()){
            parent::error(__CLASS__, __FUNCTION__, $dominios->errorMessage());
            return [];
        }

        return $dominios->data;
    }

    public static function buscarLpsPorIdPessoa($idPessoa)
    {
        $lps = SQL::ini(PessoaobjetoQuery::buscarLpPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($lps->error()){
            parent::error(__CLASS__, __FUNCTION__, $lps->errorMessage());
            return [];
        }

        return $lps->data;
    }

    public static function buscarAssinaturaDeGruposDeEmailPorIdPessoa($idPessoa)
    {
        $arrRetorno = [];

        $assinaturas = SQL::ini(WebmailAssinaturaObjeto::buscarAssinaturaDeGruposDeEmailPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($assinaturas->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturas->errorMessage());
            return [];
        }

        foreach($assinaturas->data as $assinatura)
        {
            $arrRetorno[$assinatura['idwebmailassinaturaobjeto']] = $assinatura;
        }

        return $arrRetorno;
    }

    public static function buscarAssinaturaDeFuncionariosRelacionados($idPessoa, $webMail)
    {
        $arrRetorno = [];
        $assinaturas = SQL::ini(WebmailAssinaturaQuery::buscarAssinaturaDeFuncionariosRelacionadosPorIdPessoaEWebMail(), [
            'idpessoa' => $idPessoa,
            'webmail' => $webMail
        ])::exec();

        if($assinaturas->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturas->errorMessage());
            return [];
        }

        foreach($assinaturas->data as $assinatura)
        {
            $arrRetorno[$assinatura['idwebmailassinaturaobjeto']] = $assinatura;
        }

        return $arrRetorno;
    }

    public static function buscarTagsVinculadasAoFuncionario($idPessoa)
    {
        $tags = SQL::ini(TagQuery::buscarTagPorIdPessoa(), [
            'idpessoa' => $idPessoa
        ])::exec();

        if($tags->error()){
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }
}

?>