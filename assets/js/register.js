/**
 * CraftMarket - Registration Client Simulation
 * Handles form validation, role selection, and simulated account registration
 */

document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.getElementById("registerForm");
    const fullNameInput = document.getElementById("fullName");
    const emailInput = document.getElementById("email");
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const termsCheck = document.getElementById("termsCheck");
    const formAlert = document.getElementById("formAlert");
    const roleCustomer = document.getElementById("roleCustomer");
    const roleSeller = document.getElementById("roleSeller");

    // Check URL parameters (e.g. register.html?role=seller from "Become a Seller" button)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("role") === "seller" && roleSeller) {
        roleSeller.checked = true;
    }

    // Helper: Show error on an input
    function setError(input, errorElementId, message) {
        input.classList.add("is-invalid");
        const errSpan = document.getElementById(errorElementId);
        if (errSpan) errSpan.textContent = message;
    }

    // Helper: Clear error on an input
    function clearError(input, errorElementId) {
        input.classList.remove("is-invalid");
        const errSpan = document.getElementById(errorElementId);
        if (errSpan) errSpan.textContent = "";
    }

    // Live validation listeners
    fullNameInput.addEventListener("input", () => {
        if (fullNameInput.value.trim().length >= 3) {
            clearError(fullNameInput, "fullNameError");
        }
    });

    emailInput.addEventListener("input", () => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test(emailInput.value.trim())) {
            clearError(emailInput, "emailError");
        }
    });

    usernameInput.addEventListener("input", () => {
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (usernameRegex.test(usernameInput.value.trim())) {
            clearError(usernameInput, "usernameError");
        }
    });

    passwordInput.addEventListener("input", () => {
        if (passwordInput.value.length >= 6) {
            clearError(passwordInput, "passwordError");
        }
    });

    confirmPasswordInput.addEventListener("input", () => {
        if (confirmPasswordInput.value === passwordInput.value) {
            clearError(confirmPasswordInput, "confirmPasswordError");
        }
    });

    termsCheck.addEventListener("change", () => {
        if (termsCheck.checked) {
            clearError(termsCheck, "termsError");
        }
    });

    // Form Submission Handler
    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        // Reset previous alerts
        formAlert.style.display = "none";
        formAlert.className = "form-alert-msg";

        let isValid = true;

        // 1. Validate Full Name
        const fullNameVal = fullNameInput.value.trim();
        if (fullNameVal === "") {
            setError(fullNameInput, "fullNameError", "Please enter your full name.");
            isValid = false;
        } else if (fullNameVal.length < 3) {
            setError(fullNameInput, "fullNameError", "Full name must be at least 3 characters.");
            isValid = false;
        } else {
            clearError(fullNameInput, "fullNameError");
        }

        // 2. Validate Email
        const emailVal = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailVal === "") {
            setError(emailInput, "emailError", "Please enter your email address.");
            isValid = false;
        } else if (!emailRegex.test(emailVal)) {
            setError(emailInput, "emailError", "Please enter a valid email address (e.g., name@example.ph).");
            isValid = false;
        } else {
            clearError(emailInput, "emailError");
        }

        // 3. Validate Username
        const usernameVal = usernameInput.value.trim();
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (usernameVal === "") {
            setError(usernameInput, "usernameError", "Please choose a username.");
            isValid = false;
        } else if (!usernameRegex.test(usernameVal)) {
            setError(usernameInput, "usernameError", "Username must be 3-20 characters (letters, numbers, underscores only).");
            isValid = false;
        } else {
            clearError(usernameInput, "usernameError");
        }

        // 4. Validate Password
        const passwordVal = passwordInput.value;
        if (passwordVal === "") {
            setError(passwordInput, "passwordError", "Please create a password.");
            isValid = false;
        } else if (passwordVal.length < 6) {
            setError(passwordInput, "passwordError", "Password must be at least 6 characters long.");
            isValid = false;
        } else {
            clearError(passwordInput, "passwordError");
        }

        // 5. Validate Password Confirmation
        const confirmVal = confirmPasswordInput.value;
        if (confirmVal === "") {
            setError(confirmPasswordInput, "confirmPasswordError", "Please confirm your password.");
            isValid = false;
        } else if (confirmVal !== passwordVal) {
            setError(confirmPasswordInput, "confirmPasswordError", "Passwords do not match. Please re-enter.");
            isValid = false;
        } else {
            clearError(confirmPasswordInput, "confirmPasswordError");
        }

        // 6. Validate Terms and Conditions
        if (!termsCheck.checked) {
            setError(termsCheck, "termsError", "You must agree to the Terms and Conditions to register.");
            isValid = false;
        } else {
            clearError(termsCheck, "termsError");
        }

        // If validation fails, halt
        if (!isValid) {
            formAlert.textContent = "Please correct the errors highlighted above.";
            formAlert.classList.add("alert-error");
            formAlert.style.display = "block";
            return;
        }

        // Determine Account Role
        const selectedRole = roleSeller.checked ? "Seller" : "Customer";

        // SIMULATED REGISTRATION STORAGE (Using sessionStorage for frontend simulation)
        const newUser = {
            fullName: fullNameVal,
            email: emailVal,
            username: usernameVal,
            role: selectedRole,
            // For educational frontend simulation only
            registeredAt: new Date().toLocaleDateString("en-PH", { year: "numeric", month: "long", day: "numeric" })
        };

        // Retrieve existing simulated accounts or initialize
        let simulatedAccounts = JSON.parse(sessionStorage.getItem("craftmarket_accounts") || "[]");
        
        // Check for duplicate username or email in simulation
        const existingUser = simulatedAccounts.find(acc => acc.username.toLowerCase() === usernameVal.toLowerCase() || acc.email.toLowerCase() === emailVal.toLowerCase());
        if (existingUser) {
            formAlert.textContent = "An account with this email or username already exists in this session.";
            formAlert.classList.add("alert-error");
            formAlert.style.display = "block";
            return;
        }

        // Save new user to simulated accounts
        simulatedAccounts.push({
            ...newUser,
            simulatedPassword: passwordVal
        });
        sessionStorage.setItem("craftmarket_accounts", JSON.stringify(simulatedAccounts));

        // Display Success Banner
        formAlert.innerHTML = `
            <strong>✓ Registration Successful!</strong><br>
            Welcome, <strong>${fullNameVal}</strong> (${selectedRole}). Redirecting you to login...
        `;
        formAlert.classList.add("alert-success");
        formAlert.style.display = "block";

        // Disable submit button during redirect
        document.getElementById("submitRegisterBtn").disabled = true;

        // Redirect to Login Page with auto-filled username parameter
        setTimeout(() => {
            window.location.href = `login.html?registered=true&user=${encodeURIComponent(usernameVal)}`;
        }, 1600);
    });
});
