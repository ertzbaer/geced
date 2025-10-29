/**
 * API Client
 * Handles all HTTP requests to the backend API
 */

const API_CONFIG = {
    baseURL: window.location.origin + '/api',
    timeout: 30000
};

class APIClient {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.timeout = API_CONFIG.timeout;
    }

    /**
     * Get authorization header
     */
    getAuthHeader() {
        const token = localStorage.getItem('accessToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    }

    /**
     * Make HTTP request
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;

        const defaultHeaders = {
            'Content-Type': 'application/json',
            ...this.getAuthHeader()
        };

        const config = {
            ...options,
            headers: {
                ...defaultHeaders,
                ...options.headers
            }
        };

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            if (!response.ok) {
                // Handle 401 - Unauthorized (Token expired)
                if (response.status === 401) {
                    // Try to refresh token
                    const refreshed = await this.refreshToken();
                    if (refreshed) {
                        // Retry original request
                        return this.request(endpoint, options);
                    } else {
                        // Redirect to login
                        window.location.href = '/public/signin.html';
                        throw new Error('Session expired. Please login again.');
                    }
                }

                throw new Error(data.error || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * PATCH request
     */
    async patch(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    /**
     * Refresh access token
     */
    async refreshToken() {
        try {
            const refreshToken = localStorage.getItem('refreshToken');
            if (!refreshToken) {
                return false;
            }

            const response = await fetch(`${this.baseURL}/auth/refresh`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refreshToken })
            });

            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('accessToken', data.accessToken);
                return true;
            }

            return false;
        } catch (error) {
            console.error('Token refresh failed:', error);
            return false;
        }
    }

    // ==================== Authentication ====================

    async login(email, password) {
        const data = await this.post('/auth/login', { email, password });
        if (data.success) {
            localStorage.setItem('accessToken', data.accessToken);
            localStorage.setItem('refreshToken', data.refreshToken);
            localStorage.setItem('user', JSON.stringify(data.user));
        }
        return data;
    }

    async logout() {
        const refreshToken = localStorage.getItem('refreshToken');
        try {
            await this.post('/auth/logout', { refreshToken });
        } finally {
            localStorage.removeItem('accessToken');
            localStorage.removeItem('refreshToken');
            localStorage.removeItem('user');
            window.location.href = '/public/signin.html';
        }
    }

    async checkAuth() {
        try {
            const data = await this.get('/auth/check');
            if (data.success) {
                localStorage.setItem('user', JSON.stringify(data.user));
                return data.user;
            }
            return null;
        } catch (error) {
            return null;
        }
    }

    async changePassword(currentPassword, newPassword) {
        return this.patch('/auth/change-password', { currentPassword, newPassword });
    }

    async forgotPassword(email) {
        return this.post('/auth/forgot-password', { email });
    }

    async resetPassword(token, password) {
        return this.post('/auth/reset-password', { token, password });
    }

    // ==================== Users ====================

    async getUsers() {
        return this.get('/users');
    }

    async getUser(id) {
        return this.get(`/users/${id}`);
    }

    async getCurrentUser() {
        return this.get('/users/me');
    }

    async createUser(userData) {
        return this.post('/users', userData);
    }

    async updateUser(id, userData) {
        return this.put(`/users/${id}`, userData);
    }

    async updateProfile(userData) {
        return this.patch('/users/me', userData);
    }

    async updatePreferences(preferences) {
        return this.patch('/users/me/preferences', preferences);
    }

    async deleteUser(id) {
        return this.delete(`/users/${id}`);
    }

    // ==================== Leads ====================

    async getLeads(filters = {}) {
        return this.get('/leads', filters);
    }

    async getLead(id) {
        return this.get(`/leads/${id}`);
    }

    async createLead(leadData) {
        return this.post('/leads', leadData);
    }

    async updateLead(id, leadData) {
        return this.put(`/leads/${id}`, leadData);
    }

    async deleteLead(id) {
        return this.delete(`/leads/${id}`);
    }

    async bulkDeleteLeads(ids) {
        return this.post('/leads/bulk-delete', { ids });
    }

    async bulkAssignLeads(ids, assignedTo) {
        return this.post('/leads/bulk-assign', { ids, assigned_to: assignedTo });
    }

    async bulkUpdateLeadStatus(ids, status) {
        return this.post('/leads/bulk-status', { ids, status });
    }

    async exportLeadsCSV() {
        const token = localStorage.getItem('accessToken');
        window.open(`${this.baseURL}/leads/export/csv?token=${token}`, '_blank');
    }

    // ==================== Campaigns ====================

    async getCampaigns(filters = {}) {
        return this.get('/campaigns', filters);
    }

    async getCampaign(id) {
        return this.get(`/campaigns/${id}`);
    }

    async createCampaign(campaignData) {
        return this.post('/campaigns', campaignData);
    }

    async updateCampaign(id, campaignData) {
        return this.put(`/campaigns/${id}`, campaignData);
    }

    async deleteCampaign(id) {
        return this.delete(`/campaigns/${id}`);
    }

    async assignLeadsToCampaign(campaignId, leadIds) {
        return this.post(`/campaigns/${campaignId}/assign-leads`, { lead_ids: leadIds });
    }

    async removeLeadFromCampaign(campaignId, leadId) {
        return this.delete(`/campaigns/${campaignId}/remove-lead/${leadId}`);
    }

    // ==================== Calls ====================

    async getCalls(filters = {}) {
        return this.get('/calls', filters);
    }

    async getCall(id) {
        return this.get(`/calls/${id}`);
    }

    async createCall(callData) {
        return this.post('/calls', callData);
    }

    async updateCall(id, callData) {
        return this.patch(`/calls/${id}`, callData);
    }

    async deleteCall(id) {
        return this.delete(`/calls/${id}`);
    }

    // ==================== Dashboard ====================

    async getDashboardStats() {
        return this.get('/dashboard/stats');
    }

    async getLeadStatusChart() {
        return this.get('/dashboard/charts/lead-status');
    }

    async getCampaignStatusChart() {
        return this.get('/dashboard/charts/campaign-status');
    }

    async getLeadTrendChart(days = 7) {
        return this.get('/dashboard/charts/lead-trend', { days });
    }

    async getPerformanceMetrics() {
        return this.get('/dashboard/performance');
    }

    async getRecentActivity(limit = 20) {
        return this.get('/dashboard/activity', { limit });
    }
}

// Global API instance
const api = new APIClient();
