<?
// valida se o arquivo é um cvs
$extensoesValidas = [
    "application/octet-stream",
    "application/vnd.ms-excel",
    "text/csv"
];
if(!in_array($_FILES[$_fileElementName]['type'],$extensoesValidas)){
    print_r('o tipo do arquivo Não é .csv: '.$file) ;
    die;
}

?>