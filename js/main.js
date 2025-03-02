const logout = async () => {
	await fetch('{{ url(/auth/logout) }}', { method: 'POST' });
	window.location = '{{ url(/) }}';
}
