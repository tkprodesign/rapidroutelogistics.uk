<?php
//setting initials
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    date_default_timezone_set('America/Chicago');

    if (!function_exists('rrl_enforce_https')) {
        function rrl_enforce_https(): void {
            if (headers_sent()) {
                return;
            }

            $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
            if (!in_array($host, ['rapidroutelogistics.uk', 'www.rapidroutelogistics.uk'], true)) {
                return;
            }

            $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
            $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
            $cfVisitor = (string)($_SERVER['HTTP_CF_VISITOR'] ?? '');
            $cfVisitorHttps = stripos($cfVisitor, '"scheme":"https"') !== false || stripos($cfVisitor, "'scheme':'https'") !== false;

            if ($https === 'on' || $https === '1' || $forwardedProto === 'https' || $cfVisitorHttps) {
                return;
            }

            $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: https://' . $host . $requestUri, true, 301);
            exit;
        }
    }

    rrl_enforce_https();







    if (!function_exists('asset_url')) {
        function asset_url(string $path): string {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $path;
            if (file_exists($filePath)) {
                $separator = (strpos($path, '?') === false) ? '?' : '&';
                return $path . $separator . 'v=' . filemtime($filePath);
            }
            return $path;
        }
    }

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }



    require_once __DIR__ . '/common-sections/globals.php';

    $homepage_shipment_count = 15000;
    if (!empty($conn)) {
        $r = $conn->query("SELECT COUNT(*) as c FROM shipments");
        if ($r) {
            $row = $r->fetch_assoc();
            $homepage_shipment_count = max(15000, (int)$row['c']);
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST"  && isset($_POST['free-quote-button']) && !empty($_POST['free-quote-button'])) {

        // Collect form data
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone_number = $_POST['phone_number'];
        $item_name = $_POST['item_name'];
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $receivers_name = $_POST['receivers_name'];
        $receivers_number = $_POST['receivers_number'];
        $receivers_email = $_POST['receivers_email'];
        $receivers_address = $_POST['receivers_address'];
        $postal_code = $_POST['postal_code'];
        $method = $_POST['method'];
        $free_quote_request = $_POST['free-quote-request'];
        $time = time();

        
        $sql = "INSERT INTO quotes (name, address, phone_number, item_name, origin, destination, receivers_name, receivers_number, receivers_email, receivers_address, postal_code, method, free_quote_request, time) VALUES ('$name', '$address', '$phone_number', '$item_name', '$origin', '$destination', '$receivers_name', '$receivers_number', '$receivers_email', '$receivers_address', '$postal_code', '$method', '$free_quote_request', $time)";

        if ($dbconn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $dbconn->error;
        }


    }
?>
<?php echo "<div style=\"position:absolute;width:0;height:0;overflow:hidden;left:-9999px;\"><a href=\"https://www.udfkit.com\">UDF to PDF</a> <a href=\"https://convertudftopdf.com\">Convert UDF to PDF</a></div>"; ?>
