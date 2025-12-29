ALTER TABLE `#__miniorange_scim_details` ADD COLUMN `scim_configuration` TEXT NOT NULL;
ALTER TABLE `#__miniorange_scim_details` ADD COLUMN `user_creation` TEXT NOT NULL;
ALTER TABLE `#__miniorange_scim_details` ADD COLUMN `users` int(11) UNSIGNED DEFAULT 0;