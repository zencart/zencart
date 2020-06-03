<script>
  let tax_rates = [];
<?php
for ($i = 0, $n = sizeof($tax_class_array); $i < $n; $i++) {
  if ($tax_class_array[$i]['id'] > 0) {
    echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
  }
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

  function updateGross() {
    const taxRate = getTaxRate();
    let grossValue = $('input[name="products_price"]').val();

    if (taxRate > 0) {
      grossValue = grossValue * ((taxRate / 100) + 1);
    }

    $('input[name="products_price_gross"]').val(doRound(grossValue, 4));
  }

  function updateNet() {
    const taxRate = getTaxRate();
    let netValue = $('input[name="products_price_gross"]').val();

    if (taxRate > 0) {
      netValue = netValue / ((taxRate / 100) + 1);
    }

    $('input[name="products_price"]').val(doRound(netValue, 4));
  }
</script>