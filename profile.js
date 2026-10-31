/**
 * CraftMarket - Profile Client Logic
 * Handles profile display, role matrix capabilities, edit profile toggles,
 * password updates, and session logout.
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. DEFAULT FALLBACK PROFILE (Used if user visits profile.html directly without logging in)
    const defaultProfile = {
        fullName: "Maria Clara Santos",
        email: "customer@craftmarket.ph",
        username: "maria_buyer",
        role: "Customer",
        phone: "+63 917 123 4567",
        address: "Quezon City, Metro Manila, Philippines",
        bio: "Passionate supporter of local indigenous craft, woven textiles, and sustainable handmade home accents.",
        avatar: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80"
    };

    // 2. RETRIEVE CURRENT USER SESSION
    let currentUser = null;
    const sessionData = sessionStorage.getItem("craftmarket_current_user");

    if (sessionData) {
        try {
            currentUser = JSON.parse(sessionData);
        } catch (e) {
            currentUser = defaultProfile;
        }
    } else {
        currentUser = defaultProfile;
        sessionStorage.setItem("craftmarket_current_user", JSON.stringify(currentUser));
    }

    // 3. DOM ELEMENTS
    const headerUserName = document.getElementById("headerUserName");
    const profileAvatarImg = document.getElementById("profileAvatarImg");
    const changeAvatarBtn = document.getElementById("changeAvatarBtn");
    const profileCardName = document.getElementById("profileCardName");
    const profileCardUsername = document.getElementById("profileCardUsername");
    const profileRoleBadge = document.getElementById("profileRoleBadge");
    const profileCardBio = document.getElementById("profileCardBio");
    const profileCardEmail = document.getElementById("profileCardEmail");
    const profileCardAddress = document.getElementById("profileCardAddress");
    const roleCapabilitiesContent = document.getElementById("roleCapabilitiesContent");
    const profileAlert = document.getElementById("profileAlert");

    // Form Fields
    const editFullName = document.getElementById("editFullName");
    const editUsername = document.getElementById("editUsername");
    const editEmail = document.getElementById("editEmail");
    const editPhone = document.getElementById("editPhone");
    const editAddress = document.getElementById("editAddress");
    const editBio = document.getElementById("editBio");

    // Controls
    const editToggleBtn = document.getElementById("editToggleBtn");
    const editActionsRow = document.getElementById("editActionsRow");
    const cancelEditBtn = document.getElementById("cancelEditBtn");
    const profileForm = document.getElementById("profileForm");
    const logoutBtn = document.getElementById("logoutBtn");
    const headerLogoutBtn = document.getElementById("headerLogoutBtn");

    // Password Form
    const passwordForm = document.getElementById("passwordForm");
    const currentPasswordInput = document.getElementById("currentPassword");
    const newPasswordInput = document.getElementById("newPassword");
    const confirmNewPasswordInput = document.getElementById("confirmNewPassword");

    // 4. POPULATE USER PROFILE DATA
    function renderProfile() {
        if (!currentUser) return;

        // Header
        if (headerUserName) headerUserName.textContent = currentUser.fullName.split(" ")[0] || "Profile";

        // Sidebar
        profileAvatarImg.src = currentUser.avatar || defaultProfile.avatar;
        profileCardName.textContent = currentUser.fullName;
        profileCardUsername.textContent = `@${currentUser.username}`;
        profileCardBio.textContent = currentUser.bio || "No bio added yet.";
        profileCardEmail.textContent = currentUser.email;
        profileCardAddress.textContent = currentUser.address || "Philippines";

        // Role Badge Styling
        profileRoleBadge.textContent = currentUser.role;
        profileRoleBadge.className = "role-badge";
        if (currentUser.role === "Seller") {
            profileRoleBadge.classList.add("role-badge-seller");
        } else if (currentUser.role === "Administrator") {
            profileRoleBadge.classList.add("role-badge-administrator");
        } else {
            profileRoleBadge.classList.add("role-badge-customer");
        }

        // Form Fields (Disabled by default)
        editFullName.value = currentUser.fullName;
        editUsername.value = currentUser.username;
        editEmail.value = currentUser.email;
        editPhone.value = currentUser.phone || "";
        editAddress.value = currentUser.address || "";
        editBio.value = currentUser.bio || "";

        // Render Role Capabilities Matrix
        renderRoleCapabilities(currentUser.role);
    }

    // 5. ROLE CAPABILITIES MATRIX RENDERER
    function renderRoleCapabilities(role) {
        let itemsHtml = "";

        if (role === "Administrator") {
            itemsHtml = `
                <ul class="perm-list">
                    <li><span class="perm-check">✓</span> Manage website content & announcements</li>
                    <li><span class="perm-check">✓</span> Verify & moderate artisan seller applications</li>
                    <li><span class="perm-check">✓</span> Oversee all marketplace product listings</li>
                    <li><span class="perm-check">✓</span> Monitor orders & inspect financial reports (8% commission)</li>
                </ul>
            `;
        } else if (role === "Seller") {
            itemsHtml = `
                <ul class="perm-list">
                    <li><span class="perm-check">✓</span> Manage artisan shop profile & craft bio</li>
                    <li><span class="perm-check">✓</span> Create, update, and manage product inventory</li>
                    <li><span class="perm-check">✓</span> View customer orders & update fulfillment status</li>
                    <li><span class="perm-check">✓</span> Access delivery QR verification tokens for payout</li>
                </ul>
            `;
        } else {
            // Customer
            itemsHtml = `
                <ul class="perm-list">
                    <li><span class="perm-check">✓</span> Browse handmade catalog & search local artisans</li>
                    <li><span class="perm-check">✓</span> Add items to cart & checkout with escrow protection</li>
                    <li><span class="perm-check">✓</span> Track active order status & delivery progress</li>
                    <li><span class="perm-check">✓</span> Manage personal profile & delivery addresses</li>
                </ul>
            `;
        }

        roleCapabilitiesContent.innerHTML = itemsHtml;
    }

    // 6. EDIT PROFILE TOGGLE
    let isEditing = false;

    function setEditingState(enable) {
        isEditing = enable;
        editFullName.disabled = !enable;
        editPhone.disabled = !enable;
        editAddress.disabled = !enable;
        editBio.disabled = !enable;
        
        // Email and Username remain fixed identifier locks in this demo
        editEmail.disabled = true;
        editUsername.disabled = true;

        editActionsRow.style.display = enable ? "flex" : "none";
        editToggleBtn.style.display = enable ? "none" : "inline-flex";

        if (enable) {
            editFullName.focus();
        }
    }

    editToggleBtn.addEventListener("click", () => setEditingState(true));
    cancelEditBtn.addEventListener("click", () => {
        renderProfile(); // reset inputs to saved state
        setEditingState(false);
    });

    // 7. SAVE PROFILE FORM SUBMISSION
    profileForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const updatedName = editFullName.value.trim();
        const updatedPhone = editPhone.value.trim();
        const updatedAddress = editAddress.value.trim();
        const updatedBio = editBio.value.trim();

        if (updatedName.length < 3) {
            alert("Full name must be at least 3 characters.");
            return;
        }

        // Update local object
        currentUser.fullName = updatedName;
        currentUser.phone = updatedPhone;
        currentUser.address = updatedAddress;
        currentUser.bio = updatedBio;

        // Persist to session
        sessionStorage.setItem("craftmarket_current_user", JSON.stringify(currentUser));

        // Re-render & disable inputs
        renderProfile();
        setEditingState(false);

        // Success Alert Banner
        profileAlert.innerHTML = "<strong>✓ Profile Updated!</strong> Your changes have been successfully saved.";
        profileAlert.className = "form-alert-msg alert-success";
        profileAlert.style.display = "block";

        setTimeout(() => {
            profileAlert.style.display = "none";
        }, 3000);
    });

    // 8. CHANGE PASSWORD HANDLER
    passwordForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const currPass = currentPasswordInput.value;
        const newPass = newPasswordInput.value;
        const confPass = confirmNewPasswordInput.value;

        let hasError = false;

        document.getElementById("currPassError").textContent = "";
        document.getElementById("newPassError").textContent = "";
        document.getElementById("confirmNewPassError").textContent = "";

        if (currPass === "") {
            document.getElementById("currPassError").textContent = "Please enter your current password.";
            hasError = true;
        }

        if (newPass.length < 6) {
            document.getElementById("newPassError").textContent = "New password must be at least 6 characters.";
            hasError = true;
        }

        if (confPass !== newPass) {
            document.getElementById("confirmNewPassError").textContent = "Passwords do not match.";
            hasError = true;
        }

        if (hasError) return;

        // Simulated update
        passwordForm.reset();

        profileAlert.innerHTML = "<strong>✓ Security Notice:</strong> Password updated successfully!";
        profileAlert.className = "form-alert-msg alert-success";
        profileAlert.style.display = "block";

        setTimeout(() => {
            profileAlert.style.display = "none";
        }, 3000);
    });

    // 9. CHANGE AVATAR INTERACTION
    const sampleAvatars = [
        "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80",
        "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80",
        "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80",
        "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80"
    ];
    let avatarIndex = 0;

    changeAvatarBtn.addEventListener("click", () => {
        avatarIndex = (avatarIndex + 1) % sampleAvatars.length;
        currentUser.avatar = sampleAvatars[avatarIndex];
        sessionStorage.setItem("craftmarket_current_user", JSON.stringify(currentUser));
        profileAvatarImg.src = currentUser.avatar;
    });

    // 10. LOGOUT FUNCTIONALITY
    function handleLogout() {
        if (confirm("Are you sure you want to sign out of CraftMarket?")) {
            sessionStorage.removeItem("craftmarket_current_user");
            window.location.href = "login.html";
        }
    }

    if (logoutBtn) logoutBtn.addEventListener("click", handleLogout);
    if (headerLogoutBtn) headerLogoutBtn.addEventListener("click", handleLogout);

    // Initial render
    renderProfile();
});
