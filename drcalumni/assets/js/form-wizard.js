(function () {
    const root = document.querySelector('[data-wizard]');
    if (!root) return;

    const panels = Array.from(root.querySelectorAll('[data-step-panel]'));
    const stepPills = Array.from(root.querySelectorAll('[data-step-pill]'));
    const progress = root.querySelector('[data-progress]');
    const prevButton = root.querySelector('[data-step-prev]');
    const nextButton = root.querySelector('[data-step-next]');
    const submitButton = root.querySelector('[data-step-submit]');
    const currentInput = root.querySelector('input[name="current_step"]');
    const summary = root.querySelector('[data-summary]');
    let index = Number(root.dataset.initialStep || 0);
    root.setAttribute('novalidate', 'novalidate');

    const resolveBasePath = () => {
        const marker = '/drcalumni/';
        const pathname = window.location.pathname || '';
        const markerIndex = pathname.indexOf(marker);
        return markerIndex === -1 ? '/drcalumni' : pathname.slice(0, markerIndex + marker.length - 1);
    };

    let swalLoaderPromise = null;
    const ensureSwal = () => {
        if (window.Swal) return Promise.resolve(true);
        if (swalLoaderPromise) return swalLoaderPromise;

        const basePath = resolveBasePath();
        const candidates = [
            `${basePath}/sweet/sweetalert2.all.min.js`,
            '/sweet/sweetalert2.all.min.js',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11'
        ];

        swalLoaderPromise = new Promise((resolve) => {
            const loadNext = () => {
                if (window.Swal) {
                    resolve(true);
                    return;
                }
                if (candidates.length === 0) {
                    resolve(false);
                    return;
                }
                const src = candidates.shift();
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    if (window.Swal) {
                        resolve(true);
                        return;
                    }
                    loadNext();
                };
                script.onerror = loadNext;
                document.head.appendChild(script);
            };
            loadNext();
        });

        return swalLoaderPromise;
    };

    const showError = (message) => {
        ensureSwal().then((isReady) => {
            if (isReady && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formulaire incomplet',
                    text: message,
                    confirmButtonColor: '#002868'
                });
                return;
            }
            alert(message);
        });
    };

    const goToStep = (targetStepIndex) => {
        const bounded = Math.max(0, Math.min(panels.length - 1, targetStepIndex));
        index = bounded;
        render();
    };

    const countWords = (value) => (value.match(/[\p{L}\p{N}]+(?:[’'`-][\p{L}\p{N}]+)*/gu) || []).length;

    const updateConditionalFields = () => {
        const wantsWorkgroup = root.querySelector('input[name="wants_workgroup"]:checked')?.value === '1';
        const workgroupWrapper = root.querySelector('[data-if-workgroup]');
        if (workgroupWrapper) workgroupWrapper.style.display = wantsWorkgroup ? '' : 'none';

        const wantsExpo = root.querySelector('input[name="wants_expo"]:checked')?.value === '1';
        root.querySelectorAll('[data-if-expo]').forEach((element) => {
            element.style.display = wantsExpo ? '' : 'none';
        });

        const needsAccommodation = root.querySelector('input[name="needs_accommodation"]:checked')?.value === '1';
        const accommodationWrapper = root.querySelector('[data-if-accommodation]');
        if (accommodationWrapper) accommodationWrapper.style.display = needsAccommodation ? '' : 'none';

        const sectorOtherEnabled = Array.from(root.querySelectorAll('input[name="sectors[]"]:checked')).some((el) => el.value === 'other');
        const sectorOther = root.querySelector('[data-if-sector-other]');
        if (sectorOther) sectorOther.style.display = sectorOtherEnabled ? '' : 'none';

        const programsOtherEnabled = Array.from(root.querySelectorAll('input[name="programs[]"]:checked')).some((el) => el.value === 'other');
        const programsOther = root.querySelector('[data-if-program-other]');
        if (programsOther) programsOther.style.display = programsOtherEnabled ? '' : 'none';
    };

    const renderSummary = () => {
        if (!summary) return;
        const formData = new FormData(root);
        const entries = [];
        [
            ['full_name', 'Nom et prénom'],
            ['email', 'Adresse e-mail'],
            ['phone', 'Téléphone'],
            ['province', 'Province'],
            ['city_territory', 'Ville / territoire'],
            ['organization_name', 'Organisation'],
            ['latest_exchange_year', 'Année du programme'],
            ['availability', 'Disponibilité']
        ].forEach(([key, label]) => {
            const value = String(formData.get(key) || '').trim();
            if (value !== '') {
                entries.push(`<div class="summary-item"><strong>${label}</strong><br>${value}</div>`);
            }
        });
        summary.innerHTML = entries.join('');
    };

    const updateWordCounters = () => {
        root.querySelectorAll('[data-max-words]').forEach((field) => {
            const max = Number(field.getAttribute('data-max-words'));
            const counter = root.querySelector(`[data-counter-for="${field.name}"]`);
            if (!counter || Number.isNaN(max)) return;
            const words = (field.value.match(/[\p{L}\p{N}]+(?:[’'`-][\p{L}\p{N}]+)*/gu) || []).length;
            counter.textContent = `${words}/${max} mots`;
            counter.style.color = words > max ? '#b42318' : '#52627b';
        });
    };

    const render = () => {
        panels.forEach((panel, panelIndex) => panel.classList.toggle('active', panelIndex === index));
        stepPills.forEach((pill, pillIndex) => {
            pill.classList.toggle('active', pillIndex === index);
            pill.setAttribute('aria-current', pillIndex === index ? 'step' : 'false');
        });
        if (progress) progress.style.width = `${((index + 1) / panels.length) * 100}%`;
        if (prevButton) prevButton.disabled = index === 0;
        if (nextButton) nextButton.style.display = index >= panels.length - 1 ? 'none' : 'inline-flex';
        if (submitButton) submitButton.style.display = index >= panels.length - 1 ? 'inline-flex' : 'none';
        if (currentInput) currentInput.value = String(index + 1);
        updateConditionalFields();
        renderSummary();
        updateWordCounters();
    };

    prevButton?.addEventListener('click', () => {
        if (index > 0) {
            index -= 1;
            render();
        }
    });

    nextButton?.addEventListener('click', () => {
        if (index < panels.length - 1) {
            index += 1;
            render();
        }
    });

    stepPills.forEach((pill, pillIndex) => {
        pill.style.cursor = 'pointer';
        pill.setAttribute('role', 'button');
        pill.setAttribute('tabindex', '0');
        pill.addEventListener('click', () => {
            goToStep(pillIndex);
        });
        pill.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                goToStep(pillIndex);
            }
        });
    });

    root.querySelectorAll('input, select, textarea').forEach((element) => {
        element.addEventListener('change', () => {
            updateConditionalFields();
            updateWordCounters();
            renderSummary();
        });
        element.addEventListener('input', updateWordCounters);
    });

    root.addEventListener('submit', (event) => {
        const formData = new FormData(root);
        const requiredByStep = [
            { step: 0, key: 'full_name', label: 'Q1: Nom et prénom' },
            { step: 0, key: 'email', label: 'Q2: Adresse e-mail' },
            { step: 0, key: 'phone', label: 'Q3: Téléphone / WhatsApp' },
            { step: 0, key: 'province', label: 'Q4: Province' },
            { step: 0, key: 'city_territory', label: 'Ville ou territoire de résidence' },
            { step: 0, key: 'organization_name', label: 'Q5: Organisation' },
            { step: 1, key: 'responsibilities', label: 'Q7: Activités professionnelles' },
            { step: 2, key: 'latest_exchange_year', label: 'Q9: Année du programme le plus récent' },
            { step: 3, key: 'cooperation_domains', label: 'Q11: Domaines de coopération' },
            { step: 3, key: 'washington_accords_importance', label: 'Q12: Accords de Washington' },
            { step: 3, key: 'summit_expectations', label: 'Q13: Attentes' },
            { step: 3, key: 'sharable_expertise', label: 'Q14: Expertise à partager' },
            { step: 5, key: 'availability', label: 'Q18: Disponibilité' }
        ];

        for (const item of requiredByStep) {
            const value = String(formData.get(item.key) || '').trim();
            if (value === '') {
                event.preventDefault();
                goToStep(item.step);
                showError(`${item.label} est obligatoire.`);
                return;
            }
        }

        const email = String(formData.get('email') || '').trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            event.preventDefault();
            goToStep(0);
            showError('Q2: veuillez saisir une adresse e-mail valide.');
            return;
        }

        const year = Number(formData.get('latest_exchange_year') || 0);
        const currentYear = new Date().getFullYear();
        if (!Number.isFinite(year) || year < 1950 || year > currentYear) {
            event.preventDefault();
            goToStep(2);
            showError('Q9: veuillez saisir une année valide (non future).');
            return;
        }

        const programs = root.querySelectorAll('input[name="programs[]"]:checked').length;
        if (programs < 1) {
            event.preventDefault();
            goToStep(2);
            showError('Q8: sélectionnez au moins un programme.');
            return;
        }

        const sectorCount = root.querySelectorAll('input[name="sectors[]"]:checked').length;
        if (sectorCount < 2 || sectorCount > 3) {
            event.preventDefault();
            goToStep(1);
            showError('Q6: sélectionnez entre 2 et 3 secteurs.');
            return;
        }

        const sectorOtherEnabled = Array.from(root.querySelectorAll('input[name="sectors[]"]:checked')).some((el) => el.value === 'other');
        const sectorsOther = String(formData.get('sectors_other') || '').trim();
        if (sectorOtherEnabled && sectorsOther === '') {
            event.preventDefault();
            goToStep(1);
            showError('Q6: précisez le secteur “Autre”.');
            return;
        }

        const themeCount = root.querySelectorAll('input[name="summit_themes[]"]:checked').length;
        if (themeCount < 1 || themeCount > 3) {
            event.preventDefault();
            goToStep(3);
            showError('Q10: sélectionnez entre 1 et 3 thèmes.');
            return;
        }

        const wantsWorkgroup = root.querySelector('input[name="wants_workgroup"]:checked')?.value === '1';
        const workgroupChoice = String(formData.get('workgroup_choice') || '').trim();
        if (wantsWorkgroup && workgroupChoice === '') {
            event.preventDefault();
            goToStep(3);
            showError('Q16: sélectionnez un groupe de travail.');
            return;
        }

        const wantsExpo = root.querySelector('input[name="wants_expo"]:checked')?.value === '1';
        const expoEntity = String(formData.get('expo_entity_name') || '').trim();
        const expoDescription = String(formData.get('expo_description') || '').trim();
        if (wantsExpo && expoEntity === '') {
            event.preventDefault();
            goToStep(4);
            showError('Q17: indiquez le nom de votre initiative/organisation.');
            return;
        }
        if (wantsExpo && expoDescription === '') {
            event.preventDefault();
            goToStep(4);
            showError('Q17: décrivez brièvement votre initiative.');
            return;
        }

        const needsAccommodation = root.querySelector('input[name="needs_accommodation"]:checked')?.value === '1';
        const accommodationDetails = String(formData.get('accommodation_details') || '').trim();
        if (needsAccommodation && accommodationDetails === '') {
            event.preventDefault();
            goToStep(5);
            showError('Q19: veuillez préciser l’aménagement nécessaire.');
            return;
        }

        const wordLimitRules = [
            { key: 'responsibilities', max: 150, label: 'Q7' },
            { key: 'cooperation_domains', max: 80, label: 'Q11' },
            { key: 'washington_accords_importance', max: 150, label: 'Q12' },
            { key: 'summit_expectations', max: 150, label: 'Q13' },
            { key: 'sharable_expertise', max: 150, label: 'Q14' },
            { key: 'expo_description', max: 80, label: 'Q17', conditional: wantsExpo },
            { key: 'additional_comments', max: 80, label: 'Q20' }
        ];

        for (const rule of wordLimitRules) {
            if (rule.conditional === false) continue;
            const value = String(formData.get(rule.key) || '').trim();
            if (value !== '' && countWords(value) > rule.max) {
                event.preventDefault();
                const stepForRule = rule.label === 'Q17' ? 4 : (rule.label === 'Q20' ? 5 : (rule.label === 'Q7' ? 1 : 3));
                goToStep(stepForRule);
                showError(`${rule.label}: maximum ${rule.max} mots.`);
                return;
            }
        }

        const declarationTruth = root.querySelector('input[name="declaration_truth"]')?.checked;
        const declarationUnderstanding = root.querySelector('input[name="declaration_understanding"]')?.checked;
        if (!declarationTruth || !declarationUnderstanding) {
            event.preventDefault();
            goToStep(5);
            showError('Veuillez cocher les deux déclarations obligatoires.');
            return;
        }
        const submit = root.querySelector('[type="submit"]');
        if (submit) {
            submit.setAttribute('disabled', 'disabled');
            submit.textContent = 'Envoi en cours...';
        }
        event.preventDefault();
        ensureSwal().finally(() => {
            if (window.Swal) {
                Swal.fire({
                    title: 'Envoi en cours…',
                    text: 'Votre candidature est en cours de soumission.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                    showConfirmButton: false
                });
            }
            root.submit();
        });
    });

    render();
})();
