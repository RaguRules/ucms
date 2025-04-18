// Utility to clear form inputs
function clearFormInputs() {
	const inputs = document.querySelectorAll("input[type='text'], input[type='password'], input[type='email'], input[type='number'], textarea");
	inputs.forEach(input => {
		input.value = "";
		input.classList.remove("is-invalid", "is-valid");
	});
	const checkboxes = document.querySelectorAll("input[type='checkbox'], input[type='radio']");
	checkboxes.forEach(box => box.checked = false);
}

// Set today's date to a field
function setTodayDate(fieldId) {
	const field = document.getElementById(fieldId);
	if (field) {
		const today = new Date().toISOString().split('T')[0];
		field.value = today;
	}
}

// Extract DOB from NIC
function setupNICListener(nicId, dobId) {
	const nicInput = document.getElementById(nicId);
	const dobInput = document.getElementById(dobId);

	if (!nicInput || !dobInput) return;

	nicInput.addEventListener('input', () => {
		const nic = nicInput.value.trim();
		let year = '', dayOfYear = '';

		if (nic.length === 10 && /^[0-9]{9}[VvXx]$/.test(nic)) {
			year = '19' + nic.substring(0, 2);
			dayOfYear = parseInt(nic.substring(2, 5));
		} else if (nic.length === 12 && /^[0-9]{12}$/.test(nic)) {
			year = nic.substring(0, 4);
			dayOfYear = parseInt(nic.substring(4, 7));
		} else {
			dobInput.value = '';
			return;
		}

		if (dayOfYear > 500) dayOfYear -= 500;

		const date = new Date(year, 0, dayOfYear - 1);
		dobInput.value = date.toISOString().split('T')[0];
	});
}

// Show duplicate modal
function showDuplicateModal(message) {
	const modal = new bootstrap.Modal(document.getElementById('actionModal'));
	document.getElementById('actionModalLabel').textContent = "Duplicate Detected";
	document.getElementById('actionModalBody').textContent = message || "This value is already used.";
	const confirmBtn = document.getElementById('actionModalConfirmBtn');
	confirmBtn.textContent = "OK";
	confirmBtn.className = "btn btn-primary btn-sm";
	confirmBtn.onclick = () => modal.hide();
	modal.show();
}

// AJAX duplicate check
function setupDuplicateCheck() {
	document.querySelectorAll('.check-duplicate').forEach(input => {
		input.addEventListener('blur', () => {
			const value = input.value.trim();
			const checkKey = input.dataset.check;
			const feedback = document.getElementById(input.dataset.feedback);
			if (!value) return;

			fetch('check_duplicate_AJAX.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `check=${encodeURIComponent(checkKey)}&value=${encodeURIComponent(value)}`
			})
				.then(res => res.json())
				.then(data => {
					if (data.exists) {
						feedback.textContent = data.message || "This value is already taken.";
						input.classList.add("is-invalid");
					} else {
						feedback.textContent = "";
						input.classList.remove("is-invalid");
					}
				});
		});
	});
}

// Prevent form submit if invalid fields exist
function blockFormOnInvalid() {
	const form = document.querySelector('form');
	form.addEventListener('submit', e => {
		const invalidInputs = document.querySelectorAll('.check-duplicate.is-invalid');
		if (invalidInputs.length > 0) {
			e.preventDefault();
			showDuplicateModal("Please fix the issues before submitting.");
		}
	});
}

// Validate numeric key
function isNumberKey(evt) {
	const charCode = evt.which ? evt.which : evt.keyCode;
	return (charCode === 46 || (charCode >= 48 && charCode <= 57));
}

// Validate text key
function isTextKey(evt) {
	const charCode = evt.which ? evt.which : evt.keyCode;
	return ((charCode >= 65 && charCode <= 90) ||
		(charCode >= 97 && charCode <= 122) ||
		charCode === 8 || charCode === 127 || charCode === 32 || charCode === 46);
}

// Validate mobile number
function validateMobileNumber(id) {
	const input = document.getElementById(id);
	const value = input.value.trim();

	if (value === "") return;

	if (!/^\d{10}$/.test(value)) {
		alert("Enter 10 digit Mobile Number");
		input.value = "";
		input.focus();
		return false;
	}

	if (!value.startsWith("07")) {
		alert("Enter Mobile Number starting with 07xxxxxxxx");
		input.value = "";
		input.focus();
		return false;
	}
	return true;
}

// Validate NIC
function validateNIC(id) {
	const input = document.getElementById(id);
	const nic = input.value.trim();

	if (nic.length === 0) return;

	if (nic.length === 10) {
		if (!/^[0-9]{9}[vVxX]$/.test(nic)) {
			alert("NIC must be 9 digits followed by V/v/X/x");
			input.value = "";
			input.focus();
			return false;
		}
	} else if (nic.length === 12) {
		if (!/^[0-9]{12}$/.test(nic)) {
			alert("NIC must be exactly 12 digits");
			input.value = "";
			input.focus();
			return false;
		}
	} else {
		alert("NIC must be either 10 or 12 characters");
		input.value = "";
		input.focus();
		return false;
	}
	return true;
}

// Validate Email
function validateEmail(id) {
	const input = document.getElementById(id);
	const email = input.value.trim();
	const regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

	if (email === "") return;

	if (!regex.test(email)) {
		alert("Invalid Email Address");
		input.value = "";
		input.focus();
		return false;
	}
	return true;
}

// Setup password-related logic
function setupPasswordValidation() {
	const passwordInput = document.getElementById("txt_password");
	const confirmInput = document.getElementById("txt_confirm_password");
	const toggleButton = document.getElementById("togglePassword");
	const passwordIcon = document.getElementById("password-icon");
	const form = document.querySelector("form");

	const modal = new bootstrap.Modal(document.getElementById('passwordMismatchModal'));
	const modalBody = document.getElementById("passwordModalBody");
	const modalTitle = document.getElementById("passwordMismatchModalLabel");

	const strengthDisplay = document.createElement("div");
	strengthDisplay.id = "password-strength";
	strengthDisplay.className = "mt-1 fw-semibold";
	passwordInput.closest('.input-group').after(strengthDisplay);

	toggleButton.addEventListener("click", function () {
		const type = passwordInput.type === "password" ? "text" : "password";
		passwordInput.type = confirmInput.type = type;
		passwordIcon.classList.toggle("bi-eye");
		passwordIcon.classList.toggle("bi-eye-slash");
	});

	passwordInput.addEventListener("input", () => {
		const strength = getPasswordStrength(passwordInput.value);
		updateStrengthDisplay(strength);
		passwordInput.classList.toggle("is-valid", strength === "Strong");
		passwordInput.classList.toggle("is-invalid", strength !== "Strong");
	});

	confirmInput.addEventListener("input", () => {
		confirmInput.classList.toggle("is-invalid", confirmInput.value !== passwordInput.value);
		confirmInput.classList.toggle("is-valid", confirmInput.value === passwordInput.value);
	});

	form.addEventListener("submit", function (e) {
		const password = passwordInput.value;
		const confirm = confirmInput.value;
		const strength = getPasswordStrength(password);

		if (password !== confirm) {
			e.preventDefault();
			confirmInput.classList.add("is-invalid");
			modalTitle.textContent = "Password Mismatch";
			modalBody.textContent = "The passwords you entered do not match.";
			modal.show();
			return;
		}

		if (strength !== "Strong") {
			e.preventDefault();
			passwordInput.focus();
			modalTitle.textContent = "Weak Password";
			modalBody.textContent = "Password must be strong: 8+ chars, upper, lower, number, symbol.";
			modal.show();
			return;
		}
	});

	function getPasswordStrength(password) {
		let score = 0;
		if (password.length >= 8) score++;
		if (/[A-Z]/.test(password)) score++;
		if (/[a-z]/.test(password)) score++;
		if (/\d/.test(password)) score++;
		if (/[\W_]/.test(password)) score++;

		if (score <= 2) return "Weak";
		if (score === 3 || score === 4) return "Medium";
		if (score === 5) return "Strong";
	}

	function updateStrengthDisplay(strength) {
		const colors = { Weak: "red", Medium: "orange", Strong: "green" };
		strengthDisplay.textContent = `Password Strength: ${strength}`;
		strengthDisplay.style.color = colors[strength] || "black";
	}
}

// Setup T&C modal
function setupTermsModal() {
	const checkbox = document.getElementById("acceptTerms");
	const modal = new bootstrap.Modal(document.getElementById("termsModal"));
	const agreeButton = document.querySelector("#termsModal .btn-secondary");
	let isConfirmed = false;

	if (!checkbox) return;

	checkbox.addEventListener("click", e => {
		if (!isConfirmed) {
			e.preventDefault();
			modal.show();
		}
	});

	agreeButton.addEventListener("click", () => {
		isConfirmed = true;
		checkbox.checked = true;
		modal.hide();
	});
}

// MAIN Initializer
function initializeFormScripts() {
	const clearButton = document.getElementById("btn_clear");
	if (clearButton) clearButton.addEventListener("click", clearFormInputs);

	setTodayDate("date_joined_date");
	setupNICListener("txt_nic_number", "date_date_of_birth");
	setupDuplicateCheck();
	blockFormOnInvalid();
	setupPasswordValidation();
	setupTermsModal();
}

// Run initializer on DOM ready
document.addEventListener("DOMContentLoaded", initializeFormScripts);