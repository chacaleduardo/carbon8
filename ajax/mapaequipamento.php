<?
require_once("../inc/php/functions.php");

// QUERYS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/tag_query.php");
require_once(__DIR__."/../form/querys/tagsala_query.php");
require_once(__DIR__."/../form/querys/tagreserva_query.php");
require_once(__DIR__."/../form/querys/device_query.php");
require_once(__DIR__."/../form/querys/mapaequipamento_query.php");
require_once(__DIR__."/../form/querys/log_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/tag_controller.php");

$action = $_GET['action'];

if($action)
{
    $params = $_GET['params'];

    if(!isset($params['typeParam']))
    {
        $params['typeParam'] = false;
    }

    if(is_array($params) && ($params['typeParam'] != 'array'))
    {
        return $action(implode(',', $params));
    }

    return $action($params);
}

/**
 * @param $idtag
 * Pode ser id do : Bloco ou sala
 */
function buscarTagsPaiOuFilhos($idTag)
{
    $parametros = explode(',', $idTag);
    $incluirNotExists = false;
    $tipo = 'tp.idtag';
    /**
     * Verificar se a query sera feita dentro de um sala
     * inluir equipamentos do tipo quarto termico
     */
    $buscarNaSala = false;

    if(count($parametros) > 1)
    {
        $idTag = $parametros[0];

        $where = "WHERE $tipo = $idTag";

        if(strpos($parametros[0], '|') != false)
        {
            $buscarNaSala = true;
            $idIn = explode('|', $parametros[0]);
            $idTag = "'{$idIn[0]}', '{$idIn[1]}'";

            $where = "WHERE $tipo IN($idTag)";
        }

        $incluirNotExists = $parametros[1] == 'true' ? true : false;

        if($incluirNotExists)
        {
            $idMapaEquipamento = $parametros[3];
        }

        if(isset($parametros[2]))
        {
            $tipo = $parametros[2];
        }
    }

    // $query = "SELECT
    //             tp.idtag AS idtagpai,
    //             CONCAT(ep.sigla, '-', tp.tag) AS tagpai,
    //             tp.cor AS tagpaicor,
    //             tp.idunidade AS idunidadepai,
    //             ttp.cor AS tagtipopaicor,
    //             tp.descricao AS descricaopai,
    //             ep.idempresa AS idempresapai,
    //             tf.idtag AS idtagfilho, 
    //             CONCAT(ef.sigla, '-', tf.tag) AS tagfilho,
    //             tf.cor AS tagfilhocor,
    //             tf.idunidade AS idunidadefilho,
    //             ef.idempresa AS idempresafilho,
    //             ttf.cor AS tagtipofilhocor,
    //             CONCAT(ef.sigla, '-', tf.tag, '-', tf.descricao) AS descricaofilho, 
    //             ttp.idtagtipo AS idtagtipopai,
    //             ttp.cssicone AS cssiconepai,
    //             ttf.idtagtipo AS idtagtipofilho,
    //             ttf.cssicone AS cssiconefilho,
    //             d.iddevice,
    //             dsb.iddevicesensorbloco,
    //             dsb.tipo
    //         FROM tag tp
    //         JOIN tagsala tsp ON(tsp.idtagpai = tp.idtag)
    //         LEFT JOIN empresa ep ON(ep.idempresa = tp.idempresa)
    //         LEFT JOIN tagtipo ttp ON(ttp.idtagtipo = tp.idtagtipo)
    //         JOIN tag tf ON(tf.idtag = tsp.idtag)
    //         LEFT JOIN empresa ef ON(ef.idempresa = tf.idempresa)
    //         LEFT JOIN tagtipo ttf ON(ttf.idtagtipo = tf.idtagtipo)
    //         -- Pegar devices vinculados
    //         LEFT JOIN device d ON(d.idtag = tf.idtag)
    //         LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
    //         LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
    //         -- LEFT JOIN devicesensorhist dh ON(dh.iddevice = d.iddevice)
    //         -- LEFT JOIN devicecicloativ dc on dc.iddevicecicloativ = dh.iddevicecicloativ
    //         -- LEFT JOIN deviceciclo c on c.iddeviceciclo = dc.iddeviceciclo
    //         $where
    //         AND tf.status = 'ATIVO'";

    $tags = SQL::ini(TagQuery::buscarTagsPaiOuFilhos(), [
        'where' => $where
    ])::exec();

    $tagSql = $tags->sql();

    /**
     * Verificar quais salas já estão vinculadas à esse mapa
     * Usar json
     */
    if($incluirNotExists)
    {
        $tagsFiltradas = [];
        $idDeTagsFiltradas = [];

        // $tags = SQL::ini($tags)::exec();

        // if($tags->error())
        // {

        // }

        // $tagsArr= $tags->data;

        $tags = $tags->data;

        // $resultIdTagPai = $idTag;
        $idSalas = '';

        if($buscarNaSala)
        {
            // $idIn[0] : id do bloco atual
            // $resultIdTagPai = $idIn[0];
            // $idIn[1] : id do da sala clicada
            $IdQueryTagPai = $idIn[1];
            $i = 0;
            foreach($tags as $valor)
            {
                if($valor['idtagtipofilho'] == 476)
                {
                    $tagsFiltradas[$i] = $valor;
                    $idDeTagsFiltradas[$i] = $valor['idtagfilho'];

                    continue;
                }

                if($valor['idtagpai'] == $IdQueryTagPai)
                {
                    if(!$idSalas)
                    {
                        $idSalas = "'{$valor['idtagfilho']}'";
        
                        continue;
                    }
        
                    $idSalas .= ", '{$valor['idtagfilho']}'";
                }

                $i++;
            }
        }
        
        $mapa = SQL::ini(MapaEquipamentoQuery::buscarPorChavePrimaria(), [
            'pkval' => $idMapaEquipamento
        ])::exec();

        $mapaJson = JSON_DECODE($mapa->data[0]['json']);

        foreach($mapaJson->salas as $item)
        {
            if(!$idSalas)
            {
                $idSalas = "'$item->idSala'";

                continue;
            }

            $idSalas .= ", '$item->idSala'";
        }

        if($idSalas)
        {
            $tagSql .= " AND tf.idtag NOT IN($idSalas)";
        }
    }

    $tagSql .= " GROUP BY tf.idtag";

    // $result = d::b()->query($query) or die(mysql_error(d::b()));

    $tagsArr = [];

    // $tagsArr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    $tagsFiltradas = SQL::ini($tagSql)::exec();

    if($tagsFiltradas->numRows())
    {
        $tagsFiltradasArr = $tagsFiltradas->data;
        $i = 0;

        $posicoesDosDevice = [];
        $idDasTags = "";

        $arrDeIdDosM5 = [];

        foreach($tagsFiltradasArr as $item)
        {
            $tagsArr[$item['idtagfilho']]['idtagPai'] = $item['idtagpai'];
            $tagsArr[$item['idtagfilho']]['idUnidadePai'] = $item['idunidadepai'];
            $tagsArr[$item['idtagfilho']]['tagPai'] = $item['tagpai'];
            $tagsArr[$item['idtagfilho']]['descricaoPai'] = $item['descricaopai'];
            $tagsArr[$item['idtagfilho']]['corPai'] = $item['tagpaicor'];
            $tagsArr[$item['idtagfilho']]['idempresaPai'] = $item['idempresapai'];
            $tagsArr[$item['idtagfilho']]['corTipoPai'] = $item['tagtipopaicor'];
            $tagsArr[$item['idtagfilho']]['idtagFilho'] = $item['idtagfilho'];
            $tagsArr[$item['idtagfilho']]['idunidadeFilho'] = $item['idunidadefilho'];
            $tagsArr[$item['idtagfilho']]['tagFilho'] = $item['tagfilho'];
            $tagsArr[$item['idtagfilho']]['idempresaFilho'] = $item['idempresafilho'];
            $tagsArr[$item['idtagfilho']]['descricaoFilho'] = $item['descricaofilho'];
            $tagsArr[$item['idtagfilho']]['corFilho'] = $item['tagfilhocor'];
            $tagsArr[$item['idtagfilho']]['corTipoFilho'] = $item['tagtipofilhocor'];
            $tagsArr[$item['idtagfilho']]['idtagtipoPai'] = $item['idtagtipopai'];
            $tagsArr[$item['idtagfilho']]['cssiconePai'] = $item['cssiconepai'];
            $tagsArr[$item['idtagfilho']]['idtagtipoFilho'] = $item['idtagtipofilho'];
            $tagsArr[$item['idtagfilho']]['cssiconeFilho'] = $item['cssiconefilho'];

            // Guardar id dos equipamentos do tipo controlador que nao encontraram o device
            if($item['idtagfilho']['idtagtipoFilho'] == 83 && !$item['iddevice'])
            {
                array_push($arrDeIdDosM5, $item['idtagfilho']);
            }

            if($item['iddevice'])
            {
                $tagsArr[$item['idtagfilho']]['device']['iddevice'] = $item['iddevice'];
                $tagsArr[$item['idtagfilho']]['device']['iddevicesensorbloco'] = $item['iddevicesensorbloco'];
                $tagsArr[$item['idtagfilho']]['device']['tipo'] = $item['tipo'];

                $tagsArr[$item['idtagfilho']]['cor'] = '#777777';

                $deviceInfo = buscarInformacoesDoDevice($item['iddevice'], $item['idtagfilho'], $item['iddevicesensorbloco'], $item['tipo']);

                $tagsArr[$item['idtagfilho']]['deviceInfo'][0]['valor'] = $deviceInfo[0]['valor'];
                $tagsArr[$item['idtagfilho']]['deviceInfo'][0]['un'] = $deviceInfo[0]['un'];
                $tagsArr[$item['idtagfilho']]['deviceInfo'][0]['color'] = $deviceInfo['color'];

                array_push($posicoesDosDevice, $item['idtagfilho']);

                if(!$idDasTags)
                {
                    $idDasTags = "'{$item['idtagfilho']}'";

                    continue;
                }

                $idDasTags .= ", '{$item['idtagfilho']}'";
            }

            $i++;
        }

        $M5DeTagsLocadas = buscarDevicesApartirDaLocacaoDaTag($arrDeIdDosM5);

        // Atualizando iddevice das tags do tipo controlador locadas
        foreach($M5DeTagsLocadas as $idtagLocada => $tagLocada)
        {
            $tagsArr[$idtagLocada]['device']['iddevice'] = $tagLocada['iddevice'];
            $tagsArr[$idtagLocada]['device']['iddevicesensorbloco'] = $tagLocada['iddevicesensorbloco'];
            $tagsArr[$idtagLocada]['device']['tipo'] = $tagLocada['tipo'];

            $tagsArr[$idtagLocada]['cor'] = '#777777';

            $deviceInfo = buscarInformacoesDoDevice($tagLocada['iddevice'], $idtagLocada, $tagLocada['iddevicesensorbloco'], $tagLocada['tipo']);

            $tagsArr[$idtagLocada]['deviceInfo'][0]['valor'] = $deviceInfo[0]['valor'];
            $tagsArr[$idtagLocada]['deviceInfo'][0]['un'] = $deviceInfo[0]['un'];
            // $tagsArr['equipamentos'][$idtagLocada]['deviceInfo'][0]['color'] = $deviceInfo['color'];

            array_push($posicoesDosDevice, $idtagLocada);

            if(!$idDasTags)
            {
                $idDasTags = "'{$idtagLocada}'";

                continue;
            }

            $idDasTags .= ", '{$idtagLocada}'";
        }

        $cores = verificarSituacaoDoDevice($idDasTags);

        foreach($posicoesDosDevice as $posicao)
        {
            $tagsArr[$posicao]['deviceInfo'][0]['cor'] = $cores[$posicao]['color'];
        }
    }

    echo JSON_ENCODE($tagsArr);
}

/**
 * Pegar informacoes
 * do bloco e suas respectivas Salas
 * e equipamentos incluidos;
 */
function buscarInformacoesAtualizadasDasTags($idBloco)
{
    // $query = "SELECT
    //             -- BLOCO
    //             tp.idtag AS idtagbloco,
    //             tp.tag AS tagbloco,
    //             tp.descricao AS descricaobloco,
    //             tp.cor AS corbloco,
    //             tp.status AS statusbloco,
    //             tp.idunidade AS idunidadebloco,
    //             ttp.idtagtipo AS idtagtipobloco,
    //             ttp.cssicone AS cssiconebloco,
    //             eb.idempresa AS idempresabloco,
    //             -- SALAS
    //             tf.idtag AS idtagsala,
    //             tsp.idtagpai AS idtagpaisala,
    //             CONCAT(es.sigla, '-', tf.tag) as tagsala,
    //             CONCAT(es.sigla, '-', tf.tag,'-', tf.descricao) AS descricaosala,
    //             tf.indpressao AS indpressaosala,
    //             tf.cor AS corsala,
    //             tf.status AS statussala,
    //             tf.idunidade AS idunidadesala,
    //             ttf.idtagtipo AS idtagtiposala,
    //             ttf.cssicone AS cssiconesala,
    //             es.idempresa AS idempresasala,
    //             -- EQUIPAMENTOS
    //             te.idtag AS idtagequipamento,
    //             CONCAT(ee.sigla, '-', te.tag) AS tagequipamento,
    //             te.descricao AS descricaoequipamento,
    //             te.indpressao AS indpressaoequipamento,
    //             te.cor AS corequipamento,
    //             te.status AS statusequipamento,
    //             te.idunidade AS idunidadeequipamento,
    //             tte.idtagtipo AS idtagtipoequipamento,
    //             tte.cssicone AS cssiconeequipamento,
    //             tte.cor as tipotagcorequipamento,
    //             tsf.idtagpai AS idtagpaiequipamento,
    //             ee.idempresa AS idempresaequipamento,
    //             -- EQUIPAMENTOS DE EQUIPAMENTOS
    //             tef.idtag AS idtagequipamentofilho,
    //             CONCAT(eef.sigla, '-', tef.tag) AS tagequipamentofilho,
    //             tef.descricao AS descricaoequipamentofilho, 
    //             tef.status AS statusequipamentofilho,
    //             tef.idunidade AS idunidadeequipamentofilho,
	// 			ttef.idtagtipo AS idtagtipoequipamentofilho,
    //             ttef.cssicone AS cssiconeequipamentofilho,
    //             ttef.cor AS tipotagcorequipamentofilho,
    //             tsef.idtagpai as idtagpaiequipamentofilho,
    //             eef.idempresa AS idempresaequipamentofilho,
    //             -- DEVICE
    //             d.iddevice,
    //             dsb.iddevicesensorbloco,
    //             dsb.tipo
    //         FROM tag tp
    //         JOIN tagsala tsp ON(tsp.idtagpai = tp.idtag)
    //         LEFT JOIN empresa eb ON(eb.idempresa = tp.idempresa)
    //         LEFT JOIN tagtipo ttp ON(ttp.idtagtipo = tp.idtagtipo)
    //         -- Salas
    //         JOIN tag tf ON(tf.idtag = tsp.idtag)
    //         LEFT JOIN tagsala tsf ON(tsf.idtagpai = tf.idtag)
    //         LEFT JOIN empresa es ON(es.idempresa = tf.idempresa)
    //         LEFT JOIN tagtipo ttf ON(ttf.idtagtipo = tf.idtagtipo)
    //         -- Equipamentos da sala
    //         LEFT JOIN tag te ON(te.idtag = tsf.idtag)
    //         LEFT JOIN tagtipo tte ON(tte.idtagtipo = te.idtagtipo)
    //         LEFT JOIN empresa ee ON(ee.idempresa = te.idempresa)
    //         -- Filhos de equipamentos ou salas
    //         LEFT JOIN tagsala tsef ON(tsef.idtagpai = te.idtag)
    //         LEFT JOIN tag tef ON(tsef.idtag = tef.idtag)
    //         LEFT JOIN tagtipo ttef ON(tef.idtagtipo = ttef.idtagtipo)
    //         LEFT JOIN empresa eef ON(eef.idempresa = tef.idempresa)
    //         -- Pegar devices vinculados ao equipamento ou sala
    //         LEFT JOIN device d ON((d.idtag = te.idtag) OR (d.idtag = tef.idtag))
    //         LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
    //         LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
    //         -- WHERE tp.idtag = $idBloco
    //         WHERE tp.idtagclass = 13
    //         -- AND tp.status = 'ATIVO'
    //         -- AND tf.status = 'ATIVO'
    //         -- AND te.status != 'ALOCADO'
    //         -- AND tef.status = 'ATIVO'
    //         GROUP BY te.idtag, tf.idtag, tef.idtag";

    // $result = d::b()->query($query) or die("ERRO buscarInformacoesAtualizadasDasTags():".mysql_error(d::b()));

    $tagsArr = [];

    // $tagsArr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    $tags = SQL::ini(TagQuery::buscarFilhosApartirDoBloco())::exec();

    if($tags->error())
    {

    }

    // unset($tagsArr['error']);

    $idBloco = null;
    $idSala = null;
    $idEquipamento = null;

    $posicoesDosDevice = [];
    $idDasTags = "";

    $arrDeIdDosM5 = [];

    foreach($tags->data as $item)
    {
        if($idBloco != $item['idtagbloco'])
        {
            // BLOCO
            $tagsArr['blocos'][$item['idtagbloco']]['idtagbloco'] = $item['idtagbloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['idunidadebloco'] = $item['idunidadebloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['statusbloco'] = $item['statusbloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['tagbloco'] = $item['tagbloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['descricaobloco'] = $item['descricaobloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['idtagtipobloco'] = $item['idtagtipobloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['cssiconebloco'] = $item['cssiconebloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['corbloco'] = $item['corbloco'];
            $tagsArr['blocos'][$item['idtagbloco']]['idempresabloco'] = $item['idempresabloco'];

            $idBloco = $item['idtagbloco'];
        }

        if($idSala != $item['idtagsala'])
        {
            // SALAS
            if($item['idtagclasssala'] == 1 AND $item['idtagtiposala'] != 476)
            {
                // Equipamentos vinculados direto no bloco
                $tagsArr['equipamentos'][$item['idtagsala']]['idtagequipamento'] = $item['idtagsala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['idunidadeequipamento'] = $item['idunidadesala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['statusequipamento'] = $item['statussala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['tagequipamento'] = $item['tagsala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['descricaoequipamento'] = $item['descricaosala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['idtagtipoequipamento'] = $item['idtagtiposala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['cssiconeequipamento'] = $item['cssiconesala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['idtagpaiequipamento'] = $item['idtagpaisala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['tipotagcorequipamento'] = $item['corsala'];
                $tagsArr['equipamentos'][$item['idtagsala']]['idempresaequipamento'] = $item['idempresasala'];
            } else 
            {
                $tagsArr['salas'][$item['idtagsala']]['idtagsala'] = $item['idtagsala'];
                $tagsArr['salas'][$item['idtagsala']]['idtagpaisala'] = $item['idtagpaisala'];
                $tagsArr['salas'][$item['idtagsala']]['idunidadesala'] = $item['idunidadesala'];
                $tagsArr['salas'][$item['idtagsala']]['statussala'] = $item['statussala'];
                $tagsArr['salas'][$item['idtagsala']]['tagsala'] = $item['tagsala'];
                $tagsArr['salas'][$item['idtagsala']]['descricaosala'] = $item['descricaosala'] ;
                $tagsArr['salas'][$item['idtagsala']]['idtagtiposala'] = $item['idtagtiposala'];
                $tagsArr['salas'][$item['idtagsala']]['cssiconesala'] = $item['cssiconesala'];
                $tagsArr['salas'][$item['idtagsala']]['indpressaosala'] = $item['indpressaosala'];
                $tagsArr['salas'][$item['idtagsala']]['corsala'] = $item['corsala'];
                $tagsArr['salas'][$item['idtagsala']]['idempresasala'] = $item['idempresasala'];
            }

            $idSala = $item['idtagsala'];
        }

        if($idEquipamento != $item['idtagequipamento'])
        {
            if($item['idtagtipoequipamento'] == 476)
            {
                // EQUIPAMENTO DO TIPO QUARTO TERMICO QUE E CONSIDERADO COMO SALA
                $tagsArr['salas'][$item['idtagequipamento']]['idtagsala'] = $item['idtagequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['idtagpaisala'] = $item['idtagpaiequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['idunidadesala'] = $item['idunidadeequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['statussala'] = $item['statusequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['tagsala'] = $item['tagequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['descricaosala'] = "{$item['tagequipamento']} {$item['descricaoequipamento']}";
                $tagsArr['salas'][$item['idtagequipamento']]['idtagtiposala'] = $item['idtagtipoequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['cssiconesala'] = $item['cssiconeequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['indpressaosala'] = $item['indepressaoequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['corsala'] = $item['corequipamento'];
                $tagsArr['salas'][$item['idtagequipamento']]['idempresasala'] = $item['idempresaequipamento'];
            } else 
            {
                /**
                 * Verificar se a sala dentro de outra possui sala equipamentos
                 */
                if($item['idtagequipamento'])
                {
                    // EQUIPAMENTOS
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['idtagequipamento'] = $item['idtagequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['idunidadeequipamento'] = $item['idunidadeequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['statusequipamento'] = $item['statusequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['tagequipamento'] = $item['tagequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['descricaoequipamento'] = $item['descricaoequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['idtagtipoequipamento'] = $item['idtagtipoequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['cssiconeequipamento'] = $item['cssiconeequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['idtagpaiequipamento'] = $item['idtagpaiequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['tipotagcorequipamento'] = $item['tipotagcorequipamento'];
                    $tagsArr['equipamentos'][$item['idtagequipamento']]['idempresaequipamento'] = $item['idempresaequipamento'];
                }
            }

            $idEquipamento = $item['idtagequipamento'];
        }

        /**
         * Verificar se a sala dentro de outra possui sala equipamentos
         */
        if($item['idtagequipamentofilho'])
        {
            // EQUIPAMENTOS FILHOS DE EQUIPAMENTOS
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['idtagequipamento'] = $item['idtagequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['idunidadeequipamento'] = $item['idunidadeequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['statusequipamento'] = $item['statusequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['tagequipamento'] = $item['tagequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['descricaoequipamento'] = $item['descricaoequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['idtagtipoequipamento'] = $item['idtagtipoequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['cssiconeequipamento'] = $item['cssiconeequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['idtagpaiequipamento'] = $item['idtagpaiequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['tipotagcorequipamento'] = $item['tipotagcorequipamentofilho'];
            $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['idempresaequipamento'] = $item['idempresaequipamentofilho'];
        }

        // Guardar id dos equipamentos do tipo controlador que nao encontraram o device
        if($item['idtagtipoequipamento'] == 83 && !$item['iddevice'])
        {
            array_push($arrDeIdDosM5, $item['idtagequipamento']);
        }

        if($item['idtagtipoequipamentofilho'] == 83 && !$item['iddevice'])
        {
            array_push($arrDeIdDosM5, $item['idtagequipamentofilho']);
        }

        if($item['iddevice'] && $item['idtagtipoequipamento'] != 476 && ($item['idtagequipamentofilho'] || $item['idtagequipamento']))
        {
            $tagsArr['equipamentos'][$item['idtagequipamento']]['device']['iddevice'] = $item['iddevice'];
            $tagsArr['equipamentos'][$item['idtagequipamento']]['device']['iddevicesensorbloco'] = $item['iddevicesensorbloco'];
            $tagsArr['equipamentos'][$item['idtagequipamento']]['device']['tipo'] = $item['tipo'];

            $tagsArr['equipamentos'][$item['idtagequipamento']]['cor'] = '#777777';

            $deviceInfo = buscarInformacoesDoDevice($item['iddevice'], $item['idtagequipamento'], $item['iddevicesensorbloco'], $item['tipo']);

            $tagsArr['equipamentos'][$item['idtagequipamento']]['deviceInfo'][0]['valor'] = $deviceInfo[0]['valor'];
            $tagsArr['equipamentos'][$item['idtagequipamento']]['deviceInfo'][0]['un'] = $deviceInfo[0]['un'];
            $tagsArr['equipamentos'][$item['idtagequipamento']]['deviceInfo']['cor'] = $deviceInfo['color'];
            $tagsArr['equipamentos'][$item['idtagequipamento']]['deviceInfo']['label'] = $deviceInfo['text'];

            array_push($posicoesDosDevice, $item['idtagequipamento']);

            if(!$idDasTags)
            {
                $idDasTags = "'{$item['idtagequipamento']}'";

                continue;
            }

            $idDasTags .= ", '{$item['idtagequipamento']}'";

            if($item['idtagequipamentofilho'])
            {
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['device']['iddevice'] = $item['iddevice'];
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['device']['iddevicesensorbloco'] = $item['iddevicesensorbloco'];
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['device']['tipo'] = $item['tipo'];

                $deviceInfo = buscarInformacoesDoDevice($item['iddevice'], $item['idtagequipamentofilho'], $item['iddevicesensorbloco'], $item['tipo']);

                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['deviceInfo'][0]['valor'] = $deviceInfo[0]['valor'];
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['deviceInfo'][0]['un'] = $deviceInfo[0]['un'];
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['deviceInfo']['cor'] = $deviceInfo['color'];
                $tagsArr['equipamentos'][$item['idtagequipamentofilho']]['deviceInfo']['label'] = $deviceInfo['text'];

                array_push($posicoesDosDevice, $item['idtagequipamentofilho']);
            }
        }
    }

    $M5QueDeTagsLocadas = buscarDevicesApartirDaLocacaoDaTag($arrDeIdDosM5);

    // Atualizando iddevice das tags do tipo controlador locadas
    foreach($M5QueDeTagsLocadas as $idtagLocada => $tagLocada)
    {
        if(!isset($tagsArr['equipamentos'][$idtagLocada]['device']))
        {
            $tagsArr['equipamentos'][$idtagLocada]['device'] = [];
        }

        $tagsArr['equipamentos'][$idtagLocada]['device']['idtagoriginal'] = $tagLocada['idtagoriginal'];

        $tagsArr['equipamentos'][$idtagLocada]['device']['iddevice'] = $tagLocada['iddevice'];
        $tagsArr['equipamentos'][$idtagLocada]['device']['iddevicesensorbloco'] = $tagLocada['iddevicesensorbloco'];
        $tagsArr['equipamentos'][$idtagLocada]['device']['tipo'] = $tagLocada['tipo'];

        $deviceInfo = buscarInformacoesDoDevice($tagLocada['iddevice'], $idtagLocada, $tagLocada['iddevicesensorbloco'], $tagLocada['tipo']);

        $tagsArr['equipamentos'][$idtagLocada]['deviceInfo'][0]['valor'] = $deviceInfo[0]['valor'];
        $tagsArr['equipamentos'][$idtagLocada]['deviceInfo'][0]['un'] = $deviceInfo[0]['un'];
        $tagsArr['equipamentos'][$idtagLocada]['deviceInfo']['cor'] = $deviceInfo['color'];
        $tagsArr['equipamentos'][$idtagLocada]['deviceInfo']['label'] = $deviceInfo['text'];

        array_push($posicoesDosDevice, $idtagLocada);

        if(!$idDasTags)
        {
            $idDasTags = "'{$idtagLocada}'";

            continue;
        }

        $idDasTags .= ", '{$idtagLocada}'";
    }

    return $tagsArr;
}

function buscarInformacoesDoDevice($iddevice, $idDasTags = null, $iddevicesensorbloco = null, $tipo = null)
{
    $buscaPorAjax = false;
    if(strpos($iddevice, ','))
    {
        $buscaPorAjax = true;
        $parametros = explode(',', $iddevice);

        $iddevice = explode('-', $parametros[0]);
        $idDasTags = explode('-', $parametros[1]);
        $iddevicesensorbloco = explode('-', $parametros[2]);
        $tipo = $parametros[3] == 'false' ? false : explode('|', $parametros[3]);
    }

    //Pegar estado do Device
    if($buscaPorAjax)
    {
        $deviceArr = [];
        $deviceTipo = verificarSituacaoDoDevice(implode(',', $idDasTags));

        foreach($idDasTags as $key => $idTag)
        {
            $sensorDoBloco = buscarSensorDoBlocoPeloIdSensorBloco($iddevicesensorbloco[$key]);

            if(is_array($tipo))
            {
                foreach($tipo as $keyTipo => $value)
                {
                    $deviceArr[$idTag][$keyTipo] = [
                        'un' => buscarTipoDaUnidadeDeMedida($value, $iddevice[$key]),
                        'valor' => $sensorDoBloco[$value]
                    ];
                }

                if(!isset($deviceArr[$idTag]['cor']))
                {
                    $deviceArr[$idTag]['cor'] = $sensorDoBloco['color'] ?? '#777777';
                }

               continue;
            }

            if(!$tipo)
            {
                $tipo = $deviceTipo[$idTag]['tipo'];
            }

            $valor = $sensorDoBloco[$tipo];
            $un = buscarTipoDaUnidadeDeMedida($tipo, $iddevice[$key]);
            $deviceArr[$idTag] = [
                'valor' => $valor,
                'un' => $un
            ];

            $deviceArr[$idTag]['cor'] = $sensorDoBloco['color'] ?? '#777777';
        }

        if($buscaPorAjax)
        {
            echo JSON_ENCODE($deviceArr);
            return true;
        }

        return $deviceArr;
    }

    //Pegar dados do sensor
    $sensorDoBloco = buscarSensorDoBlocoPeloIdSensorBloco($iddevicesensorbloco);

    if(is_array($tipo))
    {
        $dadosDevice = [];

        foreach($tipo as $valor)
        {
            array_push($dadosDevice, [
                'un' => buscarTipoDaUnidadeDeMedida($valor, $iddevice),
                'valor' => $sensorDoBloco[$valor]
            ]);
        }

        $dadosDevice['color'] = $sensorDoBloco['color'] ?? '#777777';
        $dadosDevice['text']  = $sensorDoBloco['text'];

        if($buscaPorAjax)
        {
            echo JSON_ENCODE($dadosDevice);
            return true;
        }

        return $dadosDevice;
    }

    $valor = $sensorDoBloco[$tipo];
    $un = buscarTipoDaUnidadeDeMedida($tipo, $iddevice);

    $dadosDevice = [
        [
            'valor' => $valor,
            'un' => $un
        ]
    ];

    $dadosDevice['color'] = $sensorDoBloco['color'] ?? '#777777';
    $dadosDevice['text'] = $sensorDoBloco['text'];

    if($buscaPorAjax)
    {
        echo JSON_ENCODE($dadosDevice);
        return true;
    }

    return $dadosDevice;
}

function buscarSensorDoBlocoPeloIdSensorBloco($idSensor)
{
    return re::dis()->hGetAll('_estado:'.$idSensor.':devicesensorbloco');
}

function verificarSituacaoDoDevice($idtag)
{
    $devices = SQL::ini(DeviceQuery::buscarInfoDevices(),[
        'idtag' => $idtag,
    ])::exec();

    $arrRetorno = [];

    foreach($devices->data as $key => $device)
    {
        $arrRetorno[$device['idtag']] = [
            'idtag' => $device['idtag'],
            'tipo' => $device['tipo']
        ];
    }

    return $arrRetorno;
}

function buscarDevicePeloIdDevice($idDevice)
{
    return re::dis()->hGetAll("_estado:$idDevice:device");
}

function buscarTipoDaUnidadeDeMedida($tipo, $iddevice)
{
    $un = 'ºC';

    if ($tipo == 't')
     {
         $un = 'ºC';
     } else if($tipo == 'p' && ($iddevice == 93 || $iddevice == 33))
     {
         $un = 'bar';
     } else if($tipo == 'u')
     {
         $un = 'um';
     }else if ($tipo == 'p'){
         $un = 'Pa';
     } else if($tipo == 'd')
     {
        $un = 'Pa/D';
     }

     return $un;
}

function buscarMapaPorIdTag($idTag)
{
    // $query = "SELECT `idmapaequipamento`, `idtag`, `json`
    //             FROM mapaequipamento
    //             WHERE idtag = $id;";

    $mapaEquipamento = SQL::ini(MapaEquipamentoQuery::buscarMapaPorIdTag(), [
        'idtag' => $idTag
    ])::exec();

    // $result = d::b()->query($query) or die("Erro: buscarTagPorId() | ".mysql_error(d::b()));

    // $arr = [];

    // $arr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    if($mapaEquipamento->error())
    {
        
    }

    // $i = 0;

    // unset($arr['error']);

    // while($item = mysql_fetch_assoc($result))
    // {
    //     $arr[$i]['idmapaequipamento'] = $item['idmapaequipamento'];
    //     $arr[$i]['idtag'] = $item['idtag'];
    //     $arr[$i]['json'] = $item['json'];

    //     $i++;
    // }

    echo JSON_ENCODE($mapaEquipamento->data);
}

function buscarTagPorIdTag($idTag)
{
    $tag = SQL::ini(TagQuery::buscarPorChavePrimariaPadrao(), [
        'pkval' => $idTag
    ])::exec();

    if($tag->error())
    {

    }

    echo json_encode($tag->data[0]);
}

function carregarMapaPorIdTag($id)
{
    // Pegar dados atualizados das salas vinculadas àquele bloco
    $tags = buscarInformacoesAtualizadasDasTags($id);

    // $query = "SELECT idmapaequipamento, json
    //         FROM mapaequipamento
    //         WHERE idtag = $id;";

    $mapaEquipamento = SQL::ini(MapaEquipamentoQuery::buscarMapaPorIdTag(), [
        'idtag' => $id
    ])::exec();

    // $result = d::b()->query($query) or die("Erro: buscarMapaPorIdTag() | ".mysql_error(d::b()));

    $tagPrincipalArr = [];

    // $arr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    if($mapaEquipamento->error())
    {

    }

    /**
     * Monta um array definindo a posicao como o id da tag e sala
    */
    if($id && $mapaEquipamento->numRows())
    {
        $arrBlocoOrdenadoPeloId = [];

        // unset($arr['error']);

        // Guardar a ordenacao das salas para quer seja respeitada
        // a sobreposicao na rederizacao na DOM
        $originalOrder = [];
        $ordenedSalas = [];

        // foreach($mapaEquipamento->data[0] as $item)
        // {
            
        // }

        $mapaJson = json_decode($mapaEquipamento->data[0]['json']);
        // $arr['mapa'] = $mapaEquipamento->data[0]['json'];
        $tagPrincipalArr['idmapaequipamento'] = $mapaEquipamento->data[0]['idmapaequipamento'];

        // Ordenando blocos por id
        foreach($mapaJson->blocos as $key => $bloco)
        {
            // Verifica se a sala salva no json ainda pertence ao bloco
            // if(!isset($tags['blocos'][$bloco->id]) || $tags['blocos'][$bloco->id]['statusbloco'] != 'ATIVO')
            if(isset($tags['blocos'][$bloco->id]['statusbloco']) && $tags['blocos'][$bloco->id]['statusbloco'] != 'ATIVO')
            {
                continue;
            }

            $originalOrder[$key] = $bloco->id;

            $arrBlocoOrdenadoPeloId['blocos'][$bloco->id] = $bloco;
        }


        // Ordenando salas por id
        foreach($mapaJson->salas as $key => $sala)
        {
            // Verifica se a sala salva no json ainda pertence ao bloco
            // if(!isset($tags['salas'][$sala->idSala]) || $tags['salas'][$sala->idSala]['statussala'] != 'ATIVO')
            if(isset($tags['salas'][$sala->idSala]['statussala']) && $tags['salas'][$sala->idSala]['statussala'] != 'ATIVO')
            {
                continue;
            }

            if(!isset($tags['salas'][$sala->idSala]))
            {
                $mapaJson->salas[$key]->localDesatualizado = true;
            } else {
                $mapaJson->salas[$key]->localDesatualizado = false;
            }

            $originalOrder[$key] = $sala->idSala;

            $arrBlocoOrdenadoPeloId['salas'][$sala->idSala] = $sala;
        }

        // Ordenando equipamentos por id
        foreach($mapaJson->equipamentos as $key => $equipamento)
        {
            // Verifica se o equipamento salvo no json ainda pertence ao bloco
            if(!isset($tags['equipamentos'][$equipamento->idtag]) || isset($tags['equipamentos'][$equipamento->idtag]['statusequipamento']) && $tags['equipamentos'][$equipamento->idtag]['statusequipamento'] != 'ATIVO')
            // if(isset($tags['equipamentos'][$equipamento->idtag]['statusequipamento']) && $tags['equipamentos'][$equipamento->idtag]['statusequipamento'] != 'ATIVO')
            {
                continue;
            }

            if(!isset($tags['equipamentos'][$equipamento->idtag]))
            {
                $mapaJson->equipamentos[$key]->localDesatualizado = true;
            } else {
                $mapaJson->equipamentos[$key]->localDesatualizado = false;
            }

            $arrBlocoOrdenadoPeloId['equipamentos'][$equipamento->idtag] = $equipamento;
        }
    }

    // Atualizando informações dos blocos no json
    foreach($tags['blocos'] as $key => $bloco)
    {
        if($bloco['statusbloco'] != 'ATIVO')
        {
            continue;
        }

        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->titulo = $bloco['descricaobloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->idunidade = $bloco['idunidadebloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->status = $bloco['statusbloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->tag = $bloco['tagbloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->idtagtipo = $bloco['idtagblocotagtipobloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->cssicone = $bloco['cssiconebloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->indpressao = $bloco['indpressaobloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->cor = $bloco['corbloco'];
        $arrBlocoOrdenadoPeloId['blocos'][$bloco['idtagbloco']]->idempresa = $bloco['idempresabloco'];
    }

    // Atualizando informações das salas no json
    foreach($tags['salas'] as $key => $sala)
    {
        if($sala['statussala'] != 'ATIVO')
        {
            continue;
        }

        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->titulo = $sala['descricaosala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->idunidade = $sala['idunidadesala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->status = $sala['statussala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->tag = $sala['tagsala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->idtagtipo = $sala['idtagtiposala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->cssicone = $sala['cssiconesala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->indpressao = $sala['indpressaosala'];
        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->idempresa = $sala['idempresasala'];

        if($arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->idSalaPai && $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->idSalaPai != $sala['idtagpaisala'])
        {
            $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->localDesatualizado = true;
        } else {
            $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->localDesatualizado = false;
        }

        $arrBlocoOrdenadoPeloId['salas'][$sala['idtagsala']]->cor = $sala['corsala'];
    }

    // Atualizando informacoes dos equipamentos no json
    foreach($tags['equipamentos'] as $key => $tag)
    {
        if($tag['statusequipamento'] !== null && $tag['statusequipamento'] != 'ATIVO')
        {
            continue;
        }

        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtag = $tag['idtagequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idunidade = $tag['idunidadeequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->status = $tag['statusequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->tag = $tag['tagequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->title = str_replace("'", "", $tag['descricaoequipamento']);
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->cssicone = $tag['cssiconeequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtagtipo = $tag['idtagtipoequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->cor = $tag['tipotagcorequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idempresa = $tag['idempresaequipamento'];
        $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtagpai = $tag['idtagpaiequipamento'];

        if($arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtagpai && $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtagpai != $tag['idtagpaiequipamento'])
        {
            $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->localDesatualizado = true;
        } else {
            $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->localDesatualizado = false;

            $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->idtagpai = $tag['idtagpaiequipamento'];
        }
        if(isset($tag['device']))
        {
            $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->device = $tag['device'];
            $arrBlocoOrdenadoPeloId['equipamentos'][$tag['idtagequipamento']]->deviceInfo = $tag['deviceInfo'];
        }
    }

    // Pegar apenas blocos que possuem PATH
    $arrBlocoOrdenadoPeloId['blocos'] = array_filter($arrBlocoOrdenadoPeloId['blocos'], function($element)
    {
        return $element->path;
    });

    // Pegar apenas salas que possuem PATH
    $arrBlocoOrdenadoPeloId['salas'] = array_filter($arrBlocoOrdenadoPeloId['salas'], function($element)
    {
        return $element->path;
    });

    foreach($originalOrder as $key => $value)
    {
        $ordenedSalas[$key] = $arrBlocoOrdenadoPeloId['salas'][$value];
    }

    $arrBlocoOrdenadoPeloId['salas'] = $ordenedSalas;

    if(!count($arrBlocoOrdenadoPeloId['equipamentos']))
    {
        $arrBlocoOrdenadoPeloId['equipamentos'] = [];
    }

    $tagPrincipalArr['mapa'] = $arrBlocoOrdenadoPeloId;

    echo JSON_ENCODE($tagPrincipalArr);
}

function buscarTagPaiOuFilho($idTag)
{
    $parametros = explode(',', $idTag);
    $tipo = 'idtagpai';

    if(count($parametros) > 1)
    {
        $idTag = (int)$parametros[0];
        $tipo = $parametros[1];
    }
    $idPaiOrFilho = $tipo;

    // $query = "SELECT idtagsala, idtag, idtagpai
    //         FROM tagsala
    //         WHERE $idPaiOrFilho = $idTag;";

    $tags = SQL::ini(TagSalaQuery::buscarTagPaiOuFilho(), [
        'coluna' => $idPaiOrFilho,
        'valor' => $idTag
    ])::exec();

    // $result = d::b()->query($query) or die("Erro: getTagPaiByIdTagFilho() | ".mysql_error(d::b()));

    // $arr = [];

    // $arr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    // if($result->num_rows)
    // {
    //     unset($arr['error']);

    //     while($item = mysql_fetch_assoc($result))
    //     {
    //         $arr['idtagsala'] = $item['idtagsala'];
    //         $arr['idtag'] = $item['idtag'];
    //         $arr['idtagpai'] = $item['idtagpai'];
    //     }
    // }

    echo JSON_ENCODE($tags->data);
}

 function buscarTags($idTagBloco = false)
 {
    /**
     * idtagclass = 1 (equipamento)
     */
    // COUNT(t.descricao) as quantidade
    //  $query = "SELECT t.idtag, CONCAT(e.sigla, '-', t.descricao) as descricao, ts.idtagpai, tt.cssicone, t.tag
    //             FROM tag t
    //             JOIN empresa e ON(e.idempresa = t.idempresa)
    //             LEFT JOIN tagtipo tt ON(tt.idtagtipo = t.idtagtipo)
    //             LEFT JOIN tagsala ts ON(ts.idtag = t.idtag)
    //             WHERE t.idtagclass = 1
    //             AND (t.status = 'ATIVO' OR t.status = 'LOCADO')";
    
    $tags = SQL::ini(TagQuery::buscarTagsAtivasOuLocadas())::exec();
    $tagSql = $tags->sql();

    if(cb::idempresa() != 8)
    {
        $tagSql .= "AND t.idempresa = ".cb::idempresa();
    }

    /**
     * TODO: Fazer um not exists
     * no json do mapaequipamento
     */
    if($idTagBloco)
    {
        $ids = "";
        // $idsTagSala = "";

        // $result = d::b()->query($tagSql) or die('Erro: buscarTags | '.mysql_error(d::b()));

        // while($item = mysql_fetch_assoc($result))
        // {
        //     if(!$idsTagSala)
        //     {
        //         $idsTagSala = $item['idtagpai'];

        //         continue;
        //     }

        //     $idsTagSala = ", {$item['idtagpai']}";
        // }

        // $queryMapaEquipamento = "SELECT json
        //                             FROM mapaequipamento
        //                             WHERE idtag IN ($idTagBloco)";

        // $resultMapaEquipamento = json_decode(mysql_fetch_assoc(d::b()->query($queryMapaEquipamento))['json']);
        
        $mapaEquipamento = SQL::ini(MapaEquipamentoQuery::buscarMapaPorIdTag(), [
            'idtag' => $idTagBloco
        ])::exec();

        $mapaEquipamentoJson = json_decode($mapaEquipamento->data[0]['json']);

        foreach($mapaEquipamentoJson->equipamentos as $item)
        {
            if(!$ids)
            {
                $ids = $item->idtag;

                continue;
            }

            $ids .= ", $item->idtag";
        }

        if($ids)
        {
            $tagSql .= "AND t.idtag NOT IN($ids)";
        }
    }

    $tagSql .= "GROUP BY t.idtag;";

    $tagsFiltradas = SQL::ini($tagSql)::exec();

    if($tagsFiltradas->error())
    {

    }

    // $arr = [];

    // $arr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    // if($result->num_rows)
    // {
    //     unset($arr['error']);

    //     $i = 0;

    //     while($item = mysql_fetch_assoc($result))
    //     {
    //         $arr[$i]['idtag'] = $item['idtag'];
    //         $arr[$i]['descricao'] = $item['descricao'];
    //         $arr[$i]['idtagpai'] = $item['idtagpai'];
    //         $arr[$i]['quantidade'] = $item['quantidade'];
    //         $arr[$i]['cssicone'] = $item['cssicone'];
    //         $arr[$i]['tag'] = $item['tag'];

    //         $i++;
    //     }
    // }

    echo JSON_ENCODE($tagsFiltradas->data);
 }

 function atualizarTagPorIdTag($dados)
 {
    $idTag = $dados['id'];
    $valores = $dados['values'];

    if(!count($valores))
    {
        echo 'Nenhum valor enviado';
        return false;
    }

    // $query = "UPDATE tag
    //           SET ";

    $colunasEValores = '';

    foreach($valores as $chave => $valor)
    {
        if(count($valores) == 1)
        {
            $colunasEValores .= "`$chave` = '$valor' ";

            continue;
        }

        $colunasEValores .= "`$chave` = '$valor', ";
    }

    // $query .= " WHERE idtag = $idTag;";

    $atualizandoValores = SQL::ini(TagQuery::atualizarColunasEValoresPorIdTag(), [
        'colunasEValores' => $colunasEValores,
        'idtag' => $idTag
    ])::exec();
 }

function buscarBlocosDisponiveis($idMapa)
{
    $mapa = SQL::ini(MapaEquipamentoQuery::buscarPorChavePrimaria(), [
        'pkval' => $idMapa
    ])::exec();

    // $queryMapa = "SELECT idmapaequipamento, json
    //             FROM mapaequipamento
    //             WHERE idmapaequipamento = $idMapa;";

    // $resultMapa = d::b()->query($queryMapa) or die("Erro: buscarBlocosDisponiveis() | ".mysql_error(d::b()));

    $ids = '';
    $notIn = null;

    if($mapa->numRows())
    {
        $jsonMapa = JSON_DECODE($mapa->data[0]['json']);

        if($jsonMapa->blocos)
        {
            foreach($jsonMapa->blocos as $bloco)
            {
                if(!$ids)
                {
                    $ids = "'$bloco->id'";

                    continue;
                }

                $ids .= ", '$bloco->id'";
            }
        }

        if($ids)
        {
            $notIn = "AND t.idtag NOT IN($ids)";
        }
    }

    // $query = "SELECT
    //             t.idtag as id,
    //             t.tag,
    //             CONCAT(e.sigla, '-', t.tag, '-', t.descricao) as descricao,
    //             t.cor,
    //             u.idunidade
    //         FROM tag t
    //         JOIN empresa e ON(t.idempresa = e.idempresa)
    //         LEFT JOIN unidade u ON(u.idunidade = t.idunidade)
    //         LEFT JOIN fluxostatus fs ON(fs.idfluxostatus = t.idfluxostatus)
    //         LEFT JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
    //         WHERE t.status = 'ATIVO'
    //         AND cs.statustipo != 'INATIVO'
    //         AND t.idtagclass = 13
    //         $notIn
    //         AND EXISTS(
    //             SELECT 1
    //             FROM tagsala
    //             WHERE idtagpai = t.idtag
    //         )
    //         GROUP BY t.idtag;";

    $blocos = SQL::ini(TagQuery::buscarBlocosDisponiveisParaVinculo(), [
        'notin' => $notIn
    ])::exec();

    if($blocos->error())
    {

    }

    // $arr = [];

    // $arr = [
    //     'error' => 'Nenhum resultado encontrado!'
    // ];

    // if($blocos->numRows())
    // {
    //     $i = 0;

    //     unset($arr['error']);

    //     while($item = mysql_fetch_assoc($result))
    //     {
    //         $arr[$i]['id'] = $item['idtag'];
    //         $arr[$i]['tag'] = $item['tag'];
    //         $arr[$i]['descricao'] = $item['descricao'];
    //         $arr[$i]['cor'] = $item['cor'];

    //         $i++;
    //     }
    // }

    echo JSON_ENCODE($blocos->data);
}

function buscarUltimoRegistroDoMapaEquipamento()
{
    // $arr = [
    //     'error' => 'ID não encontrado'
    // ];

    // $query = "SELECT idmapaequipamento
    //         FROM mapaequipamento
    //         ORDER BY idmapaequipamento DESC
    //         LIMIT 1";

    $ultimoRegistro = SQL::ini(MapaEquipamentoQuery::buscarUltimoRegistro())::exec();

    // $res = d::b()->query($query);

    // $idMapaEquipamento = mysql_fetch_assoc($res)['idmapaequipamento'];


    // if($idMapaEquipamento)
    // {
    //     unset($arr['error']);

    //     $arr['idmapaequipamento'] = $idMapaEquipamento;
    // }

    echo JSON_ENCODE($ultimoRegistro->data);
}

function locarTag($valores)
{
    $parametros = explode(',', $valores);

    $idTagQueSeraLocada = $parametros[0];
    // $idTagDoNovoLocal = $parametros[1];
    $idEmpresaDoNovoLocal = $parametros[1];
    $idUnidadeDoNovoLocal = $parametros[2];
    $dataInicioLocacao = date('Y-m-d');
    $dataFimLocacao = null;

    $tagVeioDeUmaLocacao = SQL::ini(TagReservaQuery::buscarPeloIdObjeto(), [
        'idobjeto' => $idTagQueSeraLocada,
        'tipoobjeto' => 'tag'
    ])::exec();

    $tagLocada = TagController::locacaoDeTag($idTagQueSeraLocada, $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal, $dataInicioLocacao, $dataFimLocacao, $tagVeioDeUmaLocacao->data[0]);

    if(!count($tagLocada))
    {
        $tagLocada['error'] = 'Nenhum valor retornado!';
    }

    echo JSON_ENCODE($tagLocada);
}

function buscarUnidadesPorIdEmpresa($idempresa)
{
    $unidades = SQL::ini(UnidadeQuery::buscarUnidadesPorIdEmpresa(), [
        'idempresa' => $idempresa
    ])::exec();

    return JSON_ENCODE($unidades->data);
}

function buscarDevicesApartirDaLocacaoDaTag($arrDosIds)
{
    $idsDasTagsLocadas = implode(',', $arrDosIds);
    $arrDoRetorno = [];

    // $query = "  SELECT tr.idtag as idtagoriginal, tr.idobjeto as idtaglocada, d.iddevice, dsb.iddevicesensorbloco, dsb.tipo
    //             FROM tagreserva tr
    //             JOIN device d ON(d.idtag = tr.idtag)
    //             LEFT JOIN devicesensor ds ON(d.iddevice = ds.iddevice)
    //             LEFT JOIN devicesensorbloco dsb ON(ds.iddevicesensor = dsb.iddevicesensor)
    //             WHERE tr.idobjeto IN($idsDasTagsLocadas)
    //             GROUP BY tr.idobjeto;";

    $tagsOriginais = SQL::ini(TagReservaQuery::buscarDevicesApartirDaLocacaoDaTag(), [
        'iddastagslocadas' => $idsDasTagsLocadas
    ])::exec();

    if($tagsOriginais->error())
    {

    }

    foreach ($tagsOriginais->data as $tag)
    {
        $arrDoRetorno[$tag['idtaglocada']] = [
            'idtagoriginal' => $tag['idtagoriginal'],
            'iddevice' => $tag['iddevice'],
            'iddevicesensorbloco' => $tag['iddevicesensorbloco'],
            'tipo' => $tag['tipo']
        ];
    }

    return $arrDoRetorno;
}

function buscarBlocoDaTag($idTag)
{
    $tagBloco = TagController::buscarBlocoDaTag($idTag);

    echo json_encode($tagBloco);
}