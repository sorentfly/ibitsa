/* Пояснение к структуре БД сообщений: */

/* ДИАЛОГИ */
CREATE TABLE `engine4_messages_conversations` (
  `conversation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',  /*название диалога*/
  `user_id` int(11) unsigned NOT NULL, /*инициатор диалога*/
  `recipients` int(11) unsigned NOT NULL, /*кол-во собеседников КРОМЕ инициатора*/
  `modified` datetime NOT NULL, /*дата последнего изменения*/
  `locked` tinyint(1) NOT NULL DEFAULT '0',/*1 - если беседа закрыта(туда нельзя больше писать сообщения)*/
  `resource_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT '',/*тип субъекта, к которому относится беседа, иначе null*/
  `resource_id` int(11) unsigned NOT NULL DEFAULT '0', /*ID субъекта, к которому относится беседа, иначе 0*/
  PRIMARY KEY (`conversation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2775 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* УЧАСТНИКИ ДИАЛОГА */
CREATE TABLE `engine4_messages_recipients` (
  `user_id` int(11) unsigned NOT NULL, /*участник*/
  `conversation_id` int(11) unsigned NOT NULL,/*диалог*/
  `deleted` datetime DEFAULT NULL, /*если собеседник исключён из диалога - дата его исключения(при исключении не удаляется из таблицы), иначе null*/
  `date` datetime DEFAULT NULL,/* дата создания */
  `inbox_message_id` int(11) unsigned DEFAULT NULL, /* последнее входящее сообщение участнику */
  `inbox_updated` datetime DEFAULT NULL, /* дата последнего входящего сообщения */
  `last_viewed` datetime DEFAULT NULL, /* последний просмотр диалога участником - дата */
  `inbox_read` tinyint(1) DEFAULT NULL, /* прочитал ли участник последнее входящее сообщение */
  `inbox_deleted` tinyint(1) DEFAULT NULL, /* 1 - если у участника отсутсвуют вовсе входящие сообщения (например он только начал диалог) */
  `outbox_message_id` int(11) unsigned DEFAULT NULL, /* последнее исходящее сообщение участника */
  `outbox_updated` datetime DEFAULT NULL, /* дата последнего исходящего сообщения */
  `outbox_deleted` tinyint(1) DEFAULT NULL,/* 1 - если у участника отсутсвуют вовсе исходящие сообщения (например с ним только начали беседу) */
  PRIMARY KEY (`user_id`,`conversation_id`),
  KEY `INBOX_UPDATED` (`user_id`,`conversation_id`,`inbox_updated`),
  KEY `OUTBOX_UPDATED` (`user_id`,`conversation_id`,`outbox_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*СООБЩЕНИЯ ДИАЛОГА*/
CREATE TABLE `engine4_messages_messages` (
  `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) unsigned NOT NULL, /*диалог*/
  `user_id` int(11) unsigned NOT NULL, /*отправитель сообщения*/
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,/*заголовок сообщения*/
  `body` text COLLATE utf8_unicode_ci NOT NULL,/*тело сообщения*/
  `date` datetime NOT NULL, /*дата отправки*/
  `deleted` datetime DEFAULT NULL, /* дата удаления, если сообщение удалено, иначе null */
  `attachment_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT '', /*тип объекта, который может быть прикреплён к сообщению, иначе null*/
  `attachment_id` int(11) unsigned NOT NULL DEFAULT '0', /*ID объекта, который может быть прикреплён к сообщению, иначе 0*/
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `CONVERSATIONS` (`conversation_id`,`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7583 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

