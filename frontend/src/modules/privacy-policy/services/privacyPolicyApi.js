import api from '../../../services/api';

export const privacyPolicyApi = {
	getStatus() {
		return api.get('/privacy-policy/status');
	},

	accept() {
		return api.post('/privacy-policy/accept');
	},

	getAdminConfig() {
		return api.get('/admin/privacy-policy');
	},

	updateAdminConfig(payload) {
		return api.put('/admin/privacy-policy', payload);
	},

	getAdminUsers(params = {}) {
		return api.get('/admin/users', { params });
	},
};
