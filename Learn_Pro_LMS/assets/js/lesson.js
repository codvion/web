document.addEventListener('DOMContentLoaded', () => {
    const video = document.querySelector('[data-video-player]');
    const timer = document.querySelector('[data-watch-timer]');
    const statusText = document.querySelector('[data-watch-status]');
    const progressBar = document.querySelector('[data-watch-progress]');
    const nextButton = document.querySelector('[data-next-lesson]');

    if (!video || !timer || !statusText || !progressBar) {
        return;
    }

    const requiredSeconds = Number(video.dataset.requiredSeconds || 10);
    const courseId = video.dataset.courseId;
    const lessonId = video.dataset.lessonId;
    const progressEndpoint = video.dataset.progressEndpoint;
    const alreadyUnlocked = video.dataset.unlocked === '1';
    let watchedSeconds = Number(video.dataset.watchedSeconds || 0);
    let playbackTicker = null;
    let progressSaved = alreadyUnlocked;

    if (alreadyUnlocked) {
        timer.textContent = 'Unlocked';
        statusText.textContent = 'You can continue to the next lesson.';
        progressBar.style.width = '100%';
        if (nextButton) {
            nextButton.classList.remove('disabled');
            nextButton.removeAttribute('aria-disabled');
        }
        return;
    }

    timer.textContent = `${Math.max(requiredSeconds - watchedSeconds, 0)}s required`;
    progressBar.style.width = `${Math.min((watchedSeconds / requiredSeconds) * 100, 100)}%`;

    video.addEventListener('play', () => {
        if (playbackTicker) {
            return;
        }

        playbackTicker = window.setInterval(() => {
            if (video.paused || video.ended) {
                return;
            }

            watchedSeconds += 1;
            const remaining = Math.max(requiredSeconds - watchedSeconds, 0);
            const percent = Math.min((watchedSeconds / requiredSeconds) * 100, 100);

            timer.textContent = remaining > 0 ? `${remaining}s required` : 'Unlocked';
            progressBar.style.width = `${percent}%`;

            if (remaining > 0) {
                statusText.textContent = 'Keep watching. The next lesson unlocks after the timer finishes.';
            }

            if (watchedSeconds >= requiredSeconds && !progressSaved) {
                progressSaved = true;
                statusText.textContent = 'Saving progress...';

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
                        statusText.textContent = 'You can continue to the next lesson.';
                        if (nextButton) {
                            nextButton.classList.remove('disabled');
                            nextButton.removeAttribute('aria-disabled');
                        }
                    } else {
                        progressSaved = false;
                        statusText.textContent = payload.message || 'Progress could not be saved. Please try again.';
                    }
                })
                .catch(() => {
                    progressSaved = false;
                    statusText.textContent = 'Progress could not be saved. Please check your connection.';
                });
            }
        }, 1000);
    });

    video.addEventListener('pause', () => {
        if (playbackTicker) {
            window.clearInterval(playbackTicker);
            playbackTicker = null;
        }
    });

    video.addEventListener('ended', () => {
        if (playbackTicker) {
            window.clearInterval(playbackTicker);
            playbackTicker = null;
        }
    });
});
