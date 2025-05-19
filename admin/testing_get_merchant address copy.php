<?php
// Function to fetch merchant details
function getMerchantDetails() {
    $api_url = "https://api.postex.pk/services/integration/api/order/v1/get-merchant-address";
    $api_key = "MmRiMWE4MDA5ZTAxNDQ4NDgyZGIzY2MxZTAxYmUyZmM6MTRiNDQ5MTE4NWYzNDhkODg1OTZhYjU2YTBlYmVjNDY=";

    $headers = [
        "token: $api_key",  // 🔹 Send API key as "token" header (not Authorization)
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification issues
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_msg = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    } else {
        return [
            "statusCode" => $http_code,
            "statusMessage" => "API Request Failed",
            "error" => $error_msg,
            "response" => $response
        ];
    }
}

// Check if the "Fetch Merchant Details" button was clicked
$merchantData = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $merchantData = getMerchantDetails();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchant Details - PostEx</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Merchant Details</h2>
        <form method="POST">
            <button type="submit" class="btn btn-primary mb-3">Fetch Merchant Details</button>
        </form>

        <?php if ($merchantData): ?>
            <?php if ($merchantData["statusCode"] == 200 && !empty($merchantData["dist"])): ?>
                <table class="table table-bordered">
                    <tr><th>Warehouse Contact</th><td><?= htmlspecialchars($merchantData["dist"][0]["contactPersonName"] ?? "N/A") ?></td></tr>
                    <tr><th>Phone 1</th><td><?= htmlspecialchars($merchantData["dist"][0]["phone1"] ?? "N/A") ?></td></tr>
                    <tr><th>Phone 2</th><td><?= htmlspecialchars($merchantData["dist"][0]["phone2"] ?? "N/A") ?></td></tr>
                    <tr><th>City</th><td><?= htmlspecialchars($merchantData["dist"][0]["cityName"] ?? "N/A") ?></td></tr>
                    <tr><th>Address</th><td><?= htmlspecialchars($merchantData["dist"][0]["address"] ?? "N/A") ?></td></tr>
                    <tr><th>Address Code</th><td><?= htmlspecialchars($merchantData["dist"][0]["addressCode"] ?? "N/A") ?></td></tr>
                </table>
            <?php else: ?>
                <p class="text-danger">Error: <?= htmlspecialchars($merchantData["statusMessage"]) ?></p>
                <pre><?= json_encode($merchantData, JSON_PRETTY_PRINT) ?></pre> <!-- Show detailed API response -->
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
