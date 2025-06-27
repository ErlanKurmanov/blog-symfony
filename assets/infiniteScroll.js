class InfiniteScrollPosts {
    constructor(options = {}) {
        this.offset = 0;
        this.limit = 5;
        this.isLoading = false;
        this.hasMore = true;
        this.postFeed = document.getElementById('post-feed');

        // Configure endpoint based on feed type
        this.feedType = options.feedType || 'all'; // 'all' or 'following'
        this.endpoint = this.getFeedEndpoint();

        // Initialize only if post-feed element exists
        if (this.postFeed) {
            this.init();
        }
    }

    getFeedEndpoint() {
        switch (this.feedType) {
            case 'following':
                return '/following-feed';
            case 'all':
            default:
                return '/post/feed';
        }
    }

    init() {
        // Load initial posts (assuming they're already loaded from server)
        this.offset = this.postFeed.children.length;

        // Add scroll event listener
        this.scrollHandler = this.handleScroll.bind(this);
        window.addEventListener('scroll', this.scrollHandler);

        // Add loading indicator
        this.createLoadingIndicator();
    }

    createLoadingIndicator() {
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'text-center py-4';
        this.loadingIndicator.style.display = 'none';
        this.loadingIndicator.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading more posts...</p>
        `;
        this.postFeed.parentNode.appendChild(this.loadingIndicator);
    }

    handleScroll() {
        // Check if user scrolled near bottom of page
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        // Trigger when user is 200px from bottom
        const threshold = 200;

        if (scrollTop + windowHeight >= documentHeight - threshold) {
            this.loadMorePosts();
        }
    }

    async loadMorePosts() {
        // Prevent multiple simultaneous requests
        if (this.isLoading || !this.hasMore) {
            return;
        }

        this.isLoading = true;
        this.showLoading();

        try {
            const response = await fetch(`${this.endpoint}?offset=${this.offset}&limit=${this.limit}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.posts && data.posts.length > 0) {
                this.appendPosts(data.posts);
                this.offset += data.posts.length;
                this.hasMore = data.hasMore;
            } else {
                this.hasMore = false;
            }

        } catch (error) {
            console.error('Error loading more posts:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    appendPosts(posts) {
        posts.forEach(post => {
            const postElement = this.createPostElement(post);
            this.postFeed.appendChild(postElement);
        });
    }

    createPostElement(post) {
        const postDiv = document.createElement('div');
        postDiv.className = 'card mb-4';
        postDiv.style.cssText = 'width: 100%; max-width: 800px; margin: 0 auto;';

        // Format the date
        const createdAt = new Date(post.createdAt);
        const formattedDate = createdAt.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        postDiv.innerHTML = `
            ${post.image ?
            `<img src="/images/${post.image}" class="card-img-top" alt="Image for ${this.escapeHtml(post.title)}" style="height: 300px; object-fit: cover;">` :
            `<img src="https://via.placeholder.com/800x300?text=No+Image" class="card-img-top" alt="No image" style="height: 300px; object-fit: cover;">`
        }
            <div class="card-body">
                <h5 class="card-title">${this.escapeHtml(post.title)}</h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    by ${this.escapeHtml(post.author?.email || 'Unknown')} on ${formattedDate}
                </h6>
                <p class="card-text">
                    ${post.content.length > 200 ?
            this.escapeHtml(post.content.substring(0, 200)).replace(/\n/g, '<br>') + '...' :
            this.escapeHtml(post.content).replace(/\n/g, '<br>')
        }
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="/post/${post.id}" class="btn btn-primary">Read More</a>
                    <div class="d-flex align-items-center" data-post-id="${post.id}">
                        <button class="btn btn-sm btn-outline-success me-2 react-btn ${post.isLikedByUser ? 'active' : ''}" data-type="like">
                            <i class="bi bi-heart${post.isLikedByUser ? '-fill' : ''}"></i>
                            <span class="likes-count">${post.likesCount || 0}</span>
                        </button>
                        <button class="btn btn-sm btn-outline-danger react-btn ${post.isDislikedByUser ? 'active' : ''}" data-type="dislike">
                            <i class="bi bi-heart-break${post.isDislikedByUser ? '-fill' : ''}"></i>
                            <span class="dislikes-count">${post.dislikesCount || 0}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        return postDiv;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'block';
        }
    }

    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }

    showError() {
        // Remove any existing error message
        const existingError = document.querySelector('.infinite-scroll-error');
        if (existingError) {
            existingError.remove();
        }

        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning text-center mt-3 infinite-scroll-error';
        errorDiv.innerHTML = `
            <p>Failed to load more posts. Please try again.</p>
            <button class="btn btn-outline-primary btn-sm" onclick="window.infiniteScrollPosts.retry()">
                Retry
            </button>
        `;
        this.postFeed.parentNode.appendChild(errorDiv);
    }

    // Method to manually trigger loading (useful for retry)
    retry() {
        const existingError = document.querySelector('.infinite-scroll-error');
        if (existingError) {
            existingError.remove();
        }
        this.loadMorePosts();
    }

    // Cleanup method
    destroy() {
        if (this.scrollHandler) {
            window.removeEventListener('scroll', this.scrollHandler);
        }
        if (this.loadingIndicator) {
            this.loadingIndicator.remove();
        }
        const existingError = document.querySelector('.infinite-scroll-error');
        if (existingError) {
            existingError.remove();
        }
    }
}

// Auto-initialize based on current page
document.addEventListener('DOMContentLoaded', function() {
    // Detect feed type based on current page
    let feedType = 'all';

    // Check if we're on the main/following feed page
    if (window.location.pathname === '/' || window.location.pathname.includes('main')) {
        feedType = 'following';
    } else if (window.location.pathname.includes('post')) {
        feedType = 'all';
    }

    // Create global instance with appropriate feed type
    window.infiniteScrollPosts = new InfiniteScrollPosts({ feedType: feedType });
});

// Export for module usage (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InfiniteScrollPosts;
}
