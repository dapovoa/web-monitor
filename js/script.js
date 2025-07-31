/**
 * Sistema de monitorização de IPs via ping
 * Atualização automática a cada 5 segundos
 */

let isLoading = false;

/**
 * Ativa o estado de loading
 */
function showLoading() {
    if (isLoading) return;
    isLoading = true;
}

/**
 * Desativa o estado de loading
 */
function hideLoading() {
    if (!isLoading) return;
    isLoading = false;
}

/**
 * Atualiza as tabelas com os dados recebidos do ping
 * @param {Array} data - Array com resultados dos pings
 */
function updateTable(data) {
    // Se não há dados, não fazer nada
    if (!data || data.length === 0) {
        console.log('Nenhum dado recebido ou array vazio');
        return;
    }

    let onlineCount = 0;
    let totalCount = data.length;

    // Processar cada resultado de ping
    for (let item of data) {
        // Encontrar elementos DOM pelos IDs
        let rttElem = document.getElementById('rtt-' + item.ip);
        let ttlElem = document.getElementById('ttl-' + item.ip);
        let statusElem = document.getElementById('status-' + item.ip);

        // Atualizar RTT se elemento existe
        if (rttElem) {
            rttElem.textContent = item.rtt;
        }

        // Atualizar TTL se elemento existe
        if (ttlElem) {
            ttlElem.textContent = item.ttl;
        }

        // Atualizar status e classes CSS
        if (statusElem) {
            if (item.status) {
                // IP online
                statusElem.classList.remove('offline');
                statusElem.classList.add('online');
                statusElem.textContent = 'Online';
                onlineCount++;
            } else {
                // IP offline
                statusElem.classList.remove('online');
                statusElem.classList.add('offline');
                statusElem.textContent = 'Offline';
            }
        }
    }

    // Debug: mostrar estatísticas no console
    console.log(`Status: ${onlineCount}/${totalCount} dispositivos online`);
}

/**
 * Busca dados de ping do servidor
 * Implementa retry automático em caso de erro
 */
function fetchPingData() {
    // Evitar múltiplas chamadas simultâneas
    if (isLoading) return;
    
    showLoading();

    fetch('ping.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            updateTable(data);
            hideLoading();
            
            // Reagendar próxima atualização (5 segundos)
            setTimeout(fetchPingData, 5000);
        })
        .catch(error => {
            console.error('Erro ao obter dados de ping:', error);
            hideLoading();
            
            // Retry mais longo em caso de erro (10 segundos)
            setTimeout(fetchPingData, 10000);
        });
}

/**
 * Mostra mensagem de erro (função mantida mas não utilizada)
 * @param {string} message - Mensagem a exibir
 */
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
    
    // Auto-hide após 5 segundos
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Iniciar monitorização quando página carregar
window.onload = fetchPingData;