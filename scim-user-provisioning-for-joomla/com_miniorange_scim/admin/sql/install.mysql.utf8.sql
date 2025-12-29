CREATE TABLE IF NOT EXISTS `#__miniorange_scim_details` (
`id` int(11) UNSIGNED NOT NULL,
`bearer_token` VARCHAR(255)  NOT NULL ,
`default_roles` VARCHAR(255)  NOT NULL default '[2]',
`moScimParentRole` int(2) NOT NULL default 2,
`moScimAttributeMap` VARCHAR(1024) NOT NULL default '{"profile":{"emails[type eq \\"work\\"].value":"u_email","userName":"u_username"}}',
`scim_configuration` TEXT  NOT NULL ,
`user_creation` TEXT  NOT NULL ,
`users` int(11) UNSIGNED DEFAULT 0,
`uninstall_feedback` int(2),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT IGNORE INTO `#__miniorange_scim_details`(`id`) values (1) ;

CREATE TABLE IF NOT EXISTS `#__miniorange_scim_customer` (
`id` int(11) UNSIGNED NOT NULL ,
`email` VARCHAR(255)  NOT NULL,
`password` VARCHAR(255)  NOT NULL default '',
`admin_phone` VARCHAR(255)  NOT NULL default '',
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

INSERT IGNORE INTO `#__miniorange_scim_customer`(`id`) values (1) ;
