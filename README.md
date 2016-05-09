# [Zen Cart&reg; - The Art of E-Commerce](https://www.zen-cart.com/) 
[![Build Status](https://travis-ci.org/zencart/zc-v1-series.svg)](https://travis-ci.org/zencart/zc-v1-series) 
[![Download Zen Cart&reg;](https://img.shields.io/sourceforge/dm/zencart.svg)](http://sourceforge.net/projects/zencart/files/latest/download)

===============

The latest stable version is currently [v1.5.5](https://github.com/zencart/zc-v1-series/releases/tag/v1.5.5).

[![Download Latest Official Zen Cart&reg; Release](https://a.fsdn.com/con/app/sf-download-button)<br>Download Latest Official Zen Cart&reg; Release](http://sourceforge.net/projects/zencart/files/latest/download)



You are currently viewing code for the upcoming Zen Cart&reg; v1.6.0 release, which brings optimizations for improved templating and responsive design, in addition to increased flexibility for customizing to each storeowner's own preferences.

Zen Cart is free software, with free support available 24/7 on the Zen Cart&reg; Support Site forums at <https://www.zen-cart.com/forum.php> provided by our enthusiastic community of actual Zen Cart&reg; users, integrators, and the developers themselves.

--------------------


Zen Cart&reg; v1.6.0
--------------------
[Download latest in-development version from github](https://github.com/zencart/zc-v1-series/archive/v160.zip)

Requirements
------------
Zen Cart&reg; requires you to provide your own webserver (shared or dedicated/VPS), with a standard LAMP stack (Linux/Apache/MySQL/PHP)

- Compatible with PHP 5.4.2 to 7.0.x
- Compatible with MySQL 5.1 to 5.7, via the PHP mysqli client
- CURL (via PHP) is used to communication with payment/shipping services
- Compatible with with Apache 2.2, (or Apache 2.4 with the backward-compatibility mode enabled for v2.2 .htaccess rules support.)
- Recommended Apache modules include: expires, headers, env, alias, deflate, ssl, mime, rewrite (in addition to other common modules)

Zen Cart&reg; "can" run on IIS, but with some limitations, namely the need to manually secure various folders with IIS equivalents to .htaccess rules.

Zen Cart&reg; can also run on Nginx, but does not supply a configuration file for enforcing any permission/access rules or optimization for serving page assets.


Installation
------------

Installation is simple:

1. [Download Zen Cart&reg;](http://sourceforge.net/projects/zencart/files)
2. Ensure you check that the md5/sha1 hash of the Zip matches those publicly posted.
  * The md5/sha1 values for verifying the zip files hosted at Sourceforge are displayed on the [Zen Cart&reg; website](https://www.zen-cart.com/) along with [instructions on how to verify the file using the hash values](http://www.zen-cart.com/content.php?305).
3. Unzip the downloaded zip file 
4. Everything inside the folder you unzipped needs to be uploaded to your webserver … for example, into your `public_html` or `www` or `html` folder (the folder will already exist on your webserver)
5. In your browser, enter the address to your site, such as: `www.example.com` (or if you uploaded it into another subdirectory such as `foldername` use `www.example.com/foldername`)
6. Rename the `/includes/dist-configure.php` and `/admin/includes/dist-configure.php` files to "`configure.php`" and make the files writable (so the install process can write your configuration information into them after you answer a few questions in the following steps).
7. Also make the `/cache` and `/logs` folders writable. (You will be prompted about making other folders writable during installation)
8. Follow the instructions that appear in your browser for installation. 

If some of the terms used in these brief instructions are things you don't understand, there is a much more detailed set of instructions in the [/docs/Implementation-Guide](http://www.zen-cart.com/docs/implementation-guide-v155.pdf) PDF.


Guidance for Secure Installations
---------------------------------
__The [Implementation Guide](http://www.zen-cart.com/docs/implementation-guide-v155.pdf) document is provided to give detailed instructions on how to install and secure your site in accordance with PCI Compliance requirements.__ Whether your site "needs" PCI Compliance or not is up to you to decide, but you should still follow the documented principles to maximize your site's resiliance against troublesome access attempted by any undesired/unauthorized visitors.


Documentation
-------------
Use your browser to open the [/docs/index.html](http://www.zen-cart.com/docs/index.html) page for links to documentation and the [Implementation Guide](http://www.zen-cart.com/docs/implementation-guide-v155.pdf).


Developer Documentation
-----------------------
Developers wishing to contribute to the Zen Cart&reg; core code may fork the [zencart/zc-v1-series](https://github.com/zencart/zc-v1-series) repository on github and issue Pull Requests from their own feature branches. For detailed help on using github, forking, branching, and contributing see [Contributing to Zen Cart code](http://docs.zen-cart.com/Contributing/).

Visit [docs.zen-cart.com](http://docs.zen-cart.com/Developer_Documentation/v1.5.5/) for v1.5.5-specific guidance on issues relevant to developers. This documentation site is very new, but content will be added over time.

Developers will find the standalone [Habitat VM](http://docs.zen-cart.com/Habitat/main) to be a useful tool for staging site upgrades and doing offline feature development or testing. Designers may like it for testing new templates without affecting the live site.


Support
-------
For free support, visit our support site: [https://www.zen-cart.com/forum.php](https://www.zen-cart.com/forum.php)

Follow Us
---------
For news and updates about Zen Cart&reg;, follow us on [Twitter](http://twitter.com/zencart) and [Facebook](http://facebook.com/zencart)

Sign up for our free [Newsletter](http://eepurl.com/bafnNj)

Subscribe to [Critical News Updates And Release Announcements](https://www.zen-cart.com/subscription.php?do=addsubscription&f=2)


&nbsp;  
  
*&copy;Copyright 2003-2016, Zen Cart&reg;. All rights reserved.*
