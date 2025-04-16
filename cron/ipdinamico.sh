#!/bin/bash
#beep
#/root/beepini

SERVER=$1
ZONE=$2
HOST=$3
KEYNAME=$4
HASH=$5

LOGFILE="/var/log/syslog"

# descobrir o IP atual da interface dinÃ¢mica
IP=`wget -q -O - http://nash.mobi/temp/meuip.php`

(
	echo "============================================================="
	echo "$(date) $0: Argumentos: \"$*\""
	echo "====================== IP testado: =========================="
	echo $IP
	echo "============================================================="

#Executa o comando remotamente
cat <<EOF | nsupdate -v -y "$KEYNAME:$HASH"
server $SERVER
zone $ZONE
update delete $HOST A
update add $HOST 1440 A $IP
send
EOF

	#Armazena o exit code
	RC=$?

	if [ $RC -eq 0 ]
	then
	  echo "Successo"
	else
	  echo "$(date) $0: ERRO na atualizaÃ§Ã£o de IP DinÃ¢mico: (RC=$RC)"
	fi
	echo "============================================================="
) >>$LOGFILE 2>&1

exit $RC
