<?
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
?>

<script type="text/javascript">
function load() {
    setTimeout("window.open(self.location, '_self');", 60000);
}
</script>

<body onload="load()" class="xr_bgb0">

    <div class="xr_ap" id="xr_xr" style="width: 100%; top:0px; text-align: left;">
        <div>
            <select onchange="window.location='?'+this.value+'&r='+Math.random(0,9999999);"
                style="border:1px solid #333; padding:4px;border-radius:4px;margin-top:20px;">
                <option value="arq=supervisorio-blocod2&elemento=temperatura&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocod2' and $_GET['elemento']=='temperatura' ) echo 'selected' ;?>
                    >Bloco D - Temperatura Quartos e Câmaras</option>
                <option value="arq=supervisorio-blocog&elemento=temperatura&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocog' and $_GET['elemento']=='temperatura' ) echo 'selected' ;?>
                    >Bloco G - Temperatura Sala</option>
                <option value="arq=supervisorio-blocog&elemento=pressao&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocog' and $_GET['elemento']=='pressao' ) echo 'selected' ;?>>Bloco G
                    - Pressão Sala</option>
                <option value="arq=supervisorio-blocog&elemento=umidade&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocog' and $_GET['elemento']=='umidade' ) echo 'selected' ;?>>Bloco G
                    - Umidade Sala</option>
                <option value="arq=supervisorio-blocoh&elemento=temperatura&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoh' and $_GET['elemento']=='temperatura' ) echo 'selected' ;?>
                    >Bloco H - Temperatura Sala</option>
                <option value="arq=supervisorio-blocoh&elemento=pressao&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoh' and $_GET['elemento']=='pressao' ) echo 'selected' ;?>>Bloco H
                    - Pressão Sala</option>
                <option value="arq=supervisorio-blocoh&elemento=umidade&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoh' and $_GET['elemento']=='umidade' ) echo 'selected' ;?>>Bloco H
                    - Umidade Sala</option>
                <option value="arq=supervisorio-blocoi&elemento=temperatura&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoi' and $_GET['elemento']=='temperatura' ) echo 'selected' ;?>
                    >Bloco I - Temperatura Sala</option>
                <option value="arq=supervisorio-blocoi&elemento=pressao&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoi' and $_GET['elemento']=='pressao' ) echo 'selected' ;?>>Bloco I
                    - Pressão Sala</option>
                <option value="arq=supervisorio-blocoi&elemento=umidade&sala=<?=$_GET['sala']?>" <? if
                    ($_GET['arq']=='supervisorio-blocoi' and $_GET['elemento']=='umidade' ) echo 'selected' ;?>>Bloco I
                    - Umidade Sala</option>


            </select>
        </div>

        <? 
if($_GET['arq']){
    $arq = $_GET['arq'];
}else{
	$arq = 'supervisorio-blocoh';
}

include_once($arq.'.php');
?>





    </div>
</body>

</html>