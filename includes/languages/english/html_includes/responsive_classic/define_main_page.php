<?php if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) { ?>

<a href="http://www.zen-cart.com/book"><img src="includes/templates/responsive_classic/images/zencart-book-mobile.png" alt="get your manual today" title="Have you got yours yet? Join the 1000's of Zen Cart users that have bought the only comprehensive owners manual !" class="home-image" /></a>
  
<?php  } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ){ ?>

<a href="http://www.zen-cart.com/book"><img src="includes/templates/responsive_classic/images/zencart-book.png" alt="get your manual today" title="Have you got yours yet? Join the 1000's of Zen Cart users that have bought the only comprehensive owners manual !" class="home-image" /></a>

<?php  } else if ( $_SESSION['layoutType'] == 'full' ) { ?>

<a href="http://www.zen-cart.com/book"><img src="includes/templates/responsive_classic/images/zencart-book.png" alt="get your manual today" title="Have you got yours yet? Join the 1000's of Zen Cart users that have bought the only comprehensive owners manual !" class="home-image" /></a>

<?php  } else { ?>

<a href="http://www.zen-cart.com/book"><img src="includes/templates/responsive_classic/images/zencart-book.png" alt="get your manual today" title="Have you got yours yet? Join the 1000's of Zen Cart users that have bought the only comprehensive owners manual !" class="home-image" /></a>

<?php  } ?>
<p class="biggerText">The template package uses PHP Mobile Detect to serve up the optimized layout based on device.  
    If you are on a Desktop and want to view the Tablet layout <a class="red" href="index.php?main_page=index&amp;layoutType=tablet">use this link.</a>  
    If you want to view the Mobile layout <a class="red" href="index.php?main_page=index&amp;layoutType=mobile">use this link.</a>  
    To switch back to a Desktop <a class="red" href="index.php?main_page=index&amp;layoutType=default">use this link.</a></p>
    
<p>This content is located in the file at: <code> /languages/english/html_includes/YOUR_TEMPLATE/define_main_page.php</code></p>
<p>You can quickly edit this content via Admin->Tools->Define Pages Editor, and select define_main_page from the pulldown.</p>
<p><strong>NOTE: Always backup the files in<code> /languages/english/html_includes/your_template</code></strong></p>
