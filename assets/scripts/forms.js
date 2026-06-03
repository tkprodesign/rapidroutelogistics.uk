const signupForm = document.querySelector('[data-signup-form]');
const countrySelect = document.querySelector('select[name="country_code"]');

const nameInput = signupForm?.querySelector('input[name="name"]');
const emailInput = signupForm?.querySelector('input[name="email"]');
const usernameInput = signupForm?.querySelector('input[name="username"]');
const passwordInput = signupForm?.querySelector('input[name="password"]');
const termsCheckbox = signupForm?.querySelector('input[name="accept_terms"]');

/* ---------------------------
   Load Country Codes
---------------------------- */
if (countrySelect) {
    fetch("../assets/scripts/country-codes.json")
        .then(response => response.json())
        .then(data => {
            const countries = Object.values(data);

            countries.forEach(country => {
                if (!country.phone || !country.phone[0]) return;

                const option = document.createElement("option");
                option.value = country.phone[0];
                option.textContent = `${country.emoji} ${country.name} (${country.phone[0]})`;
                countrySelect.appendChild(option);
            });
        })
        .catch(error => console.error("Error loading country codes:", error));
}

/* ---------------------------
   Password reveal controls
---------------------------- */
document.querySelectorAll('input[type="password"]').forEach((input, index) => {
    const container = input.closest(".input-box") || input.parentElement;
    if (!container || container.querySelector(".password-toggle")) return;

    container.classList.add("has-password-toggle");

    const button = document.createElement("button");
    button.type = "button";
    button.className = "password-toggle";
    button.setAttribute("aria-label", "Show password");
    button.setAttribute("aria-pressed", "false");
    button.setAttribute("aria-controls", input.id || `password-field-${index + 1}`);
    button.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">visibility</span><span class="password-toggle-text">Peek</span>';

    if (!input.id) {
        input.id = `password-field-${index + 1}`;
    }

    button.addEventListener("click", () => {
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        button.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
        button.setAttribute("aria-pressed", String(isHidden));
        button.querySelector(".material-symbols-outlined").textContent = isHidden ? "visibility_off" : "visibility";
        button.querySelector(".password-toggle-text").textContent = isHidden ? "No peek" : "Peek";
        input.focus({ preventScroll: true });
    });

    input.insertAdjacentElement("afterend", button);
});

/* ---------------------------
   Signup password live validation
---------------------------- */
const passwordRules = {
    length: value => value.length >= 8,
    letter: value => /[A-Za-z]/.test(value),
    number: value => /[0-9]/.test(value),
};

function updatePasswordRules() {
    if (!passwordInput) return true;

    const value = passwordInput.value;
    let isValid = true;

    Object.entries(passwordRules).forEach(([rule, test]) => {
        const ruleElement = document.querySelector(`[data-password-rule="${rule}"]`);
        const rulePassed = test(value);

        if (ruleElement) {
            ruleElement.classList.toggle("is-valid", rulePassed);
            ruleElement.classList.toggle("is-invalid", value.length > 0 && !rulePassed);
        }

        if (!rulePassed) {
            isValid = false;
        }
    });

    passwordInput.classList.toggle("is-valid", isValid && value.length > 0);
    passwordInput.classList.toggle("is-invalid", value.length > 0 && !isValid);

    return isValid;
}

if (passwordInput) {
    updatePasswordRules();
    passwordInput.addEventListener("input", updatePasswordRules);
    passwordInput.addEventListener("blur", updatePasswordRules);
}

/* ---------------------------
   Signup Validation
---------------------------- */
if (signupForm) {
    signupForm.addEventListener("submit", function (e) {
        let errors = [];

        // Remove old error messages and states
        signupForm.querySelectorAll(".error-message").forEach(el => el.remove());
        [nameInput, emailInput, usernameInput, passwordInput, termsCheckbox].forEach(field => {
            if (!field) return;
            field.removeAttribute("aria-invalid");
            field.removeAttribute("aria-describedby");
        });

        // Name validation
        if (nameInput && nameInput.value.trim() === "") {
            errors.push({ field: nameInput, message: "Name is required." });
        }

        // Email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailInput && emailInput.value.trim() === "") {
            errors.push({ field: emailInput, message: "Email is required." });
        } else if (emailInput && !emailPattern.test(emailInput.value)) {
            errors.push({ field: emailInput, message: "Enter a valid email address." });
        }

        // Username validation
        if (usernameInput && usernameInput.value.trim() === "") {
            errors.push({ field: usernameInput, message: "Username is required." });
        }

        // Password validation
        if (passwordInput && !updatePasswordRules()) {
            errors.push({ field: passwordInput, message: "Password must be at least 8 characters and include a letter and a number." });
        }

        // Terms validation
        if (termsCheckbox && !termsCheckbox.checked) {
            errors.push({ field: termsCheckbox, message: "You must accept the terms." });
        }

        if (errors.length > 0) {
            e.preventDefault();

            errors.forEach(error => {
                showError(error.field, error.message);
            });

            errors[0].field.focus({ preventScroll: true });
            errors[0].field.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });
}

function showError(input, message) {
    if (!input) return;

    const error = document.createElement("div");
    const errorId = `${input.name || "field"}-error-${Date.now()}`;
    error.id = errorId;
    error.className = "error-message";
    error.setAttribute("role", "alert");
    error.textContent = message;

    input.setAttribute("aria-invalid", "true");
    input.setAttribute("aria-describedby", errorId);

    const container = input.closest(".input-box") || input.parentElement;
    if (container) {
        container.appendChild(error);
    }
}
