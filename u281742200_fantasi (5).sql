-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-11-2025 a las 14:14:49
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u281742200_fantasi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dream_team`
--

CREATE TABLE `dream_team` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pid` int(11) NOT NULL,
  `points` decimal(5,2) DEFAULT 0.00,
  `slot_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dream_team`
--

INSERT INTO `dream_team` (`id`, `email`, `pid`, `points`, `slot_id`) VALUES
(84, 'naranjazos@gmail.com', 8, -9.00, 2),
(85, 'yo@test.com', 9, -9.00, 12),
(87, 'yo@test.com', 6, -9.00, 10),
(88, 'yo@test.com', 104, -9.00, 12),
(89, 'yo@test.com', 14, -9.00, 10),
(90, 'yo@test.com', 12, -9.00, 11),
(91, 'yo@test.com', 92, -9.00, 26),
(92, 'yo@test.com', 4, -9.00, 26),
(93, 'yo@test.com', 38, -9.00, 25),
(94, 'yo@test.com', 46, -2.00, 23),
(95, 'yo@test.com', 89, -9.00, 24),
(96, 'yo@test.com', 26, -9.00, 24),
(97, 'naranjazos@gmail.com', 104, -9.00, 26),
(99, 'naranjazos@gmail.com', 9, -9.00, 26),
(101, 'naranjazos@gmail.com', 38, -9.00, 25),
(103, 'naranjazos@gmail.com', 83, -9.00, 24),
(105, 'naranjazos@gmail.com', 14, -9.00, 24),
(107, 'naranjazos@gmail.com', 7, -3.00, 25),
(108, 'naranjazos@gmail.com', 22, -1.50, 7),
(109, 'naranjazos@gmail.com', 75, -1.50, 7),
(110, 'naranjazos@gmail.com', 25, -1.50, 7),
(111, 'naranjazos@gmail.com', 17, -1.50, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dream_team_temp`
--

CREATE TABLE `dream_team_temp` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pid` int(11) NOT NULL,
  `puntos_en_banco` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formaciones`
--

CREATE TABLE `formaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `max_jugadores` int(11) DEFAULT 11,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `formaciones`
--

INSERT INTO `formaciones` (`id`, `nombre`, `descripcion`, `max_jugadores`, `created_at`) VALUES
(1, '4-3-3', '4 Defensas, 3 Mediocampistas, 3 Delanteros', 11, '2025-10-23 14:38:04'),
(2, '3-5-2', '3 Defensas, 5 Mediocampistas, 2 Delanteros', 11, '2025-10-23 14:38:04'),
(3, '4-4-2', '4 Defensas, 4 Mediocampistas, 2 Delanteros', 11, '2025-10-23 14:38:04'),
(4, '4-5-1', NULL, 11, '2025-10-23 18:12:53'),
(5, '3-4-3', NULL, 11, '2025-10-23 18:12:53'),
(6, '4-2-4', NULL, 11, '2025-10-23 18:55:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jornadas`
--

CREATE TABLE `jornadas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_mercado_inicio` datetime DEFAULT NULL,
  `fecha_mercado_fin` datetime DEFAULT NULL,
  `fecha_puntuacion_inicio` datetime DEFAULT NULL,
  `fecha_puntuacion_fin` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_market_open` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `jornadas`
--

INSERT INTO `jornadas` (`id`, `nombre`, `fecha_mercado_inicio`, `fecha_mercado_fin`, `fecha_puntuacion_inicio`, `fecha_puntuacion_fin`, `active`, `created_at`, `is_market_open`) VALUES
(1, 'Jornada 1', '2025-10-10 00:00:00', '2025-10-13 23:59:59', NULL, '2025-10-14 23:59:59', 0, '2025-10-26 22:27:56', 0),
(2, 'Jornada 2', '2025-10-17 00:00:00', '2025-10-20 23:59:59', NULL, '2025-10-21 23:59:59', 1, '2025-10-26 22:27:56', 1),
(11, 'Jornada 3', '2025-10-31 00:00:00', NULL, NULL, '2025-11-04 23:59:59', 0, '2025-11-01 04:08:49', 0),
(12, 'Jornada 4', '2025-11-07 00:00:00', NULL, NULL, '2025-11-11 23:59:59', 0, '2025-11-01 04:08:49', 0),
(13, 'Jornada 5', '2025-11-14 00:00:00', NULL, NULL, '2025-11-18 23:59:59', 0, '2025-11-01 04:08:49', 0),
(14, 'Jornada 6', '2025-11-21 00:00:00', NULL, NULL, '2025-11-25 23:59:59', 0, '2025-11-01 04:08:49', 0),
(15, 'Jornada 7', '2025-11-28 00:00:00', NULL, NULL, '2025-12-02 23:59:59', 0, '2025-11-01 04:08:49', 0),
(16, 'Jornada 8', '2025-12-05 00:00:00', NULL, NULL, '2025-12-09 23:59:59', 0, '2025-11-01 04:08:49', 0),
(17, 'Jornada 9', '2025-12-12 00:00:00', NULL, NULL, '2025-12-16 23:59:59', 0, '2025-11-01 04:08:49', 0),
(18, 'Jornada 10', '2025-12-19 00:00:00', NULL, NULL, '2025-12-23 23:59:59', 0, '2025-11-01 04:08:49', 0),
(19, 'Jornada 11', '2025-12-26 00:00:00', NULL, NULL, '2025-12-30 23:59:59', 0, '2025-11-01 04:08:49', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `players`
--

CREATE TABLE `players` (
  `p_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `goals` int(11) DEFAULT 0,
  `assists` int(11) DEFAULT 0,
  `yellow_cards` int(11) DEFAULT 0,
  `red_cards` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `score` decimal(5,2) DEFAULT 0.00,
  `score_date` date DEFAULT curdate(),
  `final_score` decimal(5,2) DEFAULT 0.00,
  `posicion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `players`
--

INSERT INTO `players` (`p_id`, `name`, `position`, `goals`, `assists`, `yellow_cards`, `red_cards`, `price`, `score`, `score_date`, `final_score`, `posicion`) VALUES
(4, 'Salomón Rondón', 'Atacante', 0, 0, 0, 0, 1800000.00, 0.00, '2025-10-31', -1.50, NULL),
(5, 'Yangel Herrera', 'Mediocampista', 0, 0, 0, 0, 16500000.00, 0.00, '2025-10-31', -1.50, NULL),
(6, 'Jon Aramburu', 'Defensa', 0, 0, 0, 0, 22500000.00, 0.00, '2025-10-31', -1.50, NULL),
(7, 'Cristian Cásseres Jr', 'Mediocampista', 0, 0, 0, 0, 12000000.00, 0.00, '2025-10-31', -1.50, NULL),
(8, 'Nahuel Ferraresi', 'Defensa', 0, 0, 0, 0, 7500000.00, 0.00, '2025-10-31', -1.50, NULL),
(9, 'Kevin Kelsy', 'Atacante', 0, 0, 0, 0, 6000000.00, 0.00, '2025-10-31', -1.50, NULL),
(10, 'Telasco Segovia', 'Mediocampista', 0, 0, 0, 0, 4500000.00, 0.00, '2025-10-31', -1.50, NULL),
(11, 'Alejandro Marqués', 'Atacante', 0, 0, 0, 0, 4000000.00, 0.00, '2025-10-31', -1.50, NULL),
(12, 'Wikelman Carmona', 'Mediocampista', 0, 0, 0, 0, 2700000.00, 0.00, '2025-10-31', -1.50, NULL),
(13, 'Kervin Tuti Andrade', 'Atacante', 0, 0, 0, 0, 2250000.00, 0.00, '2025-10-31', -1.50, NULL),
(14, 'Carlos Pipo Vivas', 'Defensa', 0, 0, 0, 0, 2100000.00, 0.00, '2025-10-31', -1.50, NULL),
(15, 'Jesús Ramírez', 'Atacante', 0, 0, 0, 0, 2000000.00, 0.00, '2025-10-31', -1.50, NULL),
(16, 'Ender Echenique', 'Atacante', 0, 0, 0, 0, 1800000.00, 0.00, '2025-10-31', -1.50, NULL),
(17, 'José Contreras', 'Portero', 0, 0, 0, 0, 1200000.00, 0.00, '2025-10-31', -1.50, NULL),
(18, 'Joel Graterol', 'Portero', 0, 0, 0, 0, 1100000.00, 0.00, '2025-10-31', -1.50, NULL),
(19, 'Matías Lacava', 'Atacante', 0, 0, 0, 0, 975000.00, 0.00, '2025-10-31', -1.50, NULL),
(20, 'Jesús Bueno', 'Mediocampista', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(21, 'Teo Quintero', 'Defensa', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(22, 'Daniel Pereira', 'Mediocampista', 0, 0, 0, 0, 4500000.00, 0.00, '2025-10-31', -1.50, NULL),
(23, 'David Martínez', 'Atacante', 0, 0, 0, 0, 4500000.00, 0.00, '2025-10-31', -1.50, NULL),
(24, 'Josef Martínez', 'Atacante', 0, 0, 0, 0, 4500000.00, 0.00, '2025-10-31', -1.50, NULL),
(25, 'Jesús Bueno', 'Mediocampista', 0, 0, 0, 0, 1000000.00, 0.00, '2025-10-31', -1.50, NULL),
(26, 'Ronald Hernández', 'Defensa', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(27, 'Gustavo Caraballo', 'Atacante', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(28, 'Javier Otero', 'Portero', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(29, 'Samuel Rodríguez', 'Portero', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(30, 'Ernesto Torregrossa', 'Atacante', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(31, 'Alejandro Cichero', 'Atacante', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(32, 'Daniele Quieto', 'Atacante', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(33, 'Alessandro Milani', 'Defensa', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(34, 'Lorenzo D´Agostini', 'Atacante', 0, 0, 0, 0, 550000.00, 0.00, '2025-10-31', -1.50, NULL),
(35, 'Luis Balbo', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(36, 'Enrique Peña Zauner', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(37, 'Yeferson Soteldo', 'Atacante', 0, 0, 0, 0, 8000000.00, 0.00, '2025-10-31', -1.50, NULL),
(38, 'José \"El Brujo\" Martínez', 'Mediocampista', 0, 0, 0, 0, 3500000.00, 0.00, '2025-10-31', -1.50, NULL),
(39, 'Esli García', 'Atacante', 0, 0, 0, 0, 1000000.00, 0.00, '2025-10-31', -1.50, NULL),
(40, 'Tomás Rincón', 'Mediocampista', 0, 0, 0, 0, 700000.00, 0.00, '2025-10-31', -1.50, NULL),
(41, 'Wilker Ángel', 'Defensa', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(42, 'Nicola Profeta', 'Mediocampista', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(43, 'Alexander González', 'Defensa', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(44, 'Christian Larotonda', 'Defensa', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(45, 'José Luis Marrufo', 'Defensa', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(46, 'Rafael Romo', 'Portero', 0, 0, 0, 0, 1500000.00, 0.00, '2025-10-31', -1.50, NULL),
(47, 'Jhon Chancellor', 'Defensa', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(48, 'Yhonatham Yustiz', 'Portero', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(49, 'Richard Celis', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(50, 'Jhon Murillo', 'Atacante', 0, 0, 0, 0, 2500000.00, 0.00, '2025-10-31', -1.50, NULL),
(51, 'Delvin Alfonzo', 'Defensa', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(52, 'Leonardo Flores', 'Mediocampista', 0, 0, 0, 0, 650000.00, 0.00, '2025-10-31', -1.50, NULL),
(53, 'Jovanny Bolívar', 'Atacante', 0, 0, 0, 0, 650000.00, 0.00, '2025-10-31', -1.50, NULL),
(54, 'Henry Plazas', 'Defensa', 0, 0, 0, 0, 550000.00, 0.00, '2025-10-31', -1.50, NULL),
(55, 'Eduard Bello', 'Atacante', 0, 0, 0, 0, 1800000.00, 0.00, '2025-10-31', -1.50, NULL),
(56, 'Luis Guerra', 'Atacante', 0, 0, 0, 0, 1050000.00, 0.00, '2025-10-31', -1.50, NULL),
(57, 'Bianneider Tamayo', 'Defensa', 0, 0, 0, 0, 700000.00, 0.00, '2025-10-31', -1.50, NULL),
(58, 'Aaron Astudillo', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(59, 'Brayan Hurtado', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(60, 'Danny Pérez', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(61, 'Alain Baroja', 'Portero', 0, 0, 0, 0, 650000.00, 0.00, '2025-10-31', -1.50, NULL),
(62, 'Miguel Navarro', 'Defensa', 0, 0, 0, 0, 3200000.00, 0.00, '2025-10-31', -1.50, NULL),
(63, 'Jan Hurtado', 'Atacante', 0, 0, 0, 0, 650000.00, 0.00, '2025-10-31', -1.50, NULL),
(64, 'Andrusw Araujo', 'Mediocampista', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(65, 'Gleiker Mendoza', 'Atacante', 0, 0, 0, 0, 1200000.00, 0.00, '2025-10-31', -1.50, NULL),
(66, 'Bryan Castillo', 'Atacante', 0, 0, 0, 0, 800000.00, 0.00, '2025-10-31', -1.50, NULL),
(67, 'Luifer Hernández', 'Atacante', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(68, 'Carlos Paraco', 'Atacante', 0, 0, 0, 0, 650000.00, 0.00, '2025-10-31', -1.50, NULL),
(69, 'Carlos Rojas', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(70, 'Maiken González', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(71, 'Jhonder Cádiz', 'Atacante', 0, 0, 0, 0, 5000000.00, 0.00, '2025-10-31', -1.50, NULL),
(72, 'Javier Suárez', 'Defensa', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(73, 'Thomás Gutierrez', 'Defensa', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(74, 'Saúl Guarirapa', 'Atacante', 0, 0, 0, 0, 2250000.00, 0.00, '2025-10-31', -1.50, NULL),
(75, 'Bryant Ortega', 'Mediocampista', 0, 0, 0, 0, 900000.00, 0.00, '2025-10-31', -1.50, NULL),
(76, 'Renne Rivas', 'Defensa', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(77, 'Yerson Chacón', 'Atacante', 0, 0, 0, 0, 1300000.00, 0.00, '2025-10-31', -1.50, NULL),
(78, 'José Romo', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(79, 'Eric Ramírez', 'Atacante', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(80, 'Andrés \"Miki\" Romero', 'Mediocampista', 0, 0, 0, 0, 1000000.00, 0.00, '2025-10-31', -1.50, NULL),
(81, 'Rómulo Otero', 'Atacante', 0, 0, 0, 0, 600000.00, 0.00, '2025-10-31', -1.50, NULL),
(82, 'Alex Custodio', 'Defensa', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(83, 'Christian Makoun', 'Defensa', 0, 0, 0, 0, 3750000.00, 0.00, '2025-10-31', -1.50, NULL),
(84, 'Adrián Cova', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(85, 'Mikel Villanueva', 'Defensa', 0, 0, 0, 0, 1500000.00, 0.00, '2025-10-31', -1.50, NULL),
(86, 'Brayan Palmezano', 'Mediocampista', 0, 0, 0, 0, 1350000.00, 0.00, '2025-10-31', -1.50, NULL),
(87, 'Juanpi Añor', 'Atacante', 0, 0, 0, 0, 700000.00, 0.00, '2025-10-31', -1.50, NULL),
(88, 'Sergio Córdova', 'Atacante', 0, 0, 0, 0, 2600000.00, 0.00, '2025-10-31', -1.50, NULL),
(89, 'Teo Quintero', 'Defensa', 0, 0, 0, 0, 1000000.00, 0.00, '2025-10-31', -1.50, NULL),
(90, 'Yiandro Raap', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(91, 'Josua Mejías', 'Defensa', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(92, 'Kervin \"Tuti\" Andrade', 'Atacante', 0, 0, 0, 0, 2250000.00, 0.00, '2025-10-31', -1.50, NULL),
(93, 'Adrian Palacios', 'Defensa', 0, 0, 0, 0, 1250000.00, 0.00, '2025-10-31', -1.50, NULL),
(94, 'Diego Luna', 'Defensa', 0, 0, 0, 0, 750000.00, 0.00, '2025-10-31', -1.50, NULL),
(95, 'Ronaldo Chacón', 'Atacante', 0, 0, 0, 0, 525000.00, 0.00, '2025-10-31', -1.50, NULL),
(96, 'Jorge Yriarte', 'Mediocampista', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(97, 'Carlos Olses', 'Portero', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(98, 'Juan Arango Jr', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(99, 'Giovanny Sequera', 'Defensa', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(100, 'Santos Torrealba', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(101, 'Mathías González', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(102, 'Daniel Pérez', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(103, 'Leenhan Romero', 'Atacante', 0, 0, 0, 0, 500000.00, 0.00, '2025-10-31', -1.50, NULL),
(104, 'Jefferson Savarino', 'Atacante', 0, 0, 0, 0, 15000000.00, 0.00, '2025-10-31', -1.50, 'Atacante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `player_jornada_scores`
--

CREATE TABLE `player_jornada_scores` (
  `player_id` int(11) NOT NULL,
  `jornada_id` int(11) NOT NULL,
  `base_score` decimal(5,2) DEFAULT 0.00,
  `gol` tinyint(1) DEFAULT 0,
  `asistencia` tinyint(1) DEFAULT 0,
  `final_score` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `player_jornada_scores`
--

INSERT INTO `player_jornada_scores` (`player_id`, `jornada_id`, `base_score`, `gol`, `asistencia`, `final_score`) VALUES
(4, 1, 0.00, 0, 0, -1.50),
(4, 2, 0.00, 0, 0, -1.50),
(4, 11, 0.00, 0, 0, -1.50),
(4, 19, 0.00, 0, 0, -1.50),
(5, 1, 0.00, 0, 0, -1.50),
(5, 2, 0.00, 0, 0, -1.50),
(5, 11, 0.00, 0, 0, -1.50),
(5, 19, 0.00, 0, 0, -1.50),
(6, 1, 0.00, 0, 0, -1.50),
(6, 2, 0.00, 0, 0, -1.50),
(6, 11, 0.00, 0, 0, -1.50),
(6, 19, 0.00, 0, 0, -1.50),
(7, 1, 0.00, 0, 0, -1.50),
(7, 2, 0.00, 0, 0, -1.50),
(7, 11, 9.50, 0, 0, 8.00),
(7, 19, 8.50, 0, 0, 7.00),
(8, 1, 0.00, 0, 0, -1.50),
(8, 2, 0.00, 0, 0, -1.50),
(8, 11, 0.00, 0, 0, -1.50),
(8, 19, 0.00, 0, 0, -1.50),
(9, 1, 0.00, 0, 0, -1.50),
(9, 2, 0.00, 0, 0, -1.50),
(9, 11, 0.00, 0, 0, -1.50),
(9, 19, 0.00, 0, 0, -1.50),
(10, 1, 0.00, 0, 0, -1.50),
(10, 2, 0.00, 0, 0, -1.50),
(10, 11, 0.00, 0, 0, -1.50),
(10, 19, 0.00, 0, 0, -1.50),
(11, 1, 0.00, 0, 0, -1.50),
(11, 2, 0.00, 0, 0, -1.50),
(11, 11, 0.00, 0, 0, -1.50),
(11, 19, 0.00, 0, 0, -1.50),
(12, 1, 0.00, 0, 0, -1.50),
(12, 2, 0.00, 0, 0, -1.50),
(12, 11, 0.00, 0, 0, -1.50),
(12, 19, 0.00, 0, 0, -1.50),
(13, 1, 0.00, 0, 0, -1.50),
(13, 2, 0.00, 0, 0, -1.50),
(13, 11, 0.00, 0, 0, -1.50),
(13, 19, 0.00, 0, 0, -1.50),
(14, 1, 0.00, 0, 0, -1.50),
(14, 2, 0.00, 0, 0, -1.50),
(14, 11, 0.00, 0, 0, -1.50),
(14, 19, 0.00, 0, 0, -1.50),
(15, 1, 0.00, 0, 0, -1.50),
(15, 2, 0.00, 0, 0, -1.50),
(15, 11, 0.00, 0, 0, -1.50),
(15, 19, 0.00, 0, 0, -1.50),
(16, 1, 0.00, 0, 0, -1.50),
(16, 2, 0.00, 0, 0, -1.50),
(16, 11, 0.00, 0, 0, -1.50),
(16, 19, 0.00, 0, 0, -1.50),
(17, 1, 0.00, 0, 0, -1.50),
(17, 2, 0.00, 0, 0, -1.50),
(17, 11, 7.50, 0, 0, 6.00),
(17, 19, 0.00, 0, 0, -1.50),
(18, 1, 0.00, 0, 0, -1.50),
(18, 2, 0.00, 0, 0, -1.50),
(18, 11, 0.00, 0, 0, -1.50),
(18, 19, 0.00, 0, 0, -1.50),
(19, 1, 0.00, 0, 0, -1.50),
(19, 2, 0.00, 0, 0, -1.50),
(19, 11, 0.00, 0, 0, -1.50),
(19, 19, 0.00, 0, 0, -1.50),
(20, 1, 0.00, 0, 0, -1.50),
(20, 2, 0.00, 0, 0, -1.50),
(20, 11, 0.00, 0, 0, -1.50),
(20, 19, 0.00, 0, 0, -1.50),
(21, 1, 0.00, 0, 0, -1.50),
(21, 2, 0.00, 0, 0, -1.50),
(21, 11, 0.00, 0, 0, -1.50),
(21, 19, 0.00, 0, 0, -1.50),
(22, 1, 0.00, 0, 0, -1.50),
(22, 2, 0.00, 0, 0, -1.50),
(22, 11, 0.00, 0, 0, -1.50),
(22, 19, 0.00, 0, 0, -1.50),
(23, 1, 0.00, 0, 0, -1.50),
(23, 2, 0.00, 0, 0, -1.50),
(23, 11, 0.00, 0, 0, -1.50),
(23, 19, 0.00, 0, 0, -1.50),
(24, 1, 0.00, 0, 0, -1.50),
(24, 2, 0.00, 0, 0, -1.50),
(24, 11, 0.00, 0, 0, -1.50),
(24, 19, 0.00, 0, 0, -1.50),
(25, 1, 0.00, 0, 0, -1.50),
(25, 2, 0.00, 0, 0, -1.50),
(25, 11, 0.00, 0, 0, -1.50),
(25, 19, 0.00, 0, 0, -1.50),
(26, 1, 0.00, 0, 0, -1.50),
(26, 2, 0.00, 0, 0, -1.50),
(26, 11, 0.00, 0, 0, -1.50),
(26, 19, 0.00, 0, 0, -1.50),
(27, 1, 0.00, 0, 0, -1.50),
(27, 2, 0.00, 0, 0, -1.50),
(27, 11, 0.00, 0, 0, -1.50),
(27, 19, 0.00, 0, 0, -1.50),
(28, 1, 0.00, 0, 0, -1.50),
(28, 2, 0.00, 0, 0, -1.50),
(28, 11, 0.00, 0, 0, -1.50),
(28, 19, 0.00, 0, 0, -1.50),
(29, 1, 0.00, 0, 0, -1.50),
(29, 2, 0.00, 0, 0, -1.50),
(29, 11, 0.00, 0, 0, -1.50),
(29, 19, 0.00, 0, 0, -1.50),
(30, 1, 0.00, 0, 0, -1.50),
(30, 2, 0.00, 0, 0, -1.50),
(30, 11, 0.00, 0, 0, -1.50),
(30, 19, 0.00, 0, 0, -1.50),
(31, 1, 0.00, 0, 0, -1.50),
(31, 2, 0.00, 0, 0, -1.50),
(31, 11, 0.00, 0, 0, -1.50),
(31, 19, 0.00, 0, 0, -1.50),
(32, 1, 0.00, 0, 0, -1.50),
(32, 2, 0.00, 0, 0, -1.50),
(32, 11, 0.00, 0, 0, -1.50),
(32, 19, 0.00, 0, 0, -1.50),
(33, 1, 0.00, 0, 0, -1.50),
(33, 2, 0.00, 0, 0, -1.50),
(33, 11, 0.00, 0, 0, -1.50),
(33, 19, 0.00, 0, 0, -1.50),
(34, 1, 0.00, 0, 0, -1.50),
(34, 2, 0.00, 0, 0, -1.50),
(34, 11, 0.00, 0, 0, -1.50),
(34, 19, 0.00, 0, 0, -1.50),
(35, 1, 0.00, 0, 0, -1.50),
(35, 2, 0.00, 0, 0, -1.50),
(35, 11, 0.00, 0, 0, -1.50),
(35, 19, 0.00, 0, 0, -1.50),
(36, 1, 0.00, 0, 0, -1.50),
(36, 2, 0.00, 0, 0, -1.50),
(36, 11, 0.00, 0, 0, -1.50),
(36, 19, 0.00, 0, 0, -1.50),
(37, 1, 0.00, 0, 0, -1.50),
(37, 2, 0.00, 0, 0, -1.50),
(37, 11, 0.00, 0, 0, -1.50),
(37, 19, 0.00, 0, 0, -1.50),
(38, 1, 0.00, 0, 0, -1.50),
(38, 2, 0.00, 0, 0, -1.50),
(38, 11, 0.00, 0, 0, -1.50),
(38, 19, 0.00, 0, 0, -1.50),
(39, 1, 0.00, 0, 0, -1.50),
(39, 2, 0.00, 0, 0, -1.50),
(39, 11, 0.00, 0, 0, -1.50),
(39, 19, 0.00, 0, 0, -1.50),
(40, 1, 0.00, 0, 0, -1.50),
(40, 2, 0.00, 0, 0, -1.50),
(40, 11, 0.00, 0, 0, -1.50),
(40, 19, 0.00, 0, 0, -1.50),
(41, 1, 0.00, 0, 0, -1.50),
(41, 2, 0.00, 0, 0, -1.50),
(41, 11, 0.00, 0, 0, -1.50),
(41, 19, 0.00, 0, 0, -1.50),
(42, 1, 0.00, 0, 0, -1.50),
(42, 2, 0.00, 0, 0, -1.50),
(42, 11, 0.00, 0, 0, -1.50),
(42, 19, 0.00, 0, 0, -1.50),
(43, 1, 0.00, 0, 0, -1.50),
(43, 2, 0.00, 0, 0, -1.50),
(43, 11, 0.00, 0, 0, -1.50),
(43, 19, 0.00, 0, 0, -1.50),
(44, 1, 0.00, 0, 0, -1.50),
(44, 2, 0.00, 0, 0, -1.50),
(44, 11, 0.00, 0, 0, -1.50),
(44, 19, 0.00, 0, 0, -1.50),
(45, 1, 0.00, 0, 0, -1.50),
(45, 2, 0.00, 0, 0, -1.50),
(45, 11, 0.00, 0, 0, -1.50),
(45, 19, 0.00, 0, 0, -1.50),
(46, 1, 7.00, 0, 0, 5.50),
(46, 2, 0.00, 0, 0, -1.50),
(46, 11, 8.00, 0, 0, 6.50),
(46, 19, 0.00, 0, 0, -1.50),
(47, 1, 0.00, 0, 0, -1.50),
(47, 2, 0.00, 0, 0, -1.50),
(47, 11, 0.00, 0, 0, -1.50),
(47, 19, 0.00, 0, 0, -1.50),
(48, 1, 0.00, 0, 0, -1.50),
(48, 2, 0.00, 0, 0, -1.50),
(48, 11, 0.00, 0, 0, -1.50),
(48, 19, 0.00, 0, 0, -1.50),
(49, 1, 0.00, 0, 0, -1.50),
(49, 2, 0.00, 0, 0, -1.50),
(49, 11, 0.00, 0, 0, -1.50),
(49, 19, 0.00, 0, 0, -1.50),
(50, 1, 0.00, 0, 0, -1.50),
(50, 2, 0.00, 0, 0, -1.50),
(50, 11, 0.00, 0, 0, -1.50),
(50, 19, 0.00, 0, 0, -1.50),
(51, 1, 0.00, 0, 0, -1.50),
(51, 2, 0.00, 0, 0, -1.50),
(51, 11, 0.00, 0, 0, -1.50),
(51, 19, 0.00, 0, 0, -1.50),
(52, 1, 0.00, 0, 0, -1.50),
(52, 2, 0.00, 0, 0, -1.50),
(52, 11, 0.00, 0, 0, -1.50),
(52, 19, 0.00, 0, 0, -1.50),
(53, 1, 0.00, 0, 0, -1.50),
(53, 2, 0.00, 0, 0, -1.50),
(53, 11, 0.00, 0, 0, -1.50),
(53, 19, 0.00, 0, 0, -1.50),
(54, 1, 0.00, 0, 0, -1.50),
(54, 2, 0.00, 0, 0, -1.50),
(54, 11, 0.00, 0, 0, -1.50),
(54, 19, 0.00, 0, 0, -1.50),
(55, 1, 0.00, 0, 0, -1.50),
(55, 2, 0.00, 0, 0, -1.50),
(55, 11, 0.00, 0, 0, -1.50),
(55, 19, 0.00, 0, 0, -1.50),
(56, 1, 0.00, 0, 0, -1.50),
(56, 2, 0.00, 0, 0, -1.50),
(56, 11, 0.00, 0, 0, -1.50),
(56, 19, 0.00, 0, 0, -1.50),
(57, 1, 0.00, 0, 0, -1.50),
(57, 2, 0.00, 0, 0, -1.50),
(57, 11, 0.00, 0, 0, -1.50),
(57, 19, 0.00, 0, 0, -1.50),
(58, 1, 0.00, 0, 0, -1.50),
(58, 2, 0.00, 0, 0, -1.50),
(58, 11, 0.00, 0, 0, -1.50),
(58, 19, 0.00, 0, 0, -1.50),
(59, 1, 0.00, 0, 0, -1.50),
(59, 2, 0.00, 0, 0, -1.50),
(59, 11, 0.00, 0, 0, -1.50),
(59, 19, 0.00, 0, 0, -1.50),
(60, 1, 0.00, 0, 0, -1.50),
(60, 2, 0.00, 0, 0, -1.50),
(60, 11, 0.00, 0, 0, -1.50),
(60, 19, 0.00, 0, 0, -1.50),
(61, 1, 0.00, 0, 0, -1.50),
(61, 2, 0.00, 0, 0, -1.50),
(61, 11, 0.00, 0, 0, -1.50),
(61, 19, 0.00, 0, 0, -1.50),
(62, 1, 0.00, 0, 0, -1.50),
(62, 2, 0.00, 0, 0, -1.50),
(62, 11, 0.00, 0, 0, -1.50),
(62, 19, 0.00, 0, 0, -1.50),
(63, 1, 0.00, 0, 0, -1.50),
(63, 2, 0.00, 0, 0, -1.50),
(63, 11, 0.00, 0, 0, -1.50),
(63, 19, 0.00, 0, 0, -1.50),
(64, 1, 0.00, 0, 0, -1.50),
(64, 2, 0.00, 0, 0, -1.50),
(64, 11, 0.00, 0, 0, -1.50),
(64, 19, 0.00, 0, 0, -1.50),
(65, 1, 0.00, 0, 0, -1.50),
(65, 2, 0.00, 0, 0, -1.50),
(65, 11, 0.00, 0, 0, -1.50),
(65, 19, 0.00, 0, 0, -1.50),
(66, 1, 0.00, 0, 0, -1.50),
(66, 2, 0.00, 0, 0, -1.50),
(66, 11, 0.00, 0, 0, -1.50),
(66, 19, 0.00, 0, 0, -1.50),
(67, 1, 0.00, 0, 0, -1.50),
(67, 2, 0.00, 0, 0, -1.50),
(67, 11, 0.00, 0, 0, -1.50),
(67, 19, 0.00, 0, 0, -1.50),
(68, 1, 0.00, 0, 0, -1.50),
(68, 2, 0.00, 0, 0, -1.50),
(68, 11, 0.00, 0, 0, -1.50),
(68, 19, 0.00, 0, 0, -1.50),
(69, 1, 0.00, 0, 0, -1.50),
(69, 2, 0.00, 0, 0, -1.50),
(69, 11, 0.00, 0, 0, -1.50),
(69, 19, 0.00, 0, 0, -1.50),
(70, 1, 0.00, 0, 0, -1.50),
(70, 2, 0.00, 0, 0, -1.50),
(70, 11, 0.00, 0, 0, -1.50),
(70, 19, 0.00, 0, 0, -1.50),
(71, 1, 0.00, 0, 0, -1.50),
(71, 2, 0.00, 0, 0, -1.50),
(71, 11, 0.00, 0, 0, -1.50),
(71, 19, 0.00, 0, 0, -1.50),
(72, 1, 0.00, 0, 0, -1.50),
(72, 2, 0.00, 0, 0, -1.50),
(72, 11, 0.00, 0, 0, -1.50),
(72, 19, 0.00, 0, 0, -1.50),
(73, 1, 0.00, 0, 0, -1.50),
(73, 2, 0.00, 0, 0, -1.50),
(73, 11, 0.00, 0, 0, -1.50),
(73, 19, 0.00, 0, 0, -1.50),
(74, 1, 0.00, 0, 0, -1.50),
(74, 2, 0.00, 0, 0, -1.50),
(74, 11, 0.00, 0, 0, -1.50),
(74, 19, 0.00, 0, 0, -1.50),
(75, 1, 0.00, 0, 0, -1.50),
(75, 2, 0.00, 0, 0, -1.50),
(75, 11, 0.00, 0, 0, -1.50),
(75, 19, 0.00, 0, 0, -1.50),
(76, 1, 0.00, 0, 0, -1.50),
(76, 2, 0.00, 0, 0, -1.50),
(76, 11, 0.00, 0, 0, -1.50),
(76, 19, 0.00, 0, 0, -1.50),
(77, 1, 0.00, 0, 0, -1.50),
(77, 2, 0.00, 0, 0, -1.50),
(77, 11, 0.00, 0, 0, -1.50),
(77, 19, 0.00, 0, 0, -1.50),
(78, 1, 0.00, 0, 0, -1.50),
(78, 2, 0.00, 0, 0, -1.50),
(78, 11, 0.00, 0, 0, -1.50),
(78, 19, 0.00, 0, 0, -1.50),
(79, 1, 0.00, 0, 0, -1.50),
(79, 2, 0.00, 0, 0, -1.50),
(79, 11, 0.00, 0, 0, -1.50),
(79, 19, 0.00, 0, 0, -1.50),
(80, 1, 0.00, 0, 0, -1.50),
(80, 2, 0.00, 0, 0, -1.50),
(80, 11, 0.00, 0, 0, -1.50),
(80, 19, 0.00, 0, 0, -1.50),
(81, 1, 0.00, 0, 0, -1.50),
(81, 2, 0.00, 0, 0, -1.50),
(81, 11, 0.00, 0, 0, -1.50),
(81, 19, 0.00, 0, 0, -1.50),
(82, 1, 0.00, 0, 0, -1.50),
(82, 2, 0.00, 0, 0, -1.50),
(82, 11, 0.00, 0, 0, -1.50),
(82, 19, 0.00, 0, 0, -1.50),
(83, 1, 0.00, 0, 0, -1.50),
(83, 2, 0.00, 0, 0, -1.50),
(83, 11, 0.00, 0, 0, -1.50),
(83, 19, 0.00, 0, 0, -1.50),
(84, 1, 0.00, 0, 0, -1.50),
(84, 2, 0.00, 0, 0, -1.50),
(84, 11, 0.00, 0, 0, -1.50),
(84, 19, 0.00, 0, 0, -1.50),
(85, 1, 0.00, 0, 0, -1.50),
(85, 2, 0.00, 0, 0, -1.50),
(85, 11, 0.00, 0, 0, -1.50),
(85, 19, 0.00, 0, 0, -1.50),
(86, 1, 0.00, 0, 0, -1.50),
(86, 2, 0.00, 0, 0, -1.50),
(86, 11, 0.00, 0, 0, -1.50),
(86, 19, 0.00, 0, 0, -1.50),
(87, 1, 0.00, 0, 0, -1.50),
(87, 2, 0.00, 0, 0, -1.50),
(87, 11, 0.00, 0, 0, -1.50),
(87, 19, 0.00, 0, 0, -1.50),
(88, 1, 0.00, 0, 0, -1.50),
(88, 2, 0.00, 0, 0, -1.50),
(88, 11, 0.00, 0, 0, -1.50),
(88, 19, 0.00, 0, 0, -1.50),
(89, 1, 0.00, 0, 0, -1.50),
(89, 2, 0.00, 0, 0, -1.50),
(89, 11, 0.00, 0, 0, -1.50),
(89, 19, 0.00, 0, 0, -1.50),
(90, 1, 0.00, 0, 0, -1.50),
(90, 2, 0.00, 0, 0, -1.50),
(90, 11, 0.00, 0, 0, -1.50),
(90, 19, 0.00, 0, 0, -1.50),
(91, 1, 0.00, 0, 0, -1.50),
(91, 2, 0.00, 0, 0, -1.50),
(91, 11, 0.00, 0, 0, -1.50),
(91, 19, 0.00, 0, 0, -1.50),
(92, 1, 0.00, 0, 0, -1.50),
(92, 2, 0.00, 0, 0, -1.50),
(92, 11, 0.00, 0, 0, -1.50),
(92, 19, 0.00, 0, 0, -1.50),
(93, 1, 0.00, 0, 0, -1.50),
(93, 2, 0.00, 0, 0, -1.50),
(93, 11, 0.00, 0, 0, -1.50),
(93, 19, 0.00, 0, 0, -1.50),
(94, 1, 0.00, 0, 0, -1.50),
(94, 2, 0.00, 0, 0, -1.50),
(94, 11, 0.00, 0, 0, -1.50),
(94, 19, 0.00, 0, 0, -1.50),
(95, 1, 0.00, 0, 0, -1.50),
(95, 2, 0.00, 0, 0, -1.50),
(95, 11, 0.00, 0, 0, -1.50),
(95, 19, 0.00, 0, 0, -1.50),
(96, 1, 0.00, 0, 0, -1.50),
(96, 2, 0.00, 0, 0, -1.50),
(96, 11, 0.00, 0, 0, -1.50),
(96, 19, 0.00, 0, 0, -1.50),
(97, 1, 0.00, 0, 0, -1.50),
(97, 2, 0.00, 0, 0, -1.50),
(97, 11, 0.00, 0, 0, -1.50),
(97, 19, 0.00, 0, 0, -1.50),
(98, 1, 0.00, 0, 0, -1.50),
(98, 2, 0.00, 0, 0, -1.50),
(98, 11, 0.00, 0, 0, -1.50),
(98, 19, 0.00, 0, 0, -1.50),
(99, 1, 0.00, 0, 0, -1.50),
(99, 2, 0.00, 0, 0, -1.50),
(99, 11, 0.00, 0, 0, -1.50),
(99, 19, 0.00, 0, 0, -1.50),
(100, 1, 0.00, 0, 0, -1.50),
(100, 2, 0.00, 0, 0, -1.50),
(100, 11, 0.00, 0, 0, -1.50),
(100, 19, 0.00, 0, 0, -1.50),
(101, 1, 0.00, 0, 0, -1.50),
(101, 2, 0.00, 0, 0, -1.50),
(101, 11, 0.00, 0, 0, -1.50),
(101, 19, 0.00, 0, 0, -1.50),
(102, 1, 0.00, 0, 0, -1.50),
(102, 2, 0.00, 0, 0, -1.50),
(102, 11, 0.00, 0, 0, -1.50),
(102, 19, 0.00, 0, 0, -1.50),
(103, 1, 0.00, 0, 0, -1.50),
(103, 2, 0.00, 0, 0, -1.50),
(103, 11, 0.00, 0, 0, -1.50),
(103, 19, 0.00, 0, 0, -1.50),
(104, 1, 0.00, 0, 0, -1.50),
(104, 2, 0.00, 0, 0, -1.50),
(104, 11, 0.00, 0, 0, -1.50),
(104, 19, 0.00, 0, 0, -1.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntuaciones`
--

CREATE TABLE `puntuaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_jornada` int(11) NOT NULL,
  `puntos_totales` decimal(10,2) DEFAULT 0.00,
  `ranking_jornada` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `slots_formacion`
--

CREATE TABLE `slots_formacion` (
  `id` int(11) NOT NULL,
  `formacion_id` int(11) DEFAULT NULL,
  `posicion` varchar(20) NOT NULL,
  `cantidad` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `slots_formacion`
--

INSERT INTO `slots_formacion` (`id`, `formacion_id`, `posicion`, `cantidad`) VALUES
(1, 1, 'Portero', 1),
(2, 1, 'Defensa', 4),
(3, 1, 'Mediocampista', 3),
(4, 1, 'Atacante', 3),
(5, 2, 'Portero', 1),
(6, 2, 'Defensa', 3),
(7, 2, 'Mediocampista', 5),
(8, 2, 'Atacante', 2),
(9, 3, 'Portero', 1),
(10, 3, 'Defensa', 4),
(11, 3, 'Mediocampista', 4),
(12, 3, 'Atacante', 2),
(15, 4, 'Portero', 1),
(16, 4, 'Defensa', 4),
(17, 4, 'Mediocampista', 5),
(18, 4, 'Atacante', 1),
(19, 5, 'Portero', 1),
(20, 5, 'Defensa', 3),
(21, 5, 'Mediocampista', 4),
(22, 5, 'Atacante', 3),
(23, 6, 'Portero', 1),
(24, 6, 'Defensa', 4),
(25, 6, 'Mediocampista', 2),
(26, 6, 'Atacante', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `alias` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `subscription_active` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `capital` decimal(12,0) DEFAULT 60000000,
  `formacion_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `alias`, `password`, `subscription_active`, `reset_token`, `security_question`, `security_answer_hash`, `is_admin`, `capital`, `formacion_id`) VALUES
(3, 'naranjazos@gmail.com', 'user3', '$2y$10$X0m08MhVNdpqj8BmQX8wSOicZUzkx.qj52WSIkRz5H2aOkE5r9vFm', NULL, NULL, 'Nombre del primer colegio?', '$2y$10$uhS8gptj.7XFLj5nPylxW.lX5ftJI8jFkk01qS1Z6zSae7l7CaYjG', 1, 2550000, 2),
(4, 'yo@test.com', 'user4', '$2y$10$geRLXq0CSwABafF33RVI5OQgGP6sBFy.cJfxGBLZ3th5HmrcS74re', NULL, NULL, 'Nombre del primer colegio?', '$2y$10$pf.toB/DYD22s2qzItsvu.uvwWtc/welttIJVL1MR.CfjRbKyGk5K', 1, 750000, 6),
(6, 'ungiga.com@gmail.com', 'user6', '$2y$10$0ATSOufV8ilhgO40UjBFUucELKp6EaXma0596GznziuVKtA/kz86S', NULL, NULL, 'Nombre del primer colegio?', '$2y$10$3u3PZ30wiO6LQ.icJjFpXOiMkHhEpfZGpcxfWisYtabAnb9bcSeFG', 0, 60000000, 1),
(7, 'anvpro@gmail.com', 'user7', '$2y$10$aJ4f3dgK2N4/XhpbpwVIcen1Qfcv3QY44C3In2nhgxEaH8e3zbIYy', NULL, NULL, 'Nombre del primer colegio?', '$2y$10$kN3Efp1cjX.gl.85rw9Dt.RoHFKHsFTB5EDjRmdbTajUcu6DAVK.a', 0, 60000000, 1),
(9, 'sinruido@gmail.com', 'Epale', '$2y$10$4t4vAzluYqLyixJTdzb7nuA8/EUKVo4rCO1vWz4nwgkfsm7zQRUp2', NULL, NULL, 'Ciudad donde naciste?', '$2y$10$Uk9oypdYUoo9ljBg5SB6jeBywxyXOozTUU.ZeJIM9y0oY7eefvcbS', 0, 60000000, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `dream_team`
--
ALTER TABLE `dream_team`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_pid` (`email`,`pid`),
  ADD KEY `pid` (`pid`);

--
-- Indices de la tabla `dream_team_temp`
--
ALTER TABLE `dream_team_temp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_player` (`email`,`pid`);

--
-- Indices de la tabla `formaciones`
--
ALTER TABLE `formaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `jornadas`
--
ALTER TABLE `jornadas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`p_id`);

--
-- Indices de la tabla `player_jornada_scores`
--
ALTER TABLE `player_jornada_scores`
  ADD PRIMARY KEY (`player_id`,`jornada_id`),
  ADD KEY `jornada_id` (`jornada_id`);

--
-- Indices de la tabla `puntuaciones`
--
ALTER TABLE `puntuaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuario_jornada` (`id_usuario`,`id_jornada`),
  ADD KEY `id_jornada` (`id_jornada`);

--
-- Indices de la tabla `slots_formacion`
--
ALTER TABLE `slots_formacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formacion_id` (`formacion_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `alias` (`alias`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `dream_team`
--
ALTER TABLE `dream_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de la tabla `dream_team_temp`
--
ALTER TABLE `dream_team_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formaciones`
--
ALTER TABLE `formaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `jornadas`
--
ALTER TABLE `jornadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `players`
--
ALTER TABLE `players`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `puntuaciones`
--
ALTER TABLE `puntuaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `slots_formacion`
--
ALTER TABLE `slots_formacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `dream_team`
--
ALTER TABLE `dream_team`
  ADD CONSTRAINT `dream_team_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `players` (`p_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `player_jornada_scores`
--
ALTER TABLE `player_jornada_scores`
  ADD CONSTRAINT `player_jornada_scores_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`p_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_jornada_scores_ibfk_2` FOREIGN KEY (`jornada_id`) REFERENCES `jornadas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `puntuaciones`
--
ALTER TABLE `puntuaciones`
  ADD CONSTRAINT `puntuaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `puntuaciones_ibfk_2` FOREIGN KEY (`id_jornada`) REFERENCES `jornadas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `slots_formacion`
--
ALTER TABLE `slots_formacion`
  ADD CONSTRAINT `slots_formacion_ibfk_1` FOREIGN KEY (`formacion_id`) REFERENCES `formaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
