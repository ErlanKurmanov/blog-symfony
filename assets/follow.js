console.log("follow js")
document.addEventListener('DOMContentLoaded', function() {
    const followButtons = document.querySelectorAll('.follow-btn');

    followButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const isFollowing = this.dataset.following === 'true';

            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = isFollowing ? 'Unfollowing...' : 'Following...';

            try {
                const response = await fetch(`/follow/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    if (data.follow) {
                        this.textContent = 'Unfollow';
                        this.className = 'btn btn-sm follow-btn btn btn-warning';
                    } else {
                        this.textContent = 'Follow';
                        this.className = `btn btn-sm follow-btn btn-outline-primary`;
                    }
                    this.dataset.following = data.isFollowing.toString();

                    showNotification(data.message, 'success');
                } else {
                    throw new Error(data.message || 'Failed to update follow status');
                }

            } catch (error) {
                console.error('Error:', error);
                this.textContent = originalText;
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.disabled = false;
            }
        });
    });

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
});
