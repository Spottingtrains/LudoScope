if (document.getElementById('signin-password')) {
    // Validation du formulaire d'inscription
    // Vérifie que les mots de passe correspondent
    const password = document.getElementById('signin-password');
    const confirmPassword = document.getElementById('signin-password-confirm');

    confirmPassword.addEventListener('input', function () {
        if (password.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            confirmPassword.classList.remove('is-valid');
        } else {
            confirmPassword.classList.remove('is-invalid');
            confirmPassword.classList.add('is-valid');
        }
    });
    // validation de l'email : format valide
    const email = document.getElementById('signin-email');
    email.addEventListener('input', function () {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regex.test(email.value)) {
            email.classList.add('is-invalid');
            email.classList.remove('is-valid');
        } else {
            email.classList.remove('is-invalid');
            email.classList.add('is-valid');
        }
    });
    // Validation du mot de passe : au moins 8 caractères, une majuscule et un chiffre
    password.addEventListener('input', function () {
        const regex = /^(?=.*[A-Z])(?=.*[0-9]).{8,}$/;
        if (!regex.test(password.value)) {
            password.classList.add('is-invalid');
            password.classList.remove('is-valid');
        } else {
            password.classList.remove('is-invalid');
            password.classList.add('is-valid');
        }
    });
}