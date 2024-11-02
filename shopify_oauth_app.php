<?php

const SCOPES = 'read_products,write_products';

function getAccessToken($shop, $code, $apiKey, $apiSecret) {
    $url = "https://{$shop}.myshopify.com/admin/oauth/access_token";
    $data = [
        'client_id' => $apiKey,
        'client_secret' => $apiSecret,
        'code' => $code
    ];

    $response = sendPostRequest($url, $data);
    return $response ? $response['access_token'] : null;
}

function getShopInfo($shop, $accessToken) {
    $url = "https://{$shop}.myshopify.com/admin/api/2023-01/shop.json";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "X-Shopify-Access-Token: $accessToken"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function sendPostRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

if (isset($_GET['shop']) && isset($_GET['api_key']) && isset($_GET['api_secret']) && isset($_GET['redirect_uri']) && !isset($_GET['code'])) {
    // Step 1: Redirect to Shopify authorization
    $shop = $_GET['shop'];
    $apiKey = $_GET['api_key'];
    $redirectUri = $_GET['redirect_uri'];
    $authorizationUrl = "https://{$shop}.myshopify.com/admin/oauth/authorize?client_id=" . $apiKey
                      . "&scope=" . urlencode(SCOPES)
                      . "&redirect_uri=" . urlencode($redirectUri . "?api_key=" . $apiKey . "&api_secret=" . $_GET['api_secret']);
    header("Location: $authorizationUrl");
    exit();
} elseif (isset($_GET['code'], $_GET['shop'], $_GET['api_key'], $_GET['api_secret'])) {
    // Step 2: Handle callback and fetch access token
    $shop = $_GET['shop'];
    $apiKey = $_GET['api_key'];
    $apiSecret = $_GET['api_secret'];
    $accessToken = getAccessToken($shop, $_GET['code'], $apiKey, $apiSecret);
    if ($accessToken) {
        $shopInfo = getShopInfo($shop, $accessToken);
        echo "<script>window.onload = function() {
                document.getElementById('shopInfo').style.display = 'block';
                document.getElementById('shopData').textContent = " . json_encode($shopInfo) . ";
            }</script>";
    } else {
        echo "<p>Error: Unable to retrieve access token.</p>";
    }
} else {
    // Display HTML form if no parameters are set
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connect to Shopify</title>
    </head>
    <body>
        <h1>Connect Your Shopify Store</h1>
        <form id="shopifyForm">
            <label for="apiKey">Shopify API Key:</label>
            <input type="text" id="apiKey" name="apiKey" required>
            <br>
            <label for="apiSecret">Shopify API Secret:</label>
            <input type="text" id="apiSecret" name="apiSecret" required>
            <br>
            <label for="shopName">Shopify Store Name:</label>
            <input type="text" id="shopName" name="shopName" placeholder="example-store" required>
            <br>
            <label for="redirectUri">Redirect URI:</label>
            <input type="text" id="redirectUri" name="redirectUri" placeholder="https://your-redirect-uri.com" required>
            <br>
            <button type="button" onclick="connectShopify()">Connect</button>
        </form>
        <div id="shopInfo" style="display:none;">
            <h2>Shop Information</h2>
            <pre id="shopData"></pre>
        </div>

        <script>
            function connectShopify() {
                const apiKey = document.getElementById('apiKey').value;
                const apiSecret = document.getElementById('apiSecret').value;
                const shopName = document.getElementById('shopName').value;
                const redirectUri = document.getElementById('redirectUri').value;

                if (apiKey && apiSecret && shopName && redirectUri) {
                    window.location.href = `?shop=${shopName}&api_key=${apiKey}&api_secret=${apiSecret}&redirect_uri=${encodeURIComponent(redirectUri)}`;
                } else {
                    alert("Please fill out all fields.");
                }
            }
        </script>
    </body>
    </html>
    <?php
}
?>