#
# The following commands are used to upgrade the Zen Cart v1.1.2 or v1.1.3 database structure to v1.1.4 format.
# $Id: mysql_upgrade_zencart_112_to_114.sql 4243 2006-08-24 10:55:28Z drbyte $
#

ALTER TABLE customers_basket_attributes CHANGE COLUMN products_options_sort_order products_options_sort_order TEXT NOT NULL;
Update configuration set configuration_title = 'Package Tare Small to Medium - added percentage:weight' where configuration_key= 'SHIPPING_BOX_WEIGHT';
Update configuration set configuration_description= 'What is the weight of typical packaging of small to medium packages?<br />Example: 10% + 1lb 10:1<br />10% + 0lbs 10:0<br />0% + 5lbs 0:5<br />0% + 0lbs 0:0' where configuration_key= 'SHIPPING_BOX_WEIGHT';
Update configuration set configuration_title = 'Larger packages - added packaging percentage:weight' where configuration_key= 'SHIPPING_BOX_PADDING';
Update configuration set configuration_description= 'What is the weight of typical packaging for Large packages?<br />Example: 10% + 1lb 10:1<br />10% + 0lbs 10:0<br />0% + 5lbs 0:5<br />0% + 0lbs 0:0' where configuration_key= 'SHIPPING_BOX_PADDING';

## END OF UPDATE
