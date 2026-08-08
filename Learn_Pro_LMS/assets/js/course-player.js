document.addEventListener('DOMContentLoaded', () => {
    const courseLayout = document.querySelector('[data-course-layout]');
    const playlistToggles = document.querySelectorAll('[data-playlist-toggle]');

    if (courseLayout && playlistToggles.length > 0) {
        const syncPlaylistToggle = () => {
            const collapsed = courseLayout.classList.contains('playlist-collapsed');

            playlistToggles.forEach((playlistToggle) => {
                const toggleIcon = playlistToggle.querySelector('[data-lucide]');
                const toggleText = playlistToggle.querySelector('span');

                playlistToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

                if (toggleIcon) {
                    toggleIcon.setAttribute('data-lucide', collapsed ? 'panel-left-open' : 'panel-left-close');
                }

                if (toggleText) {
                    toggleText.textContent = collapsed ? 'Show Topics' : 'Hide Topics';
                }
            });

            if (window.lucide) {
                window.lucide.createIcons();
            }
        };

        playlistToggles.forEach((playlistToggle) => {
            playlistToggle.addEventListener('click', () => {
                courseLayout.classList.toggle('playlist-collapsed');
                syncPlaylistToggle();
            });
        });

        syncPlaylistToggle();
    }

    const mount = document.querySelector('[data-youtube-player]');

    if (!mount) {
        return;
    }

    const timer = document.querySelector('[data-watch-timer]');
    const nextLinks = document.querySelectorAll('[data-next-topic]');
    const studentMode = mount.dataset.student === '1';
    const requiredSeconds = Number(mount.dataset.requiredSeconds || 5);
    const courseId = mount.dataset.courseId;
    const lessonId = mount.dataset.lessonId;
    const progressEndpoint = mount.dataset.progressEndpoint;
    const alreadyUnlocked = mount.dataset.unlocked === '1';
    const origin = mount.dataset.origin || window.location.origin;
    let watchedSeconds = Number(mount.dataset.watchedSeconds || 0);
    let playbackTicker = null;
    let progressSaved = alreadyUnlocked;
    let player = null;

    const paintUnlockedIcon = (target, nextUrl) => {
        const icon = target.querySelector('[data-lucide]');

        if (!icon) {
            return;
        }

        if (nextUrl && nextUrl.includes('certificate.php')) {
            icon.setAttribute('data-lucide', 'award');
        } else {
            icon.setAttribute('data-lucide', 'arrow-right');
        }
    };

    const unlockNextTopics = () => {
        nextLinks.forEach((link) => {
            const nextUrl = link.dataset.nextUrl;
            link.classList.remove('disabled');
            link.removeAttribute('aria-disabled');
            paintUnlockedIcon(link, nextUrl);

            if (nextUrl && link.tagName.toLowerCase() === 'a') {
                link.setAttribute('href', nextUrl);
            }

            if (nextUrl && link.tagName.toLowerCase() !== 'a') {
                const replacement = document.createElement('a');
                replacement.className = link.className;
                replacement.innerHTML = link.innerHTML;
                replacement.href = nextUrl;
                replacement.dataset.nextTopic = '';
                replacement.dataset.nextUrl = nextUrl;
                link.replaceWith(replacement);
            }
        });

        if (window.lucide) {
            window.lucide.createIcons();
        }
    };

    const paintTimer = () => {
        if (!studentMode || !timer) {
            return;
        }

        const remaining = Math.max(requiredSeconds - watchedSeconds, 0);
        timer.textContent = remaining > 0 ? `${remaining}s` : 'Unlocked';
    };

    const saveProgress = () => {
        if (!studentMode || progressSaved) {
            return;
        }

        progressSaved = true;

        fetch(progressEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                course_id: courseId,
                lesson_id: lessonId,
                seconds: watchedSeconds,
                can_continue: 1
            })
        })
        .then((response) => response.json())
        .then((payload) => {
            if (payload.ok) {
                unlockNextTopics();
                stopTicker();
            } else {
                progressSaved = false;
            }
        })
        .catch(() => {
            progressSaved = false;
        });
    };

    const startTicker = () => {
        if (!studentMode || progressSaved || playbackTicker) {
            return;
        }

        playbackTicker = window.setInterval(() => {
            if (!player || player.getPlayerState() !== 1) {
                return;
            }

            watchedSeconds += 1;
            paintTimer();

            if (watchedSeconds >= requiredSeconds) {
                saveProgress();
            }
        }, 1000);
    };

    const stopTicker = () => {
        if (playbackTicker) {
            window.clearInterval(playbackTicker);
            playbackTicker = null;
        }
    };

    const createPlayer = () => {
        player = new window.YT.Player(mount.id, {
            videoId: mount.dataset.youtubeId,
            width: '100%',
            height: '100%',
            host: 'https://www.youtube.com',
            playerVars: {
                rel: 0,
                modestbranding: 1,
                playsinline: 1,
                origin: origin
            },
            events: {
                onStateChange: (event) => {
                    if (!studentMode) {
                        return;
                    }

                    if (event.data === window.YT.PlayerState.PLAYING) {
                        startTicker();
                    } else {
                        stopTicker();
                    }
                }
            }
        });
    };

    if (studentMode) {
        paintTimer();
    }

    if (alreadyUnlocked) {
        unlockNextTopics();
    }

    if (window.YT && window.YT.Player) {
        createPlayer();
    } else {
        window.onYouTubeIframeAPIReady = createPlayer;
    }
});
