<template>
	<section class="card">
		<div class="card-header">
			<div>
				<h2>Configuração do Módulo de Notificação</h2>
				<p class="subtitle">
					Ative ou desative o modal e gerencie o conteúdo da política vigente.
				</p>
			</div>

			<label class="toggle-wrapper" :class="{ disabled: savingToggle }">
				<input
					type="checkbox"
					:checked="form.is_enabled"
					:disabled="savingToggle || loading"
					@change="handleToggle" />
				<span class="toggle-slider"></span>
				<span class="toggle-label">
					{{ form.is_enabled ? 'Notificação Ativa' : 'Notificação Desativada' }}
				</span>
			</label>
		</div>

		<div v-if="loading" class="loading-box">Carregando configurações...</div>

		<form v-else class="config-form" @submit.prevent="handleSave">
			<div class="form-row">
				<div class="form-group">
					<label for="policy-title">Título do Modal</label>
					<input id="policy-title" v-model="form.title" type="text" required />
				</div>

				<div class="form-group version-field">
					<label for="policy-version">Versão Vigente</label>
					<input
						id="policy-version"
						v-model="form.version"
						type="text"
						required
						placeholder="Ex: 1.0" />
					<small class="hint"
						>Alterar a versão gera um novo termo e exige novo aceite dos
						clientes.</small
					>
				</div>
			</div>

			<div class="form-group">
				<label for="policy-content"
					>Texto da Notificação (Termos de Privacidade)</label
				>
				<textarea
					id="policy-content"
					v-model="form.content"
					rows="6"
					required></textarea>
			</div>

			<p v-if="feedback" class="alert-success">{{ feedback }}</p>
			<p v-if="error" class="alert-error">{{ error }}</p>

			<div class="form-actions">
				<button type="submit" class="btn btn-primary" :disabled="saving">
					{{ saving ? 'Salvando...' : 'Salvar Termos da Política' }}
				</button>
			</div>
		</form>
	</section>
</template>

<script setup>
	import { reactive, ref, onMounted } from 'vue';
	import { privacyPolicyApi } from '../services/privacyPolicyApi';

	const emit = defineEmits(['updated']);

	const loading = ref(true);
	const saving = ref(false);
	const savingToggle = ref(false);
	const feedback = ref('');
	const error = ref('');

	const form = reactive({
		version: '1.0',
		title: '',
		content: '',
		is_enabled: true,
	});

	async function loadConfig() {
		loading.value = true;
		error.value = '';
		try {
			const { data } = await privacyPolicyApi.getAdminConfig();
			Object.assign(form, {
				version: data.data.version,
				title: data.data.title,
				content: data.data.content,
				is_enabled: Boolean(data.data.is_enabled),
			});
		} catch {
			error.value = 'Não foi possível carregar a configuração da política.';
		} finally {
			loading.value = false;
		}
	}

	async function handleToggle(event) {
		const newValue = event.target.checked;
		savingToggle.value = true;
		feedback.value = '';
		error.value = '';
		try {
			const { data } = await privacyPolicyApi.updateAdminConfig({
				is_enabled: newValue,
			});
			form.is_enabled = Boolean(data.data.is_enabled);
			feedback.value = form.is_enabled
				? 'Exibição do modal ativada com sucesso.'
				: 'Exibição do modal desativada com sucesso.';
			emit('updated', data.data);
		} catch {
			error.value = 'Erro ao atualizar o status de exibição.';
		} finally {
			savingToggle.value = false;
		}
	}

	async function handleSave() {
		saving.value = true;
		feedback.value = '';
		error.value = '';
		try {
			const { data } = await privacyPolicyApi.updateAdminConfig({
				version: form.version.trim(),
				title: form.title.trim(),
				content: form.content.trim(),
				is_enabled: form.is_enabled,
			});
			feedback.value = 'Termos da política salvos com sucesso.';
			emit('updated', data.data);
		} catch (err) {
			error.value = err.response?.data?.message || 'Erro ao salvar os termos.';
		} finally {
			saving.value = false;
		}
	}

	onMounted(loadConfig);
</script>

<style scoped>
	.card-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 1rem;
		flex-wrap: wrap;
		margin-bottom: 1.25rem;
	}

	.card-header h2 {
		margin: 0;
		font-size: 1.15rem;
	}

	.subtitle {
		margin: 0.25rem 0 0;
		color: #64748b;
		font-size: 0.9rem;
	}

	.toggle-wrapper {
		display: inline-flex;
		align-items: center;
		gap: 0.65rem;
		cursor: pointer;
		user-select: none;
		background: #f8fafc;
		padding: 0.5rem 0.9rem;
		border: 1px solid #e2e8f0;
		border-radius: 999px;
	}

	.toggle-wrapper input {
		display: none;
	}

	.toggle-slider {
		position: relative;
		width: 42px;
		height: 24px;
		background: #cbd5e1;
		border-radius: 999px;
		transition: background 0.2s;
	}

	.toggle-slider::after {
		content: '';
		position: absolute;
		top: 3px;
		left: 3px;
		width: 18px;
		height: 18px;
		background: #fff;
		border-radius: 50%;
		transition: transform 0.2s;
	}

	.toggle-wrapper input:checked + .toggle-slider {
		background: #16a34a;
	}

	.toggle-wrapper input:checked + .toggle-slider::after {
		transform: translateX(18px);
	}

	.toggle-label {
		font-size: 0.875rem;
		font-weight: 600;
		color: #0f172a;
	}

	.form-row {
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 1rem;
	}

	@media (max-width: 680px) {
		.form-row {
			grid-template-columns: 1fr;
		}
	}

	.hint {
		display: block;
		margin-top: 0.25rem;
		color: #64748b;
		font-size: 0.78rem;
	}

	.form-actions {
		display: flex;
		justify-content: flex-end;
		margin-top: 0.5rem;
	}
</style>
