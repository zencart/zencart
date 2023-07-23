<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EzpagesContentTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('ezpages_content')->truncate();

        DB::table('ezpages_content')->insert(array(
            0 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => 'This is the main page listed under the Link EZPages in the Header<br /><br />

<strong>See: What is EZPages? Link for detailed use of EZPages</strong><br /><br />

This Link could show in the Header, Footer or Sidebox or a combination of all three locations.<br /><br />

The Chapter and TOC settings are for using this Page in combination with other Pages.<br /><br />

The other Pages can be shown either *only* with this Link in the Chapter and TOC or as their own Link in the Header, Footer or Sidebox, depending on how you would like them to appear on your site.<br /><br />

There is no true "Master" Link, other than the Links you actually have configured to display. But any Link in a Chapter can be displayed in any of the 3 locations for the Header, Footer or Sidebox or not at all, where it only appears together with the other Links in the Chapter.',
                    'pages_id' => 1,
                    'pages_title' => 'EZPages',
                ),
            1 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => 'This is another page that is linked to the Chapter 10 via the Chapter number used and is sorted based on the TOC Order.<br /><br />

There is not a link to this page via the Header, Footer nor the Sidebox.<br /><br />

This page is only seen if the "main" link is selected and then it will show in the TOC listing.<br /><br />

This is a handy way to have numerous links that are related but only show one main link to get to them all.<br /><br />',
                    'pages_id' => 2,
                    'pages_title' => 'A New Page',
                ),
            2 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => 'This is yet another new page or link that is part of Chapter 10<br /><br />

The numbering of the Chapters can be done in any manner. But, by number in increments such as 10, 20, 30, etc. you can later insert pages, or links, as needed within the existing pages.<br /><br />

There is no limit to the number of pages, or links, that can be grouped together using the Chapter.<br /><br />

The display of the Previous/Next and TOC listing is a setting that can be turned on or off.',
                    'pages_id' => 3,
                    'pages_title' => 'Another New Page',
                ),
            3 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 4,
                    'pages_title' => 'Shop By Brand',
                ),
            4 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => 'The title or link names can be anything that you would like to use.<br /><br />

You decide on the content and the link name relative to that content.<br /><br />

Then, define where you want the link to show: Header, Footer or Sidebox or as a combination of these three locations.<br /><br />

The content of the page can be anything you like. Be sure that your content is valid in regard to table and stylesheet rules.<br /><br />

You can even set up the links to go to Secure or Non-Secure pages as well as open in the same or a new window.<br /><br />

Links can also be setup to use internal or external links vs the HTML Content. See: examples below in the Link URL settings.',
                    'pages_id' => 5,
                    'pages_title' => 'Anything',
                ),
            5 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => 'This link is a "shared" link between the Header, Footer and Sidebox.<br /><br />

The number on the order was set to 50 on all of the settings just for the sake of an easier notation on entering it.<br /><br />

The order can be the same or different for the three locations.<br /><br />

If you wanted to really get creative, you could also have this as part of a Chapter not related to the link order.<br /><br />',
                    'pages_id' => 6,
                    'pages_title' => 'Shared',
                ),
            6 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 7,
                    'pages_title' => 'My Account',
                ),
            7 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 8,
                    'pages_title' => 'Site Map',
                ),
            8 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 9,
                    'pages_title' => 'Privacy Notice',
                ),
            9 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 10,
                    'pages_title' => 'Zen Cart',
                ),
            10 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 11,
                    'pages_title' => 'Gift Certificates',
                ),
            11 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 12,
                    'pages_title' => 'Action DVDs',
                ),
            12 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '',
                    'pages_id' => 13,
                    'pages_title' => 'Google',
                ),
            13 =>
                array(
                    'languages_id' => 1,
                    'pages_html_text' => '<span style="font-weight: bold; color: rgb(255, 0, 0);">Summary</span><br /><br /><span style="font-weight: bold;">EZ-Pages</span> is a fast, easy way of creating links and additional pages.<br /><br />The additional Pages can be for:<br /><ul><li>New Pages</li><li>Internal Links</li><li>External Links</li><li>Secure or Non-Secure pages</li><li>Same or New Window</li></ul>In Addition, there is the ability to create &quot;related&quot; links in the format of a Chapter (group) and its TOC (related pages/links).<br /><br /><span style="font-weight: bold; color: rgb(255, 0, 0);">Link Naming</span><br /><br />Links are named by the Page Title. All Links need a Page Title in order to function.<br /><br />If you forget to add a Page Title, then you will not be able to add the Link.<br /><br />If you do not assign an Order for the Header, Sidebox or Footer, then the Link will not display even if you have a Page Title.<br /><br /><span style="font-weight: bold;"><span style="color: rgb(255, 0, 0);">Link Placement</span><br /><br /></span>While you have the option of adding Additional Links to the Header, Footer and Sidebox with EZ-Pages, you are not limited to these three Link locations. Links can be in one or more locations simply by enabling the Order for the Location(s) where the Link should appear..<br /><br />The Link Location Status for the Header, Footer and Sidebox is controlled simply by setting these to Yes or No for each setting. Then, set the Order in which the Link should appear for each location.<br /><br />This means that if you were to set Header to Yes 30 and Sidebox to Yes 50 then the link would appear in both the Header and Sidebox in the Order of your Links.<br /><br />The Order numbering method is up to you. Numbering using 10, 20, 30, etc. will allow you to sort the Links and add additional Links later.<br /><br />Note: a 0 value for the Order will disable the Link from displaying.<br /><br /><span style="font-weight: bold;"><span style="color: rgb(255, 0, 0);">Open in New Window and Secure Pages</span><br /></span><br />With EZ-Pages, each Link can take you to the same, main window for your shop; or, you can have the Link open a brand new New Window. In addition, there is an option for making the Link open as a Secure Page or a Non-Secure Page.<br /><br /><span style="font-weight: bold; color: rgb(255, 0, 0);">Chapter and TOC</span><br style="font-weight: bold; color: rgb(255, 0, 0);" /><br />The Chapter and TOC, or Table of Contents, are a unique method of building Multiple Links that interact together.<br /><br />While these Links still follow the rules of the Header, Footer and Sidebox placement, the difference is that only one of the Links, the Main Link, needs to be displayed anywhere on the site.<br /><br />If you had, for example, 5 related Links, you could add the first Link as the Main Link by setting its location to the Header, Footer or Sidebox and set its Order, as usual.<br /><br />Next, you need to assign a Chapter or Group number to the Link. This Chapter holds the related Links together.<br /><br />Then, set the TOC or Table of Contents setting. This is a secondary Sort Order for within the Chapter.<br /><br />Again, you can display any of the Links within a Chapter, as well as making any of these Links the Main Link. Whether the Links all show, or just one or more of the Links show, the Chapter is the key to grouping these Links together in the TOC or Previous/Next. <br /><br /><span style="font-weight: bold; font-style: italic;">NOTE: While all Links within a Chapter will display together, you can have the different Links display in the Header, Footer or Sidebox on their own. Or, you can have the additional Links only display when the Main Link or one of the Additional Links within the Chapter has been opened.</span><br style="font-weight: bold; font-style: italic;" /><br />The versitility of EZ-Pages will make adding new Links and Pages extreamly easy for the beginner as well as the advance user.<br /><br />NOTE: Browser-based HTML editors will sometimes add the opening and closing tags for the &lt;html&gt;, &lt;head&gt; and &lt;body&gt; to the file you are working on.<br /><br />These are already added to the pages via EZ-Pages.<br /><br /><span style="color: rgb(255, 0, 0); font-weight: bold;">External Link URL</span><br /><br />External Link URLs are links to outside pages not within your shop. These can be to any valid URL such as:<br /><br />https://www.geekhost.ca<br /><br />You need to include the full URL path to any External Link URL. You may also mark these to open in a New Window or the Same Window.<br /><br /><span style="color: rgb(255, 0, 0); font-weight: bold;">Internal Link URL</span><br /><br />Internal Link URLs are links to internal pages within your shop. These can be to any valid URL, but should be written as relative links such as:<br /><br />index.php?main_page=index&amp;cPath=21<br /><br />The above Link would take you to the Category for categories_id 21<br /><br />While these links can be the Full URL to an Internal Link, it is best to write as a Relative Link so that if you change domains, are work on a temporary domain or an IP Address, the Link will remain valid if moved to another domain, IP Address, etc.<br /><br />Internal Links can also open in a New Window or the Same Window or be for Secure or Non-Secure Pages.<br /><br /><span style="font-weight: bold; color: rgb(255, 0, 0);">EZ-Pages Additional Pages vs Internal Links vs External Links</span><br /><br />The Type of Link that you create is based on an order of precidence, where HTML Content will supercede both the Internal Link and the External Link values.<br /><br />The External Link URL will supercede the Internal Link URL.<br /><br />If you try to set a combination of HTML Content, Internal Link and/or External Link, the Link will be flagged in the listing with a read icon to alert you to your mistake.<br /><br /><span style="font-weight: bold; color: rgb(255, 0, 0);">WARNING ...</span><br /><br />When using Editors such as TinyMCE or CKEditor, if you press enter in the HTML Content area <br /> will be added. These will be detected as &quot;content&quot; and will override any Internal Link URL or External Link URL.<br /><br /><span style="font-weight: bold; color: rgb(255, 0, 0);">Admin Only Display</span><br /><br />Sometimes, when working on EZ-Pages, you will want to be able to work on a Live Site and see the results of your work, but not allow the Customers to see this until you are done.<br /><br />There are 3 settings in the Configuration ... EZ-Pages Settings for the Header, Footer and Sidebox  Status:<br /><ul><li>OFF</li><li>ON</li><li>Admin Only</li></ul>The Admin Only setting is controlled by the IP Address(es) set in the Website Maintenance.<br /><br />This can be very handy when needing to work on a Live Site but not wanting customers to see the work in progress.<br /><br />',
                    'pages_id' => 14,
                    'pages_title' => 'What is EZ-Pages?',
                ),
        ));


    }
}
