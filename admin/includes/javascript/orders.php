<?php
/**
 * Javascript for Admin "orders" page
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 New in v2.0.1 $
 *
 * @var $order order
 */

// Copy address details to clipboard
if (isset($action) && $action === 'edit') {
    $address_types = ['customer', 'delivery', 'billing'];
    $addressBlock = [];
    foreach ($address_types as $address_type) {
        if ($order->{$address_type} === false) {
            $addressBlock[$address_type] = '';
            continue;
        }
        $addressBlock[$address_type] =
            $order->{$address_type}['name'] . '\n' .
            // Note the ternary is used with empty() (instead of ?? ) so that blank-but-not-null elements are excluded
            (empty($order->{$address_type}['company']) ? '' : $order->{$address_type}['company'] . '\n') .
            $order->{$address_type}['street_address'] . '\n' .
            (empty($order->{$address_type}['suburb']) ? '' : $order->{$address_type}['suburb'] . '\n') .
            (empty($order->{$address_type}['city']) ? '' : $order->{$address_type}['city'] . '\n') .
            $order->{$address_type}['state'] . '\n' .
            $order->{$address_type}['postcode'] . '\n' .
            $order->{$address_type}['country']['title'] . '\n' .
            (empty($order->{$address_type}['telephone']) ? '' : $order->{$address_type}['telephone']) . '\n' .
            (empty($order->{$address_type}['email_address']) ? '' : $order->{$address_type}['email_address']);
    }
    ?>
    <script>
        function copyToClipboard(addressType, element) {
            let address;
            switch (addressType) {
                case('customer') :
                    address = '<?= htmlspecialchars($addressBlock['customer']) ?>';
                    break;
                case('delivery') :
                    address = '<?= htmlspecialchars($addressBlock['delivery']) ?>';
                    break;
                case('billing') :
                    address = '<?= htmlspecialchars($addressBlock['billing']) ?>';
                    break;
                default:
                    return;
            }
            navigator.clipboard.writeText(address);
            element.innerHTML = '<?= TEXT_COPIED ?>';
        }
    </script>
<?php
}
