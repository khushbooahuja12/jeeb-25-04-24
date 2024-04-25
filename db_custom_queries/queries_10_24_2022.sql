-- Add companies table
ALTER TABLE `companies` ADD `deleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `notes`;
ALTER TABLE `companies` ADD `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `deleted`;
ALTER TABLE `companies` ADD `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`;

INSERT INTO `companies` (`id`, `name`, `notes`, `deleted`, `created_at`, `updated_at`) VALUES (NULL, 'Village', '', '0', current_timestamp(), current_timestamp());
INSERT INTO `companies` (`id`, `name`, `notes`, `deleted`, `created_at`, `updated_at`) VALUES (NULL, 'New Indian Supermarket & Retail Mart', '', '0', current_timestamp(), current_timestamp());
INSERT INTO `companies` (`id`, `name`, `notes`, `deleted`, `created_at`, `updated_at`) VALUES (NULL, 'Saudia', '', '0', current_timestamp(), current_timestamp());

-- Products table change company id 
ALTER TABLE `dev_products` CHANGE `company_id` `fk_company_id` INT(11) NOT NULL;
