<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");

require_once(__DIR__."/../querys/_iquery.php");

require_once(__DIR__."/../querys/nf_query.php");
require_once(__DIR__."/../querys/nfitem_query.php");
require_once(__DIR__."/../querys/nfvolume_query.php");
require_once(__DIR__."/../querys/contapagar_query.php");
require_once(__DIR__."/../querys/fluxostatus_query.php");
require_once(__DIR__."/../querys/contapagaritem_query.php");
require_once(__DIR__."/../querys/formapagamento_query.php");
require_once(__DIR__."/../querys/fluxostatushist_query.php");

require_once(__DIR__."/../../api/nf/index.php");

class NfVolumeController extends Controller{

    public static function buscarNfVolumePendente ( $idnf ) {
        $results = SQL::ini(NfVolumeQuery::buscarNfVolumePendente(), [
            'idnf' => $idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function verificarStatusEnviarNf ( $idnf ) {
        $results = SQL::ini(FluxoStatusHistQuery::verificarStatusEnviarNf(), [
            'idnf' => $idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return $results->data[0];
        }
    }

    public static function buscarStatusTipoEnviadoFluxo ( $idfluxo ) {
        $results = SQL::ini(FluxoStatusQuery::buscarStatusTipoEnviadoFluxo(), [
            'idfluxo' => $idfluxo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return $results->data[0];
        }
    }

    public static function atualizarStatusNfVolume ( $idNfVolumes, $idnf ) {
        $updateNfVolume = SQL::ini(NfVolumeQuery::atualizarStatusEnviadoNfVolume(), [
            'idnfvolumes' => $idNfVolumes
        ])::exec();

        if($updateNfVolume->error()){
            parent::error(__CLASS__, __FUNCTION__, $updateNfVolume->errorMessage());
            return false;
        }else{
            $updateNf = SQL::ini(NfQuery::atualizarEnvioEmailNf(), [
                'idnf' => $idnf,
                'envioemail' => 'Y'
            ])::exec();

            if($updateNf->error()){
                parent::error(__CLASS__, __FUNCTION__, $updateNf->errorMessage());
                return false;
            }

            return true;
        }
    }

    public static function gerarComissao ( $idnf, $idempresa ) {
        $arrNF = getObjeto("nf", $idnf, "idnf");

        $formapagamento = SQL::ini(FormaPagamentoQuery::buscarPorChavePrimaria(), [
            'pkval' => $arrNF['idformapagamento']
        ])::exec();

        if($formapagamento->error()){
            parent::error(__CLASS__, __FUNCTION__, $formapagamento->errorMessage());
            return false;
        }

        // Reduz o array para apenas um item
        $formapagamento = $formapagamento->data[0];

        $comissoes = SQL::ini(NfItemQuery::buscarComissaoNfPorPessoa(), [
            'idnf' => $idnf
        ])::exec();

        if($comissoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $comissoes->errorMessage());
            return false;
        }

        foreach( $comissoes->data as $k => $comissao ){
            if( $formapagamento['agrupado']=='Y' ){
                $parcelas = SQL::ini(ContaPagarItemQuery::buscarParcelaPorNf(), [
                    'idnf' => $idnf
                ])::exec();
            }else{
                $parcelas = SQL::ini(ContaPagarQuery::buscarFaturaNf(), [
                    'idnf' => $idnf
                ])::exec();
            }

            if($parcelas->error()){
                parent::error(__CLASS__, __FUNCTION__, $comissoes->errorMessage());
                return false;
            }

            $qtdparc = $parcelas->numRows();

            $valor = $comissao['comissao'] / $qtdparc;

            foreach ( $parcelas->data as $ch => $parcela ) {

                if(empty($parcela['proporcao'])){
                    $valor = $comissao['comissao'] / $qtdparc;
                }else{
                    $valor = $comissao['comissao'] * ($parcela['proporcao'] / 100);
                }
                
                $arrconfCP = cnf::getDadosConfContapagar('COMISSAO');

                $vdatapagto = $parcela['vdatapagto'];
                $resInsert = SQL::ini(ContaPagarItemQuery::inserirParcelaAbertaComissao(), [
                    'idempresa' => $idempresa, 
                    'idpessoa' => $comissao['idpessoa'], 
                    'idobjetoorigem' => $parcela['idcontapagar'], 
                    'idformapagamento' => $arrconfCP['idformapagamento'], 
                    'parcela' => $parcela['parcela'], 
                    'parcelas' => $parcela['parcelas'], 
                    'datapagto' => "'$vdatapagto'", 
                    'valor' => $valor, 
                    'visivel' => 'S',
                    'usuario' => $_SESSION["SESSAO"]["USUARIO"],
                    'status' => 'ABERTO',
                    'tipoobjetoorigem' => 'contapagar',
                    'tipo' => 'D'
                ])::exec();

                if($resInsert->error()){
                    parent::error(__CLASS__, __FUNCTION__, $resInsert->errorMessage());
                    return false;
                }
            }
        }

        return true;
    }
}
?>