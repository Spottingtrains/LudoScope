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

    // Handler 2b: aperçu de l'image du formulaire d'ajout de jeu
    (function jeuAddImagePreviewHandler(){
        const input = document.getElementById('image');
        if (!input) return;

        const previewWrap = document.createElement('div');
        previewWrap.id = 'image-preview-wrap';
        previewWrap.className = 'mt-3 d-none';

        const previewTitle = document.createElement('p');
        previewTitle.className = 'mb-2';
        previewTitle.textContent = 'Aperçu de la nouvelle image';

        const preview = document.createElement('img');
        preview.id = 'image-preview';
        preview.className = 'img-forms';
        preview.alt = 'Aperçu de l\'image';

        previewWrap.appendChild(previewTitle);
        previewWrap.appendChild(preview);
        input.insertAdjacentElement('afterend', previewWrap);

        let currentUrl = null;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (currentUrl) {
                URL.revokeObjectURL(currentUrl);
                currentUrl = null;
            }

            if (!file) {
                preview.src = '';
                previewWrap.classList.add('d-none');
                return;
            }

            currentUrl = URL.createObjectURL(file);
            preview.src = currentUrl;
            previewWrap.classList.remove('d-none');
        });
    })();

    // Handler 6: admin deletion via Bootstrap modal (forms marked .admin-delete-form)
    (function adminDeleteHandler(){
        const modalEl = document.getElementById('adminConfirmModal');
        if (!modalEl) return;
        const bsModal = new bootstrap.Modal(modalEl);
        const modalBody = modalEl.querySelector('#adminConfirmModalBody');
        const confirmBtn = modalEl.querySelector('#adminConfirmModalConfirm');
        let pendingForm = null;

        document.querySelectorAll('form.admin-delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                pendingForm = form;
                const itemType = form.dataset.itemType || 'élément';
                const idInput = form.querySelector('input[name="id"]');
                const id = idInput ? idInput.value : '';
                modalBody.textContent = `Êtes-vous sûr de vouloir supprimer ce ${itemType} ${id ? '(ID ' + id + ')' : ''} ? Cette action est définitive.`;
                bsModal.show();
            });
        });

        confirmBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            // submit the stored form
            pendingForm.submit();
            pendingForm = null;
            bsModal.hide();
        });
    })();

    // Handler 5: AJAX search for catalogue + filtres
    (function catalogueSearchHandler(){
        const input = document.getElementById('catalog-search');
        const searchButton = document.getElementById('catalog-search-btn');
        const list = document.getElementById('catalogue-list');
        const applyFiltersButton = document.getElementById('apply-filters-btn');
        const resetFiltersButton = document.getElementById('reset-filters-btn');
        const playersMin = document.getElementById('filter-players-min');
        const playersMax = document.getElementById('filter-players-max');
        const duration = document.getElementById('filter-duration');
        const complexity = document.getElementById('filter-complexity');
        const categoryCheckboxes = Array.from(document.querySelectorAll('.filter-category'));
        if (!input || !list) return;

        const initialHTML = list.innerHTML;
        let timer = null;

        function escapeHtml(str){
            return String(str || '').replace(/[&<>"']/g, function(m){
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        }

        function getSelectedCategories() {
            return categoryCheckboxes.filter(cb => cb.checked).map(cb => cb.value.toLowerCase());
        }

        function durationMatches(value, selected) {
            if (!selected) return true;
            if (!Number.isFinite(value)) return false;
            if (selected === 'quick') return value < 30;
            if (selected === 'medium') return value >= 30 && value <= 60;
            if (selected === 'long') return value > 60;
            return true;
        }

        function cardMatches(cardEl) {
            if (!cardEl) return false;

            const minChoice = playersMin && playersMin.value !== '' ? parseInt(playersMin.value, 10) : null;
            const maxChoice = playersMax && playersMax.value !== '' ? parseInt(playersMax.value, 10) : null;
            const selectedDuration = duration ? duration.value : '';
            const selectedComplexity = complexity ? complexity.value.toLowerCase() : '';
            const selectedCategories = getSelectedCategories();

            const cardMin = parseInt(cardEl.dataset.playersMin || '', 10);
            const cardMax = parseInt(cardEl.dataset.playersMax || '', 10);
            const cardDuration = parseInt(cardEl.dataset.duration || '', 10);
            const cardComplexity = (cardEl.dataset.complexity || '').toLowerCase();
            const cardCategories = (cardEl.dataset.categories || '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean);

            if (minChoice !== null && Number.isFinite(cardMax) && cardMax < minChoice) return false;
            if (maxChoice !== null && Number.isFinite(cardMin) && cardMin > maxChoice) return false;
            if (!durationMatches(cardDuration, selectedDuration)) return false;
            if (selectedComplexity && cardComplexity !== selectedComplexity) return false;
            if (selectedCategories.length > 0 && !selectedCategories.some(cat => cardCategories.includes(cat))) return false;

            return true;
        }

        function applyFilters() {
            const cards = Array.from(list.querySelectorAll('.col'));
            let visibleCount = 0;

            cards.forEach(col => {
                const cardEl = col.querySelector('.custom-card');
                const visible = cardMatches(cardEl);
                col.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            let empty = list.querySelector('[data-filter-empty]');
            if (visibleCount === 0) {
                if (!empty) {
                    empty = document.createElement('p');
                    empty.setAttribute('data-filter-empty', 'true');
                    empty.className = 'w-100';
                    empty.textContent = 'Aucun jeu ne correspond à ces filtres.';
                    list.appendChild(empty);
                }
            } else if (empty) {
                empty.remove();
            }
        }

        function render(items){
            if (!items || items.length === 0) {
                list.innerHTML = '<p>Aucun jeu trouvé.</p>';
                return;
            }
            const html = items.map(j => `
                <div class="col">
                    <a href="index.php?url=jeu&slug=${encodeURIComponent(j.slug)}">
                        <div class="custom-card"
                            data-titre="${escapeHtml((j.titre || '').toLowerCase())}"
                            data-description="${escapeHtml((j.description || '').toLowerCase())}"
                            data-players-min="${escapeHtml(j.nb_joueurs_min || '')}"
                            data-players-max="${escapeHtml(j.nb_joueurs_max || '')}"
                            data-duration="${escapeHtml(j.duree_partie || '')}"
                            data-complexity="${escapeHtml((j.complexite || '').toLowerCase())}"
                            data-categories="${escapeHtml((j.categories || '').toLowerCase())}">
                            <img src="/uploads/${escapeHtml(j.image || 'default.jpg')}" alt="${escapeHtml(j.titre)}">
                            <div class="card-body">
                                <div>
                                    <h3>${escapeHtml(j.titre)}</h3>
                                    <span>${j.note_moyenne ? j.note_moyenne + '/10' : 'Non noté'}</span>
                                </div>
                                <p>${escapeHtml(j.complexite || '')} • ${escapeHtml(j.duree_partie || '')} min</p>
                                <p>${escapeHtml(j.nb_joueurs_min || '')}–${escapeHtml(j.nb_joueurs_max || '')} joueurs${j.age_min ? ` • ${escapeHtml(j.age_min)} ans+` : ''}</p>
                                <div class="btn-container">
                                    <a href="index.php?url=jeu&slug=${encodeURIComponent(j.slug)}" class="btn btn-primary">Lire les avis</a>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            `).join('');
            list.innerHTML = html;
            applyFilters();
        }

        function refreshCatalog() {
            clearTimeout(timer);
            const q = input.value.trim();
            timer = setTimeout(function(){
                if (q === '') {
                    list.innerHTML = initialHTML;
                    applyFilters();
                    return;
                }
                fetch('index.php?url=jeu/search&q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(render)
                    .catch(err => console.error('Search error', err));
            }, 300);
        }

        input.addEventListener('input', refreshCatalog);
        searchButton?.addEventListener('click', refreshCatalog);
        applyFiltersButton?.addEventListener('click', applyFilters);
        resetFiltersButton?.addEventListener('click', function () {
            if (playersMin) playersMin.value = '';
            if (playersMax) playersMax.value = '';
            if (duration) duration.value = '';
            if (complexity) complexity.value = '';
            categoryCheckboxes.forEach(cb => {
                cb.checked = false;
            });
            applyFilters();
        });

        applyFilters();
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
    // Handler 2: profile form (détection de modification, validation mot de passe, affichage d'erreur inline)
(function profileHandler(){
    const form = document.getElementById('profile-form');
    if (!form) return;

    const submitBtn       = document.getElementById('submitBtn');
    const cancelBtn       = document.getElementById('cancelBtn');
    const prenom          = document.getElementById('prenom');
    const nom             = document.getElementById('nom');
    const pseudo          = document.getElementById('pseudo');
    const email           = document.getElementById('email');
    const newPassword     = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const imageInput      = document.getElementById('image_profil');
    const questionSecrete = document.getElementById('question_secrete');
    const reponseSecrete  = document.getElementById('reponse_secrete');
    const clientErrorEl   = document.getElementById('client-error');

    const initial = {
        prenom:   prenom?.value   || '',
        nom:      nom?.value      || '',
        pseudo:   pseudo?.value   || '',
        email:    email?.value    || '',
        question: questionSecrete?.value || '',
        reponse:  reponseSecrete?.value || '',
    };

    function checkChanged() {
        const changed = (
            (prenom?.value          || '') !== initial.prenom   ||
            (nom?.value             || '') !== initial.nom      ||
            (pseudo?.value          || '') !== initial.pseudo   ||
            (email?.value           || '') !== initial.email    ||
            (questionSecrete?.value || '') !== initial.question ||
            (reponseSecrete?.value  || '') !== initial.reponse  ||
            (newPassword?.value     || '') !== ''               ||
            (confirmPassword?.value || '') !== ''               ||
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
    questionSecrete?.addEventListener('change', function () { checkChanged(); });

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

    // Handler 2b: édition des avis depuis le profil
    (function profileReviewsHandler(){
        const reviewCards = document.querySelectorAll('.review-card');
        if (!reviewCards.length) return;

        reviewCards.forEach(card => {
            const editBtn = card.querySelector('.btn-edit-review');
            const saveBtn = card.querySelector('.btn-save-review');
            const cancelBtn = card.querySelector('.btn-cancel-edit');
            const textarea = card.querySelector('.review-textarea');
            const noteSelect = card.querySelector('.review-note-select');

            if (!editBtn || !saveBtn || !cancelBtn || !textarea || !noteSelect) return;

            editBtn.addEventListener('click', function () {
                textarea.disabled = false;
                noteSelect.disabled = false;
                editBtn.classList.add('d-none');
                saveBtn.classList.remove('d-none');
                cancelBtn.classList.remove('d-none');
                textarea.focus();
            });

            cancelBtn.addEventListener('click', function () {
                textarea.disabled = true;
                noteSelect.disabled = true;
                editBtn.classList.remove('d-none');
                saveBtn.classList.add('d-none');
                cancelBtn.classList.add('d-none');
            });
        });
    })();

    // Handler 3: autosave du formulaire d'ajout de jeu (localStorage)
    (function jeuAutosaveHandler() {
        const form = document.getElementById('jeu-add-form'); // ← corrigé
        if (!form) return;

        const STORAGE_KEY = 'jeu_add_draft';
        const cancelBtn = document.getElementById('jeu-add-cancel-btn');
        let debounceTimer;
        let isCancelling = false;

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
            if (isCancelling) return;
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

        cancelBtn?.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            isCancelling = true;
            clearTimeout(debounceTimer);
            localStorage.removeItem(STORAGE_KEY);
            history.back();
        });

        restoreDraft();
    })();

    // Handler admin users: recherche, filtres, tri (client-side)
    (function adminUsersHandler(){
        const table = document.querySelector('#user-controls + .table-wrapper .table');
        const tbody = table ? table.querySelector('tbody') : null;
        if (!tbody) return;
        let rows = Array.from(tbody.querySelectorAll('tr'));

        const searchEl = document.getElementById('user-search');
        const roleEl = document.getElementById('filter-role');
        const lastLoginEl = document.getElementById('filter-lastlogin');
        const registeredEl = document.getElementById('filter-registered');

        const normalize = s => String(s || '').toLowerCase().trim();

        function parseDateOnly(v){
            if (!v) return null;
            const d = new Date(v);
            if (isNaN(d)) {
                const y = String(v).substr(0,10);
                const dd = new Date(y);
                return isNaN(dd) ? null : dd;
            }
            return d;
        }

        function matchesRow(row, q, role, lastSince, regSince){
            const searchable = normalize(row.dataset.search || '');
            const id = String(row.dataset.id || '');

            if (q){
                const qn = q.toLowerCase();
                if (!(searchable.includes(qn) || id.includes(qn))) return false;
            }
            if (role && String(row.dataset.role || '') !== String(role)) return false;
            if (lastSince){
                const d = parseDateOnly(row.dataset.lastlogin || row.dataset.lastLogin || '');
                if (!d) return false;
                if (d < lastSince) return false;
            }
            if (regSince){
                const d2 = parseDateOnly(row.dataset.registered || row.dataset.dateInscription || '');
                if (!d2) return false;
                if (d2 < regSince) return false;
            }
            return true;
        }

        function applyFilters(sortField = 'id', sortDir = 'desc'){
            const q = normalize(searchEl?.value || '');
            const role = roleEl?.value || '';
                const lastSince = lastLoginEl?.value ? parseDateOnly(lastLoginEl.value) : null;
                const regSince = registeredEl?.value ? parseDateOnly(registeredEl.value) : null;

                let visible = rows.filter(r => matchesRow(r, q, role, lastSince, regSince));

            visible.sort((a,b) => {
                let av = a.dataset[sortField] || '';
                let bv = b.dataset[sortField] || '';
                if (sortField === 'id'){ av = parseInt(av)||0; bv = parseInt(bv)||0; }
                if (av < bv) return sortDir === 'asc' ? -1 : 1;
                if (av > bv) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });

            tbody.innerHTML = '';
            visible.forEach(r => tbody.appendChild(r));
        }

        searchEl?.addEventListener('input', () => applyFilters('id','desc'));
        roleEl?.addEventListener('change', () => applyFilters('id','desc'));
        // status filter removed
        lastLoginEl?.addEventListener('change', () => applyFilters('id','desc'));
        registeredEl?.addEventListener('change', () => applyFilters('id','desc'));

        // enable header sorting by clicking th[data-sort]
        document.querySelectorAll('.table thead th[data-sort]').forEach(th=>{
            th.style.cursor = 'pointer';
            th.dataset.dir = th.dataset.dir || 'desc';
            th.addEventListener('click', () => {
                const key = th.dataset.sort || 'id';
                const dir = th.dataset.dir === 'asc' ? 'desc' : 'asc';
                document.querySelectorAll('.table thead th[data-sort]').forEach(h=>h.dataset.dir='');
                th.dataset.dir = dir;
                applyFilters(key, dir);
            });
        });

        // wire delete buttons to show confirmation modal (reuse adminDeleteHandler modal if exists)
        document.querySelectorAll('.admin-delete-btn').forEach(btn=>{
            btn.addEventListener('click', function () {
                const form = btn.closest('form.admin-delete-form');
                if (!form) return;
                if (typeof bootstrap !== 'undefined' && document.getElementById('adminConfirmModal')) {
                    form.submit();
                } else {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) form.submit();
                }
            }, {passive:true});
        });

        // initial
        applyFilters('id','desc');
    })();

    // Handler: afficher/masquer la réponse secrète
    (function toggleReponseSecreteHandler(){
        const btn = document.getElementById('toggle-reponse');
        const input = document.getElementById('reponse_secrete');
        if (!btn || !input) return;

        btn.addEventListener('click', function () {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? 'Masquer' : 'Afficher';
        });
    })();
});