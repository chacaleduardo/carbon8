<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaRotulagem15x60( $_OBJ["idlote"] );

if(!empty($row)){
    $arrMeses = [
        "01" => "JAN",
        "02" => "FEV",
        "03" => "MAR",
        "04" => "ABR",
        "05" => "MAI",
        "06" => "JUN",
        "07" => "JUL",
        "08" => "AGO",
        "09" => "SET",
        "10" => "OUT",
        "11" => "NOV",
        "12" => "DEZ",
    ];
    $fabrAno = substr($row["fabricacao"],2,2);
    $vencAno = substr($row["vencimento"],2,2);
    $fabrMes = $arrMeses[substr($row["fabricacao"],5,2)];
    $vencMes = $arrMeses[substr($row["vencimento"],5,2)];


    $_CONTEUDOIMPRESSAO .= "^XA
                            ^MMT
                            ^PW288
                            ^LL0320
                            ^LS0
                            ^FT94,220^A@N,14,15,TT0003M_^FH\^CI17^F8^FDPART.: ^FS^CI0
                            ^FT94,234^A@N,14,15,TT0003M_^FH\^CI17^F8^FDFABR.:^FS^CI0
                            ^FT94,248^A@N,14,15,TT0003M_^FH\^CI17^F8^FDVENC.:^FS^CI0
                            ^FT120,220^A@N,14,13,TT0003M_^FB71,1,0,R^FH\^CI17^F8^FD".$row["partidaext"]."^FS^CI0
                            ^FT120,234^A@N,14,13,TT0003M_^FB71,1,0,R^FH\^CI17^F8^FD".$fabrMes."/".$fabrAno."^FS^CI0
                            ^FT120,248^A@N,14,13,TT0003M_^FB71,1,0,R^FH\^CI17^F8^FD".$vencMes."/".$vencAno."^FS^CI0
                            ^PQ1,0,1,Y^XZ
                            ";

    // $_CONTEUDOIMPRESSAO .= "^XA
    //                             ^MMT
    //                             ^PW256
    //                             ^LL296
    //                             ^LS0
    //                             ^FT146,249^A0N,11,15^FH\^CI28^FDPART.:  ".$row["partidaext"]."^FS^CI27
    //                             ^FT146,262^A0N,11,15^FH\^CI28^FDFABR.: ".$fabrMes."/".$fabrAno."^FS^CI27
    //                             ^FT146,275^A0N,11,15^FH\^CI28^FDVENC.: ".$vencMes."/".$vencAno."^FS^CI27
    //                             ^PQ1,0,1,Y
    //                         ^XZ";
    // $_CONTEUDOIMPRESSAO .= "^XA
    //                             ^CF0,15
    //                             ^FO370,20
    //                             ^FDPART.: ".$row["partidaext"]."^FS
    //                             ^FO370,40
    //                             ^FDFABR.: ".$fabrMes."/".$fabrAno."^FS
    //                             ^FO370,60
    //                             ^FDVENC.: ".$vencMes."/".$vencAno."^FS
    //                             ^FO375,80
    //                             ^CF0,14
    //                             ^FDConteudo: ".$row["volumeformula"]." ".$row["un"]."^FS
    //                         ^XZ";

}
?>