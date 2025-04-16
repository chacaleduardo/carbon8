<?
require_once("../inc/php/validaacesso.php");

$idrhfolha= $_GET['idrhfolha'];



$sf="select DATE_ADD(LAST_DAY(f.datafim - INTERVAL 1 MONTH), INTERVAL 1 DAY) as dataincio, f.* from rhfolha f where f.idrhfolha=".$idrhfolha ;

//echo($sf);
$rf=d::b()->query($sf) or die("Erro ao buscar folha: " . mysqli_error(d::b()) . "<p>SQL:".$sf);  
$rwf=mysqli_fetch_assoc($rf);
if($rwf['tipofolha']=='FOLHA' or $rwf['tipofolha']=='FOLHA FERIAS' or  $rwf['tipofolha']=='DECIMO TERCEIRO 2'){

    if($rwf['tipofolha']=='FOLHA'){
        $strtipo=" and (t.flgfolha='Y'  or t.flgfixo='Y'  ) ";
    }elseif($rwf['tipofolha']=='FOLHA FERIAS'){
        $strtipo=" and t.flgferias ='Y' ";      
     }else{
         $strtipo=" and t.flgdecimoterc2 ='Y' ";
     }	


    $sp="select c.tipo,i.* 
            from rhfolhaitem i 
                join pessoa p on(p.idpessoa=i.idpessoa and p.contrato='CLT' ) 
                left join sgcargo c on(c.idsgcargo = p.idsgcargo )
            where i.idrhfolha = ".$rwf['idrhfolha'];
    $rp=d::b()->query($sp) or die("Erro ao funcionarios da folha: " . mysqli_error(d::b()) . "<p>SQL:".$sp);  
    while($rwpes=mysqli_fetch_assoc($rp)){

        $sql="select  
                    e.valor,e.idrhevento,e.idrhtipoevento,e.dataevento,t.irrf,t.fgts,t.inss,t.tipo
                from rhevento e 
                join rhtipoevento t on(t.idrhtipoevento = e.idrhtipoevento and t.status='ATIVO' ".$strtipo." and (t.irrf='Y' or t.fgts='Y' or t.inss='Y'))
                join pessoa p on(p.idpessoa=e.idpessoa)
                where e.idpessoa = ".$rwpes['idpessoa']."
                and e.status='PENDENTE'
                 and dataevento between '".$rwf['dataincio']."' and '".$rwf['datafim']."'";
        $res=d::b()->query($sql) or die("Erro ao funcionarios da folha: " . mysqli_error(d::b()) . "<p>SQL:".$sql);  
        $inss=0;
        $fgts=0;
        $irrf=0;
        while($row=mysqli_fetch_assoc($res)){

            if($row['tipo']=='D'){//tipo debito
                if($row['inss']=='Y'){
                    $inss= $inss-$row['valor'];
                }
                if($row['fgts']=='Y'){
                    $fgts= $fgts-$row['valor'];
                }
                if($row['irrf']=='Y'){
                    $irrf= $irrf-$row['valor'];
                }
            }else{//tipo credito
                if($row['inss']=='Y'){
                    $inss= $inss+$row['valor'];
                }
                if($row['fgts']=='Y'){
                    $fgts= $fgts+$row['valor'];
                }
                if($row['irrf']=='Y'){
                    $irrf= $irrf+$row['valor'];
                }
            }

        }

        if($rwf['tipofolha']=='FOLHA'){//idrhevento correspondente ao tipo folha
            $idevfgts=430;
            $idevinss=47;
            $idevirrf=48;

        }elseif($rwf['tipofolha']=='FOLHA FERIAS'){
            $idevfgts=0;
            $idevinss=37;
            $idevirrf=29;
                
         }else{//decimo terceiro
            $idevfgts=449;
            $idevinss=448;
            $idevirrf=447;
         }	
    

        if($fgts>0 and $rwf['tipofolha']!='FOLHA FERIAS'){
           
            $valor_fgts=calc_fgts($rwpes,$fgts,$idevfgts);
            atualizaevento($valor_fgts,$idevfgts,$rwf,$rwpes);
        }
       
        if($inss>0){              
                
            $valor_inss=calc_inss($rwpes,$inss,$idevinss);
            atualizaevento($valor_inss,$idevinss,$rwf,$rwpes);

        }  
        if($irrf>0){
            
            $valor_irrf=calc_irrf($rwpes,$irrf,$valor_inss,$idevirrf);
            atualizaevento($valor_irrf,$idevirrf,$rwf,$rwpes);
        }     

    }//while funcionarios   


}/// tipo folha

function atualizaevento($valor_ev,$idrhtipoevento,$rwf,$rwpes){
    $sei="select * from rhevento e where e.idrhtipoevento=".$idrhtipoevento." and e.status='PENDENTE' and e.idpessoa = ".$rwpes['idpessoa']." and e.dataevento between '".$rwf['dataincio']."' and '".$rwf['datafim']."'";
    $rei=d::b()->query($sei) or die("Erro ao atualizaevento: " . mysqli_error(d::b()) . "<p>SQL:".$sei);  
    $qtdinss=mysqli_num_rows($rei);
    if($qtdinss>0){
        $rwi=mysqli_fetch_assoc($rei);
        $idrhevento=$rwi['idrhevento'];
        $sup="update rhevento set valor='".$valor_ev."' where idrhevento =". $idrhevento;
        $reup=d::b()->query($sup);
    }else{

        $ins = new Insert();
        $ins->setTable("rhevento");
        $ins->idrhtipoevento = $idrhtipoevento;
        $ins->idrhfolha = $rwf['idrhfolha'];                                        
        $ins->idpessoa = $rwpes['idpessoa'];
        $ins->dataevento = $rwf['datafim'];
        $ins->valor = $valor_ev;             

        $idrhevento = $ins->save();  

    }
}

function calc_fgts($rwpes,$fgts,$idev){
    $valor_fgts=0;
    //Empregados: 8% sobre o valor apurado de todas as rubricas com incidência
    //Jovem Aprendiz: 2% sobre o valor apurado de todas as rubricas com incidência

    if($rwpes['tipo']=='JOVEM APRENDIZ'){$flag='Y';}else{$flag='N';}
    $sqlbc="select * from rhtipoeventobc where idrhtipoevento=".$idev." and menoraprendiz='".$flag."' order by todos,valinicio,acimade";
    $resbc=d::b()->query($sqlbc) or die("Erro ao buscar base de calculo de impostos");
    $rowbc=mysqli_fetch_assoc($resbc);
    $percentual = ($rowbc['percentual']/100);
    $valor_fgts=$percentual*$fgts;
    if($valor_fgts<10){$valor_fgts=0.00;}
    return $valor_fgts;
}
function calc_inss($rwpes,$inss,$idev){
    $valor_inss=0;

    /*Exemplo:
    Valor apurado: soma dos valores de todas as rubricas que tem incidência de INSS: R$ 3.000,00
    7,5% de R$ 1.212,00 (por seu salário ter ultrapassado a primeira faixa), que corresponde a uma contribuição de R$ 90,90; mais
    9% sobre R$ 1.215,34 (essa quantia se refere a diferença de valores da segunda faixa: R$ 2.427,35 – R$ 1.212,01, 
    uma vez que o salário da segurada ultrapassou esta faixa também), que corresponde a uma contribuição de R$ 109,38 mais
    12% sobre R$ 572,66 (valor que sobrou do salário da segurada após passar pelas duas faixas: R$ 3.000,00 – R$ 1.212,00 – R$ 1.215,34), 
    que corresponde a uma contribuição de R$ 68,72.
    Totalizando, R$ 90,90 + R$ 109,38 + R$ 68,72 = R$ 269,00 a ser descontado de INSS.
    */
    $sqlbc="select *  from rhtipoeventobc where  idrhtipoevento=".$idev." and (valfim < ".$inss." or valinicio < ".$inss.") order by todos,valinicio,acimade";
    $resbc=d::b()->query($sqlbc) or die("Erro ao buscar base de calculo de impostos inss");
        $valprox = $inss;
        while($rowbc=mysqli_fetch_assoc($resbc)){
            if($inss>=$rowbc['valfim']){
                if($rowbc['valinicio']>0){$rowbc['valinicio']=$rowbc['valinicio']+0.1;}

                $valcalc=$rowbc['valfim']-$rowbc['valinicio'];                
            }else{
                
                $valcalc=$inss-$rowbc['valinicio'];
            }

            $percentual=($rowbc['percentual']/100);
            $valor_inss=$valor_inss + ( $valcalc*$percentual);
        }

    if($valor_inss<10){$valor_inss=0.00;}

    
    return $valor_inss;

}

function calc_irrf($rwpes,$irrf,$valor_inss=0,$idev){
    $valor_irrf=0;

    $sde="SELECT po.idpessoa,p.idpessoa,po.idempresa, p.nome, po.idpessoaobjeto, po.idobjeto, po.tipo
                FROM pessoa p 
                    JOIN pessoaobjeto po ON po.idobjeto = p.idpessoa AND tipoobjeto = 'pessoa'
                    JOIN pessoaobjeto pd ON pd.idpessoa=po.idobjeto and pd.tipoobjeto = 'rhtipoevento' and pd.idobjeto=452
                WHERE p.idtipopessoa = 115  
                AND po.idpessoa =".$rwpes['idpessoa']." 
                and p.status='ATIVO'
            ORDER BY nome";

    $rdep=d::b()->query($sde) or die("Erro ao buscar dependentes sql=".$sde);
    $dependentes=mysqli_num_rows($rdep);
    if(empty($dependentes)){ $dependentes=0;}

    $svd="select max(dependente) as qtddep from rhtipoeventobc where idrhtipoevento=".$idev;
    $rvd=d::b()->query($svd) or die("Erro ao buscar valor por denpendente sql=".$svd);
    $rwdv=mysqli_fetch_assoc($rvd);

    $valordep=$rwdv['qtddep']*$dependentes;

    //Salário bruto - dependentes - INSS
    $basecalculo=$irrf - $valordep - $valor_inss;

    $sfa="select *  from rhtipoeventobc where idrhtipoevento=".$idev." and valinicio < ".$basecalculo." and valfim > ". $basecalculo;
    $rfa=d::b()->query($sfa) or die("Erro ao buscar faixa do irrf sql=".$sfa);
    $rwfa=mysqli_fetch_assoc($rfa);

    //Dedução para cada dependente: R$189,59
    //Imposto de Renda retido na fonte = [(Salário bruto - dependentes - INSS) X alíquota] - dedução
    $percentual = ($rwfa['percentual']/100);
    $valor_irrf=( $basecalculo *$percentual) - $rwfa['deduzir'];

    if($valor_irrf<10){$valor_irrf=0.00;}

    return $valor_irrf;    

}


?>