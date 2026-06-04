<?php include("../app.php");

function cp_detail_epoch_seconds($value): ?int {
    if ($value === null || $value === '') {
        return null;
    }
    if (is_numeric((string)$value)) {
        $epoch = (int)$value;
        if ($epoch > 1000000000000) {
            $epoch = (int)($epoch / 1000);
        }
        return $epoch > 0 ? $epoch : null;
    }
    $parsed = strtotime((string)$value);
    return ($parsed !== false && $parsed > 0) ? (int)$parsed : null;
}

function cp_detail_datetime_input($value): string {
    $epoch = cp_detail_epoch_seconds($value);
    return $epoch ? date('Y-m-d\TH:i', $epoch) : '';
}

function cp_detail_money_value($value): string {
    if ($value === null || $value === '') {
        return '';
    }
    return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
}

$shipmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$shipment = null;
$events = [];

if ($shipmentId > 0) {
    $stmtShipment = $dbconn->prepare(
        "SELECT s.*, u.email AS user_email, u.name AS user_name
         FROM shipments s
         LEFT JOIN users u ON u.id = s.user_id
         WHERE s.id = ?
         LIMIT 1"
    );
    if ($stmtShipment) {
        $stmtShipment->bind_param('i', $shipmentId);
        $stmtShipment->execute();
        $resShipment = $stmtShipment->get_result();
        $shipment = $resShipment ? $resShipment->fetch_assoc() : null;
        $stmtShipment->close();
    }

    $stmtEvents = $dbconn->prepare(
        "SELECT *
         FROM shipment_location_events
         WHERE shipment_id = ?
         ORDER BY event_time_epoch DESC, id DESC"
    );
    if ($stmtEvents) {
        $stmtEvents->bind_param('i', $shipmentId);
        $stmtEvents->execute();
        $resEvents = $stmtEvents->get_result();
        if ($resEvents) {
            while ($row = $resEvents->fetch_assoc()) {
                $events[] = $row;
            }
        }
        $stmtEvents->close();
    }
}

$statusOptions = [
    'pending' => 'Pending — Label Created',
    'picked_up' => 'Picked Up',
    'shipped' => 'Shipped / Departed',
    'in_transit' => 'In Transit',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'failed' => 'Exception / Issue',
    'cancelled' => 'Cancelled',
];
$shipmentTypeOptions = [
    'standard' => 'Standard',
    'express' => 'Express',
    'overnight' => 'Overnight',
];
$locationLabelOptions = ['origin' => 'Origin', 'checkpoint' => 'Checkpoint', 'exception' => 'Exception', 'destination' => 'Destination'];
$severityOptions = ['neutral' => 'Neutral', 'negative' => 'Negative'];
$transportModeOptions = ['' => '— Select Mode —', 'road' => 'Road', 'air' => 'Air', 'sea' => 'Sea', 'rail' => 'Rail', 'mixed' => 'Mixed'];
$eventTypeOptions = ['', 'Shipment Created', 'Picked Up', 'Departed Facility', 'Arrived Facility', 'Customs Clearance', 'Customs Hold', 'Port Arrival', 'Port Departure', 'Vessel Departure', 'Vessel Arrival', 'Warehouse Processing', 'In Transit', 'Out For Delivery', 'Delivered', 'Delayed', 'Exception', 'Payment Required', 'Other'];
$locationTypeOptions = ['', 'Warehouse', 'Distribution Center', 'Airport', 'Seaport', 'Customs Office', 'Checkpoint', 'Vessel', 'Aircraft', 'Customer Address', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Detail | Control Panel</title>
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">
</head>
<body>
    <?php include("../partials/header.php"); ?>

    <div class="header-2">
        <div class="container">
            <h2 class="greeting"><span class="material-symbols-outlined" aria-hidden="true">edit_location</span> Shipment Detail</h2>
            <h1 class="cutomer-name"><?= $shipment ? htmlspecialchars((string)$shipment['tracking_number']) : 'Shipment Not Found' ?></h1>
        </div>
    </div>

    <div class="container content">
        <section class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2><?= $shipment ? 'Edit Shipment #' . (int)$shipment['id'] : 'Shipment Not Found' ?></h2>
                    <p>Fix shipment details, delivery times, progress, addresses, and timeline events after human entry errors.</p>
                </div>
                <div class="cp-card-head-actions">
                    <a class="cp-btn cp-btn-secondary" href="/control-panel/shipments/">All Shipments</a>
                    <a class="cp-btn cp-btn-secondary" href="/control-panel/page/#cp-shipments">Control Panel</a>
                </div>
            </div>

            <?php if (!empty($cp_shipment_detail_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_shipment_detail_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_shipment_detail_notice) ?>
                </p>
            <?php endif; ?>
            <?php if (!$shipment): ?>
                <p class="cp-quote-notice is-error">No shipment record was found for ID <?= (int)$shipmentId ?>.</p>
            <?php else: ?>
                <form method="post" class="cp-location-form">
                    <input type="hidden" name="shipment_id" value="<?= (int)$shipment['id'] ?>">
                    <div class="cp-location-grid">
                        <div>
                            <label for="tracking_number">Tracking Number <span class="cp-required">*</span></label>
                            <input id="tracking_number" type="text" name="tracking_number" value="<?= htmlspecialchars((string)$shipment['tracking_number']) ?>" required>
                        </div>
                        <div>
                            <label for="user_id_display">User ID</label>
                            <input id="user_id_display" type="text" value="<?= (int)($shipment['user_id'] ?? 0) ?>" readonly>
                        </div>
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= (string)$shipment['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="completion_percentage">Completion %</label>
                            <input id="completion_percentage" type="number" min="0" max="100" step="1" name="completion_percentage" value="<?= (int)($shipment['completion_percentage'] ?? 0) ?>" required>
                        </div>
                        <div>
                            <label for="shipment_type">Shipment Type</label>
                            <select id="shipment_type" name="shipment_type">
                                <?php foreach ($shipmentTypeOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= (string)$shipment['shipment_type'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="estimated_delivery_time">Estimated Delivery Time</label>
                            <input id="estimated_delivery_time" type="datetime-local" name="estimated_delivery_time" value="<?= htmlspecialchars(cp_detail_datetime_input($shipment['estimated_delivery_time'] ?? null)) ?>">
                        </div>
                        <div>
                            <label for="delivered_at">Delivered At</label>
                            <input id="delivered_at" type="datetime-local" name="delivered_at" value="<?= htmlspecialchars(cp_detail_datetime_input($shipment['delivered_at'] ?? null)) ?>">
                        </div>
                        <div class="cp-location-grid-wide">
                            <label for="current_location">Current Location</label>
                            <input id="current_location" type="text" name="current_location" value="<?= htmlspecialchars((string)($shipment['current_location'] ?? '')) ?>">
                        </div>

                        <div class="cp-location-grid-span4"><p class="cp-section-label"><span class="material-symbols-outlined" aria-hidden="true">person_pin_circle</span> Sender &amp; Receiver</p></div>
                        <div>
                            <label for="sender_name">Sender Name <span class="cp-required">*</span></label>
                            <input id="sender_name" type="text" name="sender_name" value="<?= htmlspecialchars((string)$shipment['sender_name']) ?>" required>
                        </div>
                        <div>
                            <label for="sender_email">Sender Email</label>
                            <input id="sender_email" type="email" name="sender_email" value="<?= htmlspecialchars((string)($shipment['sender_email'] ?? '')) ?>">
                        </div>
                        <div>
                            <label for="sender_phone">Sender Phone</label>
                            <input id="sender_phone" type="text" name="sender_phone" value="<?= htmlspecialchars((string)($shipment['sender_phone'] ?? '')) ?>">
                        </div>
                        <div>
                            <label for="receiver_name">Receiver Name <span class="cp-required">*</span></label>
                            <input id="receiver_name" type="text" name="receiver_name" value="<?= htmlspecialchars((string)$shipment['receiver_name']) ?>" required>
                        </div>
                        <div>
                            <label for="receiver_email">Receiver Email</label>
                            <input id="receiver_email" type="email" name="receiver_email" value="<?= htmlspecialchars((string)($shipment['receiver_email'] ?? '')) ?>">
                        </div>
                        <div>
                            <label for="receiver_phone">Receiver Phone</label>
                            <input id="receiver_phone" type="text" name="receiver_phone" value="<?= htmlspecialchars((string)($shipment['receiver_phone'] ?? '')) ?>">
                        </div>
                        <div class="cp-location-grid-wide">
                            <label for="origin_address">Origin Address <span class="cp-required">*</span></label>
                            <input id="origin_address" type="text" name="origin_address" value="<?= htmlspecialchars((string)$shipment['origin_address']) ?>" required>
                        </div>
                        <div class="cp-location-grid-wide">
                            <label for="destination_address">Destination Address <span class="cp-required">*</span></label>
                            <input id="destination_address" type="text" name="destination_address" value="<?= htmlspecialchars((string)$shipment['destination_address']) ?>" required>
                        </div>

                        <div class="cp-location-grid-span4"><p class="cp-section-label"><span class="material-symbols-outlined" aria-hidden="true">inventory_2</span> Parcel Metrics</p></div>
                        <div><label for="length">Length</label><input id="length" type="number" step="0.01" min="0" name="length" value="<?= htmlspecialchars(cp_detail_money_value($shipment['length'] ?? null)) ?>"></div>
                        <div><label for="width">Width</label><input id="width" type="number" step="0.01" min="0" name="width" value="<?= htmlspecialchars(cp_detail_money_value($shipment['width'] ?? null)) ?>"></div>
                        <div><label for="height">Height</label><input id="height" type="number" step="0.01" min="0" name="height" value="<?= htmlspecialchars(cp_detail_money_value($shipment['height'] ?? null)) ?>"></div>
                        <div><label for="weight">Weight</label><input id="weight" type="number" step="0.01" min="0" name="weight" value="<?= htmlspecialchars(cp_detail_money_value($shipment['weight'] ?? null)) ?>"></div>
                        <div class="cp-location-grid-span4">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars((string)($shipment['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>
                    <div class="cp-quote-actions">
                        <button class="cp-btn" type="submit" name="update_shipment_detail" value="1">Save Shipment Changes</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <?php if ($shipment): ?>
        <section class="cp-card cp-card-list" id="shipment-events">
            <div class="cp-card-head">
                <div>
                    <h2>Shipment Events</h2>
                    <p>Edit every tracking event attached to this shipment. Mark one event as current, origin, or destination when needed.</p>
                </div>
                <a class="cp-btn" href="/control-panel/page/#cp-add-location-event">Add New Event</a>
            </div>
            <?php if (!empty($cp_shipment_event_edit_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_shipment_event_edit_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_shipment_event_edit_notice) ?>
                </p>
            <?php endif; ?>
            <?php if (empty($events)): ?>
                <p class="cp-form-helper">No events have been recorded for this shipment yet.</p>
            <?php else: ?>
                <div class="cp-event-edit-list">
                    <?php foreach ($events as $event): ?>
                        <form method="post" class="cp-event-edit-card" id="event-<?= (int)$event['id'] ?>">
                            <input type="hidden" name="shipment_id" value="<?= (int)$shipment['id'] ?>">
                            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                            <div class="cp-event-edit-head">
                                <h3>Event #<?= (int)$event['id'] ?></h3>
                                <span><?= htmlspecialchars(cp_detail_datetime_input($event['event_time_epoch'] ?? null) ?: '-') ?></span>
                            </div>
                            <div class="cp-location-grid">
                                <div>
                                    <label for="event_tracking_number_<?= (int)$event['id'] ?>">Tracking Number</label>
                                    <input id="event_tracking_number_<?= (int)$event['id'] ?>" type="text" name="event_tracking_number" value="<?= htmlspecialchars((string)$event['tracking_number']) ?>" required>
                                </div>
                                <div>
                                    <label for="event_time_<?= (int)$event['id'] ?>">Event Time</label>
                                    <input id="event_time_<?= (int)$event['id'] ?>" type="datetime-local" name="event_time" value="<?= htmlspecialchars(cp_detail_datetime_input($event['event_time_epoch'] ?? null)) ?>" required>
                                </div>
                                <div>
                                    <label for="event_location_label_<?= (int)$event['id'] ?>">Label</label>
                                    <select id="event_location_label_<?= (int)$event['id'] ?>" name="event_location_label">
                                        <?php foreach ($locationLabelOptions as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= (string)$event['location_label'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_severity_<?= (int)$event['id'] ?>">Severity</label>
                                    <select id="event_severity_<?= (int)$event['id'] ?>" name="event_severity">
                                        <?php foreach ($severityOptions as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= (string)$event['event_severity'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_transport_mode_<?= (int)$event['id'] ?>">Transport Mode</label>
                                    <select id="event_transport_mode_<?= (int)$event['id'] ?>" name="event_transport_mode">
                                        <?php foreach ($transportModeOptions as $value => $label): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= (string)($event['transport_mode'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_type_<?= (int)$event['id'] ?>">Event Type</label>
                                    <select id="event_type_<?= (int)$event['id'] ?>" name="event_type">
                                        <?php foreach ($eventTypeOptions as $value): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= (string)($event['event_type'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($value === '' ? '— Select Event Type —' : $value) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_location_type_<?= (int)$event['id'] ?>">Location Type</label>
                                    <select id="event_location_type_<?= (int)$event['id'] ?>" name="event_location_type">
                                        <?php foreach ($locationTypeOptions as $value): ?>
                                            <option value="<?= htmlspecialchars($value) ?>" <?= (string)($event['location_type'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($value === '' ? '— Select Location Type —' : $value) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_location_name_<?= (int)$event['id'] ?>">Location Name</label>
                                    <input id="event_location_name_<?= (int)$event['id'] ?>" type="text" name="event_location_name" value="<?= htmlspecialchars((string)$event['location_name']) ?>" required>
                                </div>
                                <div><label for="event_city_<?= (int)$event['id'] ?>">City</label><input id="event_city_<?= (int)$event['id'] ?>" type="text" name="event_city" value="<?= htmlspecialchars((string)($event['city'] ?? '')) ?>"></div>
                                <div><label for="event_state_region_<?= (int)$event['id'] ?>">State/Region</label><input id="event_state_region_<?= (int)$event['id'] ?>" type="text" name="event_state_region" value="<?= htmlspecialchars((string)($event['state_region'] ?? '')) ?>"></div>
                                <div><label for="event_country_code_<?= (int)$event['id'] ?>">Country Code</label><input id="event_country_code_<?= (int)$event['id'] ?>" type="text" name="event_country_code" value="<?= htmlspecialchars((string)($event['country_code'] ?? '')) ?>"></div>
                                <div><label for="event_postal_code_<?= (int)$event['id'] ?>">Postal Code</label><input id="event_postal_code_<?= (int)$event['id'] ?>" type="text" name="event_postal_code" value="<?= htmlspecialchars((string)($event['postal_code'] ?? '')) ?>"></div>
                                <div class="cp-location-grid-wide"><label for="event_status_text_<?= (int)$event['id'] ?>">Status Text</label><input id="event_status_text_<?= (int)$event['id'] ?>" type="text" name="event_status_text" value="<?= htmlspecialchars((string)$event['status_text']) ?>" required></div>
                                <div class="cp-location-grid-wide"><label for="event_issue_note_<?= (int)$event['id'] ?>">Issue Note</label><input id="event_issue_note_<?= (int)$event['id'] ?>" type="text" name="event_issue_note" value="<?= htmlspecialchars((string)($event['issue_note'] ?? '')) ?>"></div>
                                <div><label for="event_payment_amount_<?= (int)$event['id'] ?>">Payment Amount</label><input id="event_payment_amount_<?= (int)$event['id'] ?>" type="number" step="0.01" min="0" name="event_payment_amount" value="<?= htmlspecialchars(cp_detail_money_value($event['payment_amount'] ?? null)) ?>"></div>
                                <div class="cp-location-grid-wide"><label for="event_payment_reason_<?= (int)$event['id'] ?>">Payment Reason</label><input id="event_payment_reason_<?= (int)$event['id'] ?>" type="text" name="event_payment_reason" value="<?= htmlspecialchars((string)($event['payment_reason'] ?? '')) ?>"></div>
                                <div><label for="event_vessel_name_<?= (int)$event['id'] ?>">Vessel Name</label><input id="event_vessel_name_<?= (int)$event['id'] ?>" type="text" name="event_vessel_name" value="<?= htmlspecialchars((string)($event['vessel_name'] ?? '')) ?>"></div>
                                <div><label for="event_voyage_number_<?= (int)$event['id'] ?>">Voyage Number</label><input id="event_voyage_number_<?= (int)$event['id'] ?>" type="text" name="event_voyage_number" value="<?= htmlspecialchars((string)($event['voyage_number'] ?? '')) ?>"></div>
                                <div><label for="event_port_of_departure_<?= (int)$event['id'] ?>">Port of Departure</label><input id="event_port_of_departure_<?= (int)$event['id'] ?>" type="text" name="event_port_of_departure" value="<?= htmlspecialchars((string)($event['port_of_departure'] ?? '')) ?>"></div>
                                <div><label for="event_port_of_arrival_<?= (int)$event['id'] ?>">Port of Arrival</label><input id="event_port_of_arrival_<?= (int)$event['id'] ?>" type="text" name="event_port_of_arrival" value="<?= htmlspecialchars((string)($event['port_of_arrival'] ?? '')) ?>"></div>
                                <div class="cp-location-grid-span4 cp-checkbox-row">
                                    <label><input type="checkbox" name="event_is_current" value="1" <?= (int)($event['is_current'] ?? 0) === 1 ? 'checked' : '' ?>> Current event</label>
                                    <label><input type="checkbox" name="event_negative_paid" value="1" <?= (int)($event['negative_event_paid'] ?? 0) === 1 ? 'checked' : '' ?>> Negative event paid</label>
                                </div>
                            </div>
                            <div class="cp-quote-actions">
                                <button class="cp-btn" type="submit" name="update_shipment_event" value="1">Save Event #<?= (int)$event['id'] ?></button>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </div>

    <?php include("../../common-sections/footer.html"); ?>
</body>
</html>
