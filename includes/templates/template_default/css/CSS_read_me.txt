
The CSS files are sent to the browser in this order: (and alphabetically within each case of more than one match):

             style*.css   // are always loaded and at least ONE should contain site-wide properties.
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
             page##.css   // EZ-Page -- css specific to a numbered EZ-page ... ie:  page21.css would be for EZ-Page number 21 ... ie: for the URL index.php?main_page=page&id=21

The 'stylesheet.css' is expected to load first and should contain the bulk of your CSS selectors. Each file loaded takes priority over previously loaded file(s). To save loading time, only new selectors or selectors whose properties you wish to change should be in the optional CSS files. You can have different overrides for the same page, in different languages, because the two would never be called at the same time.

If someone selected the French language on your site, the 'french_stylesheet.css' would also be loaded. It should only contain the site-wide changes you want to make to 'stylesheet.css'. For example, change a 'background-image' for your French customers.

If someone went to any of the other pages, that page's CSS file would be loaded. Possibly you want different 'background-image' & 'background-color' on each of 'page_x' pages. Possibly you do not want a border around '.plainBox' most of the time, but on a couple of pages you do... and on one of those pages you want it in black and the other in red.

Possibly you created a NEW tag and did a <span class="newtag"> in your Privacy Statement. It is defined in only one CSS file, 'german_privacy.css' as '.newtag { text-transform: uppercase }' Because, in Germany, that phrase must be in all CAPS, but not in other countries.

Use your CSS files and the standard tags as much as possible, just change their properties when needed. If possible, DON'T HACK the core code. Use your CSS files to do the work for you. When the style coding has been removed from the ZenCart code and people have to decide if they want to go without the upgrade ~or~ undo all their hacks and finally learn about CSS... your site will still be up and running.

Additional information is contained in the Zen Cart wiki.


Adapted from ideas presented by
Juxi Zoza
03/15/05
