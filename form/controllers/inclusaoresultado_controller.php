<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/amostra_query.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/tag_query.php");
require_once(__DIR__ . "/../querys/resultado_query.php");
require_once(__DIR__ . "/../querys/fluxostatushist_query.php");
require_once(__DIR__ . "/../querys/pessoa_query.php");
require_once(__DIR__ . "/../querys/prodservtipoopcao_query.php");
require_once(__DIR__ . "/../querys/resultadoindividual_query.php");
require_once(__DIR__ . "/../querys/servicoensaio_query.php");
require_once(__DIR__ . "/../querys/prodservtipoalerta_query.php");
require_once(__DIR__ . "/../querys/especiefinalidade_query.php");
require_once(__DIR__ . "/../querys/identificador_query.php");
require_once(__DIR__ . "/../querys/resultadoelisa_query.php");
require_once(__DIR__ . "/../querys/_auditoria_query.php");
require_once(__DIR__ . "/../querys/resultadoassinatura_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/prodservformula_query.php");
require_once(__DIR__ . "/../querys/comunicacaoext_query.php");
require_once(__DIR__ . "/../querys/lotefracao_query.php");
require_once(__DIR__ . "/../querys/resultadoprodservformula_query.php");
require_once(__DIR__ . "/../querys/objetovinculo_query.php");
require_once(__DIR__ . "/../querys/mailfila_query.php");

//Controllers
require_once(__DIR__ . "/../controllers/prodserv_controller.php");

class InclusaoResultadoController extends Controller
{

    public static function buscarNomeAlertaERotulosServico($idresultado)
    {
        $results = SQL::ini(ProdservQuery::buscarNomeAlertaERotulosServico(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }


    public static function buscarServicosVinculados($idresultado)
    {
        $results = SQL::ini(ProdservQuery::buscarServicosVinculados(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarServicosDaAmostra($idamostra)
    {
        $results = SQL::ini(ResultadoQuery::buscarServicosDaAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return  $results->data;
        }
    }

    public static function buscarInfoFluxoResultado($modulo, $idresultado)
    {
        $results = SQL::ini(FluxoStatusHistQuery::buscarInfoFluxoResultado(), [
            'modulo' => $modulo,
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $infoFluxo['data'] = $results->data;
            $infoFluxo['numRows'] = $results->numRows();
            return $infoFluxo;
        }
    }

    public static function buscarSecretariaResultado($idempresa, $idsecretaria)
    {
        $results = SQL::ini(PessoaQuery::buscarSecretariaResultado(), [
            'idempresa' => $idempresa,
            'idsecretaria' => $idsecretaria
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarDadosAmostraCabecalhoModuloResultados($idamostra)
    {
        $results = SQL::ini(AmostraQuery::buscarDadosAmostraCabecalhoModuloResultados(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarJsonConfigJsonResultado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarJsonConfigJsonResultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarJsonConfigJsonResultadoCongelado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarJsonConfigJsonResultadoCongelado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return '';
        } else {
            return $results->data[0]['jresultado'];
        }
    }

    public static function buscarJsonConfigJsonResultadoCongeladoVersaoAnterior($idresultado, $versao)
    {
        $results = SQL::ini(ResultadoQuery::buscarJsonConfigJsonResultadoCongeladoVersaoAnterior(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return '';
        } else {
            if (is_numeric($versao)) {
                if ($versao > 0) {
                    $versao = $versao - 1;
                }
                return $results->data[$versao]['valor'];
            }
            return '';
        }
    }

    public static function buscarValorProdservTipoOpcao($idprodserv, $tratazero = false)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarValorProdservTipoOpcao(), [
            'idtipoteste' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $i = 1;
            foreach ($results->data as $key => $value) {
                if ($tratazero && $value['valor'] == '0.0') {
                    $rowi['valor'] = 0;
                }
                $x[$i] = $value["valor"];
                $i++;
            }
            return $x;
        }
    }
    public static function buscarValorProdservTipoOpcao2($idprodserv)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarValorProdservTipoOpcao(), [
            'idtipoteste' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarNumeroDelinhasResultadoIndividual($idresultado)
    {
        $results = SQL::ini(ResultadoindividualQuery::buscarNumeroDelinhasResultadoIndividual(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->numRows();
        }
    }


    public static function buscarIdentificacaoResultadoBioterio($idservicoensaio)
    {
        $results = SQL::ini(ServicoEnsaioQuery::buscarIdentificacaoResultadoBioterio(), [
            'idservicoensaio' => $idservicoensaio
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $row['data'] = $results->data;
            $row['numRows'] = $results->numRows();
            return $row;
        }
    }


    public static function buscarIdentificacaoResultado($idamostra)
    {
        $results = SQL::ini(ServicoEnsaioQuery::buscarIdentificacaoResultado(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $row['data'] = $results->data;
            $row['numRows'] = $results->numRows();
            return $row;
        }
    }

    public static function inserirResultadoIndividual($idempresa, $idresultado, $identificacao = null, $usuario)
    {
        $results = SQL::ini(ResultadoindividualQuery::inserirResultadoIndividual(), [
            'idempresa' => $idempresa,
            'idresultado' => $idresultado,
            'pesagem' => 'null',
            'tipoespecial' => 'null',
            'identificacao' => $identificacao,
            'resultado' => 'null',
            'valor' => 'null',
            'criadopor' => $usuario,
            'alteradopor' => $usuario,
            'ord' => 'null',
            'ordem_elisa' => 'null'
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function deletarResultadoIndividual($idresultado)
    {
        $results = SQL::ini(ResultadoindividualQuery::deletarResultadoIndividual(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarConfiguracaoAlerta($idtipoteste)
    {
        $results = SQL::ini(ProdservTipoAlertaQuery::buscarConfiguracaoAlerta(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if ($results->numRows() > 0) {
                return parent::toFillSelect($results->data);
            } else {
                return [
                    'MG' => 'MG',
                    'MS' => 'MS',
                    'SE' => 'SE',
                    'SG' => 'SG',
                    'SP' => 'SP',
                    'ST' => 'ST',
                    'SPP' => 'SPP',
                    'HI POSITIVO' => 'HI POSITIVO-1,4[5],12:i:-'
                ];
            }
        }
    }

    public static function buscarConfiguracaoAgente($idtipoteste)
    {
        $results = SQL::ini(ProdservTipoAlertaQuery::buscarConfiguracaoAgente(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if ($results->numRows() > 0) {
                return parent::toFillSelect($results->data);
            } else {
                return true;
            }
        }
    }


    public static function buscarResultadoIndividual($idresultado)
    {
        $results = SQL::ini(ResultadoindividualQuery::buscarResultadoIndividual(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarValorProdservTipoOpcaoResultado($idtipoteste)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarValorProdservTipoOpcaoResultado(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }


    public static function buscarEspecieAmostra($idamostra)
    {
        $results = SQL::ini(EspecieFinalidadeQuery::buscarEspecieAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdentificacaoAmostra($idamostra)
    {
        $results = SQL::ini(identificadorQuery::buscarIdentificacaoAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarJsonConfigServico($idtipoteste)
    {
        $results = SQL::ini(ProdservQuery::buscarJsonConfigServico(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0]['jsonconfig'];
        }
    }


    public static function buscarResultadosDeArquivoUploadEliza($idresultado)
    {
        $results = SQL::ini(ResultadoElisaQuery::buscarResultadosDeArquivoUploadEliza(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarNomeArquivoElisaUpload($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarNomeArquivoElisaUpload(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarSementesGeradasResultado($idresultado)
    {
        $results = SQL::ini(LoteQuery::buscarSementesGeradasResultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results;
        }
    }

    public static function buscarInsumosFormulaResultado($idtipoteste)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarInsumosFormulaResultado(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarInsumosServicoConcluido($idresultado, $idprodservformula)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarInsumosServicoConcluido(), [
            'idresultado' => $idresultado,
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarInsumosServicoEmAndamento($idtipoteste, $idprodservformula, $satusFormula, $statusFormulaIns)
    {
        return ProdServController::buscarInsumosServicoEmAndamento($idtipoteste, $idprodservformula, $satusFormula, $statusFormulaIns);
    }

    public static function verificarSeExisteRegistroNaTableaResultadoProdserFormula($idresultado)
    {
        $results = SQL::ini(ResultadoProdservFormulaQuery::verificarSeExisteRegistroNaTableaResultadoProdserFormula(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0]['nresult'];
        }
    }

    public static function insertResultadoProdservFormula($idempresa, $idresultado, $idprodservformula, $usuario)
    {
        $results = SQL::ini(ResultadoProdservFormulaQuery::insertResultadoProdservFormula(), [
            'idempresa' => $idempresa,
            'idresultado' => $idresultado,
            'idprodservformula' => $idprodservformula,
            'criadopor' => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarRegistroProdservFormulaServico($idresultado, $idprodservformula)
    {
        $results = SQL::ini(ResultadoProdservFormulaQuery::buscarRegistroProdservFormulaServico(), [
            'idresultado' => $idresultado,
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLotesConsumoInsumoResultado($idresultado, $unidadepadrao, $idprodserv, $idprodservformula)
    {
        $results = SQL::ini(LoteQuery::buscarLotesConsumoInsumoResultado(), [
            'idresultado' => $idresultado,
            'unidadepadrao' => $unidadepadrao,
            'idprodserv' => $idprodserv,
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarAtribuicoesDeLotesResultado($idresultado, $unidadepadrao, $idprodserv, $idprodservformula)
    {
        $results = SQL::ini(LoteQuery::buscarAtribuicoesDeLotesResultado(), [
            'idresultado' => $idresultado,
            'unidadepadrao' => $unidadepadrao,
            'idprodserv' => $idprodserv,
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarAgentesVinculadosProdserv($idprodserv)
    {
        $results = SQL::ini(ObjetoVinculoQuery::buscarAgentesVinculadosProdserv(), [
            'idtipoteste' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarAgentesVinculados()
    {
        $results = SQL::ini(ObjetoVinculoQuery::buscarAgentesVinculados(), [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }


    public static function buscarObjetoVinculoProdservServico($idprodserv)
    {
        $results = SQL::ini(ObjetoVinculoQuery::buscarObjetoVinculoProdservServico(), [
            'idtipoteste' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if ($results->numRows() > 0) {

                return self::buscarAgentesVinculadosProdserv($idprodserv);
            } else {

                return self::buscarAgentesVinculados();
            }
        }
    }


    public static function buscarServicosDaUnidade($unidadepadrao)
    {
        $results = SQL::ini(ProdservQuery::buscarServicosDaUnidade(), [
            'unidadepadrao' => $unidadepadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCampoDescritivo($idtipoteste)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarCampoDescritivo(), [
            'idtipoteste' => $idtipoteste
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function verificarIdPessoaParaAssinatura($idpessoa)
    {
        $results = SQL::ini(ProdservQuery::buscarServicosDaUnidade(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function buscarTiposDeEnvioDeEmailResultado($idresultado)
    {
        $results = SQL::ini(ComunicacaoExtQuery::buscarComunicacaoExtResultado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarUltimoMailfilaResultadoPorTpo($idresultado, $tipo)
    {
        $results = SQL::ini(MailFilaQuery::buscarMailFilaResultadoPorTipo(), [
            'idresultado' => $idresultado,
            'tipoemail' => $tipo,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }


    public static function buscarUltimoMailfilaResultado($idresultado, $getIdempresa)
    {
        $results = SQL::ini(MailFilaQuery::buscarMailFilaResultado(), [
            'idresultado' => $idresultado,
            'getidempresa' => $getIdempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarDataconclusao($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarDataconclusao(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function verficaAssinateste($idpessoa)
    {
        $results = SQL::ini(PessoaQuery::verficaAssinateste(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
    public static function verificaSeResultadoJaFoiAssinado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarResultadoAssinado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function verificarSeResultadoEInata($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::verificarSeResultadoEInata(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            if ($results->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function atualizarInterfrasePorIdresultado($frase, $idresultado)
    {
        $results = SQL::ini(ResultadoQuery::atualizarInterfrasePorIdresultado(), [
            'frase' => $frase,
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function inserirERetornarResultadoassinatura($idempresa, $idresultado, $idpessoa)
    {
        $results = SQL::ini(ResultadoAssinaturaQuery::inserirResultadoassinatura(), [
            'idempresa' => $idempresa,
            'idresultado' => $idresultado,
            'idpessoa' => $idpessoa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $results1 = SQL::ini(ResultadoAssinaturaQuery::buscarPorChavePrimaria(), [
                'pkval' => $results->lastInsertId(),
            ])::exec();
            if ($results1->error()) {
                parent::error(__CLASS__, __FUNCTION__, $results1->errorMessage());
                return [];
            } else {
                return $results1->data[0];
            }
        }
    }

    public static function InserirAssinaturaResultado($idempresa, $idresultado, $idpessoa, $usuario, $assinatura)
    {
        $results = SQL::ini(CarimboQuery::InserirAssinaturaResultado(), [
            'idempresa' => $idempresa,
            'idresultado' => $idresultado,
            'idpessoa' => $idpessoa,
            'usuario' => $usuario,
            'assinatura' => $assinatura,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function atualizarResultadoParaAssinado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::atualizarResultadoParaAssinado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function atualizarComunicacaoExtResultadosOficiais($idresultado)
    {
        $results = SQL::ini(ComunicacaoExtQuery::buscarComunicacaoExtResultadoSucesso(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {

            foreach ($results->data as $key => $value) {
                $results1 = SQL::ini(ComunicacaoExtQuery::atualizarComunicacaoExtParaReenvio(), [
                    'idcomunicacaoext' => $value['idcomunicacaoext'],
                ])::exec();
                if ($results1->error()) {
                    parent::error(__CLASS__, __FUNCTION__, $results1->errorMessage());
                    return false;
                }
            }

            return true;
        }
    }

    public static function atualizarEmailsecResultado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::atualizarEmailsecResultado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function deletarResultadoAssinaturaPorIdresultado($idresultado)
    {
        $results = SQL::ini(ResultadoAssinaturaQuery::deletarResultadoAssinaturaPorIdresultado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarResultadoAssinaturaPorIdresultado($idresultado)
    {
        $results = SQL::ini(ResultadoAssinaturaQuery::buscarResultadoAssinaturaPorIdresultado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return $results->numRows();
        }
    }

    public static function deletarFluxostatushist($idresultado, $modulo, $idfluxostatus)
    {
        $results = SQL::ini(FluxoStatusHistQuery::deletarFluxostatushist(), [
            'idresultado' => $idresultado,
            'modulo' => $modulo,
            'idfluxostatusass' => $idfluxostatus,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function atualizarResultadoParaFechado($idresultado, $idfluxostatus)
    {
        $results = SQL::ini(ResultadoQuery::atualizarResultadoParaFechado(), [
            'idresultado' => $idresultado,
            'idfluxostatus' => $idfluxostatus,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarResultadoAssinado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarResultadoAssinado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function deletarResultadoAssinaturaPorIdresultadoComAmostra($idresultado)
    {
        $results = SQL::ini(ResultadoAssinaturaQuery::deletarResultadoAssinaturaPorIdresultadoComAmostra(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function inserirLogAuditoria($idempresa, $linha, $acao, $objeto, $idobjeto, $coluna, $valor, $usuario, $HTTP_REFERER)
    {
        $results = SQL::ini(AuditoriaQuery::inserirRegistroAuditoria(), [
            'idempresa' => $idempresa,
            'linha' => $linha,
            'acao' => $acao,
            'objeto' => $objeto,
            'idobjeto' => $idobjeto,
            'coluna' => $coluna,
            'valor' => $valor,
            'usuario' => $usuario,
            'HTTP_REFERER' => $HTTP_REFERER,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarIformacoesProdservPorIdtipoteste($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarIformacoesProdservPorIdtipoteste(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarValorProdservTipoOpcaoPorValorEIdprodserv($valor, $idprodserv)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::buscarValorProdservTipoOpcaoPorValorEIdprodserv(), [
            'valor' => $valor,
            'idprodserv' => $idprodserv,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarProdservPorIdresultado($idresultado)
    {
        $results = SQL::ini(ProdservQuery::buscarProdservPorIdresultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarValidadeSemente($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarValidadeProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarQtdLote($idlotefracao)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarQtdLoteFracao(), [
            'idlotefracao' => $idlotefracao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarAmostraPorIdResultado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarAmostraPorIdResultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarResultadoIndividualPorIdresultado($idresultado)
    {
        $results = SQL::ini(ResultadoindividualQuery::buscarResultadoIndividualPorIdresultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }


    public static function buscarCobrancaResultado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarCobrancaResultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarTagsVinculadasAoTesteAgrupado($idprodserv, $idresultado = null)
    {

        $arrTags = array();
        $tagSala = SQL::ini(ProdservQuery::buscarTagSalaETagTipoVinculoAgrupado(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($tagSala->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagSala->errorMessage());
            return [];
        } else {
            foreach ($tagSala->data as $key => $value) {
                if ($idresultado != null && !empty($value['idobjetovinc'])) {
                    $tipoTag = SQL::ini(TagQuery::buscarTagsVinculadasAoResultadoPorTagTipoEPai(), [
                        "idtagtipo" => $value['idobjetovinc'],
                        "idtagpai" => $value['idtag'],
                        "idresultado" => $idresultado,
                        "orderby" => "order by t.idtagtipo asc, descr asc"
                    ])::exec();

                    if ($tipoTag->error()) {
                        parent::error(__CLASS__, __FUNCTION__, $tipoTag->errorMessage());
                        return [];
                    } else {
                        $arrTags[$value['descricao']] = $tipoTag->data;
                    }
                } else {
                    $tipoTag = SQL::ini(TagQuery::buscarTagsPorTagTipoEPai(), [
                        "idtagtipo" => $value['idobjetovinc'],
                        "idtagpai" => $value['idtag'],
                        "orderby" => "order by t.idtagtipo asc, descr asc"
                    ])::exec();

                    if ($tipoTag->error()) {
                        parent::error(__CLASS__, __FUNCTION__, $tipoTag->errorMessage());
                        return [];
                    } else {
                        $arrTags[$value['descricao']] = $tipoTag->data;
                    }
                }

                $tagsVinculadas = [];

                if ($tipoTag)
                    $tagsVinculadas = ProdServController::buscarVinculosTipoTagPorIdProdserv($idprodserv, $value['idobjetovinc'], implode(',', array_map(function ($item) {
                        return $item['idtag'];
                    }, $tipoTag->data)));

                $arrTags[$value['descricao']]['tags'] = $tipoTag->data;
                $arrTags[$value['descricao']]['tagsVinculadas'] = [];
                if ($tipoTag->data && $tagsVinculadas)
                    $arrTags[$value['descricao']]['tagsVinculadas'] = $tagsVinculadas;
            }
        }

        return $arrTags;
    }

    public static function exigeConferenciaAmostra($idamostra)
    {
        return AmostraController::exigeConferenciaAmostra($idamostra);
    }

    public static function buscarRazaoSocialPorId($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarRazaoSocial(), [
            'idlote' => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    
    public static function buscarInformacoesResultadoPorIdResultado($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarInformacoesResultadoPorIdResultado(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarPlantelPorIdResultado($idResultado) {
        $planteis = SQL::ini(ResultadoQuery::buscarPlantelPorIdResultado(), [
            'idresultado' => $idResultado
        ])::exec();

        if ($planteis->error()) {
            parent::error(__CLASS__, __FUNCTION__, $planteis->errorMessage());
            return [];
        }

        return $planteis->data;
    }


    public static function buscarCustoTeste($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarCustoTeste(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    

    public static function atualizarCustoIdresultado($custo, $idresultado)
    {
        $results = SQL::ini(ResultadoQuery::atualizarCustoIdresultado(), [
            'custo' => $custo,
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarTipoUnidadeAmostra($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscarTipoUnidadeAmostra(), [
            'idresultado' => $idresultado
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function UpdateStatusResultado($idresultado)
    {

		$results = SQL::ini(ResultadoQuery::updateStatusResultado($idresultado), [
			'idresultado' => $idresultado
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		}

		return true;
    }


	public static function BuscaCategoriaIdtipoteste($idresultado)
    {
        $results = SQL::ini(ResultadoQuery::buscaCategoriaIdtipoteste(), [
            'idresultado' => $idresultado
            ])::exec();
            
            if ($results->error()) {
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return "";
            } else {
                return $results->data[0];
            }

	}

    public static function BuscarConformidadeResultado($idlote)
    {
        $results = SQL::ini(ResultadoQuery::buscarConformidadeResultado(), [
            'idlote' => $idlote
        ])::exec();
        
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function BuscaModuloPorIdunidadeFormalizacao($idtabela, $idunidade, $modulotipo)
    {
        $results = SQL::ini(ResultadoQuery::buscaModuloPorIdunidadeFormalizacao(), [
            'idtabela' => $idtabela,
            'idunidade' => $idunidade,
            'modulotipo' => $modulotipo
        ])::exec();
        
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }
    public static function BuscaModuloPorIdunidadeLote($idtabela, $idunidade, $modulotipo)
    {
        $results = SQL::ini(ResultadoQuery::BuscaModuloPorIdunidadeLote(), [
            'idtabela' => $idtabela,
            'idunidade' => $idunidade,
            'modulotipo' => $modulotipo
        ])::exec();
        
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }
}
