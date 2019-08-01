UPDATE admin SET pwd_last_change_date = '0001-01-01 00:00:00' WHERE pwd_last_change_date < '0001-01-01' and pwd_last_change_date is not null;
UPDATE admin SET last_modified = '0001-01-01 00:00:00' WHERE last_modified < '0001-01-01' and last_modified is not null;
UPDATE admin SET last_login_date = '0001-01-01 00:00:00' WHERE last_login_date < '0001-01-01' and last_login_date is not null;
UPDATE admin SET last_failed_attempt = '0001-01-01 00:00:00' WHERE last_failed_attempt < '0001-01-01' and last_failed_attempt is not null;
UPDATE admin_activity_log SET access_date = '0001-01-01 00:00:00' WHERE access_date < '0001-01-01' and access_date is not null;
UPDATE banners SET expires_date = NULL WHERE expires_date < '0001-01-01';
UPDATE banners SET date_scheduled = NULL WHERE date_scheduled < '0001-01-01';
UPDATE banners SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE banners SET date_status_change = NULL WHERE date_status_change < '0001-01-01';
UPDATE banners_history SET banners_history_date = '0001-01-01 00:00:00' WHERE banners_history_date < '0001-01-01' and banners_history_date is not null;
UPDATE categories SET date_added = NULL WHERE date_added < '0001-01-01';
UPDATE categories SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE configuration SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE configuration SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE coupon_email_track SET date_sent = '0001-01-01 00:00:00' WHERE date_sent < '0001-01-01' and date_sent is not null;
UPDATE coupon_gv_queue SET date_created = '0001-01-01 00:00:00' WHERE date_created < '0001-01-01' and date_created is not null;
UPDATE coupon_redeem_track SET redeem_date = '0001-01-01 00:00:00' WHERE redeem_date < '0001-01-01' and redeem_date is not null;
UPDATE coupons SET coupon_start_date = '0001-01-01 00:00:00' WHERE coupon_start_date < '0001-01-01' and coupon_start_date is not null;
UPDATE coupons SET coupon_expire_date = '0001-01-01 00:00:00' WHERE coupon_expire_date < '0001-01-01' and coupon_expire_date is not null;
UPDATE coupons SET date_created = '0001-01-01 00:00:00' WHERE date_created < '0001-01-01' and date_created is not null;
UPDATE coupons SET date_modified = '0001-01-01 00:00:00' WHERE date_modified < '0001-01-01' and date_modified is not null;
UPDATE currencies SET last_updated = NULL WHERE last_updated < '0001-01-01';
UPDATE customers SET customers_dob = '0001-01-01 00:00:00' WHERE customers_dob < '0001-01-01' and customers_dob is not null;
UPDATE customers_info SET customers_info_date_of_last_logon = NULL WHERE customers_info_date_of_last_logon < '0001-01-01';
UPDATE customers_info SET customers_info_date_account_created = NULL WHERE customers_info_date_account_created < '0001-01-01';
UPDATE customers_info SET customers_info_date_account_last_modified = NULL WHERE customers_info_date_account_last_modified < '0001-01-01';
UPDATE email_archive SET date_sent = '0001-01-01 00:00:00' WHERE date_sent < '0001-01-01' and date_sent is not null;
UPDATE featured SET featured_date_added = NULL WHERE featured_date_added < '0001-01-01';
UPDATE featured SET featured_last_modified = NULL WHERE featured_last_modified < '0001-01-01';
UPDATE featured SET expires_date = '0001-01-01' WHERE expires_date < '0001-01-01' and expires_date is not null;
UPDATE featured SET date_status_change = NULL WHERE date_status_change < '0001-01-01';
UPDATE featured SET featured_date_available = '0001-01-01' WHERE featured_date_available < '0001-01-01' and featured_date_available is not null;
UPDATE geo_zones SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE geo_zones SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE group_pricing SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE group_pricing SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE manufacturers SET date_added = NULL WHERE date_added < '0001-01-01';
UPDATE manufacturers SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE manufacturers_info SET date_last_click = NULL WHERE date_last_click < '0001-01-01';
UPDATE newsletters SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE newsletters SET date_sent = NULL WHERE date_sent < '0001-01-01';
UPDATE orders SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE orders SET date_purchased = NULL WHERE date_purchased < '0001-01-01';
UPDATE orders SET orders_date_finished = NULL WHERE orders_date_finished < '0001-01-01';
UPDATE orders_status_history SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE paypal SET payment_date = '0001-01-01 00:00:00' WHERE payment_date < '0001-01-01' and payment_date is not null;
UPDATE paypal SET last_modified = '0001-01-01 00:00:00' WHERE last_modified < '0001-01-01' and last_modified is not null;
UPDATE paypal SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE paypal_payment_status_history SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE paypal_testing SET payment_date = '0001-01-01 00:00:00' WHERE payment_date < '0001-01-01' and payment_date is not null;
UPDATE paypal_testing SET last_modified = '0001-01-01 00:00:00' WHERE last_modified < '0001-01-01' and last_modified is not null;
UPDATE paypal_testing SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE product_type_layout SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE product_type_layout SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE product_types SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE product_types SET last_modified = '0001-01-01 00:00:00' WHERE last_modified < '0001-01-01' and last_modified is not null;
UPDATE products SET products_date_added = '0001-01-01 00:00:00' WHERE products_date_added < '0001-01-01' and products_date_added is not null;
UPDATE products SET products_last_modified = NULL WHERE products_last_modified < '0001-01-01';
UPDATE products SET products_date_available = NULL WHERE products_date_available < '0001-01-01';
UPDATE products_notifications SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE project_version SET project_version_date_applied = '0001-01-01 01:01:01' WHERE project_version_date_applied < '0001-01-01' and project_version_date_applied is not null;
UPDATE project_version_history SET project_version_date_applied = '0001-01-01 01:01:01' WHERE project_version_date_applied < '0001-01-01' and project_version_date_applied is not null;
UPDATE reviews SET date_added = NULL WHERE date_added < '0001-01-01';
UPDATE reviews SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE salemaker_sales SET sale_date_start = '0001-01-01' WHERE sale_date_start < '0001-01-01' and sale_date_start is not null;
UPDATE salemaker_sales SET sale_date_end = '0001-01-01' WHERE sale_date_end < '0001-01-01' and sale_date_end is not null;
UPDATE salemaker_sales SET sale_date_added = '0001-01-01' WHERE sale_date_added < '0001-01-01' and sale_date_added is not null;
UPDATE salemaker_sales SET sale_date_last_modified = '0001-01-01' WHERE sale_date_last_modified < '0001-01-01' and sale_date_last_modified is not null;
UPDATE salemaker_sales SET sale_date_status_change = '0001-01-01' WHERE sale_date_status_change < '0001-01-01' and sale_date_status_change is not null;
UPDATE specials SET specials_date_added = NULL WHERE specials_date_added < '0001-01-01';
UPDATE specials SET specials_last_modified = NULL WHERE specials_last_modified < '0001-01-01';
UPDATE specials SET expires_date = '0001-01-01' WHERE expires_date < '0001-01-01' and expires_date is not null;
UPDATE specials SET date_status_change = NULL WHERE date_status_change < '0001-01-01';
UPDATE specials SET specials_date_available = '0001-01-01' WHERE specials_date_available < '0001-01-01' and specials_date_available is not null;
UPDATE tax_class SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE tax_class SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE tax_rates SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE tax_rates SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;
UPDATE upgrade_exceptions SET errordate = NULL WHERE errordate < '0001-01-01';
UPDATE zones_to_geo_zones SET last_modified = NULL WHERE last_modified < '0001-01-01';
UPDATE zones_to_geo_zones SET date_added = '0001-01-01 00:00:00' WHERE date_added < '0001-01-01' and date_added is not null;

