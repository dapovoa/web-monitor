# Web Monitor

![Web Monitor Dashboard](img/screenshot.png)

A real-time network monitoring dashboard. It pings a list of configured devices and displays their status in a clean web interface.

---

## What it Does

This tool monitors network devices and displays their connectivity status at a glance. It indicates whether a device is online or offline and shows its latency (RTT) and TTL values.

The main goal is to replace the tedious process of manually pinging multiple devices with an always-updated overview that refreshes every 5 seconds.

---

## How it Works

The architecture is straightforward:

```
Browser
    index.php renders the interface
    script.js polls ping.php every 5 seconds
        ping.php validates IPs against an allowlist
        ping.php executes parallel pings
        ping.php returns JSON with results
    script.js updates the DOM
```

### Execution Flow

The backend works on both Windows and Linux with automatic OS detection. It detects the operating system via `PHP_OS` and uses the appropriate ping command. Runs pings in parallel batches of 6 IPs using `proc_open()` for efficiency. This prevents a few slow devices from blocking the update cycle. If `proc_open()` isn't available, it gracefully falls back to running pings sequentially with `exec()`.

The script parses the ping output with regex to extract RTT and TTL, then returns a structured JSON to the frontend. The JavaScript then updates the status indicators in real-time.

### Security

For security, the script will only ever ping IPs that are explicitly defined in `config.php`. This allowlist approach prevents the tool from being used to ping arbitrary addresses on the network. It also includes input validation and referer checks as extra layers of protection.

---

## Project Structure

```
web-monitor/
    index.php              Renders the UI
    ping.php               Backend API
    config.php             IP lists and allowlist

    js/
        script.js          Polling and DOM updates

    css/
        estilo.css         Neumorphic styling

    img/
        favicon.ico
        favicon.png
        32x32.png
        192x192.png
        screenshot.png
```

**index.php** - The entry point. It loads the configuration and renders the initial device cards.

**ping.php** - Executes pings in parallel, parses the results, and returns them as JSON.

**config.php** - Where you define your devices, organized by category (e.g., Gateways, Store PCs, WiFi, Workstations, CCTV).

**script.js** - Polls the backend every 5 seconds and updates the status, RTT, and TTL in the interface.

**estilo.css** - The stylesheet for the neumorphic design, including the color-coded status indicators.

---

## Setup

### Requirements

- **Windows or Linux OS** (automatic detection)
- PHP 7.0+ with `proc_open()` and `exec()` enabled
- A web server (like Apache, Nginx, or IIS)

### Installation

Clone the repository:
```bash
git clone https://github.com/dapovoa/web-monitor.git
```

Then, edit `config.php` with your devices:
```php
$ipsGateways = [
    '192.168.1.1' => 'Main Gateway',
    '192.168.1.254' => 'Backup Gateway',
];

$ipsLojas = [
    '192.168.10.1' => 'Store Lisbon',
    '192.168.10.2' => 'Store Porto',
];
```
Finally, deploy the files to your web server and access `index.php` in a browser.

---

## Customization

Change the polling interval in `script.js`:
```javascript
setTimeout(fetchPingData, 5000);  // 5000ms = 5 seconds
```

Ping timeout is configured in `ping.php` via `getPingCommand()`:
```php
// Windows: -w 1500 (1500ms timeout)
// Linux: -W 1.5 (1.5s timeout)
```

OS detection is automatic, no manual configuration needed.

---

## Performance

The parallel execution handles approximately 99 devices (tested across 17 batches) with a 5-second update interval. For significantly larger networks, consider increasing the batch size or implementing a caching layer.

---

## License

MIT License - see the [LICENSE](LICENSE) file for details.
