-- TODO: REFACTOR THIS TRASH
DROP DATABASE `bitsa`;
CREATE DATABASE IF NOT EXISTS `bitsa`;
USE `bitsa`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;



--
-- Структура таблицы `market_categories`
--

CREATE TABLE `market_categories` (
  `Id_Category` int(11) NOT NULL,
  `NameCategory` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `Count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Дамп данных таблицы `market_categories`
--

INSERT INTO `market_categories` (`Id_Category`, `NameCategory`, `Count`) VALUES
  (2, 'Компьютеры и ноутбуки', 2),
  (4, 'Услуги и сервисы', 4),
  (6, 'Офисная техника, канцелярия', NULL),
  (8, 'Комплектующие для ПК', NULL),
  (9, 'ТВ, Аудио, Видео', NULL),
  (10, 'Спорт и Туризм', NULL),
  (11, 'Смартфоны и Гаджеты', NULL),
  (12, 'Автотовары и автозапчасти', NULL),
  (13, 'Планшеты и игровые приставки', NULL),
  (14, 'Бытовая техника', NULL),
  (15, 'Фото и видеокамеры', NULL),
  (16, 'Детские товары', NULL),
  (17, 'Парфюмерия и косметика', NULL),
  (18, 'Мужская одежда', NULL),
  (19, 'Женская одежда', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `user_friends`
--

CREATE TABLE `user_friends` (
  `Id_user_friends` int(255) NOT NULL,
  `IdUserTo` int(255) NOT NULL,
  `DateAdd` date DEFAULT NULL,
  `market_categories` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IdUserFrom` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `user_friends`
--

INSERT INTO `user_friends` (`Id_user_friends`, `IdUserTo`, `DateAdd`, `market_categories`, `IdUserFrom`) VALUES
  (20, 22, '2018-11-09', NULL, 1),
  (21, 3, '2018-11-09', NULL, 1),
  (22, 21, '2018-11-09', NULL, 1),
  (23, 2, '2018-11-09', NULL, 1),
  (24, 5, '2018-11-09', NULL, 25),
  (25, 24, '2018-11-09', NULL, 1),
  (26, 10, '2018-11-09', NULL, 1),
  (27, 1, '2018-11-10', NULL, 22),
  (28, 4, '2018-11-10', NULL, 1),
  (29, 5, '2018-11-10', NULL, 1),
  (30, 14, '2018-11-10', NULL, 1),
  (43, 15, '2018-11-10', NULL, 1),
  (44, 15, '2018-11-10', NULL, 1),
  (45, 16, '2018-11-10', NULL, 1),
  (46, 3, '2018-11-10', NULL, 22),
  (49, 27, '2018-11-18', NULL, 26),
  (50, 27, '2018-11-18', NULL, 22),
  (51, 26, '2018-11-18', NULL, 22),
  (52, 22, '2018-11-18', NULL, 26),
  (53, 22, '2018-11-18', NULL, 27),
  (54, 26, '2018-11-18', NULL, 27),
  (55, 22, '2018-11-20', NULL, 28),
  (71, 5, '2018-11-22', NULL, 29),
  (72, 19, '2018-11-22', NULL, 29),
  (73, 1, '2018-11-22', NULL, 29),
  (74, 20, '2018-11-24', NULL, 1),
  (75, 23, '2018-11-24', NULL, 1),
  (76, 25, '2018-11-24', NULL, 22),
  (77, 20, '2018-11-24', NULL, 22),
  (78, 17, '2018-11-24', NULL, 22),
  (79, 15, '2018-11-24', NULL, 22),
  (80, 14, '2018-11-24', NULL, 22),
  (81, 16, '2018-11-24', NULL, 22),
  (82, 21, '2018-11-24', NULL, 22),
  (83, 5, '2018-11-24', NULL, 22),
  (84, 23, '2018-11-24', NULL, 22),
  (85, 28, '2018-11-24', NULL, 22),
  (86, 33, '2018-11-27', NULL, 22),
  (87, 34, '2018-11-27', NULL, 22),
  (90, 16, '2018-11-27', NULL, 35),
  (91, 33, '2018-11-27', NULL, 35),
  (92, 17, '2018-11-27', NULL, 35),
  (93, 23, '2018-11-27', NULL, 35),
  (94, 20, '2018-11-27', NULL, 35),
  (95, 10, '2018-11-27', NULL, 35),
  (96, 19, '2018-11-27', NULL, 35),
  (97, 15, '2018-11-27', NULL, 35),
  (98, 4, '2018-11-27', NULL, 35),
  (99, 26, '2018-11-27', NULL, 35),
  (100, 27, '2018-11-27', NULL, 35),
  (101, 25, '2018-11-27', NULL, 35),
  (102, 5, '2018-11-27', NULL, 35),
  (109, 28, '2018-11-27', NULL, 35);

-- --------------------------------------------------------

--
-- Структура таблицы `school_grades`
--

CREATE TABLE `school_grades` (
  `Id_Grade` int(255) NOT NULL,
  `IdPupil` int(255) NOT NULL,
  `Grade` int(255) NOT NULL,
  `DateOfGrade` date NOT NULL,
  `IdObject` int(255) NOT NULL,
  `IdTeacher` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `school_grades`
--

INSERT INTO `school_grades` (`Id_Grade`, `IdPupil`, `Grade`, `DateOfGrade`, `IdObject`, `IdTeacher`) VALUES
  (3, 1, 5, '2018-11-18', 8, 8),
  (4, 1, 4, '2018-11-18', 8, 8),
  (5, 1, 5, '2018-11-18', 8, 8),
  (6, 1, 3, '2018-11-18', 12, 12),
  (7, 1, 4, '2018-11-18', 8, 8),
  (8, 1, 3, '2018-11-18', 8, 8),
  (9, 1, 3, '2018-11-18', 8, 8),
  (10, 1, 3, '2018-11-18', 8, 8);

-- --------------------------------------------------------

--
-- Структура таблицы `school_homework`
--

CREATE TABLE `school_homework` (
  `Id_homework` int(255) NOT NULL,
  `IdPupil` int(255) NOT NULL,
  `IdObject` int(255) NOT NULL,
  `IdTeacher` int(255) NOT NULL,
  `Home_File` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `school_homework`
--

INSERT INTO `school_homework` (`Id_homework`, `IdPupil`, `IdObject`, `IdTeacher`, `Home_File`) VALUES
  (2, 0, 0, 0, 'GrHFa44DayHhrtBnhDaE.png'),
  (3, 0, 0, 0, 'bZ8kAA4R78GHeDtbNEti.png'),
  (4, 0, 0, 0, '8D3RAzyGeQ6y9eFaGYkG.jpg'),
  (5, 0, 0, 0, 'ZkTNyyGSSG9zfY5R3Z3d.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `Id_Message` int(255) NOT NULL,
  `IdUserFrom` int(255) NOT NULL,
  `IdUserTo` int(255) NOT NULL,
  `DateAdd` datetime NOT NULL,
  `Text` varchar(255) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`Id_Message`, `IdUserFrom`, `IdUserTo`, `DateAdd`, `Text`) VALUES
  (54, 1, 22, '2018-11-22 21:51:54', 'Привет'),
  (55, 1, 22, '2018-11-22 21:52:02', 'Как дела?'),
  (56, 1, 22, '2018-11-22 21:52:45', 'эй'),
  (57, 1, 22, '2018-11-22 21:53:19', 'Ты тут вообще?'),
  (58, 22, 1, '2018-11-22 21:55:56', 'здарова\n'),
  (59, 1, 22, '2018-11-22 21:56:34', 'вавыа'),
  (60, 22, 3, '2018-11-25 17:11:23', 'Привет'),
  (61, 22, 3, '2018-11-25 17:11:45', 'привет'),
  (62, 1, 22, '2018-11-26 19:27:29', 'ти хуй'),
  (63, 1, 22, '2018-11-27 16:26:29', 'asdasd'),
  (64, 22, 3, '2018-11-27 16:41:03', 'Привет'),
  (65, 22, 3, '2018-11-27 16:48:47', 'Привет');

-- --------------------------------------------------------

--
-- Структура таблицы `chancellery_notes`
--

CREATE TABLE `chancellery_notes` (
  `Id_Note` int(255) NOT NULL,
  `Content` text COLLATE utf8_unicode_ci NOT NULL,
  `IdUser` int(255) NOT NULL,
  `NoteName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreateDate` datetime NOT NULL,
  `ChangeDate` datetime NOT NULL,
  `Color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `chancellery_notes`
--

INSERT INTO `chancellery_notes` (`Id_Note`, `Content`, `IdUser`, `NoteName`, `CreateDate`, `ChangeDate`, `Color`) VALUES
  (1, 'asdasdavxcscsa', 16, 'Zzapisss', '2018-11-03 00:00:00', '2018-11-03 00:00:00', NULL),
  (6, 'tgtrg', 4, 'ggg', '2018-11-14 00:00:00', '2018-11-30 00:00:00', NULL),
  (7, 'tgtrg', 4, 'ggg', '2018-11-14 00:00:00', '2018-11-30 00:00:00', NULL),
  (56, 'asdasdavxcscsa', 16, 'Zzapisss', '2018-11-03 00:00:00', '2018-11-03 00:00:00', NULL),
  (60, 'фыв', 1, 'Привет', '2018-11-03 00:00:00', '2018-11-26 16:14:48', NULL),
  (63, 'dsaasdsa', 5, 'кек', '2018-11-03 00:00:00', '2018-11-03 00:00:00', NULL),
  (64, 'edsadasda', 5, 'ewqw', '2018-11-03 00:00:00', '2018-11-03 00:00:00', NULL),
  (87, 'hey', 1, 'hi', '2018-11-03 00:00:00', '2018-11-26 15:53:46', NULL),
  (88, 'n', 5, 'кек', '2018-11-03 00:00:00', '2018-11-03 00:00:00', NULL),
  (90, '5', 1, '6', '2018-11-03 00:00:00', '2018-11-26 15:50:09', NULL),
  (91, '7', 1, '8', '2018-11-03 00:00:00', '2018-11-26 15:50:09', NULL),
  (93, '9', 1, '10', '2018-11-03 00:00:00', '2018-11-26 15:50:09', NULL),
  (96, 'vfhfuk', 22, 's', '2018-11-07 00:00:00', '2018-11-07 00:00:00', NULL),
  (97, 'blbmlj', 22, 'hgbkl', '2018-11-07 00:00:00', '2018-11-07 00:00:00', NULL),
  (98, '11', 1, '123123', '2018-11-24 17:27:00', '2018-11-26 15:50:09', NULL),
  (103, '12', 1, '123123', '2018-11-24 17:40:21', '2018-11-26 15:50:09', NULL),
  (104, '14', 1, '2131', '2018-11-24 17:41:09', '2018-11-26 15:50:09', NULL),
  (105, 'Zametka', 22, 'Ya dobavlyau zametku!111', '2018-11-24 17:48:05', '2018-11-24 17:48:05', NULL),
  (106, 'фмвтйх', 22, 'гсйзяц', '2018-11-25 17:29:32', '2018-11-25 17:29:32', NULL),
  (107, 'asd', 1, '1111111', '2018-11-26 16:15:19', '2018-11-26 16:15:19', NULL),
  (108, 'sasdasd', 1, 'dsd', '2018-11-27 16:30:33', '2018-11-27 16:30:38', NULL),
  (109, 'аикуеэщчя', 22, 'окыщполпг', '2018-11-27 16:41:21', '2018-11-27 16:41:21', NULL),
  (110, 'эбэиулн', 22, 'зямсжбч', '2018-11-27 16:41:48', '2018-11-27 16:41:48', NULL),
  (111, 'ьлйчьчюм', 22, 'йющывюие', '2018-11-27 16:42:26', '2018-11-27 16:42:26', NULL),
  (112, 'бабкмеаы', 22, 'жьмзччрж', '2018-11-27 16:42:46', '2018-11-27 16:42:46', NULL),
  (113, 'бягйхчкйш', 22, 'жшъжщэфрх', '2018-11-27 16:43:02', '2018-11-27 16:43:02', NULL),
  (114, 'лщътйчк', 22, 'дишъюмп', '2018-11-27 16:49:05', '2018-11-27 16:49:05', NULL),
  (115, ' njhdfghjkjhgfdgh', 22, 'xcxcx', '2018-11-27 17:43:23', '2018-11-27 17:43:23', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `core_notifications`
--

CREATE TABLE `core_notifications` (
  `Id_Notification` int(255) NOT NULL,
  `NameNotification` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TimeNotification` datetime NOT NULL,
  `IdUser` int(11) NOT NULL,
  `CreateDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `core_notifications`
--

INSERT INTO `core_notifications` (`Id_Notification`, `NameNotification`, `TimeNotification`, `IdUser`, `CreateDate`) VALUES
  (1, 'gtrcs', '2018-11-03 15:58:00', 1, '2018-11-03 15:22:00'),
  (2, 'Здарова дададада я', '2018-11-05 19:04:00', 2, '2018-11-05 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `school_subjects`
--

CREATE TABLE `school_subjects` (
  `Id_object` int(255) NOT NULL,
  `Name_object` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `school_subjects`
--

INSERT INTO `school_subjects` (`Id_object`, `Name_object`) VALUES
  (1, 'Алгебра'),
  (2, 'Геометрия'),
  (3, 'Русский язык'),
  (4, 'Информатика'),
  (5, 'История'),
  (6, 'Физика'),
  (7, 'География'),
  (8, 'Биология'),
  (9, 'Химия'),
  (10, 'Обществознание'),
  (11, 'Литература'),
  (12, 'Иностранный язык');

-- --------------------------------------------------------

--
-- Структура таблицы `market_items`
--

CREATE TABLE `market_items` (
  `Id_market_item` int(255) NOT NULL,
  `Namemarket_item` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ShortDesc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci NOT NULL,
  `Price` int(100) NOT NULL,
  `Ratingmarket_item` double(10,10) DEFAULT NULL,
  `IdCategory` int(11) NOT NULL,
  `Image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `market_items`
--

INSERT INTO `market_items` (`Id_market_item`, `Namemarket_item`, `ShortDesc`, `Description`, `Price`, `Ratingmarket_item`, `IdCategory`, `Image`) VALUES
  (1, 'Принтер лол', NULL, 'Кул принтер че', 13290, NULL, 6, '1.jpg'),
  (2, 'Ноутбук Prestigo', NULL, 'Китайский ноут, сломается через месяц', 14990, NULL, 2, '2.jpg'),
  (3, 'Микроволновка', NULL, 'Микроволновка хз че по чем', 3290, NULL, 14, '3.jpg'),
  (4, 'Бленджер', NULL, 'Брррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррррр', 1880, NULL, 14, '4.jpg'),
  (5, 'Шина лол', NULL, 'Лул', 3540, NULL, 12, '5.jpg'),
  (6, 'SSD накопитель 500ГБ', NULL, 'Хорошая но дорогая', 7510, NULL, 8, '6.jpg'),
  (7, 'Планшет Lenovo Tab 10 ', NULL, 'хз хз', 9490, NULL, 13, '7.jpg'),
  (8, 'Монитор Philips ', NULL, 'фывфывфыв', 8010, NULL, 9, '8.jpg'),
  (9, 'Фен Braun Satin Hair 7 HD 780', NULL, 'Волосы назад', 3990, NULL, 14, '9.jpg'),
  (10, 'Утюг Tefal FV 1711E0', NULL, 'Ну а че', 1870, NULL, 14, '10.jpg'),
  (11, 'Геймпад беспроводной Steelseries Nimbus ', NULL, 'Погамать можно там', 7060, NULL, 13, '11.jpg'),
  (12, 'ADATA DashDrive Durable HD330', NULL, 'Прокачай свою память', 5800, NULL, 8, '12.jpg'),
  (13, 'Колонки Microlab T10', NULL, 'Ну нормас так долбит', 6470, NULL, 9, '13.jpg'),
  (14, 'Наушники Apple AirPods MMEF2ZE/A', NULL, '', 11990, NULL, 11, '14.jpg'),
  (15, 'Стиральная машина CANDY CS4 ', NULL, '', 13290, NULL, 14, '15.jpg'),
  (17, 'DIMM DDR4, 16ГБ, Corsair Vengeance ', NULL, '', 10720, NULL, 8, '16.jpg'),
  (18, 'Швейная машина Brother ArtCity 140S', NULL, '', 6320, NULL, 14, '17.jpg'),
  (19, 'Электробритва Braun Series 3', NULL, '', 5990, NULL, 14, '18.jpg'),
  (20, 'Смартфон Samsung Galaxy A8 (2018)', NULL, '', 22990, NULL, 11, '19.jpg'),
  (21, 'Аккумулятор TORNADO 6 СТ-60 ', NULL, '', 2590, NULL, 12, '20.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `market_item_comments`
--

CREATE TABLE `market_item_comments` (
  `Id_Comment` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL,
  `Idmarket_item` int(11) NOT NULL,
  `Likes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Dislikes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Sumary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rating` double NOT NULL,
  `TimeUse` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `market_item_comments`
--

INSERT INTO `market_item_comments` (`Id_Comment`, `IdUser`, `Idmarket_item`, `Likes`, `Dislikes`, `Sumary`, `Rating`, `TimeUse`) VALUES
  (1, 2, 1, 'Дешевый и крутой', 'Нету', 'Кайфовый принтер, особенно за такую стоимость', 4.5, 'Менее месяца'),
  (2, 5, 1, 'Дешевый', 'Все плохо', 'Такой себе девайс, единственное что стоит не дорого', 1.5, 'Менее месяца'),
  (3, 1, 1, 'Вроде неплох', 'Скрипит при печати', 'Not bad', 4, 'От одного месяца до двух');

-- --------------------------------------------------------

--
-- Структура таблицы `school_pupils`
--

CREATE TABLE `school_pupils` (
  `Id_Pupil` int(255) NOT NULL,
  `IdUser` int(255) NOT NULL,
  `Class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `School` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `school_pupils`
--

INSERT INTO `school_pupils` (`Id_Pupil`, `IdUser`, `Class`, `School`) VALUES
  (1, 22, '11', '4');

-- --------------------------------------------------------

--
-- Структура таблицы `school_teachers`
--

CREATE TABLE `school_teachers` (
  `Id_Teacher` int(255) NOT NULL,
  `IdUser` int(255) NOT NULL,
  `IdObject` int(255) NOT NULL,
  `School` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `school_teachers`
--

INSERT INTO `school_teachers` (`Id_Teacher`, `IdUser`, `IdObject`, `School`) VALUES
  (1, 19, 1, '4'),
  (2, 19, 8, '4'),
  (3, 19, 7, '4'),
  (4, 19, 2, '4'),
  (5, 19, 12, '4'),
  (6, 19, 4, '4'),
  (7, 19, 5, '4'),
  (8, 19, 11, '4'),
  (9, 19, 10, '4'),
  (10, 19, 3, '4'),
  (11, 19, 6, '4'),
  (12, 19, 9, '4');

-- --------------------------------------------------------


--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `Id_User` int(255) NOT NULL,
  `FirstName` varchar(20) NOT NULL,
  `SecondName` varchar(20) NOT NULL,
  `ThirdName` varchar(20) DEFAULT NULL,
  `Mail` varchar(35) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `BirthDate` date DEFAULT NULL,
  `Sex` varchar(2) NOT NULL,
  `Rating` float DEFAULT NULL,
  `Nick` varchar(20) DEFAULT NULL,
  `City` varchar(20) DEFAULT NULL,
  `StatusNotification` tinyint(1) NOT NULL DEFAULT 0,
  `LastAuth` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`Id_User`, `FirstName`, `SecondName`, `ThirdName`, `Mail`, `Password`, `BirthDate`, `Sex`, `Rating`, `Nick`, `City`, `StatusNotification`, `LastAuth`) VALUES
  (1, 'Вася', 'Смирнов', NULL, 'stilwuc@mail.ru', 'qwerty', NULL, 'm', NULL, 'Вася бой', 'Васянск', 0, '2018-11-27 13:25:29'),
  (2, 'Даниил', 'Смирнов', NULL, 'stilwuc@gmail.com', 'VoquetijMumgaw4', '1998-07-03', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (3, 'Владислав', 'Молодцов', NULL, 'VladMol@mail.ru', 'BigKek76', '1997-09-12', 'm', NULL, NULL, NULL, 1, '0000-00-00 00:00:00');

--
-- Индексы сохранённых таблиц
--






DROP TABLE IF EXISTS `core_settings`;
CREATE TABLE `core_settings` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
INSERT IGNORE INTO `core_settings` (`name`, `value`) VALUES
  ('core.secret', ''),
  ('core.site.title', 'Social Network'),
  ('core.site.creation', NOW()),
  ('core.log.adapter', 'file')
;

DROP TABLE IF EXISTS `core_modules`;
CREATE TABLE `core_modules` (
  `name` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` TEXT NULL,
  `version` VARCHAR(32) NOT NULL,
  `enabled` TINYINT(1) DEFAULT '0' NOT NULL,
  `type` ENUM('core', 'standard', 'extra') DEFAULT 'extra' NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
INSERT INTO `core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
  ('application', 'Application',	'Token aggregation, processor for external privilegies',	'0.0.1', 1,	'extra'),
  ('core', 'Core',	'Core',	'1.0.0', 1,	'core'),
  ('messages', 'Messages',	'Messages',	'0.0.1', 1,	'standard'),
  ('user', 'Members',	'Members',	'0.0.1', 1,	'standard')
;






--
-- Индексы таблицы `market_categories`
--
ALTER TABLE `market_categories`
  ADD PRIMARY KEY (`Id_Category`);

--
-- Индексы таблицы `user_friends`
--
ALTER TABLE `user_friends`
  ADD PRIMARY KEY (`Id_user_friends`),
  ADD KEY `IdUser` (`IdUserTo`),
  ADD KEY `IdUserFrom` (`IdUserFrom`);

--
-- Индексы таблицы `school_grades`
--
ALTER TABLE `school_grades`
  ADD PRIMARY KEY (`Id_Grade`),
  ADD KEY `IdPupil` (`IdPupil`),
  ADD KEY `IdObject` (`IdObject`),
  ADD KEY `IdTeacher` (`IdTeacher`);

--
-- Индексы таблицы `school_homework`
--
ALTER TABLE `school_homework`
  ADD PRIMARY KEY (`Id_homework`),
  ADD KEY `IdObject` (`IdObject`),
  ADD KEY `IdPupil` (`IdPupil`),
  ADD KEY `IdTeacher` (`IdTeacher`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`Id_Message`),
  ADD KEY `IdUserFrom` (`IdUserFrom`),
  ADD KEY `IdUserTo` (`IdUserTo`);

--
-- Индексы таблицы `chancellery_notes`
--
ALTER TABLE `chancellery_notes`
  ADD PRIMARY KEY (`Id_Note`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `core_notifications`
--
ALTER TABLE `core_notifications`
  ADD PRIMARY KEY (`Id_Notification`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `school_subjects`
--
ALTER TABLE `school_subjects`
  ADD PRIMARY KEY (`Id_object`);

--
-- Индексы таблицы `market_items`
--
ALTER TABLE `market_items`
  ADD PRIMARY KEY (`Id_market_item`),
  ADD KEY `IdCategory` (`IdCategory`);

--
-- Индексы таблицы `market_item_comments`
--
ALTER TABLE `market_item_comments`
  ADD PRIMARY KEY (`Id_Comment`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `Idmarket_item` (`Idmarket_item`);

--
-- Индексы таблицы `school_pupils`
--
ALTER TABLE `school_pupils`
  ADD PRIMARY KEY (`Id_Pupil`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `school_teachers`
--
ALTER TABLE `school_teachers`
  ADD PRIMARY KEY (`Id_Teacher`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `IdObject` (`IdObject`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id_User`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
