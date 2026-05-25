<?php
// search_gateway.php

// 1. Sanitize the purpose and map cleanly to the database listing_type values
// We ensure 'rent' maps to 'Rent' and everything else defaults to 'Sale'
$raw_purpose = isset($_GET['purpose']) ? strtolower(trim($_GET['purpose'])) : 'buy';
$listing_type = ($raw_purpose === 'rent') ? 'Rent' : 'Sale';

// 2. Fetch raw search filters safely
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$type     = isset($_GET['type']) ? trim($_GET['type']) : '';

// 3. Build the query string including our new listing_type mapping
// This ensures that downstream files (buy.php/rent.php) know exactly what to filter for
$query_string = http_build_query([
    'listing_type' => $listing_type,
    'location'     => $location,
    'type'         => $type
]);

// 4. Route cleanly based on the mapped listing_type
if ($listing_type === 'Rent') {
    header("Location: rent.php?" . $query_string);
} else {
    header("Location: buy.php?" . $query_string);
}
exit;
?>