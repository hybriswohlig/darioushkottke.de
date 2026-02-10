/**
 * N&E Innovations Compliance Portal
 * Main JavaScript for interactions and animations
 */

// Scroll animation observer
document.addEventListener('DOMContentLoaded', function() {

    // Initialize scroll animations
    initScrollAnimations();

    // Initialize header scroll effect
    initHeaderScroll();

    // Initialize search functionality
    initSearch();

    // Initialize filters
    initFilters();
});

/**
 * Scroll animations for elements
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe all elements with scroll-animate class
    document.querySelectorAll('.scroll-animate').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Header scroll effect
 */
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;

    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });
}

/**
 * Search functionality
 */
function initSearch() {
    const searchInput = document.querySelector('#searchInput');
    if (!searchInput) return;

    let searchTimeout;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);

        const query = e.target.value.trim();

        if (query.length < 2) {
            showAllCards();
            return;
        }

        // Debounce search
        searchTimeout = setTimeout(() => {
            filterCards(query);
        }, 300);
    });
}

/**
 * Filter cards by search query
 */
function filterCards(query) {
    const cards = document.querySelectorAll('.card');
    const queryLower = query.toLowerCase();
    let visibleCount = 0;

    cards.forEach(card => {
        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.card-description')?.textContent.toLowerCase() || '';

        if (title.includes(queryLower) || description.includes(queryLower)) {
            card.style.display = '';
            card.classList.add('fade-in');
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Show "no results" message if needed
    showNoResults(visibleCount === 0);
}

/**
 * Show all cards
 */
function showAllCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.display = '';
    });
    showNoResults(false);
}

/**
 * Show/hide no results message
 */
function showNoResults(show) {
    let noResultsEl = document.querySelector('.no-results');

    if (show && !noResultsEl) {
        const grid = document.querySelector('.grid');
        if (!grid) return;

        noResultsEl = document.createElement('div');
        noResultsEl.className = 'no-results text-center';
        noResultsEl.style.gridColumn = '1 / -1';
        noResultsEl.style.padding = '3rem';
        noResultsEl.innerHTML = `
            <svg style="width: 64px; height: 64px; margin: 0 auto 1rem; color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 style="color: var(--gray-700); margin-bottom: 0.5rem;">No documents found</h3>
            <p style="color: var(--gray-500);">Try adjusting your search query</p>
        `;
        grid.appendChild(noResultsEl);
    } else if (!show && noResultsEl) {
        noResultsEl.remove();
    }
}

/**
 * Initialize filter buttons
 */
function initFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Toggle active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filterValue = this.dataset.filter;

            if (filterValue === 'all') {
                showAllCards();
            } else {
                filterByStatus(filterValue);
            }
        });
    });
}

/**
 * Filter cards by status
 */
function filterByStatus(status) {
    const cards = document.querySelectorAll('.card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardStatus = card.dataset.status;

        if (cardStatus === status) {
            card.style.display = '';
            card.classList.add('fade-in');
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    showNoResults(visibleCount === 0);
}

/**
 * Smooth scroll to section
 */
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
    const targetPosition = section.offsetTop - headerHeight - 20;

    window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
    });
}

/**
 * Track document view
 */
function trackDocumentView(documentId) {
    if (!documentId) return;

    fetch('/api/track-view.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ document_id: documentId })
    }).catch(err => console.error('Error tracking view:', err));
}

/**
 * Format date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: ${type === 'success' ? 'var(--primary-green)' : 'var(--gray-800)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        z-index: 9999;
        animation: slideInUp 0.3s ease-out;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutDown 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideOutDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
