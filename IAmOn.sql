-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 15-01-2024 a las 19:49:51
-- Versión del servidor: 8.0.26-0ubuntu0.20.04.2
-- Versión de PHP: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `iamon`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Suscriber`
--

CREATE TABLE `Suscriber` (
                             `public_uuid` varchar(36) NOT NULL,
                             `user_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `Suscriber`
--

INSERT INTO `Suscriber` (`public_uuid`, `user_name`) VALUES
                                                         ('c93cd7c1-b148-11ee-aa65-0242ac110002', 'admin'),
                                                         ('c08ccb0f-b224-11ee-9515-0242ac110002', 'Brais'),
                                                         ('1b121df9-b152-11ee-aa65-0242ac110002', 'DavidSanteiro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Switch`
--

CREATE TABLE `Switch` (
                          `private_uuid` char(36) NOT NULL DEFAULT (uuid()),
                          `user_name` varchar(50) NOT NULL,
                          `public_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                          `switch_name` varchar(100) NOT NULL,
                          `last_power_on` datetime DEFAULT NULL,
                          `power_off` datetime NOT NULL DEFAULT (now()),
                          `description` varchar(400) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `Switch`
--

INSERT INTO `Switch` (`private_uuid`, `user_name`, `public_uuid`, `switch_name`, `last_power_on`, `power_off`, `description`) VALUES
                                                                                                                                  ('14c2ed76-b3a6-11ee-8b37-0242ac110002', 'DavidSanteiro', '14c1f762-b3a6-11ee-8b37-0242ac110002', 'Hora funciona', '2024-01-15 18:52:03', '2024-01-15 18:52:04', ''),
                                                                                                                                  ('6aa512c6-b1a2-11ee-8da9-0242ac110002', 'Brais', '1b121df9-b152-11ee-aa65-0242ac110002', 'DJALSKDJas', '2024-01-15 18:39:31', '2024-01-15 19:39:31', 'asdfsfdasf'),
                                                                                                                                  ('50011a0a-b1aa-11ee-8da9-0242ac110002', 'Brais', '2d4e6f07-b1aa-11ee-8da9-0242ac110002', 'sadsssm', '2024-01-15 18:39:47', '2024-01-15 18:41:20', 'sad'),
                                                                                                                                  ('35a03f37-b39c-11ee-8b37-0242ac110002', 'Brais', '359dcadc-b39c-11ee-8b37-0242ac110002', 'Paulo tonto', '2024-01-15 18:39:46', '2024-01-15 19:39:46', 'tonto'),
                                                                                                                                  ('426f85a2-b271-11ee-9b5c-0242ac110002', 'admin', '3a45320f-b271-11ee-9b5c-0242ac110002', 'admin2', NULL, '2024-01-14 00:09:47', 'para los amigos .'),
                                                                                                                                  ('4ad73f91-b271-11ee-9b5c-0242ac110002', 'admin', '4ad71868-b271-11ee-9b5c-0242ac110002', 'administradores_XD', NULL, '2024-01-14 00:10:15', 'jaja'),
                                                                                                                                  ('772b6455-b3d7-11ee-8b37-0242ac110002', 'DavidSanteiro', '772a2394-b3d7-11ee-8b37-0242ac110002', 'uno', '2024-01-15 18:54:17', '2024-01-15 18:54:26', 'dos'),
                                                                                                                                  ('c08d5e5e-b224-11ee-9515-0242ac110002', 'DavidSanteiro', 'c08ccb0f-b224-11ee-9515-0242ac110002', 'aa', '2024-01-15 17:45:04', '2024-01-15 17:45:06', 'aa'),
                                                                                                                                  ('c696fbb3-b229-11ee-9515-0242ac110002', 'admin', 'c696b328-b229-11ee-9515-0242ac110002', 'sh_admin', NULL, '2024-01-13 15:38:19', 'el switch del admin'),
                                                                                                                                  ('a3639f59-b150-11ee-aa65-0242ac110002', 'DavidSanteiro', 'c93cd7c1-b148-11ee-aa65-0242ac110002', 'aaaaa', '2024-01-15 17:40:44', '2024-01-15 17:40:45', 'aaaaaa'),
                                                                                                                                  ('d6187233-b145-11ee-aa65-0242ac110002', 'DavidSanteiro', 'd6183859-b145-11ee-aa65-0242ac110002', 'Switch1', '2024-01-13 23:15:07', '2024-01-14 00:16:07', 'SS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `User`
--

CREATE TABLE `User` (
                        `user_name` varchar(50) NOT NULL,
                        `user_password` varchar(50) NOT NULL,
                        `user_email` varchar(50) DEFAULT NULL,
                        `security_code` int DEFAULT NULL,
                        `security_code_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `User`
--

INSERT INTO `User` (`user_name`, `user_password`, `user_email`, `security_code`, `security_code_date`) VALUES
                                                                                                           ('admin', 'S2eTgnTwCARg', 'a', NULL, NULL),
                                                                                                           ('Brais', '12345', 'brais@gmail.com', NULL, NULL),
                                                                                                           ('DavidSanteiro', '11111', 'daavid@email.co', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Suscriber`
--
ALTER TABLE `Suscriber`
    ADD PRIMARY KEY (`public_uuid`,`user_name`),
    ADD KEY `FK user in suscriber` (`user_name`);

--
-- Indices de la tabla `Switch`
--
ALTER TABLE `Switch`
    ADD PRIMARY KEY (`public_uuid`),
    ADD UNIQUE KEY `private_uuid` (`private_uuid`),
    ADD KEY `user_name` (`user_name`);

--
-- Indices de la tabla `User`
--
ALTER TABLE `User`
    ADD PRIMARY KEY (`user_name`),
    ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Suscriber`
--
ALTER TABLE `Suscriber`
    ADD CONSTRAINT `FK switch in suscriber` FOREIGN KEY (`public_uuid`) REFERENCES `Switch` (`public_uuid`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `FK user in suscriber` FOREIGN KEY (`user_name`) REFERENCES `User` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Switch`
--
ALTER TABLE `Switch`
    ADD CONSTRAINT `FK user in switch` FOREIGN KEY (`user_name`) REFERENCES `User` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
