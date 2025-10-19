<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!--
AUTOR      : Vítor Fernandes
DATA       : 11-MAR-2024
ATUALIZADO : 19-OUT-2025
-->
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <title>IP's Verification</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="apple-touch-icon" href="img/favicon.png">
    <link rel="icon" href="img/32x32.png" sizes="32x32">
    <link rel="icon" href="img/192x192.png" sizes="192x192">
    <link rel="stylesheet" type="text/css" href="css/estilo.css">
</head>

<body>
    <?php
    include_once 'config.php';

    function generateDeviceGrid($ips, $category_name) {
        $html = '<div class="category-section">';
        $html .= '<h2>' . htmlspecialchars($category_name) . '</h2>';
        $html .= '<div class="device-grid">';

        foreach ($ips as $ip => $location) {
            $safe_ip = htmlspecialchars($ip);
            $safe_location = htmlspecialchars($location);

            $html .= '<div class="device-card" id="card-' . $safe_ip . '">';
            $html .= '  <div class="card-header">';
            $html .= '    <span class="status-dot" id="status-dot-' . $safe_ip . '"></span>';
            $html .= '    <h3 class="location">' . $safe_location . '</h3>';
            $html .= '  </div>';
            $html .= '  <div class="card-body">';
            $html .= '    <p class="ip">' . $safe_ip . '</p>';
            $html .= '    <div class="stats">';
            $html .= '      <div class="stat">';
            $html .= '        <span class="label">RTT</span>';
            $html .= '        <span class="value" id="rtt-' . $safe_ip . '">...</span>';
            $html .= '      </div>';
            $html .= '      <div class="stat">';
            $html .= '        <span class="label">TTL</span>';
            $html .= '        <span class="value" id="ttl-' . $safe_ip . '">...</span>';
            $html .= '      </div>';
            $html .= '    </div>';
            $html .= '  </div>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    echo '<div class="main-container">';
    
    if (isset($ipsGateways) && !empty($ipsGateways)) {
        echo generateDeviceGrid($ipsGateways, 'GLOBAL GATEWAY');
    }
    
    if (isset($ipsLojas) && !empty($ipsLojas)) {
        echo generateDeviceGrid($ipsLojas, 'LOCAL STORES');
    }
    
    if (isset($ipsWifi) && !empty($ipsWifi)) {
        echo generateDeviceGrid($ipsWifi, 'GLOBAL WI-FI');
    }
    
    if (isset($ipsPC) && !empty($ipsPC)) {
        echo generateDeviceGrid($ipsPC, 'MAIN OFFICE & WAREHOUSE');
    }
    
    if (isset($ipsCCTV) && !empty($ipsCCTV)) {
        echo generateDeviceGrid($ipsCCTV, 'CCTV - STORES');
    }
    
    echo '</div>';
    ?>

    <script src="js/script.js"></script>
</body>
</html>
