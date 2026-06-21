# `$tplSetting->` keys

131 unique keys. Grouped and sorted by `configuration_group_id` (Admin Configuration menu), sub-sorted by key. 


## Minimum Values (2)

| Key | Title | Default | Description |
|---|---|---|---|
| `MIN_DISPLAY_ALSO_PURCHASED` | Also Purchased Products | `1` | Minimum number of products to display in the 'Also Purchased' box |
| `MIN_DISPLAY_BESTSELLERS` | Best Sellers | `1` | Minimum number of best sellers to display |

## Maximum Values (3)

| Key | Title | Default | Description |
|---|---|---|---|
| `MAX_DISPLAY_ALSO_PURCHASED` | Also Purchased Products | `6` | Number of products to display in the 'Also Purchased' box |
| `MAX_DISPLAY_BESTSELLERS` | Best Sellers For Box | `10` | Number of best sellers to display in box |
| `MAX_DISPLAY_MANUFACTURER_NAME_LEN` | Length of Manufacturers Name | `15` | Used in manufacturers box; maximum length of manufacturers name to display. Longer names will be truncated. |
| `MAX_DISPLAY_MUSIC_GENRES_NAME_LEN` | Length of Music Genre Name | `15` | Used in music genres box; maximum length of music genre name to display. Longer names will be truncated. |
| `MAX_DISPLAY_NEW_REVIEWS` | New Product Reviews Per Page | `6` | Number of new reviews to display on each page |
| `MAX_DISPLAY_ORDER_HISTORY` | Customer Order History List Per Page | `10` | Number of orders to display in the order history list in 'My Account' |
| `MAX_DISPLAY_PAGE_LINKS` | Prev/Next Navigation Page Links (Desktop) | `5` | Number of numbered pagination links to display. |
| `MAX_DISPLAY_PAGE_LINKS_MOBILE` | Prev/Next Navigation Page Links (Mobile) | `3` | Number of numbered pagination links to display on Mobile devices (assuming your template supports mobile-specific settings) |
| `MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX` | Order History Box | `6` | Number of products to display in the order history box |
| `MAX_DISPLAY_PRODUCTS_LISTING` | Products Listing Page | `10` | Number of products to show per page when viewing an index listing |
| `MAX_DISPLAY_RECORD_COMPANY_NAME_LEN` | Length of Record Company Name | `15` | Used in record companies box; maximum length of record company name to display. Longer names will be truncated. |
| `MAX_LANGUAGE_FLAGS_COLUMNS` | Maximum Display of Language Flags in Language Side Box | `3` | Number of Language Flags per Row |
| `MAX_MANUFACTURERS_LIST` | Manufacturers List - Scroll Box Size/Style | `3` | Number of manufacturers names to be displayed in the scroll box window. Setting this to 1 or 0 will display a dropdown list. |
| `MAX_PREVIEW` | Maximum Preview | `100` | Maximum Preview length<br>100 = Default |
| `MAX_RANDOM_SELECT_FEATURED_PRODUCTS` | Random Featured Products for SideBox | `2` | Number of random FEATURED products to rotate in the sidebox<br>Enter the number of products to display in this sidebox at one time.<br><br>How many products do you want to display in this sidebox? |
| `MAX_RANDOM_SELECT_NEW` | Random New Products for SideBox | `3` | Number of random NEW products to rotate in the sidebox<br>Enter the number of products to display in this sidebox at one time.<br><br>How many products do you want to display in this sidebox? |
| `MAX_RANDOM_SELECT_REVIEWS` | Random Product Reviews for SideBox | `1` | Number of random product REVIEWS to rotate in the sidebox<br>Enter the number of products to display in this sidebox at one time.<br><br>How many products do you want to display in this sidebox? |
| `MAX_RANDOM_SELECT_SPECIALS` | Random Products On Special for SideBox | `2` | Number of random products on SPECIAL to rotate in the sidebox<br>Enter the number of products to display in this sidebox at one time.<br><br>How many products do you want to display in this sidebox? |

## Images (4)

| Key | Title | Default | Description |
|---|---|---|---|
| `CATEGORY_ICON_IMAGE_HEIGHT` | Category Icon Image Height - Product Info Pages | `40` | The pixel height of Category Icon heading images for Product Info Pages |
| `CATEGORY_ICON_IMAGE_WIDTH` | Category Icon Image Width - Product Info Pages | `57` | The pixel width of Category Icon heading images for Product Info Pages |
| `IMAGE_PRODUCT_LISTING_HEIGHT` | Image - Product Listing Height | `80` | Default = 80 |
| `IMAGE_PRODUCT_LISTING_WIDTH` | Image - Product Listing Width | `100` | Default = 100 |
| `IMAGE_SHOPPING_CART_HEIGHT` | Image - Shopping Cart Height | `40` | Default = 40 |
| `IMAGE_SHOPPING_CART_STATUS` | Image - Shopping Cart Status | `1` | Show product image in the shopping cart?<br>0= off 1= on |
| `IMAGE_SHOPPING_CART_WIDTH` | Image - Shopping Cart Width | `50` | Default = 50 |
| `MEDIUM_IMAGE_HEIGHT` | Product Info - Image Height | `120` | The pixel height of Product Info images |
| `MEDIUM_IMAGE_WIDTH` | Product Info - Image Width | `150` | The pixel width of Product Info images |
| `PRODUCTS_IMAGE_NO_IMAGE` | Product And Category Image - No Image picture | `no_picture.gif` | Use automatic No Image when none is added to product or category<br>Default = no_picture.gif |
| `PRODUCTS_IMAGE_NO_IMAGE_STATUS` | Product And Category Image - No Image Status | `1` | Use automatic No Image when none is added to product or category<br>0= off<br>1= On |
| `SMALL_IMAGE_HEIGHT` | Small Image Height | `80` | The pixel height of small images |
| `SMALL_IMAGE_WIDTH` | Small Image Width | `100` | The pixel width of small images |
| `SUBCATEGORY_IMAGE_TOP_HEIGHT` | Top Subcategory Image Height | `85` | The pixel height of Top subcategory images<br>Top subcategory is when the Category contains subcategories |
| `SUBCATEGORY_IMAGE_TOP_WIDTH` | Top Subcategory Image Width | `150` | The pixel width of Top subcategory images<br>Top subcategory is when the Category contains subcategories |

## Customer Details (5)

| Key | Title | Default | Description |
|---|---|---|---|
| `CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF` | Customer Authorization: Hide Column Left | `false` | Customer Authorization: Hide Column Left <br>(true=hide false=show) |
| `CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF` | Customer Authorization: Hide Column Right | `false` | Customer Authorization: Hide Column Right <br>(true=hide false=show) |
| `CUSTOMERS_AUTHORIZATION_FOOTER_OFF` | Customer Authorization: Hide Footer | `false` | Customer Authorization: Hide Footer <br>(true=hide false=show) |
| `CUSTOMERS_AUTHORIZATION_HEADER_OFF` | Customer Authorization: Hide Header | `false` | Customer Authorization: Hide Header <br>(true=hide false=show) |

## Shipping/Packaging (7)

| Key | Title | Default | Description |
|---|---|---|---|
| `SHOW_SHIPPING_ESTIMATOR_BUTTON` | Shipping Estimator Display Settings for Shopping Cart | `1` | <br>0= Off<br>1= Display as Button on Shopping Cart<br>2= Display as Listing on Shopping Cart Page |

## Product Listing (8)

| Key | Title | Default | Description |
|---|---|---|---|
| `PREV_NEXT_BAR_LOCATION` | Prev/Next Split Page Navigation (1-top, 2-bottom, 3-both) | `3` | Sets the location of the Prev/Next Split Page Navigation |
| `PRODUCTS_LIST_PRICE_WIDTH` | Display Product Price/Add to Cart Column Width | `125` | Define the width of the Price/Add to Cart column<br>Default= 125 |
| `PRODUCT_LISTING_COLUMNS_PER_ROW` | Columns Per Row | `1` | Select the number of columns of products to show per row in the product listing.<br>Recommended: 3<br>1=[rows] mode. |
| `PRODUCT_LISTING_MULTIPLE_ADD_TO_CART` | Display Multiple Products Qty Box Status and Set Button Location | `3` | Do you want to display Add Multiple Products Qty Box and Set Button Location?<br>0= off<br>1= Top<br>2= Bottom<br>3= Both |
| `PRODUCT_LIST_ALPHA_SORTER` | Include Product Listing Alpha Sorter Dropdown | `true` | Do you want to include an Alpha Filter dropdown on the Product Listing? |
| `PRODUCT_LIST_CATEGORIES_IMAGE_STATUS` | Include Product Listing Sub Categories Image | `true` | Do you want to include the Sub Categories Image on the Product Listing? |
| `PRODUCT_LIST_CATEGORIES_IMAGE_STATUS_TOP` | Include Product Listing Top Categories Image | `true` | Do you want to include the Top Categories Image on the Product Listing? |
| `PRODUCT_LIST_CATEGORY_ROW_STATUS` | Show SubCategories on Main Page while navigating | `1` | Show Sub-Categories on Main Page while navigating through Categories<br><br>0= off<br>1= on |
| `PRODUCT_LIST_DESCRIPTION` | Display Product Description | `150` | Do you want to display the Product Description?<br><br>0= OFF<br>150= Suggested Length, or enter the maximum number of characters to display |
| `PRODUCT_LIST_IMAGE` | Display Product Image | `1` | Do you want to display the Product Image?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_MANUFACTURER` | Display Product Manufacturer Name | `0` | Do you want to display the Product Manufacturer Name?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_MODEL` | Display Product Model | `0` | Do you want to display the Product Model?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_NAME` | Display Product Name | `2` | Do you want to display the Product Name?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_PRICE` | Display Product Price/Add to Cart | `3` | Do you want to display the Product Price/Add to Cart?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_PRICE_BUY_NOW` | Display Product Add to Cart Button (0=off; 1=on; 2=on with Qty Box per Product) | `1` | Do you want to display the Add to Cart Button?<br><br><strong>NOTE:</strong> Turn OFF Display Multiple Products Qty Box Status to use Option 2 on with Qty Box per Product |
| `PRODUCT_LIST_QUANTITY` | Display Product Quantity | `0` | Do you want to display the Product Quantity?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |
| `PRODUCT_LIST_WEIGHT` | Display Product Weight | `0` | Do you want to display the Product Weight?<br>0 - Not displayed.<br>n - Displayed, with the number n defining the display order relative to similar options on the product listing page. |

## Stock (9)

| Key | Title | Default | Description |
|---|---|---|---|
| `SHOW_PRODUCTS_SOLD_OUT_IMAGE` | Show Sold Out Image in place of Add to Cart | `1` | Show Sold Out Image instead of Add to Cart Button<br><br>0= off<br>1= on |
| `SHOW_SHOPPING_CART_DELETE` | Show Shopping Cart - Delete Checkboxes or Delete Button | `3` | Show on Shopping Cart Delete Button and/or Checkboxes<br><br>1= Delete Button Only<br>2= Checkbox Only<br>3= Both Delete Button and Checkbox |
| `SHOW_SHOPPING_CART_UPDATE` | Show Shopping Cart - Update Cart Button Location | `3` | Show on Shopping Cart Update Cart Button Location as:<br><br>1= Next to each Qty Box<br>2= Below all Products<br>3= Both Next to each Qty Box and Below all Products |

## Product Info (18)

| Key | Title | Default | Description |
|---|---|---|---|
| `PREVIOUS_NEXT_IMAGE_HEIGHT` | Previous Next - Image Height? | `40` | Previous/Next Image Height? |
| `PREVIOUS_NEXT_IMAGE_WIDTH` | Previous Next - Image Width? | `50` | Previous/Next Image Width? |
| `PRODUCT_INFO_CATEGORIES` | Previous Next - Navigation Includes Category Position | `1` | Product's Category Image and Name Alignment Above Previous/Next Navigation Bar<br>0= off<br>1= Align Left<br>2= Align Center<br>3= Align Right |
| `PRODUCT_INFO_CATEGORIES_IMAGE_STATUS` | Previous Next - Navigation Includes Category Name and Image Status | `2` | Product's Category Image and Name Status<br>0= Category Name and Image always shows<br>1= Category Name only<br>2= Category Name and Image when not blank |
| `PRODUCT_INFO_PREVIOUS_NEXT` | Previous Next - Navigation Bar Position | `1` | Location of Previous/Next Navigation Bar<br>0= off<br>1= Top of Page<br>2= Bottom of Page<br>3= Both Top and Bottom of Page |
| `SHOW_PREVIOUS_NEXT_IMAGES` | Previous Next - Button and Image Settings | `0` | Show Previous/Next Button and Product Image Settings<br>0= Button Only<br>1= Button and Product Image<br>2= Product Image Only |
| `SHOW_PREVIOUS_NEXT_STATUS` | Previous Next - Button and Image Status | `0` | Button and Product Image status settings are:<br>0= Off<br>1= On |
| `SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS` | Also Purchased Products Columns per Row | `3` | Also Purchased Products Columns per Row<br>0= off or set the sort order |

## Layout Settings (19)

| Key | Title | Default | Description |
|---|---|---|---|
| `BEST_SELLERS_TRUNCATE` | Bestsellers - Truncate Product Names | `35` | What size do you want to truncate the Product Names?<br>Default = 35 |
| `BEST_SELLERS_TRUNCATE_MORE` | Bestsellers - Truncate Product Names followed by ... | `true` | When truncated Product Names follow with ...<br>Default = true |
| `BOX_WIDTH_LEFT` | Column Width - Left Boxes | `150px` | Width of the Left Column Boxes<br>px may be included<br>Default = 150px |
| `BOX_WIDTH_RIGHT` | Column Width - Right Boxes | `150px` | Width of the Right Column Boxes<br>px may be included<br>Default = 150px |
| `BREAD_CRUMBS_SEPARATOR` | Bread Crumbs Navigation Separator | `&nbsp;::&nbsp;` | Enter the separator symbol to appear between the Navigation Bread Crumb trail<br>Note: Include spaces with the &amp;nbsp; symbol if you want them part of the separator.<br>Default = &amp;nbsp;::&amp;nbsp; |
| `CATEGORIES_COUNT_PREFIX` | Categories Count Prefix | `&nbsp;(` | What do you want to Prefix the count with?<br>Default= ( |
| `CATEGORIES_COUNT_SUFFIX` | Categories Count Suffix | `)` | What do you want as a Suffix to the count?<br>Default= ) |
| `CATEGORIES_COUNT_ZERO` | Categories with 0 Products Status | `0` | Show Category Count for 0 Products?<br>0= off<br>1= on |
| `CATEGORIES_SEPARATOR` | Categories Separator between the Category Name and Count | `-&gt;` | What separator do you want between the Category name and the count?<br>Default = -&amp;gt; |
| `CATEGORIES_TABS_STATUS` | Category Header Menu ON/OFF | `1` | Category Header Nav<br>This enables the display of your store's categories as a menu across the top of your header. There are many potential creative uses for this.<br>0= Hide Categories Tabs<br>1= Show Categories Tabs |
| `COLUMN_LEFT_STATUS` | Column Left Status - Global | `1` | Show Column Left, unless page override exists?<br>0= Column Left is always off<br>1= Column Left is on, unless page override |
| `COLUMN_RIGHT_STATUS` | Column Right Status - Global | `1` | Show Column Right, unless page override exists?<br>0= Column Right is always off<br>1= Column Right is on, unless page override |
| `COLUMN_WIDTH_LEFT` | Column Width - Left | `150px` | Width of the Left Column<br>px may be included<br>Default = 150px |
| `COLUMN_WIDTH_RIGHT` | Column Width - Right | `150px` | Width of the Right Column<br>px may be included<br>Default = 150px |
| `DEFINE_BREADCRUMB_STATUS` | Define Breadcrumb Status | `1` | Enable the Breadcrumb Trail Links?<br>0= OFF<br>1= ON<br>2= Off for Home Page Only |
| `IMAGE_USE_CSS_BUTTONS` | CSS Buttons | `Yes` | CSS Buttons<br>Use CSS buttons instead of images (GIF/JPG)?<br>Button styles must be configured in the stylesheet if you enable this option.<br>Yes - Use CSS buttons<br>No - Use images buttons<br>Found - Use images if exist, else use CSS buttons |
| `SHOW_ACCOUNT_LINKS_ON_SITE_MAP` | Site Map - include My Account Links? | `No` | Should the links to My Account show up on the site-map?<br>Note: Spiders will try to index this page, and likely should not be sent to secure pages, since there is no benefit in indexing a login page.<br><br>Default: false |
| `SHOW_BANNERS_GROUP_SET1` | Banner Display Groups - Header Position 1 | `The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Header Position 1?<br>Leave blank for none` | configuration |
| `SHOW_BANNERS_GROUP_SET2` | Banner Display Groups - Header Position 2 | `The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Header Position 2?<br>Leave blank for none` | configuration |
| `SHOW_BANNERS_GROUP_SET3` | Banner Display Groups - Header Position 3 | `The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Header Position 3?<br>Leave blank for none` | configuration |
| `SHOW_BANNERS_GROUP_SET4` | Banner Display Groups - Footer Position 1 | `The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Footer Position 1?<br>Leave blank for none` | configuration |
| `SHOW_BANNERS_GROUP_SET5` | Banner Display Groups - Footer Position 2 | `The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Footer Position 2?<br>Leave blank for none` | configuration |
| `SHOW_BANNERS_GROUP_SET6` | Banner Display Groups - Footer Position 3 | `Wide-Banners` | The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br><br>Default Group is Wide-Banners<br><br>What Banner Group(s) do you want to use in the Footer Position 3?<br>Leave blank for none |
| `SHOW_BANNERS_GROUP_SET7` | Banner Display Groups - Side Box banner_box | `SideBox-Banners` | The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br>Default Group is SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Side Box - banner_box?<br>Leave blank for none |
| `SHOW_BANNERS_GROUP_SET8` | Banner Display Groups - Side Box banner_box2 | `SideBox-Banners` | The Banner Display Groups can be from one Banner Group or multiple Banner Groups<br><br>For multiple Banner Groups enter the Banner Group Name separated by a colon <strong>:</strong><br><br>Example: Wide-Banners:SideBox-Banners<br>Default Group is SideBox-Banners<br><br>What Banner Group(s) do you want to use in the Side Box - banner_box2?<br>Leave blank for none |
| `SHOW_BANNERS_GROUP_SET_ALL` | Banner Display Group - Side Box banner_box_all | `BannersAll` | The Banner Display Group may only be from one Banner Group for the Banner All sidebox<br><br>Default Group is BannersAll<br><br>What Banner Group do you want to use in the Side Box - banner_box_all?<br>Leave blank for none |
| `SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES` | Categories Box - Show Featured Category Link | `true` | Show Featured Categories Link in the Categories Box |
| `SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS` | Categories Box - Show Featured Products Link | `true` | Show Featured Products Link in the Categories Box |
| `SHOW_CATEGORIES_BOX_PRODUCTS_ALL` | Categories Box - Show Products All Link | `true` | Show Products All Link in the Categories Box |
| `SHOW_CATEGORIES_BOX_PRODUCTS_NEW` | Categories Box - Show Products New Link | `true` | Show Products New Link in the Categories Box |
| `SHOW_CATEGORIES_BOX_SPECIALS` | Categories Box - Show Specials Link | `true` | Show Specials Link in the Categories Box |
| `SHOW_CATEGORIES_SEPARATOR_LINK` | Categories Separator between links Status | `1` | Show Category Separator between Category Names and Links?<br>0= off<br>1= on |
| `SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS` | Categories - Always Open to Show SubCategories | `1` | Always Show Categories and SubCategories<br>0= off, just show Top Categories<br>1= on, Always show Categories and SubCategories when selected |
| `SHOW_CUSTOMER_GREETING` | Customer Greeting - Show on Index Page | `1` | Always Show Customer Greeting on Index?<br>0= off<br>1= on |
| `SHOW_FOOTER_IP` | Footer - Show IP Address status | `1` | Show Customer IP Address in the Footer<br>0= off<br>1= on<br>Should the Customer IP Address show in the footer? |
| `SHOW_SHOPPING_CART_BOX_STATUS` | Shopping Cart Box Status | `1` | Shopping Cart Shows<br>0= Always<br>1= Only when full<br>2= Only when full but not when viewing the Shopping Cart |
| `SHOW_TOTALS_IN_CART` | Shopping Cart - Show Totals | `1` | Show Totals Above Shopping Cart?<br>0= off<br>1= on: Items Weight Amount<br>2= on: Items Weight Amount, but no weight when 0<br>3= on: Items Amount |
| `USE_SPLIT_LOGIN_MODE` | Use split-login page | `False` | The login page can be displayed in two modes: Split or Vertical.<br>In Split mode, the create-account options are accessed by clicking a button to get to the create-account page.  In Vertical mode, the create-account input fields are all displayed inline, below the login field, making one less click for the customer to create their account.<br>Default: False |

## Website Maintenance (20)

| Key | Title | Default | Description |
|---|---|---|---|
| `DOWN_FOR_MAINTENANCE_COLUMN_LEFT_OFF` | Down for Maintenance: Hide Column Left | `false` | Down for Maintenance: Hide Column Left <br>(true=hide false=show) |
| `DOWN_FOR_MAINTENANCE_COLUMN_RIGHT_OFF` | Down for Maintenance: Hide Column Right | `false` | Down for Maintenance: Hide Column Right <br>(true=hide false=show) |
| `DOWN_FOR_MAINTENANCE_FOOTER_OFF` | Down for Maintenance: Hide Footer | `false` | Down for Maintenance: Hide Footer <br>(true=hide false=show) |
| `DOWN_FOR_MAINTENANCE_HEADER_OFF` | Down for Maintenance: Hide Header | `false` | Down for Maintenance: Hide Header <br>(true=hide false=show) |

## Index Listing (24)

| Key | Title | Default | Description |
|---|---|---|---|
| `SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS` | Featured Products And Categories Columns per Row | `3` | Featured Products And Categories Columns per Row |
| `SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS` | New Products Columns per Row | `3` | New Products Columns per Row |
| `SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS` | Special Products Columns per Row | `3` | Special Products Columns per Row |

## Define Page Status (25)

| Key | Title | Default | Description |
|---|---|---|---|
| `DEFINE_CHECKOUT_SUCCESS_STATUS` | Define Checkout Success | `1` | Enable the Defined Checkout Success Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_CONDITIONS_STATUS` | Define Conditions of Use | `1` | Enable the Defined Conditions of Use Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_CONTACT_US_STATUS` | Define Contact Us Status | `1` | Enable the Defined Contact Us Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_DISCOUNT_COUPON_STATUS` | Define Discount Coupon | `1` | Enable the Defined Discount Coupon Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_MAIN_PAGE_STATUS` | Define Main Page Status | `1` | Enable the Defined Main Page Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_PAGE_2_STATUS` | Define Page 2 | `1` | Enable the Defined Page 2 Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_PAGE_3_STATUS` | Define Page 3 | `1` | Enable the Defined Page 3 Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_PAGE_4_STATUS` | Define Page 4 | `1` | Enable the Defined Page 4 Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_PRIVACY_STATUS` | Define Privacy Status | `1` | Enable the Defined Privacy Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_SHIPPINGINFO_STATUS` | Define Shipping & Returns | `1` | Enable the Defined Shipping & Returns Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |
| `DEFINE_SITE_MAP_STATUS` | Define Site Map Status | `1` | Enable the Defined Site Map Link/Text?<br>0= Link ON, Define Text OFF<br>1= Link ON, Define Text ON<br>2= Link OFF, Define Text ON<br>3= Link OFF, Define Text OFF |

## EZ-Pages Settings (30)

| Key | Title | Default | Description |
|---|---|---|---|
| `EZPAGES_SEPARATOR_FOOTER` | EZ-Pages Footer Link Separator | `&nbsp;::&nbsp;` | EZ-Pages Footer Link Separator<br>Default = &amp;nbsp;::&amp;nbsp; |
| `EZPAGES_SEPARATOR_HEADER` | EZ-Pages Header Link Separator | `&nbsp;::&nbsp;` | EZ-Pages Header Link Separator<br>Default = &amp;nbsp;::&amp;nbsp; |
| `EZPAGES_SHOW_PREV_NEXT_BUTTONS` | EZ-Pages Prev/Next Buttons | `2` | Display Prev/Continue/Next buttons on EZ-Pages pages?<br>0=OFF (no buttons)<br>1="Continue"<br>2="Prev/Continue/Next"<br><br>Default setting: 2. |
| `EZPAGES_SHOW_TABLE_CONTENTS` | EZ-Pages Table of Contents for Chapters Status | `1` | Enable EZ-Pages Table of Contents for Chapters?<br>0= OFF<br>1= ON |
| `EZPAGES_STATUS_FOOTER` | EZ-Pages Display Status - FooterBar | `1` | Display of EZ-Pages content can be Globally enabled/disabled for the Footer Bar<br>0 = Off<br>1 = On<br>2= On ADMIN IP ONLY located in Website Maintenance<br>NOTE: Warning only shows to the Admin and not to the public |
| `EZPAGES_STATUS_HEADER` | EZ-Pages Display Status - HeaderBar | `1` | Display of EZ-Pages content can be Globally enabled/disabled for the Header Bar<br>0 = Off<br>1 = On<br>2= On ADMIN IP ONLY located in Website Maintenance<br>NOTE: Warning only shows to the Admin and not to the public |
| `EZPAGES_STATUS_SIDEBOX` | EZ-Pages Display Status - Sidebox | `1` | Display of EZ-Pages content can be Globally enabled/disabled for the Sidebox<br>0 = Off<br>1 = On<br>2= On ADMIN IP ONLY located in Website Maintenance<br>NOTE: Warning only shows to the Admin and not to the public |
