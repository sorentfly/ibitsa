# Bitsa IT

## Подготовка

Для запуска приложения на своей машине необходимо иметь установленными
 * веб-сервер;
 * сервер MySQL (преимущественно MariaDB);
 
Загрузите исходный код проекта на подготовленную рабочую машину. Это можно сделать скачав архив и распаковав его в нужную директорию или с помощью инструмента git (если он установлен в системе) 

```
git clone https://github.com/sorentfly/ibitsa /path/to/folder
```

## Запуск

Если Вы все сделали правильно, а все — это настройка окружения, то, открыв окно браузера на вкладке с доменом http://localhost или любым другим, к которому Вы сами привязали проект, Вы увидите страницу приветствия. 
Приложение заработало, ура!

## Настройка

### База данных

Используя файл **deploy.sql**, создайте БД в своей СУБД и сохраните ее название для дальнейшего использования при конфигурации приложения.
Работая из консоли можно выполнить команду

```
mysql -u <username> -p <databasename> < deploy.sql
```

### Конфигурация

Для полноценной работы приложения Вам необходимо ввести свои данные от СУБД в файл конфигурации. 

Используя как пример файл `example.config.php` Вам не составит труда подставить свои данные для доступа к СУБД. 
После внесения персональных данных необходимо создать символьную ссылку на Ваш персональный файл конфигурации приложения используя команду

```
ln -s ***.config.php config.php
```

Поздравляем, приложение настроено и готово к работе!