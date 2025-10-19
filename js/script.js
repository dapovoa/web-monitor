let isLoading = false;

function showLoading() {
    if (isLoading) return;
    isLoading = true;
}

function hideLoading() {
    if (!isLoading) return;
    isLoading = false;
}

function updateCards(data) {
    if (!data || data.length === 0) {
        console.log('No data received or empty array');
        return;
    }

    let onlineCount = 0;
    let totalCount = data.length;

    for (let item of data) {
        const cardElem = document.getElementById('card-' + item.ip);
        if (!cardElem) continue;

        const rttElem = document.getElementById('rtt-' + item.ip);
        const ttlElem = document.getElementById('ttl-' + item.ip);

        if (rttElem) rttElem.textContent = item.rtt !== null ? `${item.rtt} ms` : '...';
        if (ttlElem) ttlElem.textContent = item.ttl !== null ? item.ttl : '...';

        if (item.status) {
            cardElem.classList.remove('offline');
            cardElem.classList.add('online');
            onlineCount++;
        } else {
            cardElem.classList.remove('online');
            cardElem.classList.add('offline');
        }
    }

    console.log(`Status: ${onlineCount}/${totalCount} devices online`);
}

function fetchPingData() {
    // Prevent concurrent requests
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

            // Retry with longer interval on error
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
