-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-12-2025 a las 09:04:54
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pasteleria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `id_proveedor`, `fecha`, `total`) VALUES
(1, 1, '2025-12-06 18:51:21', 3000.00),
(2, 1, '2025-12-07 00:33:41', 3000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_det`
--

CREATE TABLE `compras_det` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_compra` int(10) UNSIGNED NOT NULL,
  `id_item` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras_det`
--

INSERT INTO `compras_det` (`id`, `id_compra`, `id_item`, `cantidad`, `precio_unitario`, `total`) VALUES
(1, 1, 1, 10, 300.00, 3000.00),
(2, 2, 1, 10, 300.00, 3000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `motivo` varchar(255) DEFAULT 'Devolución Cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `devoluciones`
--

INSERT INTO `devoluciones` (`id`, `id_venta`, `fecha`, `id_usuario`, `motivo`) VALUES
(1, 5, '2025-12-06 18:59:33', 8, 'Devolución Cliente'),
(2, 5, '2025-12-06 18:59:39', 8, 'Devolución Cliente'),
(3, 7, '2025-12-06 20:18:27', 8, 'Devolución Cliente'),
(4, 7, '2025-12-06 20:18:43', 8, 'Devolución Cliente'),
(5, 8, '2025-12-06 20:19:20', 8, 'Devolución Cliente'),
(6, 8, '2025-12-06 20:19:25', 8, 'Devolución Cliente'),
(7, 8, '2025-12-06 20:19:40', 8, 'Devolución Cliente'),
(8, 8, '2025-12-06 20:19:47', 8, 'Devolución Cliente'),
(9, 8, '2025-12-06 20:19:52', 8, 'Devolución Cliente'),
(10, 9, '2025-12-06 20:31:31', 8, 'Devolución Cliente'),
(11, 11, '2025-12-06 20:32:19', 8, 'Devolución Cliente'),
(12, 5, '2025-12-06 22:57:40', 8, 'no me gustó');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones_det`
--

CREATE TABLE `devoluciones_det` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_devolucion` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `id_item` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `monto_devuelto` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `devoluciones_det`
--

INSERT INTO `devoluciones_det` (`id`, `id_devolucion`, `id_venta`, `id_item`, `cantidad`, `monto_devuelto`) VALUES
(1, 1, 5, 4, 1, 0.00),
(2, 2, 5, 3, 1, 0.00),
(3, 3, 7, 4, 1, 0.00),
(4, 4, 7, 4, 1, 0.00),
(5, 5, 8, 4, 1, 0.00),
(6, 6, 8, 4, 1, 0.00),
(7, 7, 8, 4, 1, 0.00),
(8, 8, 8, 4, 1, 0.00),
(9, 9, 8, 4, 5, 0.00),
(10, 10, 9, 6, 1, 0.00),
(11, 11, 11, 3, 1, 0.00),
(12, 12, 5, 4, 1, 0.00);

--
-- Disparadores `devoluciones_det`
--
DELIMITER $$
CREATE TRIGGER `trg_devolucion_detalle_insert` AFTER INSERT ON `devoluciones_det` FOR EACH ROW BEGIN UPDATE existencias SET cantidad = cantidad + NEW.cantidad WHERE id_item = NEW.id_item; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `existencias`
--

CREATE TABLE `existencias` (
  `id_item` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `existencias`
--

INSERT INTO `existencias` (`id_item`, `cantidad`) VALUES
(1, 38),
(2, 17),
(3, 16),
(4, 23),
(6, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_item`
--

CREATE TABLE `imagenes_item` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_item` int(10) UNSIGNED NOT NULL,
  `imagen` longblob NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `id_proveedor` int(10) UNSIGNED DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `id_proveedor`, `activo`, `creado_en`, `imagen`) VALUES
(1, 'PST-01', 'Pastel de chocolate con fresa', 'Pastel de chocolate con un toque de fresa.', 300.00, NULL, 1, '2025-12-07 01:47:43', 'prod_20251207024743.jpg'),
(2, 'PST-02', 'Pastel de fresa con vainilla', 'Pastel de fresa delicioso con un toque de vainilla.', 300.00, NULL, 1, '2025-12-07 01:48:28', 'prod_20251207024828.jpg'),
(3, 'PST-03', 'Pastel de tres leches', 'Pastel de tres leches delicioso.', 300.00, NULL, 1, '2025-12-07 01:49:13', 'prod_20251207024913.jpg'),
(4, 'PST-04', 'Pastel de mango', 'Pastel delicioso con un toque de mango.', 300.00, NULL, 1, '2025-12-07 01:49:42', 'prod_20251207024942.jpg'),
(6, 'PST-05', 'Pastel de unicornio', 'Pastel delicioso con diseño de unicornio.', 300.00, NULL, 1, '2025-12-07 01:50:44', 'prod_20251207025044.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `correo`, `telefono`, `activo`, `creado_en`) VALUES
(1, 'Panama', 'panama@panama.com', '555-134-1123', 1, '2025-12-07 01:46:23'),
(2, 'Dulces del norte', 'dulces@norte.com', '555-134-1555', 1, '2025-12-07 01:46:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `rol`, `activo`, `creado_en`) VALUES
(6, 'Valeria', 'valeria@gmail.com', '$2y$10$5GeiIiR084R5A8SHecctK.wJyWobC0lcYRwHIYRvVUZAFUnlVkjom', 'admin', 1, '2025-12-07 01:41:23'),
(8, 'Sara', 'sara@gmail.com', '$2y$10$XleXl4uNPmkWROoII.Gri.8kCXi2fre/O/yI4j9tQbltekNPIWtW6', 'usuario', 1, '2025-12-07 01:45:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `codigo_externo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `id_usuario`, `subtotal`, `iva`, `total`, `codigo_externo`) VALUES
(1, '2025-12-06 18:57:56', 8, 517.24, 82.76, 600.00, '1234'),
(2, '2025-12-06 18:58:07', 8, 775.86, 124.14, 900.00, '1111'),
(3, '2025-12-06 18:58:19', 8, 1034.48, 165.52, 1200.00, '5432'),
(4, '2025-12-06 18:58:32', 8, 517.24, 82.76, 600.00, '6666'),
(5, '2025-12-06 18:58:43', 8, 517.24, 82.76, 600.00, '12221'),
(6, '2025-12-06 20:17:53', 8, 258.62, 41.38, 300.00, '55'),
(7, '2025-12-06 20:18:13', 8, 258.62, 41.38, 300.00, '555'),
(8, '2025-12-06 20:19:05', 8, 1293.10, 206.90, 1500.00, '111'),
(9, '2025-12-06 20:28:22', 8, 258.62, 41.38, 300.00, NULL),
(10, '2025-12-06 20:31:54', 8, 258.62, 41.38, 300.00, '444'),
(11, '2025-12-06 20:32:06', 8, 258.62, 41.38, 300.00, '666'),
(12, '2025-12-06 20:41:13', 8, 258.62, 41.38, 300.00, '242'),
(13, '2025-12-06 20:41:53', 8, 258.62, 41.38, 300.00, '5235'),
(14, '2025-12-06 20:42:34', 8, 258.62, 41.38, 300.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_det`
--

CREATE TABLE `ventas_det` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `id_item` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `devuelto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas_det`
--

INSERT INTO `ventas_det` (`id`, `id_venta`, `id_item`, `cantidad`, `precio_unitario`, `total`, `devuelto`) VALUES
(1, 1, 3, 1, 300.00, 300.00, 0),
(2, 1, 4, 1, 300.00, 300.00, 0),
(3, 2, 3, 1, 300.00, 300.00, 0),
(4, 2, 1, 2, 300.00, 600.00, 0),
(5, 3, 6, 2, 300.00, 600.00, 0),
(6, 3, 2, 2, 300.00, 600.00, 0),
(7, 4, 4, 1, 300.00, 300.00, 0),
(8, 4, 3, 1, 300.00, 300.00, 0),
(9, 5, 4, 1, 300.00, 300.00, 1),
(10, 5, 3, 1, 300.00, 300.00, 0),
(11, 6, 2, 1, 300.00, 300.00, 0),
(12, 7, 4, 1, 300.00, 300.00, 0),
(13, 8, 4, 5, 300.00, 1500.00, 0),
(14, 9, 6, 1, 300.00, 300.00, 1),
(15, 10, 3, 1, 300.00, 300.00, 0),
(16, 11, 3, 1, 300.00, 300.00, 1),
(17, 12, 6, 1, 300.00, 300.00, 0),
(18, 13, 6, 1, 300.00, 300.00, 0),
(19, 14, 4, 1, 300.00, 300.00, 0);

--
-- Disparadores `ventas_det`
--
DELIMITER $$
CREATE TRIGGER `trg_venta_detalle_delete` AFTER DELETE ON `ventas_det` FOR EACH ROW BEGIN UPDATE existencias SET cantidad = cantidad + OLD.cantidad WHERE id_item = OLD.id_item; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_venta_detalle_insert` AFTER INSERT ON `ventas_det` FOR EACH ROW BEGIN 
    IF (SELECT cantidad FROM existencias WHERE id_item = NEW.id_item) < NEW.cantidad THEN 
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Inventario insuficiente.'; 
    END IF; 
    UPDATE existencias SET cantidad = cantidad - NEW.cantidad WHERE id_item = NEW.id_item; 
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `compras_det`
--
ALTER TABLE `compras_det`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `devoluciones_det`
--
ALTER TABLE `devoluciones_det`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_devolucion` (`id_devolucion`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `existencias`
--
ALTER TABLE `existencias`
  ADD PRIMARY KEY (`id_item`);

--
-- Indices de la tabla `imagenes_item`
--
ALTER TABLE `imagenes_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `ventas_det`
--
ALTER TABLE `ventas_det`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_item` (`id_item`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compras_det`
--
ALTER TABLE `compras_det`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `devoluciones_det`
--
ALTER TABLE `devoluciones_det`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `imagenes_item`
--
ALTER TABLE `imagenes_item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `ventas_det`
--
ALTER TABLE `ventas_det`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `compras_det`
--
ALTER TABLE `compras_det`
  ADD CONSTRAINT `compras_det_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_det_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`);

--
-- Filtros para la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD CONSTRAINT `devoluciones_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `devoluciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `devoluciones_det`
--
ALTER TABLE `devoluciones_det`
  ADD CONSTRAINT `devoluciones_det_ibfk_1` FOREIGN KEY (`id_devolucion`) REFERENCES `devoluciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `devoluciones_det_ibfk_2` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `devoluciones_det_ibfk_3` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`);

--
-- Filtros para la tabla `existencias`
--
ALTER TABLE `existencias`
  ADD CONSTRAINT `existencias_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_item`
--
ALTER TABLE `imagenes_item`
  ADD CONSTRAINT `imagenes_item_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ventas_det`
--
ALTER TABLE `ventas_det`
  ADD CONSTRAINT `ventas_det_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_det_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
