// Function to toggle modals (show/hide)
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle('hidden');
    } else {
        console.error('Modal element not found');
    }
}

// Function to update the query string in the URL (for search/filter and pagination)
function updateQueryString(param, value) {
    let url = new URL(window.location);
    url.searchParams.set(param, value);
    window.location = url;
}

// Function to handle the search form submission (search for customers)
document.getElementById('search-btn')?.addEventListener('click', function(event) {
    event.preventDefault();
    const searchQuery = document.getElementById('search-input').value;
    updateQueryString('search', searchQuery);
});