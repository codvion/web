document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('[data-profile-image-input]');
    const preview = document.querySelector('[data-profile-preview]');
    const fallback = document.querySelector('[data-profile-fallback]');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files && input.files[0] ? input.files[0] : null;

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            input.value = '';
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');

        if (fallback) {
            fallback.classList.add('hidden');
        }
    });
});
