/**
 * CraftMarket - Login Client Simulation
 * Validates inputs and simulates authentication against demo roles & newly registered accounts
 */

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const loginIdentifier = document.getElementById("loginIdentifier");
    const loginPassword = document.getElementById("loginPassword");
    const loginAlert = document.getElementById("loginAlert");
    const postRegisterNotice = document.getElementById("postRegisterNotice");
    const demoButtons = document.querySelectorAll(".btn-demo-fill");
    const forgotPasswordLink = document.getElementById("forgotPasswordLink");

    // 1. BUILT-IN DEMO ACCOUNTS (Simulated database records)
    const demoAccounts = [
        {
            fullName: "Maria Clara Santos",
            email: "customer@craftmarket.ph",
            username: "maria_buyer",
            simulatedPassword: "password123",
            role: "Customer",
            phone: "+63 917 123 4567",
            address: "Quezon City, Metro Manila",
            avatar: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80",
            bio: "Passionate supporter of local indigenous craft and sustainable handmade home accents."
        },
        {
            fullName: "Elena Bautista (Studio Clayworks)",
            email: "seller@craftmarket.ph",
            username: "artisan_laguna",
            simulatedPassword: "password123",
            role: "Seller",
            phone: "+63 918 555 7890",
            address: "Pila, Laguna, Philippines",
            avatar: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80",
            bio: "Master potter crafting small-batch ceramic dining ware from locally sourced Philippine terracotta clay."
        },
        {
            fullName: "System Administrator",
            email: "admin@craftmarket.ph",
            username: "admin_craft",
            simulatedPassword: "admin123",
            role: "Administrator",
            phone: "+63 920 000 1122",
            address: "CraftMarket HQ, Makati City",
            avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80",
            bio: "Overseeing marketplace integrity, artisan verification, commission reports, and system operations."
        }
    ];

    // 2. CHECK URL PARAMETERS
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("registered") === "true") {
        if (postRegisterNotice) postRegisterNotice.style.display = "block";
        const autoUser = urlParams.get("user");
        if (autoUser && loginIdentifier) {
            loginIdentifier.value = decodeURIComponent(autoUser);
            if (loginPassword) loginPassword.focus();
        }
    }

    // 3. DEMO AUTO-FILL BUTTONS
    demoButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const user = btn.getAttribute("data-user");
            const pass = btn.getAttribute("data-pass");
            loginIdentifier.value = user;
            loginPassword.value = pass;
            clearErrors();
        });
    });

    // Helper: Clear validation errors
    function clearErrors() {
        loginIdentifier.classList.remove("is-invalid");
        loginPassword.classList.remove("is-invalid");
        document.getElementById("identifierError").textContent = "";
        document.getElementById("passwordError").textContent = "";
        loginAlert.style.display = "none";
        loginAlert.className = "form-alert-msg";
    }

    // Live validation
    loginIdentifier.addEventListener("input", () => {
        if (loginIdentifier.value.trim() !== "") {
            loginIdentifier.classList.remove("is-invalid");
            document.getElementById("identifierError").textContent = "";
        }
    });

    loginPassword.addEventListener("input", () => {
        if (loginPassword.value !== "") {
            loginPassword.classList.remove("is-invalid");
            document.getElementById("passwordError").textContent = "";
        }
    });

    // Forgot Password simulation
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener("click", (e) => {
            e.preventDefault();
            const idVal = loginIdentifier.value.trim();
            if (idVal) {
                alert(`[Simulated Reset] A password recovery link has been sent to the email associated with "${idVal}".`);
            } else {
                alert("[Simulated Reset] Please enter your email or username first, then click 'Forgot password?'.");
            }
        });
    }

    // 4. LOGIN FORM SUBMISSION
    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        clearErrors();

        const identifierVal = loginIdentifier.value.trim();
        const passwordVal = loginPassword.value;

        let hasError = false;

        if (identifierVal === "") {
            loginIdentifier.classList.add("is-invalid");
            document.getElementById("identifierError").textContent = "Please enter your email or username.";
            hasError = true;
        }

        if (passwordVal === "") {
            loginPassword.classList.add("is-invalid");
            document.getElementById("passwordError").textContent = "Please enter your password.";
            hasError = true;
        }

        if (hasError) return;

        // COMBINE DEMO ACCOUNTS + RECENTLY REGISTERED ACCOUNTS
        const registeredAccounts = JSON.parse(sessionStorage.getItem("craftmarket_accounts") || "[]");
        const allAccounts = [...demoAccounts, ...registeredAccounts];

        // Search for matching user
        const matchedAccount = allAccounts.find(acc => 
            (acc.email.toLowerCase() === identifierVal.toLowerCase() || acc.username.toLowerCase() === identifierVal.toLowerCase()) &&
            acc.simulatedPassword === passwordVal
        );

        if (!matchedAccount) {
            loginAlert.textContent = "Invalid email/username or password. Please verify and try again.";
            loginAlert.classList.add("alert-error");
            loginAlert.style.display = "block";
            return;
        }

        // SAVE LOGGED-IN SESSION
        const sessionUser = {
            fullName: matchedAccount.fullName,
            email: matchedAccount.email,
            username: matchedAccount.username,
            role: matchedAccount.role,
            phone: matchedAccount.phone || "+63 900 000 0000",
            address: matchedAccount.address || "Philippines",
            bio: matchedAccount.bio || "CraftMarket community member passionate about local handcrafted treasures.",
            avatar: matchedAccount.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80",
            loginTime: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };

        sessionStorage.setItem("craftmarket_current_user", JSON.stringify(sessionUser));

        // SHOW SUCCESS NOTIFICATION
        loginAlert.innerHTML = `
            <strong>✓ Welcome back, ${sessionUser.fullName}!</strong><br>
            Signing in as <em>${sessionUser.role}</em>. Redirecting to your profile...
        `;
        loginAlert.classList.add("alert-success");
        loginAlert.style.display = "block";

        document.getElementById("submitLoginBtn").disabled = true;

        setTimeout(() => {
            window.location.href = "profile.html";
        }, 1200);
    });
});
