#!/bin/bash
#echo $1

sed -i 's/\d187_pkid/#pkid/g' $1
sed -i 's/\d187/_/g' $1
sed -i 's/class=\"tdsilver\"//g' $1
sed -i 's/inc\/validaacesso.php/inc\/php\/validaacesso.php/g' $1
sed -i 's/inc\/cbpost.php/inc\/php\/cbpost.php/g' $1
sed -i 's/include_once/require_once/g' $1
sed -i 's/inc\/controlevariaveisgetpost.php/inc\/php\/controlevariaveisgetpost.php/g' $1
sed -i 's/functions\/controlevariaveisgetpost.php/inc\/php\/controlevariaveisgetpost.php/g' $1
sed -i 's/conectabanco\(\)\;//g' $1
