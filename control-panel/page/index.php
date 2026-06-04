<?php include("../app.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Control Panel | Rapid Route Logistics</title>
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">
</head>
<body>
    <?php include("../partials/header.php");?>
    <div class="header-2">
        <div class="container">
            <h2 class="greeting" id="adminGreeting"><span class="material-symbols-outlined" aria-hidden="true">waving_hand</span> Welcome!</h2>
            <h1 class="cutomer-name" id="adminName">Admin</h1>
        </div>
    </div>
    <div class="container content">
        
        <section id="cp-add-location-event" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Add Shipping Location Event</h2>
                    <p>Add a new tracking update for a shipment timeline. Supports road, air, sea, rail, customs, and warehouse events.</p>
                </div>
            </div>
            <?php if (!empty($cp_location_event_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_location_event_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_location_event_notice) ?>
                </p>
            <?php endif; ?>
            <p class="cp-form-helper">Enter the tracking number — the system will locate the shipment automatically.</p>
            <form method="post" class="cp-location-form" novalidate id="locationEventForm">
                <div class="cp-location-grid">

                    <!-- Tracking Number -->
                    <div class="cp-location-grid-wide">
                        <label for="event_tracking_number">Tracking Number <span class="cp-required">*</span></label>
                        <input id="event_tracking_number" type="text" name="event_tracking_number" required>
                    </div>

                    <!-- Transport Mode -->
                    <div>
                        <label for="event_transport_mode">Transport Mode</label>
                        <select id="event_transport_mode" name="event_transport_mode">
                            <option value="">— Select Mode —</option>
                            <option value="road">Road</option>
                            <option value="air">Air</option>
                            <option value="sea">Sea</option>
                            <option value="rail">Rail</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>

                    <!-- Event Type -->
                    <div>
                        <label for="event_type">Event Type</label>
                        <select id="event_type" name="event_type">
                            <option value="">— Select Event Type —</option>
                            <option value="Shipment Created">Shipment Created</option>
                            <option value="Picked Up">Picked Up</option>
                            <option value="Departed Facility">Departed Facility</option>
                            <option value="Arrived Facility">Arrived Facility</option>
                            <option value="Customs Clearance">Customs Clearance</option>
                            <option value="Customs Hold">Customs Hold</option>
                            <option value="Port Arrival">Port Arrival</option>
                            <option value="Port Departure">Port Departure</option>
                            <option value="Vessel Departure">Vessel Departure</option>
                            <option value="Vessel Arrival">Vessel Arrival</option>
                            <option value="Warehouse Processing">Warehouse Processing</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Out For Delivery">Out For Delivery</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Delayed">Delayed</option>
                            <option value="Exception">Exception</option>
                            <option value="Payment Required">Payment Required</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Location Type -->
                    <div>
                        <label for="event_location_type">Location Type</label>
                        <select id="event_location_type" name="event_location_type">
                            <option value="">— Select Location Type —</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Distribution Center">Distribution Center</option>
                            <option value="Airport">Airport</option>
                            <option value="Seaport">Seaport</option>
                            <option value="Customs Office">Customs Office</option>
                            <option value="Checkpoint">Checkpoint</option>
                            <option value="Vessel">Vessel</option>
                            <option value="Aircraft">Aircraft</option>
                            <option value="Customer Address">Customer Address</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Event Severity -->
                    <div>
                        <label for="event_severity">Event Severity</label>
                        <select id="event_severity" name="event_severity">
                            <option value="neutral">Neutral</option>
                            <option value="negative">Negative</option>
                        </select>
                    </div>

                    <!-- Country Code -->
                    <div>
                        <label for="event_country_code">Country Code <span class="cp-required">*</span></label>
                        <input id="event_country_code" type="text" name="event_country_code" maxlength="2" value="US" placeholder="e.g. GB" required>
                    </div>

                    <!-- Location Name -->
                    <div>
                        <label for="event_location_name">Location Name <span class="cp-required">*</span></label>
                        <input id="event_location_name" type="text" name="event_location_name" placeholder="e.g. Heathrow Airport, Rotterdam Port" required>
                    </div>

                    <!-- City -->
                    <div>
                        <label for="event_city">City <span class="cp-optional">(optional)</span></label>
                        <input id="event_city" type="text" name="event_city">
                    </div>

                    <!-- State/Region -->
                    <div>
                        <label for="event_state_region">State / Region <span class="cp-optional">(optional)</span></label>
                        <input id="event_state_region" type="text" name="event_state_region">
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="event_postal_code">Postal Code <span class="cp-optional">(optional)</span></label>
                        <input id="event_postal_code" type="text" name="event_postal_code">
                    </div>

                    <!-- Status Text -->
                    <div class="cp-location-grid-wide">
                        <label for="event_status_text">Status Text <span class="cp-required">*</span></label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input id="event_status_text" type="text" name="event_status_text" placeholder="e.g. Arrived at Rotterdam Port, Netherlands" required style="flex:1;min-width:0;">
                            <button type="button" id="use-status-suggestion" title="Copy suggested text into the field" style="display:none;white-space:nowrap;flex-shrink:0;padding:8px 13px;background:#e8f7f3;border:1.5px solid #1A9B82;border-radius:8px;color:#1A9B82;font-size:0.82rem;font-weight:700;cursor:pointer;letter-spacing:0.01em;">✓ Use</button>
                        </div>
                        <span id="status-suggestion-preview" style="display:none;font-size:0.78rem;color:#7aaa9a;margin-top:4px;font-style:italic;"></span>
                    </div>

                    <!-- Sea Freight Fields (conditional: shown when transport mode = sea) -->
                    <div id="sea-fields" class="cp-location-grid-span4" style="display:none;">
                        <p class="cp-section-label"><span class="material-symbols-outlined" aria-hidden="true">directions_boat</span> Sea Freight Details</p>
                        <div class="cp-location-grid">
                            <div>
                                <label for="event_vessel_name">Vessel Name <span class="cp-optional">(optional)</span></label>
                                <input id="event_vessel_name" type="text" name="event_vessel_name" placeholder="e.g. MSC Oscar">
                            </div>
                            <div>
                                <label for="event_voyage_number">Voyage Number <span class="cp-optional">(optional)</span></label>
                                <input id="event_voyage_number" type="text" name="event_voyage_number" placeholder="e.g. V019E">
                            </div>
                            <div>
                                <label for="event_port_of_departure">Port of Departure <span class="cp-optional">(optional)</span></label>
                                <input id="event_port_of_departure" type="text" name="event_port_of_departure" placeholder="e.g. Shanghai, China">
                            </div>
                            <div>
                                <label for="event_port_of_arrival">Port of Arrival <span class="cp-optional">(optional)</span></label>
                                <input id="event_port_of_arrival" type="text" name="event_port_of_arrival" placeholder="e.g. Rotterdam, Netherlands">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Fields (conditional: shown when event type requires payment) -->
                    <div id="payment-fields" class="cp-location-grid-span4" style="display:none;">
                        <p class="cp-section-label"><span class="material-symbols-outlined" aria-hidden="true">payments</span> Payment Details</p>
                        <div class="cp-location-grid">
                            <div>
                                <label for="event_payment_amount">Payment Amount (£)</label>
                                <input id="event_payment_amount" type="number" min="0" step="0.01" name="event_payment_amount" placeholder="e.g. 150.00">
                            </div>
                            <div class="cp-location-grid-wide">
                                <label for="event_payment_reason">What the Payment Is For</label>
                                <input id="event_payment_reason" type="text" name="event_payment_reason" placeholder="e.g. Customs duty, documentation review fee">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="add_location_event" value="1">Add Location Event</button>
                </div>
            </form>
        </section>

        <section id="cp-exception-payments" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Exception Payments</h2>
                    <p>Latest 10 records from exception_issue_payments</p>
                </div>
                <a class="cp-btn" href="/control-panel/exception-payments/">See All Exception Payments</a>
            </div>
            <?php
            $exceptionPaymentTableCheck = $dbconn->query("SHOW TABLES LIKE 'exception_issue_payments'");
            $exceptionPaymentTableExists = ($exceptionPaymentTableCheck && $exceptionPaymentTableCheck->num_rows > 0);
            ?>
            <?php if (!empty($cp_exception_payment_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_exception_payment_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_exception_payment_notice) ?>
                </p>
            <?php endif; ?>
            <?php if (!$exceptionPaymentTableExists): ?>
                <p class="cp-quote-notice is-error">Table <code>exception_issue_payments</code> does not exist yet.</p>
            <?php else: ?>
                <div class="cp-table-wrap">
                    <table class="cp-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tracking</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Payment For</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Proof</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $exceptionPaymentSql = "
                                SELECT id, tracking_number, user_id, name, amount, payment_for, payment_method, proof_file_name, status, created_at_epoch
                                FROM exception_issue_payments
                                ORDER BY id DESC
                                LIMIT 10
                            ";
                            $exceptionPaymentResult = $dbconn->query($exceptionPaymentSql);
                            if ($exceptionPaymentResult && $exceptionPaymentResult->num_rows > 0):
                                while ($payment = $exceptionPaymentResult->fetch_assoc()):
                                    $createdTs = (int)($payment['created_at_epoch'] ?? 0);
                                    if ($createdTs > 1000000000000) {
                                        $createdTs = (int)($createdTs / 1000);
                                    }
                                    $createdDisplay = $createdTs > 0 ? date("M d, Y H:i", $createdTs) : "-";
                                    $proofFileName = trim((string)($payment['proof_file_name'] ?? ''));
                                    $proofHref = '/shipping/create/payments-upload/' . rawurlencode($proofFileName);
                                    $methodLabel = strtolower((string)($payment['payment_method'] ?? 'card')) === 'crypto' ? 'Other Payment Methods' : 'Payment Card';
                            ?>
                            <tr>
                                <td><?= (int)$payment['id'] ?></td>
                                <td><?= htmlspecialchars((string)$payment['tracking_number']) ?></td>
                                <td><?= (int)$payment['user_id'] ?></td>
                                <td><?= htmlspecialchars((string)$payment['name']) ?></td>
                                <td>$<?= number_format((float)($payment['amount'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars((string)$payment['payment_for']) ?></td>
                                <td><?= htmlspecialchars($methodLabel) ?></td>
                                <td><?= htmlspecialchars((string)$payment['status']) ?></td>
                                <td>
                                    <?php if ($proofFileName !== ''): ?>
                                        <a class="cp-table-link" href="<?= htmlspecialchars($proofHref) ?>" target="_blank" rel="noopener noreferrer">View Proof</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($createdDisplay) ?></td>
                                <td>
                                    <?php if (strtolower((string)($payment['status'] ?? '')) === 'pending_confirmation'): ?>
                                        <form method="post" class="cp-inline-form">
                                            <input type="hidden" name="exception_payment_id" value="<?= (int)$payment['id'] ?>">
                                            <button class="cp-btn" type="submit" name="confirm_exception_payment" value="1">Confirm Payment</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="cp-table-status"><?= htmlspecialchars(ucfirst((string)$payment['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="11">No exception payment records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="cp-card-foot">
                    <a class="cp-btn cp-btn-secondary" href="/control-panel/exception-payments/">View Complete Exception Payment List</a>
                </div>
            <?php endif; ?>
        </section>

        <section id="cp-user-payment-block" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>User Payment Block</h2>
                </div>
            </div>
            <?php if (!empty($cp_user_pay_block_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_user_pay_block_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_user_pay_block_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="user_id">User ID</label>
                        <input id="user_id" type="number" min="1" step="1" name="user_id" required>
                    </div>
                    <div>
                        <label for="pay_block_tittle">Pay Block Tittle</label>
                        <select id="pay_block_tittle" name="pay_block_tittle" required>
                            <option value="">Select Error Type</option>
                            <option value="Gateway Error">Gateway Error</option>
                            <option value="Transaction Processing Error">Transaction Processing Error</option>
                            <option value="Issuer / Bank System Problem">Issuer / Bank System Problem</option>
                            <option value="Not Available in Your Country">Not Available in Your Country</option>
                        </select>
                    </div>
                    <div>
                        <label for="pay_block_message_preview">Pay Block Message</label>
                        <input id="pay_block_message_preview" type="text" value="" placeholder="Auto-filled from selected error type" readonly>
                        <input id="pay_block_message" type="hidden" name="pay_block_message" value="">
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="update_user_pay_block" value="1">Update User Block</button>
                </div>
            </form>
        </section>

        <section id="cp-support-email" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Send Support Email</h2>
                    <p>Send styled support emails via Resend from support@rapidroutelogistics.uk</p>
                </div>
            </div>
            <?php if (!empty($cp_support_email_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_support_email_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_support_email_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="support_receiver_email">Receiver Email</label>
                        <input id="support_receiver_email" type="email" name="support_receiver_email" required>
                    </div>
                    <div>
                        <label for="support_subject">Subject</label>
                        <input id="support_subject" type="text" name="support_subject" required>
                    </div>
                    <div class="cp-quote-grid-wide">
                        <label for="support_message">Message</label>
                        <textarea id="support_message" name="support_message" rows="6" required></textarea>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="send_support_email" value="1">Send Support Email</button>
                </div>
            </form>
        </section>

        <section id="cp-shipments" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Shipments</h2>
                    <p>Latest 10 shipment records</p>
                </div>
                <a class="cp-btn" href="/control-panel/shipments/">See All Shipments</a>
            </div>
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tracking Number</th>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Arrival</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $shipSql = "
                            SELECT s.id, s.tracking_number, s.user_id, s.status, s.estimated_delivery_time, s.date_created, u.email, u.name
                            FROM shipments s
                            LEFT JOIN users u ON u.id = s.user_id
                            ORDER BY s.id DESC
                            LIMIT 10
                        ";
                        $shipResult = $dbconn->query($shipSql);
                        if ($shipResult && $shipResult->num_rows > 0):
                            while ($s = $shipResult->fetch_assoc()):
                                $shipTs = (int)$s['date_created'];
                                if ($shipTs > 1000000000000) {
                                    $shipTs = (int)($shipTs / 1000);
                                }
                                $shipDisplay = $shipTs > 0 ? date("M d, Y H:i", $shipTs) : "-";
                                $arrivalRaw = $s['estimated_delivery_time'] ?? null;
                                $arrivalDisplay = '-';
                                if ($arrivalRaw !== null && $arrivalRaw !== '') {
                                    if (is_numeric((string)$arrivalRaw)) {
                                        $arrivalTs = (int)$arrivalRaw;
                                        if ($arrivalTs > 1000000000000) {
                                            $arrivalTs = (int)($arrivalTs / 1000);
                                        }
                                        if ($arrivalTs > 0) {
                                            $arrivalDisplay = date("M d, Y H:i", $arrivalTs) . ' (epoch)';
                                        }
                                    } else {
                                        $parsedArrival = strtotime((string)$arrivalRaw);
                                        if ($parsedArrival !== false && $parsedArrival > 0) {
                                            $arrivalDisplay = date("M d, Y H:i", $parsedArrival) . ' (datetime)';
                                        } else {
                                            $arrivalDisplay = (string)$arrivalRaw;
                                        }
                                    }
                                }
                        ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td><?= htmlspecialchars((string)$s['tracking_number']) ?></td>
                            <td><?= (int)$s['user_id'] ?></td>
                            <td><?= htmlspecialchars((string)($s['email'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string)($s['name'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string)$s['status']) ?></td>
                            <td><?= htmlspecialchars($arrivalDisplay) ?></td>
                            <td><?= htmlspecialchars($shipDisplay) ?></td>
                            <td><a class="cp-btn cp-btn-secondary cp-btn-small" href="/control-panel/shipments/detail.php?id=<?= (int)$s['id'] ?>">Edit Details</a></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9">No shipments found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="cp-card-foot">
                <a class="cp-btn cp-btn-secondary" href="/control-panel/shipments/">View Complete Shipment List</a>
            </div>
        </section>

        <section id="cp-update-arrival-date" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Update Shipment Arrival Date</h2>
                    <p>Change estimated delivery/arrival date by tracking number.</p>
                </div>
            </div>
            <?php if (!empty($cp_arrival_date_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_arrival_date_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_arrival_date_notice) ?>
                </p>
            <?php endif; ?>
            <p class="cp-form-helper">This checks the <code>shipments.estimated_delivery_time</code> column type first, then stores either epoch seconds or datetime accordingly.</p>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="arrival_tracking_number">Tracking Number</label>
                        <input id="arrival_tracking_number" type="text" name="arrival_tracking_number" required>
                    </div>
                    <div>
                        <label for="arrival_date">Arrival Date</label>
                        <input id="arrival_date" type="datetime-local" name="arrival_date" required>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="update_shipment_arrival_date" value="1">Update Arrival Date</button>
                </div>
            </form>
        </section>

        <section id="cp-update-shipment-status" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Update Shipment Status</h2>
                    <p>Set the shipment status and progress level. This controls the progress bar and status badge on the tracking page.</p>
                </div>
            </div>
            <?php if (!empty($cp_shipment_status_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_shipment_status_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_shipment_status_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="status_tracking_number">Tracking Number <span class="cp-required">*</span></label>
                        <input id="status_tracking_number" type="text" name="status_tracking_number" required placeholder="e.g. 1Z9A8B7C6D5E4F3G">
                    </div>
                    <div>
                        <label for="new_shipment_status">New Status <span class="cp-required">*</span></label>
                        <select id="new_shipment_status" name="new_shipment_status" required>
                            <option value="">— Select Status —</option>
                            <option value="pending">Pending — Label Created</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="shipped">Shipped / Departed</option>
                            <option value="in_transit">In Transit</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Exception / Issue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="new_completion_pct">Completion % <span class="cp-optional">(optional, 0–100)</span></label>
                        <input id="new_completion_pct" type="number" name="new_completion_pct" min="0" max="100" step="1" placeholder="Leave blank to use default">
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="update_shipment_status" value="1">Update Status</button>
                </div>
            </form>
        </section>

        <section id="cp-service-quotes" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Service Quotes</h2>
                    <p>Latest 10 records from shipment_service_quotes</p>
                </div>
                <a class="cp-btn" href="/control-panel/service-quotes/">See All Service Quotes</a>
            </div>
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Service Level</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $quotesSql = "
                            SELECT q.id, q.user_id, q.service_level, q.processing_status, q.price, q.duration, q.created_at_epoch, u.email
                            FROM shipment_service_quotes q
                            LEFT JOIN users u ON u.id = q.user_id
                            ORDER BY q.id DESC
                            LIMIT 10
                        ";
                        $quotesResult = $dbconn->query($quotesSql);
                        if ($quotesResult && $quotesResult->num_rows > 0):
                            while ($q = $quotesResult->fetch_assoc()):
                                $createdTs = (int)$q['created_at_epoch'];
                                if ($createdTs > 1000000000000) {
                                    $createdTs = (int)($createdTs / 1000);
                                }
                                $createdDisplay = $createdTs > 0 ? date("M d, Y H:i", $createdTs) : "-";
                                $priceDisplay = ($q['price'] !== null && $q['price'] !== '') ? ('$' . number_format((float)$q['price'], 2)) : '-';
                        ?>
                        <tr>
                            <td><?= (int)$q['id'] ?></td>
                            <td><?= (int)$q['user_id'] ?></td>
                            <td><?= htmlspecialchars((string)($q['email'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string)$q['service_level']) ?></td>
                            <td><?= htmlspecialchars((string)$q['processing_status']) ?></td>
                            <td><?= htmlspecialchars($priceDisplay) ?></td>
                            <td><?= isset($q['duration']) && $q['duration'] !== null && $q['duration'] !== '' ? ((int)$q['duration'] . ' day' . (((int)$q['duration'] === 1) ? '' : 's')) : '-' ?></td>
                            <td><?= htmlspecialchars($createdDisplay) ?></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8">No service quote records found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="cp-card-foot">
                <a class="cp-btn cp-btn-secondary" href="/control-panel/service-quotes/">View Complete Service Quote List</a>
            </div>
        </section>

        <section id="cp-payment-proofs" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Payment Proofs</h2>
                    <p>Latest 10 records from shipment_payment_proofs</p>
                </div>
                <a class="cp-btn" href="/control-panel/payment-proofs/">See All Payment Proofs</a>
            </div>
            <?php
            $proofTableCheck = $dbconn->query("SHOW TABLES LIKE 'shipment_payment_proofs'");
            $proofTableExists = ($proofTableCheck && $proofTableCheck->num_rows > 0);
            if ($proofTableExists && function_exists('cp_ensure_shipment_payment_proof_columns')) {
                cp_ensure_shipment_payment_proof_columns($dbconn);
            }
            $proofHasStatusColumn = $proofTableExists && function_exists('cp_table_has_column')
                ? cp_table_has_column($dbconn, 'shipment_payment_proofs', 'status')
                : false;
            ?>
            <?php if (!empty($cp_shipment_proof_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_shipment_proof_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_shipment_proof_notice) ?>
                </p>
            <?php endif; ?>
            <?php if (!$proofTableExists): ?>
                <p class="cp-quote-notice is-error">Table <code>shipment_payment_proofs</code> does not exist yet.</p>
            <?php else: ?>
                <div class="cp-table-wrap">
                    <table class="cp-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $proofSql = $proofHasStatusColumn
                                ? "SELECT id, user_id, name, email, file_name, status, uploaded_at_epoch FROM shipment_payment_proofs ORDER BY id DESC LIMIT 10"
                                : "SELECT id, user_id, name, email, file_name, uploaded_at_epoch, 'pending_confirmation' AS status FROM shipment_payment_proofs ORDER BY id DESC LIMIT 10";
                            $proofResult = $dbconn->query($proofSql);
                            if ($proofResult && $proofResult->num_rows > 0):
                                while ($proof = $proofResult->fetch_assoc()):
                                    $uploadedTs = (int)$proof['uploaded_at_epoch'];
                                    if ($uploadedTs > 1000000000000) {
                                        $uploadedTs = (int)($uploadedTs / 1000);
                                    }
                                    $uploadedDisplay = $uploadedTs > 0 ? date("M d, Y H:i", $uploadedTs) : "-";
                                    $fileName = (string)($proof['file_name'] ?? '');
                                    $fileHref = '/shipping/create/payments-upload/' . rawurlencode($fileName);
                            ?>
                            <tr>
                                <td><?= (int)$proof['id'] ?></td>
                                <td><?= (int)$proof['user_id'] ?></td>
                                <td><?= htmlspecialchars((string)$proof['name']) ?></td>
                                <td><?= htmlspecialchars((string)$proof['email']) ?></td>
                                <td><?= htmlspecialchars($fileName) ?></td>
                                <td><?= htmlspecialchars((string)($proof['status'] ?? 'pending_confirmation')) ?></td>
                                <td><?= htmlspecialchars($uploadedDisplay) ?></td>
                                <td>
                                    <?php if ($fileName !== ''): ?>
                                        <a class="cp-table-link" href="<?= htmlspecialchars($fileHref) ?>" target="_blank" rel="noopener noreferrer">View File</a>
                                        <?php if ($proofHasStatusColumn && strtolower((string)($proof['status'] ?? 'pending_confirmation')) !== 'confirmed'): ?>
                                            <form method="post" class="cp-inline-form" style="margin-top:8px;">
                                                <input type="hidden" name="shipment_payment_proof_id" value="<?= (int)$proof['id'] ?>">
                                                <button class="cp-btn" type="submit" name="confirm_shipment_payment_proof" value="1">Confirm Proof</button>
                                            </form>
                                        <?php elseif ($proofHasStatusColumn): ?>
                                            <div class="cp-table-status">Confirmed</div>
                                        <?php else: ?>
                                            <div class="cp-table-status">Status column unavailable</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8">No payment proof records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="cp-card-foot">
                    <a class="cp-btn cp-btn-secondary" href="/control-panel/payment-proofs/">View Complete Payment Proof List</a>
                </div>
            <?php endif; ?>
        </section>

        <section id="cp-negative-events" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Negative Events</h2>
                    <p>Recent negative shipment events and payment status controls.</p>
                </div>
            </div>
            <?php if (!empty($cp_negative_event_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_negative_event_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_negative_event_notice) ?>
                </p>
            <?php endif; ?>
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tracking</th>
                            <th>Status</th>
                            <th>Payment Amount</th>
                            <th>Payment For</th>
                            <th>Paid?</th>
                            <th>Event Time</th>
                            <th>Mark Paid/Unpaid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $negativeEventsSql = "
                            SELECT id, tracking_number, status_text, payment_amount, payment_reason, negative_event_paid, event_time_epoch
                            FROM shipment_location_events
                            WHERE event_severity = 'negative'
                            ORDER BY event_time_epoch DESC, id DESC
                            LIMIT 20
                        ";
                        $negativeEventsResult = $dbconn->query($negativeEventsSql);
                        if ($negativeEventsResult && $negativeEventsResult->num_rows > 0):
                            while ($negativeEvent = $negativeEventsResult->fetch_assoc()):
                                $eventTs = (int)($negativeEvent['event_time_epoch'] ?? 0);
                                if ($eventTs > 1000000000000) {
                                    $eventTs = (int)($eventTs / 1000);
                                }
                                $eventDisplay = $eventTs > 0 ? date("M d, Y H:i", $eventTs) : "-";
                                $isPaid = (int)($negativeEvent['negative_event_paid'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td><?= (int)$negativeEvent['id'] ?></td>
                            <td><?= htmlspecialchars((string)$negativeEvent['tracking_number']) ?></td>
                            <td><?= htmlspecialchars((string)$negativeEvent['status_text']) ?></td>
                            <td>
                                <?php if ($negativeEvent['payment_amount'] !== null && $negativeEvent['payment_amount'] !== ''): ?>
                                    $<?= number_format((float)$negativeEvent['payment_amount'], 2) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string)($negativeEvent['payment_reason'] ?? '-')) ?></td>
                            <td>
                                <span class="cp-table-status"><?= $isPaid ? 'Paid' : 'Unpaid' ?></span>
                            </td>
                            <td><?= htmlspecialchars($eventDisplay) ?></td>
                            <td>
                                <form method="post" class="cp-inline-form">
                                    <input type="hidden" name="negative_event_id" value="<?= (int)$negativeEvent['id'] ?>">
                                    <select name="negative_event_paid_status">
                                        <option value="unpaid" <?= !$isPaid ? 'selected' : '' ?>>Unpaid</option>
                                        <option value="paid" <?= $isPaid ? 'selected' : '' ?>>Paid</option>
                                    </select>
                                    <button class="cp-btn" type="submit" name="update_negative_event_paid" value="1">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8">No negative events found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="cp-site-users" class="cp-card cp-card-list">
            <div class="cp-card-head">
                <div>
                    <h2>Site Users</h2>
                    <p>Latest 10 registered users</p>
                </div>
                <a class="cp-btn" href="/control-panel/site-users/">See All Users</a>
            </div>
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Country Code</th>
                            <th>Phone</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $usersSql = "SELECT id, name, email, username, country_code, phone_number, created_at FROM users ORDER BY id DESC LIMIT 10";
                        $usersResult = $dbconn->query($usersSql);
                        if ($usersResult && $usersResult->num_rows > 0):
                            while ($u = $usersResult->fetch_assoc()):
                                $joinedTs = (int)$u['created_at'];
                                if ($joinedTs > 1000000000000) {
                                    $joinedTs = (int)($joinedTs / 1000);
                                }
                                $joinedDisplay = $joinedTs > 0 ? date("M d, Y H:i", $joinedTs) : "-";
                        ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars((string)$u['name']) ?></td>
                            <td><?= htmlspecialchars((string)$u['email']) ?></td>
                            <td><?= htmlspecialchars((string)$u['username']) ?></td>
                            <td><?= htmlspecialchars((string)$u['country_code']) ?></td>
                            <td><?= htmlspecialchars((string)$u['phone_number']) ?></td>
                            <td><?= htmlspecialchars($joinedDisplay) ?></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7">No users found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="cp-card-foot">
                <a class="cp-btn cp-btn-secondary" href="/control-panel/site-users/">View Complete User List</a>
            </div>
        </section>

        <section id="cp-edit-service-quote" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Edit Service Quote</h2>
                    <p>Update shipment service quote values using quote ID</p>
                </div>
            </div>
            <?php if (!empty($cp_quote_update_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_quote_update_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_quote_update_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="quote_id">Quote ID</label>
                        <input id="quote_id" type="number" min="1" step="1" name="quote_id" required>
                    </div>
                    <div>
                        <label for="quote_price">Price</label>
                        <input id="quote_price" type="number" min="0" step="0.01" name="quote_price" required>
                    </div>
                    <div>
                        <label for="quote_duration">Duration (days)</label>
                        <input id="quote_duration" type="number" min="1" step="1" name="quote_duration" required>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="update_service_quote" value="1">Update Quote</button>
                </div>
            </form>
        </section>

        <section id="cp-delete-service-quote" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Delete Service Quote</h2>
                    <p>Delete a shipment service quote using quote ID</p>
                </div>
            </div>
            <?php if (!empty($cp_quote_delete_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_quote_delete_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_quote_delete_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="delete_quote_id">Quote ID</label>
                        <input id="delete_quote_id" type="number" min="1" step="1" name="delete_quote_id" required>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="delete_service_quote" value="1">Delete Quote</button>
                </div>
            </form>
        </section>

        <section id="cp-delete-shipment" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Delete Shipment</h2>
                    <p>Delete a shipment record using shipment ID</p>
                </div>
            </div>
            <?php if (!empty($cp_shipment_delete_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_shipment_delete_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_shipment_delete_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="delete_shipment_id">Shipment ID</label>
                        <input id="delete_shipment_id" type="number" min="1" step="1" name="delete_shipment_id" required>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="delete_shipment_record" value="1">Delete Shipment</button>
                </div>
            </form>
        </section>

        <section id="cp-delete-site-user" class="cp-card cp-card-action">
            <div class="cp-card-head">
                <div>
                    <h2>Delete Site User</h2>
                    <p>Delete a user record using user ID</p>
                </div>
            </div>
            <?php if (!empty($cp_user_delete_notice)): ?>
                <p class="cp-quote-notice <?= ($cp_user_delete_notice_type === 'success') ? 'is-success' : 'is-error' ?>">
                    <?= htmlspecialchars($cp_user_delete_notice) ?>
                </p>
            <?php endif; ?>
            <form method="post" class="cp-quote-form">
                <div class="cp-quote-grid">
                    <div>
                        <label for="delete_user_id">User ID</label>
                        <input id="delete_user_id" type="number" min="1" step="1" name="delete_user_id" required>
                    </div>
                </div>
                <div class="cp-quote-actions">
                    <button class="cp-btn" type="submit" name="delete_site_user" value="1">Delete User</button>
                </div>
            </form>
        </section>

        <!-- Section group dividers (CSS order positions them visually) -->
        <div class="cp-group-header" id="cpg-tracking">
            <h3><span class="material-symbols-outlined">package_2</span> Tracking &amp; Events</h3>
            <p>Add and manage shipment tracking events and status updates.</p>
        </div>
        <div class="cp-group-header" id="cpg-payments">
            <h3><span class="material-symbols-outlined">payments</span> Payments &amp; Exceptions</h3>
            <p>Review exception payments and manage user payment restrictions.</p>
        </div>
        <div class="cp-group-header" id="cpg-comms">
            <h3><span class="material-symbols-outlined">mail</span> Communications</h3>
            <p>Send support emails to users.</p>
        </div>
        <div class="cp-group-header" id="cpg-records">
            <h3><span class="material-symbols-outlined">table</span> Data Records</h3>
            <p>Browse the latest shipments, quotes, proofs, events, and users.</p>
        </div>
        <div class="cp-group-header" id="cpg-quote-tools">
            <h3><span class="material-symbols-outlined">edit_note</span> Quote Tools</h3>
            <p>Edit or remove service quotes.</p>
        </div>
        <div class="cp-group-header cp-group-header-danger" id="cpg-danger">
            <h3><span class="material-symbols-outlined">warning</span> Danger Zone</h3>
            <p>Permanent deletions — these actions cannot be undone.</p>
        </div>

    </div>
    <?php include("../../common-sections/footer.html");?>
    <script>
    (function () {
        var greetingEl = document.getElementById('adminGreeting');
        var nameEl = document.getElementById('adminName');
        if (!greetingEl) return;

        var hour = new Date().getHours();
        var period = 'Evening';
        if (hour >= 5 && hour < 12) {
            period = 'Morning';
        } else if (hour >= 12 && hour < 18) {
            period = 'Afternoon';
        }

        var adminName = nameEl ? nameEl.textContent.trim() : 'Admin';
        greetingEl.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">waving_hand</span> Good ' + period + ', ' + adminName + '!';
    })();

    (function () {
        var titleSelect = document.getElementById('pay_block_tittle');
        var hiddenMessageInput = document.getElementById('pay_block_message');
        var previewMessageInput = document.getElementById('pay_block_message_preview');
        if (!titleSelect || !hiddenMessageInput || !previewMessageInput) return;

        var messageMap = {
            'Gateway Error': 'A payment gateway error occurred while processing your payment. Please try again.',
            'Transaction Processing Error': 'We were unable to process your payment at this time. Please try again.',
            'Issuer / Bank System Problem': 'The card issuer is currently unavailable. Please try again later.',
            'Not Available in Your Country': 'This payment method is not supported in your region.'
        };

        function syncPayBlockMessage() {
            var selectedTitle = titleSelect.value || '';
            var message = messageMap[selectedTitle] || '';
            hiddenMessageInput.value = message;
            previewMessageInput.value = message;
        }

        titleSelect.addEventListener('change', syncPayBlockMessage);
        syncPayBlockMessage();
    })();

    (function () {
        var modeSelect = document.getElementById('event_transport_mode');
        var typeSelect = document.getElementById('event_type');
        var seaFields  = document.getElementById('sea-fields');
        var payFields  = document.getElementById('payment-fields');
        var paymentTypes = ['Payment Required', 'Exception'];

        function updateSeaFields() {
            if (modeSelect && seaFields) {
                seaFields.style.display = (modeSelect.value === 'sea') ? '' : 'none';
            }
        }

        function updatePaymentFields() {
            if (typeSelect && payFields) {
                payFields.style.display = (paymentTypes.indexOf(typeSelect.value) !== -1) ? '' : 'none';
            }
        }

        if (modeSelect) modeSelect.addEventListener('change', updateSeaFields);
        if (typeSelect) typeSelect.addEventListener('change', updatePaymentFields);
        updateSeaFields();
        updatePaymentFields();
    })();

    (function () {
        var statusInput  = document.getElementById('event_status_text');
        var useBtn       = document.getElementById('use-status-suggestion');
        var preview      = document.getElementById('status-suggestion-preview');
        if (!statusInput || !useBtn || !preview) return;

        var templates = {
            'Shipment Created':     'Shipment information received at {location}',
            'Picked Up':            'Picked up from {location}, ready for departure',
            'Departed Facility':    'Departed facility at {location}',
            'Arrived Facility':     'Arrived at {location}',
            'Customs Clearance':    'Package cleared customs at {location}',
            'Customs Hold':         'Shipment held at customs \u2013 {location}',
            'Port Arrival':         'Arrived at port \u2013 {location}',
            'Port Departure':       'Departed from port at {location}',
            'Vessel Departure':     'Vessel departed from {location}',
            'Vessel Arrival':       'Vessel arrived at {location}',
            'Warehouse Processing': 'Processing at warehouse \u2013 {location}',
            'In Transit':           'Shipment in transit through {location}',
            'Out For Delivery':     'Out for delivery \u2013 {location}',
            'Delivered':            'Package successfully delivered at {location}',
            'Delayed':              'Shipment delayed at {location}',
            'Exception':            'Exception encountered at {location}',
            'Payment Required':     'Payment required \u2013 shipment held at {location}',
            'Other':                'Update from {location}'
        };

        function buildLocation() {
            var city    = (document.getElementById('event_city')         || {value:''}).value.trim();
            var state   = (document.getElementById('event_state_region') || {value:''}).value.trim();
            var country = (document.getElementById('event_country_code') || {value:''}).value.trim();
            var locName = (document.getElementById('event_location_name')|| {value:''}).value.trim();
            var parts   = [];
            if (city)                       parts.push(city);
            if (state && state !== city)    parts.push(state);
            if (country)                    parts.push(country);
            return parts.length > 0 ? parts.join(', ') : locName;
        }

        function getSuggestion() {
            var typeEl = document.getElementById('event_type');
            var type   = typeEl ? typeEl.value : '';
            var tmpl   = templates[type];
            if (!tmpl) return '';
            var loc = buildLocation();
            if (!loc) return '';
            return tmpl.replace('{location}', loc);
        }

        function update() {
            var suggestion = getSuggestion();
            if (suggestion) {
                statusInput.placeholder = suggestion;
                preview.textContent     = suggestion;
                preview.style.display   = 'block';
                useBtn.style.display    = '';
            } else {
                statusInput.placeholder = 'e.g. Arrived at Rotterdam Port, Netherlands';
                preview.style.display   = 'none';
                useBtn.style.display    = 'none';
            }
        }

        useBtn.addEventListener('click', function () {
            var suggestion = getSuggestion();
            if (suggestion) {
                statusInput.value = suggestion;
                statusInput.focus();
            }
        });

        ['event_type','event_transport_mode','event_location_name',
         'event_city','event_state_region','event_country_code'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.addEventListener('change', update); el.addEventListener('input', update); }
        });

        update();
    })();
    </script>
    </body>
</html>
