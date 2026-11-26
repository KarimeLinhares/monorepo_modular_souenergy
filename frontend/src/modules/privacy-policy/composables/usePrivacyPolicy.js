import { ref } from 'vue';
import { privacyPolicyApi } from '../services/privacyPolicyApi';

export function usePrivacyPolicy() {
	const shouldShowModal = ref(false);
	const hasAccepted = ref(false);
	const policy = ref(null);
	const loadingStatus = ref(false);
	const accepting = ref(false);
	const error = ref('');

	async function checkPolicyStatus() {
		loadingStatus.value = true;
		error.value = '';
		try {
			const { data } = await privacyPolicyApi.getStatus();
			shouldShowModal.value = data.should_show;
			hasAccepted.value = data.has_accepted;
			policy.value = data.policy;
		} catch {
			error.value =
				'Não foi possível verificar o status da política de privacidade.';
		} finally {
			loadingStatus.value = false;
		}
	}

	async function acceptPolicy() {
		accepting.value = true;
		error.value = '';
		try {
			await privacyPolicyApi.accept();
			shouldShowModal.value = false;
			hasAccepted.value = true;
			return true;
		} catch {
			error.value = 'Erro ao registrar o aceite. Tente novamente.';
			return false;
		} finally {
			accepting.value = false;
		}
	}

	return {
		shouldShowModal,
		hasAccepted,
		policy,
		loadingStatus,
		accepting,
		error,
		checkPolicyStatus,
		acceptPolicy,
	};
}
