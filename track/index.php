<?php
require_once __DIR__ . '/../common-sections/globals.php';

$trackRequestPath = (string)($_SERVER['REQUEST_URI'] ?? '/track/');
$trackSignedIn = !empty($_COOKIE['user_email']) || !empty($_COOKIE['user_Email']);
if (!$trackSignedIn) {
    header('Location: /login/?required_login=1&redirect=' . urlencode($trackRequestPath));
    exit();
}

$tracking_id_raw = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
$tracking_id = htmlspecialchars($tracking_id_raw);
$statusKey = 'in_transit';
$status = "In Transit";
$progress_percent = 65;
$estimated_delivery_text = "Thursday, March 5, 2026";
$estimated_delivery_hint = "By End of Day";
$history = [];
$tracking_found = false;
$tracking_id_missing = ($tracking_id_raw === '');
$tracking_lookup_attempted = !$tracking_id_missing;

function rrl_normalize_tracking_status($value) {
    $statusText = strtolower(trim((string)$value));
    $statusText = preg_replace('/[^a-z0-9]+/', '_', $statusText);
    $statusText = trim((string)$statusText, '_');

    $aliases = [
        'created' => 'pending',
        'label_created' => 'pending',
        'order_created' => 'pending',
        'shipment_created' => 'pending',
        'processing' => 'pending',
        'ready_for_pickup' => 'pending',
        'picked_up' => 'picked_up',
        'pickup' => 'picked_up',
        'origin_scan' => 'picked_up',
        'origin' => 'picked_up',
        'shipped' => 'shipped',
        'departed' => 'shipped',
        'departed_facility' => 'shipped',
        'arrived' => 'in_transit',
        'arrived_at_facility' => 'in_transit',
        'processed' => 'in_transit',
        'processed_at_facility' => 'in_transit',
        'checkpoint' => 'in_transit',
        'in_store' => 'in_store',
        'in_transit' => 'in_transit',
        'transit' => 'in_transit',
        'on_the_way' => 'in_transit',
        'out_for_delivery' => 'out_for_delivery',
        'ofd' => 'out_for_delivery',
        'delivery_attempt' => 'out_for_delivery',
        'delivered' => 'delivered',
        'destination' => 'delivered',
        'completed' => 'delivered',
        'complete' => 'delivered',
        'exception' => 'failed',
        'delayed' => 'failed',
        'failed' => 'failed',
        'held' => 'failed',
        'cancelled' => 'cancelled',
        'canceled' => 'cancelled'
    ];

    if (isset($aliases[$statusText])) {
        return $aliases[$statusText];
    }

    if (strpos($statusText, 'out_for_delivery') !== false) return 'out_for_delivery';
    if (strpos($statusText, 'deliver') !== false && strpos($statusText, 'out') === false) return 'delivered';
    if (strpos($statusText, 'exception') !== false || strpos($statusText, 'delay') !== false || strpos($statusText, 'hold') !== false) return 'failed';
    if (strpos($statusText, 'transit') !== false || strpos($statusText, 'arriv') !== false || strpos($statusText, 'process') !== false) return 'in_transit';
    if (strpos($statusText, 'ship') !== false || strpos($statusText, 'pickup') !== false || strpos($statusText, 'depart') !== false) return 'shipped';
    if (strpos($statusText, 'label') !== false || strpos($statusText, 'created') !== false) return 'pending';

    return null;
}

if (!$tracking_id_missing && isset($conn) && $conn instanceof mysqli) {
    $hasCompletionColumn = false;
    $completionColumnCheck = $conn->query("SHOW COLUMNS FROM shipments LIKE 'completion_percentage'");
    if ($completionColumnCheck && $completionColumnCheck->num_rows > 0) {
        $hasCompletionColumn = true;
    }
    $shipmentSelect = $hasCompletionColumn ? 'status, completion_percentage, estimated_delivery_time' : 'status, estimated_delivery_time';
    $shipmentSql = "SELECT {$shipmentSelect} FROM shipments WHERE tracking_number = ? LIMIT 1";
    $stmtShipment = $conn->prepare($shipmentSql);
    if ($stmtShipment) {
        $stmtShipment->bind_param("s", $tracking_id_raw);
        $stmtShipment->execute();
        $shipmentRes = $stmtShipment->get_result();
        $shipmentRow = $shipmentRes ? $shipmentRes->fetch_assoc() : null;
        $stmtShipment->close();

        if ($shipmentRow) {
            $tracking_found = true;
            $statusMap = [
                'pending' => 'Label Created',
                'incoming' => 'Shipped',
                'outgoing' => 'Shipped',
                'picked_up' => 'Shipped',
                'in_store' => 'In Transit',
                'shipped' => 'In Transit',
                'in_transit' => 'In Transit',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'failed' => 'Exception',
                'cancelled' => 'Cancelled'
            ];
            $statusKey = rrl_normalize_tracking_status($shipmentRow['status'] ?? 'in_transit') ?? 'in_transit';
            $status = $statusMap[$statusKey] ?? 'In Transit';

            $progressMap = [
                'pending' => 10,
                'incoming' => 25,
                'outgoing' => 25,
                'picked_up' => 30,
                'in_store' => 45,
                'shipped' => 55,
                'in_transit' => 65,
                'out_for_delivery' => 85,
                'delivered' => 100,
                'failed' => 65,
                'cancelled' => 10
            ];
            $storedProgress = isset($shipmentRow['completion_percentage']) ? (int)$shipmentRow['completion_percentage'] : null;
            $progress_percent = ($storedProgress !== null && $storedProgress >= 0 && $storedProgress <= 100) ? $storedProgress : ($progressMap[$statusKey] ?? 65);

            $etaEpoch = (int)($shipmentRow['estimated_delivery_time'] ?? 0);
            if ($etaEpoch > 0) {
                if ($etaEpoch > 1000000000000) {
                    $etaEpoch = (int)($etaEpoch / 1000);
                }
                $estimated_delivery_text = date("l, F j, Y", $etaEpoch);
            }
        }
    }

    $eventsSql = "
        SELECT id, event_time_epoch, status_text, city, state_region, country_code, location_name, event_severity, issue_note, negative_event_paid
        FROM shipment_location_events
        WHERE tracking_number = ?
        ORDER BY event_time_epoch DESC, id DESC
        LIMIT 25
    ";
    $stmtEvents = $conn->prepare($eventsSql);
    if ($stmtEvents) {
        $stmtEvents->bind_param("s", $tracking_id_raw);
        $stmtEvents->execute();
        $eventsRes = $stmtEvents->get_result();
        if ($eventsRes) {
            while ($row = $eventsRes->fetch_assoc()) {
                $epoch = (int)($row['event_time_epoch'] ?? 0);
                if ($epoch > 1000000000000) {
                    $epoch = (int)($epoch / 1000);
                }

                $pieces = [];
                if (!empty($row['location_name'])) $pieces[] = (string)$row['location_name'];
                if (!empty($row['city'])) $pieces[] = (string)$row['city'];
                if (!empty($row['state_region'])) $pieces[] = (string)$row['state_region'];
                if (!empty($row['country_code'])) $pieces[] = strtoupper((string)$row['country_code']);
                $locationText = implode(', ', $pieces);

                $severity = strtolower(trim((string)($row['event_severity'] ?? 'neutral')));
                $isNegative = ($severity === 'negative');
                $isNegativePaid = (int)($row['negative_event_paid'] ?? 0) === 1;

                $history[] = [
                    "event_id" => (int)($row['id'] ?? 0),
                    "time" => $epoch > 0 ? date("h:i A", $epoch) : "--:--",
                    "date" => $epoch > 0 ? date("M j, Y", $epoch) : "-",
                    "location" => $locationText !== '' ? $locationText : "-",
                    "activity" => (string)($row['status_text'] ?? 'Update'),
                    "is_negative" => ($isNegative && !$isNegativePaid),
                    "is_negative_paid" => $isNegativePaid,
                    "issue_note" => (string)($row['issue_note'] ?? '')
                ];
            }
        }
        $stmtEvents->close();
    }
}

if ($tracking_found && !empty($history)) {
    $latestEvent = $history[0];
    $eventStatusKey = rrl_normalize_tracking_status($latestEvent['activity'] ?? null);
    if (!$eventStatusKey && !empty($latestEvent['is_negative'])) {
        $eventStatusKey = 'failed';
    }
    if ($eventStatusKey) {
        $statusKey = $eventStatusKey;
        $statusMap = [
            'pending' => 'Label Created',
            'incoming' => 'Shipped',
            'outgoing' => 'Shipped',
            'picked_up' => 'Shipped',
            'in_store' => 'In Transit',
            'shipped' => 'In Transit',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed' => 'Exception',
            'cancelled' => 'Cancelled'
        ];
        $status = $statusMap[$statusKey] ?? $status;
    }
}

if ($tracking_id_missing) {
    $statusKey = 'pending';
    $status = 'Enter Tracking Number';
    $progress_percent = 0;
    $estimated_delivery_text = '-';
    $estimated_delivery_hint = 'Provide a tracking number to view shipment updates.';
} elseif (!$tracking_found) {
    $statusKey = 'not_found';
    $status = 'Not Found';
    $progress_percent = 0;
    $estimated_delivery_text = '-';
    $estimated_delivery_hint = 'No shipment matched that tracking number.';
}

$progress_nodes = [
    ['label' => 'Label Created', 'icon' => 'package_2', 'state' => 'pending'],
    ['label' => 'In Transit', 'icon' => 'local_shipping', 'state' => 'pending'],
    ['label' => 'Delivered', 'icon' => 'inventory_2', 'state' => 'pending'],
];

switch ($statusKey) {
    case 'pending':
        $progress_percent = max(0, min(12, $progress_percent));
        $progress_nodes[0]['state'] = 'active';
        $estimated_delivery_hint = 'Shipment information received';
        break;

    case 'incoming':
    case 'outgoing':
    case 'picked_up':
        $progress_percent = max(18, min(35, $progress_percent));
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'active';
        $estimated_delivery_hint = 'Picked up and moving';
        break;

    case 'in_store':
    case 'shipped':
    case 'in_transit':
        $progress_percent = max(45, min(74, $progress_percent));
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'active';
        $estimated_delivery_hint = 'By End of Day';
        break;

    case 'out_for_delivery':
        $progress_percent = max(82, min(96, $progress_percent));
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['label'] = 'Out for Delivery';
        $progress_nodes[1]['state'] = 'active';
        $estimated_delivery_hint = 'Expected today';
        break;

    case 'delivered':
        $progress_percent = 100;
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['state'] = 'active';
        $estimated_delivery_hint = 'Delivered';
        break;

    case 'failed':
        $progress_percent = max(45, min(78, $progress_percent));
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['label'] = 'Exception';
        $progress_nodes[1]['state'] = 'active';
        $estimated_delivery_hint = 'Delivery update required';
        break;

    case 'cancelled':
        $progress_percent = 0;
        $progress_nodes[0]['state'] = 'active';
        $estimated_delivery_hint = 'Shipment cancelled';
        break;

    case 'not_found':
        $progress_percent = 0;
        $progress_nodes[0]['state'] = 'pending';
        $estimated_delivery_hint = 'No shipment matched that tracking number.';
        break;

    default:
        $progress_percent = max(45, min(74, $progress_percent));
        $progress_nodes[0]['label'] = 'Shipped';
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'active';
        $estimated_delivery_hint = 'By End of Day';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment | TK Pro Design</title>
    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tracking.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<?php include("../common-sections/header.html"); ?>
    <main class="track-container">
        <div class="track-header">
            <h1>Tracking</h1>
            <form class="search-bar" method="get" action="/track/">
                <input type="text" name="id" placeholder="Tracking Number" value="<?= $tracking_id ?>" required>
                <button class="btn-track" type="submit">Track</button>
            </form>
        </div>

        <div class="track-grid">
            <section class="main-card">
                <div class="status-header">
                    <div class="id-group">
                        <span>Tracking Number</span>
                        <strong><?= $tracking_id !== '' ? $tracking_id : 'Not provided' ?></strong>
                    </div>
                    <div class="status-badge <?= str_replace(' ', '-', strtolower($status)) ?>">
                        <?= $status ?>
                    </div>
                </div>

                <div class="tracking-visual" aria-label="Shipment progress: <?= (int)$progress_percent ?> percent complete">
                    <div class="progress-line" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$progress_percent ?>">
                        <div class="fill" style="width: <?= (int)$progress_percent ?>%;"></div>
                    </div>
                    <div class="nodes">
                        <?php foreach ($progress_nodes as $node): ?>
                            <div class="node <?= htmlspecialchars($node['state']) ?>">
                                <i class="material-symbols-outlined"><?= htmlspecialchars($node['icon']) ?></i>
                                <span><?= htmlspecialchars($node['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="estimated-delivery">
                    <p>Estimated Delivery</p>
                    <h2><?= htmlspecialchars($estimated_delivery_text) ?></h2>
                    <span><?= htmlspecialchars($estimated_delivery_hint) ?></span>
                </div>
            </section>

            <section class="history-card">
                <h3>Detailed History</h3>
                <div class="timeline">
                    <?php if (!empty($history)): ?>
                        <?php foreach($history as $event): ?>
                            <div class="timeline-item <?= !empty($event['is_negative']) ? 'is-negative' : '' ?>">
                                <div class="time-col">
                                    <strong><?= htmlspecialchars((string)$event['time']) ?></strong>
                                    <span><?= htmlspecialchars((string)$event['date']) ?></span>
                                </div>
                                <div class="activity-col">
                                    <strong><?= htmlspecialchars((string)$event['activity']) ?></strong>
                                    <span><?= htmlspecialchars((string)$event['location']) ?></span>
                                    <?php if (!empty($event['is_negative'])): ?>
                                        <a
                                            class="urgent-cta"
                                            href="/track/exception/?tn=<?= urlencode($tracking_id_raw) ?>&eid=<?= (int)($event['event_id'] ?? 0) ?>"
                                        >
                                            <span class="material-symbols-outlined" aria-hidden="true">warning</span>
                                            Click for more details
                                        </a>
                                        <?php if (!empty($event['issue_note'])): ?>
                                            <span class="issue-note"><?= htmlspecialchars((string)$event['issue_note']) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($tracking_id_missing): ?>
                        <div class="timeline-item">
                            <div class="activity-col">
                                <strong>No tracking number provided.</strong>
                                <span>Enter a tracking number above and tap Track.</span>
                            </div>
                        </div>
                    <?php elseif ($tracking_lookup_attempted && !$tracking_found): ?>
                        <div class="timeline-item">
                            <div class="activity-col">
                                <strong>Tracking number not found.</strong>
                                <span>Please verify the number and try again.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
<?php include("../common-sections/footer.html"); ?>
</body>
</html>
