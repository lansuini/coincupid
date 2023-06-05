# pip3 install python-telegram-bot
import sys, telegram

def send_alarm_msg(token, id, msg):
    chat_id_list = [id]
    bot = telegram.Bot(token=token)
    for chat_id in chat_id_list:
        bot.send_message(chat_id, text=msg)

if __name__ == '__main__':
    send_alarm_msg(sys.argv[1], sys.argv[2], sys.argv[3])
