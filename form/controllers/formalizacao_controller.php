<?
// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/bioensaio_query.php");
require_once(__DIR__ . "/../querys/formalizacao_query.php");
require_once(__DIR__ . "/../querys/formalizacaosubtipo_query.php");

// CONTROLLERS
require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/_lp_controller.php");
require_once(__DIR__ . "/empresa_controller.php");
require_once(__DIR__ . "/lote_controller.php");
require_once(__DIR__ . "/pessoa_controller.php");
require_once(__DIR__ . "/prativ_controller.php");
require_once(__DIR__ . "/prodserv_controller.php");
require_once(__DIR__ . "/solfab_controller.php");
require_once(__DIR__ . "/tag_controller.php");

class FormalizacaoController extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarFormalizacoesPorSubTipo($subTipo, $data)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::buscarFormalizacoesPorSubTipo(), [
            'subtipo' => $subTipo,
            'data' => $data
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        }

        return $formalizacoes->data;
    }

    public static function buscarFormalizacaoPorIdLote($idlote)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::buscarFormalizacaoPorIdLote(), [
            'idlote' => $idlote
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
            return $formalizacoes->data[0];
        }
    }

    public static function buscarFormalizacaoPorlote($idsolfab)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::buscarFormalizacaoPorlote(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return "";
        } else {
            return $formalizacoes->data;
        }
    }

    public static function buscarFillSelectSubtipoFormalizacao()
    {
        $_listarsubtipo = SQL::ini(FormalizacaoSubTipoQuery::buscarFormalizacaoSubTipoPorShare(), [
            "share" => ""
        ])::exec();

        if ($_listarsubtipo->error()) {
            parent::error(__CLASS__, __FUNCTION__, $_listarsubtipo->errorMessage());
            return [];
        } else {
            $arrSubTipo = [];
            foreach ($_listarsubtipo->data as $subtipo) {
                $arrSubTipo[$subtipo['subtipo']] = $subtipo['descricao'];
            }
            return $arrSubTipo;
        }
    }

    public static function buscarClientesSolicitacaoFabricacao($_2_u_lote_idprodservformula, $_2_u_lote_idpessoa, $_1_u_formalizacao_status, $booArray = false)
    {
        //Recupera a listagem de Solicitações de fabricação de todos os clientes
        global $arrCli, $JSON;
        if (($_1_u_formalizacao_status == 'AGUARDANDO' || $_1_u_formalizacao_status == 'ABERTO') && !empty($_2_u_lote_idprodservformula) && !empty($_2_u_lote_idpessoa)) {
            $stridsf = SolfabController::listaSolfabCliente($_2_u_lote_idprodservformula, $_2_u_lote_idpessoa);
            $sqlin = " vs.idsolfab in ($stridsf)";
        } else {
            //Monta string com os clientes disponíveis
            $strCli = "";
            $virg = "";
            foreach ($arrCli as $k => $value) {
                $strCli .= $virg . $k;
                $virg = ",";
            }

            $sqlin = " vs.statussolfab IN ('ABERTO','APROVADO','UNIFICADO') AND vs.idpessoa in ($strCli)";
            $stridsf = "1";
        }

        if (!empty($stridsf)) {
            //Selecionar todas as solicitações de fabricação de todos os clientes
            $_listarSolfabPool = FormalizacaoController::buscarSolfabELotePool($sqlin);
            foreach ($_listarSolfabPool as $solfabPool) {
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["criadoem"] = $solfabPool["criadoem"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["rotulosolfab"] = $solfabPool["rotulosolfab"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["statussolfab"] = $solfabPool["statussolfab"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["exercicio"] = $solfabPool["exercicio"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["codprodserv"] = $solfabPool["codprodserv"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["idsolfabitem"] = $solfabPool["idsolfabitem"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["idlote"] = $solfabPool["idloteitem"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["partida"] = $solfabPool["rotuloloteitem"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["idpool"] = $solfabPool["idpool"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["ord"] = $solfabPool["ord"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["status"] = $solfabPool["statuslotesolfabitem"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["statussemente"] = $solfabPool["statussemente"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["situacao"] = $solfabPool["situacao"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["alerta"] = $solfabPool["alerta"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["flgalerta"] = $solfabPool["flgalerta"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["tipificacao"] = $solfabPool["tipificacao"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["orgao"] = $solfabPool["orgao"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["idprodserv"] = $solfabPool["idprodserv"];
                $arrret[$solfabPool["idpessoa"]][$solfabPool["idsolfab"]]["lotes"][$solfabPool["idloteitem"]]["codprodserv"] = $solfabPool["codprodserv"];
            }
        } else {
            $arrret = array();
        }

        return ($booArray) ? $arrret : $JSON->encode($arrret);
    }

    public static function buscarSolfabELotePool($sqlin)
    {
        return SolfabController::buscarSolfabELotePool($sqlin);
    }

    public static function buscarModulosPorModuloEIdLp($modulo, $idLps)
    {
        return _LpController::buscarModulosPorModuloEIdLp($modulo, $idLps);
    }

    public static function buscarLoteAtividade($idlote, $congelado = false, $modulo = NULL, $idloteativ =  null)
    {
        $_listarAtvidadesLote = self::buscarAtividadesLote($idlote, $idloteativ);
        foreach ($_listarAtvidadesLote as $_atividadesLote) {
            //Manter o objeto json (javascript) ordenado
            $idgrp = $_atividadesLote["loteimpressao"] . "#" . $_atividadesLote["idloteativ"];
            foreach ($_atividadesLote as $key => $valor) {
                $arrret[$idgrp][$key] = $valor;
            }

            $atividade = LoteController::buscarValorMaxAtividadePorIdLoteEStatus($idlote);
            $arrret[$idgrp]["idloteativConcluir"] = $atividade['idloteativ'];

            //LTM - 30-03-2021: Retorna as atividades que estão Ativas ou Pendentes para mostra-las na formalização
            $status = self::buscarFluxoHistoricoIdFormalizacao($_atividadesLote["idformalizacao"], $_atividadesLote["idloteativ"], $modulo);
            if (!empty($status['status'])) {
                $statusFormalizacao = $status['status'];
            } else {
                $statusFormalizacao = "SEMSTATUS";
            }
            $arrret[$idgrp]["statusFormalizacao"] = $statusFormalizacao;

            $arrret[$idgrp]["objetos"] = self::buscarPrAtivObjetos($_atividadesLote["idprativ"], $_atividadesLote["idloteativ"], $idlote, $congelado);
            $arrret[$idgrp]["amostrasRelacionadas"] = self::buscarAmostrasLoteAtiv($_atividadesLote["idloteativ"]);
            $arrret[$idgrp]["objetos"]["amostrasRelacionadasbioterio"] = self::buscarBioensaioLoteAtiv($_atividadesLote["idloteativ"]);
            $arrret[$idgrp]["tempogastoobrigatorio "] = $_atividadesLote['duracao'];
            $arrret[$idgrp]["duracao"] = substr($_atividadesLote['duracao'], 0, -3);
            $arrret[$idgrp]["tempoestimado"] = substr($_atividadesLote['tempoestimado'], 0, -3);
        }

        return $arrret;
    }

    public static function buscarAtividadesLote($idlote,    $idloteativ = null)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::buscarAtividadesLote(), [
            'idlote' => $idlote,
            'idloteativ' => $idloteativ != null ? 'and l.idloteativ in (' . $idloteativ . ')' : ''

        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
            return $formalizacoes->data;
        }
    }

    public static function buscarFluxoHistoricoIdFormalizacao($idformalizacao, $idloteativ, $modulo, $status = null)
    {
        if (!$modulo) {
            die("[Erro] Módulo vazio.");
        }

        if (!empty($status)) {
            $sqlStatus = " AND fh.status = 'PENDENTE'";
        }
        $fluxosStatusHist = SQL::ini(FormalizacaoQuery::buscarFluxoHistoricoIdFormalizacao(), [
            "idmodulo" => $idformalizacao,
            "modulo" => $modulo,
            "idloteativ" => $idloteativ,
            "sqlStatus" => $sqlStatus
        ])::exec();

        if ($fluxosStatusHist->error()) {
            parent::error(__CLASS__, __FUNCTION__, $fluxosStatusHist->errorMessage());
            return "";
        } else {
            return $fluxosStatusHist->data[0];
        }
    }

    //Recupera detalhes dos Objetos relacionados às atividades de cada grupo de atividades
    public static function buscarPrAtivObjetos($inIdPrAtiv, $inidloteativ, $inIdlote = NULL, $congelado = NULL)
    {
        if (empty($congelado) || empty($inIdlote)) {
            $_listarProcessos = PrativController::buscarObjetoPorIdPrativEIdObjetoEDescrNaoNulos($inIdPrAtiv);
        } else {
            $_listarProcessos = PrativController::buscarAtividadesLotePorIdLoteIdPrativEIdObjetoDescNaoNulos($inIdlote, $inIdPrAtiv);
        }

        if (count($_listarProcessos) == 0) {
            return [];
        } else {
            $arrret = [];
            //Sera colocado um prefixo 'o_' para não conflitar com colunas de tabelas que possuam o mesmo nome
            foreach ($_listarProcessos as $processos) {
                //Colunas padrão da prativobj
                $arrret[$processos["idprativobj"]]["o_idprativ"] = $inIdPrAtiv;
                $arrret[$processos["idprativobj"]]["o_idobjeto"] = $processos["idobjeto"];
                $arrret[$processos["idprativobj"]]["o_tipoobjeto"] = $processos["tipoobjeto"];
                $arrret[$processos["idprativobj"]]["o_descr"] = $processos["descr"];

                //MAF: Colocar no SQL as colunas necessárias para utilização
                if ($processos["tipoobjeto"] == "tagtipo") {
                    $_listarObjetos = TagController::buscarPorIdTagTipo($processos["idobjeto"]);
                    $_listarItens = PrativController::buscarAtividadesTagsPorIdEmpresaEIdPrativ($inIdPrAtiv, $processos["idobjeto"], $inIdlote);
                } elseif ($processos["tipoobjeto"] == "prodserv") {
                    $_listarObjetos = ProdServController::buscarProdutoPorIdProdserv($processos["idobjeto"]);
                    $_listarItens = false;
                } elseif ($processos["tipoobjeto"] == "ctrlproc") {
                    $_listarObjetos = PrativController::buscarObjetoPorIdPrativobj($processos["idprativobj"], 'prativobj');
                    $_listarItens = false;
                } elseif ($processos["tipoobjeto"] == "materiais") {
                    $_listarObjetos = PrativController::buscarObjetoPorIdPrativobj($processos["idprativobj"], 'materiais');
                    $_listarItens = false;
                } elseif ($processos["tipoobjeto"] == "prativopcao") {
                    $_listarObjetos = PrativController::buscarOpcaoPorIdPrativopcao($processos["idobjeto"]);
                    $_listarItens = false;
                }

                foreach ($_listarObjetos as $_listarObjetos) {
                    //para cada coluna resultante do select cria-se um item no array
                    foreach ($_listarObjetos as $key => $valor) {
                        $arrret[$processos["idprativobj"]][$key] = $valor;
                    }

                    //Recupera os subitens do objeto conforme sqldet
                    if ($_listarItens) {
                        $ii = 0;
                        foreach ($_listarItens as $itens) {
                            $ii++;
                            //para cada coluna resultante do select cria-se um item no array
                            foreach ($itens as $key => $valor) {
                                $arrret[$processos["idprativobj"]]["subitens"][$ii][$key] = $valor;
                            }
                        }
                    }
                }
            }
            return $arrret;
        }
    }

    function buscarAmostrasLoteAtiv($inIdloteativ)
    {
        $_listarAmostras = self::buscarAmostraResultadoVinculadosAtividade('resultado', $inIdloteativ, 'loteativ');
        $colid = "idtipoteste";
        foreach ($_listarAmostras as $amostras) {
            foreach ($amostras as $col => $value) {
                $arrret[$amostras[$colid]][$col] = $value;
            }
        }
        return $arrret;
    }

    public static function buscarAmostraResultadoVinculadosAtividade($tipoobjeto, $idobjetovinc, $tipoobjetovinc)
    {
        $formalizacoes = SQL::ini(ObjetoVinculoQuery::buscarAmostraResultadoVinculadosAtividade(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjetovinc" => $idobjetovinc,
            "tipoobjetovinc" => $tipoobjetovinc
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
            return $formalizacoes->data;
        }
    }

    public static function buscarBioensaioLoteAtiv($inIdloteativ)
    {
        $_listarBioensaioAtividade = self::buscarBioensaioPorAtividade($inIdloteativ);
        $arrret = [];
        $colid = "idresultado";
        foreach ($_listarBioensaioAtividade as $bioensaioAtividade) {
            foreach ($bioensaioAtividade as $key => $col) {
                $arrret[$bioensaioAtividade[$colid]][$key] = $col;
            }
        }
        return $arrret;
    }

    public static function buscarBioensaioPorAtividade($idloteativ)
    {
        $formalizacoes = SQL::ini(BioensaioQuery::buscarBioensaioPorAtividade(), [
            "idloteativ" => $idloteativ
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
            return $formalizacoes->data;
        }
    }

    public static function buscarPrimeiroFluxoTriagem($idformalizacao)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::buscarPrimeiroFluxoTriagem(), [
            "idformalizacao" => $idformalizacao
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
            return $formalizacoes->data[0];
        }
    }

    /*
     * Recupera todos os objetos utilizados/selecionados no lote
     * @todo: Verificar possibilidade de erro em caso de seleção da mesma combinação tipoobjeto+idobjeto
     *		  na mesma atividade. Neste caso agrupar por algum "tipo"
     */
    public static function buscarObjetosLote($inIdlote)
    {
        $arrret = [];
        $_listarObjetos = LoteController::buscarObjetosLote($inIdlote);
        $i = 0;
        foreach ($_listarObjetos as $objetos) {
            $i = $i + 1;
            //maf220617: idloteativ retirado do primeiro nivel. verificar necessidade de retorno
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["idobjeto"] = $objetos["idobjeto"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["tipoobjeto"] = $objetos["tipoobjeto"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["idloteobj"] = $objetos["idloteobj"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["idloteativ"] = $objetos["idloteativ"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["descr"] = $objetos["descr"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["qtd"] = $objetos["qtd"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["qtd_exp"] = $objetos["qtd_exp"];
            $arrret[$objetos["tipoobjeto"]][$objetos["idobjeto"]][$objetos["idloteativ"]]["ord"] = $objetos["ord"]; //@todo: Implementar ordenação na tela via js
        }
        return $arrret;
    }

    public static function buscarUnidadeObjetoPorModuloTipoEIdUnidade($tipoobjeto, $modulotipo, $idunidade)
    {
        return UnidadeController::buscarUnidadeObjetoPorModuloTipoEIdUnidade($tipoobjeto, $modulotipo, $idunidade);
    }

    public static function buscarQtdLoteAtivPorIdLote($idlote)
    {
        $qtdLoteAtiv = LoteController::buscarLoteAtivPorIdLote($idlote);
        return $qtdLoteAtiv['qtdLinhas'];
    }

    public static function buscarEnvioLoteReservaPorIdLote($idlote, $tipoobjeto)
    {
        return NfController::buscarEnvioLoteReservaPorIdLote($idlote, $tipoobjeto);
    }

    public static function buscarRotuloFormulaPorId($idprodservformula)
    {
        return ProdservController::buscarRotuloFormulaPorId($idprodservformula);
    }

    public static function buscarFormulaAtivaPorProdserv($idprodserv)
    {
        return ProdservFormulaController::buscarFormulaAtivaPorProdserv($idprodserv);
    }

    public static function buscarDataAprovacaoSolfab($ididsolfab)
    {
        return SolfabController::buscarDataAprovacaoSolfab($ididsolfab);
    }

    public static function buscarVolumeEQtdProdservFormula($idprodservformula)
    {
        $volume = ProdservFormulaController::buscarVolumeEQtdProdservFormula($idprodservformula);
        return $volume[0];
    }

    public static function buscarLotePorProdservFormula($idprodservformula, $idpessoa, $idsolfab)
    {
        return ProdservFormulaController::buscarLotePorProdservFormula($idprodservformula, $idpessoa, $idsolfab);
    }

    public static function buscarCaminhoImagemTipoHeaderProduto($idempresa)
    {
        return EmpresaController::buscarCaminhoImagemTipoHeaderProduto($idempresa);
    }

    public static function buscarSolfabPorIdLote($idempresa)
    {
        return LoteController::buscarSolfabPorIdLote($idempresa);
    }

    public static function buscarAssinaturaPessoa($status, $tipoobjeto, $idobjeto)
    {
        return PessoaController::buscarAssinaturaPessoa($status, $tipoobjeto, $idobjeto);
    }

    public static function buscarSolfabJoinLotePorIdSolfab($idsolfab)
    {
        return SolfabController::buscarSolfabJoinLotePorIdSolfab($idsolfab);
    }

    public static function buscarStatusSolfabPorIdSolfabEIdEmpresa($idsolfab)
    {
        return SolfabController::buscarStatusSolfabPorIdSolfabEIdEmpresa($idsolfab);
    }

    public static function apagarLoteConsRestauracaoPorIdLote($idlote)
    {
        return LoteController::apagarLoteConsRestauracaoPorIdLote($idlote);
    }

    public static function apagarLoteAtivPorIdLote($idlote)
    {
        return LoteController::apagarLoteAtivPorIdLote($idlote);
    }

    public static function deletarLoteObjPorLote($idlote)
    {
        return LoteController::deletarLoteObjPorLote($idlote);
    }

    public static function buscarEtapaLote($idlote)
    {
        return LoteController::buscarEtapaLote($idlote);
    }

    public static function excluirResultadosVinculadosFormalizacao($idlote)
    {
        $formalizacoes = SQL::ini(LoteQuery::excluirResultadosVinculadosFormalizacao(), [
            "idlote" => $idlote
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
        }
    }

    public static function atualizarLoteFormalizacao($idlote, $idformalizacao)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::atualizarLoteFormalizacao(), [
            "idlote" => $idlote,
            "idformalizacao" => $idformalizacao
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
        }
    }

    public static function gerarAtividadeLote($idlote, $idprodservformula)
    {
        LoteController::apagarLoteFormulaInsPorIdLote($idlote);
        LoteController::apagarLoteFormulaPorIdLote($idlote);
        PrativController::apagarObjetoPorIdLote($idlote);

        //congelar para busca posterior pela getArvoreInsumos()
        LoteController::inserirFormulaInsPorSelect(cb::idempresa(), $idlote, $_SESSION["SESSAO"]["USUARIO"], $idprodservformula);

        //congelar sementes
        LoteController::inserirFormulaInsSementes(cb::idempresa(), $idlote, $_SESSION["SESSAO"]["USUARIO"], $idprodservformula);

        //congelar para busca posterior na getPrativInsumo();
        LoteController::inserirLoteFormulaPorSelect(cb::idempresa(), $idlote, $_SESSION["SESSAO"]["USUARIO"]);

        //LTM (06/05/2021): Alterado para pegar a Atividade de acordo com o lote selecioinado
        $processo = ProdServController::buscarProcessoLigadoFormula($idprodservformula);
        $qtdProcesso = $processo['qtdLinhas'];
        $idprproc = $processo['dados']["idprproc"];
        if ($qtdProcesso == 0) {
            $servico = ProdServController::buscarQtdProcessoServico($idprodservformula);
            $qtdServico = $servico['qtdLinhas'];
            $idprproc = $servico['dados']["idprproc"];
            if ($qtdServico == 0) {
                echo "geraatividadelote: Erro: Produto sem processo vinculado ao produto no sistema. Verificar o cadastro do produto.";
                return false;
            }
        }

        //Recupera as atividades do grupo
        $listarAtividadesGrupo = PrProcController::buscarAtividadesGrupo($idprproc);
        foreach ($listarAtividadesGrupo as $atividadesGrupo) {
            $arrayAtividadeGrupo = [
                'idempresa' => cb::idempresa(),
                'idlote' => $idlote,
                'idprativ' => $atividadesGrupo['idprativ'],
                'ativ' => $atividadesGrupo['ativ'],
                'ord' => $atividadesGrupo['ordativ'],
                'dia' => empty($atividadesGrupo['dia']) ?  0 : $atividadesGrupo['dia'],
                'loteimpressao' => $atividadesGrupo['loteimpressao'],
                'statuslote' => $atividadesGrupo['statuspai'],
                'nomecurtoativ' => $atividadesGrupo['nomecurtoativ'],
                'bloquearstatus' => $atividadesGrupo['bloquearstatus'],
                'idprprocprativ' => $atividadesGrupo['idprprocprativ'],
                'duracao' => $atividadesGrupo['duracao'],
                'tempoestimado' => $atividadesGrupo['tempoestimado'],
                'tempogastoobrigatorio' => $atividadesGrupo['tempogastoobrigatorio'],
                'idfluxostatus' => $atividadesGrupo['idfluxostatus'],
                'idetapa' => $atividadesGrupo['idetapa'],
                'usuario' => $_SESSION['SESSAO']['USUARIO'],
            ];

            if (!empty($atividadesGrupo['idetapa'] && !empty($atividadesGrupo['idfluxostatus']))) {
                $idloteativ = LoteController::inserirAtividade($arrayAtividadeGrupo);
                PrativController::congelarAtividade($idlote, $atividadesGrupo['idprativ'], $idloteativ);
            } else {
                echo "Verificar se a Etapa ou Fluxo estão configurados corretamente na Atividade.";
                return false;
            }
        }

        //LTM (06/05/2021): Atualiza para inserir no formalizacao o idprproc para saber qual a atividade utlizada
        self::atualizarPrProcFormalizacaoPorIdLote($idlote, $idprproc);

        //HP 477936 (18/08/2021): Atualiza o rotulo da formula no lote
        $rotulo = ProdservformulaController::buscarRotuloFormulaPorId($idprodservformula);
        if (!empty($rotulo['rotulo'])) {
            LoteController::atualizarLoteRotuloForm($idlote, $rotulo['rotulo']);
        }

        return "OK";
    }

    public static function atualizarPrProcFormalizacaoPorIdLote($idlote, $idprproc)
    {
        $formalizacoes = SQL::ini(FormalizacaoQuery::atualizarPrProcFormalizacaoPorIdLote(), [
            "idlote" => $idlote,
            "idprproc" => $idprproc
        ])::exec();

        if ($formalizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
        }
    }

    public static function  atualizaLacreLote($idlote)
    {
        $results = SQL::ini(FormalizacaoQuery::atualizaLacreLote(), [
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return "OK";
        }
    }

    public static function buscarLoteAtivPorIdLote($idlote)
    {
        return LoteController::buscarLoteAtivPorIdLote($idlote);
    }

    public static function buscarIdProdservFormulaPorIdLote($idlote)
    {
        return LoteController::buscarIdProdservFormulaPorIdLote($idlote);
    }

    public static function buscarSalasParaReserva($idlote)
    {
        return LoteController::buscarSalasParaReserva($idlote);
    }

    public static function apagarSalasReserva($idlote)
    {
        LoteController::apagarSalasReserva($idlote);
    }

    public static function atualizarReservaSalaLoteFormalizacao($arrayAtualizaReserva)
    {
        TagController::atualizarReservaSalaLoteFormalizacao($arrayAtualizaReserva);
    }

    public static function inserirReservaSalaLoteFormalizacao($arrayInserirReserva)
    {
        return TagController::inserirReservaSalaLoteFormalizacao($arrayInserirReserva);
    }

    public static function atualizarDataExecucaoAtividade($idlote, $execucao)
    {
        return LoteController::atualizarDataExecucaoAtividade($idlote, $execucao);
    }

    public static function apagarAtividadeESalasReserva($idlote)
    {
        return LoteController::apagarAtividadeESalasReserva($idlote);
    }

    public static function buscarStatusPaiProcessoPorIdLote($idlote)
    {
        return LoteController::buscarStatusPaiProcessoPorIdLote($idlote);
    }

    public static function gerarAmostrasRelacionadasAoLote($idlote)
    {
        $listarTestesSelecionados = self::buscarTestesSelecionadosFormalizacao($idlote);
        $arrAtivAmostras = [];
        $arrConfAmostras = [];
        $subtipoamostra = [];
        foreach ($listarTestesSelecionados['dados'] as $testes) {
            $arrAtivAmostras[$testes["idloteativ"]][$testes["idamostra"]][$testes["idobjeto"]] = $testes["idobjeto"];
            if ($testes["tipo"] == 'PRODUTO') {
                $arrConfAmostras[$testes["idloteativ"]]["idpessoa"] = $testes["idpessoaform"];
            } else {
                $arrConfAmostras[$testes["idloteativ"]]["idpessoa"] = $testes["idpessoa"];
            }
            $arrConfAmostras[$testes["idloteativ"]]["idsubtipoamostra"] = $testes["idsubtipoamostra"];
            $arrConfAmostras[$testes["idloteativ"]]["descricao"] = $testes["descr"];
            $idempresaLote = $testes["idempresa"];
        }

        if ($listarTestesSelecionados['qtdLinhas'] > 0) {
            $unidade = UnidadeController::buscarUnidadePorIdtipoIdempresa(7, cb::idempresa());
            $idunidade = $unidade['idunidade'];
            if (empty($idunidade)) {
                die("A unidade de CQ da empresa não esta configurada para empresa.");
            }

            //LTM - 13-04-2021: Retorna o Idfluxo ContaPagar
            $idfluxostatusAmostra = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);
            $idfluxostatusResultado = FluxoController::getIdFluxoStatus('resultado', 'ABERTO', $idunidade);

            //Atividade: Criar somente 1 amostra para cada atividade. Caso exista, reutilizar
            foreach ($arrAtivAmostras as $idloteativ => $arramostra) {
                //Amostra: Caso nulo, criar nova amostra
                foreach ($arramostra as $idamostra => $arrprodserv) {
                    if (!in_array($arrConfAmostras[$idloteativ]["idsubtipoamostra"], $subtipoamostra)) {
                        if (empty($arrConfAmostras[$idloteativ]["idsubtipoamostra"])) {
                            $idsubtipoamostra = 49;
                        } else {
                            $idsubtipoamostra = $arrConfAmostras[$idloteativ]["idsubtipoamostra"];
                        }
                        if (empty($arrConfAmostras[$idloteativ]["idpessoa"])) {
                            die("Configurar no cadastro de empresa a empresa de ordem de produção.");
                        }

                        //Gerar nova amostra
                        $arrReg = geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"], $idunidade);
                        $arrayAmostra = [
                            "idpessoa" => $arrConfAmostras[$idloteativ]["idpessoa"], //INATA 3019
                            "descricao" => $arrConfAmostras[$idloteativ]["descricao"],
                            "idunidade" => $idunidade,
                            "status" => 'ABERTO',
                            "idfluxostatus" => $idfluxostatusAmostra,
                            "dataamostra" => sysdate(),
                            "idsubtipoamostra" => $idsubtipoamostra,
                            "lote" => $_SESSION['arrpostbuffer']['2']['u']['lote']['partida'] . "/" . $_SESSION['arrpostbuffer']['2']['u']['lote']['exercicio'],
                            "idempresa" => $idempresaLote,
                            "exercicio" => $arrReg["exercicio"],
                            "idregistro" => $arrReg["idregistro"],
                            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
                        ];
                        $idamostraOrig = self::inserirAmostraFormalizacao($arrayAmostra);

                        //LTM - 13-04-2021: Insere FluxoHist Amostra
                        $moduloAmostra = getModuloPadrao('amostra', $idunidade);
                        FluxoController::inserirFluxoStatusHist($moduloAmostra, $idamostraOrig, $idfluxostatusAmostra, 'PENDENTE');
                    }

                    //LTM: (08/07/2021) - Valida se o tipo de amostra do loteativ atual é diferente da anterior
                    $subtipoamostra[] = $arrConfAmostras[$idloteativ]["idsubtipoamostra"];

                    //Prodserv: testes que foram marcados na formalização mas não existem na tabela de resultados
                    foreach ($arrprodserv as $idprodserv => $val) {
                        //Gerar novo resultado
                        $arrayResulado = [
                            "idamostra" => $idamostraOrig,
                            "idtipoteste" => $idprodserv,
                            "quantidade" => 1,
                            "idempresa" => $idempresaLote,
                            "idfluxostatus" => $idfluxostatusAmostra,
                            "status" => 'ABERTO',
                            "idfluxostatus" => $idfluxostatusResultado,
                            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
                        ];

                        $idresultado = self::inserirResultadoFormalizacao($arrayResulado);

                        //LTM - 13-04-2021: Insere FluxoHist Resultado
                        $moduloResultado = getModuloPadrao('resultado', $idunidade);
                        FluxoController::inserirFluxoStatusHist($moduloResultado, $idresultado, $idfluxostatusResultado, 'PENDENTE');

                        //Insere as atividades no ObjetoVinculo com os Resultados
                        $arrayObjetoVinculo = [
                            "idobjeto" => $idresultado,
                            "tipoobjeto" => 'resultado',
                            "idobjetovinc" => $idloteativ,
                            "tipoobjetovinc" => 'loteativ',
                            "idfluxostatus" => $idfluxostatusAmostra,
                            "criadopor" => $_SESSION["SESSAO"]["USUARIO"],
                            "criadoem" => Date('Y-m-d H:i:s'),
                            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
                            "alteradoem" => Date('Y-m-d H:i:s')
                        ];
                        self::inserirObjetoVinculo($arrayObjetoVinculo);
                    }
                }
            }
        }
    }

    public static function buscarTestesSelecionadosFormalizacao($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarTestesSelecionadosFormalizacao(), [
            "idlote" => $idlote
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function inserirAmostraFormalizacao($arrayAmostra)
    {
        $results = SQL::ini(AmostraQuery::inserirAmostraFormalizacao(), $arrayAmostra)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirResultadoFormalizacao($arrayResulado)
    {
        $results = SQL::ini(ResultadoQuery::inserirResultadoFormalizacao(), $arrayResulado)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirObjetoVinculo($arrayObjetoVinculo)
    {
        $results = SQL::ini(ObjetoVinculoQuery::inserirObjetoVinculo(), $arrayObjetoVinculo)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarSolfabFormalizacao($idformalizacao)
    {
        $results = SQL::ini(FormalizacaoQuery::buscarSolfabFormalizacao(), [
            "idformalizacao" => $idformalizacao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarProdutoPorIdProdserv($idprodserv)
    {
        return ProdServController::buscarProdutoPorIdProdserv($idprodserv);
    }

    public static function atualizarPprodservFormulaLote($idlote, $idprodservformula)
    {
        LoteController::atualizarPprodservFormulaLote($idlote, $idprodservformula);
    }

    public static function buscarIdRegistroTitulacaoPorIdLote($idLote)
    {
        $idRegistros = SQL::ini(LoteAtivQuery::buscarIdRegistroTitulacaoPorIdLote(), ['idlote' => $idLote])::exec();

        if ($idRegistros->error()) {
            parent::error(__CLASS__, __FUNCTION__, $idRegistros->errorMessage());

            return [];
        }

        return $idRegistros->data;
    }

    public static function buscarIdRegistroInativacaoEsterialidadePorIdLote($idLote)
    {
        $idRegistros = SQL::ini(LoteAtivQuery::buscarIdRegistroInativacaoEsterialidadePorIdLote(), ['idlote' => $idLote])::exec();

        if ($idRegistros->error()) {
            parent::error(__CLASS__, __FUNCTION__, $idRegistros->errorMessage());

            return [];
        }

        return $idRegistros->data;
    }

    public static function buscarLotesInativarStatusFormalizacao($idformalizacao)
    {
        $results = SQL::ini(FormalizacaoQuery::buscarSolfabFormalizacao(), [
            "idformalizacao" => $idformalizacao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarConsumoProdutoRetornarEstoque($idLoteCons, $status)
    {
        return LoteController::buscarConsumoProdutoRetornarEstoque($idLoteCons, $status);
    }

    public static function atualizarStatusLoteCons($idLoteCons, $status)
    {
        LoteController::atualizarStatusLoteCons($idLoteCons, $status);
    }

    public static function buscarFluxoStatusLoteAtiv($idloteativ)
    {
        $results = SQL::ini(LoteAtivQuery::buscarFluxoStatusLoteAtiv(), [
            "idloteativ" => $idloteativ
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarTestesPorIdLoteAtiv($idLoteAtiv)
    {
        $testes = SQL::ini(ObjetoVinculoQuery::buscarTestesPorIdLoteAtiv(), [
            'idloteativ'  => $idLoteAtiv
        ])::exec();

        if ($testes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $testes->errorMessage());
            return [];
        }

        return $testes->data;
    }

    public static function atualizarStatusLoteAtiv($idLoteAtiv, $status, $idFluxoStatus)
    {
        $atualizandoLoteAtiv = SQL::ini(LoteAtivQuery::atualizarStatusLoteAtiv(), [
            'idloteativ' => $idLoteAtiv,
            'status' => $status,
            'idfluxostatus' => $idFluxoStatus
        ])::exec();

        if ($atualizandoLoteAtiv->error()) {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoLoteAtiv->errorMessage());
            return false;
        }

        return true;
    }

    public static function atualizarStatusFormalizacao($idFormalizacao, $status, $idFluxostatus)
    {
        $atualizandoFormalizacao = SQL::ini(FormalizacaoQuery::atualizarStatusFormalizacao(), [
            'idformalizacao' => $idFormalizacao,
            'status' => $status,
            'idfluxostatus' => $idFluxostatus
        ])::exec();

        if ($atualizandoFormalizacao->error()) {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoFormalizacao->errorMessage());
            return false;
        }

        return true;
    }

    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE ----	
    public static function listarEnderecoPessoaLote($idtipoendereco, $idlote)
    {
        return LoteController::listarEnderecoPessoaLote($idtipoendereco, $idlote);
    }

    public static function buscarPessoaObjetoAreaSetor($idpessoa, $tipoobjeto)
    {
        $_listarObjetoSetor = PessoaController::buscarPessoaObjetoAreaSetor($idpessoa, $tipoobjeto);
        $arrObjetoSetor = [];
        $i = 0;
        foreach ($_listarObjetoSetor as $objetoSetor) {
            $arrObjetoSetor[$i] = $objetoSetor['idsgareasetor'];
            $i++;
        }
        return $arrObjetoSetor;
    }

    public static function buscarPessoaPorStatusIdTipoPessoaEIdEmpresa($status, $idtipopessoa)
    {
        $_lsitarPessoa = PessoaController::buscarPessoaPorStatusIdTipoPessoaEIdEmpresa($status, $idtipopessoa);
        $arrPessoa = [];
        foreach ($_lsitarPessoa as $pessoa) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrPessoa[$pessoa["idpessoa"]]["nome"] = $pessoa["nome"];
            $arrPessoa[$pessoa["idpessoa"]]["centrocusto"] = $pessoa["centrocusto"];
        }
        return $arrPessoa;
    }

    public static function buscarConsumoLoteLoteconsLoteFracao($idobjeto, $tipoobjeto)
    {
        $_lsitarLote = LoteController::buscarConsumoLoteLoteconsLoteFracao($idobjeto, $tipoobjeto);
        $arrLote = [];
        foreach ($_lsitarLote as $key => $value) {
            $arrLote[$_lsitarLote["idlotefracao"]][$key] = $value;
        }
        return $arrLote;
    }

    public static function buscarProdutosFormalizacao($tipo)
    {
        $_listarProdutosFormalizacao = ProdServController::buscarProdutosFormalizacao($tipo);
        $arrProdutosFormalizacao = [];
        foreach ($_listarProdutosFormalizacao as $produtosFormalizacao) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrProdutosFormalizacao[$produtosFormalizacao["idprodserv"]]["descr"] = $produtosFormalizacao["descr"];
            $arrProdutosFormalizacao[$produtosFormalizacao["idprodserv"]]["codprodserv"] = $produtosFormalizacao["codprodserv"];
            $arrProdutosFormalizacao[$produtosFormalizacao["idprodserv"]]["qtdpadrao"] = $produtosFormalizacao["qtdpadrao"];
            $arrProdutosFormalizacao[$produtosFormalizacao["idprodserv"]]["qtdpadrao_exp"] = $produtosFormalizacao["qtdpadrao_exp"];
            $arrProdutosFormalizacao[$produtosFormalizacao["idprodserv"]]["formafarm"] = $produtosFormalizacao["formafarm"];
        }
        return $arrProdutosFormalizacao;
    }

    public static function buscarConsumoLoteProduto($inidlote, $incpde = '')
    {
        $unidade = UnidadeController::buscarIdunidadePorTipoUnidade(13, $_SESSION["SESSAO"]["IDEMPRESA"]);
        $idunidade = $unidade['idunidade'];

        if (empty($incpde) and !empty($idunidade)) { // quando não quiser o consumo do PeD
            $strped = " AND l.idunidade != " . $idunidade;
        } else {
            $strped = "";
        }

        $arrConsumo = [];
        $_listarConsumo = LoteController::buscarConsumoProduto($inidlote, 'lote', $strped);
        foreach ($_listarConsumo as $consumo) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["idprodserv"] = $consumo["idprodserv"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["partida"] = $consumo["partida"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["qtdd"] = $consumo["qtdd"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["qtdd_exp"] = $consumo["qtdd_exp"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["qtdpadrao"] = $consumo["qtdpadrao"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["qtdpadrao_exp"] = $consumo["qtdpadrao_exp"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["volumeformula"] = $consumo["volumeformula"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["volumeprod"] = $consumo["volumeprod"];
            $arrConsumo[$consumo["idprodserv"]][$consumo["idlotecons"]]["idprodservformula"] = $consumo["idprodservformula"];
        }

        $volumeconsf = 0;
        $strcalc = "";
        $arrConsumoLoteProduto = [];
        foreach ($arrConsumo as $v1) {
            foreach ($v1 as $v2) {
                if (strpos(strtolower($v2['qtdd_exp']), "d")) {
                    $arrExp = explode('d', strtolower($v2['qtdd_exp']));
                    $volumecons = $arrExp[0];
                } elseif (strpos(strtolower($v2['qtdd_exp']), "e")) {
                    $arrExp = explode('e', strtolower($v2['qtdd_exp']));
                    $volumecons = $arrExp[0];
                } else {
                    $volumecons = $v2['qtdd'];
                }

                if (strpos(strtolower($v2['qtdpadrao_exp']), "d")) {
                    $arrPad = explode('d', strtolower($v2['qtdpadrao_exp']));
                    $volumepradrao = $arrPad[0];
                } elseif (strpos(strtolower($v2['qtdpadrao_exp']), "e")) {
                    $arrPad = explode('e', strtolower($v2['qtdpadrao_exp']));
                    $volumepradrao = $arrPad[0];
                } else {
                    $volumepradrao = $v2['qtdpadrao'];
                }

                if ($v2['idprodservformula']) {
                    $volumeformula = $v2['volumeformula'];
                } else {
                    $volumeformula = $v2['volumeprod'];
                }
                if ($volumeformula > 0) {
                    if ($volumeformula < 1) {
                        $volumeformula = 0;
                    }
                    if ($volumepradrao < 1) {
                        $volumepradrao = 1;
                    }
                    $strcalc .= "[" . $volumeconsf . "+" . $volumecons . "*" . $volumeformula . "/" . $volumepradrao . "]";
                    $volumeconsf = $volumeconsf + (($volumecons * $volumeformula) / $volumepradrao);
                }
            }
        }

        $arrConsumoLoteProduto['consumo'] = $arrConsumo;
        $arrConsumoLoteProduto['strcalc'] = $strcalc;
        $arrConsumoLoteProduto['volumeconsf'] = $volumeconsf;

        return $arrConsumoLoteProduto;
    }

    //retorna os insumo inseridos no grupo de atividades na tela de produtos e serviços
    public static function buscarInsumoFormula($inidprodserv, $booArray = false, $idlote = NULL)
    {
        $arrret = [];
        if (!empty($idlote)) {
            $_listarInsumo = LoteController::buscarInsumoFormula($idlote);
        } else {
            $_listarInsumo = ProdservController::buscarInsumoPorIdProdserv($inidprodserv);
        }

        foreach ($_listarInsumo as $_insumos) {
            foreach ($_insumos as $key => $insumo) {
                $arrret[$_insumos["idprativ"]][$_insumos["idprodservformulains"]][$key] = $insumo;
            }
        }

        if ($booArray == false) {
            $json = new Services_JSON();
            $strJson = $json->encode($arrret);
            return $strJson;
        } else {
            return $arrret;
        }
    }

    public static function buscarResponsavelFormalizacao($idtipopessoa, $tipo, $status)
    {
        $_listarPessoa = PessoaController::listarFuncionarioPessoaPorIdtipoPessoa($idtipopessoa, $tipo, $status);
        $arrPessoa = [];
        foreach ($_listarPessoa as $pessoa) {
            $arrPessoa[$pessoa["idpessoa"]]["nome"] = $pessoa["nome"];
        }
        return $arrPessoa;
    }
    //----- AUTOCOMPLETE ----

    // ----- Variáveis de apoio -----
    public static $prioridade = array(
        "NORMAL" => "Normal",
        "URGENTECLIENTE" => "Urgente - Cliente",
        "URGENTEVETERINARIO" => "Urgente - Veterinário"
    );
    // ----- Variáveis de apoio -----
}
