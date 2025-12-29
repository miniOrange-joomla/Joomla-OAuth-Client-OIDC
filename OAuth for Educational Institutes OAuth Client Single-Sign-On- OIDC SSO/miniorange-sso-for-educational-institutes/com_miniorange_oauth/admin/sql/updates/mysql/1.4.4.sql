CREATE TABLE IF NOT EXISTS `#__miniorange_oauth_logs`(
`id` INT AUTO_INCREMENT PRIMARY KEY,
`timestamp` DATETIME NOT NULL,
`log_level` VARCHAR(10) NOT NULL,
`message` TEXT NOT NULL, 
`file` VARCHAR (255),
`line_number` INT,
`function_call`  VARCHAR(255)
) DEFAULT COLLATE=utf8_general_ci;

ALTER TABLE `#__miniorange_oauth_config` 
ADD COLUMN `loggers_enable` TINYINT(1) NOT NULL DEFAULT 0;