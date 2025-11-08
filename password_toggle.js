document.addEventListener('DOMContentLoaded', function() {
    /**
     * Toggles the visibility of a password input field and updates the associated icon.
     * @param {string} toggleId The ID of the password toggle icon element.
     * @param {string} passwordInputId The ID of the password input field.
     */
    function togglePasswordVisibility(toggleId, passwordInputId) {
        const toggleIcon = document.getElementById(toggleId);
        const passwordInput = document.getElementById(passwordInputId);

        if (toggleIcon && passwordInput) {
            toggleIcon.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the eye icon emoji
                toggleIcon.innerHTML = (type === 'password') ? '&#x1F441;' : '&#x1F576;'; // Eye / Dark Sunglasses emojis
            });
        }
    }

    togglePasswordVisibility('toggleLoginPassword', 'login-password');
    togglePasswordVisibility('toggleRegPassword', 'reg-password');
    togglePasswordVisibility('toggleRegConfirmPassword', 'reg-confirm-password');
});