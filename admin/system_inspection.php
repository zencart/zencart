<?php
/**
 * @package admin
 * @copyright Copyright 2015 Zen Cart Development Team
 * @copyright Copyright 2015 That Software Guy
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

require('includes/application_top.php');
require('includes/admin_html_head.php');
?>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div style="margin:2px;">
<h1><?php echo HEADING_TITLE; ?></h1>
<p><?php echo HEADING_WHY . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . "?"; ?></p>
<h2><?php echo PAGES_TABLE; ?></h2>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
              <!-- this is the heading row -->
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo HEADING_PAGE_NAME; ?>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo HEADING_PAGE_MENU_KEY; ?><br>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo HEADING_DISPLAY; ?><br>
                </td>
                <td class="dataTableHeadingContent" align="left" valign="top">
                  <?php echo HEADING_PAGE_LINK; ?><br>
                </td>
              </tr>
              <!-- end heading row -->
<?php
    $new_pages = array(); 
    $pages_query_raw = " SELECT * FROM " . TABLE_ADMIN_PAGES; 
    $pages = $db->Execute($pages_query_raw); 
    if ($pages->RecordCount() <= 0) { 
?>
      <tr><td colspan="3" align="left"><?php echo '<b>' . NO_PAGES_TABLE_FOUND . '</b>'; ?></td></tr>
<?php
    } else { 
       $unknown_pages = 0; 
       foreach ($pages as $page) {
          $key = $page['language_key']; 
          if (in_array($key, $built_in_boxes)) { 
             continue;
          }
          $unknown_pages++; 
   ?>
                <tr>
                   <td class="dataTableContent" align="left">
                   <?php 
                      if (defined($page['language_key'])) 
                         echo constant($page['language_key']);
                      else 
                         echo "(" . $page['language_key'] . ")";
                   ?>
                   </td>
                   <td class="dataTableContent">
   <?php 
                         echo $page['menu_key'];
   ?>
                   </td>
                   <td class="dataTableContent">
   <?php 
                         echo $page['display_on_menu'];
   ?>
                   </td>
                   <td class="dataTableContent">
                   <?php 
                      if (defined($page['language_key']) && 
                          defined($page['main_page'])) {
                         echo '<a href="' . zen_href_link(constant($page['main_page']), $page['page_params']) .'">' . constant($page['language_key']) . '</a>';  
                      } else {
                         echo NO_LINK; 
                      }
                   ?>
                   </td>
                 </tr>
   <?php
       }
       if ($unknown_pages == 0) { 
?>
          <tr>
            <td colspan="3"><?php echo NO_NEW_PAGES; ?></td>
          </tr>
<?php
       }
    }
?>
</table>

<h2><?php echo DB_LIST; ?></h2>
<?php
    $new_pages = array(); 
    $tables_query_raw = "SELECT TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '" . DB_DATABASE . "'"; 
    $tables = $db->Execute($tables_query_raw); 
    if ($tables->RecordCount() <= 0) { 
?>
      <tr><td colspan="3" align="left"><?php echo '<b>' . NO_INFORMATION_SCHEMA_TABLE_FOUND . '</b>'; ?></td></tr>
<?php
    } else { 
       echo "<ul>"; 
       $unknown_tables = 0; 
       foreach ($tables as $table) { 
          $key = $table['TABLE_NAME']; 
          if (DB_PREFIX != '') { 
             $key = substr($key, strlen(DB_PREFIX)); 
          }
          if (in_array($key, $built_in_tables) || 
              in_array($key, $optional_tables)) {
             continue;
          }
          echo '<li>' . $table['TABLE_NAME'] . '</li>';
          $unknown_tables++; 
       }
       if ($unknown_tables == 0) { 
          echo "<li>" . NO_NEW_TABLES . "</li>"; 
       }
       echo "</ul>"; 
    }
?>

<h2><?php echo MODULE_LIST; ?></h2>
<ul>
<?php
  echo '<li>' . BOX_MODULES_PAYMENT. ": "; 
  $list = explode (';', MODULE_PAYMENT_INSTALLED);  
  $i = 0; 
  foreach ($list as $item) { 
     if (!in_array($item, $built_in_payments)) { 
         $i++; 
         echo $item . ' '; 
     }
  }
  if ($i == 0) echo NO_EXTRAS; 
  echo "</li>\n"; 

  echo '<li>' . BOX_MODULES_SHIPPING. ": "; 
  $list = explode (';', MODULE_SHIPPING_INSTALLED);  
  $i = 0; 
  foreach ($list as $item) { 
     if (!in_array($item, $built_in_shippings)) { 
         $i++; 
         echo $item . ' '; 
     }
  }
  if ($i == 0) echo NO_EXTRAS; 
  echo "</li>\n"; 

  echo '<li>' . BOX_MODULES_ORDER_TOTAL. ": "; 
  $list = explode (';', MODULE_ORDER_TOTAL_INSTALLED);  
  $i = 0; 
  foreach ($list as $item) { 
     if (!in_array($item, $built_in_order_totals)) { 
         $i++; 
         echo $item . ' '; 
     }
  }
  if ($i == 0) echo NO_EXTRAS; 
  echo "</li>\n"; 
?>
</ul>

<h2><?php echo MISSING_ADMIN_PAGES; ?></h2>
<?php echo '<div class="smallText">' . MISSING_ADMIN_PAGES_WHY . '</div>'; ?>
<br />
<?php
    $missing_pages = array(); 
    $pages_query_raw = " SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE visible = '1'" ;
    $pages = $db->Execute($pages_query_raw); 
    foreach ($pages as $page) { 
       $gid = $page['configuration_group_id']; 
       $admin_entry = $db->Execute("SELECT * FROM " . TABLE_ADMIN_PAGES . " WHERE page_params = 'gid=". (int)$gid . "'");  
       if ($admin_entry->EOF) { 
           $missing_pages[] = array('gid' => $gid, 
                                    'name' => $page['configuration_group_title']); 
       }
    }
    if (sizeof($missing_pages) > 0) { 
       echo "<ul>"; 
       foreach ($missing_pages as $missing_page) { 
          echo "<li>";
          echo '<a href="' . zen_href_link(FILENAME_CONFIGURATION, "gID=" . (int)$missing_page['gid']) .'">' . $missing_page['name'] . '</a>';  
          echo "</li>";
       } 
       echo "</ul>"; 
    } else { 
      echo NO_MISSING_ADMIN_PAGES; 
    }
?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
