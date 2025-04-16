<?
require_once("../inc/php/validaacesso.php");

if($_POST){
    cbSetPostHeader('1','html');
}


// SELECT PARA VERIFICAR SE EXISTE M5 SELECIONADO PARA CALIBRAR
$sql = "SELECT d.iddevice,
               ds.iddevicesensor,
               dsb.iddevicesensorbloco,
               dsb.tipo,
               d.ip_hostname AS ip
          FROM device d  JOIN devicesensor ds ON ds.iddevice = d.iddevice
          JOIN devicesensorbloco dsb ON dsb.iddevicesensor = ds.iddevicesensor and dsb.tipo in ('d')
         WHERE d.subtipo = 'DIFERENCIAL'
           AND d.status = 'ATIVO'
           AND TIMESTAMPDIFF(MINUTE,reiniciadoem,NOW()) >= 1
           AND calibrar = 'Y'
      ORDER BY iddevice;";

// echo "<pre>".$sql."</pre>";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar leituras sql=".$sql);
$qtd=mysqli_num_rows($res);

// CASO TENHA M5 PARA CALIBRAR ARMAZENA NO REDIS 20 LEITURAS, REALIZA A MEDIA E TROCA O OFFSET DO M5
if($qtd>0){

    while($rowe=mysqli_fetch_assoc($res))
    {
        $entrou =true;
        echo '<br>';
        $valor = re::dis()->hGetAll('_estado:'.$rowe['iddevicesensorbloco'].':devicesensorbloco');    
        $retun = re::dis()->lpush('_calibracao:'.$rowe['iddevicesensorbloco'].':devicesensorbloco', $valor[$rowe['tipo']]);
        var_dump($retun);
        $arrnotif = re::dis()->lrange('_calibracao:'.$rowe['iddevicesensorbloco'].':devicesensorbloco',0,-1); // retorna tudo que está no array
        
        echo "<br>Device: ".$rowe['iddevice']." Iddevicesensorbloco: ".$rowe['iddevicesensorbloco']." Valor: ".$valor[$rowe['tipo']];
        echo '<br>';
        print_r($arrnotif);
        echo '<br>';
        if ($retun >= 20)
        {
            $media = 0;
            $mediaextremos = 0;
            $j=0;

            foreach ($arrnotif as $key=>$val) {
                $j++;
                $media = $media + $val;

                if(!isset($max)){
                    $max = $val;
                }else{
                    if ($val > $max) {
                        $max = $val;
        
                        echo "<br>Max: ".$max;
                    }
                }

                if(!isset($min)){
                    $min = $val;
                }else{
                    if ($val < $min) {
                        $min = $val;
        
                        echo "<br>Min: ".$min;
                    }
                }
            }
            $media = $media/$j;
            $mediaextremos = ($max+$min)/2;
            
            $media = $media * (-1);
            
            echo "<br>Device: ".$rowe['iddevice']." Iddevicesensorbloco: ".$rowe['iddevicesensorbloco'];
            echo "<br>Min: ".$min." Max: ".$max." Media Extremos: ".$mediaextremos." Media: ".$media;
            $sql = " update  
                        device d
                    JOIN
                        devicesensor ds ON ds.iddevice = d.iddevice
                    JOIN
                        devicesensorbloco dsb ON dsb.iddevicesensor = ds.iddevicesensor
                    SET 
                        dsb.offset = '".$media."', dsb.alteradoem=now(), dsb.alteradopor = 'sislaudo',
                        d.calibrar = 'N', d.alteradoem=now(), d.alteradopor = 'sislaudo',
                        d.iddeviceref = d.iddevicecal
                    where
                        dsb.tipo in ( 'p','d')
                        and d.iddevice = ".$rowe['iddevice'].";";
            mysql_query($sql) or die(mysql_error()." Erro ao atualizar offset ".$sql);

            echo "<br>";
            $retun = re::dis()->del('_calibracao:'.$rowe['iddevicesensorbloco'].':devicesensorbloco');
            ?>
            <script>
                $.ajax({
                    url: '../ajax/enviarequisicaom5.php',
                    type: 'POST',
                    data: {ip:'<?=$rowe['ip'];?>', status:"reiniciar"}
                }); 
            </script>
            <?
        }
        unset($min);
        unset($max);

    }


// CASO NÃO TENHA M5 SELECIONADO APARECE TODOS OS M5 PARA O USUARIO SELECIONAR
} else {
    ?>
    <html>
        <body>


        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: rgb(102, 102, 102); color: white;"><div class="col-md-12 mult_imp"><label style="float:left" class="alert-warning">LD</label></div>
                 Selecione os M5 para calibração:
                 <br>
                 Etapa: <?=$_REQUEST['etapa'];?>
                </div>
                <div class="panel-body"> 
                
        
              
                <?
                $sql="SELECT 
                            d.iddevice,
                            d.calibrar,
                            concat(s.descricao, ' (TAG-', t.tag, ')') as descricao,
                            ds.iddevicesensor,
                            dsb.iddevicesensorbloco,
                            dsb.tipo,
                            d.ip_hostname AS ip,
                            CONCAT(
                                LPAD(FLOOR(HOUR(TIMEDIFF(now(), d.reiniciadoem)) / 24),2,'0'), 'd ',
                                LPAD(MOD(HOUR(TIMEDIFF(now(), d.reiniciadoem)), 24),2,'0'), 'h ',
                                LPAD(MINUTE(TIMEDIFF(now(), d.reiniciadoem)),2,'0'), 'm ',
                                LPAD(SECOND(TIMEDIFF(now(), d.reiniciadoem)),2,'0'),'s'
                            ) as tempo,
                            concat(sr.descricao, ' (TAG-', tr.tag, ')') as descricaoreferencia
                        FROM
                            device d
                                JOIN
                            tag t ON t.idtag = d.idtag
                                JOIN
                            tagsala ts ON ts.idtag = t.idtag
                                JOIN
                            tag s ON s.idtag = ts.idtagpai
                                JOIN
                            devicesensor ds ON ds.iddevice = d.iddevice
                                JOIN
                            devicesensorbloco dsb ON dsb.iddevicesensor = ds.iddevicesensor
                                AND dsb.prioridade = 1
                                AND dsb.tipo = 'd'
                            join device dr on dr.iddevice= d.iddeviceref
                                JOIN
                            tag tr on tr.idtag = dr.idtag
                                JOIN
                            tagsala tsr ON tsr.idtag = tr.idtag
                                JOIN
                            tag sr ON sr.idtag = tsr.idtagpai
                        WHERE
                            d.subtipo = 'DIFERENCIAL'
                                AND d.status = 'ATIVO'
                            
                        ORDER BY d.ordem, s.descricao ASC";
                $res = d::b()->query($sql) or die("A Consulta dos devices falharam : " . mysqli_error() . "<p>SQL: $sql");
                //echo($sql);
                $qtd=mysqli_num_rows($res);
                if($qtd>0){
                    while($row = mysql_fetch_assoc($res)){
                        if($row["calibrar"] =='Y'){
                            $calibrar ='N';
                            $checkedob="checked";
                        }else{
                            $calibrar='Y';
                            $checkedob="";
                        }
                        ?>
                        <div class="row">
                            
                            <div class="col-md-3">
                                <input id="<?=$row["iddevice"]?>" type="checkbox">
                                <a href="/?_modulo=device&amp;_acao=u&amp;iddevice=<?=$row["iddevice"]?>" target="_blank"><?=$row["descricao"]?></a>
                            </div>
                            
                            <div class="col-md-3">
                                <?=$row["descricaoreferencia"]?>
                            </div>
                            <div class="col-md-2">
                               
                               <?=$row["ip"]?>
                            </div>
                            <div class="col-md-2">
                               
                               <?=$row["tempo"]?>
                            </div>
							 <div class="col-md-1">
                               
                               <button class="btn btn-primary" id="btnp-<?=$row["iddevice"]?>" onclick="prepararDevice(<?=$row["iddevice"]?>,'<?=$row["ip"]?>')">P</button>
                               <button class="btn btn-primary" id="btnv-<?=$row["iddevice"]?>" onclick="verificarDevice(<?=$row["iddevice"]?>)">V</button>
                            </div>
							 <div class="col-md-1">
                               
                               <span id="visual-<?=$row["iddevice"]?>"> - </span>
                               <span id="prepare-<?=$row["iddevice"]?>"> - </span>
                            </div>
                            
                        </div>
                        <?
                    }
                }
                ?>
                    <div><button class="btn btn-primary" id="getChecked">Calibrar</button></div> 
                    
                </div>
            </div>
        </div>

        
           
            
        </body>
    </html>
    <?
}

if ($entrou == true){
    echo "<Br>Fim: ".date("d/m/Y H:i:s", time()).'<br>'; 
    ?>
    <script>
        setTimeout(function(){window.location.reload(1);}, 5000);
    </script>
    <?
    header("Refresh:5");
}

?>

<script>

if(document.getElementById("getChecked")){
    document.getElementById("getChecked").onclick = getChecked;
}


function getChecked()  {
    inputs = document.querySelectorAll("input[type='checkbox']");
    ids = [];
    for (let input of inputs) {
        if (input.checked) ids.push(parseInt(input.id)) 
    }
    CB.post({
        parcial:true,
        refresh:'refresh',
        objetos: {
            "ids": ids
        },
    });
    console.log(ids);
}

function prepararDevice(iddevice,ipdevice)  {
	$("#btnp-"+iddevice).removeClass('btn-primary');
    $("#btnp-"+iddevice).addClass('btn-success');
    $.ajax({
    	url: '../ajax/devicecalib.php',
    	type: 'POST',
    	data: {iddevice:iddevice},
    	beforesend: function(){
    		$("#visual-"+iddevice).css({'display':'inline'});
    		$("#visual-"+iddevice).html("Carregando...");
    	},
    	success: function(data){
    		$("#visual-"+iddevice).html("OK");
    		$.ajax({
                url: '../ajax/enviarequisicaom5.php',
                type: 'POST',
                data: {ip:ipdevice, status:"reiniciar"}
            }); 

    	},
    	error: function(data){
    		$("#visual-"+iddevice).css({'display':'inline'});
    		$("#visual-"+iddevice).html("Erro ao carregar");
    	}
    }); 
}

function verificarDevice(iddevice)  {
	$("#btnv-"+iddevice).removeClass('btn-primary');
    $("#btnv-"+iddevice).addClass('btn-success');
    $.ajax({
    	url: '../ajax/devicestatus.php',
    	type: 'POST',
    	data: {iddevice:iddevice},
    	beforesend: function(){
    		$("#prepare-"+iddevice).css({'display':'inline'});
    		$("#prepare-"+iddevice).html("Carregando...");
    	},
    	success: function(data){
    		$("#prepare-"+iddevice).html(data);
    	},
    	error: function(data){
    		$("#prepare-"+iddevice).css({'display':'inline'});
    		$("#prepare-"+iddevice).html("Erro ao carregar");
    	}
    }); 
}
</script>



    