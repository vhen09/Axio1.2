const apiClient = {
    baseUrl: 'http://localhost:8000/backend/api/',

    async post(endpoint, data) {
        const response = await fetch(this.baseUrl + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        return await this.handleResponse(response);
    },

    async get(endpoint) {
        const response = await fetch(this.baseUrl + endpoint);
        return await this.handleResponse(response);
    },

    async handleResponse(response) {
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'An error occurred');
        }
        return await response.json();
    },

    async submitProof(data) {
        return await this.post('submissions.php', data);
    },

    async getScores(userId) {
        return await this.get(`scores.php?user_id=${userId}`);
    },

    async authenticate(credentials) {
        return await this.post('auth.php', credentials);
    },
};

export default apiClient;