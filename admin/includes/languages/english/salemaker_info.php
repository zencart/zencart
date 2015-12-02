<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: salemaker_info.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE', 'Salemaker');
define('SUBHEADING_TITLE', 'Salemaker Usage Tips:');
define('INFO_TEXT', '<ul>
                      <li>
                        Always use a \'.\' as decimal point in deduction and pricerange.
                      </li>
                      <li>
                        Enter amounts in the same currency as you would when creating/editing a product.
                      </li>
                      <li>
                        In the Deduction fields, you can enter an amount or a percentage to deduct,
                        or a replacement price. (eg. deduct $5.00 from all prices, deduct 10% from
                        all prices or change all prices to $25.00)
                      </li>
                      <li>
                        Entering a price range narrows down the product range that will be affected. (eg.
                        products from $50.00 to $150.00)
                      </li>
                      <li>
                        You must choose the action to take if a product is a special <i>and</i> is subject to this sale:
						<ul>
                          <li>
                            <strong>Ignore Specials Price - Apply to Product Price and Replace Special</strong><br>
							The salededuction will be applied to the regular price of the product.
                            (eg. Regular price $10.00, Specials price is $9.50, SaleCondition is 10%.
							The product\'s final price will show $9.00 on sale. The Specials price is ignored.)
                          </li>
                          <li>
                            <strong>Ignore SaleCondition - No Sale Applied When Special Exists</strong><br>
                            The salededuction will not be applied to Specials. The Specials price will show just like
                            when there is no sale defined. (eg. Regular price $10.00, Specials price is $9.50,
                            SaleCondition is 10%. The product\'s final price will show $9.50 on sale.
                            The SalesCondition is ignored.)
                          </li>
                          <li>
                            <strong>Apply SaleDeduction to Specials Price - Otherwise Apply to Price</strong><br>
                            The salededuction will be applied to the Specials price. A compounded price will show.
                            (eg. Regular price $10.00, Specials price is $9.50, SaleCondition is 10%. The product\'s
                            final price will show $8.55. An additional 10% off the Specials price.)
                          </li>
                        </ul>
                      </li>
                      <li>
                        Leaving the start date empty will start the sale immediately.
                      </li>
                      <li>
                        Leave the end date empty if you do not want the sale to expire.</li>
                      <li>
                        Checking a category automatically includes the sub-categories.
                      </li>
                    </ul>');
define('TEXT_CLOSE_WINDOW', '[ close window ]');
?>