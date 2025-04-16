<?require_once("../inc/php/validaacesso.php");
$idrhfolha=$_GET['idrhfolha'];
$idpessoa=$_GET['idpessoa'];
$dataevento1=$_GET['dataevento1'];
$dataevento2=$_GET['dataevento2'];

$tipofolha=traduzid('rhfolha', 'idrhfolha', 'tipofolha', $idrhfolha); 
$statusfolha=traduzid('rhfolha', 'idrhfolha', 'status', $idrhfolha); 

	if($tipofolha=='FOLHA'){
		$strtipo=" (flgfolha='Y'  or flgfixo='Y'  ) ";
	}else{
		$strtipo=" flgferias ='Y' ";
	}	
   
	if($statusfolha=="FECHADA"){
		$stridrhfolha="  and e.idrhfolha=".$idrhfolha." and e.status='QUITADO'  ";
	}else{$stridrhfolha=" and e.status='PENDENTE'  ";}
   
	$sql ="select  e.idrhevento,t.evento,t.tipo,e.dataevento,e.hora,e.valor,e.parcelas,e.parcela,e.status,e.situacao,t.formato
                        from rhfolha f join 
                        rhevento e join rhtipoevento t on(  ". $strtipo." and t.status = 'ATIVO' and e.idrhtipoevento=t.idrhtipoevento )                        
                        where e.idpessoa = ".$idpessoa."
							".$stridrhfolha."                        
                        and f.idrhfolha=".$idrhfolha."
                        and e.dataevento <=  f.datafim order by e.dataevento,t.evento ";

	$res = d::b()->query($sql) or die("Erro ao buscar eventos: ".mysqli_error(d::b()));

        $nomecurto= traduzid('pessoa', 'idpessoa', 'nomecurto', $idpessoa);
?>
<html>
<head>
      <link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<title>Eventos</title>
</head>
<body>
	<!-- <?=$sql?> -->
<div class="col-md-8">
<div class="panel panel-default">
    <div class="panel-heading"> </div>
    <div class="panel-body">
        <table class="normal">
        <thead>
        <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
            <td colspan="7" style="font-size:11px;"  align="center"><?=$nomecurto?></td>
        </tr>
       <tr class="header">
           <td class="tdtit grrot">Evento</td>
                <td class="tdtit grrot">Data</td>
                <td class="tdtit grrot">Parcela</td>
                <td class="tdtit grrot">Parcelas</td>                
                <td class="tdtit grrot">Status</td>   
                <td class="tdtit grrot">Hora</td>                
                <td class="tdtit grrot">Valor</td>
        </tr>
        </thead>
        <tbody>
            <?
            $val=0;
            while($row=mysqli_fetch_assoc($res)){
                $sinal='';
                if($row['formato']=='D'){
                    if($row['tipo']!='I'){
                        if($row['tipo']=='C'){
                            $val=$val+$row['valor'];                       
                        }else{
                            $val=$val-$row['valor'];                         
                        }
                    }

                    if($row['tipo']=='D'){
                        $sinal='-';
                    }
                }
                
            ?>
            <tr class="res ">
                <td ><?=$row['evento']?></td>
                <td ><?=dma($row['dataevento'])?></td>
                <td ><?=$row['parcela']?></td>
                <td ><?=$row['parcelas']?></td>                
                <td ><?=$row['status']?></td>
              <?if($row['formato']=='D'){?>
                <td ></td>
                <td ><?=$sinal?> <?=$row['valor']?></td>
              <?}else{?>
                <td ><?=$sinal?> <?=$row['valor']?></td>
                <td ></td>
              <?}?>
            </tr>
            <?
            }
            ?>
            <tr class="res ">
                <td  colspan="6" >Total</td>
                <td><?=$val?></td>
            </tr>
        </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>