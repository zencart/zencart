# [Zen Cart&reg; - The Art of E-Commerce](https://www.zen-cart.com/) 
[![Build Status](https://travis-ci.org/zencart/zencart.svg)](https://travis-ci.org/zencart/zencart) 
[![Download Zen Cart&reg;](https://img.shields.io/sourceforge/dm/zencart.svg)](http://sourceforge.net/projects/zencart/files/latest/download)

===============

You are currently viewing code for our development branch, which will be our next larger release, initially dubbed as Zen Cart&reg; v2, which brings architectural improvements to allow for increased flexibility for customizing each storeowner's own preferences.

The latest stable version is currently [v1.5.6](https://github.com/zencart/zencart/releases).

[![Download Latest Official Zen Cart&reg; Release](https://a.fsdn.com/con/app/sf-download-button)<br>Download Latest Official Zen Cart&reg; Release](http://sourceforge.net/projects/zencart/files/latest/download)


Zen Cart is free software, with free support available 24/7 on the Zen Cart&reg; Support Site forums at <https://www.zen-cart.com/forum.php> provided by our enthusiastic community of actual Zen Cart&reg; users, integrators, and the developers themselves.

--------------------


Zen Cart&reg; v2 - Development Branch
--------------------
[Download latest in-development version from github](https://github.com/zencart/zencart/archive/develop.zip)

Requirements
------------
Zen Cart&reg; requires you to provide your own webserver (shared or dedicated/VPS), with a standard LAMP stack (Linux/Apache/MySQL/PHP), based on the following specifications

Requirements for v2:
- Written for PHP 7.3 (backward compatible to PHP 5.6)
- Written for MySQL 5.7 (compatible with 5.7 and MariaDB 10.1 to 10.3)
- CURL (compiled into PHP) is used for communication with payment/shipping services
- Apache 2.4 or 2.2
- Recommended Apache modules include: expires, headers, env, alias, deflate, ssl, mime, rewrite (in addition to other common modules)

Zen Cart&reg; can also run on Nginx, but requires that the server administrator understand properly configuring the nginx *.conf files when prompted with the recommended directives at the end of the zc_install setup phase.

Zen Cart&reg; has been reported to run on IIS, but with some limitations, namely the need to manually secure various folders with IIS equivalents to .htaccess rules.



Installation (for released stable version)
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


Guidance for Secure Installations
---------------------------------
__The [Implementation Guide](https://www.zen-cart.com/docs/implementation-guide-v156.pdf) document is provided to give detailed instructions on how to install and secure your site in accordance with PCI Compliance requirements.__ Whether your site "needs" PCI Compliance or not is up to you to decide, but you should still follow the documented principles to maximize your site's resiliance against troublesome access attempted by any undesired/unauthorized visitors.


Documentation
-------------
Use your browser to open the [/docs/index.html](http://www.zen-cart.com/docs/index.html) page for links to documentation and the [Implementation Guide](https://www.zen-cart.com/docs/implementation-guide-v156.pdf).


Developer Documentation
-----------------------
Developers wishing to contribute to the Zen Cart&reg; core code may fork the [zencart/zencart](https://github.com/zencart/zencart) repository on github and issue Pull Requests from their own feature branches. For detailed help on using github, forking, branching, and contributing see [Contributing to Zen Cart code](https://docs.zen-cart.com/Contributing/).

Visit [docs.zen-cart.com](https://docs.zen-cart.com) for version-specific guidance on issues relevant to developers.  You may also help with this site by forking the [zencart/documentation](https://github.com/zencart/documentation) repository on github and submitting pull requests.


Support
-------
For free support, visit our support site: [https://www.zen-cart.com/forum.php](https://www.zen-cart.com/forum.php)

Follow Us
---------
For news and updates about Zen Cart&reg;, follow us on [Twitter](https://twitter.com/zencart) and [Facebook](https://facebook.com/zencart)

Sign up for our free [Newsletter](http://eepurl.com/bafnNj)

Subscribe to [Critical News Updates And Release Announcements](https://www.zen-cart.com/subscription.php?do=addsubscription&f=2)


&nbsp;  
  
*&copy;Copyright 2003-2018, Zen Cart&reg;. All rights reserved.*
