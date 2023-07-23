<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsDescriptionTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_description')->truncate();

        DB::table('products_description')->insert(array(
            0 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8&quot; PCI board.<br /><br />With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.<br /><br />Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.',
                    'products_id' => 1,
                    'products_name' => 'Matrox G200 MMS',
                    'products_url' => 'www.matrox.com/mga/products/g200_mms/home.cfm',
                    'products_viewed' => 0,
                ),
            1 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Dramatically Different High Performance Graphics<br /><br />Introducing the Millennium G400 Series - a dramatically different, high performance graphics experience. Armed with the industry\'s fastest graphics chip, the Millennium G400 Series takes explosive acceleration two steps further by adding unprecedented image quality, along with the most versatile display options for all your 3D, 2D and DVD applications. As the most powerful and innovative tools in your PC\'s arsenal, the Millennium G400 Series will not only change the way you see graphics, but will revolutionize the way you use your computer.<br /><br />Key features:<ul><li>New Matrox G400 256-bit DualBus graphics chip</li><li>Explosive 3D, 2D and DVD performance</li><li>DualHead Display</li><li>Superior DVD and TV output</li><li>3D Environment-Mapped Bump Mapping</li><li>Vibrant Color Quality rendering </li><li>UltraSharp DAC of up to 360 MHz</li><li>3D Rendering Array Processor</li><li>Support for 16 or 32 MB of memory</li></ul>',
                    'products_id' => 2,
                    'products_name' => 'Matrox G400 32MB',
                    'products_url' => 'www.matrox.com/mga/products/mill_g400/home.htm',
                    'products_viewed' => 0,
                ),
            2 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Every element of IntelliMouse Pro - from its unique arched shape to the texture of the rubber grip around its base - is the product of extensive customer and ergonomic research. Microsoft\'s popular wheel control, which now allows zooming and universal scrolling functions, gives IntelliMouse Pro outstanding comfort and efficiency.',
                    'products_id' => 3,
                    'products_name' => 'Microsoft IntelliMouse Pro',
                    'products_url' => 'www.microsoft.com/hardware/mouse/intellimouse.asp',
                    'products_viewed' => 0,
                ),
            3 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br />Languages: English, Deutsch.<br />Subtitles: English, Deutsch, Spanish.<br />Audio: Dolby Surround 5.1.<br />Picture Format: 16:9 Wide-Screen.<br />Length: (approx) 80 minutes.<br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 4,
                    'products_name' => 'The Replacement Killers',
                    'products_url' => 'www.replacement-killers.com',
                    'products_viewed' => 0,
                ),
            4 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).<br />Languages: English, Deutsch.<br />Subtitles: English, Deutsch, Spanish.<br />Audio: Dolby Surround 5.1.<br />Picture Format: 16:9 Wide-Screen.<br />Length: (approx) 112 minutes.<br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 5,
                    'products_name' => 'Blade Runner - Director\'s Cut Linked',
                    'products_url' => 'www.bladerunner.com',
                    'products_viewed' => 0,
                ),
            5 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch.
<br />
Audio: Dolby Surround.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 131 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Making Of.',
                    'products_id' => 6,
                    'products_name' => 'The Matrix Linked',
                    'products_url' => 'www.thematrix.com',
                    'products_viewed' => 0,
                ),
            6 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch, Spanish. <br />Subtitles: English, Deutsch, Spanish, French, Nordic, Polish. <br />Audio: Dolby Digital 5.1. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 115 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 7,
                    'products_name' => 'You\'ve Got Mail Linked',
                    'products_url' => 'www.youvegotmail.com',
                    'products_viewed' => 0,
                ),
            7 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch. <br />Subtitles: English, Deutsch, Spanish. <br />Audio: Dolby Digital 5.1 / Dolby Surround Stereo. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 91 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 8,
                    'products_name' => 'A Bug\'s Life Linked',
                    'products_url' => 'www.abugslife.com',
                    'products_viewed' => 0,
                ),
            8 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch. <br />Subtitles: English, Deutsch, Spanish. <br />Audio: Dolby Surround 5.1. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 98 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 9,
                    'products_name' => 'Under Siege Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            9 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 98 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 10,
                    'products_name' => 'Under Siege 2 - Dark Territory',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            10 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 100 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 11,
                    'products_name' => 'Fire Down Below Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            11 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch. <br />Subtitles: English, Deutsch, Spanish. <br />Audio: Dolby Surround 5.1. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 122 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 12,
                    'products_name' => 'Die Hard With A Vengeance Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            12 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 100 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 13,
                    'products_name' => 'Lethal Weapon Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            13 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 117 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 14,
                    'products_name' => 'Red Corner Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            14 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 115 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 15,
                    'products_name' => 'Frantic Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            15 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 112 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 16,
                    'products_name' => 'Courage Under Fire Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            16 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 112 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 17,
                    'products_name' => 'Speed Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            17 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 120 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 18,
                    'products_name' => 'Speed 2: Cruise Control',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            18 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 114 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 19,
                    'products_name' => 'There\'s Something About Mary Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            19 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Surround 5.1.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 164 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 20,
                    'products_name' => 'Beloved Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            20 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Windows 95/98<br /><br />211 in progress with shots fired. Officer down. Armed suspects with hostages. Respond Code 3! Los Angles, 2005, In the next seven days, representatives from every nation around the world will converge on Las Angles to witness the signing of the United Nations Nuclear Abolishment Treaty. The protection of these dignitaries falls on the shoulders of one organization, LAPD SWAT. As part of this elite tactical organization, you and your team have the weapons and all the training necessary to protect, to serve, and &quot;When needed&quot; to use deadly force to keep the peace. It takes more than weapons to make it through each mission. Your arsenal includes C2 charges, flashbangs, tactical grenades. opti-Wand mini-video cameras, and other devices critical to meeting your objectives and keeping your men free of injury. Uncompromised Duty, Honor and Valor!',
                    'products_id' => 21,
                    'products_name' => 'SWAT 3: Close Quarters Battle Linked',
                    'products_url' => 'www.swat3.com',
                    'products_viewed' => 0,
                ),
            21 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'From the creators of the best-selling Unreal, comes Unreal Tournament. A new kind of single player experience. A ruthless multiplayer revolution.<br /><br />This stand-alone game showcases completely new team-based gameplay, groundbreaking multi-faceted single player action or dynamic multi-player mayhem. It\'s a fight to the finish for the title of Unreal Grand Master in the gladiatorial arena. A single player experience like no other! Guide your team of \'bots\' (virtual teamates) against the hardest criminals in the galaxy for the ultimate title - the Unreal Grand Master.',
                    'products_id' => 22,
                    'products_name' => 'Unreal Tournament Linked',
                    'products_url' => 'www.unrealtournament.net',
                    'products_viewed' => 0,
                ),
            22 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The world in which The Wheel of Time takes place is lifted directly out of Jordan\'s pages; it\'s huge and consists of many different environments. How you navigate the world will depend largely on which game - single player or multipayer - you\'re playing. The single player experience, with a few exceptions, will see Elayna traversing the world mainly by foot (with a couple notable exceptions). In the multiplayer experience, your character will have more access to travel via Ter\'angreal, Portal Stones, and the Ways. However you move around, though, you\'ll quickly discover that means of locomotion can easily become the least of the your worries...<br /><br />During your travels, you quickly discover that four locations are crucial to your success in the game. Not surprisingly, these locations are the homes of The Wheel of Time\'s main characters. Some of these places are ripped directly from the pages of Jordan\'s books, made flesh with Legend\'s unparalleled pixel-pushing ways. Other places are specific to the game, conceived and executed with the intent of expanding this game world even further. Either way, they provide a backdrop for some of the most intense first person action and strategy you\'ll have this year.',
                    'products_id' => 23,
                    'products_name' => 'The Wheel Of Time Linked',
                    'products_url' => 'www.wheeloftime.com',
                    'products_viewed' => 0,
                ),
            23 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'A new age is dawning...<br /><br />Enter the realm of the Sacred Lands, where the dawn of a New Age has set in motion the most momentous of wars. As the prophecies long foretold, four races now clash with swords and sorcery in a desperate bid to control the destiny of their gods. Take on the quest as a champion of the Empire, the Mountain Clans, the Legions of the Damned, or the Undead Hordes and test your faith in battles of brute force, spellbinding magic and acts of guile. Slay demons, vanquish giants and combat merciless forces of the dead and undead. But to ensure the salvation of your god, the hero within must evolve.<br /><br />The day of reckoning has come... and only the chosen will survive.',
                    'products_id' => 24,
                    'products_name' => 'Disciples: Sacred Lands Linked',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            24 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Internet Keyboard has 10 Hot Keys on a comfortable standard keyboard design that also includes a detachable palm rest. The Hot Keys allow you to browse the web, or check e-mail directly from your keyboard. The IntelliType Pro software also allows you to customize your hot keys - make the Internet Keyboard work the way you want it to!',
                    'products_id' => 25,
                    'products_name' => 'Microsoft Internet Keyboard PS/2',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            25 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Microsoft introduces its most advanced mouse, the IntelliMouse Explorer! IntelliMouse Explorer features a sleek design, an industrial-silver finish, a glowing red underside and taillight, creating a style and look unlike any other mouse. IntelliMouse Explorer combines the accuracy and reliability of Microsoft IntelliEye optical tracking technology, the convenience of two new customizable function buttons, the efficiency of the scrolling wheel and the comfort of expert ergonomic design. All these great features make this the best mouse for the PC!',
                    'products_id' => 26,
                    'products_name' => 'Microsoft IntelliMouse Explorer',
                    'products_url' => 'www.microsoft.com/hardware/mouse/explorer.asp',
                    'products_viewed' => 0,
                ),
            26 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'HP has always set the pace in laser printing technology. The new generation HP LaserJet 1100 series sets another impressive pace, delivering a stunning 8 pages per minute print speed. The 600 dpi print resolution with HP\'s Resolution Enhancement technology (REt) makes every document more professional.<br /><br />Enhanced print speed and laser quality results are just the beginning. With 2MB standard memory, HP LaserJet 1100xi users will be able to print increasingly complex pages. Memory can be increased to 18MB to tackle even more complex documents with ease. The HP LaserJet 1100xi supports key operating systems including Windows 3.1, 3.11, 95, 98, NT 4.0, OS/2 and DOS. Network compatibility available via the optional HP JetDirect External Print Servers.<br /><br />HP LaserJet 1100xi also features The Document Builder for the Web Era from Trellix Corp. (featuring software to create Web documents).',
                    'products_id' => 27,
                    'products_name' => 'Hewlett Packard LaserJet 1100Xi Linked',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            27 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Purchase a Gift Certificate today to share with your family, friends or business associates!',
                    'products_id' => 28,
                    'products_name' => 'Gift Certificate $  5.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            28 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Purchase a Gift Certificate today to share with your family, friends or business associates!',
                    'products_id' => 29,
                    'products_name' => 'Gift Certificate $ 10.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            29 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Purchase a Gift Certificate today to share with your family, friends or business associates!',
                    'products_id' => 30,
                    'products_name' => 'Gift Certificate $ 25.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            30 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Purchase a Gift Certificate today to share with your family, friends or business associates!',
                    'products_id' => 31,
                    'products_name' => 'Gift Certificate $ 50.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            31 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Purchase a Gift Certificate today to share with your family, friends or business associates!',
                    'products_id' => 32,
                    'products_name' => 'Gift Certificate $100.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            32 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'A Bug\'s Life "Multi Pak" Special 2003 Collectors Edition
<br />
Regional Code: 2 (Japan, Europe, Middle East, South Africa).
<br />
Languages: English, Deutsch.
<br />
Subtitles: English, Deutsch, Spanish.
<br />
Audio: Dolby Digital 5.1 / Dolby Surround Stereo.
<br />
Picture Format: 16:9 Wide-Screen.
<br />
Length: (approx) 91 minutes.
<br />
Other: Interactive Menus, Chapter Selection, Subtitles (more languages).',
                    'products_id' => 34,
                    'products_name' => 'A Bug\'s Life "Multi Pak" Special 2003 Collectors Edition',
                    'products_url' => 'www.abugslife.com',
                    'products_viewed' => 0,
                ),
            33 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 0.00
<br /><br />

The Product Priced by Attributes is set to YES
<br /><br />

The attribute prices are defined without the price prefix of +
<br /><br />

The Display Price is made up of the lowest attribute price from each Option Name group.
<br /><br />

If there had been a Product Price, this would have been added together to the lowest attributes price from each of the Option Name groups to make up the display price.
<br /><br />

The price prefix of the + is not used as we are not "adding" to the display price.
<br /><br />

The Colors attributes are set for the discount to be applied, their prices before the discount are:<br />
White $499.99<br />
Black $519.00<br />
Blue $539.00<br />',
                    'products_id' => 36,
                    'products_name' => 'Hewlett Packard - by attributes SALE',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            34 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a free product that is also on special.
<br /><br />

This should show as having a price, special price but then be free.
<br /><br />

While this is a FREE product, this does have Shipping.',
                    'products_id' => 39,
                    'products_name' => 'A Free Product',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            35 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a Call for Price product that is also on special.
<br />

This should show as having a price, special price but then be Call for Price. This means you cannot buy it.
<br />',
                    'products_id' => 40,
                    'products_name' => 'A Call for Price Product',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            36 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a Call for Price product that is also on special and has a Sale price via Sale Maker.
<br /><br />

This should show as having a price, special price but then be Call for Price. This means you cannot buy it.
<br /><br />

The Add to Cart buttons automatically change to Call for Price, which is defined as: TEXT_CALL_FOR_PRICE
<br /><br />

This link will take the customer to the Contact Us page.
<br /><br />',
                    'products_id' => 41,
                    'products_name' => 'A Call for Price Product SALE',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            37 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a free product that is also on special.
<br />

This should show as having a price, special price but then be free.
<br />',
                    'products_id' => 42,
                    'products_name' => 'A Free Product - SALE',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            38 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a free product that is also on special.
<br /><br />

This should show as having a price, special price but then be free.
<br /><br />

Attributes can be added that can optionally be set to free or not free
<br /><br />

The Color Red attribute is priced at $5.00 but marked as a Free Attribute when the Product is Free
<br /><br />

The Size Medium attribute is priced at $1.00 but marked as a Free Attribute when Product is Free',
                    'products_id' => 43,
                    'products_name' => 'A Free Product with Attributes',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            39 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product has attributes and a minimum qty and units.
<br /><br />

Mixed is OFF this means you CANNOT mix attributes to make the minimums and units.
<br /><br />

The Size Option Value: Select from Below ... is a Display Only Attribute.
<br /><br />

This means that the product cannot be added to the Shopping Cart if that Option Value is selected. If it is still selected, then an error is triggered when the Add to Cart is pressed with a warning to the customer on what the error is.
<br /><br />

No checkout is allowed when errors exist.',
                    'products_id' => 44,
                    'products_name' => 'A Mixed OFF Product with Attributes',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            40 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product has attributes and a minimum qty and units.
<br /><br />

Mixed is ON this means you CAN mix attributes to make the minimums and units.
<br /><br />

Select from Below is a Display Only Attribute. This means that it cannot be added to the cart. If it is, then an error is triggered.
<br /><br />

No checkout is allowed when errors exist.',
                    'products_id' => 46,
                    'products_name' => 'A Mixed ON Product with Attributes',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            41 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Priced by Attributes Gift Certificates.',
                    'products_id' => 47,
                    'products_name' => 'Gift Certificates by attributes',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            42 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product for copying and deleting attributes.
<br /><br />
All of the images for this product are in the main /images directory and /large directory.
<br /><br />
The main products_image is called 1_small.jpg
<br /><br />
There are additional images for this product that will auto load located in /images called:<br />
1_small_01.jpg<br />
1_small_02.jpg<br />
1_small_03.jpg<br />
<br />
The large additional images are in /images/large called:<br />
1_small_01_LRG.jpg<br />
1_small_02_LRG.jpg<br />
1_small_03_LRG.jpg<br />

<br /><br />

The naming conventions for the additional images do not require that they be numeric. Using the numberic system helps establish the sort order of the images and how they will display.
<br /><br />

What is important is that all the additional images be located in the same directory and start with the same name: 1_small and end in the same extenion: .jpg
<br /><br />

The additional large images need to be located in the /large directory and use the same name plus the Large image suffix, defined in the Admin ... Images ... in this case _LRG
<br /><br />',
                    'products_id' => 48,
                    'products_name' => 'Test 1',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            43 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product for copying and deleting attributes.
<br /><br />

This was made using the Attributes Copy to Product in the new Admin ... Catalog ... Attributes Controller ... and copying the Attributes from the Test 1 product to Test 2.
<br /><br />

This product does not have any additional images.
<br /><br />

It does have a Large image located in /large
<br /><br />

This uses the same name: 2_small plus the large image suffix _LRG plus the matching extension .jpg to give the new name: /images/large/2_small_LRG.jpg
<br /><br />',
                    'products_id' => 49,
                    'products_name' => 'Test 2',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            44 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product for copying and deleting attributes.
<br /><br />

This was made using the Attributes Copy to Product in the new Admin ... Catalog ... Attributes Controller ... and copying the attributes from the Test 2 product to Test 3.
<br /><br />

This product does not have any additional images.
<br /><br />

It does NOT have a Large image located in /large
<br /><br />

This means that when you click on the image for enlarge, unless the original image is larger than the small image settings you will see the same image in the popup.
<br /><br />',
                    'products_id' => 50,
                    'products_name' => 'Test 3',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            45 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Free Shipping and Payment
<br /><br />

The Price is set to 25.00 ... but what makes it Free is that this product has been marked as:
<br />
Product is Free: Yes
<br /><br />

This would allow the product to be listed with a price, but the actual charge is $0.00
<br /><br />

The weight is set to 10 but could be set to 0. What really makes it truely Free Shipping is that it has been marked to be Always Free Shipping.
<br /><br />

Always Free shipping is set to: Yes<br />
This will not charge for shipping, but requres a shipping address.
<br /><br />

Because there is no shipping and the price is 0, the Zen Cart Free Charge comes up for the payment module and the other payment modules vanish.
<br /><br />

You can change the text on the Zen Cart Free Charge module to read as you would prefer.
<br /><br />

Note: if you add products that incur a charge or shipping charge, then the Zen Cart Free Charge payment module will vanish and the regular payment modules will show.',
                    'products_id' => 51,
                    'products_name' => 'Free Ship & Payment Virtual weight 10',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            46 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product Price is set to 0
<br /><br />

Payment weight is set to 2 ...
<br /><br />

Virtual is ON ... this will skip shipping address
<br /><br />',
                    'products_id' => 52,
                    'products_name' => 'Free Ship & Payment Virtual',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            47 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product is purchased based on minimums and units.
<br /><br />

The Min is set to 6 and the units is set to 3
<br /><br />

Quantity Minimums and Units are designed to more or less force the customer to make purchases of a Minimum Quantity ... and if need be, in Units.
<br /><br />

This product can only be purchased if you buy at least 6 ... and after that in units of 3 ... 9, 12, 15, 18, etc.
<br /><br />

If you do not purchase it in the right Quantity, you will not be able to checkout.
<br /><br />

When adding to the cart, the quantity box on the product_info page is "smart". It will adjust itself based on what is in the cart.
<br /><br />

The Add to Cart buttons are also smart on New Products and Product Listing ... these also will adjust what is added to the cart.
<br /><br />

For example: If there is 1 in the cart, the next use of Add to Cart will add 5 more to make the Minimum of 6. Add again and 3 more will be added to keep the Units correct.
<br /><br />

Product Quantity Min/Unit Mix is for when a product has attributes.
<br /><br />

If Mix is ON then a mix of attributes options may be used to make up the Quantity Minimum and Units. This means you can mix 1 blue, 3 silver and 2 green to get 6.
<br /><br />

If the Mix is OFF then you may not mix 2 blue, 2 silver and 2 green to get 6.
<br /><br />

This product has the Product Qty Min/Unit Mix set to ON',
                    'products_id' => 53,
                    'products_name' => 'Min and Units MIX',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            48 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product is purchased based on minimums and units.
<br /><br />

The Min is set to 6 and the units is set to 3
<br /><br />

Quantity Minimums and Units are designed to more or less force the customer to make purchases of a Minimum Quantity ... and if need be, in Units.
<br /><br />

This product can only be purchased if you buy at least 6 ... and after that in units of 3 ... 9, 12, 15, 18, etc.
<br /><br />

If you do not purchase it in the right Quantity, you will not be able to checkout.
<br /><br />

When adding to the cart, the quantity box on the product_info page is "smart". It will adjust itself based on what is in the cart.
<br /><br />

The Add to Cart buttons are also smart on New Products and Product Listing ... these also will adjust what is added to the cart.
<br /><br />

For example: If there is 1 in the cart, the next use of Add to Cart will add 5 more to make the Minimum of 6. Add again and 3 more will be added to keep the Units correct.
<br /><br />

Product Quantity Min/Unit Mix is for when a product has attributes.
<br /><br />

If Mix is ON then a mix of attributes options may be used to make up the Quantity Minimum and Units. This means you can mix 1 blue, 3 silver and 2 green to get 6.
<br /><br />

If the Mix is OFF then you may not mix 2 blue, 2 silver and 2 green to get 6.
<br /><br />

This product has the Product Qty Min/Unit Mix set to OFF',
                    'products_id' => 54,
                    'products_name' => 'Min and Units NOMIX',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            49 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product is purchased based on minimums and units.
<br /><br />

The Min is set to 6 and the units is set to 3
<br /><br />

Quantity Minimums and Units are designed to more or less force the customer to make purchases of a Minimum Quantity ... and if need be, in Units.
<br /><br />

This product can only be purchased if you buy at least 6 ... and after that in units of 3 ... 9, 12, 15, 18, etc.
<br /><br />

If you do not purchase it in the right Quantity, you will not be able to checkout.
<br /><br />

When adding to the cart, the quantity box on the product_info page is "smart". It will adjust itself based on what is in the cart.
<br /><br />

The Add to Cart buttons are also smart on New Products and Product Listing ... these also will adjust what is added to the cart.
<br /><br />

For example: If there is 1 in the cart, the next use of Add to Cart will add 5 more to make the Minimum of 6. Add again and 3 more will be added to keep the Units correct.
<br /><br />

Product Quantity Min/Unit Mix is for when a product has attributes.
<br /><br />

If Mix is ON then a mix of attributes options may be used to make up the Quantity Minimum and Units. This means you can mix 1 blue, 3 silver and 2 green to get 6.
<br /><br />

If the Mix is OFF then you may not mix 2 blue, 2 silver and 2 green to get 6.
<br /><br />

This product has the Product Qty Min/Unit Mix set to ON
<br /><br />

This product has been placed on Sale via Sale Maker',
                    'products_id' => 55,
                    'products_name' => 'Min and Units MIX - Sale',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            50 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product is purchased based on minimums and units.
<br /><br />

The Min is set to 6 and the units is set to 3
<br /><br />

Quantity Minimums and Units are designed to more or less force the customer to make purchases of a Minimum Quantity ... and if need be, in Units.
<br /><br />

This product can only be purchased if you buy at least 6 ... and after that in units of 3 ... 9, 12, 15, 18, etc.
<br /><br />

If you do not purchase it in the right Quantity, you will not be able to checkout.
<br /><br />

When adding to the cart, the quantity box on the product_info page is "smart". It will adjust itself based on what is in the cart.
<br /><br />

The Add to Cart buttons are also smart on New Products and Product Listing ... these also will adjust what is added to the cart.
<br /><br />

For example: If there is 1 in the cart, the next use of Add to Cart will add 5 more to make the Minimum of 6. Add again and 3 more will be added to keep the Units correct.
<br /><br />

Product Quantity Min/Unit Mix is for when a product has attributes.
<br /><br />

If Mix is ON then a mix of attributes options may be used to make up the Quantity Minimum and Units. This means you can mix 1 blue, 3 silver and 2 green to get 6.
<br /><br />

If the Mix is OFF then you may not mix 2 blue, 2 silver and 2 green to get 6.
<br /><br />

This product has the Product Qty Min/Unit Mix set to OFF
<br /><br />

This product has been put on Sale via Sale Maker.',
                    'products_id' => 56,
                    'products_name' => 'Min and Units NOMIX - Sale',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            51 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a free product where there are no prices at all.
<br /><br />

The Always Free Shipping is also turned ON.
<br /><br />

If this is bought separately, the Zen Cart Free Charge payment module will show if there is no charges on shipping.
<br /><br />

If other products are purchased with a price or shipping charge, then the Zen Cart Free Charge payment module will not show and the shipping will be applied accordingly.',
                    'products_id' => 57,
                    'products_name' => 'A Free Product - All',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            52 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 0.00
<br /><br />

The Product Priced by Attributes is set to YES
<br /><br />

The attribute prices are defined without the price prefix of +
<br /><br />

The Display Price is made up of the lowest attribute price from each Option Name group.
<br /><br />

If there had been a Product Price, this would have been added together to the lowest attributes price from each of the Option Name groups to make up the display price.
<br /><br />

The price prefix of the + is not used as we are not "adding" to the display price.
<br /><br />',
                    'products_id' => 59,
                    'products_name' => 'Hewlett Packard - by attributes',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            53 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 499.75
<br /><br />

A Sale Maker Discount of 10% is applied.
<br /><br />

The attribute are marked to be discounted also.
<br /><br />

Prior to the discount being applied:<br />
Blue +$20.00<br />
Black +$10.00<br />
White $0.00
<br /><br />

4 meg +$50.00<br />
8 meg +$75.00<br />
16 meg +$100.00
<br /><br />',
                    'products_id' => 60,
                    'products_name' => 'Hewlett Packard - Sale with Attributes on Sale',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            54 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 499.75
<br /><br />

A Sale Maker Discount of 10% is applied.
<br /><br />

The attribute are marked NOT to be discounted.
<br /><br />

Prior to the discount being applied:<br />
Blue +$20.00<br />
Black +$10.00<br />
White $0.00
<br /><br />

4 meg +$50.00<br />
8 meg +$75.00<br />
16 meg +$100.00
<br /><br />',
                    'products_id' => 61,
                    'products_name' => 'Hewlett Packard - Sale with Attributes NOT on Sale',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            55 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 0.00 Special is set to 20%
<br /><br />

The Product Priced by Attributes is set to YES
<br /><br />

The attribute prices are defined without the price prefix of +
<br /><br />

The Display Price is made up of the lowest attribute price from each Option Name group.
<br /><br />

If there had been a Product Price, this would have been added together to the lowest attributes price from each of the Option Name groups to make up the display price.
<br /><br />

The price prefix of the + is not used as we are not "adding" to the display price.
<br /><br />

The Colors attributes are, their prices before the Special discount is applied:<br />
White $499.99<br />
Black $519.00<br />
Blue $539.00
<br /><br />

The Specials Price is a flat $100 discount. This $100 discount is applied to all attributes marked attributes_discounted Yes.',
                    'products_id' => 74,
                    'products_name' => 'Hewlett Packard - by attributes with Special% no SALE',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            56 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $100.00
<br /><br />
Special is 25%
<br /><br />
Sale is 10%
<br /><br />',
                    'products_id' => 76,
                    'products_name' => 'TEST 25% special 10% Sale',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            57 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Priced by Attributes - Product price is set to $0.00
<br /><br />
All attributes are marked to make the price.

<br /><br />
Product is $0
<br /><br />
Special is 25%
<br /><br />
Sale is 10%
<br /><br />',
                    'products_id' => 78,
                    'products_name' => 'TEST 25% special 10% Sale Attribute Priced',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            58 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Priced by Attributes - Product price is set to $0.00
<br /><br />
All attributes are marked to make the price.

<br /><br />
Product is $0
<br /><br />
Special is 25%
<br /><br />',
                    'products_id' => 79,
                    'products_name' => 'TEST 25% Special Attribute Priced',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            59 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $100.00
<br /><br />
Special is 25%
<br /><br />',
                    'products_id' => 80,
                    'products_name' => 'TEST 25% Special',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            60 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Sale is -$5.00
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 82,
                    'products_name' => 'TEST $120 Sale -$5.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            61 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />

Special $90.00 or 25%
<br /><br />

Sale is -$5.00
<br /><br />


Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 83,
                    'products_name' => 'TEST $120 Special $90.00 Sale -$5.00',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            62 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />

There is no special and no sale on this product.
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75<br />
- Green $40
<br /><br />

Size:<br />
Select from Below:<br />
X-Small $40.00<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- None<br />
- Embossed Collector\'s Tin $40.00<br />
- Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

Features: <br />
Quality Design<br />
Custom Handling<br />
Same Day Shipping<br />
<br /><br />

NOTE: Select from below ... is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />

The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />

The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />

NOTE: None is similar to Display Only, but this can be used when for when no option value is required.
<br /><br />

Its value is set the value to $0.00 and it is NOT marked Display Only.
<br /><br />

Because its value is $0.00 if included in the Attributes Based Price on products Priced by Attributes, this Options group would not have any value included in the calculated price.
<br /><br />

NOTE: The Option Name: Featured is a READ ONLY Option Type
<br /><br />
Read-only attributes can be used to display repetitive information or any property that occurs on multiple products and are set up like any other type of attribute. They do not get added to the Shopping Cart and are not included in any order\'s information. When using the "Attribute Controller", each read-only attribute should have its Base Price button set to off.
<br /><br />',
                    'products_id' => 84,
                    'products_name' => 'TEST $120',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            63 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />

There is a $90.00 or 25% Special and no sale on this product.
<br /><br />


Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 85,
                    'products_name' => 'TEST $120 Special $90',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            64 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special is $105
<br /><br />
Sale Price is $90 or 25% - Skip Products with Specials
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 88,
                    'products_name' => 'TEST $120 Sale Special $90 Skip',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            65 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special does not exist
<br /><br />
Sale Price is 10% - Skip Products with Specials
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 89,
                    'products_name' => 'TEST $120 Sale 10% Special off Skip',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            66 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special is 25% or $90
<br /><br />
Sale Price is 10%
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 90,
                    'products_name' => 'TEST $120 Sale 10% Special',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            67 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special does not exist
<br /><br />
Sale Price is 10%
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 92,
                    'products_name' => 'TEST $120 Sale 10% Special off',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            68 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special does not exist
<br /><br />
Sale Price is New Price $100
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

Attributes are not affected by the Sale Discount Price when a New Price is used.
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 93,
                    'products_name' => 'TEST $120 Special off Sale New Price $100',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            69 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special 25% or $90
<br /><br />
Sale Price is New Price $100
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

Attributes are not affected by the Sale Discount Price when a New Price is used.
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 94,
                    'products_name' => 'TEST $120 Special 25% Sale New Price $100',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            70 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special 25% or $90
<br /><br />
Sale Price is New Price $100
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

Attributes are not affected by the Sale Discount Price when a New Price is used.
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 95,
                    'products_name' => 'TEST $120 Special 25% Sale New Price $100 Skip Specials',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            71 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special does not exist
<br /><br />
Sale Price is New Price $100
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

Attributes are not affected by the Sale Discount Price when a New Price is used.
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 96,
                    'products_name' => 'TEST $120 Special off Sale New Price $100 Skip Specials',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            72 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special is 25% or $90
<br /><br />
Sale Price is 10%
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 97,
                    'products_name' => 'TEST $120 Sale 10% Special - Apply to price',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            73 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Special does not exist
<br /><br />
Sale Price is 10%
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 98,
                    'products_name' => 'TEST $120 Sale 10% Special off - Apply to Price',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            74 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product has Free Shipping.
<br /><br />

The weight is set to 5
<br /><br />

While the weight is set to 5, it has the Always Free Shipping set to YES and the Free Shipping Module is installed.
<br /><br />',
                    'products_id' => 99,
                    'products_name' => 'Free Shipping Product with Weight',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            75 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Price is set to 0.00
<br /><br />

The Product Priced by Attributes is set to YES
<br /><br />

The attribute prices are defined without the price prefix of +
<br /><br />

The Display Price is made up of the lowest attribute price from each Option Name group.
<br /><br />

If there had been a Product Price, this would have been added together to the lowest attributes price from each of the Option Name groups to make up the display price.
<br /><br />

The price prefix of the + is not used as we are not "adding" to the display price.
<br /><br />

The Colors attributes are set for the discount to be applied, their prices before the discount are:<br />
White $499.99<br />
Black $519.00<br />
Blue $539.00<br />',
                    'products_id' => 100,
                    'products_name' => 'Hewlett Packard - by attributes SALE with Special',
                    'products_url' => 'www.pandi.hp.com/pandi-db/prodinfo.main?product=laserjet1100',
                    'products_viewed' => 0,
                ),
            76 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is Priced by Attributes.
<br /><br />

Attribute Option Group: Color and Size are used for pricing by marking these as Included in Base Price.
<br /><br />

Gift Options has everything marked included in base price also, but because None is set to $0.00 that groups lowest price is $0.00 so it is not affecting the Base Price.
<br /><br />

If None was not part of this group and you did not want to include those prices, you would mark all of the Gift Option Attributes to NOT be included in the Base Price.
<br /><br />

Product Product is $0.00
<br /><br />

Special does not exist
<br /><br />
Sale Price is 10%
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 101,
                    'products_name' => 'TEST $120 Sale 10% Special off',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            77 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product does not show the Quantity Box when Adding to the Shopping Cart.
<br /><br />

This will add 1 to the Shopping Cart when Add to Cart is hit.
<br /><br />

NOTE: If using Quantity Box Shows set to NO, unless Qty Max is also set to 1 then each time the Add to Cart is clicked the current cart quantity is updated by 1. If Qty Max is set to 1 then no more than 1 will be able to be added to the Shopping Cart per order.
<br /><br />

Because the Image name is: 1_small.jpg<br />
and stored in the /images directory ...
<br /><br />

The other matching images will show:
<br /><br />
/images/1_small_00.jpg<br />
/images/1_small_02.jpg<br />
/images/1_small_03.jpg
<br /><br />

The matching large images from /images/large will show:
<br /><br />
/images/large/1_small_00_LRG.jpg<br />
/images/large/1_small_02_LRG.jpg<br />
/images/large/1_small_03_LRG.jpg
<br /><br />

A matching image must begin with the same exact name as the Product Image and end in the same extension.
<br /><br />

These will then auto load.
<br /><br />',
                    'products_id' => 104,
                    'products_name' => 'Hide Quantity Box',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            78 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product only allows Quantity 1 because the Products Qty Maximum is set to 1.
<br /><br />

This means there will be no Quantity box.
<br /><br />

Add button will not add more than a total of 1 to the Shopping Cart.
<br /><br />',
                    'products_id' => 105,
                    'products_name' => 'A Maximum Sample of 1',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            79 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product only allows Quantity 1 because the Products Qty Maximum is set to 3.
<br /><br />

This means there will be a Quantity box.
<br /><br />

Add button will not add more than a total of 3 to the Shopping Cart.
<br /><br />',
                    'products_id' => 106,
                    'products_name' => 'A Maximum Sample of 3',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            80 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product has Free Shipping.
<br /><br />

The weight is set to 0
<br /><br />

It has the Always Free Shipping set to NO and the Free Shipping Module is installed but it will still ship for Free.
<br /><br />

In the Configruation settings for Shipping/Packaging ... Order Free Shipping 0 Weight Status has been defined to be Free Shipping.
<br /><br />

NOTE: if that setting is changed, then this product will NOT ship for free, even though the weight is set to 0.
<br /><br />',
                    'products_id' => 107,
                    'products_name' => 'Free Shipping Product without Weight',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            81 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product is Sold Out because the product quantity is <= 0
<br /><br />

Because the Configuration Settings in Stock are defined that Sold Out Products are not disabled and Sold Out cannot be purchased, the add to cart buttons are changed to either the large or small Sold Out image.
<br /><br />

If you change the Configuration Settings in Stock, then you will be able to purchase this product, even though it is Sold Out.
<br /><br />',
                    'products_id' => 108,
                    'products_name' => 'A Sold Out Product',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            82 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This product does not show the Quantity Box when Adding to the Shopping Cart.
<br /><br />

While Quantity Box Shows is set to YES, the Product Qty Max has been set to 1

This will add only 1 to the Shopping Cart when Add to Cart is hit.
<br /><br />

The reason for this is that this is a download. As a download, there is never a reason to allow more than quantity 1 to be ordered.
<br /><br />

NOTE: If using Quantity Box Shows set to NO, unless Qty Max is also set to 1 then each time the Add to Cart is clicked the current cart quantity is updated by 1. If Qty Max is set to 1 then no more than 1 will be able to be added to the Shopping Cart per order.
<br /><br />

Two methods are available to trigger the Hide Quantity Box.
<br /><br />

Method 1: Set Quantity Box Shows to NO
<br /><br />

Method 2: Set Qty Maximum to 1
<br /><br />

In either case, you will only be able to add qty 1 to the shopping cart and the quantity box will not show.
<br /><br />',
                    'products_id' => 109,
                    'products_name' => 'Hide Quantity Box Methods',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            83 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />
Sale is -$5.00
<br /><br />
Specials are skipped
<br /><br />

Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 110,
                    'products_name' => 'TEST $120 Sale -$5.00 Skip',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            84 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product is $120
<br /><br />

Special $90.00 or 25% - Specials are Skipped
<br /><br />

Sale is -$5.00
<br /><br />


Attributes:<br />
Color:<br />
- Red $100.00<br />
- Orange $50.00<br />
- Yellow $75
<br /><br />

Size:<br />
Select from Below:<br />
Small $50.00<br />
Medium $75.00<br />
Large $100.00
<br /><br />

Gift Options:<br />
- Dated Collector\'s Tin $50.00<br />
- Autographed Memorabila Card $75.00<br />
- Wrapping $100.00
<br /><br />

NOTE: Select from below is defined as a Display Only Attribute and NOT to be included in the base price.
<br /><br />
The Display Only means, the customer may NOT select this option value. If they do not selected another option value, then the product cannot be added to the shopping cart.
<br /><br />
The NOT included in base price means, if this product were priced by attributes, it would not be include. The reason for this is so that the lowest price of this group will be the Small at $50.00 and not Select from Below at $0.00
<br /><br />',
                    'products_id' => 111,
                    'products_name' => 'TEST $120 Special $90.00 Sale -$5.00 Skip',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            85 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 112,
                    'products_name' => 'Test Two',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            86 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 113,
                    'products_name' => 'Test Four',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            87 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 114,
                    'products_name' => 'Test Five',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            88 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 115,
                    'products_name' => 'Test One',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            89 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 116,
                    'products_name' => 'Test Eight',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            90 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 117,
                    'products_name' => '<strong>Test<br /> Three</strong>',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            91 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 118,
                    'products_name' => 'Test Ten',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            92 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 119,
                    'products_name' => 'Test Six',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            93 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 120,
                    'products_name' => 'Test Seven',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            94 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 121,
                    'products_name' => 'Test Twelve',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            95 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 122,
                    'products_name' => 'Test Nine',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            96 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test product to fill this category with more 12 randomly entered products to invoke the split page results on products that are not linked, have no specials, sales, etc.',
                    'products_id' => 123,
                    'products_name' => 'Test Eleven',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            97 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a normal product priced at $15</p><p>There are quantity discounts setup which will be discounted from the Products Price.</p><p>Discounts are added on the Products Price Manager.</p>',
                    'products_id' => 127,
                    'products_name' => 'Normal Product',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            98 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a Special product priced at $15 with a $10 Special</p><p>There are quantity discounts setup which will be discounted from the Special Price.</p><p>Discounts are added on the Products Price Manager.</p>',
                    'products_id' => 130,
                    'products_name' => 'Special Product',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            99 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Product is priced by attribute</p><p>The Option Name Line 1 is setup as Text</p><p>The attribute is added to the product as Required</p><p>The pricing is $0.05 per word</p><p>The Option Name Line2 is setup as Text</p><p>The attribute is added to the product as Required</p><p>The pricing is $0.05 per word with 3 words Free</p><p>The Colors are set up as radio buttons and Red is the Default.</p>',
                    'products_id' => 131,
                    'products_name' => 'Per word - required',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            100 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Products Price is set to 0 and Products Weight is set to 1</p><p>This is marked Price by Attributes</p><p>This is priced by attribute at 14.45 per club with an added weight of 1 on the attributes.</p><p>This will make the shipping weight 1lb for the product plus 1lb for each attribute (club) added.</p><p>The attributes are sorted so the clubs read in order on the Product Info, Shopping Cart, Order, Email and Account History.</p>',
                    'products_id' => 132,
                    'products_name' => 'Golf Clubs',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            101 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is set up to have multiple downloads.</p><p>The Product Price is $49.99</p><p>The attributes are setup with two Option Names, one for each download to allow for two downloads at the same time.</p><p>The first Download is listed under:</p><p>Option Name: Version<br />Option Value: Download Windows - English<br />Option Value: Download Windows - Spanish<br />Option Value: DownloadMAC - English<br /></p><p>The second Download is listed under:</p><p>Option Name: Documentation<br />Option Value: PDF - English<br />Option Value:MS Word- English</p>',
                    'products_id' => 133,
                    'products_name' => 'Multiple Downloads',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            102 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Product is priced by attribute</p><p>The Option Name Line 1 is setup as Text</p><p>The attribute is added to the product as Required</p><p>The pricing is $0.02 per letter</p><p>The Option Name Line2 is setup as Text</p><p>The attribute is added to the product as Required</p><p>The pricing is $0.02 per letter with 3 letters free</p><p>The Colors are set up as radio buttons and Red is the Default.</p>',
                    'products_id' => 134,
                    'products_name' => 'Per letter - required',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            103 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Rope is sold by foot or yard with a minimum length of 10 feet or 10 yards</p><p>Product Price of $1.00<br />Product Weight of 0</p><p>Option Values:<br />per foot $0.00 weight .25<br />per yard $1.50 weight .75</p>',
                    'products_id' => 154,
                    'products_name' => 'Rope',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            104 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is priced at $10.00</p><p>The attributes are priced using the Price Factor</p><p>Red is $10<br />Yellow is $20<br />Green is $30</p><p>This makes the total price $10 + the price factor of the attribute.</p>',
                    'products_id' => 155,
                    'products_name' => 'Price Factor',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            105 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is priced at $10.00</p><p>The attributes are priced using the Price Factor and Price Factor Offset</p><p>Red is $10 ($0)<br />Yellow is $20 ($10)<br />Green is $30 ($20)</p><p>The Price Factor Offset is set to 1 to take out the price of the Product Price then make the total price $10 + the price factor * $10 - price factor offset * $10 for the attributes.</p>',
                    'products_id' => 156,
                    'products_name' => 'Price Factor Offset',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            106 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is priced at $10.00</p><p>It is marked Price by Attributes.</p><p>The attributes are priced using the Price Factor and Price Factor Offset. </p><p>The actual Product Price is used just to compute the Price Factor.</p><p>Red is $10 ($0)<br />Yellow is $20 ($10)<br />Green is $30 ($20)</p><p>The Price Factor Offset is set to 1 to take out the price of the Product Price then make the total price the price factor * $10 - price factor offset * $10 for the attributes.</p>',
                    'products_id' => 157,
                    'products_name' => 'Price Factor Offset by Attribute',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            107 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is $45 with a one time charge set on the colors.</p><p>Red $5<br />Yellow is $10<br />Green is $15</p>',
                    'products_id' => 158,
                    'products_name' => 'One Time Charge',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            108 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Attribute qty discounts are attribute prices based on qty ordered.</p><p>Enter them as: </p><p>Red:<br />3:10.00,6:9.00,9:8.00,12:7.00,15:6.00</p><p>Yellow<br />3:10.50,6:9.50,9:8.50,12:7.50,15:6.50</p><p>Green:<br />3:11.00,6:10.00,9:9.00,12:8.00,15:7.00</p><p>A table will also show on the page to display these discounts as well as an indicator that qty discounts are available.</p>',
                    'products_id' => 159,
                    'products_name' => 'Attribute Quantity Discount',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            109 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Products Price is set to 0 and Products Weight is set to 1</p><p>This is marked Price by Attributes</p><p>This is priced by attribute at 14.45 per club with an added weight of 1 on the attributes.</p><p>This will make the shipping weight 1lb for the product plus 1lb for each attribute (club) added.</p><p>The attributes are sorted so the clubs read in order on the Product Info, Shopping Cart, Order, Email and Account History.</p>',
                    'products_id' => 160,
                    'products_name' => 'Golf Clubs',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            110 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Rope is sold by foot or yard with a minimum length of 10 feet or 10 yards</p><p>Product Price of $1.00<br />Product Weight of 0</p><p>Option Values:<br />per foot $0.00 weight .25<br />per yard $1.50 weight .75</p>',
                    'products_id' => 165,
                    'products_name' => 'Rope',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            111 =>
                array(
                    'language_id' => 1,
                    'products_description' => '',
                    'products_id' => 166,
                    'products_name' => 'Russ Tippins Band - The Hunter',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            112 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a test document',
                    'products_id' => 167,
                    'products_name' => 'Test Document',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            113 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Product General Type are your regular products.

There are no special needs or layout issues to work with.',
                    'products_id' => 168,
                    'products_name' => 'Sample of Product General Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            114 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'The Product Music Type is specially designed for music media.

This can offer a lot more flexibility than the Product General.',
                    'products_id' => 169,
                    'products_name' => 'Sample of Product Music Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            115 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Document General Type is used for Products that are actually Documents.

These cannot be added to the cart but can be configured for the Document Sidebox. If your Document Sidebox is not showing, go to the Layout Controller and turn it on for your template.',
                    'products_id' => 170,
                    'products_name' => 'Sample of Document General Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            116 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'Document Product Type is used for Documents that are also available for sale. <br /><br />You might wish to display brief peices of the Document then offer it for sale. <br /><br />This Product Type is also handy for downloadable Documents or Documents available either on CD or by download. <br /><br />The Document Product Type could be used in the Document Sidebox or the Categories Sidebox depending on how you configure its master categories id. <br /><br />This product has also been added as a linked product to the Document Category. This will allow it to show in both the Category and Document Sidebox. While not marked specifically for the master product type id related to the Product Types, it now is in a Product Type set for Document General so it will show in both boxes.',
                    'products_id' => 171,
                    'products_name' => 'Sample of Document Product Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            117 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>Product Free Shipping can be setup to highlight the Free Shipping aspect of the product. <br /><br />These pages include a Free Shipping Image on them. <br /><br />You can define the ALWAYS_FREE_SHIPPING_ICON in the language file. This can be Text, Image, Text/Image Combo or nothing. <br /><br />The weight does not matter on Always Free Shipping if you set Always Free Shipping to Yes. <br /><br />Be sure to have the Free Shipping Module Turned on! Otherwise, if this is the only product in the cart, it will not be able to be shipped. <br /><br />Notice that this is defined with a weight of 5lbs. But because of the Always Free Shipping being set to Y there will be no shipping charges for this product. <br /><br />You do not have to use the Product Free Shipping product type just to use Always Free Shipping. But the reason you may want to do this is so that the layout of the Product Free Shipping product info page can be layout specifically for the Free Shipping aspect of the product. <br /><br />This includes a READONLY attribute for Option Name: Shipping and Option Value: Free Shipping Included. READONLY attributes do not show on the options for the order.</p>',
                    'products_id' => 172,
                    'products_name' => 'Sample of Product Free Shipping Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            118 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This Book is sold as a Book that is shipped to the customer or as a Download.<br /><br />

Only the Book itself is on Special. The Downloadable versions are not on Special.<br /><br />

This Book under Categories/Products is set to:<br /><br />

Product Priced by Attributes: Yes<br />
Products Price: 0.00<br />
Weight: 0<br /><br />

An Option Name of: Version (type is dropdown)<br /><br />
Option Values of: Book Hard Cover<br /><br />
Download: MAC English<br /><br />
Download: Windows English<br /><br />

The Attributes are set as:<br />
Option Name: Version<br />
Option Value: Book Hard Cover<br />
Price Prefix is blank<br />
Price: 52.50<br />
Weight Prefix is blank
Weight: 1<br />
Include in Base Price When Priced by Attributes Yes<br />
Apply Discounts Used by Product Special/Sale: Yes<br /><br />

Option Name: Version<br />
Option Value: Download: MAC English<br />
Price Prefix is blank<br />
Price: 20.00<br />
Weight: 0
Include in Base Price When Priced by Attributes No<br />
Apply Discounts Used by Product Special/Sale: No<br /><br />

Option Name: Version<br />
Option Value: Download: Windows: English<br />
Price Prefix is blank<br />
Price: 20.00<br />
Weight: 0<br />
Include in Base Price When Priced by Attributes No<br />
Apply Discounts Used by Product Special/Sale: No<br /><br />

It is on Special for $47.50<br /><br />',
                    'products_id' => 173,
                    'products_name' => 'Book',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            119 =>
                array(
                    'language_id' => 1,
                    'products_description' => 'This is a Call for Price product with no price<br />

This should show as having a price, special price but then be Call for Price. This means you cannot buy it.
<br />',
                    'products_id' => 174,
                    'products_name' => 'A Call No Price',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            120 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a normal product priced at $60</p><p>There are quantity discounts setup which will be discounted from the Products Price.</p><p>Discounts are added on the Products Price Manager.</p><p>The Discounts are offered in increments of 1.</p><p>Note: Attributes do not inherit the Discount Qty discounts.</p><p>Attributes will inherit Discounts from Specials or sales. This can be customized per attribute by marking the Attribute to Include Product Price Special or Sale Discounts.</p><p>Red is $100.00 and marked to include the Price to Special discount but will not inherit the Discount Qty discount.</p><p>Green is $100 and marked not to include the Price to Special discount and will not inherit the Discount Qty discount.</p>',
                    'products_id' => 175,
                    'products_name' => 'Qty Discounts by 1',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            121 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a normal product priced at $100</p><p>There are quantity discounts setup which will be discounted from the Products Price by the dozen.</p><p>Discounts are added on the Products Price Manager.</p>',
                    'products_id' => 176,
                    'products_name' => 'Normal Product by the dozen',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            122 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a Special product priced at $100 with a $75 Special</p><p>There are quantity discounts setup which will be discounted from the Special Price discounted by the dozen.</p><p>Discounts are added on the Products Price Manager.</p>',
                    'products_id' => 177,
                    'products_name' => 'Special Product by the dozen',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            123 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This is a normal product priced at $60 with a special of $50</p><p>There are quantity discounts setup which will be discounted from the Products Price.</p><p>Discounts are added on the Products Price Manager.</p><p>The Discounts are offered in increments of 1.</p><p>Note: Attributes do not inherit the Discount Qty discounts.</p><p>Attributes will inherit Discounts from Specials or sales. This can be customized per attribute by marking the Attribute to Include Product Price Special or Sale Discounts.</p><p>Red is $100.00 and marked to include the Price to Special discount but will not inherit the Discount Qty discount.</p><p>Green is $100 and marked not to include the Price to Special discount and will not inherit the Discount Qty discount.</p>',
                    'products_id' => 178,
                    'products_name' => 'Qty Discounts by 1 Special',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            124 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is set up to have a single download.</p><p>The Product Price is $39.99</p><p>The attributes are setup with 1 Option Name, for the download to allow for one download but of various types.</p><p>The Download is listed under:</p><p>Option Name: Documentation<br />Option Value: PDF - English<br />Option Value: MS Word - English</p>',
                    'products_id' => 179,
                    'products_name' => 'Single Download',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
            125 =>
                array(
                    'language_id' => 1,
                    'products_description' => '<p>This product is set up to have a single download of PDF only.  In 1.5.7 and above, products like this can be added to the cart from the listing page.</p><p>The Product Price is $39.99</p><p>The Download is listed under:</p><p>Option Name: Documentation<br />Option Value: PDF - English<br /></p>',
                    'products_id' => 180,
                    'products_name' => 'Single Download, Single Type',
                    'products_url' => '',
                    'products_viewed' => 0,
                ),
        ));


    }
}
