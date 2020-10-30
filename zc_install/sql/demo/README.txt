Notes on Demo Data:

mysql_demo.sql can be rerun after demo_cleanup.sql is used if you are
tweaking the demo data and want to reload each time (without going through a
full re-install). 

Note that mysql_demo.sql should be used from the command line only, 
not from Admin > Tools > MySQL Patches (the lines are too long). 

demo_cleanup.sql deletes ALL of records in the 
tables listed below.  It is intended for developers of demo data only.
In particular, it should not be used if you have begun adding real
data on top of demo data, because it removes data indiscriminately. 

address_book 
categories
categories_description
customers
customers_info 
ezpages 
ezpages_content
featured 
group_pricing 
manufacturers 
manufacturers_info 
media_clips 
media_manager 
media_to_products
music_genre 
product_music_extra 
product_types_to_category 
products 
products_attributes 
products_attributes_download 
products_description 
products_discount_quantity 
products_options 
products_options_values 
products_options_values_to_products_options 
products_to_categories 
record_artists 
record_artists_info 
record_company 
record_company_info 
reviews 
reviews_description 
salemaker_sales 
specials 

