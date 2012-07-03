# The following commands are used to upgrade the Zen Cart v1.1.0 or v1.1.1 database structure to v1.1.2 format.
#
# $Id: mysql_upgrade_zencart_110_to_112.sql 4243 2006-08-24 10:55:28Z drbyte $
#

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) 
VALUES ('Store the Credit Card Number', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'False', 'Do you want to store the Credit Card Number. Note: The Credit Card Number will be stored unenecrypted, and as such may represent a security problem', 6, 0, NULL, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'),');


## END OF UDPATE