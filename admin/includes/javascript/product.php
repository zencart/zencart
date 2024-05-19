<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */
?>
<script>
    let tax_rates = [];
<?php
    foreach($tax_class_array as $key => $value) {
        if ($key === 0) continue;
        echo '    tax_rates["' . $value['id'] . '"] = ' . zen_get_tax_rate_value($value['id']) . ';' . "\n";
    }
?>

    function doRound(x, places) {
        return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
    }

    function getTaxRate() {
        const parameterVal = $('select[name="products_tax_class_id"]').val();
        if ((parameterVal > 0) && (tax_rates[parameterVal] > 0)) {
            return tax_rates[parameterVal];
        } else {
            return 0;
        }
    }

    function updateNoTax() {
        const taxRate = getTaxRate();
        let NoTaxValue = $('input[name="products_price_tax_incl"]').val();

        if (taxRate > 0) {
            NoTaxValue = NoTaxValue / ((taxRate / 100) + 1);
        }

        $('input[name="products_price"]').val(doRound(NoTaxValue, 4));
    }

    function updateTaxIncl() {
        const taxRate = getTaxRate();
        let TaxInclValue = $('input[name="products_price"]').val();

        if (taxRate > 0) {
            TaxInclValue = TaxInclValue * ((taxRate / 100) + 1);
        }

        $('input[name="products_price_tax_incl"]').val(doRound(TaxInclValue, 4));
    }
</script>
