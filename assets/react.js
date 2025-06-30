document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.react-btn').forEach(button => {
        button.addEventListener('click', async (event) => {
            const btn = event.currentTarget;
            const container = btn.closest('[data-post-id]');
            const postId = container.dataset.postId;
            const type = btn.dataset.type;

            try {
                const response = await fetch(`/post/${postId}/react/${type}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to react');
                }

                const data = await response.json();

                container.querySelector('.likes-count').textContent = data.likes;
                container.querySelector('.dislikes-count').textContent = data.dislikes;

                container.querySelectorAll('.react-btn').forEach(btn => btn.classList.remove('active'));
                btn.classList.add('active');

                const icon = btn.querySelector('i');
                if (type === 'like') {
                    icon.className = 'bi bi-heart-fill';
                } else {
                    icon.className = 'bi bi-heart-break-fill';
                }

            } catch (err) {
                console.error(err);
            }
        });
    });
});
