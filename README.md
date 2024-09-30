![](https://github.com/zencart/zencart/workflows/Zen%20Cart%20Unit%20Tests/badge.svg?branch=master)


Zen Cart&reg; - The Art of E-Commerce
===============

Zen Cart&reg; was the first Open Source e-Commerce web application to be fully PA-DSS Certified.

Zen Cart v2.0.0 is the latest major update to the project.  It contains bugfixes and updates for PHP8 applied on top of the PA-DSS Certified version v1.5.4.

Zen Cart is free and open source software, with free community-driven support available 24/7 on the Zen Cart&reg; Support Site forums at [zen-cart.com/forum](https://www.zen-cart.com/forum.php)

--------------------


Zen Cart&reg; v2.1.0-dev
---------------------

Compatibility
-------------
Zen Cart v2.1.0 is designed for:
 * PHP 8.0 to PHP 8.4
 * MySQL 5.7.8+ or MariaDB 10.2.7+
 * Apache 2.4
 
Refer to [compatibility requirements](https://docs.zen-cart.com/user/first_steps/server_requirements/) for additional details.


Installation
------------

Installation is simple:

1. [Download Zen Cart](https://github.com/zencart/zencart/releases)
2. Ensure you check that the md5/sha1 hash of the Zip matches those publicly posted.
  * The sha1 values for verifying the zip files hosted at Github are displayed on the [Zen Cart&reg; website](https://www.zen-cart.com/) along with [instructions on how to verify the file using the hash values](https://docs.zen-cart.com/user/installing/validate_sha/).
3. Unzip the downloaded zip file 
4. Everything inside the folder you unzipped needs to be uploaded to your webserver … for example, into your `public_html` or `www` or `html` folder (the folder will already exist on your webserver)
5. In your browser, enter the address to your site, such as: `www.example.com` (or if you uploaded it into another subdirectory such as `foldername` use `www.example.com/foldername`)
6. Rename the `/includes/dist-configure.php` and `/admin/includes/dist-configure.php` files to "`configure.php`" and make the files writable (so the install process can write your configuration information into them after you answer a few questions in the following steps).
7. Also make the `/cache` and `/logs` folders writable. (You will be prompted about making other folders writable during installation)
8. Follow the instructions that appear in your browser for installation. 

If some of the terms used in these brief instructions are things you don't understand, there is a much more detailed set of instructions in the [/docs/Implementation-Guide](https://www.zen-cart.com/docs/) PDF.

Upgrading
---------
Recommended reading related to upgrading: https://docs.zen-cart.com/user/upgrading/


Guidance for Secure Installations
---------------------------------
__The [Implementation Guide](https://www.zen-cart.com/docs/implementation-guide-v200.pdf) document is provided to give detailed instructions on how to install and secure your site in accordance with PCI Compliance requirements.__ Whether your site "needs" PCI Compliance or not is up to you to decide, but you should still follow the documented principles to maximize your site's resilience against troublesome access attempted by any undesired/unauthorized visitors.


Documentation
-------------
Use your browser to open the [/docs/index.html](https://www.zen-cart.com/docs/index.html) page for links to release documentation and the [Implementation Guide](https://www.zen-cart.com/docs/).  A storeowner documentation repository also exists at [docs.zen-cart.com/user/](https://docs.zen-cart.com/user/). 

Developer Documentation
-----------------------
Developers wishing to contribute to the Zen Cart&reg; core code may fork the [zencart/zencart](https://github.com/zencart/zencart) repository on github and issue Pull Requests from their own feature branches.  Please see [CONTRIBUTING](CONTRIBUTING.md). 

Visit [docs.zen-cart.com/dev/](https://docs.zen-cart.com/dev/) for guidance on issues relevant to developers. This documentation site is very new, but content will be added over time.  

Developers wishing to contribute documentation should fork [zencart/documentation](https://github.com/zencart/documentation) and contribute PRs.  Please see [CONTRIBUTING to documentation](https://github.com/zencart/documentation/blob/master/CONTRIBUTING.md).



Source
------

The Zen Cart source code is available at: https://github.com/zencart/zencart

Support
-------
For free community-driven support with Zen Cart, visit our support site: https://www.zen-cart.com/forum.php


Donations/Sponsorship
---------------------
Sponsorship through GitHub is a simple and convenient way to say "thank you" to Zen Cart's maintainers and contributors, and to help fund its ongoing development.

Just click the "Sponsor" button [on the Zen Cart page on GitHub](https://github.com/zencart/zencart). 

If your company uses Zen Cart, note that sponsorship and donations to the project are a valid regular business expense.

You may also donate via our website at https://www.zen-cart.com/donate


Security
--------
We take security very seriously.

If you have discovered a critical security bug in Zen Cart, please email security [at] zen-cart [.] com with the details of the problem and how to trigger it.  Issues will be responded to in a timely manner.


Follow Us
---------
For news and updates about Zen Cart&reg;, follow us on [Twitter](http://twitter.com/zencart) and [Facebook](http://facebook.com/zencart)

Sign up for our free [Newsletter](http://eepurl.com/bafnNj)

Subscribe to [Critical News Updates And Release Announcements](https://www.zen-cart.com/subscription.php?do=addsubscription&f=2)

&nbsp;  

<p>This project is supported by:</p>
<p>
  <a href="https://www.digitalocean.com/">
    <img src="https://opensource.nyc3.cdn.digitaloceanspaces.com/attribution/assets/SVG/DO_Logo_horizontal_blue.svg" width="201px">
  </a>
</p>

&nbsp;  

*&copy;Copyright 2003-2024, Zen Cart&reg;. All rights reserved.*

