<?php
//setting initials
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    date_default_timezone_set('America/Chicago');







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



    if ($_SERVER["REQUEST_METHOD"] == "POST"  && isset($_POST['free-quote-button']) && !empty($_POST['free-quote-button'])) {
        require_once __DIR__ . '/common-sections/globals.php';

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
