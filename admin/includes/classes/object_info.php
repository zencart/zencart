<?php

/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 04 Modified in v1.5.8-alpha $
 */

/**
 * Class objectInfo
 */
class objectInfo
{
    /*
     * Start of parameter declaration
     * 
     * Each module calling this class has a section with the parameters used by that module.
     * The parameters are sorted alphabetically for ease of reading.
     * Any parameters that have been previously declared are still shown commented out 
     * with the place they were first declared.
     */
// for banner_manager
    public
        $banners_clicked,
        $banners_group,
        $banners_html_text,
        $banners_id,
        $banners_image,
        $banners_image_local,
        $banners_image_target,
        $banners_on_ssl,
        $banners_open_new_windows,
        $banners_shown,
        $banners_sort_order,
        $banners_title,
        $banners_url,
        $bID,
        $date_added,
        $date_scheduled,
        $date_status_change,
        $delete_image,
        $expires_date,
        $expires_impressions,
        $new_banners_group,
        $status;

    // for categories, category_product_listing
    public 
        $categories_id,
        $categories_image,
        $categories_name,
        $categories_status,
        $parent_id,
        $sort_order;
    
    // for configuration
    public
        $configuration_description,
        $configuration_id,
        $configuration_key,
        $configuration_title,
        $configuration_value,
//        $date_added,    declared in banner_manager
        $last_modified,
        $set_function,
        $use_function;

    // for countries
    public
        $countries_id,
        $countries_name,
        $countries_iso_code_2,
        $countries_iso_code_3,
        $address_format_id;
//        $status;    declared in banner_manager
    
    //for coupon_admin
    public
        $coupon_active,
        $coupon_amount,
        $coupon_calc_base,
        $coupon_code,
        $coupon_expire_date,
        $coupon_id,
        $coupon_is_valid_for_sales,
        $coupon_minimum_order,
        $coupon_order_limit,
        $coupon_product_count,
        $coupon_start_date,
        $coupon_type,
        $coupon_zone_restriction,
        $date_created,
        $date_modified,
        $restrict_to_categories,
        $restrict_to_customers,
        $restrict_to_products,
        $uses_per_coupon,
        $uses_per_user;
    
    //for currencies
    public
        $code,
        $currencies_id,
        $decimal_places,
        $decimal_point,
        $last_updated,
        $symbol_left,
        $symbol_right,
        $thousands_point,
        $title,
        $value;
    
    // for customer_groups
    public
        $customer_count,
//        $date_added,   declared in banner_manager
        $group_comment,
        $group_id,
        $group_name,
        $updated_at;
    
    //for customers
    public
        $address_book_id,
        $addresses,
        $cID,
        $city,
        $company,
        $country_id,
        $country_iso,
        $country_name,
        $current_status,
        $customers_authorization,
        $customers_default_address_id,
        $customers_dob,
        $customers_email_address,
        $customers_email_format,
        $customers_fax,
        $customers_firstname,
        $customers_gender,
        $customers_group_pricing,
        $customer_groups,
        $customers_id,
        $customers_lastname,
        $customers_newsletter,
        $customers_nick,
        $customers_paypal_ec,
        $customers_paypal_payerid,
        $customers_referral,
        $customers_secret,
        $customers_telephone,
        $date_account_created,
        $date_account_last_modified,
        $date_of_last_login,
        $default_address_id,
        $delete_type_forget,
        $delete_reviews,
        $entry_city,
        $entry_company,
        $entry_country_id,
        $entry_postcode,
        $entry_state,
        $entry_street_address,
        $entry_suburb,
        $entry_zone_id,
        $firstname,
        $gv_balance,
        $last_login_ip,
        $last_order,
        $lastname,
        $lifetime_value,
        $newpassword,
        $newpasswordConfirm,
        $number_of_logins,
        $number_of_orders,
        $number_of_reviews,
        $postcode,
        $pricing_group_discount_percentage,
        $pricing_group_name,
        $registration_ip,
        $state,
        $street_address,
        $suburb,
        $zone_id,
        $zone_iso,
        $zone_name;

    // for downloads_manager
    public
        $options_id,
        $options_values_id,
        $product_is_always_free_shipping,
        $products_attributes_filename,
        $products_attributes_id,
        $products_attributes_maxcount,
        $products_attributes_maxdays,
        $products_id,
        $products_model,
        $products_name,
        $products_virtual;

    //for ezpages
    public
        $alt_url_external,
        $alt_url,
        $ezID,
        $fieldName,
        $footer_sort_order,
        $header_sort_order,
        $new_status,
        $page_is_ssl,
        $page_open_new_window,
        $pages_html_text,
        $pages_id,
        $pages_title,
        $sidebox_sort_order,
        $status_footer,
        $status_header,
        $status_sidebox,
        $status_toc,
        $status_visible,
        $toc_chapter,
        $toc_sort_order;
    
    //for featured
    public
//        $date_status_change,   declared in banner_manager
//        $expires_date,   declared in banner_manager
        $featured_date_added,
        $featured_date_available,
        $featured_id,
        $featured_last_modified,
//        $products_id,   declared in downloads_manager
        $products_image,
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manager
        $products_price,
        $products_priced_by_attribute,
        $products_quantity;
//        $status;    declared in banner_manager

    // for geo_zones
    public
//        $date_added,    declared in categories
        $geo_zone_description,
        $geo_zone_id,
        $geo_zone_name,
//        $last_modified,    declared in configuration
        $num_tax_rates,
        $num_zones;

    // for group_pricing
    public 
//        $customer_count,    declared in customer_groups
//        $date_added,   declared in banner_manager
//        $group_id,    declared in customer_groups
//        $group_name,    declared in customer_groups
        $group_percentage;
//        $last_modified,    declared in configuration

    // for gv_queue
    public 
        $amount,
//        $customers_firstname,    declared in customers
//        $customers_lastname,    declared in customers
//        $date_created,    declared in coupon_admin
        $order_id,
        $unique_id;
    
    //for gv_sent
    public 
//        $coupon_amount,    declared in coupon_admin
//        $coupon_code,    declared in coupon_admin
//        $coupon_id,    declared in coupon_admin
        $customer_id_sent,
        $date_sent,
        $emailed_to,
        $redeem_date,
        $sent_firstname,
        $sent_lastname;
    
    //for modules/copy_product
    public 
        $master_categories_id,
//        $products_id,   declared in downloads_manager
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manager
//        $products_price,   declared in featured
//        $products_quantity,   declared in featured
        $products_sort_order,
        $products_status,
        $products_type;
        
    //for modules/.../collect_info
    public
        $artists_id, // only required for music
        $categories_description,
//        $categories_id,    declared in categories
//        $categories_image,    declared in categories
//        $categories_name,    declared in categories
//       $categories_status,    declared in categories
//        $date_added,    declared in categories
        $language_id,
//        $last_modified,    declared in configuration
        $manufacturers_id,
//        $master_categories_id,   declared in copy_product
        $metatags_model_status,
        $metatags_price_status,
        $metatags_products_name_status,
        $metatags_title_status,
        $metatags_title_tagline_status,
        $music_genre_id, // only required for music
//        $parent_id,    declared in categories
//        $product_is_always_free_shipping,   declared in downloads_manager
        $product_is_call,
        $product_is_free,
        $products_date_added,
        $products_date_available,
        $products_description,
        $products_discount_type_from,
        $products_discount_type,
//        $products_id,   declared in downloads_manager
//        $products_image,   declared in fearured
        $products_last_modified,
        $products_mixed_discount_quantity,
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manager
        $products_ordered,
        $products_price_sorter,
//        $products_price,   declared in featured
//        $products_priced_by_attribute,   declared in featured
        $products_qty_box_status,
        $products_quantity_mixed,
        $products_quantity_order_max,
        $products_quantity_order_min,
        $products_quantity_order_units,
//        $products_quantity,   declared in featured
//        $products_sort_order,   declared in copy_product
//        $products_status,   declared in copy_product
        $products_tax_class_id,
//        $products_type,   declared in copy_product
        $products_url,
//        $products_virtual,    declared in banner_manager
        $products_weight,
        $record_company_id, // only required for music
        $search;
//        $sort_order;    declared in banner_manager

    // for modules/.../collect_info_metatags and preview_info_meta_tags
    public 
        $metatags_description,
        $metatags_keywords,
//        $metatags_model_status,    declared in modules/.../collect_info
//        $metatags_price_status,    declared in modules/.../collect_info
//        $metatags_products_name_status,    declared in modules/.../collect_info
//        $metatags_title_status,    declared in modules/.../collect_info
//        $metatags_title_tagline_status,    declared in modules/.../collect_info
        $metatags_title,
//        $products_id,   declared in downloads_manager - only required for collect_info
//        $products_model,   declared in downloads_manager  - only required for collect_info
//        $products_name,   declared in downloads_manager
//        $products_price_sorter;   declared in downloads_manager
          $securityToken; // only required for preview
        
    // for modules/.../preview_info
    public
//        $artists_id,    declared in modules/.../collect_info - only required for music
        $image_delete,
        $img_dir,
//        $manufacturers_id,    declared in modules/.../collect_info
//        $master_categories_id,   declared in copy_product
        $master_category, // only needed for document_product
        $overwrite,
//        $product_is_always_free_shipping,   declared in downloads_manager
//        $product_is_call,    declared in modules/.../collect_info
//        $product_is_free,    declared in modules/.../collect_info
        $product_type,
//        $products_date_added,    declared in modules/.../collect_info
//        $products_date_available,    declared in modules/.../collect_info
//        $products_description,    declared in modules/.../collect_info
//        $products_discount_type_from,    declared in modules/.../collect_info
//        $products_discount_type,    declared in modules/.../collect_info
        $products_image_manual,
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manager
        $products_previous_image,
        $products_price_gross;
//        $products_price_sorter,    declared in modules/.../collect_info
//        $products_price,   declared in featured
//        $products_priced_by_attribute,   declared in featured
//        $products_qty_box_status,    declared in modules/.../collect_info
//        $products_quantity_mixed,    declared in modules/.../collect_info
//        $products_quantity_order_max,    declared in modules/.../collect_info
//        $products_quantity_order_min,    declared in modules/.../collect_info
//        $products_quantity_order_units,    declared in modules/.../collect_info
//        $products_quantity,   declared in featured
//        $products_sort_order,   declared in copy_product
//        $products_status,   declared in copy_product
//        $products_tax_class_id,    declared in modules/.../collect_info
//        $products_url,    declared in modules/.../collect_info
//        $products_virtual,    declared in banner_manager
//        $products_weight,    declared in modules/.../collect_info
//        $record_company_id,    declared in modules/.../collect_info - only required for music
//        $securityToken,    declared in modules/.../collect_info_metatags and preview_info_meta_tags
//        $search,    declared in modules/.../collect_info    
         
    // for languages
    public
//        $code,    declared in currencies
        $directory,
        $image,
        $languages_id,
        $name;
//        $sort_order;    declared in banner_manager

    // for manufactures
    public 
//        $date_added,    declared in banner_manager
        $featured,
//        $last_modified,    declared in configuration
//        $manufacturers_id,    declared in modules/.../collect_info
        $manufacturers_image,
        $manufacturers_name,
        $products_count,
        $weighted;
    
    // for media_manager
    public 
//        $date_added,    declared in banner_manager
//        $last_modified,    declared in configuration
        $media_id,
        $media_name;
    
    // for media_types
    public 
        $type_ext,
        $type_id,
        $type_name;
        
    //for modules
    public 
//        $code,    declared in currencies
        $description,
        $keys;
//        $status,    declared in banner_manager
//        $title;    declared in currencies
        
    // for music_genre
    public 
//        $date_added,    declared in banner_manager
//        $last_modified,    declared in configuration
//        $music_genre_id,    declared in modules/.../collect_info
        $music_genre_name;
//        $products_count;    declared in manufactures
    
    // for newsletter
    public 
        $content,
        $content_html,
        $content_html_length,
        $content_length,
//        $date_added,    declared in banner_manager
//        $date_sent,    defined in gv_sent
        $module,
        $newsletters_id;
//        $status,    declared in banner_manager
//        $title;    declared in currencies
    
    //for orders
    public 
        $billing_company,
        $billing_name,
        $billing_street_address,
        $currency_value,
        $currency,
        $customers_company,
//        $customers_email_address,    declared in customers
//        $customers_id,    declared in customers
        $customers_name,
        $customers_street_address,
        $date_purchased,
        $delivery_company,
        $delivery_country,
        $delivery_name,
        $delivery_state,
        $delivery_street_address,
        $ip_address,
        $language_code,
//        $last_modified,    declared in configuration
        $order_total,
        $orders_id,
        $orders_status_name,
        $orders_status,
        $payment_method,
        $payment_module_code,
        $shipping_method,
        $shipping_module_code;
    
    // for orders_status
    public 
//        $language_id,    declared in collect_info
        $orders_status_id;
//        $orders_status_name,    declared in orders
//        $sort_order;    declared in banner_manager
        
    // for paypal
    public 
//        $date_added,    declared in banner_manager
        $first_name,
        $last_name,
        $mc_currency,
        $mc_gross,
//        $order_id,    declared in gv_queue
        $parent_txn_id,
        $payer_business_name,
        $payer_status,
        $payment_status,
        $payment_type,
        $paypal_ipn_id,
        $pending_reason,
        $txn_id,
        $txn_type;
    
    // for product_types
    public 
        $allow_add_to_cart,
//        $date_added,    declared in banner_manager
        $default_image,
//        $last_modified,    declared in configuration
//        $products_count;    declared in manufactures
        $type_handler,
//        $type_id,    declared in media_types
        $type_master_type;
//        $type_name;    declared in media_types
    
    // for products_expected
 //   public 
//        $products_date_available,    declared in modules/.../collect_info
//        $products_id,   declared in downloads_manager - only required for collect_info
//        $products_name,   declared in downloads_manager
    
    // for products_price_manager
    public 
//        $expires_date,   declared in banner_manager
//        $featured_date_available,    declared in featured
//        $featured_id,    declared in featured
//        $master_categories_id,   declared in copy_product
//        $product_is_call,    declared in modules/.../collect_info
//        $product_is_free,    declared in modules/.../collect_info
//        $products_date_available,    declared in modules/.../collect_info
//        $products_discount_type_from,    declared in modules/.../collect_info
//        $products_discount_type,    declared in modules/.../collect_info
//        $products_id,   declared in downloads_manager 
//        $products_mixed_discount_quantity,    declared in modules/.../collect_info
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manager
//        $products_price_sorter,    declared in modules/.../collect_info
//        $products_price,   declared in featured
//        $products_priced_by_attribute,   declared in featured
//        $products_quantity_mixed,    declared in modules/.../collect_info
//        $products_quantity_order_max,    declared in modules/.../collect_info
//        $products_quantity_order_min,    declared in modules/.../collect_info
//        $products_quantity_order_units,    declared in modules/.../collect_info
//        $products_status,   declared in copy_product
//        $products_tax_class_id,    declared in modules/.../collect_info
        $specials_date_available,
        $specials_id,
        $specials_new_products_price;
//        $status;    declared in banner_manager
    
    // for record_artist
    public 
//        $artists_id,    declared in modules/.../collect_info
        $artists_image,
        $artists_name;
//        $date_added,    declared in banner_manager
//        $last_modified,    declared in configuration
//        $products_count;    declared in manufactures
        
    // for record_company
    public 
//        $record_company_id;    declared in modules/.../collect_info
        $record_company_name,
        $record_company_image;
//        $date_added,    declared in banner_manager
//        $last_modified,    declared in configuration
//        $products_count;    declared in manufactures        

    // for reviews
    public
        $average_rating,
//        $customers_name,    declared in customers
//        $date_added,    declared in banner_manager
        $flag,
//        $language_id,    declared in collect_info
//        $last_modified,    declared in configuration
//        $products_id,   declared in downloads_manager 
//        $products_image,   declared in fearured
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manage
        $rID,
        $reviews_id,
        $reviews_rating,
        $reviews_read,
        $reviews_text_size,
        $reviews_text;
//        $status;    declared in banner_manager

    // for salemaker
    public
        $sale_categories_all,
        $sale_categories_selected,
        $sale_date_added,
        $sale_date_end,
        $sale_date_last_modified,
        $sale_date_start,
        $sale_date_status_change,
        $sale_deduction_type,
        $sale_deduction_value,
        $sale_id,
        $sale_name,
        $sale_pricerange_from,
        $sale_pricerange_to,
        $sale_specials_condition,
        $sale_status;
    
    // for specials
    public 
//        $date_status_change,   declared in banner_manager
//        $expires_date,   declared in banner_manager
//        $products_id,   declared in downloads_manager 
//        $products_image,   declared in fearured
//        $products_model,   declared in downloads_manager
//        $products_name,   declared in downloads_manage
//        $products_price,   declared in featured
//        $products_priced_by_attribute,   declared in featured
//        $products_quantity,   declared in featured
        $specials_date_added,
//        $specials_date_available,   declared in products_price_manager
//        $specials_id,   declared in products_price_manager
        $specials_last_modified;
//        $specials_new_products_price,   declared in products_price_manager
//        $status;    declared in banner_manager
    // for tax_classes
    public 
//        $date_added,    declared in banner_manager
//        $last_modified,    declared in configuration
        $tax_class_description,
        $tax_class_id,
        $tax_class_title;
    
    // for tax_rates
    public 
//        $date_added,    declared in banner_manager
//        $geo_zone_id,    declared in geo_zones
//        $geo_zone_name,    declared in geo_zones
//        $last_modified,    declared in configuration
//        $tax_class_id,    declared in tax_classes
//        $tax_class_title,    declared in tax_classes
        $tax_description,
        $tax_priority,
        $tax_rate,
        $tax_rates_id;
    
    // for template_select
    public 
        $template_id,
        $template_dir,
        $template_language;
    
    // for zones
    public 
//        $zone_id,    declared in customers
//        $countries_id,    declared in countries
//        $countries_name,    declared in countries
//        $zone_name,    declared in customers
        $zone_code,
        $zone_country_id;  
    
    // General from $_POST
    public
        $cPath,
        $action;
    
/*
 * Still need method of allowing customisation fields to be included.
 */

    
/*
 * End of parameter declaration
 */    
    /**
     * @param $object_array
     */
    public function __construct($object_array)
    {
        $this->updateObjectInfo($object_array);
    }

    /**
     * @param $object_array array
     */
    public function objectInfo($object_array)
    {
        if (!is_array($object_array)) return;

        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
        $this->object_array = $object_array;
    }

    /**
     * @param $object_array array
     */
    public function updateObjectInfo($object_array)
    {
        if (!is_array($object_array)) return;

        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
    }

    public function __isset($field)
    {
        return isset($this->$field);
    }

    public function __set($field, $value)
    {
        $this->$field = $value;
    }

    /**
     * @param $field
     * @return array|string
     */
    public function __get($field)
    {
        if (isset($this->$field)) return $this->$field;

        if ($field == 'keys') return array();

        return null;
    }
}
