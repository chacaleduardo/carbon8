<?
include_once(__DIR__ . "/_controller.php");
include_once(__DIR__ . "/../../form/querys/nf_query.php");
include_once(__DIR__ . "/../../form/querys/log_query.php");
include_once(__DIR__ . "/../../form/querys/lote_query.php");
include_once(__DIR__ . "/../../form/querys/empresa_query.php");
include_once(__DIR__ . "/../../form/querys/cotacao_query.php");
include_once(__DIR__ . "/../../form/querys/endereco_query.php");
include_once(__DIR__ . "/../../form/querys/resultado_query.php");
include_once(__DIR__ . "/../../form/querys/orcamento_query.php");
include_once(__DIR__ . "/../../form/querys/notafiscal_query.php");
include_once(__DIR__ . "/../../form/querys/contapagar_query.php");
include_once(__DIR__ . "/../../form/querys/pessoa_query.php.php");
include_once(__DIR__ . "/../../form/querys/empresaimagem_query.php");
include_once(__DIR__ . "/../../form/querys/pessoacontato_query.php");
include_once(__DIR__ . "/../../form/querys/empresaemails_query.php");
include_once(__DIR__ . "/../../form/querys/emailvirtualconf_query.php");
include_once(__DIR__ . "/../../form/querys/empresarodapeemail_query.php");
include_once(__DIR__ . "/../../form/querys/empresaemailobjeto_query.php");



class EnviaEmailGeralController extends ControllerCron{

    public static function inserirLog($idempresa,$sessao,$tipoobjeto,$idobjeto,$tipolog,$log,$status,$info,$criadoem,$data){
        $results = SQL::ini(LogQuery::inserirLog(), [          
            "idempresa" => $idempresa,
            "sessao" => $sessao,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "tipolog" => $tipolog,
            "log" => $log,
            "status" => $status,
            "info" => $info,
            "criadoem" => $criadoem,
            "data" => $data,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
            
        }
    }

    public static function inserirMailfila($idempresa,$remetente,$destinatario,$queueid,$status,$idobjeto,$tipoobjeto,$idsubtipoobjeto,$subtipoobjeto,$idpessoa,$idenvio,$enviadode,$link,$conteudoemail,$usuario){
        $results = SQL::ini(MailFilaQuery::inserirMailfila(), [          
            'idempresa' => $idempresa ,
            'remetente' => $remetente ,
            'destinatario' => $destinatario ,
            'queueid' => $queueid ,
            'status' => $status ,
            'idobjeto' => $idobjeto ,
            'tipoobjeto' => $tipoobjeto ,
            'idsubtipoobjeto' => $idsubtipoobjeto ,
            'subtipoobjeto' => $subtipoobjeto ,
            'idpessoa' => $idpessoa ,
            'idenvio' => $idenvio ,
            'enviadode' => $enviadode ,
            'link' => $link ,
            'conteudoemail' => $conteudoemail ,
            'usuario' => $usuario ,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
            
        }
    }

    public static function buscarCotacoesParaEnvio(){
        $results = SQL::ini(CotacaoQuery::buscarCotacoesParaEnvioDeEmail(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarCotacoesAprovadasParaEnvio(){
        $results = SQL::ini(CotacaoQuery::buscarCotacoesAprovadaParaEnvioDeEmail(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function atualizarNotaParaEnviado($idnf){
        $results = SQL::ini(NfQuery::atualizarNFParaEnviado(), [
            'idnf' =>$idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarEmailsDaNotaParaEnvio($idnf,$idempresa,$tipoenvio){

        $results = SQL::ini(EmailVirtualConfQuery::buscarEmailsParaEnvioDeEmailCotacao(), [
            'idnf' =>$idnf,
            'idempresa' =>$idempresa,
            'tipoenvio' =>$tipoenvio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarDominioEmpresaCotacao($idempresa,$tipoenvio){

        $results = SQL::ini(EmpresaEmailsQuery::buscarEmpresaemailCotacao(), [
            'idempresa' =>$idempresa,
            'tipoenvio' =>$tipoenvio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarDominioDaempresaPorTipoEnvio($idempresa,$tipoenvio){

        $results = SQL::ini(EmailVirtualConfQuery::buscarDominioDaempresaPorTipoEnvio(), [
            'idempresa' =>$idempresa,
            'tipoenvio' =>$tipoenvio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarInformacoesDaEmpresa($idempresa){

        $results = SQL::ini(EmpresaQuery::buscarPorChavePrimaria(), [
            'pkval' =>$idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarRodapePorTipoEnvio($idempresa,$tipoenvio){

        $results = SQL::ini(EmpresaRodapeEmailQuery::buscarRodapePorTipoEnvio(), [
            'idempresa' =>$idempresa,
            'tipoenvio' => $tipoenvio
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarPessoaAtivaPorUsuario($usuario){

        $results = SQL::ini(PessoaQuery::buscarPessoaAtivaPorUsuario(), [
            'usuario' =>$usuario
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function atualizarEnvioEmailOrcNF($idnf,$envioemailorc){

        $results = SQL::ini(NfQuery::atualizarEnvioEmailOrcParaO(), [
            'idnf' =>$idnf,
            'envioemailorc' => $envioemailorc,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function atualizarEnvioEmailAprovacaoNF($idnf,$emailaprovacao){

        $results = SQL::ini(NfQuery::atualizarEnvioEmailAprovacaoNF(), [
            'idnf' =>$idnf,
            'emailaprovacao' => $emailaprovacao,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarNotaFiscalParaEnvio(){

        $results = SQL::ini(NotaFiscalQuery::buscarNotaFiscalParaEnvioDetalhe(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarEmpresaEmailObjetoOriginalComIdobjeto($tipoenvio,$tipoobjeto,$idobjeto,$idempresa){

        $results = SQL::ini(EmpresaEmailObjetoQuery::buscarEmpresaEmailObjetoOriginalComIdobjeto(), [
            "tipoenvio" => $tipoenvio,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "idempresa" => $idempresa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function atualizarNotaFiscal($camposvalores,$idnotafiscal){

        $results = SQL::ini(NotaFiscalQuery::atualizarNotafiscal(), [
            'camposvalores' => $camposvalores,
            'idnotafiscal' => $idnotafiscal,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
            
        }
    }

    public static function buscarNFParaEnvioNFP(){

        $results = SQL::ini(NfQuery::buscarNFParaEnvioNFP(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function atualizarEnvioEmailNF($envioemail,$idnf){

        $results = SQL::ini(NfQuery::atualizarEnvioEmailNF(), [
            'envioemail' => $envioemail,
            'idnf' => $idnf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function atualizarEnvioEmailNfComLog($envioemail,$idnf,$msg){

        $results = SQL::ini(NfQuery::atualizarEnvioEmailNfComLog(), [
            'envioemail' => $envioemail,
            'idnf' => $idnf,
            'msg' => $msg,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function atualizarEnvioEmailOrcNfComLog($envioemail,$idnf,$msg){

        $results = SQL::ini(NfQuery::atualizarEnvioEmailOrcComLog(), [
            'envioemail' => $envioemail,
            'idnf' => $idnf,
            'msg' => $msg,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function atualizarEnvioEmailOrcamentoComLog($envioemail,$idorcamento,$msg){

        $results = SQL::ini(OrcamentoQuery::atualizarEnvioEmailOrcamentoComLog(), [
            'envioemail' => $envioemail,
            'idorcamento' => $idorcamento,
            'msg' => $msg,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarXMLParaEnvioDeEmail($idnf){

        $results = SQL::ini(NfQuery::buscarXMLParaEnvioDeEmail(), [
            'idnf' => $idnf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarEmpresaImagemPorTipo($idempresa,$tipo){

        $results = SQL::ini(EmpresaImagemQuery::buscarCaminhoImagemPorTipo(), [
            'idempresa' => $idempresa,
            'tipo' => $tipo,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarPessoasEEmailPorContatoETipoPessoa($idpessoa,$idtipopessoa){

        $results = SQL::ini(PessoaContatoQuery::buscarPessoasEEmailPorContatoETipoPessoa(), [
            'idpessoa' => $idpessoa,
            'idtipopessoa' => $idtipopessoa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarEnderecoDeEntregaParaEmaill($idendrotulo,$idpessoa){

        $results = SQL::ini(EnderecoQuery::buscarEnderecoDeEntregaParaEmail(), [
            'idendrotulo' => $idendrotulo,
            'idpessoa' => $idpessoa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

    public static function buscarFaturaBoletoPorNf($idnf){

        $results = SQL::ini(ContaPagarQuery::buscarFaturaBoletoPorNf(), [
            'idnf' => $idnf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarCertificadoDeAnaliseDosLotesParaEnvioDeEmail($idnf){

        $results = SQL::ini(LoteQuery::buscarCertificadoDeAnaliseDosLotesParaEnvioDeEmail(), [
            'idnf' => $idnf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarNFOrcamentoProdParaEnvioDeEmail(){

        $results = SQL::ini(NfQuery::buscarNFOrcamentoProdutoParaEnvioDeEmail(), [
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarOrcamentosParaEnvio(){

        $results = SQL::ini(OrcamentoQuery::buscarOrcamentosParaEnvioDeEmail(), [
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function atualizarEnvioEmailOrcamento($idorcamento,$envioemail){

        $results = SQL::ini(OrcamentoQuery::atualizarEnvioEmailOrcamento(), [
            "idorcamento" => $idorcamento,
            "envioemail" => $envioemail,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarResultadosOficiaisParaEnvioDeEmail(){

        $results = SQL::ini(ResultadoQuery::buscarResultadosOficiaisParaEnvioDeEmail(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarEmailsOficiaisPositivosPorSecretaria($idsecretaria){

        $results = SQL::ini(PessoaContatoQuery::buscarEmailsOficiaisPositivosPorSecretaria(), [
            'idsecretaria' =>$idsecretaria
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarEmailsOficiaisPorSecretaria($idsecretaria){

        $results = SQL::ini(PessoaContatoQuery::buscarEmailsOficiaisPorSecretaria(), [
            'idsecretaria' =>$idsecretaria
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarNFSParaEnvioDeEmail(){

        $results = SQL::ini(NotaFiscalQuery::buscarNFSParaEnvioDeEmail(), [
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function atualizarEnvioEmailNFENotafiscal(){

        $results = SQL::ini(NotaFiscalQuery::buscarNFSParaEnvioDeEmail(), [
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarFaturaBoletoPorNotaFiscal($idnotafiscal){

        $results = SQL::ini(ContaPagarQuery::buscarFaturaBoletoPorNotaFiscal(), [
            'idnotafiscal' => $idnotafiscal
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    public static function buscarResultadosParaEnvioContatoEmpresa($alterado_1,$alterado_2){

        $results = SQL::ini(ResultadoQuery::buscarResultadosParaEnvioContatoEmpresa(), [
            'alterado_1' => $alterado_1,
            'alterado_2' => $alterado_2
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarEmailsParaEnvioDeResultado($idpessoa,$idempresa,$tipores){

        if($tipores == "POS"){
            $results = SQL::ini(PessoaContatoQuery::buscarEmailsOficiaisPorIdpessoaIdempresa(), [
                'idpessoa' => $idpessoa,
                'idempresa' => $idempresa
            ])::exec();
        }else{
            $results = SQL::ini(PessoaContatoQuery::buscarEmailsOficiaisPorIdpessoaIdempresa2(), [
                'idpessoa' => $idpessoa,
                'idempresa' => $idempresa
            ])::exec();
        }

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
}