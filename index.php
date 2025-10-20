<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!--
AUTOR      : Vítor Fernandes
DATA       : 11-MAR-2024
ATUALIZADO : 20-OUT-2025
-->
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <title>Web Dashboard</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="apple-touch-icon" href="img/favicon.png">
    <link rel="icon" href="img/32x32.png" sizes="32x32">
    <link rel="icon" href="img/192x192.png" sizes="192x192">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <?php
    include_once 'config.php';

    $icons = [
        'gateway' => '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'stores' => '<svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>',
        'wifi' => '<svg viewBox="0 0 24 24"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><circle cx="12" cy="20" r="1"/></svg>',
        'office' => '<svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'cctv' => '<svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>'
    ];

    function generateSummaryCard($title, $icon, $total, $category_key) {
        return [
            'title' => $title,
            'icon' => $icon,
            'total' => $total,
            'key' => $category_key
        ];
    }

    function generateDeviceGrid($ips, $category_name) {
        $html = '<div class="accordion-item" data-category="' . strtolower(str_replace(' ', '-', $category_name)) . '">';
        $html .= '  <div class="accordion-header" onclick="toggleAccordion(this)">';
        $html .= '    <div class="accordion-left">';
        $html .= '      <div class="accordion-icon">';
        $html .= '        <svg viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>';
        $html .= '      </div>';
        $html .= '      <h3 class="accordion-title">' . htmlspecialchars($category_name) . '</h3>';
        $html .= '    </div>';
        $html .= '    <div class="accordion-right">';
        $html .= '      <div class="accordion-badge">';
        $html .= '        <span class="status-indicator"></span>';
        $html .= '        <span class="badge-online" id="badge-online-' . strtolower(str_replace(' ', '-', $category_name)) . '">0</span>';
        $html .= '        <span class="badge-total">/ ' . count($ips) . ' online</span>';
        $html .= '      </div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '  <div class="accordion-content">';
        $html .= '    <div class="devices-grid">';

        foreach ($ips as $ip => $location) {
            $safe_ip = htmlspecialchars($ip);
            $safe_location = htmlspecialchars($location);

            $html .= '      <div class="device-card" id="card-' . $safe_ip . '">';
            $html .= '        <div class="device-header">';
            $html .= '          <div class="device-info">';
            $html .= '            <div class="device-name">' . $safe_location . '</div>';
            $html .= '            <div class="device-ip">' . $safe_ip . '</div>';
            $html .= '          </div>';
            $html .= '          <div class="device-status-badge">';
            $html .= '            <div class="device-dot"></div>';
            $html .= '            <span id="status-text-' . $safe_ip . '">...</span>';
            $html .= '          </div>';
            $html .= '        </div>';
            $html .= '        <div class="device-stats">';
            $html .= '          <div class="device-stat">';
            $html .= '            <span class="stat-label">RTT</span>';
            $html .= '            <span class="stat-value" id="rtt-' . $safe_ip . '">...</span>';
            $html .= '          </div>';
            $html .= '          <div class="device-stat">';
            $html .= '            <span class="stat-label">TTL</span>';
            $html .= '            <span class="stat-value" id="ttl-' . $safe_ip . '">...</span>';
            $html .= '          </div>';
            $html .= '          <div class="device-stat">';
            $html .= '            <span class="stat-label">Loss</span>';
            $html .= '            <span class="stat-value" id="loss-' . $safe_ip . '">...</span>';
            $html .= '          </div>';
            $html .= '        </div>';
            $html .= '        <div class="device-meta">';
            $html .= '          <span id="last-check-' . $safe_ip . '">Verificando...</span>';
            $html .= '          <span id="uptime-' . $safe_ip . '">...</span>';
            $html .= '        </div>';
            $html .= '      </div>';
        }

        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }

    $summaryData = [];
    if (isset($ipsGateways) && !empty($ipsGateways)) {
        $summaryData[] = generateSummaryCard('Gateway', $icons['gateway'], count($ipsGateways), 'gateways');
    }
    if (isset($ipsLojas) && !empty($ipsLojas)) {
        $summaryData[] = generateSummaryCard('Stores', $icons['stores'], count($ipsLojas), 'lojas');
    }
    if (isset($ipsWifi) && !empty($ipsWifi)) {
        $summaryData[] = generateSummaryCard('Wi-Fi', $icons['wifi'], count($ipsWifi), 'wifi');
    }
    if (isset($ipsPC) && !empty($ipsPC)) {
        $summaryData[] = generateSummaryCard('Office', $icons['office'], count($ipsPC), 'pc');
    }
    if (isset($ipsCCTV) && !empty($ipsCCTV)) {
        $summaryData[] = generateSummaryCard('CCTV', $icons['cctv'], count($ipsCCTV), 'cctv');
    }
    ?>

    <div class="dashboard-container">
        <div class="dashboard-summary">
            <?php foreach ($summaryData as $card): ?>
            <div class="summary-card status-full" id="summary-<?php echo $card['key']; ?>">
                <div class="summary-header">
                    <div class="summary-icon">
                        <?php echo $card['icon']; ?>
                    </div>
                    <div class="summary-location"><?php echo $card['title']; ?></div>
                </div>
                <div class="summary-main">
                    <div class="summary-count"><?php echo $card['total']; ?></div>
                    <div class="summary-label">dispositivos</div>
                </div>
                <div class="summary-status">
                    <span class="status-online" id="summary-online-<?php echo $card['key']; ?>">● 0 online</span>
                    <span class="status-offline" id="summary-offline-<?php echo $card['key']; ?>" style="display: none;">● 0 offline</span>
                </div>
                <div class="summary-uptime">
                    <div class="uptime-bar-bg">
                        <div class="uptime-bar-fill" id="uptime-bar-<?php echo $card['key']; ?>" style="width: 0%"></div>
                    </div>
                    <div class="uptime-text">
                        <span>Uptime</span>
                        <span id="uptime-percent-<?php echo $card['key']; ?>">0%</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="accordion-wrapper">
            <?php
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
            ?>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
