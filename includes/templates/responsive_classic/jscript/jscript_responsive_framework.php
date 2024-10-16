<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Jeff Rutt 2024 Sep 17 Modified in v2.1.0-beta1 $
 */
?>

<script>

(function($) {
$(document).ready(function() {

$('#contentMainWrapper').addClass('onerow-fluid');
 $('#mainWrapper').css({
     'max-width': '100%',
     'margin': 'auto'
 });
 $('#headerWrapper').css({
     'max-width': '100%',
     'margin': 'auto'
 });
 $('#navSuppWrapper').css({
     'max-width': '100%',
     'margin': 'auto'
 });

<?php if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) { ?>

$('.leftBoxContainer').css('width', '');
$('.rightBoxContainer').css('width', '');
$('#mainWrapper').css('margin', 'auto');
$('.centerColumn').css('clear', 'both');

$('#bannerbox').css({ 'display': 'none', 'visibility': 'hidden' });
$('#bannerbox2').css({ 'display': 'none', 'visibility': 'hidden' });
$('#bannerboxall').css({ 'display': 'none', 'visibility': 'hidden' });
$('#bestsellers').css({ 'display': 'none', 'visibility': 'hidden' });
$('#brands').css({ 'display': 'none', 'visibility': 'hidden' });
$('#categories').css({ 'display': 'none', 'visibility': 'hidden' });
$('#currencies').css({ 'display': 'none', 'visibility': 'hidden' });
$('#documentcategories').css({'display': 'none', 'visibility': 'hidden'  });
$('#ezpages').css({ 'display': 'none', 'visibility': 'hidden' });
$('#featured').css({ 'display': 'none', 'visibility': 'hidden' });
$('#featuredcategories').css({ 'display': 'none', 'visibility': 'hidden' });
$('#information').css({ 'display': 'none', 'visibility': 'hidden' });
$('#languages').css({ 'display': 'none', 'visibility': 'hidden' });
$('#manufacturerinfo').css({ 'display': 'none', 'visibility': 'hidden' });
$('#manufacturers').css({'display': 'none', 'visibility': 'hidden'  });
$('#moreinformation').css({ 'display': 'none', 'visibility': 'hidden' });
$('#musicgenres').css({ 'display': 'none', 'visibility': 'hidden' });
$('#orderhistory').css({ 'display': 'none', 'visibility': 'hidden' });
$('#productnotifications').css({ 'display': 'none', 'visibility': 'hidden' });
$('#recordcompanies').css({ 'display': 'none', 'visibility': 'hidden' });
$('#reviews').css({ 'display': 'none', 'visibility': 'hidden' });
$('#search').css({ 'display': 'none', 'visibility': 'hidden' });
$('#shoppingcart').css({ 'display': 'none', 'visibility': 'hidden' });
$('#specials').css({'display': 'none', 'visibility': 'hidden'  });
$('#whatsnew').css({ 'display': 'none', 'visibility': 'hidden' });
$('#whosonline').css({ 'display': 'none', 'visibility': 'hidden' });


$('input#email-address').clone().attr('type','email').insertAfter('input#email-address').prev().remove();
$('input#searchHeader').clone().attr('type','search').insertAfter('input#searchHeader').prev().remove();
$('input#mailChimp').clone().attr('type','email').insertAfter('input#mailChimp').prev().remove();
$('input#login-email-address').clone().attr('type','email').insertAfter('input#login-email-address').prev().remove();
// The following turns the postcode into a number-only field, which probably only suits USA addresses:
//$('input#postcode').clone().attr('type','number').insertAfter('input#postcode').prev().remove();
$('input#telephone').clone().attr('type','tel').insertAfter('input#telephone').prev().remove();
$('input#dob').clone().attr('type','date').insertAfter('input#dob').prev().remove();
$('input#fax').clone().attr('type','tel').insertAfter('input#fax').prev().remove();

<?php } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ){ ?>
$('#mainWrapper').css({
     'max-width': '100%',
     'margin': 'auto'
 });

$('.leftBoxContainer').css('width', '');
$('.rightBoxContainer').css('width', '');
$('#mainWrapper').css('margin', 'auto');
$('.centerColumn').css('clear', 'both');

$('#bannerbox').css({  });
$('#bannerbox2').css({  });
$('#bannerboxall').css({  });
$('#bestsellers').css({  });
$('#brands').css({  });
$('#categories').css({  });
$('#currencies').css({  });
$('#documentcategories').css({  });
$('#ezpages').css({  });
$('#featured').css({  });
$('#featuredcategories').css({  });
$('#information').css({  });
$('#languages').css({  });
$('#manufacturerinfo').css({  });
$('#manufacturers').css({  });
$('#moreinformation').css({  });
$('#musicgenres').css({  });
$('#orderhistory').css({  });
$('#productnotifications').css({  });
$('#recordcompanies').css({  });
$('#reviews').css({  });
$('#search').css({  });
$('#shoppingcart').css({  });
$('#specials').css({  });
$('#whatsnew').css({  });
$('#whosonline').css({  });


<?php } else if ( $_SESSION['layoutType'] == 'full' ){ ?>

 $('#mainWrapper').css({
     'width': '100%',
     'margin': 'auto'
 });
 $('#headerWrapper').css({
     'width': '100%',
     'margin': 'auto'
 });
 $('#navSuppWrapper').css({
     'width': '100%',
     'margin': 'auto'
 });

<?php } else { ?>

$('.leftBoxContainer').css('width', '');
$('.rightBoxContainer').css('width', '');
$('#mainWrapper').css('margin', 'auto');

<?php } ?>
$('a[href="#top"]').click(function(){
$('html, body').animate({scrollTop:0}, 'slow');
return false;
});

$(".categoryListBoxContents").click(function() {
window.location = $(this).find("a").attr("href");
return false;
});

$('.centeredContent').matchHeight();
$('.specialsListBoxContents').matchHeight();
$('.centerBoxContentsAlsoPurch').matchHeight();
$('.categoryListBoxContents').matchHeight();

$('.no-fouc').removeClass('no-fouc');
});

}) (jQuery);

</script>
