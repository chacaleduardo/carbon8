<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/emailvirtualconf_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/assinaturaemailcampos_query.php");
require_once(__DIR__."/../querys/webmailassinaturatemplate_query.php");
require_once(__DIR__."/../querys/imgrupo_query.php");
require_once(__DIR__."/../querys/webmailassinatura_query.php");
require_once(__DIR__."/../querys/log_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class EmailVirtualConfController extends Controller
{
    public static $status = [
        'ATIVO' => 'ATIVO',
        'INATIVO' => 'INATIVO'
    ];

    public static $tipoDeEnvio = [
        'CC' => 'Cc',
        'CCO' => 'Cco'
    ];

    public static function buscarPessoasPorIdEmailVirtualConfEShare($idEmailVirtualConf, $share)
    {
        $pessoas = SQL::ini(PessoaQuery::buscarPessoasPorIdEmailVirtualConfEShare(), [
            'idemailvirtualconf' => $idEmailVirtualConf,
            'share' => $share
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function buscarPessoasPorIdEmailVirtualConf($idEmailVirtualConf)
    {
        $pessoas = SQL::ini(PessoaQuery::buscarPessoasPorIdEmailVirtualConf(), [
            'idemailvirtualconf' => $idEmailVirtualConf
        ])::exec();

        if($pessoas->error()){
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return [];
        }

        return $pessoas->data;
    }

    public static function buscarGruposDisponiveisParaVinculoPorIdEmailVirtualConf($idEmailVirtualConf)
    {
        $grupos = SQL::ini(EmailVirtualConfQuery::buscarGruposDisponiveisParaVinculoPorIdEmailVirtualConf(), [
            'idemailvirtualconf' => $idEmailVirtualConf
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarWebmailAssinaturaTemplateDisponiveisParaVinculoPorIdEmailVirtualConf($idEmailVirtualConf)
    {
        $emails = SQL::ini(WebmailAssinaturaTemplateQuery::buscarWebmailAssinaturaTemplateDisponiveisParaVinculoPorIdEmailVirtualConf(), [
            'idemailvirtualconf' => $idEmailVirtualConf
        ])::exec();

        if($emails->error()){
            parent::error(__CLASS__, __FUNCTION__, $emails->errorMessage());
            return [];
        }

        return $emails->data;
    }

    public static function buscarEmailDestinoPorEmail($emailsDeDestino, $idEmailVirtualConf)
    {
        $arrRetorno = [];
        $arrIdPessoas = [];

        // Separa os emails de destino em um array
        $emailsDeDestinoArr = explode(" ", $emailsDeDestino);

        // Loop para construção da tabela de emails de destino
        foreach($emailsDeDestinoArr as $key => $value)
        {  
            if(filter_var($value, FILTER_VALIDATE_EMAIL))
            {
                // Consulta o idpessoa e o nome curto da pessoa associada o nome do email
                // Realiza uma subquery para identificar as pessoas associadas de forma manual ou a partir de um grupo,
                // diferenciando-os por cor
                $emailDeDestino = SQL::ini(PessoaQuery::buscarEmailVirtualConfPorGetIdEmpresaIdEmailVirtualConfEEmailDestino(), [
                    'getidempresa' => getidempresa('e.idempresa','emailvirtualconfpessoa'),
                    'idemailvirtualconf' => $idEmailVirtualConf,
                    'emaildestino' => $value
                ])::exec();

                if($emailDeDestino->numRows())
                {
                    array_push($arrRetorno, [
                        'cor' => $emailDeDestino->data[0]['cor'],
                        'nomecurto' => $emailDeDestino->data[0]['nomecurto'],
                        'emaildestino' => $value
                    ]);

                    array_push($arrIdPessoas, $emailDeDestino->data[0]["idpessoa"]);
                }
            }
        }

        return [
            'emaildestino' => $arrRetorno,
            'idpessoas' => $arrIdPessoas
        ];
    }

    public static function buscarGruposVinculadosPorIdEmailVirtualConf($idEmailVirtualConf)
    {
        $grupos = SQL::ini(ImgrupoQuery::buscarGruposPorIdEmailVirtualConf(), [
            'idemailvirtualconf' => $idEmailVirtualConf
        ])::exec();

        if($grupos->error()){
            parent::error(__CLASS__, __FUNCTION__, $grupos->errorMessage());
            return [];
        }

        return $grupos->data;
    }

    public static function buscarAssinaturaEmailCamposPorIdEmailVirtualConf($idEmailVirtualConf)
    {
        $assinaturaEmailCampo = SQL::ini(AssinaturaEmailCamposQuery::buscarAssinaturaEmailCamposPorIdObjetoEGetIdEmpresa(), [
            'idobjeto' => $idEmailVirtualConf,
            'tipoobjeto' => 'EMAILVIRTUAL',
            'getidempresa' => getidempresa('idempresa','emailvirtualconf')
        ])::exec();

        if($assinaturaEmailCampo->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturaEmailCampo->errorMessage());
            return [];
        }

        return $assinaturaEmailCampo->data;
    }

    public static function buscarWebmailAssinaturaPorIdEmailVirtualConfEEmailOriginal($idEmailVirtualConf, $emailOriginal)
    {
        $assinaturas = SQL::ini(WebmailAssinaturaQuery::buscarWebmailAssinaturaPorIdEmailVirtualConfEEmailOriginal(), [
            'idemailvirtualconf' => $idEmailVirtualConf,
            'emailoriginal' => $emailOriginal
        ])::exec();

        if($assinaturas->error()){
            parent::error(__CLASS__, __FUNCTION__, $assinaturas->errorMessage());
            return [];
        }

        return $assinaturas->data;
    }

    public static function atualizarEmailsDeDestinoPorIdEmailVirtualConf($idEmailVirtualConf = false)
    {
        if(!$idEmailVirtualConf)
        {
            $emailsDeDestino = SQL::ini(EmailVirtualConfQuery::buscarEmailsDeDestinoPorGetIdEmpresa())::exec();
        } else 
        {
            $emailsDeDestino = SQL::ini(EmailVirtualConfQuery::buscarEmailsDeDestinoPorIdEmailVirtualConfEGetIdEmpresa(), [
                'idemailvirtualconf' => $idEmailVirtualConf
            ])::exec();
        }

        if($emailsDeDestino->error()){
            parent::error(__CLASS__, __FUNCTION__, $emailsDeDestino->errorMessage());
            return [];
        }

        if($emailsDeDestino->numRows())
        {
            foreach($emailsDeDestino->data as $email)
            {
                if($email['original'] and $email['emaildest'] and $email['idemailvirtualconf'])
                {
                    $atualizandoEmail = SQL::ini(EmailVirtualConfQuery::atualizarEmailOriginalEEmailDestinoPorIdEmailVirtualConf(), [
                        'idemailvirtualconf' => $email['idemailvirtualconf'],
                        'emailoriginal' => $email['original'],
                        'emaildestino' => $email['emaildest']
                    ])::exec();
                }
            }
        }
    }
}

?>