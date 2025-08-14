/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.4-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: seguimientoresidencias
-- ------------------------------------------------------
-- Server version	11.4.4-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumnos` (
  `IdAlumno` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoUsuario` int(11) DEFAULT NULL,
  `Matricula` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ApellidoPaterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ApellidoMaterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CorreoInstitucional` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IdProfesor` int(11) DEFAULT NULL,
  `PrimerLogin` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IdAsesor` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdAlumno`),
  UNIQUE KEY `Matricula` (`Matricula`),
  UNIQUE KEY `CorreoInstitucional` (`CorreoInstitucional`),
  KEY `IdTipoUsuario` (`IdTipoUsuario`),
  KEY `IdProfesor` (`IdProfesor`),
  KEY `fk_asesor` (`IdAsesor`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`IdTipoUsuario`) REFERENCES `tipousuario` (`IdTipo`),
  CONSTRAINT `alumnos_ibfk_2` FOREIGN KEY (`IdProfesor`) REFERENCES `profesores` (`IdProfesor`),
  CONSTRAINT `fk_asesor` FOREIGN KEY (`IdAsesor`) REFERENCES `asesores` (`IdAsesor`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES
(2,2,'202024135','Pablo Xiuhnel','Perez Amaya','Arellano','pablo_paa@tesch.edu.mx',1,'N',43),
(67,2,'10001','Juan','Robles','Perea','Juan.p@lol.com',1,'N',42),
(68,2,'10002','María','Rodríguez','Sánchez',NULL,1,'N',43),
(69,2,'10003','Pedro','López','Martínez',NULL,1,'N',NULL),
(70,2,'10004','Ana','García','Fernández',NULL,1,'N',NULL),
(71,2,'10005','Luis','Díaz','Romero',NULL,1,'N',NULL),
(72,2,'10006','Laura','Ruiz','López',NULL,1,'N',NULL),
(73,2,'10007','Carlos','González','Pérez',NULL,1,'N',NULL),
(74,2,'10008','Sofía','Morales','Castro',NULL,1,'N',NULL),
(75,2,'10009','Diego','Herrera','Soto',NULL,1,'N',NULL),
(76,2,'10010','Marta','Martínez','Fernández',NULL,1,'N',NULL),
(77,2,'aaa','AAAA','aaaaaaaa','aaaaaaa',NULL,1,'N',NULL),
(78,2,'111111111111','Potro','Jesus','BBBB',NULL,1,'N',NULL);
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER actualizar_nombre_usuarios
AFTER UPDATE ON Alumnos
FOR EACH ROW
BEGIN
    
    IF OLD.Nombre != NEW.Nombre THEN
        
        UPDATE Usuarios
        SET Nombre = NEW.Nombre
        WHERE Matricula = NEW.Matricula AND IdTipo = 2;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Table structure for table `asesores`
--

DROP TABLE IF EXISTS `asesores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asesores` (
  `IdAsesor` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoUsuario` int(11) DEFAULT NULL,
  `Matricula` varchar(255) DEFAULT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `ApellidoPaterno` varchar(255) DEFAULT NULL,
  `ApellidoMaterno` varchar(255) DEFAULT NULL,
  `IdCarrera` int(11) DEFAULT NULL,
  `Estado` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdAsesor`),
  KEY `IdTipoUsuario` (`IdTipoUsuario`),
  KEY `fk_IdCarrera` (`IdCarrera`),
  CONSTRAINT `asesores_ibfk_1` FOREIGN KEY (`IdTipoUsuario`) REFERENCES `tipousuario` (`IdTipo`),
  CONSTRAINT `fk_IdCarrera` FOREIGN KEY (`IdCarrera`) REFERENCES `carreras` (`IdCarrera`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asesores`
--

LOCK TABLES `asesores` WRITE;
/*!40000 ALTER TABLE `asesores` DISABLE KEYS */;
INSERT INTO `asesores` VALUES
(42,3,'111','Gloria Concepción','Tenorio','Sepúlveda',5,1),
(43,3,'222','Iván','Azamar','Palma',5,1);
/*!40000 ALTER TABLE `asesores` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_insert_asesores
AFTER INSERT ON Asesores
FOR EACH ROW
BEGIN
    INSERT INTO Usuarios (IdTipo, Nombre, Matricula)
    VALUES (NEW.IdTipoUsuario, NEW.Nombre, NEW.Matricula);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_delete_asesores
AFTER DELETE ON Asesores
FOR EACH ROW
BEGIN
    DELETE FROM Usuarios
    WHERE Matricula = OLD.Matricula;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `carreras`
--

DROP TABLE IF EXISTS `carreras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carreras` (
  `IdCarrera` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IdCarrera`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carreras`
--

LOCK TABLES `carreras` WRITE;
/*!40000 ALTER TABLE `carreras` DISABLE KEYS */;
INSERT INTO `carreras` VALUES
(1,'Ingeniería Electromecánica'),
(2,'Ingeniería Electrónica'),
(3,'Ingeniería Industrial'),
(4,'Ingeniería Informática'),
(5,'Ingeniería en Sistemas Computacionales'),
(6,'Ingeniería en Administración');
/*!40000 ALTER TABLE `carreras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docssubidos`
--

DROP TABLE IF EXISTS `docssubidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docssubidos` (
  `IdDocumento` int(11) NOT NULL AUTO_INCREMENT,
  `IdProfesor` int(11) NOT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `NombrePDF` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdDocumento`),
  KEY `IdProfesor` (`IdProfesor`),
  CONSTRAINT `docssubidos_ibfk_1` FOREIGN KEY (`IdProfesor`) REFERENCES `profesores` (`IdProfesor`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docssubidos`
--

LOCK TABLES `docssubidos` WRITE;
/*!40000 ALTER TABLE `docssubidos` DISABLE KEYS */;
INSERT INTO `docssubidos` VALUES
(1,1,'SOLICITUD_RP','Colorful Creative Concept Map Graph .pdf');
/*!40000 ALTER TABLE `docssubidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos` (
  `IdDocumento` int(11) NOT NULL AUTO_INCREMENT,
  `IdSeguimiento` int(11) DEFAULT NULL,
  `NombreDoc` varchar(255) DEFAULT NULL,
  `NombrePDF` varchar(255) DEFAULT NULL,
  `Observacion` varchar(255) DEFAULT NULL,
  `Estado` varchar(255) DEFAULT NULL,
  `FechaSubida` datetime DEFAULT NULL,
  PRIMARY KEY (`IdDocumento`),
  KEY `documentos_ibfk_1` (`IdSeguimiento`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`IdSeguimiento`) REFERENCES `seguimientos` (`IdSeguimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profesores`
--

DROP TABLE IF EXISTS `profesores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profesores` (
  `IdProfesor` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoUsuario` int(11) DEFAULT NULL,
  `Matricula` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ApellidoPaterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ApellidoMaterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IdCarrera` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdProfesor`),
  UNIQUE KEY `Matricula` (`Matricula`),
  KEY `IdTipoUsuario` (`IdTipoUsuario`),
  KEY `IdCarrera` (`IdCarrera`),
  CONSTRAINT `profesores_ibfk_1` FOREIGN KEY (`IdTipoUsuario`) REFERENCES `tipousuario` (`IdTipo`),
  CONSTRAINT `profesores_ibfk_2` FOREIGN KEY (`IdCarrera`) REFERENCES `carreras` (`IdCarrera`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profesores`
--

LOCK TABLES `profesores` WRITE;
/*!40000 ALTER TABLE `profesores` DISABLE KEYS */;
INSERT INTO `profesores` VALUES
(1,1,'11111','Monserrat','Gallardo','Bautista',5),
(2,1,'22222','Claudia','Guzmán','Barrera',5),
(6,1,'00000','Kani','Perez Amaya','Gallardo',1);
/*!40000 ALTER TABLE `profesores` ENABLE KEYS */;
UNLOCK TABLES;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_profesor_insert
AFTER INSERT ON Profesores
FOR EACH ROW
BEGIN
    INSERT INTO Usuarios (IdTipo, Matricula, Nombre, Password)
    VALUES (NEW.IdTipoUsuario, NEW.Matricula, NEW.Nombre, NULL);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Table structure for table `seguimientos`
--

DROP TABLE IF EXISTS `seguimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seguimientos` (
  `IdSeguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `IdAlumno` int(11) DEFAULT NULL,
  `FechaInicio` datetime DEFAULT NULL,
  PRIMARY KEY (`IdSeguimiento`),
  UNIQUE KEY `IdAlumno` (`IdAlumno`),
  CONSTRAINT `seguimientos_ibfk_1` FOREIGN KEY (`IdAlumno`) REFERENCES `alumnos` (`IdAlumno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seguimientos`
--

LOCK TABLES `seguimientos` WRITE;
/*!40000 ALTER TABLE `seguimientos` DISABLE KEYS */;
INSERT INTO `seguimientos` VALUES
(1,2,'2025-02-25 21:19:15'),
(2,67,'2025-02-26 01:55:33');
/*!40000 ALTER TABLE `seguimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipousuario` (
  `IdTipo` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IdTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipousuario`
--

LOCK TABLES `tipousuario` WRITE;
/*!40000 ALTER TABLE `tipousuario` DISABLE KEYS */;
INSERT INTO `tipousuario` VALUES
(1,'Profesor'),
(2,'Alumno'),
(3,'Asesor');
/*!40000 ALTER TABLE `tipousuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `IdUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipo` int(11) DEFAULT NULL,
  `Nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Matricula` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IdUsuario`),
  UNIQUE KEY `Matricula` (`Matricula`),
  KEY `IdTipo` (`IdTipo`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`IdTipo`) REFERENCES `tipousuario` (`IdTipo`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(2,2,'Pablo Xiuhnel','202024135','12345678'),
(3,1,'Monserrat','11111','12345678'),
(4,1,'Claudia','22222','12345678'),
(67,2,'Juan','10001','12345678'),
(68,2,'María','10002','10002'),
(69,2,'Pedro','10003','10003'),
(70,2,'Ana','10004','10004'),
(71,2,'Luis','10005','10005'),
(72,2,'Laura','10006','10006'),
(73,2,'Carlos','10007','10007'),
(74,2,'Sofía','10008','10008'),
(75,2,'Diego','10009','10009'),
(76,2,'Marta','10010','10010'),
(77,1,'Kani','00000','12345678'),
(78,2,'AAAA','aaa','123'),
(121,3,'A','111','123'),
(122,3,'Cris','222','123'),
(123,2,'Potro','111111111111','111111111111');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_insert_usuario
AFTER INSERT ON Usuarios
FOR EACH ROW
BEGIN
    
    IF NEW.IdTipo = 2 THEN
        
        INSERT INTO Alumnos (IdTipoUsuario, Matricula, Nombre, ApellidoPaterno, ApellidoMaterno, CorreoInstitucional, IdProfesor, PrimerLogin)
        VALUES (NEW.IdTipo, NEW.Matricula, NEW.Nombre, NULL, NULL, NULL, NULL, 'N');
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `seguimientoresidencias` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-02-26 19:44:10
