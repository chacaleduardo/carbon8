<?
require_once("builder.php");

class mapaequipamento
{
    const TABELA = 'mapaequipamento';

    public function getByTagId($id)
    {
        $queryBuilder = new Builder();

        $result = $queryBuilder
                    ->select('idmapaequipamento, json')
                    ->from(self::TABELA)
                    ->where("idtag = $id")
                    ->get();
        
        $arr = [];

        $arr['error'] = "Nenhum registro encontrado";
    
        if ($result->num_rows) {
            unset($arr['error']);
    
            $i = 0;
    
            while ($item = mysql_fetch_assoc($result)) {
                $arr[$i]['idmapaequipamento'] = $item['idmapaequipamento'];
                $arr[$i]['json'] = $item['json'];
    
                $i++;
            }
        }
    
        return $arr;
    }
}

?>