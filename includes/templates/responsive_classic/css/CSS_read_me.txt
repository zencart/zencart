These CSS files are sent to the browser in this order: (and alphabetically within each case of more than one match):

        stylesheet*.css   // are always loaded and at least ONE should contain site-wide properties.
language_stylesheet.css   // changes to ALL pages, when that language is used
         index_home.css   // specifically affects the home page only
          page_name.css   // changes to one page, ie: index.php?main_page=page_name
 language_page_name.css   // changes to one page, when that language is used
            c_??_??.css   // changes to all info pages in a category
   language_c_??_??.css   // changes to all info pages in a category, when that language is used
   c_??_??_children.css   // changes for all children of the specified parent. Also supports a language prefix.
               m_??.css   // changes to a manufacturer's listing page
      language_m_??.css   // changes to a manufacturer's listing page, when that language is used
               p_??.css   // changes to a product's info page
      language_p_??.css   // changes to a product's info page, when that language is used
             print*.css   // printer-friendly global usage site-wide changes for printing-only
             page##.css   // EZ-Page -- css specific to a numbered EZ-page ... ie:  page21.css would be 
						                 for EZ-Page number 21 ... ie: for the URL index.php?main_page=page&id=21
					 
These CSS files are sent to the browser after the above files:
		
         responsive.css   // is loaded on every page except the popup_image and popup_image_additional pages and 
				                     contain site-wide selectors that control the responsive HTML elements.
 responsive_default.css   // is loaded on by default where a mobile or tablet UA is NOT detected.
  responsive_mobile.css   // is loaded on mobile devices where a mobile UA is detected, it replaces the 'responsive_default.css' file.
  responsive_tablet.css   // is loaded on tablet devices where a tablet UA is detected, it replaces the 'responsive_default.css' file.			 
						 					 
The 'stylesheet.css' is expected to load first and should contain the bulk of your CSS selectors. Each file loaded 
takes priority over previously loaded file(s). To save loading time, only new selectors or selectors whose properties 
you wish to change should be in the optional CSS files. You can have different overrides for the same page, in 
different languages, because the two would never be called at the same time.

If someone selected the French language on your site, the 'french_stylesheet.css' would also be loaded. It should 
only contain the site-wide changes you want to make to 'stylesheet.css'. For example, change a 'background-image' 
for your French customers.

If someone went to any of the other pages, that page's CSS file would be loaded. Possibly you want 
different 'background-image' & 'background-color' on each of 'page_x' pages. Possibly you do not want a 
border around '.plainBox' most of the time, but on a couple of pages you do... and on one of those pages you 
want it in black and the other in red.

Possibly you created a NEW tag and did a <span class="newtag"> in your Privacy Statement. It is defined in only one 
CSS file, 'german_privacy.css' as '.newtag { text-transform: uppercase }' Because, in Germany, that phrase must be 
in all CAPS, but not in other countries.

Use your CSS files and the standard tags as much as possible, just change their properties when needed. If possible, 
DON'T HACK the core code. Use your CSS files to do the work for you. When the style coding has been removed from 
the ZenCart code and people have to decide if they want to go without the upgrade ~or~ undo all their hacks and 
finally learn about CSS... your site will still be up and running.

Additional information is contained in the Zen Cart wiki.

Adapted from ideas presented by
Juxi Zoza
03/15/05

-- Responsive CSS Files --

The 'responsive.css' should NOT be altered, it contains specific selectors that calculate how your site will resize 
as the browser window resizes and displays the correct width percentage for smaller browser windows.

The 'responsive_default.css' is the default CSS file that will contain all the sitewide selectors that need 
manipulation to display correctly at different browser widths, included are (4) standand CSS media breakpoints:

@media (min-width:0px) and (max-width:480px) {
This CSS media breakpoint will only apply CSS selector manipulations for browser windows 
under 480px wide ( mobile devices ). 
}

@media (min-width:481px) and (max-width:767px) {
This CSS media breakpoint will only apply CSS selector manipulations for browser windows 
larger than 481px wide but under 767px wide ( tablet devices ). 
}

@media (min-width:768px) and (max-width:1500px) {
This CSS media breakpoint will only apply CSS selector manipulations for browser windows 
larger than 768px wide but under 1500px wide { average desktop }. 
}

@media (min-width:1500px) and (max-width:1800px) {
This CSS media breakpoint will only apply CSS selector manipulations for browser windows 
larger than 1500px wide but under 1800px wide ( wide-screen desktop ). 
}

An easy example using the 'responsive_default.css' media breakpoints, lets say you want to hide the banner images 
on browser windows under 480px, simply add the banners selector to the appropriate media query like so:

@media (min-width:0px) and (max-width:480px) {
.banners { display:none; }
}

Now all your banner images are hidden on browser windows under 480px, adding CSS selector manipulations so that 
content displays correctly across all broweser widths can get pretty hefty depending on how customized your template 
is, so thats where the device specific 'responsive_mobile.css' and 'responsive_tablet.css' come in.

The 'responsive_mobile.css' and 'responsive_tablet.css' are CSS files that will contain all the sitewide selectors 
that need manipulation to display correctly on specific devices and are only loaded if a browsers UA is detected, 
very similar to the 'responsive_default.css' file but device specific and only contains device specific CSS media 
breakpoints to save loading time.

In most cases you should only have to copy the selectors added to the CSS media breakpoints from 
the 'responsive_default.css' file to the correct device specific 'responsive_??.css' file.

All the CSS selector manipulation changes made to the 'responsive_default.css' for media breakpoints for browser 
windows under 480px wide ( mobile devices ) should be copied over to the 'responsive_mobile.css' file.

And all the CSS selector manipulation changes made to the 'responsive_default.css' for media breakpoints for 
browser windows larger than 481px wide but under 767px wide ( tablet devices ) should be copied over to 
the 'responsive_tablet.css' file.

The 'responsive_mobile.css' and 'responsive_tablet.css' files use different CSS media breakpoints:

@media only screen and (orientation:landscape) { }
@media only screen and (orientation:portrait) { }

In most cases CSS media breakpoints copied from the 'responsive_default.css' will suffice on both orientations but 
in some circumstances you may have to add an extra (margin) to an selectors HTML element in (orientation:landscape) 
but not in (orientation:portrait), so they are there for those rare occurrences.
