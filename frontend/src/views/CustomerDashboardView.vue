<template>
	<div class="page-shell">
		<header class="topbar">
			<div class="topbar-brand">
				<strong>Sou Energy</strong>
				<span>Portal do Cliente</span>
			</div>
			<div class="topbar-user">
				<span>{{ state.user?.name }} ({{ state.user?.email }})</span>
				<button type="button" class="btn btn-ghost" @click="handleLogout">
					Sair
				</button>
			</div>
		</header>

		<main class="container">
			<section class="card">
				<h1>Bem-vindo(a), {{ state.user?.name }}</h1>
				<p class="subtitle">
					Sua área de cliente está ativa. O sistema verifica automaticamente se
					há termos de privacidade pendentes de aceite após o login.
				</p>

				<div class="status-box">
					<span>Status da Política de Privacidade:</span>
					<span v-if="loadingStatus" class="badge badge-neutral"
						>Verificando...</span
					>
					<span v-else-if="hasAccepted" class="badge badge-success">
						Aceito{{ policy ? ` (v${policy.version})` : '' }}
					</span>
					<span v-else class="badge badge-warning">Pendente</span>
				</div>
			</section>
		</main>

		<PrivacyPolicyModal
			:visible="shouldShowModal"
			:policy="policy"
			:loading="accepting"
			:error="error"
			@accept="acceptPolicy"
			@logout="handleLogout" />
	</div>
</template>

<script setup>
	import { onMounted } from 'vue';
	import { useRouter } from 'vue-router';
	import { useAuth } from '../stores/auth';
	import { usePrivacyPolicy } from '../modules/privacy-policy/composables/usePrivacyPolicy';
	import PrivacyPolicyModal from '../modules/privacy-policy/components/PrivacyPolicyModal.vue';

	const router = useRouter();
	const { state, logout } = useAuth();
	const {
		shouldShowModal,
		hasAccepted,
		policy,
		loadingStatus,
		accepting,
		error,
		checkPolicyStatus,
		acceptPolicy,
	} = usePrivacyPolicy();

	async function handleLogout() {
		await logout();
		router.push('/login');
	}

	onMounted(checkPolicyStatus);
</script>

<style scoped>
	.status-box {
		margin-top: 1.25rem;
		display: flex;
		align-items: center;
		gap: 0.75rem;
		padding: 1rem;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
	}
</style>
