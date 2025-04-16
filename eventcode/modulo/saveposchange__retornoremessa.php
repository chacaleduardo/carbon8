<?
require_once("../api/nf/index.php");

$arrsq = $_SESSION["arrpostbuffer"];

reset($arrsq);
//Insere os novos Fluxos
while (list($linha, $arrlinha) = each($arrsq)){
    while (list($ui, $arrui) = each($arrlinha)){
        while (list($tab, $arrsql) = each($arrui)){
            $idcontapagar = $_SESSION["arrpostbuffer"][$linha][$ui]["contapagar"]["idcontapagar"];
            $status = $_SESSION["arrpostbuffer"][$linha][$ui]["contapagar"]["status"];                    
        
            if($tab == "contapagar" && $status == "QUITADO" && !empty($idcontapagar)){
                $sql="select (c.valor-c.valorant) as valormulta,c.*
                    from contapagar c
                    where  (c.valor>c.valorant or  c.valor<c.valorant)
                    and c.tipoespecifico='AGRUPAMENTO' 
                    and c.idcontapagar=".$idcontapagar;
                $res = mysql_query($sql) or die("Falha ao verificar multa " . mysql_error() . "<p>SQL: $sql");
                $qtd = mysqli_num_rows($res);
                if($qtd>0){
                    $row=mysqli_fetch_assoc($res);
                    if($row['valormulta']>0){

                        $insnfcp[1]['status']='QUITADO';	
                        $insnfcp[1]['idpessoa']=$row['idpessoa'];
                        $insnfcp[1]['idagencia']=$row['idagencia'];
                        $insnfcp[1]['idcontapagar']=$row['idcontapagar'];
                        $insnfcp[1]['idobjetoorigem']=$row['idobjeto'];
                        $insnfcp[1]['tipoobjetoorigem']=$row['tipoobjeto'];
                        $insnfcp[1]['tipo']=$row['tipo'];
                        $insnfcp[1]['visivel']=$row['visivel'];
                        $insnfcp[1]['parcela']='1';
                        $insnfcp[1]['parcelas']='1';
                        $insnfcp[1]['datapagto']=$row['datareceb'];
                        $insnfcp[1]['valor']=$row['valormulta'];
                        $insnfcp[1]['ajuste']='Y';
                        $insnfcp[1]['obs']='Multa/Juros';
                        $insnfcp[1]['idformapagamento']=$row['idformapagamento'];	
    
                        $idnfcp=cnf::inseredb($insnfcp,'contapagaritem');
                        unset($insnfcp);
                    }
                }
                
            }
        }
    }
}