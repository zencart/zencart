INSERT INTO `admin_pages` (`page_key`, `language_key`, `main_page`, `page_params`, `menu_key`, `display_on_menu`, `sort_order`) VALUES
('featured_categories', 'BOX_CATALOG_FEATURED_CATEGORIES', 'FILENAME_FEATURED_CATEGORIES', '', 'catalog', 'Y', 19);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function, val_function) VALUES
('Show Featured Categories on Main Page', 'SHOW_PRODUCT_INFO_MAIN_FEATURED_CATEGORIES', '0', 'Show Featured Categories on Main Page<br />0= off or set the sort order', 24, 73, '2024-08-01 20:39:58', '2024-08-01 20:39:31', NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ', NULL),
('Random Featured Categories for SideBox', 'MAX_RANDOM_SELECT_FEATURED_CATEGORIES', '1', 'Number of random FEATURED categories to rotate in the sidebox<br />Enter the number of categories to display in this sidebox at one time.<br /><br />How many categories do you want to display in this sidebox?', 3, 32, NULL, '2024-08-03 03:29:18', NULL, NULL, '{\"error\":\"TEXT_MAX_ADMIN_RANDOM_SELECT_FEATURED_CATEGORIES_LENGTH\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}'),
('Categories Box - Show Featured Category Link', 'SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES', 'false', 'Show Featured Categories Link in the Categories Box', 19, 11, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),',NULL);

UPDATE `configuration` SET `set_function` = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE `configuration_key` = 'SHOW_PRODUCT_INFO_MAIN_NEW_PRODUCTS';
UPDATE `configuration` SET `set_function` = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE `configuration_key` = 'SHOW_PRODUCT_INFO_MAIN_FEATURED_PRODUCTS'; 
UPDATE `configuration` SET `set_function` = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE `configuration_key` = 'SHOW_PRODUCT_INFO_MAIN_SPECIALS_PRODUCTS'; 
UPDATE `configuration` SET `set_function` = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE `configuration_key` = 'SHOW_PRODUCT_INFO_MAIN_UPCOMING'; 

--For some reason it doesn't want this at the same time as the configuration cahnges but both run fine alone.
-- I am too tired to care right now

CREATE TABLE `featured_categories` (
  `featured_categories_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL DEFAULT 0,
  `featured_date_added` datetime DEFAULT NULL,
  `featured_last_modified` datetime DEFAULT NULL,
  `expires_date` date NOT NULL DEFAULT '0001-01-01',
  `date_status_change` datetime DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `featured_date_available` date NOT NULL DEFAULT '0001-01-01'
);
ALTER TABLE `featured_categories`
  ADD PRIMARY KEY (`featured_categories_id`),
  ADD KEY `idx_status_zen` (`status`),
  ADD KEY `idx_categories_id_zen` (`categories_id`),
  ADD KEY `idx_date_avail_zen` (`featured_date_available`),
  ADD KEY `idx_expires_date_zen` (`expires_date`),
  MODIFY `featured_categories_id` int(11) NOT NULL AUTO_INCREMENT;


-- If You're using the test products you can also add this so something shows up in fatured categories

INSERT INTO `featured_categories` (`categories_id`, `featured_date_added`, `featured_last_modified`, `expires_date`, `date_status_change`, `status`, `featured_date_available`) VALUES
(34, '2004-02-21 16:34:31', '2004-02-21 16:34:31', '0001-01-01', '2004-02-21 16:34:31', 1, '0001-01-01'),
(8, '2004-02-21 17:04:54', '2004-02-21 22:31:52', '2004-02-27', '2004-04-25 22:50:50', 0, '2004-02-21'),
(12, '2004-02-21 17:10:49', '2004-02-21 17:10:49', '0001-01-01', '2004-02-21 17:10:49', 1, '0001-01-01'),
(26, '2004-02-21 22:31:24', NULL, '0001-01-01', NULL, 1, '0001-01-01'),
(40, '2004-05-13 22:50:33', NULL, '0001-01-01', NULL, 1, '0001-01-01'),
(47, '2024-08-03 21:51:51', NULL, '2025-08-15', NULL, 1, '2024-08-03'),
(25, '2024-08-03 01:47:03', NULL, '2025-08-02', NULL, 1, '2024-08-02'),
(62, '2024-08-03 01:44:32', NULL, '0001-01-01', NULL, 1, '0001-01-01'),
(22, '2024-08-04 17:31:37', NULL, '0001-01-01', NULL, 1, '0001-01-01');

