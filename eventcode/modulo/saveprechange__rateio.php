<?
$rateiosql=$_POST['rateiosql'];
$statusant=$_POST['statusant'];
$idrateio=$_SESSION['arrpostbuffer']['1']['u']['rateio']['idrateio'];
$status=$_SESSION['arrpostbuffer']['1']['u']['rateio']['status'];



if($status=='FECHADO' AND !empty($idrateio) and $statusant!='FECHADO'){
    $sql="SELECT 
    group_concat(`idrateioitem`) AS `idrateioitem`
    FROM(".$rateiosql." ) as x";


    $res = d::b()->query($sql) or die("Falha ao buscar itens do rateio sql=".$sql); 
    $row = mysqli_fetch_assoc($res);  
    if(!empty($row['idrateioitem'])){
        $sqld="update rateioitem set idrateio=null where  idrateio=".$idrateio." ";
        $resd = d::b()->query($sqld) or die("Falha ao Desvincular itens do rateio sql=".$sqld); 

        $sqlu="update rateioitem set idrateio=".$idrateio." where idrateioitem in(".$row['idrateioitem'].")";
        $resu = d::b()->query($sqlu) or die("Falha ao refazer os vincuolos do itens do rateio sql=".$sqlu); 
    }
}



?>