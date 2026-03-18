const apiClient = {
    baseUrl: '../../backend/api/',

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

    /**
     * Check if user is authenticated - redirects to login if not
     * Used by protected pages on load
     */
    async checkAuthAndRedirect() {
        try {
            // Create abort controller with 5 second timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);

            const response = await fetch(this.baseUrl + 'auth.php?action=check_session', {
                method: 'GET',
                credentials: 'include',
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            const data = await response.json();
            
            if (!data.authenticated) {
                console.log('User not authenticated, redirecting to login');
                window.location.href = '/frontend/pages/auth.html?mode=login';
                return null;
            }
            
            return data;
        } catch (error) {
            console.error('Auth check error:', error);
            // Don't redirect on timeout - let the page load but check again after a delay
            if (error.name === 'AbortError') {
                console.warn('Auth check timed out, page may not be fully protected');
                return null;
            }
            window.location.href = '/frontend/pages/auth.html?mode=login';
            return null;
        }
    }
};

window.apiClient = apiClient;