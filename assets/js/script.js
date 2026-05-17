// Structure uniforme: helpers partagés + deux handlers encapsulés dans DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    // Helpers partagés
    const PASSWORD_REGEX = /^(?=.*[A-Z])(?=.*[0-9]).{8,}$/; // >=8 chars, 1 uppercase, 1 digit
    function isPasswordStrong(pw) {
        return PASSWORD_REGEX.test(pw);
    }
    function isEmailValid(value) {
        if (!value) return false;
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(value);
    }
    function setValid(el) {
        if (!el) return;
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
    }
    function setInvalid(el) {
        if (!el) return;
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
    }

    // Handler 1: signup form validation (si présent)
    (function signupHandler(){
        const pw = document.getElementById('signin-password');
        const pwConfirm = document.getElementById('signin-password-confirm');
        const email = document.getElementById('signin-email');
        if (!pw && !pwConfirm && !email) return; // pas de formulaire signup ici

        // Complexité du mot de passe
        pw?.addEventListener('input', function () {
            isPasswordStrong(pw.value) ? setValid(pw) : setInvalid(pw);
        });

        // Confirmation du mot de passe
        pwConfirm?.addEventListener('input', function () {
            pwConfirm.value === pw.value ? setValid(pwConfirm) : setInvalid(pwConfirm);
        });

        // Email simple (utilise le helper commun)
        email?.addEventListener('input', function () {
            isEmailValid(email.value) ? setValid(email) : setInvalid(email);
        });
    })();

    // Handler 2: profile form (détection de modification, validation mot de passe, affichage d'erreur inline)
    (function profileHandler(){
        const form = document.querySelector('form[enctype="multipart/form-data"]');
        if (!form) return;

        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const prenom = document.getElementById('prenom');
        const nom = document.getElementById('nom');
        const pseudo = document.getElementById('pseudo');
        const email = document.getElementById('email');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const imageInput = document.getElementById('image_profil');
        const clientErrorEl = document.getElementById('client-error');

        const initial = {
            prenom: prenom?.value || '',
            nom: nom?.value || '',
            pseudo: pseudo?.value || '',
            email: email?.value || ''
        };

        function checkChanged() {
            const changed = (
                (prenom?.value || '') !== initial.prenom ||
                (nom?.value || '') !== initial.nom ||
                (pseudo?.value || '') !== initial.pseudo ||
                (email?.value || '') !== initial.email ||
                (newPassword?.value || '') !== '' ||
                (confirmPassword?.value || '') !== '' ||
                (imageInput?.files.length || 0) > 0
            );
            if (submitBtn) submitBtn.disabled = !changed;
            if (cancelBtn) cancelBtn.disabled = !changed;
        }

        function showClientError(msg) {
            if (clientErrorEl) {
                clientErrorEl.textContent = msg;
                clientErrorEl.classList.remove('d-none');
                clientErrorEl.style.display = 'block';
                try { clientErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            } else {
                alert(msg);
            }
        }
        function clearClientError() {
            if (!clientErrorEl) return;
            clientErrorEl.textContent = '';
            clientErrorEl.classList.add('d-none');
            clientErrorEl.style.display = 'none';
        }

        function validatePasswords() {
            if (!newPassword || !confirmPassword) return true;
            const a = newPassword.value;
            const b = confirmPassword.value;
            if (a !== '' || b !== '') {
                if (!isPasswordStrong(a)) {
                    setInvalid(newPassword);
                    if (submitBtn) submitBtn.disabled = true;
                    return false;
                } else {
                    setValid(newPassword);
                }
                if (a !== b) {
                    setInvalid(confirmPassword);
                    if (submitBtn) submitBtn.disabled = true;
                    return false;
                } else {
                    setValid(confirmPassword);
                }
            }
            return true;
        }

        form.addEventListener('input', function () {
            clearClientError();
            checkChanged();
            validatePasswords();
            // vérifier email côté client et empêcher la soumission si invalide
            if (email) {
                if (!isEmailValid(email.value)) {
                    setInvalid(email);
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    setValid(email);
                }
            }
        });
        imageInput?.addEventListener('change', function () { checkChanged(); });

        form.addEventListener('submit', function (e) {
            clearClientError();
            if (email && !isEmailValid(email.value)) {
                e.preventDefault();
                showClientError("Le format de l'email est invalide.");
                return;
            }
            if (!validatePasswords()) {
                e.preventDefault();
                showClientError('Les mots de passe doivent correspondre et respecter la complexité (8 caractères, une majuscule, un chiffre).');
            }
        });

        checkChanged();
    })();
});