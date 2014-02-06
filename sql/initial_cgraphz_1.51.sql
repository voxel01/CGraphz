CREATE SCHEMA IF NOT EXISTS `cgraphz` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `cgraphz` ;

--
-- Table structure for table `auth_group`
--

DROP TABLE IF EXISTS `auth_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_group` (
  `id_auth_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(45) NOT NULL,
  `group_description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_auth_group`),
  UNIQUE KEY `group_UNIQUE` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_group`
--

LOCK TABLES `auth_group` WRITE;
/*!40000 ALTER TABLE `auth_group` DISABLE KEYS */;
INSERT INTO `auth_group` VALUES (1,'admin','Administrator 1'),(2,'guest','Guest');
/*!40000 ALTER TABLE `auth_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_user`
--

DROP TABLE IF EXISTS `auth_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user` (
  `id_auth_user` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `user` varchar(45) NOT NULL,
  `mail` varchar(45) DEFAULT NULL,
  `passwd` varchar(45) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_auth_user`),
  UNIQUE KEY `ix_au_user_passwd` (`user`,`passwd`),
  UNIQUE KEY `user_UNIQUE` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_user`
--

LOCK TABLES `auth_user` WRITE;
/*!40000 ALTER TABLE `auth_user` DISABLE KEYS */;
INSERT INTO `auth_user` VALUES (1,'admin','root','admin','noreply@neant.com','*196BDEDE2AE4F84CA44C47D54D78478C7E2BD7B7','mysql');
/*!40000 ALTER TABLE `auth_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_user_group`
--

DROP TABLE IF EXISTS `auth_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user_group` (
  `id_auth_group` int(10) unsigned NOT NULL,
  `id_auth_user` int(10) unsigned NOT NULL,
  `manager` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_auth_group`,`id_auth_user`),
  KEY `fk_aug_id_auth_group` (`id_auth_group`),
  KEY `fk_aug_id_auth_user` (`id_auth_user`),
  CONSTRAINT `fk_aug_id_auth_group` FOREIGN KEY (`id_auth_group`) REFERENCES `auth_group` (`id_auth_group`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_aug_id_auth_user` FOREIGN KEY (`id_auth_user`) REFERENCES `auth_user` (`id_auth_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_user_group`
--

LOCK TABLES `auth_user_group` WRITE;
/*!40000 ALTER TABLE `auth_user_group` DISABLE KEYS */;
INSERT INTO `auth_user_group` VALUES (1,1,1);
/*!40000 ALTER TABLE `auth_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_dynamic_dashboard`
--

DROP TABLE IF EXISTS `config_dynamic_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_dynamic_dashboard` (
  `id_config_dynamic_dashboard` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  PRIMARY KEY (`id_config_dynamic_dashboard`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_dynamic_dashboard_content`
--

DROP TABLE IF EXISTS `config_dynamic_dashboard_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_dynamic_dashboard_content` (
  `id_config_dynamic_dashboard_content` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_config_dynamic_dashboard` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `dash_ordering` int(11) NOT NULL,
  `regex_srv` varchar(255) NOT NULL,
  `regex_p_filter` varchar(80) NOT NULL,
  `regex_pi_filter` varchar(80) NOT NULL,
  `regex_t_filter` varchar(80) NOT NULL,
  `regex_ti_filter` varchar(80) NOT NULL,
  `rrd_ordering` varchar(255) NOT NULL,
  PRIMARY KEY (`id_config_dynamic_dashboard_content`),
  KEY `fk_cddc_id_config_dynamic_content` (`id_config_dynamic_dashboard`),
  CONSTRAINT `fk_cddc_id_config_dynamic_content` FOREIGN KEY (`id_config_dynamic_dashboard`) REFERENCES `config_dynamic_dashboard` (`id_config_dynamic_dashboard`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_dynamic_dashboard_group`
--

DROP TABLE IF EXISTS `config_dynamic_dashboard_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_dynamic_dashboard_group` (
  `id_config_dynamic_dashboard` int(10) unsigned NOT NULL,
  `id_auth_group` int(10) unsigned NOT NULL,
  `group_manager` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_config_dynamic_dashboard`,`id_auth_group`),
  KEY `fk_cddg_id_auth_group` (`id_auth_group`),
  KEY `fk_cddg_id_config_dynamic_dashboard` (`id_config_dynamic_dashboard`),
  CONSTRAINT `fk_cddg_id_auth_group` FOREIGN KEY (`id_auth_group`) REFERENCES `auth_group` (`id_auth_group`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cddg_id_config_dynamic_dashboard` FOREIGN KEY (`id_config_dynamic_dashboard`) REFERENCES `config_dynamic_dashboard` (`id_config_dynamic_dashboard`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `config_environment`
--

DROP TABLE IF EXISTS `config_environment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_environment` (
  `id_config_environment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `environment` varchar(45) DEFAULT NULL,
  `environment_description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_config_environment`),
  UNIQUE KEY `environment_UNIQUE` (`environment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_environment`
--

LOCK TABLES `config_environment` WRITE;
/*!40000 ALTER TABLE `config_environment` DISABLE KEYS */;
/*!40000 ALTER TABLE `config_environment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_environment_server`
--

DROP TABLE IF EXISTS `config_environment_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_environment_server` (
  `id_config_environment` int(10) unsigned NOT NULL,
  `id_config_server` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_config_environment`,`id_config_server`),
  KEY `fk_ces_id_config_environnement` (`id_config_environment`),
  KEY `fk_ces_id_config_server` (`id_config_server`),
  CONSTRAINT `fk_ces_id_config_environnement` FOREIGN KEY (`id_config_environment`) REFERENCES `config_environment` (`id_config_environment`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ces_id_config_server` FOREIGN KEY (`id_config_server`) REFERENCES `config_server` (`id_config_server`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_plugin_filter`
--

DROP TABLE IF EXISTS `config_plugin_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_plugin_filter` (
  `id_config_plugin_filter` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin` varchar(45) NOT NULL,
  `plugin_instance` varchar(45) DEFAULT NULL,
  `type` varchar(45) NOT NULL,
  `type_instance` varchar(45) DEFAULT NULL,
  `plugin_filter_desc` varchar(45) DEFAULT NULL,
  `plugin_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_config_plugin_filter`),
  UNIQUE KEY `ix_plugin_filter_desc` (`plugin_filter_desc`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_plugin_filter`
--

LOCK TABLES `config_plugin_filter` WRITE;
/*!40000 ALTER TABLE `config_plugin_filter` DISABLE KEYS */;
INSERT INTO `config_plugin_filter` VALUES (1,'\\w+','.*','\\w+','.*','all',99),(2,'load',NULL,'load',NULL,'load_average',1),(3,'memory','','memory','\\w+','memory',2),(4,'interface','','if_octets','eth0','eth0_traffic',3),(5,'mysql','.*','\\w+','.*','mysql',4),(6,'nginx','','nginx_\\w+','.*','nginx',4),(7,'processes','\\w+','ps_count',NULL,'processes',5),(8,'processes','','\\w+','\\w+','ps',5),(9,'tcpconns','\\d+-\\w+','tcp_connections','\\w+','tcpconns',5),(10,'df','','df','.+','df',7),(11,'cpu','\\d+','cpu','\\w+','cpu',8),(12,'df','\\w+','df_complex','.+','df_complex',6);
/*!40000 ALTER TABLE `config_plugin_filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_plugin_filter_group`
--

DROP TABLE IF EXISTS `config_plugin_filter_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_plugin_filter_group` (
  `id_config_plugin_filter` int(10) unsigned NOT NULL,
  `id_auth_group` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_config_plugin_filter`,`id_auth_group`),
  KEY `fk_cpfg_id_auth_group` (`id_auth_group`),
  CONSTRAINT `fk_cpfg_id_config_plugin_filter` FOREIGN KEY (`id_config_plugin_filter`) REFERENCES `config_plugin_filter` (`id_config_plugin_filter`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cpfg_id_auth_group` FOREIGN KEY (`id_auth_group`) REFERENCES `auth_group` (`id_auth_group`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_plugin_filter_group`
--

LOCK TABLES `config_plugin_filter_group` WRITE;
/*!40000 ALTER TABLE `config_plugin_filter_group` DISABLE KEYS */;
INSERT INTO `config_plugin_filter_group` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1);
/*!40000 ALTER TABLE `config_plugin_filter_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_project`
--

DROP TABLE IF EXISTS `config_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_project` (
  `id_config_project` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project` varchar(45) NOT NULL,
  `project_description` text,
  PRIMARY KEY (`id_config_project`),
  UNIQUE KEY `ix_cp_project` (`project`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_role`
--

DROP TABLE IF EXISTS `config_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_role` (
  `id_config_role` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(45) DEFAULT NULL,
  `role_description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_config_role`),
  UNIQUE KEY `role_UNIQUE` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_role_server`
--

DROP TABLE IF EXISTS `config_role_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_role_server` (
  `id_config_role` int(10) unsigned NOT NULL,
  `id_config_server` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_config_role`,`id_config_server`),
  KEY `fk_crs_id_config_role` (`id_config_role`),
  KEY `fk_crs_id_config_server` (`id_config_server`),
  CONSTRAINT `fk_crs_id_config_role` FOREIGN KEY (`id_config_role`) REFERENCES `config_role` (`id_config_role`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_crs_id_config_server` FOREIGN KEY (`id_config_server`) REFERENCES `config_server` (`id_config_server`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_server`
--

DROP TABLE IF EXISTS `config_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_server` (
  `id_config_server` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_name` varchar(45) NOT NULL,
  `server_description` text,
  `collectd_version` smallint(5) unsigned DEFAULT '5',
  PRIMARY KEY (`id_config_server`),
  UNIQUE KEY `ix_cs_server_name` (`server_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_server_project`
--

DROP TABLE IF EXISTS `config_server_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_server_project` (
  `id_config_server` int(10) unsigned NOT NULL,
  `id_config_project` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_config_server`,`id_config_project`),
  KEY `fk_csp_id_config_project` (`id_config_project`),
  KEY `fk_csp_id_config_server` (`id_config_server`),
  CONSTRAINT `fk_csp_id_config_project` FOREIGN KEY (`id_config_project`) REFERENCES `config_project` (`id_config_project`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_csp_id_config_server` FOREIGN KEY (`id_config_server`) REFERENCES `config_server` (`id_config_server`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perm_module`
--

DROP TABLE IF EXISTS `perm_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perm_module` (
  `id_perm_module` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(45) DEFAULT NULL,
  `component` varchar(45) DEFAULT NULL,
  `menu_name` varchar(45) DEFAULT NULL,
  `menu_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_perm_module`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perm_module`
--

LOCK TABLES `perm_module` WRITE;
/*!40000 ALTER TABLE `perm_module` DISABLE KEYS */;
INSERT INTO `perm_module` VALUES (1,'perm','module','Modules1',1),(2,'config','project','Projects',2),(3,'config','server','Servers',1),(4,'dashboard','view','Dashboards',1),(5,'auth','user','Users',1),(6,'auth','group','Groups',2),(7,'config','plugin','Filters',3),(8,'dashboard','dynamic','Dynamic Dashboards',2),(9,'small_admin','myaccount','My Account',1),(10,'small_admin','mygroup','My Groups',2),(11,'small_admin','newuser','New User',3),(12,'small_admin','mydashboard','My Dynamic Dashboards',4),(13,'config','dynamic_dashboard','Dynamic Dashboards',4),(14,'config','role','Roles',5),(15,'config','environment','Environments',6),(16,'auth','login','Login',0),(17,'graph','view','Graph',0);
/*!40000 ALTER TABLE `perm_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perm_module_group`
--

DROP TABLE IF EXISTS `perm_module_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perm_module_group` (
  `id_perm_module_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_auth_group` int(10) unsigned DEFAULT NULL,
  `id_perm_module` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_perm_module_group`),
  KEY `fk_pmg_id_auth_group` (`id_auth_group`),
  KEY `fk_pmg_id_perm_module` (`id_perm_module`),
  CONSTRAINT `fk_pmg_id_auth_group` FOREIGN KEY (`id_auth_group`) REFERENCES `auth_group` (`id_auth_group`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pmg_id_perm_module` FOREIGN KEY (`id_perm_module`) REFERENCES `perm_module` (`id_perm_module`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perm_module_group`
--

LOCK TABLES `perm_module_group` WRITE;
/*!40000 ALTER TABLE `perm_module_group` DISABLE KEYS */;
INSERT INTO `perm_module_group` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,1,10),(12,1,12),(13,1,13),(14,1,14),(15,1,15),(17,1,17),(20,1,11);
/*!40000 ALTER TABLE `perm_module_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perm_project_group`
--

DROP TABLE IF EXISTS `perm_project_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perm_project_group` (
  `id_auth_group` int(10) unsigned NOT NULL,
  `id_config_project` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_auth_group`,`id_config_project`),
  KEY `fk_ppg_id_config_project` (`id_config_project`),
  KEY `fk_ppg_id_auth_group` (`id_auth_group`),
  CONSTRAINT `fk_ppg_id_config_project` FOREIGN KEY (`id_config_project`) REFERENCES `config_project` (`id_config_project`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ppg_id_auth_group` FOREIGN KEY (`id_auth_group`) REFERENCES `auth_group` (`id_auth_group`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
