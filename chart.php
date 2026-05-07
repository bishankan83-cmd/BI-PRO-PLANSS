
<?php

// Function to fetch stock data from Alpha Vantage
function fetchStockData($symbol) {
    $api_key = 'XREGEAVRHKICQ3XT'; // Replace with your Alpha Vantage API key
    $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$symbol}.CSE&apikey={$api_key}";

    // Initialize curl session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute curl session
    $response = curl_exec($ch);

    // Close curl session
    curl_close($ch);

    // Decode JSON response
    $data = json_decode($response, true);

    // Check if data retrieved successfully
    if (isset($data['Global Quote'])) {
        return $data['Global Quote'];
    } else {
        return null;
    }
}

// Example usage:
$stock_symbol = 'NASDAQ%3ANDX'; // Replace with the stock symbol you want to fetch, e.g., 'AAPL' for Apple Inc.
$stock_data = fetchStockData($stock_symbol);

// Display the stock data
if ($stock_data) {
    echo "Symbol: {$stock_data['01. symbol']}<br>";
    echo "Open: {$stock_data['02. open']}<br>";
    echo "High: {$stock_data['03. high']}<br>";
    echo "Low: {$stock_data['04. low']}<br>";
    echo "Price: {$stock_data['05. price']}<br>";
    echo "Volume: {$stock_data['06. volume']}<br>";
    echo "Latest trading day: {$stock_data['07. latest trading day']}<br>";
    echo "Previous close: {$stock_data['08. previous close']}<br>";
    echo "Change: {$stock_data['09. change']}<br>";
    echo "Change percent: {$stock_data['10. change percent']}<br>";
} else {
    echo "Failed to fetch stock data.";
}
?>
