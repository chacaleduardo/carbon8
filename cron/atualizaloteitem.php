<?//maf270320: Colocar a reconfiguracao da arvore de protuso e insumos em modo background

ini_set("display_errors","1");
error_reporting(E_ALL);
 
if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/laudo.php");
  //include_once("/var/www/carbon8/model/prodserv.php");
 include_once("/var/www/carbon8/api/prodserv/index.php");
}else{//se estiver sendo executado via requisicao htt
  
    include_once("../inc/php/functions.php");
    include_once("../inc/php/laudo.php");
    //include_once("../model/prodserv.php");
    require_once("../api/prodserv/index.php");
 }
 
 $atualizaf=$_REQUEST['atualizaf'];
 //se for o ultimo dia ou dia 15 do mês ele deve atualizar todas as formulas



$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'atualizavlrcustoprodserv', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


/*

//atualizar valor dos lotes produzidos
$sql = "select l.idlote,ifnull(l.qtdprod,1) as qtdprod 
from lote l 
 where l.idprodservformula is not null
    and l.exercicio >= year(DATE_SUB(now(), INTERVAL 1 year)) 
  and not exists (select 1 from loteitem li where li.idlote = l.idlote)
  and l.status in ('APROVADO','LIBERADO','QUARENTENA','CANCELADO','REPROVADO') order by l.alteradoem asc";
echo "<pre>".$sql."</pre>";
$res= d::b()->query($sql);
//$sqlupd = "";

while($row=mysqli_fetch_assoc($res)){ 

  //  $sqld="delete from loteitem where idlote=".$row["idlote"];
 //   d::b()->query($sqld);
 
  $valorlote=buscavalor_lote($row["idlote"],1,'Y',$row["idlote"]);
	//$vlr=$valorlote/$row['qtdprod'];
 // $sqlupd .="update lote set vlrlote = '".$vlr."' WHERE idlote = ".$row["idlote"].";\n";
    

}

echo "<pre>".$sqlupd."</pre>";
//$resupd = d::b()->query($sqlupd) or die(mysqli_error());
$resupd = d::b()->multi_query($sqlupd) or die();


*/

 
$sql = "SELECT f.idprodservformula, f.idprodserv from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv and p.status='ATIVO')
WHERE f.status='ATIVO' AND p.idempresa !=4";
echo "<pre>".$sql."</pre>";
$res= d::b()->query($sql);
$sqlupdf = "";

while($row=mysqli_fetch_assoc($res)){ 
 // $vlr = $prodservclass->busca_valor_formula($row["idprodservformula"]);

  $sqld="delete from prodservformulaitem where idprodservformula=".$row["idprodservformula"];
  d::b()->query($sqld);

  $valoritem=0;
  $vlr=prodformulaitem($row["idprodservformula"],$row["idprodservformula"],1,'N');
 // $prodservclass->valoritem=0;

  
}
 echo('Fim!!!');


$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'atualizavlrcustoprodserv', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
echo "<pre>".$sqli."</pre>";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


function buscavalor_lote($inidlote,$percentual,$zerar,$idlotepai, $lvl = 0){
  if($zerar=='Y'){
      global  $valor;
      $valor = 0;
  }else{
     global $valor;
  }
 /* $sql="select l.idlote,c.qtdd,ifnull(l.vlrlote,0) as vlrlote, 
          CASE
              WHEN l.qtdprod  < 1 THEN 1   
              ELSE l.qtdprod 
          END as qtdproduzido,
          l.idprodservformula,p.comprado,p.fabricado,p.descr
          from lotecons c 
          join lote l on(l.idlote= c.idlote) 
          join prodserv p on(p.idprodserv=l.idprodserv)  
       where c.idobjeto =".$inidlote." and c.tipoobjeto ='lote' and c.status!='INATIVO' and c.qtdd>0";
       */

      $sql="select 
            idloteinsumo as idlote,idempresa,idprodserv,qtdd,qtdd_exp, vlrlote, qtdproduzido,idprodservformula,comprado,fabricado,descr,unpadrao,partida,descr  
        from vw8LoteConsInsumo   
        where idlote=".$inidlote;
        
  $res= d::b()->query($sql);
  $valorc=0;
  while($row=mysqli_fetch_assoc($res)){
      if($row['fabricado']=='Y'){     
          $percentualcon=$row['qtdd']/$row['qtdproduzido'];
          $percent=$percentual*$percentualcon;

          $valorcpf=($row['vlrlote']*$row['qtdd'])*$percentual; 
          $qtdcons = ($row['qtdd']);

          $sqli="INSERT INTO loteitem
            (idempresa,idlote,idloteins,idprodserv,qtd,qtd_exp,un,valorun,valortotal,partida,descr,nivel,fabricado,criadopor,criadoem)
            VALUES
            (".$row['idempresa'].",".$idlotepai.",".$row['idlote'].",".$row['idprodserv'].",'".$qtdcons."','".$row['qtdd_exp']."','".$row['unpadrao']."','".$row['vlrlote']."','".$valorcpf."','".$row['partida']."','".$row['descr']."','".$lvl ."','Y','cron',now());";

           // echo($sqli);
          $resf=d::b()->query($sqli);



          $valorform=buscavalor_lote($row['idlote'],$percent,'N',$idlotepai,$lvl + 1);
         // echo($row['idlote']." ". $valorlote."<br>");
         //$valorf =$valorf + (( $valorform/$row['qtdprod']) * $row['qtdd']);                 
      }elseif($row['fabricado']=='N' and $row['vlrlote']>0){
         
          $valorcp=($row['vlrlote']*$row['qtdd'])*$percentual;              	 
         // $valoritem=$valoritem+$valor;
          $valorc=$valorc+$valorcp;
          // echo($valoritem.'<br>');
          $qtdcons = ($row['qtdd'] * $percentual);

          $sqli="INSERT INTO loteitem
          (idempresa,idlote,idloteins,idprodserv,qtd,qtd_exp,un,valorun,valortotal,partida,descr,nivel,fabricado,criadopor,criadoem)
          VALUES
          (".$row['idempresa'].",".$idlotepai.",".$row['idlote'].",".$row['idprodserv'].",'".$qtdcons."','".$row['qtdd_exp']."','".$row['unpadrao']."','".$row['vlrlote']."','".$valorcp."','".$row['partida']."','".$row['descr']."','".$lvl ."','N','cron',now());";
        //   echo($sqli);
        $resf=d::b()->query($sqli);
      }                    
  }//while($row=mysqli_fetch_assoc($res)){   
      $valor=$valor+  ($valorc);    
  return $valor;

}//function buscarvalorform($inidprodservformula,$inidplantel){





 function prodformulaitem($inidprodservformulapai,$inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0)
    {
        global $excel;

        if ($lvl > 0) {
            $m = $lvl * 15;
            $margin = "margin-left:".$m."px;";
        } else {
            $margin = "";
        }
        global $valoritem;
        if($lvl == 0){
            $valoritem = 0;
        }
        
        $sql = "SELECT * FROM (SELECT p.idempresa,
                                      i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                      p.un,
                                      fi.idprodservformula,
                                      IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
                                 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
                                WHERE f.idprodservformula = '$inidprodservformula' 
                         UNION SELECT p.idempresa,
                                      i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
                                      p.un,
                                      fi.idprodservformula,
                                      IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
                                 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
                                 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
                                WHERE f.idprodservformula = '$inidprodservformula'
                                  AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
                         UNION SELECT p.idempresa,
                                      i.idprodservformulains,
                                      i.qtdi,
                                      i.qtdi_exp,
                                      i.idprodserv,
                                      p.fabricado,
                                      p.descr,
                                      '' AS rotulo,
                                      p.un,
                                      NULL,
                                      IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc
                                 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
                                 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                                WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
                             GROUP BY idprodservformulains
                             ORDER BY fabricado";

        $res = d::b()->query($sql);
        
        while ($row = mysqli_fetch_assoc($res)) {
            $linha = $linha + 1;            

            // Concatena os contadores dos níveis para formar $nivel
            if($lvl == 0){
                cb::$session["nivel_old"] = $nivel;
                $nivel = $nivel + 1;
                $negritoInicial = '<b>';
                $negritoFinal = '</b>';
            } else {
                $arrayNivel = explode('.', $nivel);
                $contador = count($arrayNivel);
                if($lvl_old <> $lvl){
                    $nivel = $nivel.'.1';
                } else {
                    $arrayNivel[$contador - 1]++;
                    $nivel = implode('.', $arrayNivel);
                }
            }

            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {

              $valorQtd = tratanumero($row['qtdi'] * $percentagem);
                                 

              $sqli="INSERT INTO prodservformulaitem 
              (idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal )
              VALUES
              (".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','Y',0,0);";
              echo($sqli);
              $resf=d::b()->query($sqli);

                

              $lvl_old = $lvl;
              prodformulaitem($inidprodservformulapai,$row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old);
             
            } elseif ($row['fabricado'] == 'N') {
              $valor = buscavaloritem($row['idprodserv'], $row['qtdi']);

              $valorun = buscavalorloteprod($row['idprodserv'],1);
              $valor = $valor * $percentagem;
               
              $valorQtd = tratanumero($row['qtdi'] * $percentagem);

              $sqli="INSERT INTO prodservformulaitem 
              (idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal)
              VALUES
              (".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','N','". $valorun ."','". $valor ."');";
              echo($sqli);
              $resf=d::b()->query($sqli);

              $lvl_old = $lvl;
            }
        } //while($row=mysqli_fetch_assoc($res)){

        return  number_format(tratanumero($valoritem), 4, ',', '.');
    } //function buscarvalorform($inidprodservformula,$inidplantel){



    function  buscavalorloteprod($inidprodserv,$qtdi=1)
      {
  
          $sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
          from lote l 
          where l.idprodserv = ".$inidprodserv."  and vlrlote > 0   order by idlote desc limit 1";
          $res = d::b()->query($sql);
          $row = mysqli_fetch_assoc($res);
          $vlri=$row['valoritem']*$qtdi;
          $valor = round(($vlri), 4);
          return $valor;
      }

      function buscavaloritem($inidprodserv, $qtdi)
    {

        $sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
        from lote l 
        where l.idprodserv = ".$inidprodserv." and vlrlote > 0  order by idlote desc limit 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);
        $valor = round(($qtdi * $row['valoritem']), 4);
        return $valor;
    }
