ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `sso_var` varchar(255) NOT NULL;
ALTER TABLE `#__miniorange_oauth_customer` ADD COLUMN  `sso_test` varchar(255) NOT NULL;


UPDATE `#__miniorange_oauth_customer` SET `sso_var` ='MjU=';