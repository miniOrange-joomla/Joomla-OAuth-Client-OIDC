ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `cd_plugin` VARCHAR(255) NOT NULL;
ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `dno_ssos` int(11) NOT NULL;
ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `tno_ssos` int(11) NOT NULL;
ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `previous_update` VARCHAR(255) NOT NULL;