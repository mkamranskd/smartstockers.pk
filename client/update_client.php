<?php
require_once '../php_action/db_connect.php';

if (isset($_POST['update_vendor'])) {
    // Get client info
    $client_id = $_POST['client_id'];
    $vendor_name = $_POST['vendor_name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $cnic = $_POST['cnic'];
    $rate_of_product = $_POST['rate_of_product'];
    $address = $_POST['address'];

    // Update clients table
    $sql = "UPDATE clients SET vendor_name = ?, phone_number = ?, email = ?, cnic = ?, rate_of_product = ?, address = ? WHERE client_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sssssss", $vendor_name, $phone_number, $email, $cnic, $rate_of_product, $address, $client_id);

    if ($stmt->execute()) {
        // ✅ Clear previous VDS & VS records
        $connect->query("DELETE FROM vendor_delivery_services WHERE client_id = '$client_id'");
        $connect->query("DELETE FROM vendor_stores WHERE client_id = '$client_id'");

        // ✅ Insert updated VDS
        if (isset($_POST['delivery_type']) && isset($_POST['api_token'])) {
            $delivery_types = $_POST['delivery_type'];
            $api_tokens = $_POST['api_token'];

            for ($i = 0; $i < count($delivery_types); $i++) {
                $type = $delivery_types[$i];
                $token = $api_tokens[$i];
                if (!empty($type) && !empty($token)) {
                    $stmt = $connect->prepare("INSERT INTO vendor_delivery_services (client_id, delivery_type, api_token) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $client_id, $type, $token);
                    $stmt->execute();
                }
            }
        }

        // ✅ Insert updated VS
        if (isset($_POST['store_link']) && isset($_POST['store_type'])) {
            $store_links = $_POST['store_link'];
            $store_types = $_POST['store_type'];
            $consumer_keys = $_POST['consumer_key'];
            $consumer_secrets = $_POST['consumer_secret'];
            $access_tokens = $_POST['access_token'];
            $platforms = $_POST['platform'];

            for ($i = 0; $i < count($store_links); $i++) {
                $link = $store_links[$i];
                $type = $store_types[$i];
                $key = $consumer_keys[$i];
                $secret = $consumer_secrets[$i];
                $token = $access_tokens[$i];
                $platform = $platforms[$i];

                if (!empty($link) && !empty($type)) {
                    $stmt = $connect->prepare("INSERT INTO vendor_stores (client_id, store_link, store_type, consumer_key, consumer_secret, access_token, platform) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $client_id, $link, $type, $key, $secret, $token, $platform);
                    $stmt->execute();
                }
            }
        }

        header('location:client.php');
    } else {
        echo "Error updating client: " . $connect->error;
    }
}
?>