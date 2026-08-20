// Owner: Member D (Frontend Logic / JavaScript)
// Client-side validation for form inputs (register, login, add/edit book)

document.addEventListener("DOMContentLoaded", function() {
    
    // --- Auth Forms Validation (Register & Login) ---
    
    // 1. Register Form Validation
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function(e) {
            // Retrieve input values and remove leading/trailing spaces
            const fullNameInput = document.getElementById("full_name");
            const emailInput = document.getElementById("email");
            const passwordInput = document.getElementById("password");
            
            const fullName = fullNameInput ? fullNameInput.value.trim() : "";
            const email = emailInput ? emailInput.value.trim() : "";
            const password = passwordInput ? passwordInput.value : "";
            
            // Basic regex to validate email format (e.g. user@domain.com)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Validation: Full name cannot be empty
            if (fullName === "") {
                alert("Full Name is required.");
                e.preventDefault(); // Prevent the form from submitting to the server
                return; // Stop further validation checks
            }
            
            // Validation: Email must not be empty and must match the valid format
            if (email === "" || !emailRegex.test(email)) {
                alert("Please enter a valid email address.");
                e.preventDefault();
                return;
            }
            
            // Validation: Password must meet minimum length requirements for basic security
            if (password === "" || password.length < 6) {
                alert("Password must be at least 6 characters long.");
                e.preventDefault();
                return;
            }
        });
    }

    // 2. Login Form Validation
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function(e) {
            // Retrieve input values
            const emailInput = document.getElementById("email");
            const passwordInput = document.getElementById("password");
            
            const email = emailInput ? emailInput.value.trim() : "";
            const password = passwordInput ? passwordInput.value : "";
            
            // Validation: Ensure email is provided before allowing login attempt
            if (email === "") {
                alert("Email is required to login.");
                e.preventDefault();
                return;
            }
            
            // Validation: Ensure password is provided
            if (password === "") {
                alert("Password is required to login.");
                e.preventDefault();
                return;
            }
        });
    }

    // --- Book Forms Validation (Add & Edit) ---
    
    // 3. Add / Edit Book Form Validation
    const bookForm = document.getElementById("bookForm");
    if (bookForm) {
        bookForm.addEventListener("submit", function(e) {
            // Retrieve input values and trim whitespace
            const titleInput = document.getElementById("title");
            const authorInput = document.getElementById("author");
            const categoryIdInput = document.getElementById("category_id");
            const quantityInput = document.getElementById("quantity");
            
            const title = titleInput ? titleInput.value.trim() : "";
            const author = authorInput ? authorInput.value.trim() : "";
            const categoryId = categoryIdInput ? categoryIdInput.value.trim() : "";
            
            // Validation: Title must not be empty
            if (title === "") {
                alert("Title is required.");
                e.preventDefault();
                return;
            }
            
            // Validation: Author must not be empty
            if (author === "") {
                alert("Author is required.");
                e.preventDefault();
                return;
            }
            
            // Validation: Ensure a valid category is selected (not the placeholder)
            if (categoryId === "") {
                alert("Please select a valid category.");
                e.preventDefault();
                return;
            }
            
            // Validation: If quantity is provided, it must be a valid number > 0
            if (quantityInput) {
                const quantity = parseInt(quantityInput.value.trim(), 10);
                if (isNaN(quantity) || quantity <= 0) {
                    alert("Quantity must be a valid positive number.");
                    e.preventDefault();
                    return;
                }
            }
        });
    }
});
