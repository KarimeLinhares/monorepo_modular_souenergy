<template>
	<Teleport to="body">
		<div
			v-if="visible && policy"
			class="modal-backdrop"
			role="dialog"
			aria-modal="true"
			aria-labelledby="policy-modal-title">
			<div class="modal-card">
				<header class="modal-header">
					<span class="version-pill">Versão {{ policy.version }}</span>
					<h2 id="policy-modal-title">{{ policy.title }}</h2>
				</header>

				<div class="modal-body">
					<p class="policy-text">{{ policy.content }}</p>
				</div>

				<p v-if="error" class="alert-error">{{ error }}</p>

				<footer class="modal-footer">
					<button
						type="button"
						class="btn btn-ghost"
						:disabled="loading"
						@click="$emit('logout')">
						Sair da conta
					</button>
					<button
						type="button"
						class="btn btn-primary"
						:disabled="loading"
						@click="$emit('accept')">
						{{ loading ? 'Registrando aceite...' : 'Li e aceito os termos' }}
					</button>
				</footer>
			</div>
		</div>
	</Teleport>
</template>

<script setup>
	defineProps({
		visible: { type: Boolean, required: true },
		policy: { type: Object, default: null },
		loading: { type: Boolean, default: false },
		error: { type: String, default: '' },
	});

	defineEmits(['accept', 'logout']);
</script>

<style scoped>
	.modal-backdrop {
		position: fixed;
		inset: 0;
		background: rgba(15, 23, 42, 0.72);
		backdrop-filter: blur(3px);
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1rem;
		z-index: 1000;
	}

	.modal-card {
		background: #ffffff;
		width: 100%;
		max-width: 580px;
		border-radius: 12px;
		box-shadow: 0 24px 48px rgba(15, 23, 42, 0.28);
		display: flex;
		flex-direction: column;
		max-height: 85vh;
		overflow: hidden;
	}

	.modal-header {
		padding: 1.5rem 1.5rem 1rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.modal-header h2 {
		margin: 0.5rem 0 0;
		font-size: 1.25rem;
		color: #0f172a;
	}

	.version-pill {
		display: inline-block;
		font-size: 0.75rem;
		font-weight: 600;
		padding: 0.2rem 0.6rem;
		border-radius: 999px;
		background: #eff6ff;
		color: #1d4ed8;
	}

	.modal-body {
		padding: 1.25rem 1.5rem;
		overflow-y: auto;
		flex: 1;
	}

	.policy-text {
		margin: 0;
		white-space: pre-line;
		line-height: 1.65;
		color: #334155;
		font-size: 0.95rem;
	}

	.modal-footer {
		padding: 1rem 1.5rem;
		border-top: 1px solid #e2e8f0;
		display: flex;
		justify-content: flex-end;
		gap: 0.75rem;
		background: #f8fafc;
	}
</style>
