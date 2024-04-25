-- Add companies table
CREATE TABLE `companies` (`id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL , `notes` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

-- Alter store table
ALTER TABLE `stores` ADD `company_id` INT NOT NULL AFTER `mobile`;
ALTER TABLE `stores` ADD `api_url` TEXT NULL DEFAULT NULL AFTER `company_id` ;
ALTER TABLE `stores` ADD `last_api_updated_at` VARCHAR(191) NULL DEFAULT NULL AFTER `api_url`;

-- Alter products table
ALTER TABLE `dev_products` ADD `store5_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store4_distributor_price`, ADD `store6_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store5_distributor_price`, ADD `store7_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store6_distributor_price`, ADD `store8_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store7_distributor_price`, ADD `store9_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store8_distributor_price`, ADD `store10_distributor_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store9_distributor_price`;
ALTER TABLE `dev_products` ADD `store5_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store4_price`, ADD `store6_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store5_price`, ADD `store7_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store6_price`, ADD `store8_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store7_price`, ADD `store9_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store8_price`, ADD `store10_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `store9_price`;
ALTER TABLE `dev_products` CHANGE `store1` `store1` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `dev_products` CHANGE `store2` `store2` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `dev_products` CHANGE `store3` `store3` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `dev_products` CHANGE `store4` `store4` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `dev_products` ADD `store5` INT(11) NOT NULL DEFAULT '0' AFTER `store4`, ADD `store6` INT(11) NOT NULL DEFAULT '0' AFTER `store5`, ADD `store7` INT(11) NOT NULL DEFAULT '0' AFTER `store6`, ADD `store8` INT(11) NOT NULL DEFAULT '0' AFTER `store7`, ADD `store9` INT(11) NOT NULL DEFAULT '0' AFTER `store8`, ADD `store10` INT(11) NOT NULL DEFAULT '0' AFTER `store9`;
ALTER TABLE `dev_products` ADD `company_id` INT NOT NULL AFTER `store10`;
