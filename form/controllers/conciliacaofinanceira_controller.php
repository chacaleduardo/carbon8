<?
// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/arquivo_query.php");
require_once(__DIR__ . "/../querys/conciliacaofinanceira_query.php");

// CONTROLLERS
require_once(__DIR__ . "/_controller.php");

class ConciliacaoFinanceiraController extends Controller
{
    public static function buscarArquivoPorTipoObjetoEIdObjeto($idobjeto, $tipoobjeto, $todos = false)
    {
        $arquivo = SQL::ini(ArquivoQuery::buscarArquivoPorTipoObjetoEIdObjeto(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto
        ])::exec();

        if ($arquivo->error()) {
            parent::error(__CLASS__, __FUNCTION__, $arquivo->errorMessage());
            return [];
        }

        if ($todos) return $arquivo->data;

        return $arquivo->data[0];
    }

    public static function inserirLancamentos($idConciliacaoFinanceira, $lancamentos, $idEmpresa)
    {
        $dadosInsercao = [];

        foreach ($lancamentos as $datas) {
            foreach ($datas as $data) {
                array_push($dadosInsercao, [
                    'idconciliacaofinanceiraitem' => strpos($data->id, 'id') !== false ? 'null' : $data->id,
                    'idconciliacaofinanceira' => $idConciliacaoFinanceira,
                    'dataemissaofatura' => $data->dataEmissaoFatura ? validadate($data->dataEmissaoFatura) : null,
                    'descricaofatura' => $data->descricaoFatura,
                    'totalfatura' => $data->totalFatura,
                    'dataemissaosistema' => $data->dataEmissaoSistema ? validadate($data->dataEmissaoSistema) : null,
                    'descricaosistema' => $data->descricaoSistema,
                    'totalsistema' => $data->totalSistema,
                    'status' => $data->status,
                    'idcontapagaritem' => $data->idcontapagaritem ?? null,
                    'idnf' => $data->idnf ?? null,
                    'idcontapagar' => $data->idcontapagar ?? null,
                    'idpai' => $data->idPai,
                    'idgrupo' => $data->idGrupo,
                    'indiceorigem' => $data->indiceOrigem,
                    'idempresa' => $idEmpresa,
                    'porcentagem' => $data->porcentagem ?? 0.0,
                    'match' => $data->match ? 1 : 0,
                    'pai' => $data->pai ? 1 : 0
                ]);

                if (count($data->filhos)) {
                    foreach ($data->filhos as $lancamentoFilho) {
                        array_push($dadosInsercao, [
                            'idconciliacaofinanceiraitem' => strpos($lancamentoFilho->id, 'id') !== false ? 'null' : $lancamentoFilho->id,
                            'idconciliacaofinanceira' => $idConciliacaoFinanceira,
                            'dataemissaofatura' => $lancamentoFilho->dataEmissaoFatura ? validadate($lancamentoFilho->dataEmissaoFatura) : null,
                            'descricaofatura' => $lancamentoFilho->descricaoFatura,
                            'totalfatura' => $lancamentoFilho->totalFatura,
                            'dataemissaosistema' => $lancamentoFilho->dataEmissaoSistema ? validadate($lancamentoFilho->dataEmissaoSistema) : null,
                            'descricaosistema' => $lancamentoFilho->descricaoSistema,
                            'totalsistema' => $lancamentoFilho->totalSistema,
                            'status' => $lancamentoFilho->status,
                            'idcontapagaritem' => $lancamentoFilho->idcontapagaritem ?? null,
                            'idnf' => $data->idnf ?? null,
                            'idcontapagar' => $lancamentoFilho->idcontapagar ?? null,
                            'idpai' => $lancamentoFilho->idPai,
                            'idgrupo' => $lancamentoFilho->idGrupo,
                            'indiceorigem' => $lancamentoFilho->indiceOrigem,
                            'idempresa' => $idEmpresa,
                            'porcentagem' => $lancamentoFilho->porcentagem ?? 0.0,
                            'match' => $lancamentoFilho->match ? 1 : 0,
                            'pai' => $lancamentoFilho->pai ? 1 : 0
                        ]);
                    }
                }
            }
        }

        $sqlInsercao = '';

        if ($dadosInsercao) {
            $sqlInsercao = 'INSERT INTO conciliacaofinanceiraitem (
                                        idconciliacaofinanceiraitem, 
                                        idconciliacaofinanceira, 
                                        idempresa, 
                                        dataemissaofatura, 
                                        descricaofatura,
                                        totalfatura,
                                        dataemissaosistema, 
                                        descricaosistema,
                                        totalsistema,
                                        `status`,
                                        idcontapagaritem,
                                        idnf,
                                        idcontapagar,
                                        idpai,
                                        idgrupo,
                                        indiceorigem,
                                        porcentagem,
                                        `match`,
                                        pai,
                                        criadopor,
                                        criadoem,
                                        alteradopor,
                                        alteradoem
                                    ) VALUES ';
            $virgula = '';

            foreach ($dadosInsercao as $dado) {
                $sqlInsercao .= "$virgula (
                        {$dado['idconciliacaofinanceiraitem']}, 
                        $idConciliacaoFinanceira, 
                        {$dado['idempresa']}, 
                        " . ($dado['dataemissaofatura'] ? "'" . $dado['dataemissaofatura'] . "'" : "null") . ", 
                        '" . addslashes($dado['descricaofatura'] ?? "") . "', 
                        " . ($dado['totalfatura'] ?? 0.0) . ", 
                        " . ($dado['dataemissaosistema'] ? "'" . $dado['dataemissaosistema'] . "'" : "null") . ", 
                        '" . addslashes($dado['descricaosistema'] ?? "") . "', 
                        " . ($dado['totalsistema'] ?? 0.0) . ", 
                        '" . ($dado['status'] ?? "") . "', 
                        " . ($dado['idcontapagaritem'] ?? "null") . ", 
                        " . ($dado['idnf'] ?? "null") . ", 
                        " . ($dado['idcontapagar'] ?? "null") . ", 
                        " . ($dado['idpai'] ?? "null") . ", 
                        " . ($dado['idgrupo'] ? "'" . $dado['idgrupo'] . "'" : "null") . ", 
                        " . ($dado['indiceorigem'] ?? "null") . ", 
                        {$dado['porcentagem']}, 
                        {$dado['match']},
                        {$dado['pai']},
                        '" . $_SESSION['SESSAO']['USUARIO'] . "',
                        now(),
                        '" . $_SESSION['SESSAO']['USUARIO'] . "',
                        now())";

                if (!$virgula)
                    $virgula = ',';
            }

            $sqlInsercao .= "as ins 
                            ON DUPLICATE KEY UPDATE 
                            -- idconciliacaofinanceira = ins.idconciliacaofinanceira,
                            idempresa = ins.idempresa,
                            dataemissaofatura = ins.dataemissaofatura,
                            descricaofatura = ins.descricaofatura,
                            totalfatura = ins.totalfatura,
                            dataemissaosistema = ins.dataemissaosistema,
                            descricaosistema = ins.descricaosistema,
                            totalsistema = ins.totalsistema,
                            `status` = ins.status,
                            idcontapagaritem = ins.idcontapagaritem,
                            idnf = ins.idnf,
                            idcontapagar = ins.idcontapagar,
                            idpai = ins.idpai,
                            idgrupo = ins.idgrupo,
                            indiceorigem = ins.indiceorigem,
                            porcentagem = ins.porcentagem,
                            `match` = ins.match,
                            pai = ins.pai,
                            criadopor = ins.criadopor,
                            criadoem = ins.criadoem,
                            alteradopor = ins.alteradopor,
                            alteradoem = ins.alteradoem";
        }

        if ($sqlInsercao) {
            $insercaoLancamentos = SQL::ini($sqlInsercao)::exec();
        }

        // Verificar lancamentos que foram conciliados
        // self::inserirLancamentosConciliados($dadosInsercaoVinculo, $idEmpresa);

        if ($insercaoLancamentos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $insercaoLancamentos->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarLancamentosPorIdConciliacaoFinanceira($idConciliacaoFinanceira, $idEmpresa)
    {
        $lancamentos = SQL::ini(ConciliacaoFinanceiraQuery::buscarLancamentosPorIdConciliacaoFinanceira(), [
            'idempresa' => $idEmpresa,
            'idconciliacaofinanceira' => $idConciliacaoFinanceira
        ])::exec();

        if ($lancamentos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $lancamentos->errorMessage());
            return [];
        }

        $lancamentosFormatados = [];

        foreach ($lancamentos->data as $lancamento) {
            $dataFormatada = $lancamento['dataEmissaoFatura'] ? dma($lancamento['dataEmissaoFatura']) : dma($lancamento['dataEmissaoSistema']);
            $lancamento['dataEmissaoFatura'] =  $lancamento['dataEmissaoFatura'] ? dma($lancamento['dataEmissaoFatura']) : '';
            $lancamento['dataEmissaoSistema'] = $lancamento['dataEmissaoSistema'] ? dma($lancamento['dataEmissaoSistema']) : '';

            $lancamento['match'] = $lancamento['match'] == '1';
            $lancamento['pai'] = $lancamento['pai'] == '1';

            if (!$lancamentosFormatados[$dataFormatada])
                $lancamentosFormatados[$dataFormatada] = [];

            // Buscando filhos do lancamento
            $lancamento['filhos'] = array_filter($lancamentos->data, function ($item) use ($lancamento) {
                return $item['idPai'] == $lancamento['id'];
            });

            $lancamento['filhos'] = array_map(function ($item) {
                $itemAtualizado = $item;

                $itemAtualizado['match'] = $item['match'] == '1';
                $itemAtualizado['pai'] = $item['pai'] == '1';
                $itemAtualizado['dataEmissaoFatura'] = dma($itemAtualizado['dataEmissaoFatura']);
                $itemAtualizado['dataEmissaoSistema'] = dma($itemAtualizado['dataEmissaoSistema']);

                return $itemAtualizado;
            }, $lancamento['filhos']);

            if (!$lancamento['idPai'])
                array_push($lancamentosFormatados[$dataFormatada], $lancamento);
        }

        return $lancamentosFormatados;
    }

    public static function inserirLancamentosConciliados($lancamentos, $idempresa)
    {
        if (!$lancamentos) return false;

        $insercaoVinculoLancamentos = 'INSERT INTO conciliacaofinanceiravinculo (idlancamentofatura, idlancamentosistema, idempresa) VALUES ';

        $virgula = '';

        foreach ($lancamentos as $lancamento) {
            $insercaoVinculoLancamentos .= "$virgula ({$lancamento['idlancamentofatura']}, {$lancamento['idlancamentosistema']}, $idempresa)";

            if (!$virgula) {
                $virgula = ',';
            }
        }

        $insercaoVinculoLancamentos .= " as ins 
                                ON DUPLICATE KEY UPDATE 
                                idlancamentofatura = ins.idlancamentofatura,
                                idlancamentosistema = ins.idlancamentosistema,
                                idempresa = ins.idempresa
                                ";

        $inserindoVinculoLancamentos = SQL::ini($insercaoVinculoLancamentos)::exec();

        if ($inserindoVinculoLancamentos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $inserindoVinculoLancamentos->errorMessage());
            return false;
        }

        return true;
    }

    public static function removerLancamentos($idLancamentos)
    {
        $removendoLancamentos = SQL::ini(ConciliacaoFinanceiraQuery::removerLancamentos(), [
            'idconciliacaofinanceiraitem' => $idLancamentos
        ])::exec();

        if ($removendoLancamentos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $removendoLancamentos->errorMessage());
            return false;
        }

        return true;
    }

    public static function removerLancamentosPorIdConciliacaoFinanceira($idConciliacaoFinanceira)
    {
        $removendoConciliacaoFinanceira = SQL::ini(ConciliacaoFinanceiraQuery::removerLancamentosPorIdConciliacaoFinanceira(), [
            'idconciliacaofinanceira' => $idConciliacaoFinanceira
        ])::exec();

        if ($removendoConciliacaoFinanceira->error()) {
            parent::error(__CLASS__, __FUNCTION__, $removendoConciliacaoFinanceira->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarConciliacaoFinananceiraPorIdContaPagar($idContaPagar)
    {
        $conciliacaoFinanceira = SQL::ini(ConciliacaoFinanceiraQuery::buscarConciliacaoFinananceiraPorIdContaPagar(), [
            'idcontapagar' => $idContaPagar
        ])::exec();

        if ($conciliacaoFinanceira->error()) {
            parent::error(__CLASS__, __FUNCTION__, $conciliacaoFinanceira->errorMessage());
            return [];
        }

        return $conciliacaoFinanceira->data[0];
    }

    public static function adicionarLancamentoPeloComprasApp($idConciliacaoFinanceira, $idContaPagar, $idContaPagarItem, $dataEmissao, $descricao, $valor, $status, $idempresa)
    {
        $inserindoLancamento = SQL::ini(ConciliacaoFinanceiraQuery::adicionarLancamentoPeloComprasApp(), [
            'idconciliacaofinanceira' => $idConciliacaoFinanceira,
            'idcontapagar' => $idContaPagar,
            'idcontapagaritem' => $idContaPagarItem,
            'dataemissaosistema' => $dataEmissao,
            'descricaosistema' => addslashes($descricao),
            'totalsistema' => floatval($valor),
            'idempresa' => $idempresa,
            'status' => $status,
            'criadopor' => $_SESSION['SESSAO']['USUARIO'],
            'criadoem' => 'now()',
            'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
            'alteradoem' => 'now()'
        ])::exec();

        if ($inserindoLancamento->error()) {
            parent::error(__CLASS__, __FUNCTION__, $inserindoLancamento->errorMessage());
            return false;
        }

        return true;
    }

    public static function verificarSeExisteConciliacaoPorIdContaItem($idContaPagar, $idConciliacaoFinanceira = false)
    {
        $clausula = '';

        if ($idConciliacaoFinanceira)
            $clausula = "AND idconciliacaofinanceira != $idConciliacaoFinanceira";

        $conciliacaoFinanceira = SQL::ini(ConciliacaoFinanceiraQuery::verificarSeExisteConciliacaoPorIdContaItem(), [
            'idcontapagar' => $idContaPagar,
            'clausula' => $clausula
        ])::exec();

        if ($conciliacaoFinanceira->error()) {
            parent::error(__CLASS__, __FUNCTION__, $conciliacaoFinanceira->errorMessage());
            return false;
        }

        return count($conciliacaoFinanceira->data) > 0;
    }

    public static function buscarLancamentoPorIdNf($idNf)
    {
        $lancamento = SQL::ini(ConciliacaoFinanceiraQuery::buscarLancamentoPorIdNf(), [
            'idnf' => $idNf
        ])::exec();

        if ($lancamento->error()) {
            parent::error(__CLASS__, __FUNCTION__, $lancamento->errorMessage());
            return [];
        }

        return $lancamento->data[0];
    }

    public static function limparMensagemLancamento($idConciliacaoFinanceiraItem)
    {
        $limpandoMensagem = SQL::ini(ConciliacaoFinanceiraQuery::limparMensagemLancamento(), [
            'idconciliacaofinanceiraitem' => $idConciliacaoFinanceiraItem
        ])::exec();

        if ($limpandoMensagem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $limpandoMensagem->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarContapagarItemPorIdContaPagarItem($idContaPagarItem)
    {
        $contaPagarItem = SQL::ini(ConciliacaoFinanceiraQuery::buscarContapagarItemPorIdContaPagarItem(), [
            'idcontapagaritem' => $idContaPagarItem
        ])::exec();

        if ($contaPagarItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $contaPagarItem->errorMessage());
            return [];
        }

        return $contaPagarItem->data[0];
    }
}
