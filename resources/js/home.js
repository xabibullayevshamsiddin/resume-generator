// Home sahifa — video modal (native <dialog> elementi)

const trigger = document.querySelector('[data-video-trigger]');
const modal = document.getElementById('video-modal');
const closeBtn = modal?.querySelector('[data-video-close]');
const player = modal?.querySelector('video');

trigger?.addEventListener('click', () => {
    modal.showModal();
});

closeBtn?.addEventListener('click', () => {
    modal.close();
});

// Fondan tashqariga bosilsa yopiladi
modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.close();
    }
});

// Yopilganda ijro to'xtatilsin
modal?.addEventListener('close', () => {
    player?.pause();
});
