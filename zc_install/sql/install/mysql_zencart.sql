#
# * Main Zen Cart SQL Load for MySQL databases
# * @access private
# * @copyright Copyright 2003-2020 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
#

############ IMPORTANT INSTRUCTIONS ###############
#
# * Zen Cart uses the zc_install/index.php program to do installations
# * This SQL script is intended to be used by running zc_install
# * It is *not* recommended to simply run these statements manually via any other means
# * ie: not via phpMyAdmin or other SQL front-end tools
# * The zc_install program catches possible problems/exceptions
# * and also handles table-prefixes automatically, based on selections made during installation
#
#####################################################


# --------------------------------------------------------
#
# Table structure for table upgrade_exceptions
# (Placed at top so any exceptions during installation can be trapped as well)
#

DROP TABLE IF EXISTS upgrade_exceptions;
CREATE TABLE upgrade_exceptions (
  upgrade_exception_id smallint(5) NOT NULL auto_increment,
  sql_file varchar(128) default NULL,
  reason text default NULL,
  errordate datetime default NULL,
  sqlstatement text,
  PRIMARY KEY  (upgrade_exception_id)
) ENGINE=MyISAM;


# --------------------------------------------------------
#
# Table structure for table address_book
#

DROP TABLE IF EXISTS address_book;
CREATE TABLE address_book (
  address_book_id int(11) NOT NULL auto_increment,
  customers_id int(11) NOT NULL default '0',
  entry_gender char(1) NOT NULL default '',
  entry_company varchar(64) default NULL,
  entry_firstname varchar(32) NOT NULL default '',
  entry_lastname varchar(32) NOT NULL default '',
  entry_street_address varchar(64) NOT NULL default '',
  entry_suburb varchar(32) default NULL,
  entry_postcode varchar(10) NOT NULL default '',
  entry_city varchar(32) NOT NULL default '',
  entry_state varchar(32) default NULL,
  entry_country_id int(11) NOT NULL default '0',
  entry_zone_id int(11) NOT NULL default '0',
  PRIMARY KEY  (address_book_id),
  KEY idx_address_book_customers_id_zen (customers_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'address_format'
#

DROP TABLE IF EXISTS address_format;
CREATE TABLE address_format (
  address_format_id int(11) NOT NULL auto_increment,
  address_format varchar(128) NOT NULL default '',
  address_summary varchar(48) NOT NULL default '',
  PRIMARY KEY  (address_format_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin'
#

DROP TABLE IF EXISTS admin;
CREATE TABLE admin (
  admin_id int(11) NOT NULL auto_increment,
  admin_name varchar(32) NOT NULL default '',
  admin_email varchar(96) NOT NULL default '',
  admin_profile int(11) NOT NULL default '0',
  admin_pass varchar(255) NOT NULL default '',
  prev_pass1 varchar(255) NOT NULL default '',
  prev_pass2 varchar(255) NOT NULL default '',
  prev_pass3 varchar(255) NOT NULL default '',
  pwd_last_change_date datetime NOT NULL default '0001-01-01 00:00:00',
  reset_token varchar(255) NOT NULL default '',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  last_login_date datetime NOT NULL default '0001-01-01 00:00:00',
  last_login_ip varchar(45) NOT NULL default '',
  failed_logins smallint(4) unsigned NOT NULL default '0',
  lockout_expires int(11) NOT NULL default '0',
  last_failed_attempt datetime NOT NULL default '0001-01-01 00:00:00',
  last_failed_ip varchar(45) NOT NULL default '',
  PRIMARY KEY  (admin_id),
  KEY idx_admin_name_zen (admin_name),
  KEY idx_admin_email_zen (admin_email),
  KEY idx_admin_profile_zen (admin_profile)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'admin_notifications'
#

DROP TABLE IF EXISTS admin_notifications;
CREATE TABLE admin_notifications (
  notification_key varchar(40) NOT NULL,
  admin_id int(11),
  dismissed char(1),
  UNIQUE KEY notification_key (notification_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin_activity_log'
#

DROP TABLE IF EXISTS admin_activity_log;
CREATE TABLE admin_activity_log (
  log_id bigint(15) NOT NULL auto_increment,
  access_date datetime NOT NULL default '0001-01-01 00:00:00',
  admin_id int(11) NOT NULL default '0',
  page_accessed varchar(80) NOT NULL default '',
  page_parameters text,
  ip_address varchar(45) NOT NULL default '',
  flagged tinyint NOT NULL default '0',
  attention MEDIUMTEXT,
  gzpost mediumblob,
  logmessage mediumtext NOT NULL,
  severity varchar(9) NOT NULL default 'info',
  PRIMARY KEY  (log_id),
  KEY idx_page_accessed_zen (page_accessed),
  KEY idx_access_date_zen (access_date),
  KEY idx_flagged_zen (flagged),
  KEY idx_ip_zen (ip_address)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin_menu'
#

DROP TABLE IF EXISTS admin_menus;
CREATE TABLE admin_menus (
  menu_key VARCHAR(191) NOT NULL DEFAULT '',
  language_key VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT(11) NOT NULL DEFAULT 0,
  UNIQUE KEY menu_key (menu_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin_pages'
#

DROP TABLE IF EXISTS admin_pages;
CREATE TABLE admin_pages (
  page_key VARCHAR(191) NOT NULL DEFAULT '',
  language_key VARCHAR(255) NOT NULL DEFAULT '',
  main_page varchar(255) NOT NULL default '',
  page_params varchar(255) NOT NULL default '',
  menu_key varchar(191) NOT NULL default '',
  display_on_menu char(1) NOT NULL default 'N',
  sort_order int(11) NOT NULL default 0,
  UNIQUE KEY page_key (page_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin_profiles'
#

DROP TABLE IF EXISTS admin_profiles;
CREATE TABLE admin_profiles (
  profile_id int(11) NOT NULL AUTO_INCREMENT,
  profile_name varchar(191) NOT NULL default '',
  PRIMARY KEY (profile_id),
  UNIQUE KEY idx_profile_name_zen (profile_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'admin_pages_to_profiles'
#

DROP TABLE IF EXISTS admin_pages_to_profiles;
CREATE TABLE admin_pages_to_profiles (
  profile_id int(11) NOT NULL default '0',
  page_key varchar(191) NOT NULL default '',
  UNIQUE KEY profile_page (profile_id, page_key),
  UNIQUE KEY page_profile (page_key, profile_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'authorizenet'
#
DROP TABLE IF EXISTS authorizenet;
CREATE TABLE authorizenet (
  id int(11) unsigned NOT NULL auto_increment,
  customer_id int(11) NOT NULL default '0',
  order_id int(11) NOT NULL default '0',
  response_code int(1) NOT NULL default '0',
  response_text varchar(255) NOT NULL default '',
  authorization_type varchar(50) NOT NULL default '',
  transaction_id varchar(32) default NULL,
  sent longtext NOT NULL,
  received longtext NOT NULL,
  time varchar(50) NOT NULL default '',
  session_id varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'banners'
#

DROP TABLE IF EXISTS banners;
CREATE TABLE banners (
  banners_id int(11) NOT NULL auto_increment,
  banners_title varchar(64) NOT NULL default '',
  banners_url varchar(255) NOT NULL default '',
  banners_image varchar(255) NOT NULL default '',
  banners_group varchar(15) NOT NULL default '',
  banners_html_text text,
  expires_impressions int(7) default '0',
  expires_date datetime default NULL,
  date_scheduled datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  date_status_change datetime default NULL,
  status int(1) NOT NULL default '1',
  banners_open_new_windows int(1) NOT NULL default '1',
  banners_on_ssl int(1) NOT NULL default '1',
  banners_sort_order int(11) NOT NULL default '0',
  PRIMARY KEY  (banners_id),
  KEY idx_status_group_zen (status,banners_group),
  KEY idx_expires_date_zen (expires_date),
  KEY idx_date_scheduled_zen (date_scheduled)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'banners_history'
#

DROP TABLE IF EXISTS banners_history;
CREATE TABLE banners_history (
  banners_history_id int(11) NOT NULL auto_increment,
  banners_id int(11) NOT NULL default '0',
  banners_shown int(5) NOT NULL default '0',
  banners_clicked int(5) NOT NULL default '0',
  banners_history_date datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (banners_history_id),
  KEY idx_banners_id_zen (banners_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'categories'
#

DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  categories_id int(11) NOT NULL auto_increment,
  categories_image varchar(255) default NULL,
  parent_id int(11) NOT NULL default '0',
  sort_order int(3) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  categories_status tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (categories_id),
  KEY idx_parent_id_cat_id_zen (parent_id,categories_id),
  KEY idx_status_zen (categories_status),
  KEY idx_sort_order_zen (sort_order)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'categories_description'
#

DROP TABLE IF EXISTS categories_description;
CREATE TABLE categories_description (
  categories_id int(11) NOT NULL default '0',
  language_id int(11) NOT NULL default '1',
  categories_name varchar(32) NOT NULL default '',
  categories_description text NOT NULL,
  PRIMARY KEY  (categories_id,language_id),
  KEY idx_categories_name_zen (categories_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'configuration'
#

DROP TABLE IF EXISTS configuration;
CREATE TABLE configuration (
  configuration_id int(11) NOT NULL auto_increment,
  configuration_title text NOT NULL,
  configuration_key varchar(180) NOT NULL default '',
  configuration_value text NOT NULL,
  configuration_description text NOT NULL,
  configuration_group_id int(11) NOT NULL default '0',
  sort_order int(5) default NULL,
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  use_function text default NULL,
  set_function text default NULL,
  val_function text default NULL,
  PRIMARY KEY  (configuration_id),
  UNIQUE KEY unq_config_key_zen (configuration_key),
  KEY idx_key_value_zen (configuration_key,configuration_value(10)),
  KEY idx_cfg_grp_id_zen (configuration_group_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'configuration_group'
#

DROP TABLE IF EXISTS configuration_group;
CREATE TABLE configuration_group (
  configuration_group_id int(11) NOT NULL auto_increment,
  configuration_group_title varchar(64) NOT NULL default '',
  configuration_group_description varchar(255) NOT NULL default '',
  sort_order int(5) default NULL,
  visible int(1) default '1',
  PRIMARY KEY  (configuration_group_id),
  KEY idx_visible_zen (visible)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'counter'
#

DROP TABLE IF EXISTS counter;
CREATE TABLE counter (
  startdate char(8) default NULL,
  counter int(12) default NULL
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'counter_history'
#

DROP TABLE IF EXISTS counter_history;
CREATE TABLE counter_history (
  startdate char(8) NOT NULL default '',
  counter int(12) default NULL,
  session_counter int(12) default NULL,
  PRIMARY KEY  (startdate)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'count_product_views'
#

DROP TABLE IF EXISTS count_product_views;
CREATE TABLE count_product_views (
  product_id int(11) NOT NULL default 0,
  language_id int(11) NOT NULL default 1,
  date_viewed date NOT NULL,
  views int(11) default NULL,
  PRIMARY KEY (product_id, language_id, date_viewed),
  KEY idx_pid_lang_date_zen (language_id, product_id, date_viewed),
  KEY idx_date_pid_lang_zen (date_viewed, product_id, language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'countries'
#

DROP TABLE IF EXISTS countries;
CREATE TABLE countries (
  countries_id int(11) NOT NULL auto_increment,
  countries_name varchar(64) NOT NULL default '',
  countries_iso_code_2 char(2) NOT NULL default '',
  countries_iso_code_3 char(3) NOT NULL default '',
  address_format_id int(11) NOT NULL default '0',
  status tinyint(1) default 1,
  PRIMARY KEY  (countries_id),
  KEY idx_countries_name_zen (countries_name),
  KEY idx_address_format_id_zen (address_format_id),
  KEY idx_iso_2_zen (countries_iso_code_2),
  KEY idx_iso_3_zen (countries_iso_code_3)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupon_email_track'
#

DROP TABLE IF EXISTS coupon_email_track;
CREATE TABLE coupon_email_track (
  unique_id int(11) NOT NULL auto_increment,
  coupon_id int(11) NOT NULL default '0',
  customer_id_sent int(11) NOT NULL default '0',
  sent_firstname varchar(32) default NULL,
  sent_lastname varchar(32) default NULL,
  emailed_to varchar(32) default NULL,
  date_sent datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (unique_id),
  KEY idx_coupon_id_zen (coupon_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupon_gv_customer'
#

DROP TABLE IF EXISTS coupon_gv_customer;
CREATE TABLE coupon_gv_customer (
  customer_id int(5) NOT NULL default '0',
  amount decimal(15,4) NOT NULL default '0.0000',
  PRIMARY KEY  (customer_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupon_gv_queue'
#

DROP TABLE IF EXISTS coupon_gv_queue;
CREATE TABLE coupon_gv_queue (
  unique_id int(5) NOT NULL auto_increment,
  customer_id int(5) NOT NULL default '0',
  order_id int(5) NOT NULL default '0',
  amount decimal(15,4) NOT NULL default '0.0000',
  date_created datetime NOT NULL default '0001-01-01 00:00:00',
  ipaddr varchar(45) NOT NULL default '',
  release_flag char(1) NOT NULL default 'N',
  PRIMARY KEY  (unique_id),
  KEY idx_cust_id_order_id_zen (customer_id,order_id),
  KEY idx_release_flag_zen (release_flag)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupon_redeem_track'
#

DROP TABLE IF EXISTS coupon_redeem_track;
CREATE TABLE coupon_redeem_track (
  unique_id int(11) NOT NULL auto_increment,
  coupon_id int(11) NOT NULL default '0',
  customer_id int(11) NOT NULL default '0',
  redeem_date datetime NOT NULL default '0001-01-01 00:00:00',
  redeem_ip varchar(45) NOT NULL default '',
  order_id int(11) NOT NULL default '0',
  PRIMARY KEY  (unique_id),
  KEY idx_coupon_id_zen (coupon_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupon_restrict'
#

DROP TABLE IF EXISTS coupon_restrict;
CREATE TABLE coupon_restrict (
  restrict_id int(11) NOT NULL auto_increment,
  coupon_id int(11) NOT NULL default '0',
  product_id int(11) NOT NULL default '0',
  category_id int(11) NOT NULL default '0',
  coupon_restrict char(1) NOT NULL default 'N',
  PRIMARY KEY  (restrict_id),
  KEY idx_coup_id_prod_id_zen (coupon_id,product_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupons'
#

DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
  coupon_id int(11) NOT NULL auto_increment,
  coupon_type char(1) NOT NULL default 'F',
  coupon_code varchar(32) NOT NULL default '',
  coupon_amount decimal(15,4) NOT NULL default '0.0000',
  coupon_minimum_order decimal(15,4) NOT NULL default '0.0000',
  coupon_start_date datetime NOT NULL default '0001-01-01 00:00:00',
  coupon_expire_date datetime NOT NULL default '0001-01-01 00:00:00',
  uses_per_coupon int(5) NOT NULL default 1,
  uses_per_user int(5) NOT NULL default 0,
  restrict_to_products varchar(255) default NULL,
  restrict_to_categories varchar(255) default NULL,
  restrict_to_customers text,
  coupon_active char(1) NOT NULL default 'Y',
  date_created datetime NOT NULL default '0001-01-01 00:00:00',
  date_modified datetime NOT NULL default '0001-01-01 00:00:00',
  coupon_zone_restriction int(11) NOT NULL default 0,
  coupon_calc_base tinyint(1) NOT NULL DEFAULT 0,
  coupon_order_limit int(4) NOT NULL default 0,
  coupon_is_valid_for_sales tinyint(1) NOT NULL DEFAULT 1,
  coupon_product_count tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (coupon_id),
  KEY idx_active_type_zen (coupon_active,coupon_type),
  KEY idx_coupon_code_zen (coupon_code),
  KEY idx_coupon_type_zen (coupon_type)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'coupons_description'
#

DROP TABLE IF EXISTS coupons_description;
CREATE TABLE coupons_description (
  coupon_id int(11) NOT NULL default '0',
  language_id int(11) NOT NULL default '0',
  coupon_name varchar(64) NOT NULL default '',
  coupon_description text,
  PRIMARY KEY (coupon_id,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'currencies'
#

DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
  currencies_id int(11) NOT NULL auto_increment,
  title varchar(32) NOT NULL default '',
  code char(3) NOT NULL default '',
  symbol_left varchar(32) default NULL,
  symbol_right varchar(32) default NULL,
  decimal_point char(1) default NULL,
  thousands_point char(1) default NULL,
  decimal_places char(1) default NULL,
  value decimal(14,6) default NULL,
  last_updated datetime default NULL,
  PRIMARY KEY  (currencies_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'customers'
#

DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
  customers_id int(11) NOT NULL auto_increment,
  customers_gender char(1) NOT NULL default '',
  customers_firstname varchar(32) NOT NULL default '',
  customers_lastname varchar(32) NOT NULL default '',
  customers_dob datetime NOT NULL default '0001-01-01 00:00:00',
  customers_email_address varchar(96) NOT NULL default '',
  customers_nick varchar(96) NOT NULL default '',
  customers_default_address_id int(11) NOT NULL default '0',
  customers_telephone varchar(32) NOT NULL default '',
  customers_fax varchar(32) default NULL,
  customers_password varchar(255) NOT NULL default '',
  customers_secret varchar(64) NOT NULL default '',
  customers_newsletter char(1) default NULL,
  customers_group_pricing int(11) NOT NULL default '0',
  customers_email_format varchar(4) NOT NULL default 'TEXT',
  customers_authorization int(1) NOT NULL default '0',
  customers_referral varchar(32) NOT NULL default '',
  customers_paypal_payerid VARCHAR(20) NOT NULL default '',
  customers_paypal_ec TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
  PRIMARY KEY  (customers_id),
  KEY idx_email_address_zen (customers_email_address),
  KEY idx_referral_zen (customers_referral(10)),
  KEY idx_grp_pricing_zen (customers_group_pricing),
  KEY idx_nick_zen (customers_nick),
  KEY idx_newsletter_zen (customers_newsletter)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'customers_basket'
#

DROP TABLE IF EXISTS customers_basket;
CREATE TABLE customers_basket (
  customers_basket_id int(11) NOT NULL auto_increment,
  customers_id int(11) NOT NULL default '0',
  products_id tinytext NOT NULL,
  customers_basket_quantity float NOT NULL default '0',
  customers_basket_date_added varchar(8) default NULL,
  PRIMARY KEY  (customers_basket_id),
  KEY idx_customers_id_zen (customers_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'customers_basket_attributes'
#

DROP TABLE IF EXISTS customers_basket_attributes;
CREATE TABLE customers_basket_attributes (
  customers_basket_attributes_id int(11) NOT NULL auto_increment,
  customers_id int(11) NOT NULL default '0',
  products_id tinytext NOT NULL,
  products_options_id varchar(64) NOT NULL default '0',
  products_options_value_id int(11) NOT NULL default '0',
  products_options_value_text BLOB NULL,
  products_options_sort_order text NOT NULL,
  PRIMARY KEY  (customers_basket_attributes_id),
  KEY idx_cust_id_prod_id_zen (customers_id,products_id(36))
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'customers_info'
#

DROP TABLE IF EXISTS customers_info;
CREATE TABLE customers_info (
  customers_info_id int(11) NOT NULL default '0',
  customers_info_date_of_last_logon datetime default NULL,
  customers_info_number_of_logons int(5) default NULL,
  customers_info_date_account_created datetime default NULL,
  customers_info_date_account_last_modified datetime default NULL,
  global_product_notifications int(1) default '0',
  PRIMARY KEY  (customers_info_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table db_cache
#
DROP TABLE IF EXISTS db_cache;
CREATE TABLE db_cache (
  cache_entry_name varchar(64) NOT NULL default '',
  cache_data mediumblob,
  cache_entry_created int(15) default NULL,
  PRIMARY KEY  (cache_entry_name)
) ENGINE=MyISAM;



# --------------------------------------------------------

#
# Table structure for table 'email_archive'
#

DROP TABLE IF EXISTS email_archive;
CREATE TABLE email_archive (
  archive_id int(11) NOT NULL auto_increment,
  email_to_name varchar(96) NOT NULL default '',
  email_to_address varchar(96) NOT NULL default '',
  email_from_name varchar(96) NOT NULL default '',
  email_from_address varchar(96) NOT NULL default '',
  email_subject varchar(255) NOT NULL default '',
  email_html text NOT NULL,
  email_text text NOT NULL,
  date_sent datetime NOT NULL default '0001-01-01 00:00:00',
  module varchar(64) NOT NULL default '',
  PRIMARY KEY  (archive_id),
  KEY idx_email_to_address_zen (email_to_address),
  KEY idx_module_zen (module)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'ezpages'
#

DROP TABLE IF EXISTS ezpages;
CREATE TABLE ezpages (
  pages_id int(11) NOT NULL auto_increment,
  alt_url varchar(255) NOT NULL default '',
  alt_url_external varchar(255) NOT NULL default '',
  status_header int(1) NOT NULL default '1',
  status_sidebox int(1) NOT NULL default '1',
  status_footer int(1) NOT NULL default '1',
  status_visible int(1) NOT NULL default '0',
  status_toc int(1) NOT NULL default '1',
  header_sort_order int(3) NOT NULL default '0',
  sidebox_sort_order int(3) NOT NULL default '0',
  footer_sort_order int(3) NOT NULL default '0',
  toc_sort_order int(3) NOT NULL default '0',
  page_open_new_window int(1) NOT NULL default '0',
  page_is_ssl int(1) NOT NULL default '0',
  toc_chapter int(11) NOT NULL default '0',
  PRIMARY KEY  (pages_id),
  KEY idx_ezp_status_header_zen (status_header),
  KEY idx_ezp_status_sidebox_zen (status_sidebox),
  KEY idx_ezp_status_footer_zen (status_footer),
  KEY idx_ezp_status_toc_zen (status_toc)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'ezpages_content'
#

DROP TABLE IF EXISTS ezpages_content;
CREATE TABLE ezpages_content (
  pages_id int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL DEFAULT '1',
  pages_title varchar(64) NOT NULL DEFAULT '',
  pages_html_text mediumtext,
  UNIQUE KEY idx_ezpages_content (pages_id,languages_id),
  KEY idx_lang_id_zen (languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------
#
# Table structure for table 'featured'
#

DROP TABLE IF EXISTS featured;
CREATE TABLE featured (
  featured_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  featured_date_added datetime default NULL,
  featured_last_modified datetime default NULL,
  expires_date date NOT NULL default '0001-01-01',
  date_status_change datetime default NULL,
  status int(1) NOT NULL default '1',
  featured_date_available date NOT NULL default '0001-01-01',
  PRIMARY KEY  (featured_id),
  KEY idx_status_zen (status),
  KEY idx_products_id_zen (products_id),
  KEY idx_date_avail_zen (featured_date_available),
  KEY idx_expires_date_zen (expires_date)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'files_uploaded'
#

DROP TABLE IF EXISTS files_uploaded;
CREATE TABLE files_uploaded (
  files_uploaded_id int(11) NOT NULL auto_increment,
  sesskey varchar(32) default NULL,
  customers_id int(11) default NULL,
  files_uploaded_name varchar(64) NOT NULL default '',
  PRIMARY KEY  (files_uploaded_id),
  KEY idx_customers_id_zen (customers_id)
) ENGINE=MyISAM COMMENT='Must always have either a sesskey or customers_id';

# --------------------------------------------------------

#
# Table structure for table 'geo_zones'
#

DROP TABLE IF EXISTS geo_zones;
CREATE TABLE geo_zones (
  geo_zone_id int(11) NOT NULL auto_increment,
  geo_zone_name varchar(32) NOT NULL default '',
  geo_zone_description varchar(255) NOT NULL default '',
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (geo_zone_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'get_terms_to_filter'
#
DROP TABLE IF EXISTS get_terms_to_filter;
CREATE TABLE get_terms_to_filter (
  get_term_name varchar(191) NOT NULL default '',
  get_term_table varchar(64) NOT NULL,
  get_term_name_field varchar(64) NOT NULL,
  PRIMARY KEY  (get_term_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'group_pricing'
#

DROP TABLE IF EXISTS group_pricing;
CREATE TABLE group_pricing (
  group_id int(11) NOT NULL auto_increment,
  group_name varchar(32) NOT NULL default '',
  group_percentage decimal(5,2) NOT NULL default '0.00',
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (group_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'languages'
#

DROP TABLE IF EXISTS languages;
CREATE TABLE languages (
  languages_id int(11) NOT NULL auto_increment,
  name varchar(32) NOT NULL default '',
  code char(2) NOT NULL default '',
  image varchar(64) default NULL,
  directory varchar(32) default NULL,
  sort_order int(3) default NULL,
  PRIMARY KEY  (languages_id),
  KEY idx_languages_name_zen (name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'layout_boxes'
#

DROP TABLE IF EXISTS layout_boxes;
CREATE TABLE layout_boxes (
  layout_id int(11) NOT NULL auto_increment,
  layout_template varchar(64) NOT NULL default '',
  layout_box_name varchar(64) NOT NULL default '',
  layout_box_status tinyint(1) NOT NULL default '0',
  layout_box_location tinyint(1) NOT NULL default '0',
  layout_box_sort_order int(11) NOT NULL default '0',
  layout_box_sort_order_single int(11) NOT NULL default '0',
  layout_box_status_single tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (layout_id),
  KEY idx_name_template_zen (layout_template,layout_box_name),
  KEY idx_layout_box_status_zen (layout_box_status),
  KEY idx_layout_box_sort_order_zen (layout_box_sort_order)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'manufacturers'
#

DROP TABLE IF EXISTS manufacturers;
CREATE TABLE manufacturers (
  manufacturers_id int(11) NOT NULL auto_increment,
  manufacturers_name varchar(32) NOT NULL default '',
  manufacturers_image varchar(255) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (manufacturers_id),
  KEY idx_mfg_name_zen (manufacturers_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'manufacturers_info'
#

DROP TABLE IF EXISTS manufacturers_info;
CREATE TABLE manufacturers_info (
  manufacturers_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  manufacturers_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime default NULL,
  PRIMARY KEY  (manufacturers_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_clips'
#

DROP TABLE IF EXISTS media_clips;
CREATE TABLE media_clips (
  clip_id int(11) NOT NULL auto_increment,
  media_id int(11) NOT NULL default '0',
  clip_type smallint(6) NOT NULL default '0',
  clip_filename text NOT NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (clip_id),
  KEY idx_media_id_zen (media_id),
  KEY idx_clip_type_zen (clip_type)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_manager'
#

DROP TABLE IF EXISTS media_manager;
CREATE TABLE media_manager (
  media_id int(11) NOT NULL auto_increment,
  media_name varchar(255) NOT NULL default '',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (media_id),
  KEY idx_media_name_zen (media_name(191))
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_to_products'
#

DROP TABLE IF EXISTS media_to_products;
CREATE TABLE media_to_products (
  media_id int(11) NOT NULL default '0',
  product_id int(11) NOT NULL default '0',
  KEY idx_media_product_zen (media_id,product_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_types'
#

DROP TABLE IF EXISTS media_types;
CREATE TABLE media_types (
  type_id int(11) NOT NULL auto_increment,
  type_name varchar(64) NOT NULL default '',
  type_ext varchar(8) NOT NULL default '',
  PRIMARY KEY  (type_id),
  KEY idx_type_name_zen (type_name)
) ENGINE=MyISAM;

INSERT INTO media_types (type_name, type_ext) VALUES ('MP3','.mp3');

# -------------------------------------------------------

#
# Table structure for table 'meta_tags_categories_description'
#

DROP TABLE IF EXISTS meta_tags_categories_description;
CREATE TABLE meta_tags_categories_description (
  categories_id int(11) NOT NULL,
  language_id int(11) NOT NULL default '1',
  metatags_title varchar(255) NOT NULL default '',
  metatags_keywords text,
  metatags_description text,
  PRIMARY KEY  (categories_id,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'meta_tags_products_description'
#

DROP TABLE IF EXISTS meta_tags_products_description;
CREATE TABLE meta_tags_products_description (
  products_id int(11) NOT NULL,
  language_id int(11) NOT NULL default '1',
  metatags_title varchar(255) NOT NULL default '',
  metatags_keywords text,
  metatags_description text,
  PRIMARY KEY  (products_id,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'music_genre'
#

DROP TABLE IF EXISTS music_genre;
CREATE TABLE music_genre (
  music_genre_id int(11) NOT NULL auto_increment,
  music_genre_name varchar(32) NOT NULL default '',
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (music_genre_id),
  KEY idx_music_genre_name_zen (music_genre_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'newsletters'
#

DROP TABLE IF EXISTS newsletters;
CREATE TABLE newsletters (
  newsletters_id int(11) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  content text NOT NULL,
  content_html text NOT NULL,
  module varchar(255) NOT NULL default '',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  date_sent datetime default NULL,
  status int(1) default NULL,
  locked int(1) default '0',
  PRIMARY KEY  (newsletters_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders'
#

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  orders_id int(11) NOT NULL auto_increment,
  customers_id int(11) NOT NULL default 0,
  customers_name varchar(64) NOT NULL default '',
  customers_company varchar(64) default NULL,
  customers_street_address varchar(64) NOT NULL default '',
  customers_suburb varchar(32) default NULL,
  customers_city varchar(32) NOT NULL default '',
  customers_postcode varchar(10) NOT NULL default '',
  customers_state varchar(32) default NULL,
  customers_country varchar(32) NOT NULL default '',
  customers_telephone varchar(32) NOT NULL default '',
  customers_email_address varchar(96) NOT NULL default '',
  customers_address_format_id int(5) NOT NULL default 0,
  delivery_name varchar(64) NOT NULL default '',
  delivery_company varchar(64) default NULL,
  delivery_street_address varchar(64) NOT NULL default '',
  delivery_suburb varchar(32) default NULL,
  delivery_city varchar(32) NOT NULL default '',
  delivery_postcode varchar(10) NOT NULL default '',
  delivery_state varchar(32) default NULL,
  delivery_country varchar(32) NOT NULL default '',
  delivery_address_format_id int(5) NOT NULL default 0,
  billing_name varchar(64) NOT NULL default '',
  billing_company varchar(64) default NULL,
  billing_street_address varchar(64) NOT NULL default '',
  billing_suburb varchar(32) default NULL,
  billing_city varchar(32) NOT NULL default '',
  billing_postcode varchar(10) NOT NULL default '',
  billing_state varchar(32) default NULL,
  billing_country varchar(32) NOT NULL default '',
  billing_address_format_id int(5) NOT NULL default 0,
  payment_method varchar(128) NOT NULL default '',
  payment_module_code varchar(32) NOT NULL default '',
  shipping_method varchar(255) default NULL,
  shipping_module_code varchar(32) NOT NULL default '',
  coupon_code varchar(32) NOT NULL default '',
  cc_type varchar(20) default NULL,
  cc_owner varchar(64) default NULL,
  cc_number varchar(32) default NULL,
  cc_expires varchar(4) default NULL,
  cc_cvv blob,
  last_modified datetime default NULL,
  date_purchased datetime default NULL,
  orders_status int(5) NOT NULL default 0,
  orders_date_finished datetime default NULL,
  currency char(3) default NULL,
  currency_value decimal(14,6) default NULL,
  order_total decimal(15,4) default NULL,
  order_tax decimal(15,4) default NULL,
  paypal_ipn_id int(11) NOT NULL default 0,
  ip_address varchar(96) NOT NULL default '',
  order_weight float default NULL,
  language_code char(2) NOT NULL default '',
  PRIMARY KEY  (orders_id),
  KEY idx_status_orders_cust_zen (orders_status,orders_id,customers_id),
  KEY idx_date_purchased_zen (date_purchased),
  KEY idx_cust_id_orders_id_zen (customers_id,orders_id),
  KEY idx_status_date_id_zen (orders_status,date_purchased,orders_id)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'orders_products'
#

DROP TABLE IF EXISTS orders_products;
CREATE TABLE orders_products (
  orders_products_id int(11) NOT NULL auto_increment,
  orders_id int(11) NOT NULL default '0',
  products_id int(11) NOT NULL default '0',
  products_model varchar(32) default NULL,
  products_name varchar(64) NOT NULL default '',
  products_price decimal(15,4) NOT NULL default '0.0000',
  final_price decimal(15,4) NOT NULL default '0.0000',
  products_tax decimal(7,4) NOT NULL default '0.0000',
  products_quantity float NOT NULL default '0',
  onetime_charges decimal(15,4) NOT NULL default '0.0000',
  products_priced_by_attribute tinyint(1) NOT NULL default 0,
  product_is_free tinyint(1) NOT NULL default 0,
  products_discount_type tinyint(1) NOT NULL default 0,
  products_discount_type_from tinyint(1) NOT NULL default 0,
  products_prid tinytext NOT NULL,
  products_weight FLOAT default NULL,
  products_virtual tinyint(1) default NULL,
  product_is_always_free_shipping tinyint(1) default NULL,
  products_quantity_order_min float default NULL,
  products_quantity_order_units float default NULL,
  products_quantity_order_max float default NULL,
  products_quantity_mixed tinyint(1) default NULL,
  products_mixed_discount_quantity tinyint(1) default NULL,
  PRIMARY KEY  (orders_products_id),
  KEY idx_orders_id_prod_id_zen (orders_id,products_id),
  KEY idx_prod_id_orders_id_zen (products_id,orders_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders_products_attributes'
#

DROP TABLE IF EXISTS orders_products_attributes;
CREATE TABLE orders_products_attributes (
  orders_products_attributes_id int(11) NOT NULL auto_increment,
  orders_id int(11) NOT NULL default '0',
  orders_products_id int(11) NOT NULL default '0',
  products_options varchar(32) NOT NULL default '',
  products_options_values text NOT NULL,
  options_values_price decimal(15,4) NOT NULL default '0.0000',
  price_prefix char(1) NOT NULL default '',
  product_attribute_is_free tinyint(1) NOT NULL default '0',
  products_attributes_weight float NOT NULL default '0',
  products_attributes_weight_prefix char(1) NOT NULL default '',
  attributes_discounted tinyint(1) NOT NULL default '1',
  attributes_price_base_included tinyint(1) NOT NULL default '1',
  attributes_price_onetime decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_offset decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_onetime decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_onetime_offset decimal(15,4) NOT NULL default '0.0000',
  attributes_qty_prices text,
  attributes_qty_prices_onetime text,
  attributes_price_words decimal(15,4) NOT NULL default '0.0000',
  attributes_price_words_free int(4) NOT NULL default '0',
  attributes_price_letters decimal(15,4) NOT NULL default '0.0000',
  attributes_price_letters_free int(4) NOT NULL default '0',
  products_options_id int(11) NOT NULL default '0',
  products_options_values_id int(11) NOT NULL default '0',
  products_prid tinytext NOT NULL,
  PRIMARY KEY  (orders_products_attributes_id),
  KEY idx_orders_id_prod_id_zen (orders_id,orders_products_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders_products_download'
#

DROP TABLE IF EXISTS orders_products_download;
CREATE TABLE orders_products_download (
  orders_products_download_id int(11) NOT NULL auto_increment,
  orders_id int(11) NOT NULL default 0,
  orders_products_id int(11) NOT NULL default 0,
  orders_products_filename varchar(255) NOT NULL default '',
  download_maxdays int(2) NOT NULL default 0,
  download_count int(2) NOT NULL default 0,
  products_prid tinytext NOT NULL,
  products_attributes_id int(11) default NULL,
  PRIMARY KEY  (orders_products_download_id),
  KEY idx_orders_id_zen (orders_id),
  KEY idx_orders_products_id_zen (orders_products_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders_status'
#

DROP TABLE IF EXISTS orders_status;
CREATE TABLE orders_status (
  orders_status_id int(11) NOT NULL default 0,
  language_id int(11) NOT NULL default 1,
  orders_status_name varchar(32) NOT NULL default '',
  sort_order int(11) NOT NULL default 0,
  PRIMARY KEY  (orders_status_id,language_id),
  KEY idx_orders_status_name_zen (orders_status_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders_status_history'
#

DROP TABLE IF EXISTS orders_status_history;
CREATE TABLE orders_status_history (
  orders_status_history_id int(11) NOT NULL auto_increment,
  orders_id int(11) NOT NULL default '0',
  orders_status_id int(5) NOT NULL default '0',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  customer_notified int(1) default '0',
  comments text,
  updated_by varchar(45) NOT NULL default '',
  PRIMARY KEY  (orders_status_history_id),
  KEY idx_orders_id_status_id_zen (orders_id,orders_status_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'orders_total'
#

DROP TABLE IF EXISTS orders_total;
CREATE TABLE orders_total (
  orders_total_id int(10) unsigned NOT NULL auto_increment,
  orders_id int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  text varchar(255) NOT NULL default '',
  value decimal(15,4) NOT NULL default '0.0000',
  class varchar(32) NOT NULL default '',
  sort_order int(11) NOT NULL default '0',
  PRIMARY KEY  (orders_total_id),
  KEY idx_ot_orders_id_zen (orders_id),
  KEY idx_ot_class_zen (class),
  KEY idx_oid_class_zen (orders_id, class)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'paypal'
#

DROP TABLE IF EXISTS paypal;
CREATE TABLE paypal (
  paypal_ipn_id int(11) unsigned NOT NULL auto_increment,
  order_id int(11) unsigned NOT NULL default '0',
  txn_type varchar(40) NOT NULL default '',
  module_name varchar(40) NOT NULL default '',
  module_mode varchar(40) NOT NULL default '',
  reason_code varchar(40) default NULL,
  payment_type varchar(40) NOT NULL default '',
  payment_status varchar(32) NOT NULL default '',
  pending_reason varchar(32) default NULL,
  invoice varchar(128) default NULL,
  mc_currency char(3) NOT NULL default '',
  first_name varchar(32) NOT NULL default '',
  last_name varchar(32) NOT NULL default '',
  payer_business_name varchar(128) default NULL,
  address_name varchar(64) default NULL,
  address_street varchar(254) default NULL,
  address_city varchar(120) default NULL,
  address_state varchar(120) default NULL,
  address_zip varchar(10) default NULL,
  address_country varchar(64) default NULL,
  address_status varchar(11) default NULL,
  payer_email varchar(128) NOT NULL default '',
  payer_id varchar(32) NOT NULL default '',
  payer_status varchar(10) NOT NULL default '',
  payment_date datetime NOT NULL default '0001-01-01 00:00:00',
  business varchar(128) NOT NULL default '',
  receiver_email varchar(128) NOT NULL default '',
  receiver_id varchar(32) NOT NULL default '',
  txn_id varchar(20) NOT NULL default '',
  parent_txn_id varchar(20) default NULL,
  num_cart_items tinyint(4) unsigned NOT NULL default '1',
  mc_gross decimal(15,4) NOT NULL default '0.00',
  mc_fee decimal(15,4) NOT NULL default '0.00',
  payment_gross decimal(15,4) default NULL,
  payment_fee decimal(15,4) default NULL,
  settle_amount decimal(15,4) default NULL,
  settle_currency char(3) default NULL,
  exchange_rate decimal(15,4) default NULL,
  notify_version varchar(6) NOT NULL default '',
  verify_sign varchar(128) NOT NULL default '',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  memo text,
  PRIMARY KEY (paypal_ipn_id,txn_id),
  KEY idx_order_id_zen (order_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'paypal_payment_status'
#

DROP TABLE IF EXISTS paypal_payment_status;
CREATE TABLE paypal_payment_status (
  payment_status_id int(11) NOT NULL auto_increment,
  payment_status_name varchar(64) NOT NULL default '',
  PRIMARY KEY (payment_status_id)
) ENGINE=MyISAM;

#
# Dumping data for table 'paypal_payment_status'
#

INSERT INTO paypal_payment_status VALUES (1, 'Completed');
INSERT INTO paypal_payment_status VALUES (2, 'Pending');
INSERT INTO paypal_payment_status VALUES (3, 'Failed');
INSERT INTO paypal_payment_status VALUES (4, 'Denied');
INSERT INTO paypal_payment_status VALUES (5, 'Refunded');
INSERT INTO paypal_payment_status VALUES (6, 'Canceled_Reversal');
INSERT INTO paypal_payment_status VALUES (7, 'Reversed');

# --------------------------------------------------------

#
# Table structure for table 'paypal_payment_status_history'
#

DROP TABLE IF EXISTS paypal_payment_status_history;
CREATE TABLE paypal_payment_status_history (
  payment_status_history_id int(11) NOT NULL auto_increment,
  paypal_ipn_id int(11) NOT NULL default '0',
  txn_id varchar(64) NOT NULL default '',
  parent_txn_id varchar(64) NOT NULL default '',
  payment_status varchar(17) NOT NULL default '',
  pending_reason varchar(32) default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY (payment_status_history_id),
  KEY idx_paypal_ipn_id_zen (paypal_ipn_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'paypal_session'
#

DROP TABLE IF EXISTS paypal_session;
CREATE TABLE paypal_session (
  unique_id int(11) NOT NULL auto_increment,
  session_id text NOT NULL,
  saved_session mediumblob NOT NULL,
  expiry int(17) NOT NULL default '0',
  PRIMARY KEY  (unique_id),
  KEY idx_session_id_zen (session_id(36))
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'paypal_testing'
#

DROP TABLE IF EXISTS paypal_testing;
CREATE TABLE paypal_testing (
  paypal_ipn_id int(11) unsigned NOT NULL auto_increment,
  order_id int(11) unsigned NOT NULL default '0',
  custom varchar(255) NOT NULL default '',
  txn_type varchar(40) NOT NULL default '',
  module_name varchar(40) NOT NULL default '',
  module_mode varchar(40) NOT NULL default '',
  reason_code varchar(40) default NULL,
  payment_type varchar(40) NOT NULL default '',
  payment_status varchar(32) NOT NULL default '',
  pending_reason varchar(32) default NULL,
  invoice varchar(128) default NULL,
  mc_currency char(3) NOT NULL default '',
  first_name varchar(32) NOT NULL default '',
  last_name varchar(32) NOT NULL default '',
  payer_business_name varchar(128) default NULL,
  address_name varchar(64) default NULL,
  address_street varchar(254) default NULL,
  address_city varchar(120) default NULL,
  address_state varchar(120) default NULL,
  address_zip varchar(10) default NULL,
  address_country varchar(64) default NULL,
  address_status varchar(11) default NULL,
  payer_email varchar(128) NOT NULL default '',
  payer_id varchar(32) NOT NULL default '',
  payer_status varchar(10) NOT NULL default '',
  payment_date datetime NOT NULL default '0001-01-01 00:00:00',
  business varchar(128) NOT NULL default '',
  receiver_email varchar(128) NOT NULL default '',
  receiver_id varchar(32) NOT NULL default '',
  txn_id varchar(20) NOT NULL default '',
  parent_txn_id varchar(20) default NULL,
  num_cart_items tinyint(4) unsigned NOT NULL default '1',
  mc_gross decimal(7,2) NOT NULL default '0.00',
  mc_fee decimal(7,2) NOT NULL default '0.00',
  payment_gross decimal(7,2) default NULL,
  payment_fee decimal(7,2) default NULL,
  settle_amount decimal(7,2) default NULL,
  settle_currency char(3) default NULL,
  exchange_rate decimal(4,2) default NULL,
  notify_version decimal(2,1) NOT NULL default '0.0',
  verify_sign varchar(128) NOT NULL default '',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  memo text,
  PRIMARY KEY  (paypal_ipn_id,txn_id),
  KEY idx_order_id_zen (order_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_control'
#

DROP TABLE IF EXISTS plugin_control;
CREATE TABLE plugin_control (
  unique_key varchar(40) NOT NULL,
  name varchar(64) NOT NULL default '',
  description text,
  type varchar(11) NOT NULL default 'free',
  managed tinyint(1) NOT NULL default 0,
  status tinyint(1) NOT NULL default 0,
  author varchar(64) NOT NULL,
  version varchar(10),
  zc_versions text NOT NULL,
  zc_contrib_id int(11),
  infs tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (unique_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_control_versions'
#

DROP TABLE IF EXISTS plugin_control_versions;
CREATE TABLE plugin_control_versions (
  unique_key varchar(40) NOT NULL,
  version varchar(10),
  author varchar(64) NOT NULL,
  zc_versions text NOT NULL,
  infs tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (unique_key, version)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_groups'
#

DROP TABLE IF EXISTS plugin_groups;
CREATE TABLE plugin_groups (
  unique_key varchar(20) NOT NULL,
  PRIMARY KEY  (unique_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_groups_description'
#

DROP TABLE IF EXISTS plugin_groups_description;
CREATE TABLE plugin_groups_description (
  plugin_group_unique_key varchar(20) NOT NULL,
  language_id int(11) NOT NULL default 1,
  name varchar(64) NOT NULL default '',
  PRIMARY KEY  (plugin_group_unique_key,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'product_music_extra'
#

DROP TABLE IF EXISTS product_music_extra;
CREATE TABLE product_music_extra (
  products_id int(11) NOT NULL default '0',
  artists_id int(11) NOT NULL default '0',
  record_company_id int(11) NOT NULL default '0',
  music_genre_id int(11) NOT NULL default '0',
  PRIMARY KEY  (products_id),
  KEY idx_music_genre_id_zen (music_genre_id),
  KEY idx_artists_id_zen (artists_id),
  KEY idx_record_company_id_zen (record_company_id)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'product_type_layout'
#
DROP TABLE IF EXISTS product_type_layout;
CREATE TABLE product_type_layout (
  configuration_id int(11) NOT NULL auto_increment,
  configuration_title text NOT NULL,
  configuration_key varchar(180) NOT NULL default '',
  configuration_value text NOT NULL,
  configuration_description text NOT NULL,
  product_type_id int(11) NOT NULL default '0',
  sort_order int(5) default NULL,
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  use_function text default NULL,
  set_function text default NULL,
  PRIMARY KEY  (configuration_id),
  UNIQUE KEY unq_config_key_zen (configuration_key),
  KEY idx_key_value_zen (configuration_key,configuration_value(10)),
  KEY idx_type_id_sort_order_zen (product_type_id,sort_order)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'product_types'
#

DROP TABLE IF EXISTS product_types;
CREATE TABLE product_types (
  type_id int(11) NOT NULL auto_increment,
  type_name varchar(255) NOT NULL default '',
  type_handler varchar(255) NOT NULL default '',
  type_master_type int(11) NOT NULL default '1',
  allow_add_to_cart char(1) NOT NULL default 'Y',
  default_image varchar(255) NOT NULL default '',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (type_id),
  KEY idx_type_master_type_zen (type_master_type)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'product_types_to_category'
#

DROP TABLE IF EXISTS product_types_to_category;
CREATE TABLE product_types_to_category (
  product_type_id int(11) NOT NULL default '0',
  category_id int(11) NOT NULL default '0',
  KEY idx_category_id_zen (category_id),
  KEY idx_product_type_id_zen (product_type_id)
) ENGINE=MyISAM;


# --------------------------------------------------------

#
# Table structure for table 'products'
#

DROP TABLE IF EXISTS products;
CREATE TABLE products (
  products_id int(11) NOT NULL auto_increment,
  products_type int(11) NOT NULL default '1',
  products_quantity float NOT NULL default '0',
  products_model varchar(32) default NULL,
  products_image varchar(255) default NULL,
  products_price decimal(15,4) NOT NULL default '0.0000',
  products_virtual tinyint(1) NOT NULL default '0',
  products_date_added datetime NOT NULL default '0001-01-01 00:00:00',
  products_last_modified datetime default NULL,
  products_date_available datetime default NULL,
  products_weight float NOT NULL default '0',
  products_status tinyint(1) NOT NULL default '0',
  products_tax_class_id int(11) NOT NULL default '0',
  manufacturers_id int(11) default NULL,
  products_ordered float NOT NULL default '0',
  products_quantity_order_min float NOT NULL default '1',
  products_quantity_order_units float NOT NULL default '1',
  products_priced_by_attribute tinyint(1) NOT NULL default '0',
  product_is_free tinyint(1) NOT NULL default '0',
  product_is_call tinyint(1) NOT NULL default '0',
  products_quantity_mixed tinyint(1) NOT NULL default '0',
  product_is_always_free_shipping tinyint(1) NOT NULL default '0',
  products_qty_box_status tinyint(1) NOT NULL default '1',
  products_quantity_order_max float NOT NULL default '0',
  products_sort_order int(11) NOT NULL default '0',
  products_discount_type tinyint(1) NOT NULL default '0',
  products_discount_type_from tinyint(1) NOT NULL default '0',
  products_price_sorter decimal(15,4) NOT NULL default '0.0000',
  master_categories_id int(11) NOT NULL default '0',
  products_mixed_discount_quantity tinyint(1) NOT NULL default '1',
  metatags_title_status tinyint(1) NOT NULL default '0',
  metatags_products_name_status tinyint(1) NOT NULL default '0',
  metatags_model_status tinyint(1) NOT NULL default '0',
  metatags_price_status tinyint(1) NOT NULL default '0',
  metatags_title_tagline_status tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (products_id),
  KEY idx_products_date_added_zen (products_date_added),
  KEY idx_products_status_zen (products_status),
  KEY idx_products_date_available_zen (products_date_available),
  KEY idx_products_ordered_zen (products_ordered),
  KEY idx_products_model_zen (products_model),
  KEY idx_products_price_sorter_zen (products_price_sorter),
  KEY idx_master_categories_id_zen (master_categories_id),
  KEY idx_products_sort_order_zen (products_sort_order),
  KEY idx_manufacturers_id_zen (manufacturers_id)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table 'products_attributes'
#

DROP TABLE IF EXISTS products_attributes;
CREATE TABLE products_attributes (
  products_attributes_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  options_id int(11) NOT NULL default '0',
  options_values_id int(11) NOT NULL default '0',
  options_values_price decimal(15,4) NOT NULL default '0.0000',
  price_prefix char(1) NOT NULL default '',
  products_options_sort_order int(11) NOT NULL default '0',
  product_attribute_is_free tinyint(1) NOT NULL default '0',
  products_attributes_weight float NOT NULL default '0',
  products_attributes_weight_prefix char(1) NOT NULL default '',
  attributes_display_only tinyint(1) NOT NULL default '0',
  attributes_default tinyint(1) NOT NULL default '0',
  attributes_discounted tinyint(1) NOT NULL default '1',
  attributes_image varchar(255) default NULL,
  attributes_price_base_included tinyint(1) NOT NULL default '1',
  attributes_price_onetime decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_offset decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_onetime decimal(15,4) NOT NULL default '0.0000',
  attributes_price_factor_onetime_offset decimal(15,4) NOT NULL default '0.0000',
  attributes_qty_prices text,
  attributes_qty_prices_onetime text,
  attributes_price_words decimal(15,4) NOT NULL default '0.0000',
  attributes_price_words_free int(4) NOT NULL default '0',
  attributes_price_letters decimal(15,4) NOT NULL default '0.0000',
  attributes_price_letters_free int(4) NOT NULL default '0',
  attributes_required tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (products_attributes_id),
  KEY idx_id_options_id_values_zen (products_id,options_id,options_values_id),
  KEY idx_opt_sort_order_zen (products_options_sort_order)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_attributes_download'
#

DROP TABLE IF EXISTS products_attributes_download;
CREATE TABLE products_attributes_download (
  products_attributes_id int(11) NOT NULL default '0',
  products_attributes_filename varchar(255) NOT NULL default '',
  products_attributes_maxdays int(2) default '0',
  products_attributes_maxcount int(2) default '0',
  PRIMARY KEY  (products_attributes_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_description'
#

DROP TABLE IF EXISTS products_description;
CREATE TABLE products_description (
  products_id int(11) NOT NULL,
  language_id int(11) NOT NULL default '1',
  products_name varchar(64) NOT NULL default '',
  products_description text,
  products_url varchar(255) default NULL,
  products_viewed int(5) default '0',
  PRIMARY KEY  (products_id,language_id),
  KEY idx_products_name_zen (products_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_discount_quantity'
#
DROP TABLE IF EXISTS products_discount_quantity;
CREATE TABLE products_discount_quantity (
  discount_id int(4) NOT NULL default '0',
  products_id int(11) NOT NULL default '0',
  discount_qty float NOT NULL default '0',
  discount_price decimal(15,4) NOT NULL default '0.0000',
  KEY idx_id_qty_zen (products_id,discount_qty)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_notifications'
#

DROP TABLE IF EXISTS products_notifications;
CREATE TABLE products_notifications (
  products_id int(11) NOT NULL default '0',
  customers_id int(11) NOT NULL default '0',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (products_id,customers_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_options'
#

DROP TABLE IF EXISTS products_options;
CREATE TABLE products_options (
  products_options_id int(11) NOT NULL default '0',
  language_id int(11) NOT NULL default '1',
  products_options_name varchar(32) NOT NULL default '',
  products_options_sort_order int(11) NOT NULL default '0',
  products_options_type int(5) NOT NULL default '0',
  products_options_length smallint(2) NOT NULL default '32',
  products_options_comment varchar(256) default NULL,
  products_options_size smallint(2) NOT NULL default '32',
  products_options_images_per_row int(2) default '5',
  products_options_images_style int(1) default '0',
  products_options_rows smallint(2) NOT NULL default '1',
  PRIMARY KEY  (products_options_id,language_id),
  KEY idx_lang_id_zen (language_id),
  KEY idx_products_options_sort_order_zen (products_options_sort_order),
  KEY idx_products_options_name_zen (products_options_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_options_types'
#

DROP TABLE IF EXISTS products_options_types;
CREATE TABLE products_options_types (
  products_options_types_id int(11) NOT NULL default '0',
  products_options_types_name varchar(32) default NULL,
  PRIMARY KEY  (products_options_types_id)
) ENGINE=MyISAM COMMENT='Track products_options_types';

# --------------------------------------------------------

#
# Table structure for table 'products_options_values'
#

DROP TABLE IF EXISTS products_options_values;
CREATE TABLE products_options_values (
  products_options_values_id int(11) NOT NULL default '0',
  language_id int(11) NOT NULL default '1',
  products_options_values_name varchar(64) NOT NULL default '',
  products_options_values_sort_order int(11) NOT NULL default '0',
  PRIMARY KEY (products_options_values_id,language_id),
  KEY idx_products_options_values_name_zen (products_options_values_name),
  KEY idx_products_options_values_sort_order_zen (products_options_values_sort_order)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_options_values_to_products_options'
#

DROP TABLE IF EXISTS products_options_values_to_products_options;
CREATE TABLE products_options_values_to_products_options (
  products_options_values_to_products_options_id int(11) NOT NULL auto_increment,
  products_options_id int(11) NOT NULL default '0',
  products_options_values_id int(11) NOT NULL default '0',
  PRIMARY KEY  (products_options_values_to_products_options_id),
  KEY idx_products_options_id_zen (products_options_id),
  KEY idx_products_options_values_id_zen (products_options_values_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'products_to_categories'
#

DROP TABLE IF EXISTS products_to_categories;
CREATE TABLE products_to_categories (
  products_id int(11) NOT NULL default '0',
  categories_id int(11) NOT NULL default '0',
  PRIMARY KEY  (products_id,categories_id),
  KEY idx_cat_prod_id_zen (categories_id,products_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'project_version'
#

DROP TABLE IF EXISTS project_version;
CREATE TABLE project_version (
  project_version_id tinyint(3) NOT NULL auto_increment,
  project_version_key varchar(40) NOT NULL default '',
  project_version_major varchar(20) NOT NULL default '',
  project_version_minor varchar(20) NOT NULL default '',
  project_version_patch1 varchar(20) NOT NULL default '',
  project_version_patch2 varchar(20) NOT NULL default '',
  project_version_patch1_source varchar(20) NOT NULL default '',
  project_version_patch2_source varchar(20) NOT NULL default '',
  project_version_comment varchar(250) NOT NULL default '',
  project_version_date_applied datetime NOT NULL default '0001-01-01 01:01:01',
  PRIMARY KEY  (project_version_id),
  UNIQUE KEY idx_project_version_key_zen (project_version_key)
) ENGINE=MyISAM COMMENT='Database Version Tracking';


# --------------------------------------------------------

#
# Table structure for table 'project_version_history'
#
DROP TABLE IF EXISTS project_version_history;
CREATE TABLE project_version_history (
  project_version_id tinyint(3) NOT NULL auto_increment,
  project_version_key varchar(40) NOT NULL default '',
  project_version_major varchar(20) NOT NULL default '',
  project_version_minor varchar(20) NOT NULL default '',
  project_version_patch varchar(20) NOT NULL default '',
  project_version_comment varchar(250) NOT NULL default '',
  project_version_date_applied datetime NOT NULL default '0001-01-01 01:01:01',
  PRIMARY KEY  (project_version_id)
) ENGINE=MyISAM COMMENT='Database Version Tracking History';

# --------------------------------------------------------

#
# Table structure for table 'query_builder'
# This table is used by audiences.php for building data-extraction queries
#
DROP TABLE IF EXISTS query_builder;
CREATE TABLE query_builder (
  query_id int(11) NOT NULL auto_increment,
  query_category varchar(40) NOT NULL default '',
  query_name varchar(80) NOT NULL default '',
  query_description TEXT NOT NULL,
  query_string TEXT NOT NULL,
  query_keys_list TEXT NOT NULL,
  PRIMARY KEY  (query_id),
  UNIQUE KEY query_name (query_name)
) ENGINE=MyISAM COMMENT='Stores queries for re-use in Admin email and report modules';

# --------------------------------------------------------

#
# Table structure for table 'record_artists'
#

DROP TABLE IF EXISTS record_artists;
CREATE TABLE record_artists (
  artists_id int(11) NOT NULL auto_increment,
  artists_name varchar(32) NOT NULL default '',
  artists_image varchar(255) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (artists_id),
  KEY idx_rec_artists_name_zen (artists_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_artists_info'
#

DROP TABLE IF EXISTS record_artists_info;
CREATE TABLE record_artists_info (
  artists_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  artists_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime default NULL,
  PRIMARY KEY  (artists_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_company'
#

DROP TABLE IF EXISTS record_company;
CREATE TABLE record_company (
  record_company_id int(11) NOT NULL auto_increment,
  record_company_name varchar(32) NOT NULL default '',
  record_company_image varchar(255) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (record_company_id),
  KEY idx_rec_company_name_zen (record_company_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_company_info'
#

DROP TABLE IF EXISTS record_company_info;
CREATE TABLE record_company_info (
  record_company_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  record_company_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime default NULL,
  PRIMARY KEY  (record_company_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'reviews'
#

DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
  reviews_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  customers_id int(11) default NULL,
  customers_name varchar(64) NOT NULL default '',
  reviews_rating int(1) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  reviews_read int(5) NOT NULL default '0',
  status int(1) NOT NULL default '1',
  PRIMARY KEY  (reviews_id),
  KEY idx_products_id_zen (products_id),
  KEY idx_customers_id_zen (customers_id),
  KEY idx_status_zen (status),
  KEY idx_date_added_zen (date_added)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'reviews_description'
#

DROP TABLE IF EXISTS reviews_description;
CREATE TABLE reviews_description (
  reviews_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  reviews_text text NOT NULL,
  PRIMARY KEY  (reviews_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'salemaker_sales'
#

DROP TABLE IF EXISTS salemaker_sales;
CREATE TABLE salemaker_sales (
  sale_id int(11) NOT NULL auto_increment,
  sale_status tinyint(4) NOT NULL default '0',
  sale_name varchar(128) NOT NULL default '',
  sale_deduction_value decimal(15,4) NOT NULL default '0.0000',
  sale_deduction_type tinyint(4) NOT NULL default '0',
  sale_pricerange_from decimal(15,4) NOT NULL default '0.0000',
  sale_pricerange_to decimal(15,4) NOT NULL default '0.0000',
  sale_specials_condition tinyint(4) NOT NULL default '0',
  sale_categories_selected text,
  sale_categories_all text,
  sale_date_start date NOT NULL default '0001-01-01',
  sale_date_end date NOT NULL default '0001-01-01',
  sale_date_added date NOT NULL default '0001-01-01',
  sale_date_last_modified date NOT NULL default '0001-01-01',
  sale_date_status_change date NOT NULL default '0001-01-01',
  PRIMARY KEY  (sale_id),
  KEY idx_sale_status_zen (sale_status),
  KEY idx_sale_date_start_zen (sale_date_start),
  KEY idx_sale_date_end_zen (sale_date_end)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'sessions'
# NOTE: requires minimum MySQL 5.0.3 for varchar(256)
# NOTE: leaving it at (191) to be compatible with utf8mb4, but if PHP is configured to use 256 char session IDs then this will break.
#

DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  sesskey varchar(191) NOT NULL default '',
  expiry int(11) unsigned NOT NULL default 0,
  value mediumblob NOT NULL,
  PRIMARY KEY  (sesskey)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table 'specials'
#

DROP TABLE IF EXISTS specials;
CREATE TABLE specials (
  specials_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  specials_new_products_price decimal(15,4) NOT NULL default '0.0000',
  specials_date_added datetime default NULL,
  specials_last_modified datetime default NULL,
  expires_date date NOT NULL default '0001-01-01',
  date_status_change datetime default NULL,
  status int(1) NOT NULL default '1',
  specials_date_available date NOT NULL default '0001-01-01',
  PRIMARY KEY  (specials_id),
  KEY idx_status_zen (status),
  KEY idx_products_id_zen (products_id),
  KEY idx_date_avail_zen (specials_date_available),
  KEY idx_expires_date_zen (expires_date)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'tax_class'
#

DROP TABLE IF EXISTS tax_class;
CREATE TABLE tax_class (
  tax_class_id int(11) NOT NULL auto_increment,
  tax_class_title varchar(32) NOT NULL default '',
  tax_class_description varchar(255) NOT NULL default '',
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (tax_class_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'tax_rates'
#

DROP TABLE IF EXISTS tax_rates;
CREATE TABLE tax_rates (
  tax_rates_id int(11) NOT NULL auto_increment,
  tax_zone_id int(11) NOT NULL default '0',
  tax_class_id int(11) NOT NULL default '0',
  tax_priority int(5) default '1',
  tax_rate decimal(7,4) NOT NULL default '0.0000',
  tax_description varchar(255) NOT NULL default '',
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (tax_rates_id),
  KEY idx_tax_zone_id_zen (tax_zone_id),
  KEY idx_tax_class_id_zen (tax_class_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'template_select'
#

DROP TABLE IF EXISTS template_select;
CREATE TABLE template_select (
  template_id int(11) NOT NULL auto_increment,
  template_dir varchar(64) NOT NULL default '',
  template_language varchar(64) NOT NULL default '0',
  PRIMARY KEY  (template_id),
  KEY idx_tpl_lang_zen (template_language)
) ENGINE=MyISAM;

# --------------------------------------------------------


#
# Table structure for table 'whos_online'
# NOTE: session_id needs to be same length (er, at least as long) as sesskey in 'sessions' table.
#

DROP TABLE IF EXISTS whos_online;
CREATE TABLE whos_online (
  customer_id int(11) default NULL,
  full_name varchar(64) NOT NULL default '',
  session_id varchar(191) NOT NULL default '',
  ip_address varchar(45) NOT NULL default '',
  time_entry varchar(14) NOT NULL default '',
  time_last_click varchar(14) NOT NULL default '',
  last_page_url varchar(255) NOT NULL default '',
  host_address text NOT NULL,
  user_agent varchar(255) NOT NULL default '',
  KEY idx_ip_address_zen (ip_address),
  KEY idx_session_id_zen (session_id),
  KEY idx_customer_id_zen (customer_id),
  KEY idx_time_entry_zen (time_entry),
  KEY idx_time_last_click_zen (time_last_click),
  KEY idx_last_page_url_zen (last_page_url(191))
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'zones'
#

DROP TABLE IF EXISTS zones;
CREATE TABLE zones (
  zone_id int(11) NOT NULL auto_increment,
  zone_country_id int(11) NOT NULL default '0',
  zone_code varchar(32) NOT NULL default '',
  zone_name varchar(32) NOT NULL default '',
  PRIMARY KEY  (zone_id),
  KEY idx_zone_country_id_zen (zone_country_id),
  KEY idx_zone_code_zen (zone_code)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'zones_to_geo_zones'
#

DROP TABLE IF EXISTS zones_to_geo_zones;
CREATE TABLE zones_to_geo_zones (
  association_id int(11) NOT NULL auto_increment,
  zone_country_id int(11) NOT NULL default '0',
  zone_id int(11) default NULL,
  geo_zone_id int(11) default NULL,
  last_modified datetime default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (association_id),
  KEY idx_zones_zen (geo_zone_id,zone_country_id,zone_id)
) ENGINE=MyISAM;















# default data

INSERT INTO template_select (template_id, template_dir, template_language) VALUES (1, 'responsive_classic', '0');

# 1 - Default, 2 - USA, 3 - Spain, 4 - Singapore, 5 - Germany, 6 - UK/GB, 7 - Australia
INSERT INTO address_format VALUES (1, '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country','$city / $country');
INSERT INTO address_format VALUES (2, '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country','$city, $state / $country');
INSERT INTO address_format VALUES (3, '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country','$state / $country');
INSERT INTO address_format VALUES (4, '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country', '$postcode / $country');
INSERT INTO address_format VALUES (5, '$firstname $lastname$cr$streets$cr$postcode $city$cr$country','$city / $country');
INSERT INTO address_format VALUES (6, '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country','$postcode / $country');
INSERT INTO address_format VALUES (7, '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country','$city $state / $country');

INSERT INTO admin (admin_id, admin_name, admin_email, admin_pass, admin_profile, last_modified) VALUES
 (1, 'Admin', 'admin@localhost', '351683ea4e19efe34874b501fdbf9792:9b', 1, now());

# Insert default data into admin profiles table
INSERT INTO admin_profiles (profile_id, profile_name) VALUES (1, 'Superuser');

# Insert default data into admin_menus table
INSERT INTO admin_menus (menu_key, language_key, sort_order)
VALUES ('configuration', 'BOX_HEADING_CONFIGURATION', 1),
       ('catalog', 'BOX_HEADING_CATALOG', 2),
       ('modules', 'BOX_HEADING_MODULES', 3),
       ('customers', 'BOX_HEADING_CUSTOMERS', 4),
       ('taxes', 'BOX_HEADING_LOCATION_AND_TAXES', 5),
       ('localization', 'BOX_HEADING_LOCALIZATION', 6),
       ('reports', 'BOX_HEADING_REPORTS', 7),
       ('tools', 'BOX_HEADING_TOOLS', 8),
       ('gv', 'BOX_HEADING_GV_ADMIN', 9),
       ('access', 'BOX_HEADING_ADMIN_ACCESS', 10),
       ('extras', 'BOX_HEADING_EXTRAS', 11);

# Insert data into admin_pages table
INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('configMyStore', 'BOX_CONFIGURATION_MY_STORE', 'FILENAME_CONFIGURATION', 'gID=1', 'configuration', 'Y', 1),
       ('configMinimumValues', 'BOX_CONFIGURATION_MINIMUM_VALUES', 'FILENAME_CONFIGURATION', 'gID=2', 'configuration', 'Y', 2),
       ('configMaximumValues', 'BOX_CONFIGURATION_MAXIMUM_VALUES', 'FILENAME_CONFIGURATION', 'gID=3', 'configuration', 'Y', 3),
       ('configImages', 'BOX_CONFIGURATION_IMAGES', 'FILENAME_CONFIGURATION', 'gID=4', 'configuration', 'Y', 4),
       ('configCustomerDetails', 'BOX_CONFIGURATION_CUSTOMER_DETAILS', 'FILENAME_CONFIGURATION', 'gID=5', 'configuration', 'Y', 5),
       ('configShipping', 'BOX_CONFIGURATION_SHIPPING_PACKAGING', 'FILENAME_CONFIGURATION', 'gID=7', 'configuration', 'Y', 6),
       ('configProductListing', 'BOX_CONFIGURATION_PRODUCT_LISTING', 'FILENAME_CONFIGURATION', 'gID=8', 'configuration', 'Y', 7),
       ('configStock', 'BOX_CONFIGURATION_STOCK', 'FILENAME_CONFIGURATION', 'gID=9', 'configuration', 'Y', 8),
       ('configLogging', 'BOX_CONFIGURATION_LOGGING', 'FILENAME_CONFIGURATION', 'gID=10', 'configuration', 'Y', 9),
       ('configEmail', 'BOX_CONFIGURATION_EMAIL_OPTIONS', 'FILENAME_CONFIGURATION', 'gID=12', 'configuration', 'Y', 10),
       ('configAttributes', 'BOX_CONFIGURATION_ATTRIBUTE_OPTIONS', 'FILENAME_CONFIGURATION', 'gID=13', 'configuration', 'Y', 11),
       ('configGzipCompression', 'BOX_CONFIGURATION_GZIP_COMPRESSION', 'FILENAME_CONFIGURATION', 'gID=14', 'configuration', 'Y', 12),
       ('configSessions', 'BOX_CONFIGURATION_SESSIONS', 'FILENAME_CONFIGURATION', 'gID=15', 'configuration', 'Y', 13),
       ('configRegulations', 'BOX_CONFIGURATION_REGULATIONS', 'FILENAME_CONFIGURATION', 'gID=11', 'configuration', 'Y', 14),
       ('configGvCoupons', 'BOX_CONFIGURATION_GV_COUPONS', 'FILENAME_CONFIGURATION', 'gID=16', 'configuration', 'Y', 15),
       ('configCreditCards', 'BOX_CONFIGURATION_CREDIT_CARDS', 'FILENAME_CONFIGURATION', 'gID=17', 'configuration', 'Y', 16),
       ('configProductInfo', 'BOX_CONFIGURATION_PRODUCT_INFO', 'FILENAME_CONFIGURATION', 'gID=18', 'configuration', 'Y', 17),
       ('configLayoutSettings', 'BOX_CONFIGURATION_LAYOUT_SETTINGS', 'FILENAME_CONFIGURATION', 'gID=19', 'configuration', 'Y', 18),
       ('configWebsiteMaintenance', 'BOX_CONFIGURATION_WEBSITE_MAINTENANCE', 'FILENAME_CONFIGURATION', 'gID=20', 'configuration', 'Y', 19),
       ('configNewListing', 'BOX_CONFIGURATION_NEW_LISTING', 'FILENAME_CONFIGURATION', 'gID=21', 'configuration', 'Y', 20),
       ('configFeaturedListing', 'BOX_CONFIGURATION_FEATURED_LISTING', 'FILENAME_CONFIGURATION', 'gID=22', 'configuration', 'Y', 21),
       ('configAllListing', 'BOX_CONFIGURATION_ALL_LISTING', 'FILENAME_CONFIGURATION', 'gID=23', 'configuration', 'Y', 22),
       ('configIndexListing', 'BOX_CONFIGURATION_INDEX_LISTING', 'FILENAME_CONFIGURATION', 'gID=24', 'configuration', 'Y', 23),
       ('configDefinePageStatus', 'BOX_CONFIGURATION_DEFINE_PAGE_STATUS', 'FILENAME_CONFIGURATION', 'gID=25', 'configuration', 'Y', 24),
       ('configEzPagesSettings', 'BOX_CONFIGURATION_EZPAGES_SETTINGS', 'FILENAME_CONFIGURATION', 'gID=30', 'configuration', 'Y', 25),
       ('categories', 'BOX_CATALOG_CATEGORY', 'FILENAME_CATEGORIES', '', 'catalog', 'N', 18),
       ('categoriesProductListing', 'BOX_CATALOG_CATEGORIES_PRODUCTS', 'FILENAME_CATEGORY_PRODUCT_LISTING', '', 'catalog', 'Y', 1),
       ('productTypes', 'BOX_CATALOG_PRODUCT_TYPES', 'FILENAME_PRODUCT_TYPES', '', 'catalog', 'Y', 2),
       ('priceManager', 'BOX_CATALOG_PRODUCTS_PRICE_MANAGER', 'FILENAME_PRODUCTS_PRICE_MANAGER', '', 'catalog', 'Y', 3),
       ('optionNames', 'BOX_CATALOG_CATEGORIES_OPTIONS_NAME_MANAGER', 'FILENAME_OPTIONS_NAME_MANAGER', '', 'catalog', 'Y', 4),
       ('optionValues', 'BOX_CATALOG_CATEGORIES_OPTIONS_VALUES_MANAGER', 'FILENAME_OPTIONS_VALUES_MANAGER', '', 'catalog', 'Y', 5),
       ('attributes', 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_CONTROLLER', 'FILENAME_ATTRIBUTES_CONTROLLER', '', 'catalog', 'Y', 6),
       ('downloads', 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_DOWNLOADS_MANAGER', 'FILENAME_DOWNLOADS_MANAGER', '', 'catalog', 'Y', 7),
       ('optionNameSorter', 'BOX_CATALOG_PRODUCT_OPTIONS_NAME', 'FILENAME_PRODUCTS_OPTIONS_NAME', '', 'catalog', 'Y', 8),
       ('optionValueSorter', 'BOX_CATALOG_PRODUCT_OPTIONS_VALUES', 'FILENAME_PRODUCTS_OPTIONS_VALUES', '', 'catalog', 'Y', 9),
       ('manufacturers', 'BOX_CATALOG_MANUFACTURERS', 'FILENAME_MANUFACTURERS', '', 'catalog', 'Y', 10),
       ('reviews', 'BOX_CATALOG_REVIEWS', 'FILENAME_REVIEWS', '', 'catalog', 'Y', 11),
       ('specials', 'BOX_CATALOG_SPECIALS', 'FILENAME_SPECIALS', '', 'catalog', 'Y', 12),
       ('featured', 'BOX_CATALOG_FEATURED', 'FILENAME_FEATURED', '', 'catalog', 'Y', 13),
       ('salemaker', 'BOX_CATALOG_SALEMAKER', 'FILENAME_SALEMAKER', '', 'catalog', 'Y', 14),
       ('productsExpected', 'BOX_CATALOG_PRODUCTS_EXPECTED', 'FILENAME_PRODUCTS_EXPECTED', '', 'catalog', 'Y', 15),
       ('product', 'BOX_CATALOG_PRODUCT', 'FILENAME_PRODUCT', '', 'catalog', 'N', 16),
       ('productsToCategories', 'BOX_CATALOG_PRODUCTS_TO_CATEGORIES', 'FILENAME_PRODUCTS_TO_CATEGORIES', '', 'catalog', 'Y', 17),
       ('payment', 'BOX_MODULES_PAYMENT', 'FILENAME_MODULES', 'set=payment', 'modules', 'Y', 1),
       ('shipping', 'BOX_MODULES_SHIPPING', 'FILENAME_MODULES', 'set=shipping', 'modules', 'Y', 2),
       ('plugins', 'BOX_MODULES_PLUGINS', 'FILENAME_PLUGIN_MANAGER', '', 'modules', 'Y', 4),
       ('orderTotal', 'BOX_MODULES_ORDER_TOTAL', 'FILENAME_MODULES', 'set=ordertotal', 'modules', 'Y', 3),
       ('customers', 'BOX_CUSTOMERS_CUSTOMERS', 'FILENAME_CUSTOMERS', '', 'customers', 'Y', 1),
       ('orders', 'BOX_CUSTOMERS_ORDERS', 'FILENAME_ORDERS', '', 'customers', 'Y', 2),
       ('groupPricing', 'BOX_CUSTOMERS_GROUP_PRICING', 'FILENAME_GROUP_PRICING', '', 'customers', 'Y', 3),
       ('paypal', 'BOX_CUSTOMERS_PAYPAL', 'FILENAME_PAYPAL', '', 'customers', 'Y', 4),
       ('invoice', 'BOX_CUSTOMERS_INVOICE', 'FILENAME_ORDERS_INVOICE', '', 'customers', 'N', 5),
       ('packingslip', 'BOX_CUSTOMERS_PACKING_SLIP', 'FILENAME_ORDERS_PACKINGSLIP', '', 'customers', 'N', 6),
       ('countries', 'BOX_TAXES_COUNTRIES', 'FILENAME_COUNTRIES', '', 'taxes', 'Y', 1),
       ('zones', 'BOX_TAXES_ZONES', 'FILENAME_ZONES', '', 'taxes', 'Y', 2),
       ('geoZones', 'BOX_TAXES_GEO_ZONES', 'FILENAME_GEO_ZONES', '', 'taxes', 'Y', 3),
       ('taxClasses', 'BOX_TAXES_TAX_CLASSES', 'FILENAME_TAX_CLASSES', '', 'taxes', 'Y', 4),
       ('taxRates', 'BOX_TAXES_TAX_RATES', 'FILENAME_TAX_RATES', '', 'taxes', 'Y', 5),
       ('currencies', 'BOX_LOCALIZATION_CURRENCIES', 'FILENAME_CURRENCIES', '', 'localization', 'Y', 1),
       ('languages', 'BOX_LOCALIZATION_LANGUAGES', 'FILENAME_LANGUAGES', '', 'localization', 'Y', 2),
       ('ordersStatus', 'BOX_LOCALIZATION_ORDERS_STATUS', 'FILENAME_ORDERS_STATUS', '', 'localization', 'Y', 3),
       ('reportCustomers', 'BOX_REPORTS_ORDERS_TOTAL', 'FILENAME_STATS_CUSTOMERS', '', 'reports', 'Y', 1),
       ('reportReferrals', 'BOX_REPORTS_CUSTOMERS_REFERRALS', 'FILENAME_STATS_CUSTOMERS_REFERRALS', '', 'reports', 'Y', 2),
       ('reportLowStock', 'BOX_REPORTS_PRODUCTS_LOWSTOCK', 'FILENAME_STATS_PRODUCTS_LOWSTOCK', '', 'reports', 'Y', 3),
       ('reportProductsSold', 'BOX_REPORTS_PRODUCTS_PURCHASED', 'FILENAME_STATS_PRODUCTS_PURCHASED', '', 'reports', 'Y', 4),
       ('reportProductsViewed', 'BOX_REPORTS_PRODUCTS_VIEWED', 'FILENAME_STATS_PRODUCTS_VIEWED', '', 'reports', 'Y', 5),
       ('templateSelect', 'BOX_TOOLS_TEMPLATE_SELECT', 'FILENAME_TEMPLATE_SELECT', '', 'tools', 'Y', 1),
       ('layoutController', 'BOX_TOOLS_LAYOUT_CONTROLLER', 'FILENAME_LAYOUT_CONTROLLER', '', 'tools', 'Y', 2),
       ('banners', 'BOX_TOOLS_BANNER_MANAGER', 'FILENAME_BANNER_MANAGER', '', 'tools', 'Y', 3),
       ('mail', 'BOX_TOOLS_MAIL', 'FILENAME_MAIL', '', 'tools', 'Y', 4),
       ('newsletters', 'BOX_TOOLS_NEWSLETTER_MANAGER', 'FILENAME_NEWSLETTERS', '', 'tools', 'Y', 5),
       ('server', 'BOX_TOOLS_SERVER_INFO', 'FILENAME_SERVER_INFO', '', 'tools', 'Y', 6),
       ('whosOnline', 'BOX_TOOLS_WHOS_ONLINE', 'FILENAME_WHOS_ONLINE', '', 'tools', 'Y', 7),
       ('storeManager', 'BOX_TOOLS_STORE_MANAGER', 'FILENAME_STORE_MANAGER', '', 'tools', 'Y', 9),
       ('developersToolKit', 'BOX_TOOLS_DEVELOPERS_TOOL_KIT', 'FILENAME_DEVELOPERS_TOOL_KIT', '', 'tools', 'Y', 10),
       ('ezpages', 'BOX_TOOLS_EZPAGES', 'FILENAME_EZPAGES_ADMIN', '', 'tools', 'Y', 11),
       ('definePagesEditor', 'BOX_TOOLS_DEFINE_PAGES_EDITOR', 'FILENAME_DEFINE_PAGES_EDITOR', '', 'tools', 'Y', 12),
       ('sqlPatch', 'BOX_TOOLS_SQLPATCH', 'FILENAME_SQLPATCH', '', 'tools', 'Y', 13),
       ('couponAdmin', 'BOX_COUPON_ADMIN', 'FILENAME_COUPON_ADMIN', '', 'gv', 'Y', 1),
       ('couponRestrict', 'BOX_COUPON_RESTRICT', 'FILENAME_COUPON_RESTRICT', '', 'gv', 'N', 1),
       ('gvQueue', 'BOX_GV_ADMIN_QUEUE', 'FILENAME_GV_QUEUE', '', 'gv', 'Y', 2),
       ('gvMail', 'BOX_GV_ADMIN_MAIL', 'FILENAME_GV_MAIL', '', 'gv', 'Y', 3),
       ('gvSent', 'BOX_GV_ADMIN_SENT', 'FILENAME_GV_SENT', '', 'gv', 'Y', 4),
       ('profiles', 'BOX_ADMIN_ACCESS_PROFILES', 'FILENAME_PROFILES', '', 'access', 'Y', 1),
       ('users', 'BOX_ADMIN_ACCESS_USERS', 'FILENAME_USERS', '', 'access', 'Y', 2),
       ('pageRegistration', 'BOX_ADMIN_ACCESS_PAGE_REGISTRATION', 'FILENAME_ADMIN_PAGE_REGISTRATION', '', 'access', 'Y', 3),
       ('adminlogs', 'BOX_ADMIN_ACCESS_LOGS', 'FILENAME_ADMIN_ACTIVITY', '', 'access', 'Y', 4),
       ('recordArtists', 'BOX_CATALOG_RECORD_ARTISTS', 'FILENAME_RECORD_ARTISTS', '', 'extras', 'Y', 1),
       ('recordCompanies', 'BOX_CATALOG_RECORD_COMPANY', 'FILENAME_RECORD_COMPANY', '', 'extras', 'Y', 2),
       ('musicGenre', 'BOX_CATALOG_MUSIC_GENRE', 'FILENAME_MUSIC_GENRE', '', 'extras', 'Y', 3),
       ('mediaManager', 'BOX_CATALOG_MEDIA_MANAGER', 'FILENAME_MEDIA_MANAGER', '', 'extras', 'Y', 4),
       ('mediaTypes', 'BOX_CATALOG_MEDIA_TYPES', 'FILENAME_MEDIA_TYPES', '', 'extras', 'Y', 5);

# Insert a default profile for managing orders, as a built-in example of profile functionality
INSERT INTO admin_profiles (profile_name) values ('Order Processing');
SET @profile_id=last_insert_id();
INSERT INTO admin_pages_to_profiles (profile_id, page_key) VALUES
(@profile_id, 'customers'),
(@profile_id, 'mail'),
(@profile_id, 'orders'),
(@profile_id, 'invoice'),
(@profile_id, 'packingslip'),
(@profile_id, 'paypal'),
(@profile_id, 'currencies'),
(@profile_id, 'reportCustomers'),
(@profile_id, 'reportLowStock'),
(@profile_id, 'reportProductsSold'),
(@profile_id, 'reportProductsViewed'),
(@profile_id, 'reportReferrals'),
(@profile_id, 'gvMail'),
(@profile_id, 'gvQueue'),
(@profile_id, 'gvSent'),
(@profile_id, 'whosOnline');


INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Zen Cart', 'https://www.zen-cart.com', 'banners/zencart_468_60_02.gif', 'Wide-Banners', '', 0, NULL, NULL, '2004-01-11 20:59:12', NULL, 1, 1, 1, 0);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Zen Cart the art of e-commerce', 'https://www.zen-cart.com', 'banners/125zen_logo.gif', 'SideBox-Banners', '', 0, NULL, NULL, '2004-01-11 20:59:12', NULL, 1, 1, 1, 0);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Zen Cart the art of e-commerce', 'https://www.zen-cart.com', 'banners/125x125_zen_logo.gif', 'SideBox-Banners', '', 0, NULL, NULL, '2004-01-11 20:59:12', NULL, 1, 1, 1, 0);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('if you have to think ... you haven''t been Zenned!', 'https://www.zen-cart.com', 'banners/think_anim.gif', 'Wide-Banners', '', 0, NULL, NULL, '2004-01-12 20:53:18', NULL, 1, 1, 1, 0);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Zen Cart the art of e-commerce', 'https://www.zen-cart.com', 'banners/bw_zen_88wide.gif', 'BannersAll', '', 0, NULL, NULL, '2005-05-13 10:54:38', NULL, 1, 1, 1, 10);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Zen Cart', 'https://www.zen-cart.com', '', 'Wide-Banners', '<script><!--//<![CDATA[\r\n   var loc = \'//pan.zen-cart.com/display/group/1/\';\r\n   var rd = Math.floor(Math.random()*99999999999);\r\n   document.write (\"<scr\"+\"ipt src=\'\"+loc);\r\n   document.write (\'?rd=\' + rd);\r\n   document.write (\"\'></scr\"+\"ipt>\");\r\n//]]>--></script>', 0, NULL, NULL, '2004-01-11 20:59:12', NULL, 1, 1, 1, 0);
INSERT INTO banners (banners_title, banners_url, banners_image, banners_group, banners_html_text, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, banners_open_new_windows, banners_on_ssl, banners_sort_order) VALUES ('Credit Card Processing', 'https://www.zen-cart.com/partners/square_promo', 'banners/cardsvcs_468x60.gif', 'Wide-Banners', '', 0, NULL, NULL, '2005-05-13 10:54:38', NULL, 1, 1, 1, 0);


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Name', 'STORE_NAME', '', 'The name of my store', '1', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Owner', 'STORE_OWNER', '', 'The name of my store owner', '1', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Telephone - Customer Service', 'STORE_TELEPHONE_CUSTSERVICE', '', 'Enter a telephone number for customers to reach your Customer Service department. This number may be sent as part of payment transaction details.', '1', '3', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country', 'STORE_COUNTRY', '223', 'The country my store is located in <br /><br /><strong>Note: Please remember to update the store zone.</strong>', '1', '6', 'zen_get_country_name', 'zen_cfg_pull_down_country_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Zone', 'STORE_ZONE', '18', 'The zone my store is located in', '1', '7', 'zen_cfg_get_zone_name', 'zen_cfg_pull_down_zone_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Order', 'EXPECTED_PRODUCTS_SORT', 'desc', 'This is the sort order used in the expected products box.', '1', '8', 'zen_cfg_select_option(array(\'asc\', \'desc\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Field', 'EXPECTED_PRODUCTS_FIELD', 'date_expected', 'The column to sort by in the expected products box.', '1', '9', 'zen_cfg_select_option(array(\'products_name\', \'date_expected\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Switch To Default Language Currency', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 'Automatically switch to the language\'s currency when it is changed', '1', '10', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Language Selector', 'LANGUAGE_DEFAULT_SELECTOR', 'Default', 'Should the default language be based on the Store preferences, or the customer\'s browser settings?<br /><br />Default: Store\'s default settings', '1', '11', 'zen_cfg_select_option(array(\'Default\', \'Browser\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Search-Engine Safe URLs (still in development)', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 'Use search-engine safe urls for all site links', '6', '12', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Cart After Adding Product', 'DISPLAY_CART', 'true', 'Display the shopping cart after adding a product (or return back to their origin)', '1', '14', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Default Search Operator', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', 'Default search operators', '1', '17', 'zen_cfg_select_option(array(\'and\', \'or\'), ', now());
# New setting for zc157, enabling product meta-tags to be conditionally included in search results.
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Include meta-tags in product search?', 'ADVANCED_SEARCH_INCLUDE_METATAGS', 'true', 'Should a product\'s meta-tag keywords and meta-tag descriptions be considered in any <code>advanced_search_results</code> displayed?', 1, 18, now(), 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Address and Phone', 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 'This is the Store Name, Address and Phone used on printable documents and displayed online', '1', '7', 'zen_cfg_textarea(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Category Counts', 'SHOW_COUNTS', 'true', 'Count recursively how many products are in each category', '1', '19', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Tax Decimal Places', 'TAX_DECIMAL_PLACES', '0', 'Pad the tax value this amount of decimal places', '1', '20', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Prices with Tax', 'DISPLAY_PRICE_WITH_TAX', 'false', 'Display prices with tax included (true) or add the tax at the end (false)', '1', '21', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Prices with Tax in Admin', 'DISPLAY_PRICE_WITH_TAX_ADMIN', 'false', 'Display prices with tax included (true) or add the tax at the end (false) in Admin(Invoices)', '1', '21', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Basis of Product Tax', 'STORE_PRODUCT_TAX_BASIS', 'Shipping', 'On what basis is Product Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '1', '21', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Basis of Shipping Tax', 'STORE_SHIPPING_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone - Can be overridden by correctly written Shipping Module', '1', '21', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Sales Tax Display Status', 'STORE_TAX_DISPLAY_STATUS', '0', 'Always show Sales Tax even when amount is $0.00?<br />0= Off<br />1= On', '1', '21', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Split Tax Lines', 'SHOW_SPLIT_TAX_CHECKOUT', 'false', 'If multiple tax rates apply, show each rate as a separate line at checkout', '1', '22', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('PA-DSS Admin Session Timeout Enforced?', 'PADSS_ADMIN_SESSION_TIMEOUT_ENFORCED', '1', 'PA-DSS Compliance requires that any Admin login sessions expire after 15 minutes of inactivity. <strong>Disabling this makes your site NON-COMPLIANT with PA-DSS rules, thus invalidating any certification.</strong>', 1, 30, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Non-Compliant\'), array(\'id\'=>\'1\', \'text\'=>\'On\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('PA-DSS Strong Password Rules Enforced?', 'PADSS_PWD_EXPIRY_ENFORCED', '1', 'PA-DSS Compliance requires that admin passwords must be changed after 90 days and cannot re-use the last 4 passwords. <strong>Disabling this makes your site NON-COMPLIANT with PA-DSS rules, thus invalidating any certification.</strong>', 1, 30, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Non-Compliant\'), array(\'id\'=>\'1\', \'text\'=>\'On\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('PA-DSS Ajax Checkout?', 'PADSS_AJAX_CHECKOUT', '1', 'PA-DSS Compliance requires that for some inbuilt payment methods, that we use ajax to draw the checkout confirmation screen. While this will only happen if one of those payment methods is actually present, some people may want the traditional checkout flow <strong>Disabling this makes your site NON-COMPLIANT with PA-DSS rules, thus invalidating any certification.</strong>', 1, 30, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Non-Compliant\'), array(\'id\'=>\'1\', \'text\'=>\'On\')),');

# Admin storefront login configuration.  Using INSERT IGNORE followed by an UPDATE in consideration of shops with EMP already installed.
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer <em>Place Order</em>: Single Admin ID', 'EMP_LOGIN_ADMIN_ID', '0', 'Identify the ID number of an admin that is permitted to use the <em>Place Order</em> feature on the customers list, regardless of their assigned admin-profile. Set the value to 0 to disable the <em>Single Admin ID</em> feature.', 1, 300, now());
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Single Admin ID', configuration_description = 'Identify the ID number of an admin that is permitted to use the <em>Place Order</em> feature on the customers list, regardless of their assigned admin-profile. Set the value to 0 to disable the <em>Single Admin ID</em> feature.' WHERE configuration_key = 'EMP_LOGIN_ADMIN_ID' LIMIT 1;

INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Customer <em>Place Order</em>: Passwordless Login', 'EMP_LOGIN_AUTOMATIC', 'false', 'Login directly to store without entering credentials', 1, 302, now(), 'zen_cfg_select_option(array(\'true\', \'false\'),');
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Passwordless Login', configuration_description = 'Login directly to store without entering credentials' WHERE configuration_key = 'EMP_LOGIN_AUTOMATIC' LIMIT 1;

INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer <em>Place Order</em>: Admin Profiles', 'EMP_LOGIN_ADMIN_PROFILE_ID', '0', 'Identify the admin <em>User Profile IDs</em> that are permitted to use the <em>Place Order</em> feature on the customers list &mdash; all admins that are in these profiles are permitted. Enter the value as a comma-separated list (intervening blanks are OK) of Admin Profile IDs, e.g. <b>1, 2, 3</b>. Set the value to 0 to disable the <em>Admin Profiles</em> feature.<br><br><b>Default: 0</b>', 1, 301, now());
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Admin Profiles', configuration_description = 'Identify the admin <em>User Profile IDs</em> that are permitted to use the <em>Place Order</em> feature on the customers list &mdash; all admins that are in these profiles are permitted. Enter the value as a comma-separated list (intervening blanks are OK) of Admin Profile IDs, e.g. <b>1, 2, 3</b>. Set the value to 0 to disable the <em>Admin Profiles</em> feature.<br><br><b>Default: 0 </b>' WHERE configuration_key = 'EMP_LOGIN_ADMIN_PROFILE_ID' LIMIT 1;

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) VALUES ('Admin Session Time Out in Seconds', 'SESSION_TIMEOUT_ADMIN', '900', 'Enter the time in seconds.<br />Max allowed is 900 for PCI Compliance Reasons.<br /> Default=900<br />Example: 900= 15 min <br /><br />Note: Too few seconds can result in timeout issues when adding/editing products', 1, 40, NULL, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Admin Set max_execution_time for processes', 'GLOBAL_SET_TIME_LIMIT', '60', 'Enter the time in seconds for how long the max_execution_time of processes should be. Default=60<br />Example: 60= 1 minute<br /><br />Note: Changing the time limit is only needed if you are having problems with the execution time of a process', 1, 42, NULL, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show if version update available', 'SHOW_VERSION_UPDATE_IN_HEADER', 'true', 'Automatically check to see if a new version of Zen Cart is available. Enabling this can sometimes slow down the loading of Admin pages. (Displayed on main Index page after login, and Server Info page.)', 1, 44, 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Status', 'STORE_STATUS', '0', 'What is your Store Status<br />0= Normal Store<br />1= Showcase no prices<br />2= Showcase with prices', '1', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Server Uptime', 'DISPLAY_SERVER_UPTIME', 'true', 'Displaying Server uptime can cause entries in error logs on some servers. (true = Display, false = don\'t display)', 1, 46, '2003-11-08 20:24:47', '0001-01-01 00:00:00', '', 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Missing Page Check', 'MISSING_PAGE_CHECK', 'Page Not Found', 'Zen Cart can check for missing pages in the URL and redirect to Index page. For debugging you may want to turn this off. <br /><br /><strong>Default=On</strong><br />On = Send missing pages to \'index\'<br />Off = Don\'t check for missing pages<br />Page Not Found = display the Page-Not-Found page', 1, 48, '2003-11-08 20:24:47', '0001-01-01 00:00:00', '', 'zen_cfg_select_option(array(\'On\', \'Off\', \'Page Not Found\'),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('cURL Proxy Status', 'CURL_PROXY_REQUIRED', 'False', 'Does your host require that you use a proxy for cURL communication?', 6, '50', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('cURL Proxy Address', 'CURL_PROXY_SERVER_DETAILS', '', 'If you have a hosting service that requires use of a proxy to talk to external sites via cURL, enter their proxy address here.<br />format: address:port<br />ie: 127.0.0.1:3128', 6, 51, NULL, now(), NULL, NULL);


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('HTML Editor', 'HTML_EDITOR_PREFERENCE', 'NONE', 'Please select the HTML/Rich-Text editor you wish to use for composing Admin-related emails, newsletters, and product descriptions', '1', '110', 'zen_cfg_pull_down_htmleditors(', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Default for Notify Customer on Order Status Update?', 'NOTIFY_CUSTOMER_DEFAULT', '1', 'Set the default email behavior on status update to Send Email, Do Not Send Email, or Hide Update.', 1, 120, now(), now(), NULL, 'zen_cfg_select_drop_down(array( array(\'id\'=>\'1\', \'text\'=>\'Email\'), array(\'id\'=>\'0\', \'text\'=>\'No Email\'), array(\'id\'=>\'-1\', \'text\'=>\'Hide\')),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Category Counts - Admin', 'SHOW_COUNTS_ADMIN', 'true', 'Show Category Counts in Admin?', '1', '19', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show linked status for categories', 'SHOW_CATEGORY_PRODUCTS_LINKED_STATUS', 'true', 'Show Category products linked status?', '1', '19', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Currency Conversion Ratio', 'CURRENCY_UPLIFT_RATIO', '1.05', 'When auto-updating currencies, what "uplift" ratio should be used to calculate the exchange rate used by your store?<br />ie: the bank rate is obtained from the currency-exchange servers; how much extra do you want to charge in order to make up the difference between the bank rate and the consumer rate?<br /><br /><strong>Default: 1.05 </strong><br />This will cause the published bank rate to be multiplied by 1.05 to set the currency rates in your store.', 1, 55, NULL, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Primary Source', 'CURRENCY_SERVER_PRIMARY', 'ecb', 'Where to request external currency updates from (Primary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Secondary Source', 'CURRENCY_SERVER_BACKUP', 'boc', 'Where to request external currency updates from (Secondary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('First Name', 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', '{"error":"TEXT_MIN_ADMIN_FIRST_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of first name', '2', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Last Name', 'ENTRY_LAST_NAME_MIN_LENGTH', '2', '{"error":"TEXT_MIN_ADMIN_LAST_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of last name', '2', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Date of Birth', 'ENTRY_DOB_MIN_LENGTH', '10', '{"error":"TEXT_MIN_ADMIN_DOB_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of date of birth', '2', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail Address', 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', '{"error":"TEXT_MIN_ADMIN_EMAIL_ADDRESS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of e-mail address', '2', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Street Address', 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '5', '{"error":"TEXT_MIN_ADMIN_STREET_ADDRESS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of street address', '2', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Company', 'ENTRY_COMPANY_MIN_LENGTH', '0', '{"error":"TEXT_MIN_ADMIN_COMPANY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of company name', '2', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Post Code', 'ENTRY_POSTCODE_MIN_LENGTH', '4', '{"error":"TEXT_MIN_ADMIN_POSTCODE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of post code', '2', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('City', 'ENTRY_CITY_MIN_LENGTH', '2', '{"error":"TEXT_MIN_ADMIN_CITY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of city', '2', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('State', 'ENTRY_STATE_MIN_LENGTH', '2', '{"error":"TEXT_MIN_ADMIN_STATE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of state', '2', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Telephone Number', 'ENTRY_TELEPHONE_MIN_LENGTH', '3', '{"error":"TEXT_MIN_ADMIN_TELEPHONE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of telephone number', '2', '10', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Password', 'ENTRY_PASSWORD_MIN_LENGTH', '7', '{"error":"TEXT_MIN_ADMIN_PASSWORD_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of password', '2', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Owner Name', 'CC_OWNER_MIN_LENGTH', '3', '{"error":"TEXT_MIN_ADMIN_CC_OWNER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of credit card owner name', '2', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Number', 'CC_NUMBER_MIN_LENGTH', '10', '{"error":"TEXT_MIN_ADMIN_CC_NUMBER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of credit card number', '2', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card CVV Number', 'CC_CVV_MIN_LENGTH', '3', '{"error":"TEXT_MIN_ADMIN_CC_CVV_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of credit card CVV number', '2', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Review Text', 'REVIEW_TEXT_MIN_LENGTH', '50', '{"error":"TEXT_MIN_ADMIN_REVIEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of product review text', '2', '14', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers', 'MIN_DISPLAY_BESTSELLERS', '1', '{"error":"TEXT_MIN_ADMIN_DISPLAY_BESTSELLERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum number of best sellers to display', '2', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased Products', 'MIN_DISPLAY_ALSO_PURCHASED', '1', '{"error":"TEXT_MIN_ADMIN_DISPLAY_ALSO_PURCHASED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum number of products to display in the \'Also Purchased\' box', '2', '16', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Nick Name', 'ENTRY_NICK_MIN_LENGTH', '3', '{"error":"TEXT_MIN_ADMIN_ENTRY_NICK_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Minimum length of Nick Name', '2', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Admin Usernames', 'ADMIN_NAME_MINIMUM_LENGTH', '4', '{"error":"TEXT_MIN_ADMIN_USER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":4}}}', 'Minimum length of admin usernames (must be 4 or more)', '2', '18', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Address Book Entries', 'MAX_ADDRESS_BOOK_ENTRIES', '5', '{"error":"TEXT_MAX_ADMIN_ADDRESS_BOOK_ENTRIES_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum address book entries a customer is allowed to have', '3', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Admin Search Results Per Page', 'MAX_DISPLAY_SEARCH_RESULTS', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products to list on an Admin search result page', '3', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Prev/Next Navigation Page Links (Desktop)', 'MAX_DISPLAY_PAGE_LINKS', '5', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PAGE_LINKS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of numbered pagination links to display.', '3', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Prev/Next Navigation Page Links (Mobile)', 'MAX_DISPLAY_PAGE_LINKS_MOBILE', '3', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PAGE_LINKS_MOBILE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of numbered pagination links to display on Mobile devices (assuming your template supports mobile-specific settings)', '3', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products on Special ', 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SPECIAL_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products on special to display', '3', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Module', 'MAX_DISPLAY_NEW_PRODUCTS', '9', '{"error":"TEXT_MAX_ADMIN_DISPLAY_NEW_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of new products to display in a category', '3', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Upcoming Products ', 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_UPCOMING_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of \'upcoming\' products to display', '3', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Manufacturers List - Scroll Box Size/Style', 'MAX_MANUFACTURERS_LIST', '3', '{"error":"TEXT_MAX_ADMIN_MANUFACTURERS_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of manufacturers names to be displayed in the scroll box window. Setting this to 1 or 0 will display a dropdown list.', '3', '7', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Manufacturers List - Verify Product Exist', 'PRODUCTS_MANUFACTURERS_STATUS', '1', 'Verify that at least 1 product exists and is active for the manufacturer name to show<br /><br />Note: When this feature is ON it can produce slower results on sites with a large number of products and/or manufacturers<br />0= off 1= on', 3, 7, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Music Genre List - Scroll Box Size/Style', 'MAX_MUSIC_GENRES_LIST', '3', '{"error":"TEXT_MAX_ADMIN_MUSIC_GENRES_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of music genre names to be displayed in the scroll box window. Setting this to 1 or 0 will display a dropdown list.', '3', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Record Company List - Scroll Box Size/Style', 'MAX_RECORD_COMPANY_LIST', '3', '{"error":"TEXT_MAX_ADMIN_RECORD_COMPANY_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of record company names to be displayed in the scroll box window. Setting this to 1 or 0 will display a dropdown list.', '3', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Length of Record Company Name', 'MAX_DISPLAY_RECORD_COMPANY_NAME_LEN', '15', '{"error":"TEXT_MAX_ADMIN_RECORD_COMPANY_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Used in record companies box; maximum length of record company name to display. Longer names will be truncated.', '3', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Length of Music Genre Name', 'MAX_DISPLAY_MUSIC_GENRES_NAME_LEN', '15', '{"error":"TEXT_MAX_ADMIN_MUSIC_GENRES_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Used in music genres box; maximum length of music genre name to display. Longer names will be truncated.', '3', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Length of Manufacturers Name', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '15', '{"error":"TEXT_MAX_ADMIN_DISPLAY_MANUFACTURERS_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Used in manufacturers box; maximum length of manufacturers name to display. Longer names will be truncated.', '3', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Product Reviews Per Page', 'MAX_DISPLAY_NEW_REVIEWS', '6', '{"error":"TEXT_MAX_ADMIN_DISPLAY_NEW_REVIEWS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of new reviews to display on each page', '3', '9', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Random Product Reviews for SideBox', 'MAX_RANDOM_SELECT_REVIEWS', '1', '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_REVIEWS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of random product REVIEWS to rotate in the sidebox<br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?', '3', '10', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Random New Products for SideBox', 'MAX_RANDOM_SELECT_NEW', '3', '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_NEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of random NEW products to rotate in the sidebox<br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?', '3', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Random Products On Special for SideBox', 'MAX_RANDOM_SELECT_SPECIALS', '2', '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_SPECIALS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of random products on SPECIAL to rotate in the sidebox<br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?', '3', '12', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Categories To List Per Row', 'MAX_DISPLAY_CATEGORIES_PER_ROW', '3', '{"error":"TEXT_MAX_ADMIN_DISPLAY_CATEGORIES_PER_ROW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'How many categories to list per row', '3', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Listing- Number Per Page', 'MAX_DISPLAY_PRODUCTS_NEW', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_NEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of new products listed per page', '3', '14', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers For Box', 'MAX_DISPLAY_BESTSELLERS', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_BESTSELLERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of best sellers to display in box', '3', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased Products', 'MAX_DISPLAY_ALSO_PURCHASED', '6', '{"error":"TEXT_MAX_ADMIN_DISPLAY_ALSO_PURCHASED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products to display in the \'Also Purchased\' box', '3', '16', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Order History Box', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products to display in the order history box', '3', '17', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer Order History List Per Page', 'MAX_DISPLAY_ORDER_HISTORY', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_ORDER_HISTORY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of orders to display in the order history list in \'My Account\'', '3', '18', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Customers on Customers Page', 'MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_CUSTOMER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', '', 3, 19, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Orders on Orders Page', 'MAX_DISPLAY_SEARCH_RESULTS_ORDERS', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_ORDERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', '', 3, 20, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Products on Reports', 'MAX_DISPLAY_SEARCH_RESULTS_REPORTS', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_RESULTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', '', 3, 21, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Categories Products Display List', 'MAX_DISPLAY_RESULTS_CATEGORIES', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_RESULTS_CATEGORIES_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products to list per screen', 3, 22, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Listing- Number Per Page', 'MAX_DISPLAY_PRODUCTS_LISTING', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_LISTING_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Number of Products to list per page on main page', '3', '30', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Attributes - Option Names and Values Display', 'MAX_ROW_LISTS_OPTIONS', '10', '{"error":"TEXT_MAX_ADMIN_ROW_LISTS_OPTIONS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum number of option names and values to display in the products attributes page', '3', '24', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Attributes - Attributes Controller Display', 'MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER', '30', '{"error":"TEXT_MAX_ADMIN_ROW_LISTS_ATTRIBUTES_CONTROLLER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum number of attributes to display in the Attributes Controller page', '3', '25', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Attributes - Downloads Manager Display', 'MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER', '30', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum number of attributes downloads to display in the Downloads Manager page', '3', '26', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Featured Products - Number to Display Admin', 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of featured products to list per screen - Admin', 3, 27, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Featured Products - Main Page', 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED', '9', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of featured products to list on main page', 3, 28, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Featured Products Page', 'MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of featured products to list per screen', 3, 29, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Random Featured Products for SideBox', 'MAX_RANDOM_SELECT_FEATURED_PRODUCTS', '2', '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_FEATURED_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of random FEATURED products to rotate in the sidebox<br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?', '3', '30', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Specials Products - Main Page', 'MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX', '9', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SPECIAL_PRODUCTS_INDEX_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of special products to list on main page', 3, 31, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('New Product Listing - Limited to ...', 'SHOW_NEW_PRODUCTS_LIMIT', '0', '{"error":"TEXT_MAX_ADMIN_SHOW_NEW_PRODUCTS_LIMIT_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Limit the New Product Listing to<br />0= All Products<br />1= Current Month<br />7= 7 Days<br />14= 14 Days<br />30= 30 Days<br />60= 60 Days<br />90= 90 Days<br />120= 120 Days', '3', '40', 'zen_cfg_select_option(array(\'0\', \'1\', \'7\', \'14\', \'30\', \'60\', \'90\', \'120\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Products All Page', 'MAX_DISPLAY_PRODUCTS_ALL', '10', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_ALL_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of products to list per screen', 3, 45, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display of Language Flags in Language Side Box', 'MAX_LANGUAGE_FLAGS_COLUMNS', '3', '{"error":"TEXT_MAX_ADMIN_LANGUAGE_FLAGS_COLUMNS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Number of Language Flags per Row', 3, 50, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum File Upload Size', 'MAX_FILE_UPLOAD_SIZE', '2048000', '{"error":"TEXT_MAX_ADMIN_FILE_UPLOAD_SIZE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'What is the Maximum file size for uploads?<br />Default= 2048000', 3, 60, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Orders Detail Display on Admin Orders Listing', 'MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING', '0', '{"error":"TEXT_MAX_ADMIN_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum number of Order Details<br />0 = Unlimited', 3, 65, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum PayPal IPN Display on Admin Listing', 'MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum number of PayPal IPN Listings in Admin<br />Default is 20', 3, 66, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display Columns Products to Multiple Categories Manager', 'MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS', '3', '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Display Columns Products to Multiple Categories Manager<br />3 = Default', 3, 70, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display EZ-Pages', 'MAX_DISPLAY_SEARCH_RESULTS_EZPAGE', '20', '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_EZPAGE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Display EZ-Pages<br />20 = Default', 3, 71, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Preview', 'MAX_PREVIEW', '100', '{"error":"TEXT_MAX_PREVIEW","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Preview length<br />100 = Default', 3, 80, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Width', 'SMALL_IMAGE_WIDTH', '100', 'The pixel width of small images', '4', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Height', 'SMALL_IMAGE_HEIGHT', '80', 'The pixel height of small images', '4', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Width - Admin', 'HEADING_IMAGE_WIDTH', '57', 'The pixel width of heading images in the Admin<br />NOTE: Presently, this adjusts the spacing on the pages in the Admin Pages or could be used to add images to the heading in the Admin', '4', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Height - Admin', 'HEADING_IMAGE_HEIGHT', '40', 'The pixel height of heading images in the Admin<br />NOTE: Presently, this adjusts the spacing on the pages in the Admin Pages or could be used to add images to the heading in the Admin', '4', '4', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Width', 'SUBCATEGORY_IMAGE_WIDTH', '100', 'The pixel width of subcategory images', '4', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Height', 'SUBCATEGORY_IMAGE_HEIGHT', '57', 'The pixel height of subcategory images', '4', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Calculate Image Size', 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 'Calculate the size of images?', '4', '7', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image Required', 'IMAGE_REQUIRED', 'true', 'Enable to display broken images. Good for development.', '4', '8', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image - Shopping Cart Status', 'IMAGE_SHOPPING_CART_STATUS', '1', 'Show product image in the shopping cart?<br />0= off 1= on', 4, 9, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Shopping Cart Width', 'IMAGE_SHOPPING_CART_WIDTH', '50', 'Default = 50', 4, 10, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Shopping Cart Height', 'IMAGE_SHOPPING_CART_HEIGHT', '40', 'Default = 40', 4, 11, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Category Icon Image Width - Product Info Pages', 'CATEGORY_ICON_IMAGE_WIDTH', '57', 'The pixel width of Category Icon heading images for Product Info Pages', '4', '13', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Category Icon Image Height - Product Info Pages', 'CATEGORY_ICON_IMAGE_HEIGHT', '40', 'The pixel height of Category Icon heading images for Product Info Pages', '4', '14', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Top Subcategory Image Width', 'SUBCATEGORY_IMAGE_TOP_WIDTH', '150', 'The pixel width of Top subcategory images<br />Top subcategory is when the Category contains subcategories', '4', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Top Subcategory Image Height', 'SUBCATEGORY_IMAGE_TOP_HEIGHT', '85', 'The pixel height of Top subcategory images<br />Top subcategory is when the Category contains subcategories', '4', '16', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Image Width', 'MEDIUM_IMAGE_WIDTH', '150', 'The pixel width of Product Info images', '4', '20', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Image Height', 'MEDIUM_IMAGE_HEIGHT', '120', 'The pixel height of Product Info images', '4', '21', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Image Medium Suffix', 'IMAGE_SUFFIX_MEDIUM', '_MED', 'Product Info Medium Image Suffix<br />Default = _MED', '4', '22', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Image Large Suffix', 'IMAGE_SUFFIX_LARGE', '_LRG', 'Product Info Large Image Suffix<br />Default = _LRG', '4', '23', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Number of Additional Images per Row', 'IMAGES_AUTO_ADDED', '3', 'Product Info - Enter the number of additional images to display per row<br />Default = 3', '4', '30', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product Listing Width', 'IMAGE_PRODUCT_LISTING_WIDTH', '100', 'Default = 100', 4, 40, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product Listing Height', 'IMAGE_PRODUCT_LISTING_HEIGHT', '80', 'Default = 80', 4, 41, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product New Listing Width', 'IMAGE_PRODUCT_NEW_LISTING_WIDTH', '100', 'Default = 100', 4, 42, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product New Listing Height', 'IMAGE_PRODUCT_NEW_LISTING_HEIGHT', '80', 'Default = 80', 4, 43, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - New Products Width', 'IMAGE_PRODUCT_NEW_WIDTH', '100', 'Default = 100', 4, 44, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - New Products Height', 'IMAGE_PRODUCT_NEW_HEIGHT', '80', 'Default = 80', 4, 45, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Featured Products Width', 'IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH', '100', 'Default = 100', 4, 46, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Featured Products Height', 'IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT', '80', 'Default = 80', 4, 47, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product All Listing Width', 'IMAGE_PRODUCT_ALL_LISTING_WIDTH', '100', 'Default = 100', 4, 48, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Image - Product All Listing Height', 'IMAGE_PRODUCT_ALL_LISTING_HEIGHT', '80', 'Default = 80', 4, 49, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Image - No Image Status', 'PRODUCTS_IMAGE_NO_IMAGE_STATUS', '1', 'Use automatic No Image when none is added to product<br />0= off<br />1= On', '4', '60', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Image - No Image picture', 'PRODUCTS_IMAGE_NO_IMAGE', 'no_picture.gif', 'Use automatic No Image when none is added to product<br />Default = no_picture.gif', '4', '61', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image - Use Proportional Images on Products and Categories', 'PROPORTIONAL_IMAGES_STATUS', '1', 'Use Proportional Images on Products and Categories?<br /><br />NOTE: Do not use 0 height or width settings for Proportion Images<br />0= off 1= on', 4, 75, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Email Salutation', 'ACCOUNT_GENDER', 'false', 'Display salutation choice during account creation and with account information', '5', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Date of Birth', 'ACCOUNT_DOB', 'false', 'Display date of birth field during account creation and with account information<br />NOTE: Set Minimum Value Date of Birth to blank for not required<br />Set Minimum Value Date of Birth > 0 to require', '5', '2', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Company', 'ACCOUNT_COMPANY', 'true', 'Display company field during account creation and with account information', '5', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Address Line 2', 'ACCOUNT_SUBURB', 'true', 'Display address line 2 field during account creation and with account information', '5', '4', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('State', 'ACCOUNT_STATE', 'true', 'Display state field during account creation and with account information', '5', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('State - Always display as pulldown?', 'ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN', 'false', 'When state field is displayed, should it always be a pulldown menu?', 5, '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Create Account Default Country ID', 'SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY', '223', 'Set Create Account Default Country ID to:<br />Default is 223', '5', '6', 'zen_get_country_name', 'zen_cfg_pull_down_country_list_none(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Fax Number', 'ACCOUNT_FAX_NUMBER', 'true', 'Display fax number field during account creation and with account information', '5', '10', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Newsletter Checkbox', 'ACCOUNT_NEWSLETTER_STATUS', '1', 'Show Newsletter Checkbox<br />0= off<br />1= Display Unchecked<br />2= Display Checked<br /><strong>Note: Defaulting this to accepted may be in violation of certain regulations for your state or country</strong>', 5, 45, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Default Email Preference', 'ACCOUNT_EMAIL_PREFERENCE', '0', 'Set the Default Customer Default Email Preference<br />0= Text<br />1= HTML<br />', 5, 46, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Product Notification Status', 'CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS', '1', 'Customer should be asked about product notifications after checkout success and in account preferences<br />0= Never ask<br />1= Ask (ignored on checkout if has already selected global notifications)<br /><br />Note: Sidebox must be turned off separately', '5', '50', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Shop Status - View Shop and Prices', 'CUSTOMERS_APPROVAL', '0', 'Customer must be approved to shop<br />0= Not required<br />1= Must login to browse<br />2= May browse but no prices unless logged in<br />3= Showroom Only<br /><br />It is recommended that Option 2 be used for the purposes of Spiders if you wish customers to login to see prices.', '5', '55', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

#customer approval to shop
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Approval Status - Authorization Pending', 'CUSTOMERS_APPROVAL_AUTHORIZATION', '0', 'Customer must be Authorized to shop<br />0= Not required<br />1= Must be Authorized to Browse<br />2= May browse but no prices unless Authorized<br />3= Customer May Browse and May see Prices but Must be Authorized to Buy<br /><br />It is recommended that Option 2 or 3 be used for the purposes of Spiders', '5', '65', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: filename', 'CUSTOMERS_AUTHORIZATION_FILENAME', 'customers_authorization', 'Customer Authorization filename<br />Note: Do not include the extension<br />Default=customers_authorization', '5', '66', '', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: Hide Header', 'CUSTOMERS_AUTHORIZATION_HEADER_OFF', 'false', 'Customer Authorization: Hide Header <br />(true=hide false=show)', '5', '67', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: Hide Column Left', 'CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF', 'false', 'Customer Authorization: Hide Column Left <br />(true=hide false=show)', '5', '68', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: Hide Column Right', 'CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF', 'false', 'Customer Authorization: Hide Column Right <br />(true=hide false=show)', '5', '69', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: Hide Footer', 'CUSTOMERS_AUTHORIZATION_FOOTER_OFF', 'false', 'Customer Authorization: Hide Footer <br />(true=hide false=show)', '5', '70', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Customer Authorization: Hide Prices', 'CUSTOMERS_AUTHORIZATION_PRICES_OFF', 'false', 'Customer Authorization: Hide Prices <br />(true=hide false=show)', '5', '71', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customers Referral Status', 'CUSTOMERS_REFERRAL_STATUS', '0', 'Customers Referral Code is created from<br />0= Off<br />1= 1st Discount Coupon Code used<br />2= Customer can add during create account or edit if blank<br /><br />NOTE: Once the Customers Referral Code has been set it can only be changed in the Admin Customer', '5', '80', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_PAYMENT_INSTALLED', 'freecharger.php;moneyorder.php', 'List of payment module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: freecharger.php;cod.php;paypal.php)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_shipping.php;ot_coupon.php;ot_group_pricing.php;ot_tax.php;ot_loworderfee.php;ot_gv.php;ot_total.php', 'List of order_total module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_SHIPPING_INSTALLED', 'flat.php;freeshipper.php;item.php;storepickup.php', 'List of shipping module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ups.php;flat.php;item.php)', '6', '0', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Enable Free Shipping', 'MODULE_SHIPPING_FREESHIPPER_STATUS', 'True', 'Do you want to offer Free shipping?', 6, 0, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Pickup Locations', 'MODULE_SHIPPING_STOREPICKUP_LOCATIONS_LIST', 'Walk In', 'Enter a list of locations, separated by semicolons (;).<br>Optionally you may specify a fee/surcharge for each location by adding a comma and an amount. If no amount is specified, then the generic Shipping Cost amount from the next setting will be applied.<br><br>Examples:<br>121 Main Street;20 Church Street<br>Sunnyside,4.00;Lee Park,5.00;High Street,0.00<br>Dallas;Tulsa,5.00;Phoenix,0.00<br>For multilanguage use, see the define-statement in the language file for this module.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Free Shipping Cost', 'MODULE_SHIPPING_FREESHIPPER_COST', '0.00', 'What is the Shipping cost?', 6, 6, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Handling Fee', 'MODULE_SHIPPING_FREESHIPPER_HANDLING', '0', 'Handling fee for this shipping method.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_SHIPPING_FREESHIPPER_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', 6, 0, now(), 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FREESHIPPER_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', 6, 0, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER', '0', 'Sort order of display.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Enable Store Pickup Shipping', 'MODULE_SHIPPING_STOREPICKUP_STATUS', 'True', 'Do you want to offer In Store rate shipping?', 6, 0, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Shipping Cost', 'MODULE_SHIPPING_STOREPICKUP_COST', '0.00', 'The shipping cost for all orders using this shipping method.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_SHIPPING_STOREPICKUP_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', 6, 0, now(), 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Basis', 'MODULE_SHIPPING_STOREPICKUP_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', 6, 0, now(), NULL, 'zen_cfg_select_option(array(\'Shipping\', \'Billing\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Shipping Zone', 'MODULE_SHIPPING_STOREPICKUP_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', 6, 0, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_SHIPPING_STOREPICKUP_SORT_ORDER', '0', 'Sort order of display.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Enable Item Shipping', 'MODULE_SHIPPING_ITEM_STATUS', 'True', 'Do you want to offer per item rate shipping?', 6, 0, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Shipping Cost', 'MODULE_SHIPPING_ITEM_COST', '2.50', 'The shipping cost will be multiplied by the number of items in an order that uses this shipping method.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Handling Fee', 'MODULE_SHIPPING_ITEM_HANDLING', '0', 'Handling fee for this shipping method.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_SHIPPING_ITEM_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', 6, 0, now(), 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Basis', 'MODULE_SHIPPING_ITEM_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', 6, 0, now(), NULL, 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Shipping Zone', 'MODULE_SHIPPING_ITEM_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', 6, 0, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_SHIPPING_ITEM_SORT_ORDER', '0', 'Sort order of display.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Enable Free Charge Module', 'MODULE_PAYMENT_FREECHARGER_STATUS', 'True', 'Do you want to accept Free Charge payments?', 6, 1, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort order of display.', 'MODULE_PAYMENT_FREECHARGER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Payment Zone', 'MODULE_PAYMENT_FREECHARGER_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 2, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Set Order Status', 'MODULE_PAYMENT_FREECHARGER_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module to this value', 6, 0, now(), 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Enable Check/Money Order Module', 'MODULE_PAYMENT_MONEYORDER_STATUS', 'True', 'Do you want to accept Check/Money Order payments?', 6, 1, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Make Payable to:', 'MODULE_PAYMENT_MONEYORDER_PAYTO', 'the Store Owner/Website Name', 'Who should payments be made payable to?', 6, 1, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort order of display.', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 0, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Payment Zone', 'MODULE_PAYMENT_MONEYORDER_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 2, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Set Order Status', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', 6, 0, now(), 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'false', 'Include Tax value in amount before discount calculation?', 6, 6, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'true', '', 6, 1, now(), NULL, 'zen_cfg_select_option(array(\'true\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', '290', 'Sort order of display.', 6, 2, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping value in amount before discount calculation?', 6, 5, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'Standard', 'Re-Calculate Tax', 6, 7, now(), NULL, 'zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS', '0', 'Use the following tax class when treating Group Discount as Credit Note.', 6, 0, now(), 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Flat Shipping', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Tax Basis', 'MODULE_SHIPPING_FLAT_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FLAT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Currency', 'DEFAULT_CURRENCY', 'USD', 'Default Currency', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Language', 'DEFAULT_LANGUAGE', 'en', 'Default Language', '6', '0', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Order Status For New Orders', 'DEFAULT_ORDERS_STATUS_ID', '1', 'When a new order is created, this order status will be assigned to it.', '6', '0', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Admin configuration_key shows', 'ADMIN_CONFIGURATION_KEY_ON', '0', 'Manually switch to value of 1 to see the configuration_key name in configuration displays', '6', '0', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country of Origin', 'SHIPPING_ORIGIN_COUNTRY', '223', 'Select the country of origin to be used in shipping quotes.', '7', '1', 'zen_get_country_name', 'zen_cfg_pull_down_country_list(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Postal Code', 'SHIPPING_ORIGIN_ZIP', 'NONE', 'Enter the Postal Code (ZIP) of the Store to be used in shipping quotes. NOTE: For USA zip codes, only use your 5 digit zip code.', '7', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Enter the Maximum Package Weight you will ship', 'SHIPPING_MAX_WEIGHT', '50', 'Carriers have a max weight limit for a single package. This is a common one for all.', '7', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Package Tare Small to Medium - added percentage:weight', 'SHIPPING_BOX_WEIGHT', '0:3', 'What is the weight of typical packaging of small to medium packages?<br />Example: 10% + 1lb 10:1<br />10% + 0lbs 10:0<br />0% + 5lbs 0:5<br />0% + 0lbs 0:0', '7', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Larger packages - added packaging percentage:weight', 'SHIPPING_BOX_PADDING', '10:0', 'What is the weight of typical packaging for Large packages?<br />Example: 10% + 1lb 10:1<br />10% + 0lbs 10:0<br />0% + 5lbs 0:5<br />0% + 0lbs 0:0', '7', '5', now());

# moved to product_types
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Virtual Default Status - Skip Shipping Address', 'PRODUCTS_VIRTUAL_DEFAULT', '0', 'What should the Default Virtual Product status be when adding new products?<br /><br />0= Virtual Product Defaults to OFF<br />1= Virtual Product Defaults to ON<br />NOTE: Virtual Products do not require a Shipping Address', '7', '10', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Default Status - Normal Shipping Rules', 'PRODUCTS_IS_ALWAYS_FREE_SHIPPING_DEFAULT', '0', 'What should the Default Free Shipping status be when adding new products?<br /><br />0= Free Shipping Defaults to OFF<br />1= Free Shipping Defaults to ON<br />NOTE: Free Shipping Products require a Shipping Address', '7', '11', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Number of Boxes and Weight Status', 'SHIPPING_BOX_WEIGHT_DISPLAY', '3', 'Display Shipping Weight and Number of Boxes?<br /><br />0= off<br />1= Boxes Only<br />2= Weight Only<br />3= Both Boxes and Weight', '7', '15', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shipping Estimator Display Settings for Shopping Cart', 'SHOW_SHIPPING_ESTIMATOR_BUTTON', '1', '<br />0= Off<br />1= Display as Button on Shopping Cart<br />2= Display as Listing on Shopping Cart Page', '7', '20', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Display Order Comments on Admin Invoice', 'ORDER_COMMENTS_INVOICE', '1', 'Do you want to display the Order Comments on the Admin Invoice?<br />0= OFF<br />1= First Comment by Customer only<br />2= All Comments for the Order', 7, 25, now(), NULL, 'zen_cfg_select_option(array(''0'', ''1'', ''2''), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Display Order Comments on Admin Packing Slip', 'ORDER_COMMENTS_PACKING_SLIP', '1', 'Do you want to display the Order Comments on the Admin Packing Slip?<br />0= OFF<br />1= First Comment by Customer only<br />2= All Comments for the Order', 7, 26, now(), NULL, 'zen_cfg_select_option(array(''0'', ''1'', ''2''), ');


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Order Free Shipping 0 Weight Status', 'ORDER_WEIGHT_ZERO_STATUS', '0', 'If there is no weight to the order, does the order have Free Shipping?<br />0= no<br />1= yes<br /><br />Note: When using Free Shipping, Enable the Free Shipping Module this will only show when shipping is free.', '7', '15', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_LIST_IMAGE', '1', 'Do you want to display the Product Image?', '8', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufacturer Name','PRODUCT_LIST_MANUFACTURER', '0', 'Do you want to display the Product Manufacturer Name?', '8', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_LIST_MODEL', '0', 'Do you want to display the Product Model?', '8', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_LIST_NAME', '2', 'Do you want to display the Product Name?', '8', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price/Add to Cart', 'PRODUCT_LIST_PRICE', '3', 'Do you want to display the Product Price/Add to Cart', '8', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_LIST_QUANTITY', '0', 'Do you want to display the Product Quantity?', '8', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_LIST_WEIGHT', '0', 'Do you want to display the Product Weight?', '8', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price/Add to Cart Column Width', 'PRODUCTS_LIST_PRICE_WIDTH', '125', 'Define the width of the Price/Add to Cart column<br />Default= 125', '8', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Category/Manufacturer Filter (0=off; 1=on)', 'PRODUCT_LIST_FILTER', '1', 'Do you want to display the Category/Manufacturer Filter?', '8', '9', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Prev/Next Split Page Navigation (1-top, 2-bottom, 3-both)', 'PREV_NEXT_BAR_LOCATION', '3', 'Sets the location of the Prev/Next Split Page Navigation', '8', '10', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Listing Default Sort Order', 'PRODUCT_LISTING_DEFAULT_SORT_ORDER', '', 'Product Listing Default sort order?<br />NOTE: Leave Blank for Product Sort Order. Sort the Product Listing in the order you wish for the default display to start in to get the sort order setting. Example: 2a', '8', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Product Add to Cart Button (0=off; 1=on; 2=on with Qty Box per Product)', 'PRODUCT_LIST_PRICE_BUY_NOW', '1', 'Do you want to display the Add to Cart Button?<br /><br /><strong>NOTE:</strong> Turn OFF Display Multiple Products Qty Box Status to use Option 2 on with Qty Box per Product', '8', '20', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', '3', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '8', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Description', 'PRODUCT_LIST_DESCRIPTION', '150', 'Do you want to display the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', '8', '30', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Product Listing Ascending Sort Order', 'PRODUCT_LIST_SORT_ORDER_ASCENDING', '+', 'What do you want to use to indicate Sort Order Ascending?<br />Default = +', 8, 40, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Product Listing Descending Sort Order', 'PRODUCT_LIST_SORT_ORDER_DESCENDING', '-', 'What do you want to use to indicate Sort Order Descending?<br />Default = -', 8, 41, NULL, now(), NULL, 'zen_cfg_textarea_small(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Alpha Sorter Dropdown', 'PRODUCT_LIST_ALPHA_SORTER', 'true', 'Do you want to include an Alpha Filter dropdown on the Product Listing?', '8', '50', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Sub Categories Image', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS', 'true', 'Do you want to include the Sub Categories Image on the Product Listing?', '8', '52', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Top Categories Image', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS_TOP', 'true', 'Do you want to include the Top Categories Image on the Product Listing?', '8', '53', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show SubCategories on Main Page while navigating', 'PRODUCT_LIST_CATEGORY_ROW_STATUS', '1', 'Show Sub-Categories on Main Page while navigating through Categories<br /><br />0= off<br />1= on', '8', '60', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check stock level', 'STOCK_CHECK', 'true', 'Check to see if sufficient stock is available', '9', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Subtract stock', 'STOCK_LIMITED', 'true', 'Subtract product in stock by product orders', '9', '2', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Checkout', 'STOCK_ALLOW_CHECKOUT', 'true', 'Allow customer to checkout even if there is insufficient stock', '9', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Mark product out of stock', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '***', 'Display something on screen so customer can see which product has insufficient stock', '9', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Stock Re-order level', 'STOCK_REORDER_LEVEL', '5', 'Define when stock needs to be re-ordered', '9', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Products status in Catalog when out of stock should be set to', 'SHOW_PRODUCTS_SOLD_OUT', '0', 'Show Products when out of stock<br /><br />0= set product status to OFF<br />1= leave product status ON', '9', '10', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Sold Out Image in place of Add to Cart', 'SHOW_PRODUCTS_SOLD_OUT_IMAGE', '1', 'Show Sold Out Image instead of Add to Cart Button<br /><br />0= off<br />1= on', '9', '11', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable disabled product by available date', 'ENABLE_DISABLED_UPCOMING_PRODUCT', 'Automatic', 'How should hidden (disabled) product with a future available date be made visible (active) to customers when the date is reached?<br />', '9', '12', 'zen_cfg_select_option(array(\'Manual\', \'Automatic\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Quantity Decimals', 'QUANTITY_DECIMALS', '0', 'Allow how many decimals on Quantity<br /><br />0= off', '9', '15', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Shopping Cart - Delete Checkboxes or Delete Button', 'SHOW_SHOPPING_CART_DELETE', '3', 'Show on Shopping Cart Delete Button and/or Checkboxes<br /><br />1= Delete Button Only<br />2= Checkbox Only<br />3= Both Delete Button and Checkbox', '9', '20', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Shopping Cart - Update Cart Button Location', 'SHOW_SHOPPING_CART_UPDATE', '3', 'Show on Shopping Cart Update Cart Button Location as:<br /><br />1= Next to each Qty Box<br />2= Below all Products<br />3= Both Next to each Qty Box and Below all Products', '9', '22', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_NEW_PRODUCTS', '1', 'Show New Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '30', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_FEATURED_PRODUCTS', '2', 'Show Featured Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '31', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_SPECIALS_PRODUCTS', '3', 'Show Special Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '32', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_UPCOMING', '4', 'Show Upcoming Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '33', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Notice of Combining Shopping Cart on Login', 'SHOW_SHOPPING_CART_COMBINED', '1', 'When a customer logs in and has a previously stored shopping cart, the products are combined with the existing shopping cart.<br /><br />Do you wish to display a Notice to the customer?<br /><br />0= OFF, do not display a notice<br />1= Yes show notice and go to shopping cart<br />2= Yes show notice, but do not go to shopping cart', '9', '35', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Log Database Queries', 'STORE_DB_TRANSACTIONS', 'false', 'Record the database queries to files in the system /logs/ folder. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.<br><strong>Enabling this makes your site NON-COMPLIANT with PCI DSS rules, thus invalidating any certification.</strong>', '10', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Admin)?', 'REPORT_ALL_ERRORS_ADMIN', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', 10, 40, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Store)?', 'REPORT_ALL_ERRORS_STORE', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', 10, 41, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors: Backtrace on Notice Errors?', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE', 'No', 'Include backtrace information on &quot;Notice&quot; errors?  These are usually isolated to the identified file and the backtrace information just fills the logs. Default (<b>No</b>).', 10, 42, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\'),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Transport Method', 'EMAIL_TRANSPORT', 'PHP', 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should only be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.', '12', '1', 'zen_cfg_select_option(array(\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Qmail\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Account Mailbox', 'EMAIL_SMTPAUTH_MAILBOX', 'YourEmailAccountNameHere', 'Enter the mailbox account name (me@mydomain.com) supplied by your host. This is the account name that your host requires for SMTP authentication.<br />Only required if using SMTP Authentication for email.', '12', '101', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('SMTP Email Account Password', 'EMAIL_SMTPAUTH_PASSWORD', 'YourPasswordHere', 'Enter the password for your SMTP mailbox. <br />Only required if using SMTP Authentication for email.', '12', '101', now(), 'zen_cfg_password_display');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Mail Host', 'EMAIL_SMTPAUTH_MAIL_SERVER', 'mail.EnterYourDomain.com', 'Enter the DNS name of your SMTP mail server.<br />ie: mail.mydomain.com<br />or 55.66.77.88<br />Only required if using SMTP Authentication for email.', '12', '101', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Mail Server Port', 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT', '25', 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.<br><br>Default: 25<br>Typical values are:<br>25 - normal unencrypted SMTP<br>587 - encrypted SMTP<br>465 - older MS SMTP port', '12', '101', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send E-Mails', 'SEND_EMAILS', 'true', 'Send out e-mails?<br>Normally this is set to true.<br>Set to false to suppress ALL outgoing email messages from this store, such as when working with a test copy of your store offline.', '12', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Linefeeds', 'EMAIL_LINEFEED', 'LF', 'Defines the character sequence used to separate mail headers.', '12', '2', 'zen_cfg_select_option(array(\'LF\', \'CRLF\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable HTML Emails?', 'EMAIL_USE_HTML', 'false', 'Send emails in HTML format if recipient has enabled it in their preferences.', '12', '3', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Verify E-Mail Addresses Through DNS', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 'Verify e-mail address through a DNS server', '6', '6', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Email Archiving Active?', 'EMAIL_ARCHIVE', 'false', 'If you wish to have email messages archived/stored when sent, set this to "true".', '12', '6', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Friendly-Errors', 'EMAIL_FRIENDLY_ERRORS', 'true', 'Do you want to display friendly errors if emails fail?  Setting this to false will display PHP errors and likely cause the script to fail. Only set to false while troubleshooting, and true for a live shop.', '12', '7', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Email Address (Displayed to Contact you)', 'STORE_OWNER_EMAIL_ADDRESS', 'root@localhost', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Email address of Store Owner.  Used as "display only" when informing customers of how to contact you.', '12', '10', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Email Address (sent FROM)', 'EMAIL_FROM', 'Zen Cart <root@localhost>', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Address from which email messages will be "sent" by default. Can be over-ridden at compose-time in admin modules.', '12', '11', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function) VALUES ('Emails must send from known domain?', 'EMAIL_SEND_MUST_BE_STORE', 'Yes', 'Does your mailserver require that all outgoing emails have their "from" address match a known domain that exists on your webserver?<br /><br />This is often required in order to prevent spoofing and spam broadcasts.  If set to Yes, this will cause the email address (sent FROM) to be used as the "from" address on all outgoing mail.', 12, 11, NULL, 'zen_cfg_select_option(array(\'No\', \'Yes\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function) VALUES ('Email Admin Format?', 'ADMIN_EXTRA_EMAIL_FORMAT', 'TEXT', 'Please select the Admin extra email format (Note: Enable HTML Emails must be on for HTML option to work)', 12, 12, NULL, 'zen_cfg_select_option(array(\'TEXT\', \'HTML\'), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Order Confirmation Emails To', 'SEND_EXTRA_ORDER_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send COPIES of order confirmation emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Create Account Emails To - Status', 'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS', '0', 'Send copy of Create Account Status<br />0= off 1= on', '12', '13', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Create Account Emails To', 'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Create Account emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '14', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Customer GV Send Emails To - Status', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS', '0', 'Send copy of Customer GV Send Status<br />0= off 1= on', '12', '17', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Customer GV Send Emails To', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Customer GV Send emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '18', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Admin GV Mail Emails To - Status', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO_STATUS', '0', 'Send copy of Admin GV Mail Status<br />0= off 1= on', '12', '19', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Customer Admin GV Mail Emails To', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Admin GV Mail emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '20', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Admin Discount Coupon Mail Emails To - Status', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS', '0', 'Send copy of Admin Discount Coupon Mail Status<br />0= off 1= on', '12', '21', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Customer Admin Discount Coupon Mail Emails To', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Admin Discount Coupon Mail emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '22', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Admin Orders Status Emails To - Status', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS', '0', 'Send copy of Admin Orders Status Status<br />0= off 1= on', '12', '23', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Admin Orders Status Emails To', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Admin Orders Status emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '24', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Notice of Pending Reviews Emails To - Status', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS', '0', 'Send copy of Pending Reviews Status<br />0= off 1= on', '12', '25', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Notice of Pending Reviews Emails To', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'Send copy of Pending Reviews emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '26', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Set "Contact Us" Email Dropdown List', 'CONTACT_US_LIST', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'On the "Contact Us" Page, set the list of email addresses , in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '40', 'zen_cfg_textarea(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Contact Us - Show Store Name and Address', 'CONTACT_US_STORE_NAME_ADDRESS', '1', 'Include Store Name and Address<br />0= off 1= on', '12', '50', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Send Low Stock Emails', 'SEND_LOWSTOCK_EMAIL', '0', 'When stock level is at or below low stock level send an email<br />0= off<br />1= on', '12', '60', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Low Stock Emails To', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO', '', '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}', 'When stock level is at or below low stock level send an email to this address, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '61', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display "Newsletter Unsubscribe" Link?', 'SHOW_NEWSLETTER_UNSUBSCRIBE_LINK', 'true', 'Show "Newsletter Unsubscribe" link in the "Information" side-box?', '12', '70', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Audience-Select Count Display', 'AUDIENCE_SELECT_DISPLAY_COUNTS', 'true', 'When displaying lists of available audiences/recipients, should the recipients-count be included? <br /><em>(This may make things slower if you have a lot of customers or complex audience queries)</em>', '12', '90', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Downloads', 'DOWNLOAD_ENABLED', 'true', 'Enable the products download functions.', '13', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Download by Redirect', 'DOWNLOAD_BY_REDIRECT', 'true', 'Use browser redirection for download. Disable on non-Unix systems.<br /><br />Note: Set /pub to 777 when redirect is true', '13', '2', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Download by streaming', 'DOWNLOAD_IN_CHUNKS', 'false', 'If download-by-redirect is disabled, and your PHP memory_limit setting is under 8 MB, you might need to enable this setting so that files are streamed in smaller segments to the browser.<br /><br />Has no effect if Download By Redirect is enabled.', '13', '2', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Download Expiration (Number of Days)' ,'DOWNLOAD_MAX_DAYS', '7', 'Set number of days before the download link expires. 0 means no limit.', '13', '3', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Number of Downloads Allowed - Per Product' ,'DOWNLOAD_MAX_COUNT', '5', 'Set the maximum number of downloads. 0 means no download authorized.', '13', '4', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Downloads Controller Update Status Value', 'DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE', '4', 'What orders_status resets the Download days and Max Downloads - Default is 4', '13', '10', now(), now(), NULL , NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Downloads Controller Order Status Value >= lower value', 'DOWNLOADS_CONTROLLER_ORDERS_STATUS', '2', 'Downloads Controller Order Status Value - Default >= 2<br /><br />Downloads are available for checkout based on the orders status. Orders with orders status greater than this value will be available for download. The orders status is set for an order by the Payment Modules. Set the lower range for this range.', '13', '12', now(), now(), NULL , NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Downloads Controller Order Status Value <= upper value', 'DOWNLOADS_CONTROLLER_ORDERS_STATUS_END', '4', 'Downloads Controller Order Status Value - Default <= 4<br /><br />Downloads are available for checkout based on the orders status. Orders with orders status less than this value will be available for download. The orders status is set for an order by the Payment Modules.  Set the upper range for this range.', '13', '13', now(), now(), NULL , NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Price Factor', 'ATTRIBUTES_ENABLED_PRICE_FACTOR', 'true', 'Enable the Attributes Price Factor.', '13', '25', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Qty Price Discount', 'ATTRIBUTES_ENABLED_QTY_PRICES', 'true', 'Enable the Attributes Quantity Price Discounts.', '13', '26', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Attribute Images', 'ATTRIBUTES_ENABLED_IMAGES', 'true', 'Enable the Attributes Images.', '13', '28', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Text Pricing by word or letter', 'ATTRIBUTES_ENABLED_TEXT_PRICES', 'true', 'Enable the Attributes Text Pricing by word or letter.', '13', '35', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Text Pricing - Spaces are Free', 'TEXT_SPACES_FREE', '1', 'On Text pricing Spaces are Free<br /><br />0= off 1= on', '13', '36', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Read Only option type - Ignore for Add to Cart', 'PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED', '1', 'When a Product only uses READONLY attributes, should the Add to Cart button be On or Off?<br />0= OFF<br />1= ON', '13', '37', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());



INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable GZip Compression', 'GZIP_LEVEL', '0', '0= off 1= on', '14', '1', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'This should point to the folder specified in your DIR_FS_SQL_CACHE setting in your configure.php files.', '15', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Cookie Domain', 'SESSION_USE_FQDN', 'True', 'If True the full domain name will be used to store the cookie, e.g. www.mydomain.com. If False only a partial domain name will be used, e.g. mydomain.com. If you are unsure about this, always leave set to true.', '15', '2', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Force Cookie Use', 'SESSION_FORCE_COOKIE_USE', 'False', 'Force the use of sessions when cookies are only enabled.', '15', '2', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check SSL Session ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Validate the SSL_SESSION_ID on every secure HTTPS page request.', '15', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check User Agent', 'SESSION_CHECK_USER_AGENT', 'False', 'Validate the clients browser user agent on every page request.', '15', '4', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check IP Address', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Validate the clients IP address on every page request.', '15', '5', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Prevent Spider Sessions', 'SESSION_BLOCK_SPIDERS', 'True', 'Prevent known spiders from starting a session.', '15', '6', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Recreate Session', 'SESSION_RECREATE', 'True', 'Recreate the session to generate a new session ID when the customer logs on or creates an account (PHP >=4.1 needed).', '15', '7', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('IP to Host Conversion Status', 'SESSION_IP_TO_HOST_ADDRESS', 'true', 'Convert IP Address to Host Address<br /><br />Note: on some servers this can slow down the initial start of a session or execution of Emails', '15', '10', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Length of the redeem code', 'SECURITY_CODE_LENGTH', '10', 'Enter the length of the redeem code<br />The longer the more secure', 16, 1, NULL, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Default Order Status For Zero Balance Orders', 'DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID', '2', 'When an order\'s balance is zero, this order status will be assigned to it.', '16', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('New Signup Discount Coupon ID#', 'NEW_SIGNUP_DISCOUNT_COUPON', '', 'Select the coupon<br />', 16, 75, NULL, now(), NULL, 'zen_cfg_select_coupon_id(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('New Signup Gift Voucher Amount', 'NEW_SIGNUP_GIFT_VOUCHER_AMOUNT', '', 'Leave blank for none<br />Or enter an amount ie. 10 for $10.00', 16, 76, NULL, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Discount Coupons Per Page', 'MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS', '20', 'Number of Discount Coupons to list per Page', '16', '81', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Discount Coupon Report Results Per Page', 'MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS', '20', 'Number of Discount Coupons to list on Reports Page', '16', '81', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - VISA', 'CC_ENABLED_VISA', '1', 'Accept VISA 0= off 1= on', '17', '1', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - MasterCard', 'CC_ENABLED_MC', '1', 'Accept MasterCard 0= off 1= on', '17', '2', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - AmericanExpress', 'CC_ENABLED_AMEX', '0', 'Accept AmericanExpress 0= off 1= on', '17', '3', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - Diners Club', 'CC_ENABLED_DINERS_CLUB', '0', 'Accept Diners Club 0= off 1= on', '17', '4', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - Discover Card', 'CC_ENABLED_DISCOVER', '0', 'Accept Discover Card 0= off 1= on', '17', '5', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - JCB', 'CC_ENABLED_JCB', '0', 'Accept JCB 0= off 1= on', '17', '6', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - AUSTRALIAN BANKCARD', 'CC_ENABLED_AUSTRALIAN_BANKCARD', '0', 'Accept AUSTRALIAN BANKCARD 0= off 1= on', '17', '7', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - SOLO', 'CC_ENABLED_SOLO', '0', 'Accept SOLO Card 0= off 1= on', '17', '8', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - Debit', 'CC_ENABLED_DEBIT', '0', 'Accept Debit Cards 0= off 1= on<br>NOTE: This is not deeply integrated at this time, and this setting may be redundant if your payment modules do not yet specifically have code to honour this switch.', '17', '9', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enable Status - Maestro', 'CC_ENABLED_MAESTRO', '0', 'Accept MAESTRO Card 0= off 1= on', '17', '10', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Credit Card Enabled - Show on Payment', 'SHOW_ACCEPTED_CREDIT_CARDS', '0', 'Show accepted credit cards on Payment page?<br />0= off<br />1= As Text<br />2= As Images<br /><br />Note: images and text must be defined in both the database and language file for specific credit card types.', '17', '50', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '840', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:40', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Queue Purchases', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', 'Do you want to queue purchases of the Gift Voucher?', 6, 3, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', 'Include Shipping in calculation', 6, 5, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', 'Include Tax in calculation.', 6, 6, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', 'Re-Calculate Tax', 6, 7, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', 'Use the following tax class when treating Gift Voucher as Credit Note.', 6, 0, NULL, '2003-10-30 22:16:40', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Credit including Tax', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', 'Add tax to purchased Gift Voucher when crediting to Account', 6, 8, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set Order Status', 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID', '0', 'Set the status of orders made where GV covers full payment', 6, 0, NULL, now(), 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:43', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', '400', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:43', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Allow Low Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'false', 'Do you want to allow low order fees?', 6, 3, NULL, '2003-10-30 22:16:43', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Order Fee For Orders Under', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', '50', 'Add the low order fee to orders under this amount.', 6, 4, NULL, '2003-10-30 22:16:43', 'currencies->format', NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', '5', 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00', 6, 5, NULL, '2003-10-30 22:16:43', '', NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Attach Low Order Fee On Orders Made', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'both', 'Attach low order fee for orders sent to the set destination.', 6, 6, NULL, '2003-10-30 22:16:43', NULL, 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', '0', 'Use the following tax class on the low order fee.', 6, 7, NULL, '2003-10-30 22:16:43', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('No Low Order Fee on Virtual Products', 'MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL', 'false', 'Do not charge Low Order Fee when cart is Virtual Products Only', 6, 8, NULL, '2004-04-20 22:16:43', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('No Low Order Fee on Gift Vouchers', 'MODULE_ORDER_TOTAL_LOWORDERFEE_GV', 'false', 'Do not charge Low Order Fee when cart is Gift Vouchers Only', 6, 9, NULL, '2004-04-20 22:16:43', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:46', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '200', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:46', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', 6, 3, NULL, '2003-10-30 22:16:46', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', 6, 4, NULL, '2003-10-30 22:16:46', 'currencies->format', NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', 6, 5, NULL, '2003-10-30 22:16:46', NULL, 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:49', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '100', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:49', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:52', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '300', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:52', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:55', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '999', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:55', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', 6, 0, NULL, '2003-10-30 22:16:36', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', 6, 6, NULL, '2003-10-30 22:16:36', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', 6, 2, NULL, '2003-10-30 22:16:36', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'false', 'Include Shipping in calculation', 6, 5, NULL, '2003-10-30 22:16:36', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', 6, 1, NULL, '2003-10-30 22:16:36', NULL, 'zen_cfg_select_option(array(\'true\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', 6, 7, NULL, '2003-10-30 22:16:36', NULL, 'zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Product option type Select', 'PRODUCTS_OPTIONS_TYPE_SELECT', '0', 'The number representing the Select type of product option.', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Text product option type', 'PRODUCTS_OPTIONS_TYPE_TEXT', '1', 'Numeric value of the text product option type', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Radio button product option type', 'PRODUCTS_OPTIONS_TYPE_RADIO', '2', 'Numeric value of the radio button product option type', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Check box product option type', 'PRODUCTS_OPTIONS_TYPE_CHECKBOX', '3', 'Numeric value of the check box product option type', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('File product option type', 'PRODUCTS_OPTIONS_TYPE_FILE', '4', 'Numeric value of the file product option type', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('ID for text and file products options values', 'PRODUCTS_OPTIONS_VALUES_TEXT_ID', '0', 'Numeric value of the products_options_values_id used by the text and file attributes.', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Upload prefix', 'UPLOAD_PREFIX', 'upload_', 'Prefix used to differentiate between upload options and other options', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Text prefix', 'TEXT_PREFIX', 'txt_', 'Prefix used to differentiate between text option values and other option values', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Read Only option type', 'PRODUCTS_OPTIONS_TYPE_READONLY', '5', 'Numeric value of the file product option type', 6, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('login mode https', 'SSLPWSTATUSCHECK', '', 'System setting. Do not edit.', 6, 99, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order) VALUES ('Default Target Category (Products to Multiple Categories Manager)', 'P2C_TARGET_CATEGORY_DEFAULT', '', 'Default Target Category for Products to Multiple Categories Manager (set on page)', 6, 100);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Products Info - Products Option Name Sort Order', 'PRODUCTS_OPTIONS_SORT_ORDER', '0', 'Sort order of Option Names for Products Info<br />0= Sort Order, Option Name<br />1= Option Name', 18, 35, now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Products Info - Product Option Value of Attributes Sort Order', 'PRODUCTS_OPTIONS_SORT_BY_PRICE', '1', 'Sort order of Product Option Values of Attributes for Products Info<br />0= Sort Order, Price<br />1= Sort Order, Option Value Name', 18, 36, now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');

# test remove and only use products_options_images_per_row
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product Info - Number of Attribute Images per Row', 'PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW', '5', 'Product Info - Enter the number of attribute images to display per row<br />Default = 5', '18', '40', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Show Option Values Name Below Attributes Image', 'PRODUCT_IMAGES_ATTRIBUTES_NAMES', '1', 'Product Info - Show the name of the Option Value beneath the Attribute Image?<br />0= off 1= on', 18, 41, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

# test remove and only use products_options_images_style
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Show Option Values and Attributes Images for Radio Buttons and Checkboxes', 'PRODUCT_IMAGES_ATTRIBUTES_NAMES_COLUMN', '0', '0= Images Below Option Names<br />1= Element, Image and Option Value<br />2= Element, Image and Option Name Below<br />3= Option Name Below Element and Image<br />4= Element Below Image and Option Name<br />5= Element Above Image and Option Name', 18, 42, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Show Sales Discount Savings Status', 'SHOW_SALE_DISCOUNT_STATUS', '1', 'Product Info - Show the amount of discount savings?<br />0= off 1= on', 18, 45, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Show Sales Discount Savings Dollars or Percentage', 'SHOW_SALE_DISCOUNT', '1', 'Product Info - Show the amount of discount savings display as:<br />1= % off 2= $amount off', 18, 46, 'zen_cfg_select_option(array(\'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Show Sales Discount Savings Percentage Decimals', 'SHOW_SALE_DISCOUNT_DECIMALS', '0', 'Product Info - Show discount savings display as a Percentage with how many decimals?:<br />Default= 0', 18, 47, NULL, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Price is Free Image or Text Status', 'OTHER_IMAGE_PRICE_IS_FREE_ON', '1', 'Product Info - Show the Price is Free Image or Text on Displayed Price<br />0= Text<br />1= Image', 18, 50, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Info - Price is Call for Price Image or Text Status', 'PRODUCTS_PRICE_IS_CALL_IMAGE_ON', '1', 'Product Info - Show the Price is Call for Price Image or Text on Displayed Price<br />0= Text<br />1= Image', 18, 51, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Quantity Box Status - Adding New Products', 'PRODUCTS_QTY_BOX_STATUS', '1', 'What should the Default Quantity Box Status be set to when adding New Products?<br /><br />0= off<br />1= on<br />NOTE: This will show a Qty Box when ON and default the Add to Cart to 1', '18', '55', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product Reviews Require Approval', 'REVIEWS_APPROVAL', '1', 'Do product reviews require approval?<br /><br />Note: When Review Status is off, it will also not show<br /><br />0= off 1= on', '18', '62', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product page generated &lt;title&gt; tag - include Product Model?', 'META_TAG_INCLUDE_MODEL', '1', 'When custom Keywords and Description meta tags are not set, include the Product Model in the generated page &lt;title&gt; tag?<br><br>0=no / 1=yes', '18', '69', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Product page generated &lt;title&gt; tag - include Product Price?', 'META_TAG_INCLUDE_PRICE', '1', 'When custom Keywords and Description meta tags are not set, include the Product Price in the generated page &lt;title&gt; tag?<br><br>0=no / 1=yes', '18', '70', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Product page generated &lt;meta - description&gt; tag - Maximum Length', 'MAX_META_TAG_DESCRIPTION_LENGTH', '50', 'When custom Keywords and Description meta tags are not set, limit the generated &lt;meta - description&gt; tag to this number of words. Default 50.', '18', '71', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Also Purchased Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS', '3', 'Also Purchased Products Columns per Row<br />0= off or set the sort order', '18', '72', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Navigation Bar Position', 'PRODUCT_INFO_PREVIOUS_NEXT', '1', 'Location of Previous/Next Navigation Bar<br />0= off<br />1= Top of Page<br />2= Bottom of Page<br />3= Both Top and Bottom of Page', 18, 21, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Off\'), array(\'id\'=>\'1\', \'text\'=>\'Top of Page\'), array(\'id\'=>\'2\', \'text\'=>\'Bottom of Page\'), array(\'id\'=>\'3\', \'text\'=>\'Both Top & Bottom of Page\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Sort Order', 'PRODUCT_INFO_PREVIOUS_NEXT_SORT', '1', 'Products Display Order by<br />0= Product ID<br />1= Product Name<br />2= Model<br />3= Price, Product Name<br />4= Price, Model<br />5= Product Name, Model<br />6= Product Sort Order', 18, 22, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Product ID\'), array(\'id\'=>\'1\', \'text\'=>\'Name\'), array(\'id\'=>\'2\', \'text\'=>\'Product Model\'), array(\'id\'=>\'3\', \'text\'=>\'Product Price - Name\'), array(\'id\'=>\'4\', \'text\'=>\'Product Price - Model\'), array(\'id\'=>\'5\', \'text\'=>\'Product Name - Model\'), array(\'id\'=>\'6\', \'text\'=>\'Product Sort Order\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Button and Image Status', 'SHOW_PREVIOUS_NEXT_STATUS', '0', 'Button and Product Image status settings are:<br />0= Off<br />1= On', 18, 20, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Off\'), array(\'id\'=>\'1\', \'text\'=>\'On\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Button and Image Settings', 'SHOW_PREVIOUS_NEXT_IMAGES', '0', 'Show Previous/Next Button and Product Image Settings<br />0= Button Only<br />1= Button and Product Image<br />2= Product Image Only', 18, 21, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Button Only\'), array(\'id\'=>\'1\', \'text\'=>\'Button and Product Image\'), array(\'id\'=>\'2\', \'text\'=>\'Product Image Only\')),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Previous Next - Image Width?', 'PREVIOUS_NEXT_IMAGE_WIDTH', '50', 'Previous/Next Image Width?', '18', '22', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Previous Next - Image Height?', 'PREVIOUS_NEXT_IMAGE_HEIGHT', '40', 'Previous/Next Image Height?', '18', '23', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Navigation Includes Category Position', 'PRODUCT_INFO_CATEGORIES', '1', 'Product\'s Category Image and Name Alignment Above Previous/Next Navigation Bar<br />0= off<br />1= Align Left<br />2= Align Center<br />3= Align Right', 18, 20, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Off\'), array(\'id\'=>\'1\', \'text\'=>\'Align Left\'), array(\'id\'=>\'2\', \'text\'=>\'Align Center\'), array(\'id\'=>\'3\', \'text\'=>\'Align Right\')),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Navigation Includes Category Name and Image Status', 'PRODUCT_INFO_CATEGORIES_IMAGE_STATUS', '2', 'Product\'s Category Image and Name Status<br />0= Category Name and Image always shows<br />1= Category Name only<br />2= Category Name and Image when not blank', 18, 20, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Category Name and Image Always\'), array(\'id\'=>\'1\', \'text\'=>\'Category Name only\'), array(\'id\'=>\'2\', \'text\'=>\'Category Name and Image when not blank\')),');



INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Column Width - Left Boxes', 'BOX_WIDTH_LEFT', '150px', 'Width of the Left Column Boxes<br />px may be included<br />Default = 150px', 19, 1, NULL, '2003-11-21 22:16:36', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Column Width - Right Boxes', 'BOX_WIDTH_RIGHT', '150px', 'Width of the Right Column Boxes<br />px may be included<br />Default = 150px', 19, 2, NULL, '2003-11-21 22:16:36', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Bread Crumbs Navigation Separator', 'BREAD_CRUMBS_SEPARATOR', '&nbsp;::&nbsp;', 'Enter the separator symbol to appear between the Navigation Bread Crumb trail<br />Note: Include spaces with the &amp;nbsp; symbol if you want them part of the separator.<br />Default = &amp;nbsp;::&amp;nbsp;', 19, 3, NULL, '2003-11-21 22:16:36', NULL, 'zen_cfg_textarea_small(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Define Breadcrumb Status', 'DEFINE_BREADCRUMB_STATUS', '1', 'Enable the Breadcrumb Trail Links?<br />0= OFF<br />1= ON<br />2= Off for Home Page Only', 19, 4, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Bestsellers - Number Padding', 'BEST_SELLERS_FILLER', '&nbsp;', 'What do you want to Pad the numbers with?<br />Default = &amp;nbsp;', 19, 5, NULL, '2003-11-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Bestsellers - Truncate Product Names', 'BEST_SELLERS_TRUNCATE', '35', 'What size do you want to truncate the Product Names?<br />Default = 35', 19, 6, NULL, '2003-11-21 22:16:36', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Bestsellers - Truncate Product Names followed by ...', 'BEST_SELLERS_TRUNCATE_MORE', 'true', 'When truncated Product Names follow with ...<br />Default = true', 19, 7, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Box - Show Specials Link', 'SHOW_CATEGORIES_BOX_SPECIALS', 'true', 'Show Specials Link in the Categories Box', 19, 8, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Box - Show Products New Link', 'SHOW_CATEGORIES_BOX_PRODUCTS_NEW', 'true', 'Show Products New Link in the Categories Box', 19, 9, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shopping Cart Box Status', 'SHOW_SHOPPING_CART_BOX_STATUS', '1', 'Shopping Cart Shows<br />0= Always<br />1= Only when full<br />2= Only when full but not when viewing the Shopping Cart', 19, 10, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Box - Show Featured Products Link', 'SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS', 'true', 'Show Featured Products Link in the Categories Box', 19, 11, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Box - Show Products All Link', 'SHOW_CATEGORIES_BOX_PRODUCTS_ALL', 'true', 'Show Products All Link in the Categories Box', 19, 12, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Column Left Status - Global', 'COLUMN_LEFT_STATUS', '1', 'Show Column Left, unless page override exists?<br />0= Column Left is always off<br />1= Column Left is on, unless page override', 19, 15, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Column Right Status - Global', 'COLUMN_RIGHT_STATUS', '1', 'Show Column Right, unless page override exists?<br />0= Column Right is always off<br />1= Column Right is on, unless page override', 19, 16, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Column Width - Left', 'COLUMN_WIDTH_LEFT', '150px', 'Width of the Left Column<br />px may be included<br />Default = 150px', 19, 20, NULL, '2003-11-21 22:16:36', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Column Width - Right', 'COLUMN_WIDTH_RIGHT', '150px', 'Width of the Right Column<br />px may be included<br />Default = 150px', 19, 21, NULL, '2003-11-21 22:16:36', NULL, NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories Separator between links Status', 'SHOW_CATEGORIES_SEPARATOR_LINK', '1', 'Show Category Separator between Category Names and Links?<br />0= off<br />1= on', 19, 24, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Separator between the Category Name and Count', 'CATEGORIES_SEPARATOR', '-&gt;', 'What separator do you want between the Category name and the count?<br />Default = -&amp;gt;', 19, 25, NULL, '2003-11-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Separator between the Category Name and Sub Categories', 'CATEGORIES_SEPARATOR_SUBS', '|_&nbsp;', 'What separator do you want between the Category name and Sub Category Name?<br />Default = |_&amp;nbsp;', 19, 26, NULL, '2004-03-25 22:16:36', NULL, 'zen_cfg_textarea_small(');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Count Prefix', 'CATEGORIES_COUNT_PREFIX', '&nbsp;(', 'What do you want to Prefix the count with?<br />Default= (', 19, 27, NULL, '2003-01-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Count Suffix', 'CATEGORIES_COUNT_SUFFIX', ')', 'What do you want as a Suffix to the count?<br />Default= )', 19, 28, NULL, '2003-01-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories SubCategories Indent', 'CATEGORIES_SUBCATEGORIES_INDENT', '&nbsp;&nbsp;', 'What do you want to use as the subcategories indent?<br />Default= &nbsp;&nbsp;', 19, 29, NULL, '2004-06-24 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories with 0 Products Status', 'CATEGORIES_COUNT_ZERO', '0', 'Show Category Count for 0 Products?<br />0= off<br />1= on', 19, 30, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Split Categories Box', 'CATEGORIES_SPLIT_DISPLAY', 'True', 'Split the categories box display by product type', 19, 31, 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shopping Cart - Show Totals', 'SHOW_TOTALS_IN_CART', '1', 'Show Totals Above Shopping Cart?<br />0= off<br />1= on: Items Weight Amount<br />2= on: Items Weight Amount, but no weight when 0<br />3= on: Items Amount', 19, 31, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Greeting - Show on Index Page', 'SHOW_CUSTOMER_GREETING', '1', 'Always Show Customer Greeting on Index?<br />0= off<br />1= on', 19, 40, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories - Always Show on Main Page', 'SHOW_CATEGORIES_ALWAYS', '0', 'Always Show Categories on Main Page<br />0= off<br />1= on<br />Default category can be set to Top Level or a Specific Top Level', 19, 45, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Main Page - Opens with Category', 'CATEGORIES_START_MAIN', '0', '0= Top Level Categories<br />Or enter the Category ID#<br />Note: Sub Categories can also be used Example: 3_10', '19', '46', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories - Always Open to Show SubCategories', 'SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS', '1', 'Always Show Categories and SubCategories<br />0= off, just show Top Categories<br />1= on, Always show Categories and SubCategories when selected', 19, 47, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Header Position 1', 'SHOW_BANNERS_GROUP_SET1', '', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Header Position 1?<br />Leave blank for none', '19', '55', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Header Position 2', 'SHOW_BANNERS_GROUP_SET2', '', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Header Position 2?<br />Leave blank for none', '19', '56', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Header Position 3', 'SHOW_BANNERS_GROUP_SET3', '', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Header Position 3?<br />Leave blank for none', '19', '57', '', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Footer Position 1', 'SHOW_BANNERS_GROUP_SET4', '', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Footer Position 1?<br />Leave blank for none', '19', '65', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Footer Position 2', 'SHOW_BANNERS_GROUP_SET5', '', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Footer Position 2?<br />Leave blank for none', '19', '66', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Footer Position 3', 'SHOW_BANNERS_GROUP_SET6', 'Wide-Banners', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br /><br />Default Group is Wide-Banners<br /><br />What Banner Group(s) do you want to use in the Footer Position 3?<br />Leave blank for none', '19', '67', '', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Side Box banner_box', 'SHOW_BANNERS_GROUP_SET7', 'SideBox-Banners', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br />Default Group is SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Side Box - banner_box?<br />Leave blank for none', '19', '70', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Groups - Side Box banner_box2', 'SHOW_BANNERS_GROUP_SET8', 'SideBox-Banners', 'The Banner Display Groups can be from 1 Banner Group or Multiple Banner Groups<br /><br />For Multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br /><br />Example: Wide-Banners:SideBox-Banners<br />Default Group is SideBox-Banners<br /><br />What Banner Group(s) do you want to use in the Side Box - banner_box2?<br />Leave blank for none', '19', '71', '', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Group - Side Box banner_box_all', 'SHOW_BANNERS_GROUP_SET_ALL', 'BannersAll', 'The Banner Display Group may only be from one (1) Banner Group for the Banner All sidebox<br /><br />Default Group is BannersAll<br /><br />What Banner Group do you want to use in the Side Box - banner_box_all?<br />Leave blank for none', '19', '72', '', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Footer - Show IP Address status', 'SHOW_FOOTER_IP', '1', 'Show Customer IP Address in the Footer<br />0= off<br />1= on<br />Should the Customer IP Address show in the footer?', 19, 80, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Product Discount Quantities - Add how many blank discounts?', 'DISCOUNT_QTY_ADD', '5', 'How many blank discount quantities should be added for Product Pricing?', '19', '90', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Product Discount Quantities - Display how many per row?', 'DISCOUNT_QUANTITY_PRICES_COLUMN', '5', 'How many discount quantities should show per row on Product Info Pages?', '19', '95', '', '', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories/Products Display Sort Order', 'CATEGORIES_PRODUCTS_SORT_ORDER', '0', 'Categories/Products Display Sort Order<br />0= Categories/Products Sort Order/Name<br />1= Categories/Products Name<br />2= Products Model<br />3= Products Qty+, Products Name<br />4= Products Qty-, Products Name<br />5= Products Price+, Products Name<br />6= Products Price-, Products Name', '19', '100', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Option Names and Values Global Add, Copy and Delete Features Status', 'OPTION_NAMES_VALUES_GLOBAL_STATUS', '1', 'Option Names and Values Global Add, Copy and Delete Features Status<br />0= Hide Features<br />1= Show Features<br />(Default=1)', '19', '110', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories-Tabs Menu ON/OFF', 'CATEGORIES_TABS_STATUS', '1', 'Categories-Tabs<br />This enables the display of your store\'s categories as a menu across the top of your header. There are many potential creative uses for this.<br />0= Hide Categories Tabs<br />1= Show Categories Tabs', '19', '112', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Site Map - include My Account Links?', 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP', 'No', 'Should the links to My Account show up on the site-map?<br />Note: Spiders will try to index this page, and likely should not be sent to secure pages, since there is no benefit in indexing a login page.<br /><br />Default: false', 19, 115, 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Skip 1-prod Categories', 'SKIP_SINGLE_PRODUCT_CATEGORIES', 'True', 'Skip single-product categories<br />If this option is set to True, then if the customer clicks on a link to a category which only contains a single item, then Zen Cart will take them directly to that product-page, rather than present them with another link to click in order to see the product.<br />Default: True', '19', '120', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use split-login page', 'USE_SPLIT_LOGIN_MODE', 'False', 'The login page can be displayed in two modes: Split or Vertical.<br />In Split mode, the create-account options are accessed by clicking a button to get to the create-account page.  In Vertical mode, the create-account input fields are all displayed inline, below the login field, making one less click for the customer to create their account.<br />Default: False', '19', '121', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());

# CSS Buttons switch
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('CSS Buttons', 'IMAGE_USE_CSS_BUTTONS', 'Yes', 'CSS Buttons<br />Use CSS buttons instead of images (GIF/JPG)?<br />Button styles must be configured in the stylesheet if you enable this option.', '19', '147', 'zen_cfg_select_option(array(\'No\', \'Yes\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('<strong>Down for Maintenance: ON/OFF</strong>', 'DOWN_FOR_MAINTENANCE', 'false', 'Down for Maintenance <br />(true=on false=off)', '20', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: filename', 'DOWN_FOR_MAINTENANCE_FILENAME', 'down_for_maintenance', 'Down for Maintenance filename<br />Note: Do not include the extension<br />Default=down_for_maintenance', '20', '2', '', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: Hide Header', 'DOWN_FOR_MAINTENANCE_HEADER_OFF', 'false', 'Down for Maintenance: Hide Header <br />(true=hide false=show)', '20', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: Hide Column Left', 'DOWN_FOR_MAINTENANCE_COLUMN_LEFT_OFF', 'false', 'Down for Maintenance: Hide Column Left <br />(true=hide false=show)', '20', '4', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: Hide Column Right', 'DOWN_FOR_MAINTENANCE_COLUMN_RIGHT_OFF', 'false', 'Down for Maintenance: Hide Column Right <br />(true=hide false=show)', '20', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: Hide Footer', 'DOWN_FOR_MAINTENANCE_FOOTER_OFF', 'false', 'Down for Maintenance: Hide Footer <br />(true=hide false=show)', '20', '6', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added, use_function) VALUES ('Down for Maintenance: Hide Prices', 'DOWN_FOR_MAINTENANCE_PRICES_OFF', 'false', 'Down for Maintenance: Hide Prices <br />(true=hide false=show)', '20', '7', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now(), NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Down For Maintenance (exclude this IP-Address)', 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE', 'your IP (ADMIN)', 'This IP Address is able to access the website while it is Down For Maintenance (like webmaster)<br />To enter multiple IP Addresses, separate with a comma. If you do not know your IP Address, check in the Footer of your Shop.', 20, 8, '2003-03-21 13:43:22', '2003-03-21 21:20:07', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('NOTICE PUBLIC Before going Down for Maintenance: ON/OFF', 'WARN_BEFORE_DOWN_FOR_MAINTENANCE', 'false', 'Give a WARNING some time before you put your website Down for Maintenance<br />(true=on false=off)<br />If you set the \'Down For Maintenance: ON/OFF\' to true this will automatically be updated to false', 20, 9, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Date and hours for notice before maintenance', 'PERIOD_BEFORE_DOWN_FOR_MAINTENANCE', '15/05/2003  2-3 PM', 'Date and hours for notice before maintenance website, enter date and hours for maintenance website', 20, 10, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Display when webmaster has enabled maintenance', 'DISPLAY_MAINTENANCE_TIME', 'false', 'Display when Webmaster has enabled maintenance <br />(true=on false=off)<br />', 20, 11, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Display website maintenance period', 'DISPLAY_MAINTENANCE_PERIOD', 'false', 'Display Website maintenance period <br />(true=on false=off)<br />', 20, 12, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Website maintenance period', 'TEXT_MAINTENANCE_PERIOD_TIME', '2h00', 'Enter Website Maintenance period (hh:mm)', 20, 13, '2003-03-21 13:08:25', '2003-03-21 11:42:47', NULL, NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Confirm Terms and Conditions During Checkout Procedure', 'DISPLAY_CONDITIONS_ON_CHECKOUT', 'false', 'Show the Terms and Conditions during the checkout procedure which the customer must agree to.', '11', '1', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Confirm Privacy Notice During Account Creation Procedure', 'DISPLAY_PRIVACY_CONDITIONS', 'false', 'Show the Privacy Notice during the account creation procedure which the customer must agree to.', '11', '2', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_NEW_LIST_IMAGE', '1102', 'Do you want to display the Product Image?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_NEW_LIST_QUANTITY', '1202', 'Do you want to display the Product Quantity?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Buy Now Button', 'PRODUCT_NEW_BUY_NOW', '1300', 'Do you want to display the Product Buy Now Button<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_NEW_LIST_NAME', '2101', 'Do you want to display the Product Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_NEW_LIST_MODEL', '2201', 'Do you want to display the Product Model?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufacturer Name','PRODUCT_NEW_LIST_MANUFACTURER', '2302', 'Do you want to display the Product Manufacturer Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price', 'PRODUCT_NEW_LIST_PRICE', '2402', 'Do you want to display the Product Price<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_NEW_LIST_WEIGHT', '2502', 'Do you want to display the Product Weight?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Date Added', 'PRODUCT_NEW_LIST_DATE_ADDED', '2601', 'Do you want to display the Product Date Added?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '21', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Description', 'PRODUCT_NEW_LIST_DESCRIPTION', '150', 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', '21', '10', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Product Display - Default Sort Order', 'PRODUCT_NEW_LIST_SORT_DEFAULT', '6', 'What Sort Order Default should be used for New Products Display?<br />Default= 6 for Date New to Old<br /><br />1= Products Name<br />2= Products Name Desc<br />3= Price low to high, Products Name<br />4= Price high to low, Products Name<br />5= Model<br />6= Date Added desc<br />7= Date Added<br />8= Product Sort Order', '21', '11', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Products New Group ID', 'PRODUCT_NEW_LIST_GROUP_ID', '21', 'Warning: Only change this if your Products New Group ID has changed from the default of 21<br />What is the configuration_group_id for New Products Listings?', '21', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_NEW_LISTING_MULTIPLE_ADD_TO_CART', '3', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '21', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Mask Upcoming Products from being include as New Products', 'SHOW_NEW_PRODUCTS_UPCOMING_MASKED', '0', 'Do you want to mask Upcoming Products from being included as New Products in Listing, Sideboxes and Centerbox?<br />0= off<br />1= on', '21', '30', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_FEATURED_LIST_IMAGE', '1102', 'Do you want to display the Product Image?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_FEATURED_LIST_QUANTITY', '1202', 'Do you want to display the Product Quantity?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Buy Now Button', 'PRODUCT_FEATURED_BUY_NOW', '1300', 'Do you want to display the Product Buy Now Button<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_FEATURED_LIST_NAME', '2101', 'Do you want to display the Product Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_FEATURED_LIST_MODEL', '2201', 'Do you want to display the Product Model?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufacturer Name','PRODUCT_FEATURED_LIST_MANUFACTURER', '2302', 'Do you want to display the Product Manufacturer Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price', 'PRODUCT_FEATURED_LIST_PRICE', '2402', 'Do you want to display the Product Price<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_FEATURED_LIST_WEIGHT', '2502', 'Do you want to display the Product Weight?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Date Added', 'PRODUCT_FEATURED_LIST_DATE_ADDED', '2601', 'Do you want to display the Product Date Added?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '22', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Description', 'PRODUCT_FEATURED_LIST_DESCRIPTION', '150', 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', '22', '10', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Product Display - Default Sort Order', 'PRODUCT_FEATURED_LIST_SORT_DEFAULT', '1', 'What Sort Order Default should be used for Featured Product Display?<br />Default= 1 for Product Name<br /><br />1= Products Name<br />2= Products Name Desc<br />3= Price low to high, Products Name<br />4= Price high to low, Products Name<br />5= Model<br />6= Date Added desc<br />7= Date Added<br />8= Product Sort Order', '22', '11', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Featured Products Group ID', 'PRODUCT_FEATURED_LIST_GROUP_ID', '22', 'Warning: Only change this if your Featured Products Group ID has changed from the default of 22<br />What is the configuration_group_id for Featured Products Listings?', '22', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART', '3', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '22', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_ALL_LIST_IMAGE', '1102', 'Do you want to display the Product Image?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '1', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_ALL_LIST_QUANTITY', '1202', 'Do you want to display the Product Quantity?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '2', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Buy Now Button', 'PRODUCT_ALL_BUY_NOW', '1300', 'Do you want to display the Product Buy Now Button<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '3', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_ALL_LIST_NAME', '2101', 'Do you want to display the Product Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '4', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_ALL_LIST_MODEL', '2201', 'Do you want to display the Product Model?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '5', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufacturer Name','PRODUCT_ALL_LIST_MANUFACTURER', '2302', 'Do you want to display the Product Manufacturer Name?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '6', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price', 'PRODUCT_ALL_LIST_PRICE', '2402', 'Do you want to display the Product Price<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '7', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_ALL_LIST_WEIGHT', '2502', 'Do you want to display the Product Weight?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '8', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Date Added', 'PRODUCT_ALL_LIST_DATE_ADDED', '2601', 'Do you want to display the Product Date Added?<br /><br />0= off<br />1st digit Left or Right<br />2nd and 3rd digit Sort Order<br />4th digit number of breaks after<br />', '23', '9', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Description', 'PRODUCT_ALL_LIST_DESCRIPTION', '150', 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', '23', '10', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Product Display - Default Sort Order', 'PRODUCT_ALL_LIST_SORT_DEFAULT', '1', 'What Sort Order Default should be used for All Products Display?<br />Default= 1 for Product Name<br /><br />1= Products Name<br />2= Products Name Desc<br />3= Price low to high, Products Name<br />4= Price high to low, Products Name<br />5= Model<br />6= Date Added desc<br />7= Date Added<br />8= Product Sort Order', '23', '11', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Products All Group ID', 'PRODUCT_ALL_LIST_GROUP_ID', '23', 'Warning: Only change this if your Products All Group ID has changed from the default of 23<br />What is the configuration_group_id for Products All Listings?', '23', '12', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART', '3', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '23', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products on Main Page', 'SHOW_PRODUCT_INFO_MAIN_NEW_PRODUCTS', '1', 'Show New Products on Main Page<br />0= off or set the sort order', '24', '65', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products on Main Page', 'SHOW_PRODUCT_INFO_MAIN_FEATURED_PRODUCTS', '2', 'Show Featured Products on Main Page<br />0= off or set the sort order', '24', '66', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products on Main Page', 'SHOW_PRODUCT_INFO_MAIN_SPECIALS_PRODUCTS', '3', 'Show Special Products on Main Page<br />0= off or set the sort order', '24', '67', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products on Main Page', 'SHOW_PRODUCT_INFO_MAIN_UPCOMING', '4', 'Show Upcoming Products on Main Page<br />0= off or set the sort order', '24', '68', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products on Main Page - Category with SubCategories', 'SHOW_PRODUCT_INFO_CATEGORY_NEW_PRODUCTS', '1', 'Show New Products on Main Page - Category with SubCategories<br />0= off or set the sort order', '24', '70', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products on Main Page - Category with SubCategories', 'SHOW_PRODUCT_INFO_CATEGORY_FEATURED_PRODUCTS', '2', 'Show Featured Products on Main Page - Category with SubCategories<br />0= off or set the sort order', '24', '71', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products on Main Page - Category with SubCategories', 'SHOW_PRODUCT_INFO_CATEGORY_SPECIALS_PRODUCTS', '3', 'Show Special Products on Main Page - Category with SubCategories<br />0= off or set the sort order', '24', '72', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products on Main Page - Category with SubCategories', 'SHOW_PRODUCT_INFO_CATEGORY_UPCOMING', '4', 'Show Upcoming Products on Main Page - Category with SubCategories<br />0= off or set the sort order', '24', '73', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products on Main Page - Errors and Missing Products Page', 'SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS', '1', 'Show New Products on Main Page - Errors and Missing Product<br />0= off or set the sort order', '24', '75', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products on Main Page - Errors and Missing Products Page', 'SHOW_PRODUCT_INFO_MISSING_FEATURED_PRODUCTS', '2', 'Show Featured Products on Main Page - Errors and Missing Product<br />0= off or set the sort order', '24', '76', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products on Main Page - Errors and Missing Products Page', 'SHOW_PRODUCT_INFO_MISSING_SPECIALS_PRODUCTS', '3', 'Show Special Products on Main Page - Errors and Missing Product<br />0= off or set the sort order', '24', '77', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products on Main Page - Errors and Missing Products Page', 'SHOW_PRODUCT_INFO_MISSING_UPCOMING', '4', 'Show Upcoming Products on Main Page - Errors and Missing Product<br />0= off or set the sort order', '24', '78', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products - below Product Listing', 'SHOW_PRODUCT_INFO_LISTING_BELOW_NEW_PRODUCTS', '1', 'Show New Products below Product Listing<br />0= off or set the sort order', '24', '85', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products - below Product Listing', 'SHOW_PRODUCT_INFO_LISTING_BELOW_FEATURED_PRODUCTS', '2', 'Show Featured Products below Product Listing<br />0= off or set the sort order', '24', '86', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products - below Product Listing', 'SHOW_PRODUCT_INFO_LISTING_BELOW_SPECIALS_PRODUCTS', '3', 'Show Special Products below Product Listing<br />0= off or set the sort order', '24', '87', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products - below Product Listing', 'SHOW_PRODUCT_INFO_LISTING_BELOW_UPCOMING', '4', 'Show Upcoming Products below Product Listing<br />0= off or set the sort order', '24', '88', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('New Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS', '3', 'New Products Columns per Row', '24', '95', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Featured Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS', '3', 'Featured Products Columns per Row', '24', '96', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Special Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS', '3', 'Special Products Columns per Row', '24', '97', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Filter Product Listing for Current Top Level Category When Enabled', 'SHOW_PRODUCT_INFO_ALL_PRODUCTS', '1', 'Filter the products when Product Listing is enabled for current Main Category or show products from all categories?<br />0= Filter Off 1=Filter On ', '24', '100', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

#Define Page Status
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Main Page Status', 'DEFINE_MAIN_PAGE_STATUS', '1', 'Enable the Defined Main Page Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '60', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Contact Us Status', 'DEFINE_CONTACT_US_STATUS', '1', 'Enable the Defined Contact Us Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '61', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Privacy Status', 'DEFINE_PRIVACY_STATUS', '1', 'Enable the Defined Privacy Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '62', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Shipping & Returns', 'DEFINE_SHIPPINGINFO_STATUS', '1', 'Enable the Defined Shipping & Returns Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '63', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Conditions of Use', 'DEFINE_CONDITIONS_STATUS', '1', 'Enable the Defined Conditions of Use Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '64', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Checkout Success', 'DEFINE_CHECKOUT_SUCCESS_STATUS', '1', 'Enable the Defined Checkout Success Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '65', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Discount Coupon', 'DEFINE_DISCOUNT_COUPON_STATUS', '1', 'Enable the Defined Discount Coupon Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '66', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Site Map Status', 'DEFINE_SITE_MAP_STATUS', '1', 'Enable the Defined Site Map Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '67', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Page-Not-Found Status', 'DEFINE_PAGE_NOT_FOUND_STATUS', '1', 'Enable the Defined Page-Not-Found Text from define-pages?<br />0= Define Text OFF<br />1= Define Text ON', '25', '67', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Page 2', 'DEFINE_PAGE_2_STATUS', '1', 'Enable the Defined Page 2 Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '82', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Page 3', 'DEFINE_PAGE_3_STATUS', '1', 'Enable the Defined Page 3 Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '83', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Page 4', 'DEFINE_PAGE_4_STATUS', '1', 'Enable the Defined Page 4 Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '84', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');

#EZ-Pages settings
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('EZ-Pages Display Status - HeaderBar', 'EZPAGES_STATUS_HEADER', '1', 'Display of EZ-Pages content can be Globally enabled/disabled for the Header Bar<br />0 = Off<br />1 = On<br />2= On ADMIN IP ONLY located in Website Maintenance<br />NOTE: Warning only shows to the Admin and not to the public', 30, 10, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('EZ-Pages Display Status - FooterBar', 'EZPAGES_STATUS_FOOTER', '1', 'Display of EZ-Pages content can be Globally enabled/disabled for the Footer Bar<br />0 = Off<br />1 = On<br />2= On ADMIN IP ONLY located in Website Maintenance<br />NOTE: Warning only shows to the Admin and not to the public', 30, 11, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('EZ-Pages Display Status - Sidebox', 'EZPAGES_STATUS_SIDEBOX', '1', 'Display of EZ-Pages content can be Globally enabled/disabled for the Sidebox<br />0 = Off<br />1 = On<br />2= On ADMIN IP ONLY located in Website Maintenance<br />NOTE: Warning only shows to the Admin and not to the public', 30, 12, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Header Link Separator', 'EZPAGES_SEPARATOR_HEADER', '&nbsp;::&nbsp;', 'EZ-Pages Header Link Separator<br />Default = &amp;nbsp;::&amp;nbsp;', 30, 20, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Footer Link Separator', 'EZPAGES_SEPARATOR_FOOTER', '&nbsp;::&nbsp;', 'EZ-Pages Footer Link Separator<br />Default = &amp;nbsp;::&amp;nbsp;', 30, 21, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('EZ-Pages Prev/Next Buttons', 'EZPAGES_SHOW_PREV_NEXT_BUTTONS', '2', 'Display Prev/Continue/Next buttons on EZ-Pages pages?<br />0=OFF (no buttons)<br />1="Continue"<br />2="Prev/Continue/Next"<br /><br />Default setting: 2.', 30, 30, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Table of Contents for Chapters Status', 'EZPAGES_SHOW_TABLE_CONTENTS', '1', 'Enable EZ-Pages Table of Contents for Chapters?<br />0= OFF<br />1= ON', 30, 35, now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Pages to disable headers', 'EZPAGES_DISABLE_HEADER_DISPLAY_LIST', '', 'EZ-Pages "pages" on which to NOT display the normal "header" for your site.<br />Simply list page ID numbers separated by commas with no spaces.<br />Page ID numbers can be obtained from the EZ-Pages screen under Admin->Tools.<br />ie: 1,5,2<br />or leave blank.', 30, 40, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Pages to disable footers', 'EZPAGES_DISABLE_FOOTER_DISPLAY_LIST', '', 'EZ-Pages "pages" on which to NOT display the normal "footer" for your site.<br />Simply list page ID numbers separated by commas with no spaces.<br />Page ID numbers can be obtained from the EZ-Pages screen under Admin->Tools.<br />ie: 3,7<br />or leave blank.', 30, 41, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Pages to disable left-column', 'EZPAGES_DISABLE_LEFTCOLUMN_DISPLAY_LIST', '', 'EZ-Pages "pages" on which to NOT display the normal "left" column (of sideboxes) for your site.<br />Simply list page ID numbers separated by commas with no spaces.<br />Page ID numbers can be obtained from the EZ-Pages screen under Admin->Tools.<br />ie: 21<br />or leave blank.', 30, 42, NULL, now(), NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('EZ-Pages Pages to disable right-column', 'EZPAGES_DISABLE_RIGHTCOLUMN_DISPLAY_LIST', '', 'EZ-Pages "pages" on which to NOT display the normal "right" column (of sideboxes) for your site.<br />Simply list page ID numbers separated by commas with no spaces.<br />Page ID numbers can be obtained from the EZ-Pages screen under Admin->Tools.<br />ie: 3,82,13<br />or leave blank.', 30, 43, NULL, now(), NULL, 'zen_cfg_textarea_small(');

#global auth key
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('global auth key', 'GLOBAL_AUTH_KEY', '', '', 6, 30, now(), now(), NULL, NULL);


INSERT INTO configuration_group VALUES (1, 'My Store', 'General information about my store', '1', '1');
INSERT INTO configuration_group VALUES (2, 'Minimum Values', 'The minimum values for functions / data', '2', '1');
INSERT INTO configuration_group VALUES (3, 'Maximum Values', 'The maximum values for functions / data', '3', '1');
INSERT INTO configuration_group VALUES (4, 'Images', 'Image parameters', '4', '1');
INSERT INTO configuration_group VALUES (5, 'Customer Details', 'Customer account configuration', '5', '1');
INSERT INTO configuration_group VALUES (6, 'Module Options', 'Hidden from configuration', '6', '0');
INSERT INTO configuration_group VALUES (7, 'Shipping/Packaging', 'Shipping options available at my store', '7', '1');
INSERT INTO configuration_group VALUES (8, 'Product Listing', 'Product Listing configuration options', '8', '1');
INSERT INTO configuration_group VALUES (9, 'Stock', 'Stock configuration options', '9', '1');
INSERT INTO configuration_group VALUES (10, 'Logging', 'Logging configuration options', '10', '1');
INSERT INTO configuration_group VALUES (11, 'Regulations', 'Regulation options', '16', '1');
INSERT INTO configuration_group VALUES (12, 'Email', 'Email-related settings', '12', '1');
INSERT INTO configuration_group VALUES (13, 'Attribute Settings', 'Configure products attributes settings', '13', '1');
INSERT INTO configuration_group VALUES (14, 'GZip Compression', 'GZip compression options', '14', '1');
INSERT INTO configuration_group VALUES (15, 'Sessions', 'Session options', '15', '1');
INSERT INTO configuration_group VALUES (16, 'GV Coupons', 'Gift Vouchers and Coupons', '16', '1');
INSERT INTO configuration_group VALUES (17, 'Credit Cards', 'Credit Cards Accepted', '17', '1');
INSERT INTO configuration_group VALUES (18, 'Product Info', 'Product Info Display Options', '18', '1');
INSERT INTO configuration_group VALUES (19, 'Layout Settings', 'Layout Options', '19', '1');
INSERT INTO configuration_group VALUES (20, 'Website Maintenance', 'Website Maintenance Options', '20', '1');
INSERT INTO configuration_group VALUES (21, 'New Listing', 'New Products Listing', '21', '1');
INSERT INTO configuration_group VALUES (22, 'Featured Listing', 'Featured Products Listing', '22', '1');
INSERT INTO configuration_group VALUES (23, 'All Listing', 'All Products Listing', '23', '1');
INSERT INTO configuration_group VALUES (24, 'Index Listing', 'Index Products Listing', '24', '1');
INSERT INTO configuration_group VALUES (25, 'Define Page Status', 'Define Pages Options Settings', '25', '1');
INSERT INTO configuration_group VALUES (30, 'EZ-Pages Settings', 'EZ-Pages Settings', 30, '1');

INSERT INTO currencies VALUES (1,'US Dollar','USD','$','','.',',','2','1.0000', now());
INSERT INTO currencies VALUES (2,'Euro','EUR','&euro;','','.',',','2','0.7730', now());
INSERT INTO currencies VALUES (3,'GB Pound','GBP','&pound;','','.',',','2','0.6726', now());
INSERT INTO currencies VALUES (4,'Canadian Dollar','CAD','$','','.',',','2','1.1042', now());
INSERT INTO currencies VALUES (5,'Australian Dollar','AUD','$','','.',',','2','1.1789', now());
#INSERT INTO currencies VALUES (6,'Japanese Yen','JPY','&yen;','','','','0','95.5927', now());

INSERT INTO languages VALUES (1,'English','en','icon.gif','english',1);

INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'banner_box_all.php', 1, 1, 5, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'banner_box.php', 1, 0, 300, 1, 127);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'banner_box2.php', 1, 1, 15, 1, 15);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'best_sellers.php', 1, 1, 30, 70, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'categories.php', 1, 0, 10, 10, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'currencies.php', 0, 1, 80, 60, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'document_categories.php', 1, 0, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'ezpages.php', 1, 1, -1, 2, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'featured.php', 1, 0, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'information.php', 1, 0, 50, 40, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'languages.php', 0, 1, 70, 50, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'manufacturers.php', 1, 0, 30, 20, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'manufacturer_info.php', 1, 1, 35, 95, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'more_information.php', 1, 0, 200, 200, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'music_genres.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'order_history.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'product_notifications.php', 1, 1, 55, 85, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'record_companies.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'reviews.php', 1, 0, 40, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'search.php', 1, 1, 10, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'search_header.php', 0, 0, 0, 0, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'shopping_cart.php', 1, 1, 20, 30, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'specials.php', 1, 1, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'whats_new.php', 1, 0, 20, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'whos_online.php', 1, 1, 200, 200, 1);

INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'banner_box_all.php', 1, 1, 5, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'banner_box.php', 1, 0, 300, 1, 127);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'banner_box2.php', 1, 1, 15, 1, 15);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'best_sellers.php', 1, 1, 30, 70, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'categories.php', 1, 0, 10, 10, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'currencies.php', 0, 1, 80, 60, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'ezpages.php', 1, 1, -1, 2, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'featured.php', 1, 0, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'information.php', 1, 0, 50, 40, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'languages.php', 0, 1, 70, 50, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'manufacturers.php', 1, 0, 30, 20, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'manufacturer_info.php', 1, 1, 35, 95, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'more_information.php', 1, 0, 200, 200, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'my_broken_box.php', 1, 0, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'order_history.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'product_notifications.php', 1, 1, 55, 85, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'reviews.php', 1, 0, 40, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'search.php', 1, 1, 10, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'search_header.php', 0, 0, 0, 0, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'shopping_cart.php', 1, 1, 20, 30, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'specials.php', 1, 1, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'whats_new.php', 1, 0, 20, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('template_default', 'whos_online.php', 1, 1, 200, 200, 1);

INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'banner_box.php', 1, 0, 300, 1, 127);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'banner_box2.php', 1, 1, 15, 1, 15);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'banner_box_all.php', 1, 1, 5, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'best_sellers.php', 1, 1, 30, 70, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'categories.php', 1, 0, 10, 10, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'currencies.php', 0, 1, 80, 60, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'document_categories.php', 1, 0, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'ezpages.php', 1, 1, -1, 2, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'featured.php', 1, 0, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'information.php', 1, 0, 50, 40, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'languages.php', 0, 1, 70, 50, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'manufacturers.php', 1, 0, 30, 20, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'manufacturer_info.php', 1, 1, 35, 95, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'more_information.php', 1, 0, 200, 200, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'music_genres.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'order_history.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'product_notifications.php', 1, 1, 55, 85, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'record_companies.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'reviews.php', 1, 0, 40, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'search.php', 1, 1, 10, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'search_header.php', 0, 0, 0, 0, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'shopping_cart.php', 1, 1, 20, 30, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'specials.php', 1, 1, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'whats_new.php', 1, 0, 20, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('classic', 'whos_online.php', 1, 1, 200, 200, 1);

INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'banner_box.php', 1, 0, 300, 1, 127);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'banner_box2.php', 1, 1, 15, 1, 15);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'banner_box_all.php', 1, 1, 5, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'best_sellers.php', 1, 1, 30, 70, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'categories.php', 1, 0, 10, 10, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'currencies.php', 0, 1, 80, 60, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'document_categories.php', 1, 0, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'ezpages.php', 1, 1, -1, 2, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'featured.php', 1, 0, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'information.php', 1, 0, 50, 40, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'languages.php', 0, 1, 70, 50, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'manufacturers.php', 1, 0, 30, 20, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'manufacturer_info.php', 1, 1, 35, 95, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'more_information.php', 1, 0, 200, 200, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'music_genres.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'order_history.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'product_notifications.php', 1, 1, 55, 85, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'record_companies.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'reviews.php', 1, 0, 40, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'search.php', 1, 1, 10, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'search_header.php', 0, 0, 0, 0, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'shopping_cart.php', 1, 1, 20, 30, 1);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'specials.php', 1, 1, 45, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'whats_new.php', 1, 0, 20, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('responsive_classic', 'whos_online.php', 1, 1, 200, 200, 1);

INSERT INTO orders_status VALUES ( '1', '1', 'Pending', 0);
INSERT INTO orders_status VALUES ( '2', '1', 'Processing', 10);
INSERT INTO orders_status VALUES ( '3', '1', 'Delivered', 20);
INSERT INTO orders_status VALUES ( '4', '1', 'Update', 30);

INSERT INTO product_types VALUES (1, 'Product - General', 'product', '1', 'Y', '', now(), now());
INSERT INTO product_types VALUES (2, 'Product - Music', 'product_music', '1', 'Y', '', now(), now());
INSERT INTO product_types VALUES (3, 'Document - General', 'document_general', '3', 'N', '', now(), now());
INSERT INTO product_types VALUES (4, 'Document - Product', 'document_product', '3', 'Y', '', now(), now());
INSERT INTO product_types VALUES (5, 'Product - Free Shipping', 'product_free_shipping', '1', 'Y', '', now(), now());

INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (0, 'Dropdown');
INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (1, 'Text');
INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (2, 'Radio');
INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (3, 'Checkbox');
INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (4, 'File');
INSERT INTO products_options_types (products_options_types_id, products_options_types_name) VALUES (5, 'Read Only');

INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES (0, 1, 'TEXT');

# USA/Florida
INSERT INTO tax_rates VALUES (1, 1, 1, 1, 7.0, 'FL TAX 7.0%', now(), now());
INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) VALUES (1,'Florida','Florida local sales tax zone',now());
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (1,223,18,1,now());
INSERT INTO tax_class (tax_class_id, tax_class_title, tax_class_description, date_added) VALUES (1, 'Taxable Goods', 'The following types of products are included: non-food, services, etc', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Model Number', 'SHOW_PRODUCT_INFO_MODEL', '1', 'Display Model Number on Product Info 0= off 1= on', '1', '1', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Weight', 'SHOW_PRODUCT_INFO_WEIGHT', '1', 'Display Weight on Product Info 0= off 1= on', '1', '2', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Attribute Weight', 'SHOW_PRODUCT_INFO_WEIGHT_ATTRIBUTES', '1', 'Display Attribute Weight on Product Info 0= off 1= on', '1', '3', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Manufacturer', 'SHOW_PRODUCT_INFO_MANUFACTURER', '1', 'Display Manufacturer Name on Product Info 0= off 1= on', '1', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Shopping Cart', 'SHOW_PRODUCT_INFO_IN_CART_QTY', '1', 'Display Quantity in Current Shopping Cart on Product Info 0= off 1= on', '1', '5', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Stock', 'SHOW_PRODUCT_INFO_QUANTITY', '1', 'Display Quantity in Stock on Product Info 0= off 1= on', '1', '6', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Count', 'SHOW_PRODUCT_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info 0= off 1= on', '1', '7', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Button', 'SHOW_PRODUCT_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info 0= off 1= on', '1', '8', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Available', 'SHOW_PRODUCT_INFO_DATE_AVAILABLE', '1', 'Display Date Available on Product Info 0= off 1= on', '1', '9', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Added', 'SHOW_PRODUCT_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info 0= off 1= on', '1', '10', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product URL', 'SHOW_PRODUCT_INFO_URL', '1', 'Display URL on Product Info 0= off 1= on', '1', '11', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Additional Images', 'SHOW_PRODUCT_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info 0= off 1= on', '1', '13', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Starting At text on Price', 'SHOW_PRODUCT_INFO_STARTING_AT', '1', 'Display Starting At text on products with attributes Product Info 0= off 1= on', '1', '12', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Image Status - Catalog', 'SHOW_PRODUCT_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', '0', 'Show the Free Shipping image/text in the catalog?', '1', '16', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
#admin defaults
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, use_function, set_function, date_added) VALUES ('Product Price Tax Class Default - When adding new products?', 'DEFAULT_PRODUCT_TAX_CLASS_ID', '0', 'What should the Product Price Tax Class Default ID be when adding new products?', '1', '100', '', '', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Virtual Default Status - Skip Shipping Address - When adding new products?', 'DEFAULT_PRODUCT_PRODUCTS_VIRTUAL', '0', 'Default Virtual Product status to be ON when adding new products?', '1', '101', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Default Status - Normal Shipping Rules - When adding new products?', 'DEFAULT_PRODUCT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', '0', 'What should the Default Free Shipping status be when adding new products?<br />Yes, Always Free Shipping ON<br />No, Always Free Shipping OFF<br />Special, Product/Download Requires Shipping', '1', '102', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'), array(\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'), array(\'id\'=>\'2\', \'text\'=>\'Special\')), ', now());


INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Model Number', 'SHOW_PRODUCT_MUSIC_INFO_MODEL', '1', 'Display Model Number on Product Info 0= off 1= on', '2', '1', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Weight', 'SHOW_PRODUCT_MUSIC_INFO_WEIGHT', '0', 'Display Weight on Product Info 0= off 1= on', '2', '2', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Attribute Weight', 'SHOW_PRODUCT_MUSIC_INFO_WEIGHT_ATTRIBUTES', '1', 'Display Attribute Weight on Product Info 0= off 1= on', '2', '3', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Artist', 'SHOW_PRODUCT_MUSIC_INFO_ARTIST', '1', 'Display Artists Name on Product Info 0= off 1= on', '2', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Music Genre', 'SHOW_PRODUCT_MUSIC_INFO_GENRE', '1', 'Display Music Genre on Product Info 0= off 1= on', '2', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Record Company', 'SHOW_PRODUCT_MUSIC_INFO_RECORD_COMPANY', '1', 'Display Record Company on Product Info 0= off 1= on', '2', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Shopping Cart', 'SHOW_PRODUCT_MUSIC_INFO_IN_CART_QTY', '1', 'Display Quantity in Current Shopping Cart on Product Info 0= off 1= on', '2', '5', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Stock', 'SHOW_PRODUCT_MUSIC_INFO_QUANTITY', '0', 'Display Quantity in Stock on Product Info 0= off 1= on', '2', '6', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Count', 'SHOW_PRODUCT_MUSIC_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info 0= off 1= on', '2', '7', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Button', 'SHOW_PRODUCT_MUSIC_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info 0= off 1= on', '2', '8', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Available', 'SHOW_PRODUCT_MUSIC_INFO_DATE_AVAILABLE', '1', 'Display Date Available on Product Info 0= off 1= on', '2', '9', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Added', 'SHOW_PRODUCT_MUSIC_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info 0= off 1= on', '2', '10', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Starting At text on Price', 'SHOW_PRODUCT_MUSIC_INFO_STARTING_AT', '1', 'Display Starting At text on products with attributes Product Info 0= off 1= on', '2', '12', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Additional Images', 'SHOW_PRODUCT_MUSIC_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info 0= off 1= on', '2', '13', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Image Status - Catalog', 'SHOW_PRODUCT_MUSIC_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', '0', 'Show the Free Shipping image/text in the catalog?', '2', '16', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
#admin defaults
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, use_function, set_function, date_added) VALUES ('Product Price Tax Class Default - When adding new products?', 'DEFAULT_PRODUCT_MUSIC_TAX_CLASS_ID', '0', 'What should the Product Price Tax Class Default ID be when adding new products?', '2', '100', '', '', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Virtual Default Status - Skip Shipping Address - When adding new products?', 'DEFAULT_PRODUCT_MUSIC_PRODUCTS_VIRTUAL', '0', 'Default Virtual Product status to be ON when adding new products?', '2', '101', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Default Status - Normal Shipping Rules - When adding new products?', 'DEFAULT_PRODUCT_MUSIC_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', '0', 'What should the Default Free Shipping status be when adding new products?<br />Yes, Always Free Shipping ON<br />No, Always Free Shipping OFF<br />Special, Product/Download Requires Shipping', '2', '102', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'), array(\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'), array(\'id\'=>\'2\', \'text\'=>\'Special\')), ', now());


INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Count', 'SHOW_DOCUMENT_GENERAL_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info 0= off 1= on', '3', '7', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Button', 'SHOW_DOCUMENT_GENERAL_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info 0= off 1= on', '3', '8', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Available', 'SHOW_DOCUMENT_GENERAL_INFO_DATE_AVAILABLE', '1', 'Display Date Available on Product Info 0= off 1= on', '3', '9', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Added', 'SHOW_DOCUMENT_GENERAL_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info 0= off 1= on', '3', '10', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product URL', 'SHOW_DOCUMENT_GENERAL_INFO_URL', '1', 'Display URL on Product Info 0= off 1= on', '3', '11', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Additional Images', 'SHOW_DOCUMENT_GENERAL_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info 0= off 1= on', '3', '13', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

#admin defaults


INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Model Number', 'SHOW_DOCUMENT_PRODUCT_INFO_MODEL', '1', 'Display Model Number on Product Info 0= off 1= on', '4', '1', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Weight', 'SHOW_DOCUMENT_PRODUCT_INFO_WEIGHT', '0', 'Display Weight on Product Info 0= off 1= on', '4', '2', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Attribute Weight', 'SHOW_DOCUMENT_PRODUCT_INFO_WEIGHT_ATTRIBUTES', '1', 'Display Attribute Weight on Product Info 0= off 1= on', '4', '3', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Manufacturer', 'SHOW_DOCUMENT_PRODUCT_INFO_MANUFACTURER', '1', 'Display Manufacturer Name on Product Info 0= off 1= on', '4', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Shopping Cart', 'SHOW_DOCUMENT_PRODUCT_INFO_IN_CART_QTY', '1', 'Display Quantity in Current Shopping Cart on Product Info 0= off 1= on', '4', '5', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Stock', 'SHOW_DOCUMENT_PRODUCT_INFO_QUANTITY', '0', 'Display Quantity in Stock on Product Info 0= off 1= on', '4', '6', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Count', 'SHOW_DOCUMENT_PRODUCT_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info 0= off 1= on', '4', '7', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Button', 'SHOW_DOCUMENT_PRODUCT_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info 0= off 1= on', '4', '8', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Available', 'SHOW_DOCUMENT_PRODUCT_INFO_DATE_AVAILABLE', '1', 'Display Date Available on Product Info 0= off 1= on', '4', '9', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Added', 'SHOW_DOCUMENT_PRODUCT_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info 0= off 1= on', '4', '10', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product URL', 'SHOW_DOCUMENT_PRODUCT_INFO_URL', '1', 'Display URL on Product Info 0= off 1= on', '4', '11', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Additional Images', 'SHOW_DOCUMENT_PRODUCT_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info 0= off 1= on', '4', '13', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());


INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Starting At text on Price', 'SHOW_DOCUMENT_PRODUCT_INFO_STARTING_AT', '1', 'Display Starting At text on products with attributes Product Info 0= off 1= on', '4', '12', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Image Status - Catalog', 'SHOW_DOCUMENT_PRODUCT_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', '0', 'Show the Free Shipping image/text in the catalog?', '4', '16', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
#admin defaults
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, use_function, set_function, date_added) VALUES ('Product Price Tax Class Default - When adding new products?', 'DEFAULT_DOCUMENT_PRODUCT_TAX_CLASS_ID', '0', 'What should the Product Price Tax Class Default ID be when adding new products?', '4', '100', '', '', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Virtual Default Status - Skip Shipping Address - When adding new products?', 'DEFAULT_DOCUMENT_PRODUCT_PRODUCTS_VIRTUAL', '0', 'Default Virtual Product status to be ON when adding new products?', '4', '101', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Default Status - Normal Shipping Rules - When adding new products?', 'DEFAULT_DOCUMENT_PRODUCT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', '0', 'What should the Default Free Shipping status be when adding new products?<br />Yes, Always Free Shipping ON<br />No, Always Free Shipping OFF<br />Special, Product/Download Requires Shipping', '4', '102', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'), array(\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'), array(\'id\'=>\'2\', \'text\'=>\'Special\')), ', now());


INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Model Number', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_MODEL', '1', 'Display Model Number on Product Info 0= off 1= on', '5', '1', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Weight', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_WEIGHT', '0', 'Display Weight on Product Info 0= off 1= on', '5', '2', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Attribute Weight', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_WEIGHT_ATTRIBUTES', '1', 'Display Attribute Weight on Product Info 0= off 1= on', '5', '3', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Manufacturer', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_MANUFACTURER', '1', 'Display Manufacturer Name on Product Info 0= off 1= on', '5', '4', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Shopping Cart', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_IN_CART_QTY', '1', 'Display Quantity in Current Shopping Cart on Product Info 0= off 1= on', '5', '5', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Quantity in Stock', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_QUANTITY', '1', 'Display Quantity in Stock on Product Info 0= off 1= on', '5', '6', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Count', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info 0= off 1= on', '5', '7', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Reviews Button', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info 0= off 1= on', '5', '8', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Available', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_DATE_AVAILABLE', '0', 'Display Date Available on Product Info 0= off 1= on', '5', '9', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Date Added', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info 0= off 1= on', '5', '10', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product URL', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_URL', '1', 'Display URL on Product Info 0= off 1= on', '5', '11', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Product Additional Images', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info 0= off 1= on', '5', '13', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Starting At text on Price', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_STARTING_AT', '1', 'Display Starting At text on products with attributes Product Info 0= off 1= on', '5', '12', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Image Status - Catalog', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', '1', 'Show the Free Shipping image/text in the catalog?', '5', '16', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
#admin defaults
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, use_function, set_function, date_added) VALUES ('Product Price Tax Class Default - When adding new products?', 'DEFAULT_PRODUCT_FREE_SHIPPING_TAX_CLASS_ID', '0', 'What should the Product Price Tax Class Default ID be when adding new products?', '5', '100', '', '', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Virtual Default Status - Skip Shipping Address - When adding new products?', 'DEFAULT_PRODUCT_FREE_SHIPPING_PRODUCTS_VIRTUAL', '0', 'Default Virtual Product status to be ON when adding new products?', '5', '101', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product Free Shipping Default Status - Normal Shipping Rules - When adding new products?', 'DEFAULT_PRODUCT_FREE_SHIPPING_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', '1', 'What should the Default Free Shipping status be when adding new products?<br />Yes, Always Free Shipping ON<br />No, Always Free Shipping OFF<br />Special, Product/Download Requires Shipping', '5', '102', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'), array(\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'), array(\'id\'=>\'2\', \'text\'=>\'Special\')), ', now());

#insert product type layout settings for meta-tags
#Product
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Name', 'SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '1', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Title Additional Text', 'SHOW_PRODUCT_INFO_METATAGS_TITLE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Title Additional text in the page &lt;title&gt; tag.', '1', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Model', 'SHOW_PRODUCT_INFO_METATAGS_MODEL_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Model in the page &lt;title&gt; tag.', '1', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Price', 'SHOW_PRODUCT_INFO_METATAGS_PRICE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Price in the page &lt;title&gt; tag.', '1', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use SITE_TAGLINE', 'SHOW_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the defined constant "SITE_TAGLINE" in the page &lt;title&gt; tag.', '1', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
#Product Music
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Name', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '2', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Title Additional Text', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '2', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Model', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_MODEL_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '2', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Price', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRICE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '2', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use SITE_TAGLINE', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '2', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
#Document General
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Document page &lt;title&gt; tag - default: use Document Title', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Title in the page &lt;title&gt; tag.', '3', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Document page &lt;title&gt; tag - default: use Document Name', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Name in the page &lt;title&gt; tag.', '3', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Tagline', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Tagline in the page &lt;title&gt; tag.', '3', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
#Document Product
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Title', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Title in the page &lt;title&gt; tag.', '4', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Name', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Name in the page &lt;title&gt; tag.', '4', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Model', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_MODEL_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Model in the page &lt;title&gt; tag.', '4', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Price', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRICE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Price in the page &lt;title&gt; tag.', '4', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Document Tagline', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Document  Tagline in the page &lt;title&gt; tag.', '4', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
#Product Free Shipping
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Name', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.', '5', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Title Additional Text', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Title Additional text in the page &lt;title&gt; tag.', '5', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Model', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_MODEL_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Model in the page &lt;title&gt; tag.', '5', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use Product Price', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRICE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the Product Price in the page &lt;title&gt; tag.', '5', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Product page &lt;title&gt; tag - default: use SITE_TAGLINE', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Default setting for a new product (can be modified per product).<br>Show the defined constant "SITE_TAGLINE" in the page &lt;title&gt; tag.', '5', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
### eof: meta tags database updates and changes

#insert product type layout settings
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Display Only - Default', 'DEFAULT_PRODUCT_ATTRIBUTES_DISPLAY_ONLY', '0', 'PRODUCT Attribute is Display Only<br />Used For Display Purposes Only<br />0= No 1= Yes', '1', '200', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Free - Default', 'DEFAULT_PRODUCT_ATTRIBUTE_IS_FREE', '1', 'PRODUCT Attribute is Free<br />Attribute is Free When Product is Free<br />0= No 1= Yes', '1', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Default - Default', 'DEFAULT_PRODUCT_ATTRIBUTES_DEFAULT', '0', 'PRODUCT Attribute is Default<br />Default Attribute to be Marked Selected<br />0= No 1= Yes', '1', '202', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Discounted - Default', 'DEFAULT_PRODUCT_ATTRIBUTES_DISCOUNTED', '1', 'PRODUCT Attribute is Discounted<br />Apply Discounts Used by Product Special/Sale<br />0= No 1= Yes', '1', '203', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Included in Base Price - Default', 'DEFAULT_PRODUCT_ATTRIBUTES_PRICE_BASE_INCLUDED', '1', 'PRODUCT Attribute is Included in Base Price<br />Include in Base Price When Priced by Attributes<br />0= No 1= Yes', '1', '204', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute is Required - Default', 'DEFAULT_PRODUCT_ATTRIBUTES_REQUIRED', '0', 'PRODUCT Attribute is Required<br />Attribute Required for Text<br />0= No 1= Yes', '1', '205', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute Price Prefix - Default', 'DEFAULT_PRODUCT_PRICE_PREFIX', '1', 'PRODUCT Attribute Price Prefix<br />Default Attribute Price Prefix for Adding<br />Blank, + or -', '1', '206', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT Attribute Weight Prefix - Default', 'DEFAULT_PRODUCT_PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', '1', 'PRODUCT Attribute Weight Prefix<br />Default Attribute Weight Prefix<br />Blank, + or -', '1', '207', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Display Only - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTES_DISPLAY_ONLY', '0', 'MUSIC Attribute is Display Only<br />Used For Display Purposes Only<br />0= No 1= Yes', '2', '200', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Free - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTE_IS_FREE', '1', 'MUSIC Attribute is Free<br />Attribute is Free When Product is Free<br />0= No 1= Yes', '2', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Default - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTES_DEFAULT', '0', 'MUSIC Attribute is Default<br />Default Attribute to be Marked Selected<br />0= No 1= Yes', '2', '202', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Discounted - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTES_DISCOUNTED', '1', 'MUSIC Attribute is Discounted<br />Apply Discounts Used by Product Special/Sale<br />0= No 1= Yes', '2', '203', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Included in Base Price - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTES_PRICE_BASE_INCLUDED', '1', 'MUSIC Attribute is Included in Base Price<br />Include in Base Price When Priced by Attributes<br />0= No 1= Yes', '2', '204', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute is Required - Default', 'DEFAULT_PRODUCT_MUSIC_ATTRIBUTES_REQUIRED', '0', 'MUSIC Attribute is Required<br />Attribute Required for Text<br />0= No 1= Yes', '2', '205', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute Price Prefix - Default', 'DEFAULT_PRODUCT_MUSIC_PRICE_PREFIX', '1', 'MUSIC Attribute Price Prefix<br />Default Attribute Price Prefix for Adding<br />Blank, + or -', '2', '206', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('MUSIC Attribute Weight Prefix - Default', 'DEFAULT_PRODUCT_MUSIC_PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', '1', 'MUSIC Attribute Weight Prefix<br />Default Attribute Weight Prefix<br />Blank, + or -', '2', '207', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Display Only - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTES_DISPLAY_ONLY', '0', 'DOCUMENT GENERAL Attribute is Display Only<br />Used For Display Purposes Only<br />0= No 1= Yes', '3', '200', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Free - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTE_IS_FREE', '1', 'DOCUMENT GENERAL Attribute is Free<br />Attribute is Free When Product is Free<br />0= No 1= Yes', '3', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Default - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTES_DEFAULT', '0', 'DOCUMENT GENERAL Attribute is Default<br />Default Attribute to be Marked Selected<br />0= No 1= Yes', '3', '202', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Discounted - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTES_DISCOUNTED', '1', 'DOCUMENT GENERAL Attribute is Discounted<br />Apply Discounts Used by Product Special/Sale<br />0= No 1= Yes', '3', '203', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Included in Base Price - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTES_PRICE_BASE_INCLUDED', '1', 'DOCUMENT GENERAL Attribute is Included in Base Price<br />Include in Base Price When Priced by Attributes<br />0= No 1= Yes', '3', '204', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute is Required - Default', 'DEFAULT_DOCUMENT_GENERAL_ATTRIBUTES_REQUIRED', '0', 'DOCUMENT GENERAL Attribute is Required<br />Attribute Required for Text<br />0= No 1= Yes', '3', '205', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute Price Prefix - Default', 'DEFAULT_DOCUMENT_GENERAL_PRICE_PREFIX', '1', 'DOCUMENT GENERAL Attribute Price Prefix<br />Default Attribute Price Prefix for Adding<br />Blank, + or -', '3', '206', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT GENERAL Attribute Weight Prefix - Default', 'DEFAULT_DOCUMENT_GENERAL_PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', '1', 'DOCUMENT GENERAL Attribute Weight Prefix<br />Default Attribute Weight Prefix<br />Blank, + or -', '3', '207', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Display Only - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTES_DISPLAY_ONLY', '0', 'DOCUMENT PRODUCT Attribute is Display Only<br />Used For Display Purposes Only<br />0= No 1= Yes', '4', '200', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Free - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTE_IS_FREE', '1', 'DOCUMENT PRODUCT Attribute is Free<br />Attribute is Free When Product is Free<br />0= No 1= Yes', '4', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Default - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTES_DEFAULT', '0', 'DOCUMENT PRODUCT Attribute is Default<br />Default Attribute to be Marked Selected<br />0= No 1= Yes', '4', '202', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Discounted - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTES_DISCOUNTED', '1', 'DOCUMENT PRODUCT Attribute is Discounted<br />Apply Discounts Used by Product Special/Sale<br />0= No 1= Yes', '4', '203', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Included in Base Price - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTES_PRICE_BASE_INCLUDED', '1', 'DOCUMENT PRODUCT Attribute is Included in Base Price<br />Include in Base Price When Priced by Attributes<br />0= No 1= Yes', '4', '204', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute is Required - Default', 'DEFAULT_DOCUMENT_PRODUCT_ATTRIBUTES_REQUIRED', '0', 'DOCUMENT PRODUCT Attribute is Required<br />Attribute Required for Text<br />0= No 1= Yes', '4', '205', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute Price Prefix - Default', 'DEFAULT_DOCUMENT_PRODUCT_PRICE_PREFIX', '1', 'DOCUMENT PRODUCT Attribute Price Prefix<br />Default Attribute Price Prefix for Adding<br />Blank, + or -', '4', '206', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('DOCUMENT PRODUCT Attribute Weight Prefix - Default', 'DEFAULT_DOCUMENT_PRODUCT_PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', '1', 'DOCUMENT PRODUCT Attribute Weight Prefix<br />Default Attribute Weight Prefix<br />Blank, + or -', '4', '207', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Display Only - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTES_DISPLAY_ONLY', '0', 'PRODUCT FREE SHIPPING Attribute is Display Only<br />Used For Display Purposes Only<br />0= No 1= Yes', '5', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Free - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTE_IS_FREE', '1', 'PRODUCT FREE SHIPPING Attribute is Free<br />Attribute is Free When Product is Free<br />0= No 1= Yes', '5', '201', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Default - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTES_DEFAULT', '0', 'PRODUCT FREE SHIPPING Attribute is Default<br />Default Attribute to be Marked Selected<br />0= No 1= Yes', '5', '202', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Discounted - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTES_DISCOUNTED', '1', 'PRODUCT FREE SHIPPING Attribute is Discounted<br />Apply Discounts Used by Product Special/Sale<br />0= No 1= Yes', '5', '203', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Included in Base Price - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTES_PRICE_BASE_INCLUDED', '1', 'PRODUCT FREE SHIPPING Attribute is Included in Base Price<br />Include in Base Price When Priced by Attributes<br />0= No 1= Yes', '5', '204', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute is Required - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_ATTRIBUTES_REQUIRED', '0', 'PRODUCT FREE SHIPPING Attribute is Required<br />Attribute Required for Text<br />0= No 1= Yes', '5', '205', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'Yes\'), array(\'id\'=>\'0\', \'text\'=>\'No\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute Price Prefix - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_PRICE_PREFIX', '1', 'PRODUCT FREE SHIPPING Attribute Price Prefix<br />Default Attribute Price Prefix for Adding<br />Blank, + or -', '5', '206', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('PRODUCT FREE SHIPPING Attribute Weight Prefix - Default', 'DEFAULT_PRODUCT_FREE_SHIPPING_PRODUCTS_ATTRIBUTES_WEIGHT_PREFIX', '1', 'PRODUCT FREE SHIPPING Attribute Weight Prefix<br />Default Attribute Weight Prefix<br />Blank, + or -', '5', '207', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Blank\'), array(\'id\'=>\'1\', \'text\'=>\'+\'), array(\'id\'=>\'2\', \'text\'=>\'-\')), ', now());
### eof: attribute default database updates and changes

### bof: Add control to enable/disable the display of the 'Ask a Question' block for each product type
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 1, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_MUSIC_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 2, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_DOCUMENT_GENERAL_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 3, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_DOCUMENT_PRODUCT_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 4, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 5, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
### eof: Add control to enable/disable the display of the 'Ask a Question' block for each product type


## Insert the default queries for "all customers" and "all newsletter subscribers"
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '1', 'email', 'All Customers', 'Returns all customers name and email address for sending mass emails (ie: for newsletters, coupons, GVs, messages, etc).', 'select customers_email_address, customers_firstname, customers_lastname from TABLE_CUSTOMERS order by customers_lastname, customers_firstname, customers_email_address', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '2', 'email,newsletters', 'All Newsletter Subscribers', 'Returns name and email address of newsletter subscribers', 'select customers_firstname, customers_lastname, customers_email_address from TABLE_CUSTOMERS where customers_newsletter = \'1\'', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '3', 'email,newsletters', 'Dormant Customers (>3months) (Subscribers)', 'Subscribers who HAVE purchased something, but have NOT purchased for at least three months.', 'select max(o.date_purchased) as date_purchased, c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id AND c.customers_newsletter = 1 GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname HAVING max(o.date_purchased) <= subdate(now(),INTERVAL 3 MONTH) ORDER BY c.customers_lastname, c.customers_firstname ASC', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '4', 'email,newsletters', 'Active customers in past 3 months (Subscribers)', 'Newsletter subscribers who are also active customers (purchased something) in last 3 months.', 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o where c.customers_newsletter = \'1\' AND c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '5', 'email,newsletters', 'Active customers in past 3 months (Regardless of subscription status)', 'All active customers (purchased something) in last 3 months, ignoring newsletter-subscription status.', 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '6', 'email,newsletters', 'Administrator', 'Just the email account of the current administrator', 'select \'ADMIN\' as customers_firstname, admin_name as customers_lastname, admin_email as customers_email_address from TABLE_ADMIN where admin_id = $SESSION:admin_id', '');
INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string , query_keys_list ) VALUES ( '7', 'email,newsletters', 'Customers who have never completed a purchase', 'For sending newsletter to all customers who registered but have never completed a purchase', 'SELECT DISTINCT c.customers_email_address as customers_email_address, c.customers_lastname as customers_lastname, c.customers_firstname as customers_firstname FROM TABLE_CUSTOMERS c LEFT JOIN  TABLE_ORDERS o ON c.customers_id=o.customers_id WHERE o.date_purchased IS NULL', '');

#
# end of Query-Builder Setup
#

#
# Dumping data for table get_terms_to_filter
#

INSERT INTO get_terms_to_filter VALUES ('manufacturers_id', 'TABLE_MANUFACTURERS', 'manufacturers_name');
INSERT INTO get_terms_to_filter VALUES ('music_genre_id', 'TABLE_MUSIC_GENRE', 'music_genre_name');
INSERT INTO get_terms_to_filter VALUES ('record_company_id', 'TABLE_RECORD_COMPANY', 'record_company_name');

#
# Dumping data for table project_version
#

INSERT INTO project_version (project_version_id, project_version_key, project_version_major, project_version_minor, project_version_patch1, project_version_patch1_source, project_version_patch2, project_version_patch2_source, project_version_comment, project_version_date_applied) VALUES (1, 'Zen-Cart Main', '1', '5.7', '', '', '', '', 'New Installation-v157', now());
INSERT INTO project_version (project_version_id, project_version_key, project_version_major, project_version_minor, project_version_patch1, project_version_patch1_source, project_version_patch2, project_version_patch2_source, project_version_comment, project_version_date_applied) VALUES (2, 'Zen-Cart Database', '1', '5.7', '', '', '', '', 'New Installation-v157', now());
INSERT INTO project_version_history (project_version_id, project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_comment, project_version_date_applied) VALUES (1, 'Zen-Cart Main', '1', '5.7', '', 'New Installation-v157', now());
INSERT INTO project_version_history (project_version_id, project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_comment, project_version_date_applied) VALUES (2, 'Zen-Cart Database', '1', '5.7', '', 'New Installation-v157', now());

##### End of SQL setup for Zen Cart.
