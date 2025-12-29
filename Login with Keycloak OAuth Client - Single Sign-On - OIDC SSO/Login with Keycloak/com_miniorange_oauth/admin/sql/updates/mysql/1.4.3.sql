ALTER TABLE `#__miniorange_oauth_config`
ADD COLUMN `password` VARCHAR(255) NOT NULL AFTER `proxy_password`,
ADD COLUMN `proxy_host_name` VARCHAR(255) NOT NULL AFTER `password`,
ADD COLUMN `port_number` VARCHAR(255) NOT NULL AFTER `proxy_host_name`,
ADD COLUMN `username` VARCHAR(255) NOT NULL AFTER `port_number`,
ADD COLUMN `sso_enable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `username`,
ADD COLUMN `sso_button_enable` TINYINT(1) NOT NULL default 1 AFTER `sso_enable`;