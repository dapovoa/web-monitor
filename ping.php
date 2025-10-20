<?php
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';

if (!empty($referer) && !str_contains($referer, $host)) {
    http_response_code(403);
    exit;
}

include 'config.php';

function getPingCommand($ip) {
    $os = strtoupper(PHP_OS);

    if (strpos($os, 'WIN') !== false) {
        return "C:\\Windows\\System32\\ping.exe -n 1 -w 1500 " . escapeshellarg($ip);
    } else {
        return "ping -c 1 -W 1.5 " . escapeshellarg($ip);
    }
}

function sanitizeIP($ip) {
    $ip = preg_replace('/[^0-9.]/', '', trim($ip));
    
    if (!preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $ip)) {
        return false;
    }
    
    $octets = explode('.', $ip);
    foreach ($octets as $octet) {
        $num = intval($octet);
        if ($num < 0 || $num > 255) {
            return false;
        }
    }
    
    return $ip;
}

function isValidConfigIP($ip, $all_configured_ips) {
    return array_key_exists($ip, $all_configured_ips);
}

function pingStatusSequential($ip) {
    $output = [];
    $returnCode = -1;
    $command = getPingCommand($ip);

    exec($command, $output, $returnCode);
    
    $rtt = "N/A";
    $ttl = "N/A";
    $output_string = implode("\n", $output);

    if (stripos($output_string, 'unreachable') !== false || 
        stripos($output_string, 'timed out') !== false ||
        stripos($output_string, 'could not find host') !== false) {
        return ["ip" => $ip, "rtt" => "N/A", "ttl" => "N/A", "status" => false];
    }
    
    if ($returnCode === 0) {
        $matches_rtt = [];
        $matches_ttl = [];
        
        if (preg_match('/time[=<](.+?)ms/i', $output_string, $matches_rtt)) {
            $rtt_value_str = str_replace(['<', '='], '', $matches_rtt[1]);
            $rtt_val = floatval($rtt_value_str);
            
            if ($rtt_val < 1 && $rtt_val > 0) {
                $rtt = "<1 ms";
            } else {
                $rtt = number_format($rtt_val, 0) . ' ms';
            }
        }
        
        if (preg_match('/TTL=(\d+)/i', $output_string, $matches_ttl)) {
            $ttl = $matches_ttl[1];
        }
        
        if ($rtt !== "N/A") {
            return ["ip" => $ip, "rtt" => $rtt, "ttl" => $ttl, "status" => true];
        }
    }
    
    return ["ip" => $ip, "rtt" => "N/A", "ttl" => "N/A", "status" => false];
}

function pingBatchParallel($ips_batch) {
    $processes = [];
    $results = [];

    foreach ($ips_batch as $ip) {
        $command = getPingCommand($ip);
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($command, $descriptors, $pipes);
        
        if (is_resource($process)) {
            fclose($pipes[0]);
            $processes[$ip] = [
                'process' => $process,
                'stdout' => $pipes[1],
                'stderr' => $pipes[2]
            ];
        }
    }
    
    foreach ($processes as $ip => $proc_data) {
        $output = stream_get_contents($proc_data['stdout']);
        $error = stream_get_contents($proc_data['stderr']);
        
        fclose($proc_data['stdout']);
        fclose($proc_data['stderr']);
        
        $return_code = proc_close($proc_data['process']);
        
        $rtt = "N/A";
        $ttl = "N/A";
        
        if (stripos($output, 'unreachable') !== false || 
            stripos($output, 'timed out') !== false ||
            stripos($output, 'could not find host') !== false) {
            $results[] = ["ip" => $ip, "rtt" => "N/A", "ttl" => "N/A", "status" => false];
            continue;
        }
        
        if ($return_code === 0) {
            $matches_rtt = [];
            $matches_ttl = [];
            
            if (preg_match('/time[=<](.+?)ms/i', $output, $matches_rtt)) {
                $rtt_value_str = str_replace(['<', '='], '', $matches_rtt[1]);
                $rtt_val = floatval($rtt_value_str);
                
                if ($rtt_val < 1 && $rtt_val > 0) {
                    $rtt = "<1 ms";
                } else {
                    $rtt = number_format($rtt_val, 0) . ' ms';
                }
            }
            
            if (preg_match('/TTL=(\d+)/i', $output, $matches_ttl)) {
                $ttl = $matches_ttl[1];
            }
            
            if ($rtt !== "N/A") {
                $results[] = ["ip" => $ip, "rtt" => $rtt, "ttl" => $ttl, "status" => true];
            } else {
                $results[] = ["ip" => $ip, "rtt" => "N/A", "ttl" => "N/A", "status" => false];
            }
        } else {
            $results[] = ["ip" => $ip, "rtt" => "N/A", "ttl" => "N/A", "status" => false];
        }
    }
    
    return $results;
}

$results = [];
$all_ips_to_ping = [];

if (isset($ipsGateways) && is_array($ipsGateways)) {
    $all_ips_to_ping = array_merge($all_ips_to_ping, $ipsGateways);
}

if (isset($ipsLojas) && is_array($ipsLojas)) {
    $all_ips_to_ping = array_merge($all_ips_to_ping, $ipsLojas);
}

if (isset($ipsWifi) && is_array($ipsWifi)) {
    $all_ips_to_ping = array_merge($all_ips_to_ping, $ipsWifi);
}

if (isset($ipsPC) && is_array($ipsPC)) {
    $all_ips_to_ping = array_merge($all_ips_to_ping, $ipsPC);
}

if (isset($ipsCCTV) && is_array($ipsCCTV)) {
    $all_ips_to_ping = array_merge($all_ips_to_ping, $ipsCCTV);
}

if (empty($all_ips_to_ping)) {
    echo json_encode([]);
    exit;
}

$ips_only = array_keys($all_ips_to_ping);
$safe_ips = [];

foreach ($ips_only as $ip) {
    $sanitized_ip = sanitizeIP($ip);
    
    if ($sanitized_ip && 
        isValidConfigIP($ip, $all_ips_to_ping) && 
        filter_var($sanitized_ip, FILTER_VALIDATE_IP)) {
        $safe_ips[] = $sanitized_ip;
    }
}

if (empty($safe_ips)) {
    echo json_encode([]);
    exit;
}

try {
    if (function_exists('proc_open')) {
        $batches = array_chunk($safe_ips, 6);

        foreach ($batches as $batch) {
            $batch_results = pingBatchParallel($batch);
            $results = array_merge($results, $batch_results);
        }
    } else {
        foreach ($safe_ips as $ip) {
            $results[] = pingStatusSequential($ip);
        }
    }
} catch (Exception $e) {
    foreach ($safe_ips as $ip) {
        $results[] = pingStatusSequential($ip);
    }
}

echo json_encode($results);
?>
