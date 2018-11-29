CREATE SCHEMA IF NOT EXISTS bitsa_tmp;
USE bitsa_tmp;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bitsa_tmp`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Category`
--

CREATE TABLE `Category` (
  `Id_Category` int(11) NOT NULL,
  `NameCategory` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `Count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Category`
--

INSERT INTO `Category` (`Id_Category`, `NameCategory`, `Count`) VALUES
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
-- Структура таблицы `Friends`
--

CREATE TABLE `Friends` (
  `Id_Friends` int(255) NOT NULL,
  `IdUserTo` int(255) NOT NULL,
  `DateAdd` date DEFAULT NULL,
  `Category` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IdUserFrom` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Friends`
--

INSERT INTO `Friends` (`Id_Friends`, `IdUserTo`, `DateAdd`, `Category`, `IdUserFrom`) VALUES
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
-- Структура таблицы `Grades`
--

CREATE TABLE `Grades` (
  `Id_Grade` int(255) NOT NULL,
  `IdPupil` int(255) NOT NULL,
  `Grade` int(255) NOT NULL,
  `DateOfGrade` date NOT NULL,
  `IdObject` int(255) NOT NULL,
  `IdTeacher` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Grades`
--

INSERT INTO `Grades` (`Id_Grade`, `IdPupil`, `Grade`, `DateOfGrade`, `IdObject`, `IdTeacher`) VALUES
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
-- Структура таблицы `Homework`
--

CREATE TABLE `Homework` (
  `Id_homework` int(255) NOT NULL,
  `IdPupil` int(255) NOT NULL,
  `IdObject` int(255) NOT NULL,
  `IdTeacher` int(255) NOT NULL,
  `Home_File` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Homework`
--

INSERT INTO `Homework` (`Id_homework`, `IdPupil`, `IdObject`, `IdTeacher`, `Home_File`) VALUES
  (2, 0, 0, 0, 'GrHFa44DayHhrtBnhDaE.png'),
  (3, 0, 0, 0, 'bZ8kAA4R78GHeDtbNEti.png'),
  (4, 0, 0, 0, '8D3RAzyGeQ6y9eFaGYkG.jpg'),
  (5, 0, 0, 0, 'ZkTNyyGSSG9zfY5R3Z3d.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `Messages`
--

CREATE TABLE `Messages` (
  `Id_Message` int(255) NOT NULL,
  `IdUserFrom` int(255) NOT NULL,
  `IdUserTo` int(255) NOT NULL,
  `DateAdd` datetime NOT NULL,
  `Text` varchar(255) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Messages`
--

INSERT INTO `Messages` (`Id_Message`, `IdUserFrom`, `IdUserTo`, `DateAdd`, `Text`) VALUES
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
-- Структура таблицы `Notes`
--

CREATE TABLE `Notes` (
  `Id_Note` int(255) NOT NULL,
  `Content` text COLLATE utf8_unicode_ci NOT NULL,
  `IdUser` int(255) NOT NULL,
  `NoteName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreateDate` datetime NOT NULL,
  `ChangeDate` datetime NOT NULL,
  `Color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Notes`
--

INSERT INTO `Notes` (`Id_Note`, `Content`, `IdUser`, `NoteName`, `CreateDate`, `ChangeDate`, `Color`) VALUES
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
-- Структура таблицы `Notifications`
--

CREATE TABLE `Notifications` (
  `Id_Notification` int(255) NOT NULL,
  `NameNotification` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TimeNotification` datetime NOT NULL,
  `IdUser` int(11) NOT NULL,
  `CreateDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Notifications`
--

INSERT INTO `Notifications` (`Id_Notification`, `NameNotification`, `TimeNotification`, `IdUser`, `CreateDate`) VALUES
  (1, 'gtrcs', '2018-11-03 15:58:00', 16, '2018-11-03 15:22:00'),
  (12, 'Здарова дададада я', '2018-11-05 19:04:00', 5, '2018-11-05 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `Objects`
--

CREATE TABLE `Objects` (
  `Id_object` int(255) NOT NULL,
  `Name_object` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Objects`
--

INSERT INTO `Objects` (`Id_object`, `Name_object`) VALUES
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
-- Структура таблицы `Products`
--

CREATE TABLE `Products` (
  `Id_Product` int(255) NOT NULL,
  `NameProduct` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ShortDesc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci NOT NULL,
  `Price` int(100) NOT NULL,
  `RatingProduct` double(10,10) DEFAULT NULL,
  `IdCategory` int(11) NOT NULL,
  `Image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Products`
--

INSERT INTO `Products` (`Id_Product`, `NameProduct`, `ShortDesc`, `Description`, `Price`, `RatingProduct`, `IdCategory`, `Image`) VALUES
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
-- Структура таблицы `Product_Comment`
--

CREATE TABLE `Product_Comment` (
  `Id_Comment` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL,
  `IdProduct` int(11) NOT NULL,
  `Likes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Dislikes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Sumary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rating` double NOT NULL,
  `TimeUse` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Product_Comment`
--

INSERT INTO `Product_Comment` (`Id_Comment`, `IdUser`, `IdProduct`, `Likes`, `Dislikes`, `Sumary`, `Rating`, `TimeUse`) VALUES
  (1, 2, 1, 'Дешевый и крутой', 'Нету', 'Кайфовый принтер, особенно за такую стоимость', 4.5, 'Менее месяца'),
  (2, 5, 1, 'Дешевый', 'Все плохо', 'Такой себе девайс, единственное что стоит не дорого', 1.5, 'Менее месяца'),
  (3, 1, 1, 'Вроде неплох', 'Скрипит при печати', 'Not bad', 4, 'От одного месяца до двух');

-- --------------------------------------------------------

--
-- Структура таблицы `Pupil`
--

CREATE TABLE `Pupil` (
  `Id_Pupil` int(255) NOT NULL,
  `IdUser` int(255) NOT NULL,
  `Class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `School` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Pupil`
--

INSERT INTO `Pupil` (`Id_Pupil`, `IdUser`, `Class`, `School`) VALUES
  (1, 22, '11', '4');

-- --------------------------------------------------------

--
-- Структура таблицы `Teachers`
--

CREATE TABLE `Teachers` (
  `Id_Teacher` int(255) NOT NULL,
  `IdUser` int(255) NOT NULL,
  `IdObject` int(255) NOT NULL,
  `School` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `Teachers`
--

INSERT INTO `Teachers` (`Id_Teacher`, `IdUser`, `IdObject`, `School`) VALUES
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
-- Структура таблицы `test`
--

CREATE TABLE `test` (
  `Id` int(11) NOT NULL,
  `Text` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `test`
--

INSERT INTO `test` (`Id`, `Text`) VALUES
  (2, 'da'),
  (3, 'da'),
  (4, 'da'),
  (5, 'da');

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
  (2, 'Daniel', 'Was', NULL, 'Stilwuc', 'IGOgjetVarsagy4', NULL, 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (3, 'Даниил', 'Смирнов', NULL, 'stilwuc@gmail.com', 'VoquetijMumgaw4', '1998-07-03', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (4, 'sad', 'ddd', NULL, 'daniel@mail.ru', '1', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (5, 'Vladislav', 'Molodcov', NULL, 'VladMol@mail.ru', 'BigKek76', '1997-09-12', 'm', NULL, NULL, NULL, 1, '0000-00-00 00:00:00'),
  (10, 'Il’ya', 'Miroshkin', NULL, 'petros9nbl4@gmail.co', 'idinaxyipes', '1998-06-18', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (14, 'Fruct', 'Fruct; drop table us', NULL, 'gdfgfgd@mail.ru', 'fdgdfgfdg', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (15, 'dsdsd', 'd; drop table user;', NULL, 'gddssdfgfgd@mail.ru', 'fdgdfgfdg', '2018-01-01', 'f', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (16, 'Lenka', 'Tvo9mamka', NULL, 'Tvo9_mama@mail.ru', 'mamkyebal', '1980-05-29', 'f', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (17, 'fdf', 'f;drop table users;', NULL, 'gddfdffgdfg@mail.ru', 'dfdf', '2018-01-01', 'f', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (19, 'Daniel', 'Was', NULL, 'stilwusdc@gsdmddail.com', 'IPUgarlEpNirjO4', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (20, 'Wasyan', 'Smirnov', NULL, 'wasis1999@mail.ru', '3222281488', '2007-07-20', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (21, 'Дима', 'Иванов', NULL, 'pes@mail.ru', 'qwerty', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (22, 'Владислав', 'Молодцов', NULL, 'plad@mail.ru', '678908asd', '1997-09-12', 'm', NULL, '', 'Углич', 0, '2018-11-27 14:41:47'),
  (23, 'авава', 'ыаыа', NULL, 'qwerty@mail.ru', '325252355232', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (24, 'Василий', 'Смирнов', NULL, 'wasis1999@gmail.com', '14881488', '2005-05-05', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (25, 'Андрев', 'Коч', NULL, 'koch@topchel.koch', 'qwerty123', '2018-01-01', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (26, 'Петр', 'Дремал', NULL, 'rrr1915@inbox.ru', '01071996', '1984-11-13', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (27, 'ВАСЯН', 'ВАСИН ', NULL, 'zopa1310@mail.ru', '13Ab1314', '1988-02-02', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (28, 'Данил', 'Орешкин', NULL, 'nutprog@gmail.com', 'bossofgays', '2000-04-07', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (29, 'Рома', 'Лобанов', NULL, 'wasya999@mail.ru', 'qwerty', '2001-12-14', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (30, 'еханмгеъ', 'тйяткпью', NULL, 'effqfjym@mail.ru', 'eihsplfx', '1997-09-12', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (31, 'чфнбчлупу', 'жбойексык', NULL, 'gjqtjayil@mail.ru', 'ebpujgmeh', '1997-09-09', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (32, 'олртсф', 'лнщпкм', NULL, 'neonqs@mail.ru', 'rprgth', '1997-09-12', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (33, 'ттъоег', 'еиьядо', NULL, 'cybkzv@mail.ru', 'ykydrv', '1997-09-12', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (34, 'пвпвыавы', 'ыфафыпывв', NULL, 'fsfsdsgdsa@mail.ru', 'rcbgdsfsfdDFRGS', '1984-07-28', 'm', NULL, NULL, NULL, 0, '0000-00-00 00:00:00'),
  (35, 'Пётр', 'Копал', NULL, 'petr@mail.ru', '1234567as', '2001-03-14', 'm', NULL, NULL, NULL, 0, '2018-11-27 14:46:40');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`Id_Category`);

--
-- Индексы таблицы `Friends`
--
ALTER TABLE `Friends`
  ADD PRIMARY KEY (`Id_Friends`),
  ADD KEY `IdUser` (`IdUserTo`),
  ADD KEY `IdUserFrom` (`IdUserFrom`);

--
-- Индексы таблицы `Grades`
--
ALTER TABLE `Grades`
  ADD PRIMARY KEY (`Id_Grade`),
  ADD KEY `IdPupil` (`IdPupil`),
  ADD KEY `IdObject` (`IdObject`),
  ADD KEY `IdTeacher` (`IdTeacher`);

--
-- Индексы таблицы `Homework`
--
ALTER TABLE `Homework`
  ADD PRIMARY KEY (`Id_homework`),
  ADD KEY `IdObject` (`IdObject`),
  ADD KEY `IdPupil` (`IdPupil`),
  ADD KEY `IdTeacher` (`IdTeacher`);

--
-- Индексы таблицы `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`Id_Message`),
  ADD KEY `IdUserFrom` (`IdUserFrom`),
  ADD KEY `IdUserTo` (`IdUserTo`);

--
-- Индексы таблицы `Notes`
--
ALTER TABLE `Notes`
  ADD PRIMARY KEY (`Id_Note`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `Notifications`
--
ALTER TABLE `Notifications`
  ADD PRIMARY KEY (`Id_Notification`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `Objects`
--
ALTER TABLE `Objects`
  ADD PRIMARY KEY (`Id_object`);

--
-- Индексы таблицы `Products`
--
ALTER TABLE `Products`
  ADD PRIMARY KEY (`Id_Product`),
  ADD KEY `IdCategory` (`IdCategory`);

--
-- Индексы таблицы `Product_Comment`
--
ALTER TABLE `Product_Comment`
  ADD PRIMARY KEY (`Id_Comment`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `IdProduct` (`IdProduct`);

--
-- Индексы таблицы `Pupil`
--
ALTER TABLE `Pupil`
  ADD PRIMARY KEY (`Id_Pupil`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Индексы таблицы `Teachers`
--
ALTER TABLE `Teachers`
  ADD PRIMARY KEY (`Id_Teacher`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `IdObject` (`IdObject`);

--
-- Индексы таблицы `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id_User`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Category`
--
ALTER TABLE `Category`
  MODIFY `Id_Category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `Friends`
--
ALTER TABLE `Friends`
  MODIFY `Id_Friends` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT для таблицы `Grades`
--
ALTER TABLE `Grades`
  MODIFY `Id_Grade` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `Homework`
--
ALTER TABLE `Homework`
  MODIFY `Id_homework` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `Messages`
--
ALTER TABLE `Messages`
  MODIFY `Id_Message` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT для таблицы `Notes`
--
ALTER TABLE `Notes`
  MODIFY `Id_Note` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT для таблицы `Notifications`
--
ALTER TABLE `Notifications`
  MODIFY `Id_Notification` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `Objects`
--
ALTER TABLE `Objects`
  MODIFY `Id_object` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `Products`
--
ALTER TABLE `Products`
  MODIFY `Id_Product` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `Product_Comment`
--
ALTER TABLE `Product_Comment`
  MODIFY `Id_Comment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `Pupil`
--
ALTER TABLE `Pupil`
  MODIFY `Id_Pupil` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Teachers`
--
ALTER TABLE `Teachers`
  MODIFY `Id_Teacher` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `test`
--
ALTER TABLE `test`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `Id_User` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Friends`
--
ALTER TABLE `Friends`
  ADD CONSTRAINT `Friends_ibfk_1` FOREIGN KEY (`IdUserTo`) REFERENCES `users` (`Id_User`),
  ADD CONSTRAINT `Friends_ibfk_2` FOREIGN KEY (`IdUserFrom`) REFERENCES `users` (`Id_User`);

--
-- Ограничения внешнего ключа таблицы `Grades`
--
ALTER TABLE `Grades`
  ADD CONSTRAINT `Grades_ibfk_1` FOREIGN KEY (`IdPupil`) REFERENCES `Pupil` (`Id_Pupil`),
  ADD CONSTRAINT `Grades_ibfk_2` FOREIGN KEY (`IdObject`) REFERENCES `Objects` (`Id_object`),
  ADD CONSTRAINT `Grades_ibfk_3` FOREIGN KEY (`IdTeacher`) REFERENCES `Teachers` (`Id_Teacher`);

--
-- Ограничения внешнего ключа таблицы `Messages`
--
ALTER TABLE `Messages`
  ADD CONSTRAINT `Messages_ibfk_1` FOREIGN KEY (`IdUserFrom`) REFERENCES `users` (`Id_User`),
  ADD CONSTRAINT `Messages_ibfk_2` FOREIGN KEY (`IdUserTo`) REFERENCES `users` (`Id_User`);

--
-- Ограничения внешнего ключа таблицы `Notes`
--
ALTER TABLE `Notes`
  ADD CONSTRAINT `Notes_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id_User`);

--
-- Ограничения внешнего ключа таблицы `Notifications`
--
ALTER TABLE `Notifications`
  ADD CONSTRAINT `Notifications_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id_User`);

--
-- Ограничения внешнего ключа таблицы `Products`
--
ALTER TABLE `Products`
  ADD CONSTRAINT `Products_ibfk_1` FOREIGN KEY (`IdCategory`) REFERENCES `Category` (`Id_Category`);

--
-- Ограничения внешнего ключа таблицы `Product_Comment`
--
ALTER TABLE `Product_Comment`
  ADD CONSTRAINT `Product_Comment_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id_User`),
  ADD CONSTRAINT `Product_Comment_ibfk_2` FOREIGN KEY (`IdProduct`) REFERENCES `Products` (`Id_Product`);

--
-- Ограничения внешнего ключа таблицы `Pupil`
--
ALTER TABLE `Pupil`
  ADD CONSTRAINT `Pupil_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id_User`);

--
-- Ограничения внешнего ключа таблицы `Teachers`
--
ALTER TABLE `Teachers`
  ADD CONSTRAINT `Teachers_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id_User`),
  ADD CONSTRAINT `Teachers_ibfk_2` FOREIGN KEY (`IdObject`) REFERENCES `Objects` (`Id_object`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
