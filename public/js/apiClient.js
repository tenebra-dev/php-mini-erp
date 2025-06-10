class ApiClient {
    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl;
    }

    async request(endpoint, method = 'GET', data = null) {
        const url = `${this.baseUrl}${endpoint}`;
        const options = {
            method,
            headers: {
                'Accept': 'application/json'
            }
        };

        if (data && !(data instanceof FormData)) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        } else if (data instanceof FormData) {
            options.body = data;
            // Não define Content-Type, o browser faz isso
        }

        let response;
        try {
            response = await fetch(url, options);
        } catch (networkError) {
            // Erro de rede (ex: offline)
            throw { success: false, message: 'Erro de rede ou servidor inacessível', networkError };
        }

        const contentType = response.headers.get('content-type');
        let responseData = contentType && contentType.includes('application/json')
            ? await response.json()
            : await response.text();

        if (!response.ok) {
            // Erro HTTP: lança para ser tratado no .catch do front
            throw responseData && responseData.message
                ? responseData
                : { success: false, message: 'Erro desconhecido na API', status: response.status };
        }

        return responseData;
    }

    get(endpoint) {
        return this.request(endpoint, 'GET');
    }

    post(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    }

    put(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    }

    delete(endpoint, data) {
        return this.request(endpoint, 'DELETE', data);
    }
}

// Exemplo de uso global
window.apiClient = new ApiClient();