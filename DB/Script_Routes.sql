CREATE TABLE IF NOT EXISTS `routes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_rota` varchar(100) CHARACTER SET utf8mb4 NOT NULL COMMENT 'O Nome da Rota DEVE ser Unico no Sistema!!! Não pode conter espaços no nome!!\r\n',
  `slug` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_dynamic` tinyint(1) DEFAULT '0',
  `pattern` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `routes` (`nome_rota`, `slug`, `controller`, `action`, `status`, `is_dynamic`, `pattern`) VALUES
('home', '', 'SiteController', 'login', 1, 0, NULL),
('login', 'login', 'SiteController', 'login', 1, 0, NULL),
('login_autenticar', 'login/autenticar', 'LoginController', 'autenticar', 1, 0, NULL),
('logout', 'logout', 'LoginController', 'logout', 1, 0, NULL),
('dashboard', 'dashboard', 'SiteController', 'dashboard', 1, 0, NULL);
