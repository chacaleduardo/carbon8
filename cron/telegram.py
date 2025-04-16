import requests

def telegram_bot_sendtext(bot_message):
    
    bot_token = '640547891:AAF8DW5qabCnRKI1eg08bK_Ve8uYe0WMfaM'
    bot_chatID = '229561591'
    send_text = 'https://api.telegram.org/bot' + bot_token + '/sendMessage?chat_id=' + bot_chatID + '&parse_mode=Markdown&text=' + bot_message

    response = requests.get(send_text)

    return response.json()
    

test = telegram_bot_sendtext("Dump iniciado")
print(test)
