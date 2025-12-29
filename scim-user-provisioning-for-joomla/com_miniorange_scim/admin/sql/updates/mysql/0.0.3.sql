CREATE TABLE IF NOT EXISTS `#__miniorange_scim_customer` (
`id` int(11) UNSIGNED NOT NULL ,
`email` VARCHAR(255)  NOT NULL ,
`password` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(255)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`login_status` tinyint(1) DEFAULT FALSE,
`status` VARCHAR(255)  NOT NULL,
`registration_status` VARCHAR(255) NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`sms_count` int(11),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;
INSERT INTO `#__miniorange_scim_CUSTOMER`(`id`) values (1) ;



ALTER TABLE `#__miniorange_scim_details` ADD COLUMN `moScimParentRole` int(2) NOT NULL default 2;
ALTER TABLE `#__miniorange_scim_details` ADD COLUMN `moScimAttributeMap` varchar(1024) NOT NULL default '{"profile":{"userName":"u_username"}}';
