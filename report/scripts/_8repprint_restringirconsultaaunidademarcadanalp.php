<?
foreach($lpRep as $r)
{
    if($r['flgunidade']=='Y')
    {
        if($_iclausulas > 0){
            $_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
        }else{
            $_sqlresultado = getDbTabela($_tab).".". $_tab." where 1 ";
        }

        if($wIdempresa !== false)
        {
            if ($wIdempresa == '')
                $_sqlresultado .= " and idempresa = ".cb::idempresa()."";
            else
                $_sqlresultado .= " and idempresa in (".$wIdempresa.")";
        }

        $_sqlresultado .= " and exists (select 1 from vw8PessoaUnidade pu where pu.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']." and pu.idunidade = ".$_tab.".idunidade)";
        break;
    }
}
?>