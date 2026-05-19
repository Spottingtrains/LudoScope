// Structure uniforme: helpers partagés + trois handlers encapsulés dans DOMContentLoaded
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
        if (!pw && !pwConfirm && !email) return;

        pw?.addEventListener('input', function () {
            isPasswordStrong(pw.value) ? setValid(pw) : setInvalid(pw);
        });

        pwConfirm?.addEventListener('input', function () {
            pwConfirm.value === pw.value ? setValid(pwConfirm) : setInvalid(pwConfirm);
        });

        email?.addEventListener('input', function () {
            isEmailValid(email.value) ? setValid(email) : setInvalid(email);
        });
    })();

    // Handler 4 : efface les brouillons d'ajout de jeu (clés commençant par 'jeu_add') lors de la déconnexion
    (function logoutClearHandler(){
        function clearJeuDraftKeys() {
            try {
                for (let i = localStorage.length - 1; i >= 0; i--) {
                    const key = localStorage.key(i);
                    if (!key) continue;
                    if (key.indexOf('jeu_add') === 0) localStorage.removeItem(key);
                }
            } catch (e) {
                // Ignorer les erreurs (ex. localStorage non accessible en navigation privée)
            }
        }

        // Attache le nettoyeur aux liens de déconnexion (tous les <a> dont l'attribut href contient 'logout')
        document.querySelectorAll('a[href*="logout"]').forEach(a => {
            a.addEventListener('click', clearJeuDraftKeys);
        });

        // Si la page a été chargée suite à une redirection de déconnexion (ex. ?logout=1), on efface immédiatement
        if (location.search && location.search.indexOf('logout') !== -1) {
            clearJeuDraftKeys();
        }
    })();

    // Handler 2: profile form (détection de modification, validation mot de passe, affichage d'erreur inline)
    (function profileHandler(){
        const form = document.getElementById('profile-form'); // ← corrigé
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

    // Handler 3: autosave du formulaire d'ajout de jeu (localStorage)
    (function jeuAutosaveHandler() {
        const form = document.getElementById('jeu-add-form'); // ← corrigé
        if (!form) return;

        const STORAGE_KEY = 'jeu_add_draft';
        let debounceTimer;

        function getFormData() {
            return {
                titre:            document.getElementById('titre')?.value || '',
                description:      document.getElementById('description')?.value || '',
                nom_editeur:      document.getElementById('nom_editeur')?.value || '',
                nom_auteur:       document.getElementById('nom_auteur')?.value || '',
                nom_illustrateur: document.getElementById('nom_illustrateur')?.value || '',
                annee_edition:    document.getElementById('annee_edition')?.value || '',
                duree_partie:     document.getElementById('duree_partie')?.value || '',
                age_min:          document.getElementById('age_min')?.value || '',
                complexite:       document.getElementById('complexite')?.value || '',
                nb_joueurs_min:   document.getElementById('nb_joueurs_min')?.value || '',
                nb_joueurs_max:   document.getElementById('nb_joueurs_max')?.value || '',
                categories: Array.from(
                    form.querySelectorAll('input[name="categories[]"]:checked')
                ).map(cb => cb.value)
            };
        }

        function saveDraft() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(getFormData()));
        }

        function restoreDraft() {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const data = JSON.parse(raw);

            ['titre','description','nom_editeur','nom_auteur','nom_illustrateur',
             'annee_edition','duree_partie','age_min','complexite','nb_joueurs_min','nb_joueurs_max'
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el && data[id]) el.value = data[id];
            });

            if (data.categories) {
                form.querySelectorAll('input[name="categories[]"]').forEach(cb => {
                    cb.checked = data.categories.includes(cb.value);
                });
            }
        }

        form.addEventListener('focusout', saveDraft);

        form.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(saveDraft, 3000);
        });

        form.addEventListener('submit', function () {
            localStorage.removeItem(STORAGE_KEY);
        });

        restoreDraft();
    })();
});