# [Zen Cart&reg; - The Art of E-Commerce](http://www.zen-cart.com/) 
[![Build Status](https://travis-ci.org/zencart/zc-v1-series.svg)](https://travis-ci.org/zencart/zc-v1-series) 
[![ProjectStatus](http://stillmaintained.com/zencart/zc-v1-series.png)](http://stillmaintained.com/zencart)
[![Selenium Test Status](https://saucelabs.com/browser-matrix/zencart.svg)](https://saucelabs.com/u/zencart)

===============

Zen Cart&reg; v1.5.0 was the first Open Source e-Commerce web application to be fully PA-DSS Certified.

Zen Cart&reg; v1.6.0 brings improved templating and responsive design, in addition to increased flexibility for customizing to each storeowner's own preferences.

It's free software, with free support available 24/7 on the Zen Cart&reg; Support Site forums at <http://www.zen-cart.com/forum.php> provided by our enthusiastic community of actual Zen Cart&reg; users, integrators, and the developers themselves.

--------------------


Zen Cart&reg; v1.6.0
--------------------


Installation
------------

Installation is simple:

1. [Download Zen Cart&reg;](http://sourceforge.net/projects/zencart/files)
2. Ensure you check that the md5/sha1 hash of the Zip matches those publicly posted.
  * The md5/sha1 values for verifying the zip files hosted at Sourceforge are displayed on the [Zen Cart&reg; website](http://www.zen-cart.com/) along with [instructions on how to verify the file using the hash values](http://www.zen-cart.com/content.php?305).
3. Unzip the downloaded zip file 
4. Everything inside the folder you unzipped needs to be uploaded to your webserver … for example, into your public_html or www or html folder (the folder will already exist on your webserver)
5. In your browser, enter the address to your site, such as: `www.your_site.com` (or if you uploaded it into another foldername use `www.your_site.com/foldername` )
6. Rename the `/includes/dist-configure.php` and `/admin/includes/dist-configure.php` files to `configure.php` and make the files writable (so the install process can write your configuration information into them after you answer a few questions in the following steps).
7. Also make the `/cache` and `/logs` folders writable. (You will be prompted about making other folders writable during installation)
8. Follow the instructions that appear in your browser for installation. 

That's the abbreviated version of installation instructions!

For a MUCH more detailed set of installation instructions, see the [/docs/1.readme_installation.html](http://www.zen-cart.com/docs/1.readme_installation.html) file in your Zen Cart files.

PCI/PA-DSS Compliance
---------------------
Currently only the v1.5.0 release is PA-DSS certified, and only when downloaded from the official distribution server: [Download Zen Cart&reg;](http://sourceforge.net/projects/zencart/files).  

Other branches are not PA-DSS Certified unless otherwise stated.
 
__The [Implementation Guide](http://www.zen-cart.com/docs/implementation-guide-v153.pdf) should be followed for PCI Compliant implementation.__

Documentation
-------------
Use your browser to open the [/docs/index.html](http://www.zen-cart.com/docs/index.html) page for links to documentation and the [Implementation Guide](http://www.zen-cart.com/docs/implementation-guide-v153.pdf).


Support
-------
For free support, visit our support site: http://www.zen-cart.com/forum.php

Follow Us
---------
For news and updates about Zen Cart&reg;, follow us on [Twitter](http://twitter.com/zencart) and [Facebook](http://facebook.com/zencart)

Sign up for our free [Newsletter](http://visitor.constantcontact.com/d.jsp?m=1101879248585)

Subscribe to [Critical News Updates And Release Announcements](http://www.zen-cart.com/subscription.php?do=addsubscription&f=2)


&nbsp;  
  
*&copy;Copyright 2003-2013, Zen Cart&reg;. All rights reserved.*

