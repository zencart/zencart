<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_STATS_SALES_REPORT_GRAPHS, '')) return;

// prepare data (last 30 days)

$days_to_show = 30;
$sales_data = [];
$orders_data = [];
$labels = [];
$today = time();

// initialize the arrays with 0 for the last 30 days
for ($i = $days_to_show - 1; $i >= 0; $i--) {
    $timestamp = strtotime("-$i days", $today);
    $date_key = date('Y-m-d', $timestamp);

    $sales_data[$date_key] = 0;
    $orders_data[$date_key] = 0;

    // Label format: "Jan 15"
    $labels[$date_key] = date('M j', $timestamp);
}

// SQL: get daily sales totals AND order counts
$sql = "SELECT date(o.date_purchased) as sale_date,
               SUM(ot.value) as total_sales,
               COUNT(DISTINCT o.orders_id) as total_orders
        FROM " . TABLE_ORDERS . " o
        LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id)
        WHERE ot.class = 'ot_total'
        AND o.date_purchased >= DATE_SUB(NOW(), INTERVAL " . (int)$days_to_show . " DAY)
        GROUP BY sale_date";

$result = $db->Execute($sql);

while (!$result->EOF) {
    $day = $result->fields['sale_date'];

    // map data if date falls within range
    if (isset($sales_data[$day])) {
        $sales_data[$day] = (float)$result->fields['total_sales'];
        $orders_data[$day] = (int)$result->fields['total_orders'];
    }
    $result->MoveNext();
}

// prepare JSON for JS
$js_labels      = json_encode(array_values($labels));
$js_sales_data  = json_encode(array_values($sales_data));
$js_orders_data = json_encode(array_values($orders_data));

?>

<div style="position: relative; height: 350px; width: 100%;">
    <canvas id="dashboardSalesChart"></canvas>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('dashboardSalesChart').getContext('2d');

        var gradientSales = ctx.createLinearGradient(0, 0, 0, 400);
        gradientSales.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
        gradientSales.addColorStop(1, 'rgba(54, 162, 235, 0.0)');

        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $js_labels; ?>,
                datasets: [
                    {
                        label: '<?php echo BOX_SALES_LABEL_REVENUE; ?>',
                        data: <?php echo $js_sales_data; ?>,
                        borderColor: '#337ab7', // Blue
                        backgroundColor: gradientSales,
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#337ab7',
                        pointRadius: 3,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: '<?php echo BOX_SALES_LABEL_ORDERS; ?>',
                        data: <?php echo $js_orders_data; ?>,
                        borderColor: '#d9534f',
                        backgroundColor: 'rgba(217, 83, 79, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointBackgroundColor: '#d9534f',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        fill: false,
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.parsed.y;

                                if (label === '<?php echo BOX_SALES_LABEL_REVENUE; ?>') {
                                    return label + ': ' + new Intl.NumberFormat('<?php echo BOX_SALES_GRAPH_NUMBER_FORMAT; ?>', { style: 'currency', currency: '<?php echo DEFAULT_CURRENCY; ?>' }).format(value);
                                } else {
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: false, text: '<?php echo BOX_SALES_LABEL_REVENUE; ?>' },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: false, text: '<?php echo BOX_SALES_LABEL_ORDERS; ?>' },
                        grid: { display: false },
                        min: 0,
                        suggestedMax: 10
                    }
                }
            }
        });
    });
</script>
