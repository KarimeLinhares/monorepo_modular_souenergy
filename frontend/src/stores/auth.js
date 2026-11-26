import { reactive, computed } from 'vue';
import api from '../services/api';

const savedUser = localStorage.getItem('auth_user');

const state = reactive({
	token: localStorage.getItem('auth_token') || null,
	user: savedUser ? JSON.parse(savedUser) : null,
});

export function useAuth() {
	const isAuthenticated = computed(() => Boolean(state.token && state.user));
	const isAdmin = computed(() => state.user?.role === 'admin');

	async function login(email, password) {
		const { data } = await api.post('/login', { email, password });
		state.token = data.token;
		state.user = data.user;
		localStorage.setItem('auth_token', data.token);
		localStorage.setItem('auth_user', JSON.stringify(data.user));
		return data;
	}

	async function logout() {
		try {
			await api.post('/logout');
		} catch {
			// Ignora erro caso o token já tenha expirado
		} finally {
			state.token = null;
			state.user = null;
			localStorage.removeItem('auth_token');
			localStorage.removeItem('auth_user');
		}
	}

	return {
		state,
		isAuthenticated,
		isAdmin,
		login,
		logout,
	};
}
