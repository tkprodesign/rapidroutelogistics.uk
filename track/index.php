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
$progressStatusKey = 'in_transit';
$status = "In Transit";
$progress_percent = 65;
$estimated_delivery_text = "—";
$estimated_delivery_hint = "By End of Day";
$history = [];
$tracking_found = false;
$tracking_id_missing = ($tracking_id_raw === '');
$tracking_lookup_attempted = !$tracking_id_missing;
$shipmentOrigin = '';
$shipmentDestination = '';
$shipmentWeight = null;
$currentTransportMode = '';
$originEvent = null;
$destinationEvent = null;
$lastUpdatedText = '—';

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

    if (isset($aliases[$statusText])) return $aliases[$statusText];

    if (strpos($statusText, 'out_for_delivery') !== false) return 'out_for_delivery';
    if (strpos($statusText, 'deliver') !== false && strpos($statusText, 'out') === false) return 'delivered';
    if (strpos($statusText, 'exception') !== false || strpos($statusText, 'delay') !== false || strpos($statusText, 'hold') !== false) return 'failed';
    if (strpos($statusText, 'transit') !== false || strpos($statusText, 'arriv') !== false || strpos($statusText, 'process') !== false) return 'in_transit';
    if (strpos($statusText, 'ship') !== false || strpos($statusText, 'pickup') !== false || strpos($statusText, 'depart') !== false) return 'shipped';
    if (strpos($statusText, 'label') !== false || strpos($statusText, 'created') !== false) return 'pending';

    return null;
}

// Ensure new event columns exist (safe ALTER TABLE — silently ignores if already present)
if (!$tracking_id_missing && isset($conn) && $conn instanceof mysqli) {
    $newCols = [
        "ALTER TABLE shipment_location_events ADD COLUMN transport_mode VARCHAR(40) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN event_type VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN location_type VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN vessel_name VARCHAR(190) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN voyage_number VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN port_of_departure VARCHAR(190) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN port_of_arrival VARCHAR(190) NULL DEFAULT NULL",
    ];
    foreach ($newCols as $_sql) {
        try { $conn->query($_sql); } catch (Throwable $_e) {}
    }
}

if (!$tracking_id_missing && isset($conn) && $conn instanceof mysqli) {

    // Check which optional shipment columns exist
    $hasCompletionColumn = false;
    $r = $conn->query("SHOW COLUMNS FROM shipments LIKE 'completion_percentage'");
    if ($r && $r->num_rows > 0) $hasCompletionColumn = true;

    $hasOriginCol = false;
    $r = $conn->query("SHOW COLUMNS FROM shipments LIKE 'origin'");
    if ($r && $r->num_rows > 0) $hasOriginCol = true;

    $hasDestCol = false;
    $r = $conn->query("SHOW COLUMNS FROM shipments LIKE 'destination'");
    if ($r && $r->num_rows > 0) $hasDestCol = true;

    $hasWeightCol = false;
    $r = $conn->query("SHOW COLUMNS FROM shipments LIKE 'weight'");
    if ($r && $r->num_rows > 0) $hasWeightCol = true;

    $selectParts = ['status', 'estimated_delivery_time'];
    if ($hasCompletionColumn) $selectParts[] = 'completion_percentage';
    if ($hasOriginCol)        $selectParts[] = 'origin';
    if ($hasDestCol)          $selectParts[] = 'destination';
    if ($hasWeightCol)        $selectParts[] = 'weight';

    $shipmentSql = "SELECT " . implode(', ', $selectParts) . " FROM shipments WHERE tracking_number = ? LIMIT 1";
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
                'pending'          => 'Label Created',
                'incoming'         => 'Shipped',
                'outgoing'         => 'Shipped',
                'picked_up'        => 'Picked Up',
                'in_store'         => 'In Transit',
                'shipped'          => 'In Transit',
                'in_transit'       => 'In Transit',
                'out_for_delivery' => 'Out for Delivery',
                'delivered'        => 'Delivered',
                'failed'           => 'Exception',
                'cancelled'        => 'Cancelled',
            ];

            $statusKey = rrl_normalize_tracking_status($shipmentRow['status'] ?? 'in_transit') ?? 'in_transit';
            $status = $statusMap[$statusKey] ?? 'In Transit';

            // Percentages represent position within the bar that runs between
            // node centers (node 1 = 0%, node 5 = 100%, nodes evenly spaced).
            $progressMap = [
                'pending'          => 0,
                'incoming'         => 25,
                'outgoing'         => 25,
                'picked_up'        => 25,
                'in_store'         => 50,
                'shipped'          => 50,
                'in_transit'       => 50,
                'out_for_delivery' => 75,
                'delivered'        => 100,
                'failed'           => 50,
                'cancelled'        => 0,
            ];

            // Treat completion_percentage = 0 (default DB value) the same as NULL —
            // only use it when the admin has explicitly set a value > 0.
            $rawCompletion = $shipmentRow['completion_percentage'] ?? null;
            $storedProgress = ($rawCompletion !== null && (int)$rawCompletion > 0 && (int)$rawCompletion <= 100)
                ? (int)$rawCompletion
                : null;
            $progress_percent = ($storedProgress !== null) ? $storedProgress : ($progressMap[$statusKey] ?? 50);

            $etaEpoch = (int)($shipmentRow['estimated_delivery_time'] ?? 0);
            if ($etaEpoch > 0) {
                if ($etaEpoch > 1000000000000) $etaEpoch = (int)($etaEpoch / 1000);
                $estimated_delivery_text = date("l, F j, Y", $etaEpoch);
            }

            $shipmentOrigin      = trim((string)($shipmentRow['origin'] ?? ''));
            $shipmentDestination = trim((string)($shipmentRow['destination'] ?? ''));
            $shipmentWeight      = (isset($shipmentRow['weight']) && $shipmentRow['weight'] !== null) ? (float)$shipmentRow['weight'] : null;
        }
    }

    // Fetch events — includes new columns (safe since we ensured them above)
    $eventsSql = "
        SELECT id, event_time_epoch, status_text, city, state_region, country_code,
               location_name, event_severity, issue_note, negative_event_paid,
               is_origin, is_destination,
               transport_mode, event_type, location_type
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
                if ($epoch > 1000000000000) $epoch = (int)($epoch / 1000);

                $pieces = [];
                if (!empty($row['location_name'])) $pieces[] = (string)$row['location_name'];
                if (!empty($row['city']))           $pieces[] = (string)$row['city'];
                if (!empty($row['state_region']))   $pieces[] = (string)$row['state_region'];
                if (!empty($row['country_code']))   $pieces[] = strtoupper((string)$row['country_code']);
                $locationText = implode(', ', $pieces);

                $severity = strtolower(trim((string)($row['event_severity'] ?? 'neutral')));
                $isNegative = ($severity === 'negative');
                $isNegativePaid = (int)($row['negative_event_paid'] ?? 0) === 1;
                $isOriginFlag = (int)($row['is_origin'] ?? 0) === 1;
                $isDestinationFlag = (int)($row['is_destination'] ?? 0) === 1;
                $transportMode = strtolower(trim((string)($row['transport_mode'] ?? '')));

                $history[] = [
                    'event_id'       => (int)($row['id'] ?? 0),
                    'time'           => $epoch > 0 ? date("h:i A", $epoch) : '--:--',
                    'date'           => $epoch > 0 ? date("M j, Y", $epoch) : '-',
                    'location'       => $locationText !== '' ? $locationText : '-',
                    'activity'       => (string)($row['status_text'] ?? 'Update'),
                    'is_negative'    => ($isNegative && !$isNegativePaid),
                    'is_negative_paid' => $isNegativePaid,
                    'issue_note'     => (string)($row['issue_note'] ?? ''),
                    'is_origin'      => $isOriginFlag,
                    'is_destination' => $isDestinationFlag,
                    'transport_mode' => $transportMode,
                    'event_type'     => (string)($row['event_type'] ?? ''),
                    'location_type'  => (string)($row['location_type'] ?? ''),
                ];

                if ($isOriginFlag && $originEvent === null) {
                    $originEvent = end($history);
                }
                if ($isDestinationFlag && $destinationEvent === null) {
                    $destinationEvent = end($history);
                }
            }
        }
        $stmtEvents->close();
    }

    // Post-process: find origin/destination events from the final history array
    $originEvent = null;
    $destinationEvent = null;
    foreach ($history as $evt) {
        if ($evt['is_origin'] && $originEvent === null)      $originEvent = $evt;
        if ($evt['is_destination'] && $destinationEvent === null) $destinationEvent = $evt;
        if (!empty($evt['transport_mode']) && $currentTransportMode === '') {
            $currentTransportMode = $evt['transport_mode'];
        }
    }
}

// The shipment's own status field (set in the control panel) is the sole
// authoritative source for the overall status badge and progress bar.
// Event severity marks individual timeline entries only — it must never
// override the shipment-level status key.
$progressStatusKey = $statusKey;

if ($tracking_found && !empty($history)) {
    $lastUpdatedText = $history[0]['date'] . ' · ' . $history[0]['time'];
}

if ($tracking_id_missing) {
    $statusKey = $progressStatusKey = 'pending';
    $status = 'Enter Tracking Number';
    $progress_percent = 0;
    $estimated_delivery_text = '—';
    $estimated_delivery_hint = 'Provide a tracking number to view shipment updates.';
} elseif (!$tracking_found) {
    $statusKey = $progressStatusKey = 'not_found';
    $status = 'Not Found';
    $progress_percent = 0;
    $estimated_delivery_text = '—';
    $estimated_delivery_hint = 'No shipment matched that tracking number.';
}

// Transport mode display
$transportModeIcons = [
    'road'  => 'local_shipping',
    'air'   => 'flight',
    'sea'   => 'directions_boat',
    'rail'  => 'train',
    'mixed' => 'multiple_stop',
];
$transportIcon  = $transportModeIcons[$currentTransportMode] ?? 'local_shipping';
$transportLabel = $currentTransportMode ? ucwords(str_replace('_', ' ', $currentTransportMode)) . ' Freight' : 'Standard';

// Status badge CSS class
$statusBadgeClass = [
    'pending'          => 'badge-pending',
    'picked_up'        => 'badge-transit',
    'shipped'          => 'badge-transit',
    'in_store'         => 'badge-transit',
    'in_transit'       => 'badge-transit',
    'out_for_delivery' => 'badge-ofd',
    'delivered'        => 'badge-delivered',
    'failed'           => 'badge-exception',
    'cancelled'        => 'badge-cancelled',
    'not_found'        => 'badge-pending',
][$statusKey] ?? 'badge-transit';

// 5-step progress nodes
$progress_nodes = [
    ['label' => 'Shipment Created', 'icon' => 'package_2',       'state' => 'pending'],
    ['label' => 'Picked Up',        'icon' => 'inventory',        'state' => 'pending'],
    ['label' => 'In Transit',       'icon' => 'local_shipping',   'state' => 'pending'],
    ['label' => 'At Destination',   'icon' => 'location_on',      'state' => 'pending'],
    ['label' => 'Delivered',        'icon' => 'check_circle',     'state' => 'pending'],
];

switch ($progressStatusKey) {
    case 'pending':
        $progress_percent = max(0, min(12, $progress_percent));
        $progress_nodes[0]['state'] = 'active';
        $estimated_delivery_hint = 'Shipment information received';
        break;

    case 'incoming':
    case 'outgoing':
    case 'picked_up':
        $progress_percent = max(20, min(30, $progress_percent));
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'active';
        $estimated_delivery_hint = 'Picked up and moving';
        break;

    case 'in_store':
    case 'shipped':
    case 'in_transit':
        $progress_percent = max(44, min(56, $progress_percent));
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['state'] = 'active';
        $estimated_delivery_hint = 'By End of Day';
        break;

    case 'out_for_delivery':
        $progress_percent = max(70, min(80, $progress_percent));
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['state'] = 'done';
        $progress_nodes[3]['label'] = 'Out for Delivery';
        $progress_nodes[3]['icon']  = 'local_shipping';
        $progress_nodes[3]['state'] = 'active';
        $estimated_delivery_hint = 'Expected today';
        break;

    case 'delivered':
        $progress_percent = 100;
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['state'] = 'done';
        $progress_nodes[3]['state'] = 'done';
        $progress_nodes[4]['state'] = 'active';
        $estimated_delivery_hint = 'Successfully delivered';
        break;

    case 'failed':
        $progress_percent = max(44, min(56, $progress_percent));
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['label'] = 'Exception';
        $progress_nodes[2]['icon']  = 'warning';
        $progress_nodes[2]['state'] = 'active';
        $estimated_delivery_hint = 'Action required';
        break;

    case 'cancelled':
        $progress_percent = 0;
        $progress_nodes[0]['state'] = 'active';
        $estimated_delivery_hint = 'Shipment cancelled';
        break;

    case 'not_found':
        $progress_percent = 0;
        $estimated_delivery_hint = 'No shipment matched that tracking number.';
        break;

    default:
        $progress_percent = max(44, min(56, $progress_percent));
        $progress_nodes[0]['state'] = 'done';
        $progress_nodes[1]['state'] = 'done';
        $progress_nodes[2]['state'] = 'active';
        $estimated_delivery_hint = 'By End of Day';
        break;
}

// Route text: prefer origin/destination event location, fall back to shipment columns
$originLocationText      = $originEvent      ? $originEvent['location']      : $shipmentOrigin;
$destinationLocationText = $destinationEvent ? $destinationEvent['location'] : $shipmentDestination;
$currentLocationText     = !empty($history)  ? $history[0]['location']       : '';

// Avoid showing current = origin or destination if they're the same text
$showCurrentInRoute = ($currentLocationText !== '' && $currentLocationText !== $originLocationText && $currentLocationText !== $destinationLocationText && $currentLocationText !== '-');

// Collect unique intermediate waypoints visited between origin and current (chronological order).
// Only include physical stops — exclude in-motion/transit location types (Vessel, Aircraft).
$intermediateWaypoints = [];
$_routeSeen = ['-' => true, '' => true];
foreach ([$originLocationText, $currentLocationText, $destinationLocationText] as $_sl) {
    if ((string)$_sl !== '' && $_sl !== '-') $_routeSeen[$_sl] = true;
}
$_transitTypes = ['Vessel', 'Aircraft'];
foreach (array_reverse($history) as $_evt) {
    $_loc  = $_evt['location'];
    $_type = (string)($_evt['location_type'] ?? '');
    if (isset($_routeSeen[$_loc])) continue;
    if (in_array($_type, $_transitTypes, true)) continue;
    $_routeSeen[$_loc] = true;
    $intermediateWaypoints[] = $_loc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment | Rapid Route Logistics</title>
    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tracking.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<?php include("../common-sections/header.html"); ?>
    <main class="track-container">

        <!-- Search header -->
        <div class="track-header">
            <h1>Track Your Shipment</h1>
            <form class="search-bar" method="get" action="/track/">
                <input type="text" name="id" placeholder="Enter Tracking Number" value="<?= $tracking_id ?>" required>
                <button class="btn-track" type="submit">Track</button>
            </form>
        </div>

        <?php if ($tracking_found && $tracking_id !== ''): ?>
        <!-- Status Hero -->
        <div class="track-status-hero">
            <div class="track-status-hero-inner">
                <div class="track-status-hero-left">
                    <span class="track-status-badge <?= $statusBadgeClass ?>">
                        <span class="badge-dot"></span>
                        <?= htmlspecialchars($status) ?>
                    </span>
                    <p class="track-tn">Tracking # <strong><?= $tracking_id ?></strong></p>
                    <?php if ($originLocationText || $destinationLocationText): ?>
                    <p class="track-hero-route">
                        <span class="material-symbols-outlined" aria-hidden="true">trip_origin</span>
                        <?= htmlspecialchars($originLocationText ?: '—') ?>
                        <span class="route-arrow-icon material-symbols-outlined" aria-hidden="true">east</span>
                        <?= htmlspecialchars($destinationLocationText ?: '—') ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($lastUpdatedText !== '—'): ?>
                    <p class="track-hero-updated">
                        <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
                        Last updated: <?= htmlspecialchars($lastUpdatedText) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="track-status-hero-right">
                    <span class="material-symbols-outlined track-transport-icon" aria-hidden="true"><?= htmlspecialchars($transportIcon) ?></span>
                    <span class="track-transport-label"><?= htmlspecialchars($transportLabel) ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Info Strip -->
        <div class="track-quick-strip">
            <div class="qstrip-item">
                <span class="qstrip-icon material-symbols-outlined" aria-hidden="true">radio_button_checked</span>
                <div>
                    <span class="qstrip-label">Status</span>
                    <span class="qstrip-value"><?= htmlspecialchars($status) ?></span>
                </div>
            </div>
            <?php if ($currentLocationText && $currentLocationText !== '-'): ?>
            <div class="qstrip-item">
                <span class="qstrip-icon material-symbols-outlined" aria-hidden="true">location_on</span>
                <div>
                    <span class="qstrip-label">Current Location</span>
                    <span class="qstrip-value"><?= htmlspecialchars($currentLocationText) ?></span>
                </div>
            </div>
            <?php endif; ?>
            <div class="qstrip-item">
                <span class="qstrip-icon material-symbols-outlined" aria-hidden="true">calendar_today</span>
                <div>
                    <span class="qstrip-label">Estimated Delivery</span>
                    <span class="qstrip-value"><?= htmlspecialchars($estimated_delivery_text) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Grid -->
        <div class="track-grid">

            <!-- Left column: progress + details -->
            <section class="main-card">

                <!-- 5-step progress bar -->
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

                <!-- ETA block -->
                <div class="estimated-delivery">
                    <p>Estimated Delivery</p>
                    <h2><?= htmlspecialchars($estimated_delivery_text) ?></h2>
                    <span><?= htmlspecialchars($estimated_delivery_hint) ?></span>
                </div>

                <?php if ($tracking_found): ?>

                <!-- Shipment detail pills -->
                <?php $hasAnyDetail = ($currentTransportMode || $shipmentWeight !== null || $lastUpdatedText !== '—'); ?>
                <?php if ($hasAnyDetail): ?>
                <div class="track-details-grid">
                    <?php if ($currentTransportMode): ?>
                    <div class="track-detail-item">
                        <small>Transport Mode</small>
                        <strong>
                            <span class="material-symbols-outlined" aria-hidden="true"><?= htmlspecialchars($transportIcon) ?></span>
                            <?= htmlspecialchars($transportLabel) ?>
                        </strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($shipmentWeight !== null): ?>
                    <div class="track-detail-item">
                        <small>Shipment Weight</small>
                        <strong><?= number_format($shipmentWeight, 2) ?> kg</strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($lastUpdatedText !== '—'): ?>
                    <div class="track-detail-item">
                        <small>Last Updated</small>
                        <strong><?= htmlspecialchars($lastUpdatedText) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Current location card -->
                <?php if (!empty($history)): ?>
                <div class="track-current-location">
                    <div class="current-loc-header">
                        <span class="material-symbols-outlined" aria-hidden="true">location_on</span>
                        <h4>Current Location</h4>
                    </div>
                    <p class="current-loc-name"><?= htmlspecialchars($history[0]['location'] !== '-' ? $history[0]['location'] : '—') ?></p>
                    <p class="current-loc-status"><?= htmlspecialchars($history[0]['activity']) ?></p>
                    <?php if ($lastUpdatedText !== '—'): ?>
                    <p class="current-loc-time"><?= htmlspecialchars($lastUpdatedText) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Route display -->
                <?php if ($originLocationText || $destinationLocationText): ?>
                <div class="track-route-display">
                    <h4 class="track-route-title">Shipment Route</h4>
                    <div class="route-steps">
                        <?php if ($originLocationText): ?>
                        <div class="route-step route-origin">
                            <div class="route-step-dot"></div>
                            <div class="route-step-body">
                                <span class="route-step-label">Origin</span>
                                <span class="route-step-text"><?= htmlspecialchars($originLocationText) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php foreach ($intermediateWaypoints as $_wp): ?>
                        <div class="route-step route-waypoint">
                            <div class="route-step-dot"></div>
                            <div class="route-step-body">
                                <span class="route-step-label">Checkpoint</span>
                                <span class="route-step-text"><?= htmlspecialchars($_wp) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($showCurrentInRoute): ?>
                        <div class="route-step route-current">
                            <div class="route-step-dot"></div>
                            <div class="route-step-body">
                                <span class="route-step-label">Current</span>
                                <span class="route-step-text"><?= htmlspecialchars($currentLocationText) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($destinationLocationText): ?>
                        <div class="route-step route-dest">
                            <div class="route-step-dot"></div>
                            <div class="route-step-body">
                                <span class="route-step-label">Destination</span>
                                <span class="route-step-text"><?= htmlspecialchars($destinationLocationText) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php endif; /* tracking_found */ ?>
            </section>

            <!-- Right column: event history -->
            <section class="history-card">
                <h3>Detailed History</h3>
                <div class="timeline">
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $event): ?>
                            <div class="timeline-item <?= !empty($event['is_negative']) ? 'is-negative' : '' ?>">
                                <div class="time-col">
                                    <strong><?= htmlspecialchars((string)$event['time']) ?></strong>
                                    <span><?= htmlspecialchars((string)$event['date']) ?></span>
                                </div>
                                <div class="activity-col">
                                    <strong><?= htmlspecialchars((string)$event['activity']) ?></strong>
                                    <span><?= htmlspecialchars((string)$event['location']) ?></span>
                                    <?php if (!empty($event['event_type'])): ?>
                                        <span class="event-type-tag"><?= htmlspecialchars((string)$event['event_type']) ?></span>
                                    <?php endif; ?>
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
                    <?php else: ?>
                        <div class="timeline-item">
                            <div class="activity-col">
                                <strong>No events yet.</strong>
                                <span>Tracking updates will appear here once the shipment is in motion.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div><!-- /.track-grid -->
    </main>
<?php include("../common-sections/footer.html"); ?>
</body>
</html>
