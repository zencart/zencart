Zen Cart&reg; - The Art of E-Commerce
===============

Zen Cart&reg; was the first Open Source e-Commerce web application to be fully PA-DSS Certified.

Zen Cart&reg; v1.5.6 is an update with several bugfix patches applied on top of the PA-DSS Certified version v1.5.4.

It's free software, with free community-driven support available 24/7 on the Zen Cart&reg; Support Site forums at <https://www.zen-cart.com/forum.php>

--------------------


Zen Cart&reg; v1.5.6c
---------------------

Compatibility
-------------
Zen Cart v1.5.6 is designed for:
 * PHP 5.6 to PHP 7.3 (it "can" work on PHP 5.5, but you really should use a newer version)
 * Apache 2.2 and 2.4
 * MySQL 5.1 to 5.7 (including MariaDB 10.0 to 10.3)


Installation
------------

Installation is simple:

1. [Download Zen Cart&reg;](http://sourceforge.net/projects/zencart/files)
2. Ensure you check that the md5/sha1 hash of the Zip matches those publicly posted.
  * The md5/sha1 values for verifying the zip files hosted at Sourceforge are displayed on the [Zen Cart&reg; website](https://www.zen-cart.com/) along with [instructions on how to verify the file using the hash values](https://www.zen-cart.com/content.php?305).
3. Unzip the downloaded zip file 
4. Everything inside the folder you unzipped needs to be uploaded to your webserver … for example, into your `public_html` or `www` or `html` folder (the folder will already exist on your webserver)
5. In your browser, enter the address to your site, such as: `www.example.com` (or if you uploaded it into another subdirectory such as `foldername` use `www.example.com/foldername`)
6. Rename the `/includes/dist-configure.php` and `/admin/includes/dist-configure.php` files to "`configure.php`" and make the files writable (so the install process can write your configuration information into them after you answer a few questions in the following steps).
7. Also make the `/cache` and `/logs` folders writable. (You will be prompted about making other folders writable during installation)
8. Follow the instructions that appear in your browser for installation. 

If some of the terms used in these brief instructions are things you don't understand, there is a much more detailed set of instructions in the [/docs/Implementation-Guide](https://www.zen-cart.com/docs/implementation-guide-v156.pdf) PDF.

Upgrading
---------
Recommended reading related to upgrading: https://www.zen-cart.com/entry.php?3


Guidance for Secure Installations
---------------------------------
__The [Implementation Guide](https://www.zen-cart.com/docs/implementation-guide-v156.pdf) document is provided to give detailed instructions on how to install and secure your site in accordance with PCI Compliance requirements.__ Whether your site "needs" PCI Compliance or not is up to you to decide, but you should still follow the documented principles to maximize your site's resilience against troublesome access attempted by any undesired/unauthorized visitors.


Documentation
-------------
Use your browser to open the [/docs/index.html](https://www.zen-cart.com/docs/index.html) page for links to documentation and the [Implementation Guide](https://www.zen-cart.com/docs/implementation-guide-v156.pdf).


Developer Documentation
-----------------------
Developers wishing to contribute to the Zen Cart&reg; core code may fork the [zencart/zencart](https://github.com/zencart/zencart) repository on github and issue Pull Requests from their own feature branches. For detailed help on using github, forking, branching, and contributing see [Contributing to Zen Cart code](http://docs.zen-cart.com/Contributing/).

Visit [docs.zen-cart.com](https://docs.zen-cart.com/Developer_Documentation/) for guidance on issues relevant to developers. This documentation site is very new, but content will be added over time.
Developers wishing to contribute documentation should fork [zencart/documentation](https://github.com/zencart/documentation) and contribute PRs. 


Support
-------
For free support, visit our support site: https://www.zen-cart.com/forum.php

Follow Us
---------
For news and updates about Zen Cart&reg;, follow us on [Twitter](http://twitter.com/zencart) and [Facebook](http://facebook.com/zencart)

Sign up for our free [Newsletter](http://eepurl.com/bafnNj)

Subscribe to [Critical News Updates And Release Announcements](https://www.zen-cart.com/subscription.php?do=addsubscription&f=2)


&nbsp;  

*&copy;Copyright 2003-2019, Zen Cart&reg;. All rights reserved.*

