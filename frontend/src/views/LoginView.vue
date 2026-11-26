<template>
	<main class="login-page">
		<section class="login-card">
			<header class="login-header">
				<span class="brand-badge">Sou Energy • Mercado Eletrônico</span>
				<h1>Acesso ao Sistema</h1>
				<p>
					Entre com seu perfil para testar o módulo de política de privacidade.
				</p>
			</header>

			<form @submit.prevent="handleSubmit">
				<div class="form-group">
					<label for="email">E-mail</label>
					<input
						id="email"
						v-model="email"
						type="email"
						required
						placeholder="seu@email.com" />
				</div>

				<div class="form-group">
					<label for="password">Senha</label>
					<input
						id="password"
						v-model="password"
						type="password"
						required
						placeholder="••••••••" />
				</div>

				<p v-if="error" class="alert-error">{{ error }}</p>

				<button
					type="submit"
					class="btn btn-primary full-width"
					:disabled="loading">
					{{ loading ? 'Entrando...' : 'Entrar' }}
				</button>
			</form>

			<div class="quick-accounts">
				<span>Acesso rápido para demonstração:</span>
				<div class="quick-buttons">
					<button
						type="button"
						class="btn-chip"
						@click="fillCredentials('admin@souenergy.com.br', 'admin123')">
						Admin
					</button>
					<button
						type="button"
						class="btn-chip"
						@click="fillCredentials('carla@cliente.com', 'cliente123')">
						Cliente (Pendente)
					</button>
					<button
						type="button"
						class="btn-chip"
						@click="fillCredentials('ana@cliente.com', 'cliente123')">
						Cliente (Aceito)
					</button>
				</div>
			</div>
		</section>
	</main>
</template>

<script setup>
	import { ref } from 'vue';
	import { useRouter } from 'vue-router';
	import { useAuth } from '../stores/auth';

	const router = useRouter();
	const { login } = useAuth();

	const email = ref('');
	const password = ref('');
	const loading = ref(false);
	const error = ref('');

	function fillCredentials(userEmail, userPassword) {
		email.value = userEmail;
		password.value = userPassword;
		error.value = '';
	}

	async function handleSubmit() {
		loading.value = true;
		error.value = '';
		try {
			const data = await login(email.value, password.value);
			router.push(data.user.role === 'admin' ? '/admin' : '/dashboard');
		} catch (err) {
			error.value = err.response?.data?.message || 'Credenciais inválidas.';
		} finally {
			loading.value = false;
		}
	}
</script>

<style scoped>
	.login-page {
		min-height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1.5rem;
	}

	.login-card {
		width: 100%;
		max-width: 420px;
		background: #ffffff;
		padding: 2rem;
		border-radius: 14px;
		box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
		border: 1px solid #e2e8f0;
	}

	.login-header {
		margin-bottom: 1.5rem;
	}

	.brand-badge {
		display: inline-block;
		font-size: 0.75rem;
		font-weight: 700;
		color: #b45309;
		background: #fef3c7;
		padding: 0.25rem 0.65rem;
		border-radius: 999px;
	}

	.login-header h1 {
		margin: 0.75rem 0 0.25rem;
		font-size: 1.5rem;
	}

	.login-header p {
		margin: 0;
		color: #64748b;
		font-size: 0.9rem;
	}

	.full-width {
		width: 100%;
		margin-top: 0.5rem;
	}

	.quick-accounts {
		margin-top: 1.5rem;
		padding-top: 1.25rem;
		border-top: 1px solid #e2e8f0;
	}

	.quick-accounts span {
		display: block;
		font-size: 0.8rem;
		color: #64748b;
		margin-bottom: 0.6rem;
	}

	.quick-buttons {
		display: flex;
		gap: 0.5rem;
		flex-wrap: wrap;
	}

	.btn-chip {
		border: 1px solid #cbd5e1;
		background: #f8fafc;
		color: #334155;
		font-size: 0.8rem;
		font-weight: 600;
		padding: 0.35rem 0.7rem;
		border-radius: 8px;
		cursor: pointer;
	}

	.btn-chip:hover {
		background: #e2e8f0;
	}
</style>
