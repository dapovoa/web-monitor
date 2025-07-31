<?php
// Headers para evitar cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!--
AUTOR      : Vítor Fernandes
DATA       : 11-MAR-2024
ATUALIZADO : 28-JUL-2025
-->
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <title>Verificação de IPs</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="apple-touch-icon" href="img/favicon.png">
    <link rel="icon" href="img/favicon.png" sizes="32x32">
    <link rel="icon" href="img/favicon.png" sizes="192x192">
    
    <!-- Estilos -->
    <link rel="stylesheet" type="text/css" href="css/estilo.css">
</head>

<body>
    <?php
    include_once 'config.php';

    /**
     * Gera uma tabela HTML para uma categoria de IPs
     * @param array $ips - Array associativo [ip => localização]
     * @param string $type - Nome da categoria (GATEWAY, PC LOJA, etc.)
     * @return string - HTML da tabela completa
     */
    function generateTable($ips, $type) {
        // Cabeçalho da tabela
        $html = '<div class="table-wrapper">
                    <table>
                        <tr>
                            <th colspan="6">' . htmlspecialchars($type) . '</th>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <th>IP</th>
                            <th>LOCATION</th>
                            <th>CONNECTION</th>
                            <th>TTL</th>
                            <th>RTT (ms)</th>
                        </tr>';
        
        $id = 1;
        
        // Gerar linhas da tabela
        foreach ($ips as $ip => $location) {
            $safe_ip = htmlspecialchars($ip);
            $safe_location = htmlspecialchars($location);
            
            $html .= '<tr class="dot">
                        <td>' . $id . '</td>
                        <td>' . $safe_ip . '</td>
                        <td>' . $safe_location . '</td>
                        <td class="dot" id="status-' . $safe_ip . '">...</td>
                        <td class="dot" id="ttl-' . $safe_ip . '">...</td>
                        <td class="dot" id="rtt-' . $safe_ip . '">...</td>
                      </tr>';
            $id++;
        }
        
        $html .= '    </table>
                 </div>';
        
        return $html;
    }

    // Gerar todas as tabelas
    echo '<div class="table-container">';
    
    if (isset($ipsGateways) && !empty($ipsGateways)) {
        echo generateTable($ipsGateways, 'GATEWAY');
    }
    
    if (isset($ipsLojas) && !empty($ipsLojas)) {
        echo generateTable($ipsLojas, 'PC LOJA');
    }
    
    if (isset($ipsWifi) && !empty($ipsWifi)) {
        echo generateTable($ipsWifi, 'WIFI LOJA');
    }
    
    if (isset($ipsPC) && !empty($ipsPC)) {
        echo generateTable($ipsPC, 'PC SEDE & ARMAZEM');
    }
    
    if (isset($ipsCCTV) && !empty($ipsCCTV)) {
        echo generateTable($ipsCCTV, 'CCTV LOJA');
    }
    
    echo '</div>';
    ?>

    <!-- JavaScript para atualizações automáticas -->
    <script src="js/script.js"></script>
</body>
</html>