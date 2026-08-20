// Owner: Member D (Frontend Logic / JavaScript)
// Dynamic UI behavior, fetch() calls, and UX enhancements

document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Confirmation Dialog for Delete Actions
    // Member C should add the class 'delete-form' to the form or 'delete-link' to anchor tags
    const deleteForms = document.querySelectorAll(".delete-form");
    deleteForms.forEach(function(form) {
        form.addEventListener("submit", function(e) {
            const confirmed = confirm("Are you sure you want to delete this item? This action cannot be undone.");
            if (!confirmed) {
                e.preventDefault(); // Stop form submission if user cancels
            }
        });
    });

    const deleteLinks = document.querySelectorAll(".delete-link");
    deleteLinks.forEach(function(link) {
        link.addEventListener("click", function(e) {
            const confirmed = confirm("Are you sure you want to delete this item?");
            if (!confirmed) {
                e.preventDefault(); // Stop navigation if user cancels
            }
        });
    });

    // 2. Disable Submit Button While Processing
    // Prevents accidental double-submissions. Member C should add 'process-form' class to forms.
    const processForms = document.querySelectorAll("form.process-form");
    processForms.forEach(function(form) {
        form.addEventListener("submit", function(e) {
            // Ensure basic HTML5 validation passes before disabling
            if (form.checkValidity()) {
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    // Small timeout ensures the browser registers the submit event before we disable the button
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        if (submitBtn.tagName === 'BUTTON') {
                            // Save original text in case we need to revert
                            submitBtn.dataset.originalText = submitBtn.textContent;
                            submitBtn.textContent = "Processing...";
                        } else {
                            submitBtn.dataset.originalValue = submitBtn.value;
                            submitBtn.value = "Processing...";
                        }
                    }, 10);
                }
            }
        });
    });

    // 3. Show/Hide Dynamic Fields (Example)
    // This allows toggling elements based on dropdown selections
    const categorySelect = document.getElementById("category_id");
    const dynamicFieldContainer = document.getElementById("dynamic-field-container");
    
    if (categorySelect && dynamicFieldContainer) {
        categorySelect.addEventListener("change", function(e) {
            // As an example, if category 'other' (e.g. value 99) is selected, show an extra field
            if (e.target.value === "99") {
                dynamicFieldContainer.style.display = "block";
            } else {
                dynamicFieldContainer.style.display = "none";
            }
        });
    }

    // 4. Live Search using Fetch API (Step 5 Polish)
    // Allows searching and filtering books without reloading the page.
    const searchInput = document.getElementById("search-input");
    const categoryFilter = document.getElementById("category-filter");
    const booksTableBody = document.getElementById("books-table-body");

    // Utility: Debounce function to prevent hammering the server on every keystroke
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // The core fetch function
    const fetchBooks = debounce(function() {
        if (!booksTableBody) return;

        const searchQuery = searchInput ? searchInput.value.trim() : "";
        const categoryId = categoryFilter ? categoryFilter.value : "";

        // Show a loading indicator in the table
        booksTableBody.innerHTML = "<tr><td colspan='100%' style='text-align:center;'>Loading results...</td></tr>";

        // Build the URL query string
        const params = new URLSearchParams();
        if (searchQuery) params.append("search", searchQuery);
        if (categoryId) params.append("category_id", categoryId);

        // Fetch from the endpoint (Assuming list_books.php returns <tr> elements when queried this way)
        fetch(`../books/list_books.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                // We expect HTML string of rows (as agreed in contract: "HTML table or JSON rows")
                return response.text(); 
            })
            .then(html => {
                booksTableBody.innerHTML = html;
            })
            .catch(error => {
                console.error("Error fetching books:", error);
                booksTableBody.innerHTML = "<tr><td colspan='100%' style='text-align:center; color:red;'>Error loading results.</td></tr>";
            });
    }, 300); // 300ms delay

    // Attach event listeners for real-time updates
    if (searchInput) {
        searchInput.addEventListener("input", fetchBooks);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener("change", fetchBooks);
    }
});
