document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    const navToggle = document.querySelector('[data-nav-toggle]');
    const navLinks = document.querySelector('[data-nav-links]');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    const learnproMenuToggle = document.querySelector('[data-learnpro-menu-toggle]');
    const learnproHeader = document.querySelector('.learnpro-header');

    if (learnproMenuToggle && learnproHeader) {
        learnproMenuToggle.addEventListener('click', () => {
            const isOpen = learnproHeader.classList.toggle('menu-open');
            learnproMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    const seedLoginButtons = document.querySelectorAll('[data-seed-login]');
    const identifierInput = document.querySelector('#identifier');
    const passwordInput = document.querySelector('#password');

    seedLoginButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (identifierInput) {
                identifierInput.value = button.dataset.identifier || '';
                identifierInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (passwordInput) {
                passwordInput.value = button.dataset.password || '';
                passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
                passwordInput.focus();
            }
        });
    });

    const questionSetBuilder = document.querySelector('[data-question-set-builder]');

    if (questionSetBuilder) {
        const maxQuestions = Math.max(parseInt(questionSetBuilder.dataset.maxQuestions || '10', 10), 1);
        const questionCards = Array.from(questionSetBuilder.querySelectorAll('[data-question-card]'));
        const addQuestionButton = questionSetBuilder.querySelector('[data-add-question-row]');
        const questionCount = questionSetBuilder.querySelector('[data-question-count]');

        const visibleQuestionCards = () => questionCards.filter((card) => !card.classList.contains('is-hidden'));

        const clearQuestionCard = (card) => {
            card.querySelectorAll('textarea, input').forEach((input) => {
                if (input.name === 'batch_marks[]') {
                    input.value = '1';
                    return;
                }
                input.value = '';
            });

            card.querySelectorAll('select').forEach((select) => {
                select.value = 'A';
            });
        };

        const updateQuestionBuilder = () => {
            const activeCount = visibleQuestionCards().length;

            if (questionCount) {
                questionCount.textContent = String(activeCount);
            }

            if (addQuestionButton) {
                addQuestionButton.disabled = activeCount >= maxQuestions;
                addQuestionButton.classList.toggle('disabled', activeCount >= maxQuestions);
            }
        };

        if (addQuestionButton) {
            addQuestionButton.addEventListener('click', () => {
                const nextCard = questionCards.find((card) => card.classList.contains('is-hidden'));

                if (!nextCard) {
                    return;
                }

                nextCard.classList.remove('is-hidden');
                updateQuestionBuilder();

                const questionTextarea = nextCard.querySelector('textarea');
                if (questionTextarea) {
                    questionTextarea.focus();
                }
            });
        }

        questionCards.forEach((card) => {
            const removeButton = card.querySelector('[data-remove-question-row]');

            if (!removeButton) {
                return;
            }

            removeButton.addEventListener('click', () => {
                clearQuestionCard(card);

                if (visibleQuestionCards().length <= 1) {
                    card.classList.remove('is-hidden');
                } else {
                    card.classList.add('is-hidden');
                }

                updateQuestionBuilder();
            });
        });

        updateQuestionBuilder();
    }

    const quizCountdown = document.querySelector('[data-quiz-countdown]');

    if (quizCountdown) {
        const quizForm = document.querySelector('[data-quiz-form]');
        const quizAutoSubmit = document.querySelector('[data-quiz-auto-submit]');
        const quizTimeText = quizCountdown.querySelector('[data-quiz-time-text]');
        const quizTimeBar = quizCountdown.querySelector('[data-quiz-time-bar]');
        const quizDuration = Math.max(parseInt(quizCountdown.dataset.duration || '90', 10), 1);
        let quizRemaining = Math.max(parseInt(quizCountdown.dataset.remaining || '0', 10), 0);

        const renderQuizTimer = () => {
            const minutes = Math.floor(quizRemaining / 60);
            const seconds = quizRemaining % 60;
            const label = minutes > 0 ? `${minutes}:${String(seconds).padStart(2, '0')}` : `${seconds} sec`;
            const width = Math.max(0, Math.min(100, (quizRemaining / quizDuration) * 100));

            if (quizTimeText) {
                quizTimeText.textContent = label;
            }

            if (quizTimeBar) {
                quizTimeBar.style.width = `${width}%`;
            }

            quizCountdown.classList.toggle('is-ending', quizRemaining <= 15);
        };

        const submitQuizByTimer = () => {
            if (quizAutoSubmit) {
                quizAutoSubmit.value = '1';
            }

            if (quizForm) {
                window.HTMLFormElement.prototype.submit.call(quizForm);
            }
        };

        renderQuizTimer();

        if (quizRemaining <= 0) {
            submitQuizByTimer();
        } else {
            const quizInterval = window.setInterval(() => {
                quizRemaining -= 1;
                renderQuizTimer();

                if (quizRemaining <= 0) {
                    window.clearInterval(quizInterval);
                    submitQuizByTimer();
                }
            }, 1000);
        }
    }

    const loader = document.querySelector('[data-site-loader]');

    const hideLoader = () => {
        document.body.classList.add('is-loaded');

        if (!loader) {
            return;
        }

        if (window.gsap) {
            window.gsap.to(loader, {
                opacity: 0,
                duration: 0.45,
                ease: 'power2.out',
                onComplete: () => {
                    loader.classList.add('hidden');
                }
            });
            return;
        }

        loader.classList.add('hidden');
    };

    const animatePage = () => {
        document.documentElement.dataset.gsap = window.gsap ? 'ready' : 'missing';

        if (!window.gsap) {
            return;
        }

        const animatedSelectors = [
            '.learnpro-header',
            '.sidebar',
            '.hero-copy',
            '.section-head',
            '.page-top',
            '.auth-story',
            '.auth-panel',
            '.profile-summary-card',
            '.profile-form-panel',
            '.metric',
            '.panel',
            '.course-card',
            '.table-shell',
            '.notice',
            '.video-studio-card',
            '.studio-playlist',
            '.playlist-toggle',
            '.certificate-card',
            '.learnpro-footer',
            '[data-animate]'
        ].join(', ');

        const elements = window.gsap.utils.toArray(animatedSelectors);

        if (!elements.length) {
            return;
        }

        window.gsap.set(elements, { willChange: 'transform, opacity' });
        window.gsap.from(elements, {
            y: 22,
            opacity: 0,
            duration: 0.72,
            stagger: 0.045,
            ease: 'power3.out',
            clearProps: 'willChange'
        });
    };

    const startPage = () => {
        hideLoader();
        animatePage();
    };

    if (document.readyState === 'complete') {
        window.setTimeout(startPage, 180);
    } else {
        window.addEventListener('load', () => {
            window.setTimeout(startPage, 180);
        }, { once: true });
    }
});
