<?

// valida se o arquivo é um arquivo .r0 ou superior, ou um arquivo .crt
$arquivo = $_FILES[$_fileElementName]['name'];
$extensao = pathinfo($arquivo, PATHINFO_EXTENSION);
if (!preg_match('/^(r\d+|crt)$/i', $extensao)) {
    print_r('A extensão do arquivo não é .CRT, .R0 ou superior: '.$arquivo);
    die;
}
?>