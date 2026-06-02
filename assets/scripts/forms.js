const form = document.querySelector("form");
const countrySelect = document.querySelector('select[name="country_code"]');

const nameInput = document.querySelector('input[name="name"]');
const emailInput = document.querySelector('input[name="email"]');
const usernameInput = document.querySelector('input[name="username"]');
const passwordInput = document.querySelector('input[name="password"]');
const termsCheckbox = document.querySelector('input[name="accept_terms"]');

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
   Validation
---------------------------- */
if (form) {
    form.addEventListener("submit", function (e) {
        let errors = [];

        // Remove old error messages and states
        document.querySelectorAll(".error-message").forEach(el => el.remove());
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
        if (passwordInput && passwordInput.value.length < 8) {
            errors.push({ field: passwordInput, message: "Password must be at least 8 characters." });
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
