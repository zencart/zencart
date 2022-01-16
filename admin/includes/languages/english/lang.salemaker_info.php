<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
*/

$define = [
    'HEADING_TITLE' => 'Salemaker',
    'SUBHEADING_TITLE' => 'Salemaker Usage Tips:',
    'INFO_TEXT' => '<ul>
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
                    </ul>',
];

return $define;
