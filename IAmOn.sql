-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 22-10-2023 a las 15:07:35
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
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Switch`
--

CREATE TABLE `Switch` (
                          `private_uuid` char(36) NOT NULL DEFAULT (uuid()),
                          `user_name` varchar(50) NOT NULL,
                          `public_uuid` char(36) NOT NULL DEFAULT (uuid()),
                          `switch_name` varchar(100) NOT NULL,
                          `last_power_on` datetime DEFAULT NULL,
                          `power_off` datetime NOT NULL DEFAULT (now()),
                          `description` varchar(400) DEFAULT NULL
#                           `last_notification` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `User`
--

CREATE TABLE `User` (
                        `user_name` varchar(50) NOT NULL,
                        `user_password` varchar(50) NOT NULL,
                        `user_email` varchar(50) DEFAULT NULL
);

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