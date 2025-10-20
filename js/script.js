let isLoading = false;

const categoryMap = {
    'GLOBAL GATEWAY': 'gateways',
    'LOCAL STORES': 'lojas',
    'GLOBAL WI-FI': 'wifi',
    'MAIN OFFICE & WAREHOUSE': 'pc',
    'CCTV - STORES': 'cctv'
};

function toggleAccordion(header) {
    const item = header.parentElement;
    const wasExpanded = item.classList.contains('expanded');

    document.querySelectorAll('.accordion-item').forEach(i => {
        i.classList.remove('expanded');
    });

    if (!wasExpanded) {
        item.classList.add('expanded');
    }
}

function showLoading() {
    if (isLoading) return;
    isLoading = true;
}

function hideLoading() {
    if (!isLoading) return;
    isLoading = false;
}

function updateSummaryCards(categoryStats) {
    for (const [categoryName, stats] of Object.entries(categoryStats)) {
        const categoryKey = categoryMap[categoryName];
        if (!categoryKey) continue;

        const summaryCard = document.getElementById(`summary-${categoryKey}`);
        if (!summaryCard) continue;

        const onlineSpan = document.getElementById(`summary-online-${categoryKey}`);
        const offlineSpan = document.getElementById(`summary-offline-${categoryKey}`);
        const uptimeBar = document.getElementById(`uptime-bar-${categoryKey}`);
        const uptimePercent = document.getElementById(`uptime-percent-${categoryKey}`);

        if (onlineSpan) onlineSpan.textContent = `● ${stats.online} online`;

        if (offlineSpan) {
            if (stats.offline > 0) {
                offlineSpan.style.display = 'inline';
                offlineSpan.textContent = `● ${stats.offline} offline`;
            } else {
                offlineSpan.style.display = 'none';
            }
        }

        const uptimePercentValue = Math.round((stats.online / stats.total) * 100);
        if (uptimeBar) uptimeBar.style.width = `${uptimePercentValue}%`;
        if (uptimePercent) uptimePercent.textContent = `${uptimePercentValue}%`;

        summaryCard.classList.remove('status-full', 'status-partial', 'status-down');
        if (stats.online === stats.total) {
            summaryCard.classList.add('status-full');
        } else if (stats.online === 0) {
            summaryCard.classList.add('status-down');
        } else {
            summaryCard.classList.add('status-partial');
        }

        const categorySlug = categoryName.toLowerCase().replace(/\s+/g, '-');
        const badgeOnline = document.getElementById(`badge-online-${categorySlug}`);
        if (badgeOnline) {
            badgeOnline.textContent = stats.online;
        }
    }
}

function updateCards(data) {
    if (!data || data.length === 0) {
        console.log('No data received or empty array');
        return;
    }

    const categoryStats = {};

    for (let item of data) {
        const cardElem = document.getElementById('card-' + item.ip);
        if (!cardElem) continue;

        const accordionItem = cardElem.closest('.accordion-item');
        const categoryName = accordionItem ? accordionItem.querySelector('.accordion-title')?.textContent : null;

        if (categoryName && !categoryStats[categoryName]) {
            const totalDevices = accordionItem.querySelectorAll('.device-card').length;
            categoryStats[categoryName] = {
                total: totalDevices,
                online: 0,
                offline: 0
            };
        }

        const statusTextElem = document.getElementById('status-text-' + item.ip);
        const rttElem = document.getElementById('rtt-' + item.ip);
        const ttlElem = document.getElementById('ttl-' + item.ip);
        const lossElem = document.getElementById('loss-' + item.ip);
        const lastCheckElem = document.getElementById('last-check-' + item.ip);

        if (rttElem) rttElem.textContent = item.rtt !== null ? `${item.rtt} ms` : '---';
        if (ttlElem) ttlElem.textContent = item.ttl !== null ? item.ttl : '---';
        if (lossElem) lossElem.textContent = item.loss !== null ? `${item.loss}%` : '0%';
        if (lastCheckElem) lastCheckElem.textContent = 'Última verificação: agora';

        if (item.status) {
            cardElem.classList.remove('offline');
            cardElem.classList.add('online');
            if (statusTextElem) statusTextElem.textContent = 'Online';
            if (categoryName) categoryStats[categoryName].online++;
        } else {
            cardElem.classList.remove('online');
            cardElem.classList.add('offline');
            if (statusTextElem) statusTextElem.textContent = 'Offline';
            if (categoryName) categoryStats[categoryName].offline++;
        }
    }

    updateSummaryCards(categoryStats);

    const onlineCount = Object.values(categoryStats).reduce((sum, cat) => sum + cat.online, 0);
    const totalCount = data.length;
    console.log(`Status: ${onlineCount}/${totalCount} devices online`);
}

function fetchPingData() {

    if (isLoading) return;

    showLoading();

    fetch('ping.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Response error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            updateCards(data);
            hideLoading();

            setTimeout(fetchPingData, 5000);
        })
        .catch(error => {
            console.error('Error fetching ping data:', error);
            hideLoading();

            setTimeout(fetchPingData, 10000);
        });
}

function showErrorMessage(message) {
    let errorDiv = document.getElementById('error-message');

    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'error-message';
        errorDiv.className = 'error-message';
        document.body.appendChild(errorDiv);
    }

    errorDiv.textContent = message;
    errorDiv.style.display = 'block';

    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

window.onload = fetchPingData;
