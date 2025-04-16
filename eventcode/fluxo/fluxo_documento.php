<?


if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $problema = array();

    $sqls="SELECT  s.* from sgdoc s
            JOIN sgdoctipodocumento t ON (t.idsgdoctipodocumento = s.idsgdoctipodocumento)
            WHERE s.idsgdoc = '".$_idobjeto."' AND t.conferencia = 'Y' AND s.status = 'REVISAO' AND s.idempresa = 1";    
  
    $ress=d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql=".$sqls);
    $qtd=mysqli_num_rows($ress);
    $i=0;
    if($qtd>0){

        $sqlhist = "SELECT criadopor from (
                        SELECT fh.criadopor,(fh.criadoem)
                                            from fluxostatushist fh
                                            JOIN fluxostatus f on (f.idfluxostatus = fh.idfluxostatus)
                                        JOIN carbonnovo._status s ON (s.idstatus = f.idstatus and s.statustipo = 'REVISAO' and s.rotulo like 'REVISÃO%')
                                            WHERE
                                                fh.idmodulo= $_idobjeto
                                                AND fh.modulo= 'documento'
                                                AND fh.status= 'PENDENTE'
                                                ORDER BY fh.criadoem desc limit 1) a where criadopor = '".$_SESSION["SESSAO"]["USUARIO"]."';";
        $reshist=d::b()->query($sqlhist) or die("Erro ao buscar rateios da nota sql=".$sqlhist);
        $qtdhist=mysqli_num_rows($reshist);
        if($qtdhist>0){
            $escondebotao = 'Y';
            $problema[$i] = 'DOCUMENTOREVISAO';
            $i++;
        }else{
            $escondebotao = 'N';
            $i++;
        }
    }else{
        $escondebotao = 'N';
         $i++;
    } 
    
   
}

$status['permissao']['modulo'] = 'documento';
$status['permissao']['esconderbotao'] = $escondebotao;
$status['permissao']['status'] = 'APROVADO';
$status['permissao']['problema'] = $problema;
?>
