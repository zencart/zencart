/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2024 Mar 08 New in v2.0.0-rc1 $
 *
 * Support javascript for category_product_listing page.
 */

// Copy To support
$( () => {
    // Some options are only relevant to the "link" vs "duplicate" option.
    // Show/hide some panels based on the checked status of the radio buttons.
    $('input[name=copy_as]').on('change', (e) => {
        const duplicateSelected = e.target.getAttribute('value') === 'duplicate';

        // Show/hide panels relevant to the Duplicate option
        $('.duplicate-only')[duplicateSelected ? 'removeClass' : 'addClass']('hiddenField');
        // Show/hide panels relevant to the Link option
        $('.link-only')[duplicateSelected ? 'addClass' : 'removeClass']('hiddenField');
    })
})