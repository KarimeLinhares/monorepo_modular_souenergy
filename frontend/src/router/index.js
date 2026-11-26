import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../stores/auth';
import LoginView from '../views/LoginView.vue';
import CustomerDashboardView from '../views/CustomerDashboardView.vue';
import AdminUsersGridView from '../views/AdminUsersGridView.vue';

const routes = [
	{ path: '/', redirect: '/login' },
	{
		path: '/login',
		name: 'login',
		component: LoginView,
		meta: { guestOnly: true },
	},
	{
		path: '/dashboard',
		name: 'dashboard',
		component: CustomerDashboardView,
		meta: { requiresAuth: true },
	},
	{
		path: '/admin',
		name: 'admin',
		component: AdminUsersGridView,
		meta: { requiresAuth: true, requiresAdmin: true },
	},
];

const router = createRouter({
	history: createWebHistory(),
	routes,
});

router.beforeEach((to) => {
	const { isAuthenticated, isAdmin } = useAuth();

	if (to.meta.requiresAuth && !isAuthenticated.value) {
		return { name: 'login' };
	}

	if (to.meta.requiresAdmin && !isAdmin.value) {
		return { name: 'dashboard' };
	}

	if (to.meta.guestOnly && isAuthenticated.value) {
		return { name: isAdmin.value ? 'admin' : 'dashboard' };
	}
});

export default router;
