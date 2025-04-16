<?
require_once("../inc/php/functions.php");
require_once("builder.php");

class Unidade
{
    const TABLE_VINCULO = 'unidadeobjeto';
    const TABLE         = 'unidade';

    public function getAtivas($toString = false)
    {
        $queryBuilder = new Builder();

        $query = $queryBuilder
                    ->select("idunidade, unidade")
                    ->from(self::TABLE)
                    ->where("status = 'ATIVO'");

        if($toString)
        {
            return $query->toString()->get();
        }

        $result = $query->get();

        $arr = [];

        $arr['error'] = "Nenhum registro encontrado";

        if($result->num_rows)
        {
            unset($arr['error']);

            $i = 0;

            while($item = mysql_fetch_assoc($result))
            {
                $arr[$i]['idunidade'] = $item['idunidade'];
                $arr[$i]['unidade']   = $item['unidade'];

                $i++;
            }
        }

        return $arr;
    }

    public function getDisponiveis($idobjeto, $tipoobjeto)
    {
        $arr = [];
        $i = 0;

        $queryBuilder = new Builder();
        $queryBuilder2 = new Builder();

        $queryWhereNotIn = $queryBuilder2
                            ->select("uo2.idunidade")
                            ->from("unidadeobjeto uo2")
                            ->where("uo2.idobjeto = $idobjeto")
                            ->where("uo2.tipoobjeto = '$tipoobjeto'")
                            ->getQuery();

        $query = $queryBuilder
                    ->select(self::TABLE.".idempresa, ".self::TABLE.".idunidade, ".self::TABLE.".unidade")
                    ->from(self::TABLE)
                    ->join("empresa e", "e.idempresa = ".self::TABLE.".idempresa")
                    ->join(self::TABLE_VINCULO, self::TABLE_VINCULO.".idunidade = ".self::TABLE.".idunidade")
                    ->whereNotIn(self::TABLE_VINCULO.".idunidade", $queryWhereNotIn)
                    ->where("e.status = 'ATIVO'")
                    ->where(self::TABLE.".status = 'ATIVO'")
                    ->where(self::TABLE.".idempresa = ".cb::idempresa())
                    ->groupBy(self::TABLE.".idunidade");

        $result = $query->get();

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]["idempresa"] = $item["idempresa"];
            $arr[$i]["value"] = $item["idunidade"];
            $arr[$i]["label"] = $item["unidade"];
            $i++;
        }

        if(!count($arr))
        {
            $arr[$i]['error'] = 'Nenhum resultado encontrado!';
        }

        return $arr;
    }

    public function getByTipo($idobjeto = null, $tipoobjeto)
    {
        $arr = [];
        $i = 0;

        $queryBuilder = new Builder();
        $queryBuilderUnion = new Builder();

        // Pegar unidades do objeto principal
        $query = $queryBuilder->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, '$tipoobjeto' as tipoobjeto,".self::TABLE_VINCULO.".idunidadeobjeto")
                ->from(self::TABLE_VINCULO)
                ->join('unidade u', "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                ->where("u.idempresa = ".cb::idempresa());

        // Todas consultas restringem à unidade com cb::idempresa()
        switch($tipoobjeto)
        {
            case 'sgconselho':
                $subQueryBuilder  = new Builder();
                $subQueryBuilder2 = new Builder();

                $queryBuilderUnion2 = new Builder();
                $queryBuilderUnion3 = new Builder();

                // Unidades das areas de um conselho
                $queryUnion = $queryBuilderUnion
                                ->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, ".self::TABLE_VINCULO.".tipoobjeto, ".self::TABLE_VINCULO.".idunidadeobjeto")
                                ->from(self::TABLE_VINCULO)
                                ->join('unidade u', 'u.idunidade = '.self::TABLE_VINCULO.'.idunidade')
                                ->join('sgarea a', "a.idsgarea = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto = 'sgarea'")
                                ->join('objetovinculo ov', "ov.tipoobjeto = 'sgconselho' and ov.tipoobjetovinc = 'sgarea' AND ov.idobjetovinc = a.idsgarea")
                                ->where("u.idempresa = ".cb::idempresa())
                                ->where("ov.idobjeto = $idobjeto")
                                ->where("a.status = 'ATIVO'")
                                ->where("u.status = 'ATIVO'");
                
                // Buscar todas as areas para q seja buscado as unidades de seus respectivos departamentos
                $subQuery = $subQueryBuilder
                                ->select("u.idunidade, u.unidade, unidadeobjeto.idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto ")
                                ->from(self::TABLE_VINCULO)
                                ->join('unidade u', 'u.idunidade = '.self::TABLE_VINCULO.'.idunidade')
                                ->join('sgarea a', "a.idsgarea = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto = 'sgarea'")
                                ->join('objetovinculo ov', "ov.tipoobjeto = 'sgconselho' and ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea'")
                                ->where("u.idempresa = ".cb::idempresa())
                                ->where("ov.idobjeto = $idobjeto")
                                ->where("a.status = 'ATIVO'")
                                ->where("u.status = 'ATIVO'");

                // Unidades dos departamentos das areas de um conselho
                $queryUnion2 = $queryBuilderUnion2
                                ->select("u.idunidade, u.unidade, uo.idobjeto, uo.tipoobjeto, uo.idunidadeobjeto")
                                ->from("($subQuery->query) as qry")
                                ->join("objetovinculo ov", "ov.idobjeto = qry.idobjeto and ov.tipoobjeto = 'sgarea' and ov.tipoobjetovinc = 'sgdepartamento'")
                                ->join("unidadeobjeto uo", "uo.idobjeto = ov.idobjetovinc and uo.tipoobjeto = 'sgdepartamento'")
                                ->join("unidade u", "u.idunidade = uo.idunidade")
                                ->where("u.idempresa = ".cb::idempresa());

                // Unidades dos setores dos departamentos das areas de um conselho
                $subQuery2 = $subQueryBuilder2
                                ->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, ".self::TABLE_VINCULO.".tipoobjeto, ".self::TABLE_VINCULO.".idunidadeobjeto ")
                                ->from(self::TABLE_VINCULO)
                                ->join("unidade u", "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                                ->join("sgarea a", "a.idsgarea = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto = 'sgarea'")
                                ->join("objetovinculo ov", "ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea' AND ov.tipoobjeto = 'sgconselho'")
                                ->where("ov.idobjeto = $idobjeto")
                                ->where("u.idempresa = ".cb::idempresa())
                                ->where("a.status = 'ATIVO'")
                                ->where("u.status = 'ATIVO'");

                $queryUnion3 = $queryBuilderUnion3
                                ->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, ".self::TABLE_VINCULO.".tipoobjeto, ".self::TABLE_VINCULO.".idunidadeobjeto")
                                ->from("($subQuery2->query) as qry")
                                ->join("objetovinculo ov", "ov.idobjeto = qry.idobjeto and ov.tipoobjeto = 'sgarea' and ov.tipoobjetovinc = 'sgdepartamento'")
                                ->join("objetovinculo ov2", "ov2.idobjeto = ov.idobjetovinc AND ov2.tipoobjetovinc = 'sgsetor' AND ov2.tipoobjeto = 'sgdepartamento'")
                                ->join("sgsetor s", "s.idsgsetor = ov2.idobjetovinc")
                                ->join(self::TABLE_VINCULO, self::TABLE_VINCULO.".idobjeto = s.idsgsetor and ".self::TABLE_VINCULO.".tipoobjeto = 'sgsetor'")
                                ->join("unidade u", "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                                ->where("u.idempresa = ".cb::idempresa())
                                ->where("s.status = 'ATIVO'");

                $query->join('sgconselho', "$tipoobjeto.idsgconselho = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto='sgconselho'")
                        ->union($queryUnion)
                        ->union($queryUnion2)
                        ->union($queryUnion3);

                break;
            case 'sgarea':
                $subQueryBuilder = new Builder();
                $queryBuilderUnion2 = new Builder();

                // Pega unidades de todos os setores abaixo de todos os departamentos a baixo de uma area
                $subQuery = $subQueryBuilder
                            ->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, ".self::TABLE_VINCULO.".tipoobjeto, ".self::TABLE_VINCULO.".idunidadeobjeto")
                            ->from(self::TABLE_VINCULO)
                            ->join('unidade u', "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                            ->join('sgdepartamento sgdep', "sgdep.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgdepartamento'")
                            ->join('objetovinculo ov', "ov.idobjetovinc = sgdep.idsgdepartamento AND ov.tipoobjetovinc = 'sgdepartamento' AND ov.tipoobjeto = 'sgarea'")
                            ->where("ov.idobjeto = $idobjeto")
                            ->where("u.idempresa = ".cb::idempresa());

                $queryUnion = $queryBuilderUnion
                                    ->select("u.idunidade, u.unidade, uo.idobjeto, uo.tipoobjeto, uo.idunidadeobjeto")
                                    ->from("($subQuery->query) as qry")
                                    ->join('objetovinculo ov', "ov.idobjeto = qry.idobjeto and ov.tipoobjeto = 'sgdepartamento' and ov.tipoobjetovinc = 'sgsetor'")
                                    ->join('unidadeobjeto uo', "uo.idobjeto = ov.idobjetovinc and uo.tipoobjeto = 'sgsetor'")
                                    ->join('unidade u', 'u.idunidade = uo.idunidade')
                                    ->where("u.idempresa = ".cb::idempresa());

                // Pega todos as unidades de todos os departamentos abaixo de uma area
                $queryUnion2 = $queryBuilderUnion2
                                ->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, ".self::TABLE_VINCULO.".tipoobjeto, ".self::TABLE_VINCULO.".idunidadeobjeto")
                                ->from(self::TABLE_VINCULO)
                                ->join("unidade u", "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                                ->join("sgdepartamento sgdep", "sgdep.idsgdepartamento = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto = 'sgdepartamento'")
                                ->join("objetovinculo ov", "ov.tipoobjeto = 'sgarea' AND ov.idobjetovinc = sgdep.idsgdepartamento AND ov.tipoobjetovinc = 'sgdepartamento'")
                                ->where("u.idempresa = ".cb::idempresa())
                                ->where("ov.idobjeto = $idobjeto")
                                ->where("sgdep.status = 'ATIVO'")
                                ->where("u.status = 'ATIVO'")
                                ->groupBy(self::TABLE_VINCULO.".idunidade");

                $query->join('sgarea', "$tipoobjeto.idsgarea = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto='sgarea'")
                        ->union($queryUnion2)
                        ->union($queryUnion);

                break;
            case 'sgdepartamento':
                $queryUnion = $queryBuilderUnion->select("u.idunidade, u.unidade, ".self::TABLE_VINCULO.".idobjeto, 'sgdepartamento',".self::TABLE_VINCULO.".idunidadeobjeto")
                                                ->from(self::TABLE_VINCULO)
                                                ->join('unidade u', "u.idunidade = ".self::TABLE_VINCULO.".idunidade")
                                                ->join('sgsetor s', "s.idsgsetor = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto = 'sgsetor'")
                                                ->join('objetovinculo ov', "ov.idobjetovinc = s.idsgsetor and ov.tipoobjetovinc = 'sgsetor' and ov.tipoobjeto = 'sgdepartamento'")
                                                ->where("ov.idobjeto = $idobjeto")
                                                ->where("u.idempresa = ".cb::idempresa());

                $query->join('sgdepartamento', "$tipoobjeto.idsgdepartamento = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto='sgdepartamento'")
                        ->union($queryUnion);

                break;
            case 'sgsetor':
                $query->join('sgsetor', "$tipoobjeto.idsgsetor = ".self::TABLE_VINCULO.".idobjeto AND ".self::TABLE_VINCULO.".tipoobjeto='sgsetor'")
                        ->where("sgsetor.status = 'ATIVO'")
                        ->where("u.idempresa = ".cb::idempresa());;
                break;
        }

        if($idobjeto)
        {
            $query->where(self::TABLE_VINCULO.".idobjeto = $idobjeto");
        }

        $query->where(self::TABLE_VINCULO.".tipoobjeto = '$tipoobjeto'");

        $queryDistinctBuilder = new Builder();

        $queryDistinct = $queryDistinctBuilder
                            ->select("qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto")
                            ->from("(".$query->getQuery().") as qry_distinct")
                            ->groupBy("qry_distinct.idunidade");

        $result = $queryDistinct->get();

        while($item=mysql_fetch_assoc($result))
        {
            $arr[$i]['idunidade'] = $item['idunidade'];
            $arr[$i]['unidade']   = $item['unidade'];
            $arr[$i]['idobjeto'] = $item['idobjeto'];
            $arr[$i]['tipoobjeto'] = $item['tipoobjeto'];
            $arr[$i]['idunidadeobjeto'] = $item['idunidadeobjeto'];

            $i++;
        }

        if(!count($arr))
        {
            $arr[$i]['error'] = 'Nenhum resultado encontrado!';
        }

        return $arr;
    }

    public function getBySgConselho($idsgconselho = null)
    {
        return $this->getByTipo($idsgconselho, 'sgconselho');
    }

    public function getBySgArea($idsgarea = null)
    {
        return $this->getByTipo($idsgarea, 'sgarea');
    }

    public function getBySgDepartamento($idsgdepartamento = null)
    {
        return $this->getByTipo($idsgdepartamento, 'sgdepartamento');
    }

    public function getBySgSetor($idsgsetor = null)
    {
        return $this->getByTipo($idsgsetor, 'sgsetor');
    }

    function listaUnidades($unidades, $idobjeto = null, $tipoobjeto = null)
    {
        $title = 'Editar Unidade';
        $trStart = '<tr>';
        $trEnd   = '</tr>';

        foreach($unidades as $unidade)
        {
            if(isset($unidade['error']))
            {
                echo "  <tr>
                            <td class='py-3'>{$unidade['error']}</td>
                        </tr>";

                break;
            }

            $trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>";

            if(($idobjeto == null && $tipoobjeto == null) || (($idobjeto && $tipoobjeto) && ($unidade['idobjeto'] == $idobjeto && $unidade['tipoobjeto'] == $tipoobjeto)) || ($tipoobjeto && $tipoobjeto == 'sgsetor'))
            {
                $trContent .= " <td align='center'>
                                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidade(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
                                </td>";
                
                $trContent = str_replace("colspan='2'", '', $trContent);
            }

            $tr = " $trStart
                        $trContent
                    $trEnd";

            echo $tr;
        }
    }
}