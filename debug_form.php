<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Form Data</h2>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>FILES Data Received:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    echo "<h3>All Data:</h3>";
    echo "<pre>";
    print_r($_REQUEST);
    echo "</pre>";
} else {
    echo "<p>No POST data received. This script should be called via POST.</p>";
}
?> 