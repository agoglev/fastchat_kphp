# fastchat_kphp
Автор: Андрей Гоглев

VK: https://vk.com/ru151


Чат написанный на KPHP.

KPHP — язык программирования, на котором работает серверная часть ВКонтакте и Telegram.


Движки необходимые для работы скрипта:

pmemcached

text

friends

hints

bayes

queue

Так-же необходимо создать базу данных boxed_base с логином boxed и паролем password

В ней таблицу users с полями:

uid (int)

email (varchar)

name (varchar)

lname (varchar)

password (varchar)

photo (varchar)

Не забудьте закрыть доступ к mysql серверу из вне.

Порты для KDB движков можно посмотреть в app/config.php


Скрипт написан исключительно для тестов, и не закончен.

Аптейтов не будет, то что есть хорошо работает и полность протестированно.

Если все будет настроено верно то проблем не возникнет, демо больше нет.


https://vk.com/kitphp
