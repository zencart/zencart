<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

// safety check
if (defined('TABLE_COUNTER_HISTORY')) {
    $stats_enabled = true;
} else {
    return; // counter module not installed
}

$maxRows = 30; // show last 30 days

// prepare data arrays
$dates = [];
$sessions = [];
$hits = [];

// get history
$visits_query = "SELECT startdate, counter, session_counter
                 FROM " . TABLE_COUNTER_HISTORY . "
                 ORDER BY startdate DESC
                 LIMIT " . (int)$maxRows;
$visits = $db->Execute($visits_query);

// process data (note: SQL returns DESC, we need ASC for the chart, so we fetch then reverse)
$temp_data = [];
while (!$visits->EOF) {
    $raw_date = $visits->fields['startdate'];
    // convert YYYYMMDD to "M j" (e.g., Jan 15)
    $formatted_date = date('M j', mktime(0, 0, 0, substr($raw_date, 4, 2), substr($raw_date, 6, 2), substr($raw_date, 0, 4)));

    $temp_data[] = [
        'label' => $formatted_date,
        'sessions' => (int)$visits->fields['session_counter'],
        'hits' => (int)$visits->fields['counter']
    ];
    $visits->MoveNext();
}

// reverse array to show oldest -> newest
$temp_data = array_reverse($temp_data);

// separate into arrays for JS
foreach ($temp_data as $row) {
    $dates[] = $row['label'];
    $sessions[] = $row['sessions'];
    $hits[] = $row['hits'];
}

$js_dates = json_encode($dates);
$js_sessions = json_encode($sessions);
$js_hits = json_encode($hits);
?>

    <div class="col-md-6 col-sm-12">
        <div class="panel widget-wrapper">
            <div class="panel-heading">
                <i class="fa fa-users"></i> <?php echo BOX_TRAFFIC_HEADING; ?> <small class="text-muted"><?php echo sprintf(BOX_TRAFFIC_SUBHEADING, $maxRows); ?></small>
            </div>
            <div class="panel-body">
                <?php if (count($dates) > 0) { ?>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="trafficChart"></canvas>
                    </div>
                <?php } else { ?>
                    <div class="text-center text-muted" style="padding: 40px;">
                        <i class="fa fa-bar-chart fa-3x"></i><br><br>
                        <?php echo BOX_TRAFFIC_NO_DATA; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

<?php if (count($dates) > 0) { ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctxTraffic = document.getElementById('trafficChart').getContext('2d');

            var trafficChart = new Chart(ctxTraffic, {
                type: 'bar',
                data: {
                    labels: <?php echo $js_dates; ?>,
                    datasets: [
                        {
                            label: '<?php echo BOX_TRAFFIC_SESSIONS; ?>',
                            data: <?php echo $js_sessions; ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: '<?php echo BOX_TRAFFIC_HITS; ?>',
                            type: 'line',
                            data: <?php echo $js_hits; ?>,
                            borderColor: 'rgba(255, 159, 64, 1)',
                            backgroundColor: 'rgba(255, 159, 64, 0.1)',
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.3,
                            fill: false,
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
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 10 }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: '<?php echo BOX_TRAFFIC_SESSIONS; ?>' },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: '<?php echo BOX_TRAFFIC_HITS; ?>' },
                            grid: { display: false },
                            suggestedMin: 0
                        }
                    }
                }
            });
        });
    </script>
<?php } ?>
