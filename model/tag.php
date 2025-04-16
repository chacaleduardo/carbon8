<?
require_once("../model/builder.php");

class TAG
{
  const TABLE = 'tag';

  function getListaTag()
  {
    $sqlm = "SELECT idtagtipo, tagtipo
                   FROM tagtipo
                  WHERE status='ATIVO'
                    AND idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . "
               ORDER BY tagtipo;";
    $resm =  d::b()->query($sqlm)  or die("Erro tagtipo campo Prompt sql model/tag.php:" . $sqlm);
    return $resm;
  }

  //Retorna as Tags utilizadas - Nativo
  function getListaTagObjetos($idevento, $ideventotipoadd)
  {
    $sqo = "SELECT concat(t.tag,' - ',t.descricao) as descobj,e.* 
                FROM eventoobj e JOIN tag t ON(t.idtag=e.idobjeto)
               WHERE e.idevento=" . $idevento . "
                 AND  e.ideventoadd=" . $ideventotipoadd . "
                 AND e.objeto='tag' 
            UNION
              SELECT concat(t.tag,' - ',t.descricao) as descobj,e.* 
                FROM eventoobj e JOIN tag t ON(t.idtag=e.idobjeto)
               WHERE e.idevento=" . $idevento . "
                 AND  e.ideventoadd=" . $ideventotipoadd . "
                 AND e.objeto='tag' 
            UNION            
            SELECT concat(t.nome) as descobj,e.* 
              FROM eventoobj e JOIN pessoa t ON(t.idpessoa=e.idobjeto)
             WHERE e.idevento=" . $idevento . "
               AND e.ideventoadd=" . $ideventotipoadd . "
               AND e.objeto='pessoa'         
            UNION            
            SELECT concat(t.titulo) as descobj,e.* 
              FROM eventoobj e JOIN sgdoc t ON(t.idsgdoc=e.idobjeto)
             WHERE e.idevento=" . $idevento . "
               AND  e.ideventoadd=" . $ideventotipoadd . "
               AND e.objeto='sgdoc' 							
            UNION            
            SELECT concat(IFNULL(p.descrcurta,p.descr)) as descobj,e.* 
              FROM eventoobj e JOIN prodserv p ON(p.idprodserv=e.idobjeto)
             WHERE e.idevento=" . $idevento . "
               AND  e.ideventoadd=" . $ideventotipoadd . "
               AND e.objeto='prodserv' 							
          ORDER BY ord, descobj";
    $res = d::b()->query($sqo) or die("erro ao buscar os getListaTagObjetos do evento model/tag.php: " . mysqli_error(d::b()));
    return $res;
  }


  //Retorna a Tag do Evento - Nativo
  function getTag($inidtagtipo)
  {
    global $JSON;
    //Aparecer somente as tags que estão diferentes do Status Inativo - Lidiane (12-02-2020)
    $sql = "SELECT 
                  t.idtag,
                  CONCAT(t.tag, ' - ', t.descricao) AS descrtag,
                  e.sigla,
                  e.idempresa AS idempp,
                  t.idempresa AS idemptag,
                  CONCAT(e.sigla, '-', t.idtag) AS siglatag
              FROM
                  tag t
                      JOIN
                  empresa e ON (t.idempresa = e.idempresa)
              WHERE
                  t.idtagtipo IN (" . $inidtagtipo . ")
                      AND t.status NOT IN ('INATIVO','ALOCADO')
              ORDER BY (t.tag * 1);";
    $res = d::b()->query($sql) or die("getClientes: Erro model/tag.php: " . mysqli_error(d::b()) . "\n" . $sql);

    $arrtmp = array();
    $i = 0;

    while ($r = mysqli_fetch_assoc($res)) {
      $arrtmp[$i]["value"] = $r["idtag"];
      $arrtmp[$i]["label"] = $r["descrtag"];
      $arrtmp[$i]["sigla"] = $r["sigla"];
      $i++;
    }
    return $JSON->encode($arrtmp);
  }

  function getTipoObjeto($idevento, $ideventotipoadd)
  {
    $sql = "SELECT eo.objeto, t.idtag, t.padraotempmin, t.padraotempmax
                FROM eventoobj eo JOIN tag t ON eo.idobjeto = t.idtag and eo.objeto = 'tag'
               WHERE ideventoadd = '$ideventotipoadd' AND idevento = '$idevento';";
    $res = d::b()->query($sql) or die("getClientes: Erro model/tag.php: " . mysqli_error(d::b()) . "\n" . $sql);
    $arrtmp = array();
    while ($r = mysqli_fetch_assoc($res)) {
      $arrtmp["objeto"] = $r["objeto"];
      $arrtmp[$r["idtag"]]["padraotempmin"] = $r["padraotempmin"];
      $arrtmp[$r["idtag"]]["padraotempmax"] = $r["padraotempmax"];
    }
    return $arrtmp;
  }

  public function getSemVinculo($toString = false)
  {
    $queryBuilder = new Builder();

    $query = $queryBuilder
      ->select("idtag, descricao")
      ->from(self::TABLE)
      ->where(function($query)
      {
        $query->where("idunidade = ''")
              ->orWhere("idunidade is null");
      })
      ->where("status = 'ATIVO'")
      ->groupBy("descricao");

    if ($toString) {
      return $query->toString()->get();
    }

    $result = $query->get();

    $arr = [];

    $arr['error'] = "Nenhum registro encontrado";

    if ($result->num_rows) {
      unset($arr['error']);

      $i = 0;

      while ($item = mysql_fetch_assoc($result)) {
        $arr[$i]['idunidade'] = $item['idunidade'];
        $arr[$i]['unidade']   = $item['unidade'];

        $i++;
      }
    }

    return $arr;
  }

  public function getEquipamentosByEmpresaUnidade($idUnidade, $idNotInTag = false, $toString = false)
  {
    $queryBuilder = new Builder();

    $query = $queryBuilder
      ->select(self::TABLE.".idtag, ".self::TABLE.".descricao")
      ->from(self::TABLE)
      ->join('unidade u', 'u.idunidade = '.self::TABLE.'.idunidade')
      ->where(self::TABLE.".idtagclass = 1")
      ->where(self::TABLE.".idempresa = ".cb::idempresa())
      ->whereIn('u.idunidade', $idUnidade);

    if($idNotInTag)
    {
      $query->whereNotIn('idtag', $idNotInTag);
    };

    $query->where("u.status = 'ATIVO'")
      ->groupBy(self::TABLE.".idtag")
      ->orderBy(self::TABLE.".descricao");

    if ($toString) {
      return $query->toString()->get();
    }

    $result = $query->get();

    $arr = [];

    $arr['error'] = "Nenhum registro encontrado";

    if ($result->num_rows) {
      unset($arr['error']);

      $i = 0;

      while ($item = mysql_fetch_assoc($result)) {
        $arr[$i]['idunidade'] = $item['idunidade'];
        $arr[$i]['unidade']   = $item['unidade'];

        $i++;
      }
    }

    return $arr;
  }

  public function getBlocos($toString = false)
  {
    $queryBuilder = new Builder();


    $query = $queryBuilder
                ->select('t.idtag, CONCAT(e.sigla, " - ", t.descricao) as descricao, a.caminho')
                ->from('tag t')
                ->join('empresa e', 'e.idempresa = t.idempresa')
                ->leftJoin('arquivo a', "t.idtag = a.idobjeto AND a.tipoobjeto = 'tagplanta'")
                ->where("t.idtagclass = 13")
                ->where("t.status = 'ATIVO'");

    if(cb::idempresa() != 8)
    {
        $query->where("t.idempresa = ".cb::idempresa());
    }

    $query->groupBy("t.idtag");

    if ($toString) {
      return $query->toString()->get();
    }

    $result = $query->get();

    $arr = [];

    $arr['error'] = "Nenhum registro encontrado";

    if ($result->num_rows) {
      unset($arr['error']);

      $i = 0;

      while ($item = mysql_fetch_assoc($result)) {
        $arr[$i]['idtag'] = $item['idtag'];
        $arr[$i]['descricao'] = $item['descricao'];
        $arr[$i]['caminho']   = $item['caminho'];

        $i++;
      }
    }

    return $arr;
  }

  public function getUnidadesByEmpresa($toString = false)
  {
    $queryBuilder = new Builder();

    $query = $queryBuilder
                ->select('GROUP_CONCAT("u.idunidade") as idunidadeempresa')
                ->from('arquivo a')
                ->join('tag t', "t.idtag = a.idobjeto AND a.tipoobjeto = 'tagplanta'")
                ->join('unidade u', "t.idempresa = u.idempresa")
                ->where("t.idempresa = ".cb::idempresa());

    if ($toString) {
      return $query->toString()->get();
    }

    $result = $query->get();

    $arr = [];

    $arr['error'] = "Nenhum registro encontrado";

    if ($result->num_rows) {
      unset($arr['error']);

      while ($item = mysql_fetch_assoc($result)) {
        $arr['idunidadeempresa'] = $item['idunidadeempresa'];
      }
    }

    return $arr;
  }

  public function  getPlantas($id)
  {
    $queryBuilder = new Builder();

    $result = $queryBuilder
                ->select('p.idplanta', 'p.caminho', 't.descricao')
                ->from('planta p')
                ->join('tag t', "t.idtag = p.idtag")
                ->where("p.idtag = $id")
                ->get();

    $arr = [];

    $arr['error'] = "Nenhum registro encontrado";

    if ($result->num_rows) {
      unset($arr['error']);

      $i = 0;

      while ($item = mysql_fetch_assoc($result)) {
        $arr[$i]['idplanta'] = $item['idplanta'];
        $arr[$i]['caminho']   = $item['caminho'];

        $i++;
      }
    }

    return $arr;
  }
}
