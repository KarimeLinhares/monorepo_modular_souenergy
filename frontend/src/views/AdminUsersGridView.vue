<template>
	<div class="page-shell">
		<header class="topbar">
			<div class="topbar-brand">
				<strong>Sou Energy</strong>
				<span>Painel Administrativo</span>
			</div>
			<div class="topbar-user">
				<span>{{ state.user?.name }}</span>
				<button type="button" class="btn btn-ghost" @click="handleLogout">
					Sair
				</button>
			</div>
		</header>

		<main class="container stack">
			<!-- Módulo de ativação/desativação e edição do texto -->
			<PrivacyPolicyAdminConfig @updated="fetchUsers" />

			<!-- Grid de Usuários com status de aceite -->
			<section class="card">
				<div class="grid-header">
					<div>
						<h2>Grid de Usuários — Aceite de Privacidade</h2>
						<p class="subtitle" v-if="currentPolicy">
							Política vigente: <strong>v{{ currentPolicy.version }}</strong>
						</p>
					</div>

					<div class="summary-pills">
						<span class="pill"
							>Total: <strong>{{ summary.total }}</strong></span
						>
						<span class="pill pill-success"
							>Aceitos: <strong>{{ summary.accepted }}</strong></span
						>
						<span class="pill pill-warning"
							>Pendentes: <strong>{{ summary.pending }}</strong></span
						>
					</div>
				</div>

				<div class="filters">
					<input
						v-model="search"
						type="search"
						placeholder="Buscar por nome ou e-mail..."
						@input="debouncedFetch" />
					<select v-model="statusFilter" @change="fetchUsers">
						<option value="">Todos os status</option>
						<option value="accepted">Aceito</option>
						<option value="pending">Pendente</option>
					</select>
				</div>

				<div v-if="loading" class="loading-box">Carregando usuários...</div>
				<p v-else-if="error" class="alert-error">{{ error }}</p>

				<div v-else class="table-wrapper">
					<table class="users-table">
						<thead>
							<tr>
								<th>Cliente</th>
								<th>E-mail</th>
								<th>Política de Privacidade</th>
								<th>Versão Aceita</th>
								<th>Data do Aceite</th>
							</tr>
						</thead>
						<tbody>
							<tr v-if="users.length === 0">
								<td colspan="5" class="empty-row">
									Nenhum usuário encontrado com os filtros aplicados.
								</td>
							</tr>
							<tr v-for="user in users" :key="user.id">
								<td class="cell-strong">{{ user.name }}</td>
								<td>{{ user.email }}</td>
								<td>
									<span
										class="badge"
										:class="user.accepted ? 'badge-success' : 'badge-warning'">
										{{ user.accepted ? 'Aceito' : 'Pendente' }}
									</span>
								</td>
								<td>
									{{ user.policy_version ? `v${user.policy_version}` : '—' }}
								</td>
								<td>{{ formatDate(user.accepted_at) }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</main>
	</div>
</template>

<script setup>
	import { ref, onMounted } from 'vue';
	import { useRouter } from 'vue-router';
	import { useAuth } from '../stores/auth';
	import { privacyPolicyApi } from '../modules/privacy-policy/services/privacyPolicyApi';
	import PrivacyPolicyAdminConfig from '../modules/privacy-policy/components/PrivacyPolicyAdminConfig.vue';

	const router = useRouter();
	const { state, logout } = useAuth();

	const users = ref([]);
	const currentPolicy = ref(null);
	const summary = ref({ total: 0, accepted: 0, pending: 0 });
	const statusFilter = ref('');
	const search = ref('');
	const loading = ref(false);
	const error = ref('');

	let searchTimeout = null;

	async function fetchUsers() {
		loading.value = true;
		error.value = '';
		try {
			const params = {};
			if (statusFilter.value) params.status = statusFilter.value;
			if (search.value.trim()) params.search = search.value.trim();

			const { data } = await privacyPolicyApi.getAdminUsers(params);
			users.value = data.data;
			currentPolicy.value = data.current_policy;
			summary.value = data.summary;
		} catch {
			error.value = 'Não foi possível carregar a lista de usuários.';
		} finally {
			loading.value = false;
		}
	}

	function debouncedFetch() {
		clearTimeout(searchTimeout);
		searchTimeout = setTimeout(fetchUsers, 300);
	}

	function formatDate(isoDate) {
		if (!isoDate) return '—';
		return new Intl.DateTimeFormat('pt-BR', {
			dateStyle: 'short',
			timeStyle: 'short',
		}).format(new Date(isoDate));
	}

	async function handleLogout() {
		await logout();
		router.push('/login');
	}

	onMounted(fetchUsers);
</script>

<style scoped>
	.stack {
		display: flex;
		flex-direction: column;
		gap: 1.5rem;
	}

	.grid-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 1rem;
		flex-wrap: wrap;
		margin-bottom: 1rem;
	}

	.grid-header h2 {
		margin: 0;
		font-size: 1.15rem;
	}

	.summary-pills {
		display: flex;
		gap: 0.5rem;
		flex-wrap: wrap;
	}

	.pill {
		font-size: 0.82rem;
		padding: 0.3rem 0.7rem;
		border-radius: 8px;
		background: #f1f5f9;
		color: #334155;
	}

	.pill-success {
		background: #dcfce7;
		color: #166534;
	}

	.pill-warning {
		background: #fef3c7;
		color: #92400e;
	}

	.filters {
		display: flex;
		gap: 0.75rem;
		margin-bottom: 1rem;
		flex-wrap: wrap;
	}

	.filters input {
		flex: 1;
		min-width: 220px;
	}

	.filters select {
		width: 200px;
	}

	.table-wrapper {
		overflow-x: auto;
	}

	.users-table {
		width: 100%;
		border-collapse: collapse;
		font-size: 0.9rem;
	}

	.users-table th,
	.users-table td {
		text-align: left;
		padding: 0.85rem 0.75rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.users-table th {
		font-size: 0.78rem;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #64748b;
		background: #f8fafc;
	}

	.cell-strong {
		font-weight: 600;
		color: #0f172a;
	}

	.empty-row {
		text-align: center;
		color: #64748b;
		padding: 2rem !important;
	}
</style>
