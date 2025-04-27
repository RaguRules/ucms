//  1. JS clear the form button
function clearFormInputs() {
	const inputs = document.querySelectorAll("input[type='text'], input[type='password'], input[type='email'], input[type='number'], textarea");
	inputs.forEach(input => {
		input.value = "";
		input.classList.remove("is-invalid", "is-valid");
	});
	const checkboxes = document.querySelectorAll("input[type='checkbox'], input[type='radio']");
	checkboxes.forEach(box => box.checked = false);
}


//2. JS Set joined date to today
function setTodayDate(fieldId) {
	const field = document.getElementById(fieldId);
	if (field) {
		const today = new Date().toISOString().split('T')[0];
		field.value = today;
	}
}


// 3. Set date of birth, gender from NIC
function setupNICListenerWithGender(nicId, dobId, genderId) {
	const nicInput = document.getElementById(nicId);
	const dobInput = document.getElementById(dobId);
	const genderInput = document.getElementById(genderId);

	if (!nicInput || !dobInput || !genderInput) return;

	nicInput.addEventListener('input', function () {
		const nic = nicInput.value.trim();
		let year = '';
		let dayOfYear = '';
		let gender = '';

		if (nic.length === 10 && /^[0-9]{9}[VvXx]$/.test(nic)) {
			year = '19' + nic.substring(0, 2);
			dayOfYear = parseInt(nic.substring(2, 5));
		} else if (nic.length === 12 && /^[0-9]{12}$/.test(nic)) {
			year = nic.substring(0, 4);
			dayOfYear = parseInt(nic.substring(4, 7));
		} else {
			dobInput.value = '';
			genderInput.value = '';
			return;
		}

		if (dayOfYear > 500) {
			gender = 'Female';
			dayOfYear -= 500;
		} else {
			gender = 'Male';
		}

		const date = new Date(year, 0, dayOfYear - 1);
		const yyyy = date.getFullYear();
		const mm = String(date.getMonth() + 1).padStart(2, '0');
		const dd = String(date.getDate()).padStart(2, '0');

		dobInput.value = `${yyyy}-${mm}-${dd}`;
		genderInput.value = gender;
	});
}


// 4. Show Duplicate Modal
function showGenericModal({ title, message, buttonText = "OK", buttonAction = null }) {
	const modalElement = document.getElementById('genericModal');
	const modal = new bootstrap.Modal(modalElement);
	
	// Set title and message
	document.getElementById('genericModalLabel').textContent = title;
	document.getElementById('genericModalBody').textContent = message;

	// Handle confirm button
	const confirmBtn = document.getElementById('modalConfirmBtn');
	if (buttonAction) {
		confirmBtn.style.display = "inline-block";
		confirmBtn.textContent = buttonText;
		confirmBtn.onclick = () => {
			buttonAction();
			modal.hide();
		};
	} else {
		confirmBtn.style.display = "none";
	}

	// Show the modal
	modal.show();
}


// 5. AJAX duplicate check
// function DuplicateCheck(staffId = null) {
//     document.querySelectorAll('.check-duplicate').forEach(input => {
//         input.addEventListener('blur', function () {
//             const value = input.value.trim();
//             const checkKey = input.dataset.check;
//             const feedback = document.getElementById(input.dataset.feedback);

//             if (!value) return;

//             const data = {
//                 check: checkKey,
//                 value: value,
//                 staff_id: staffId // Only pass staffId in Edit mode
//             };

//             fetch('action/copycat_detector.ajax.php', {
//                 method: 'POST',
//                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//                 body: new URLSearchParams(data)
//             })
//             .then(res => res.json())
//             .then(data => {
//                 if (data.exists) {
//                     feedback.textContent = data.message || "This value is already taken.";
//                     input.classList.add("is-invalid");
//                 } else {
//                     feedback.textContent = "";
//                     input.classList.remove("is-invalid");
//                 }
//             })
//             .catch(() => {
//                 feedback.textContent = "Error checking for duplicates.";
//                 input.classList.add("is-invalid");
//             });
//         });
//     });

//     // Block form submission if any field is invalid
//     document.querySelector('form').addEventListener('submit', function (e) {
//         const invalidInputs = document.querySelectorAll('.check-duplicate.is-invalid');
//         if (invalidInputs.length > 0) {
//             e.preventDefault();
//             showGenericModal({ title: "Errors Found!", message: "Please fix the issues before submitting.", buttonText: "OK", buttonAction: null });
//         }
//     });
// }

function DuplicateCheck() {
    // Duplicate check on blur
    document.querySelectorAll('.check-duplicate').forEach(input => {
        input.addEventListener('blur', function () {
            const value = input.value.trim();
            const checkKey = input.dataset.check;
            const feedbackId = input.dataset.feedback;
            const feedback = feedbackId ? document.getElementById(feedbackId) : null;

            if (!value || !checkKey || !feedback) return;

            fetch('action/copycat_detector.ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    check: checkKey,
                    value: value
                })
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
            })
            .catch(() => {
                if (feedback) {
                    feedback.textContent = "Error checking for duplicates.";
                }
                input.classList.add("is-invalid");
            });
        });
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const invalidInputs = document.querySelectorAll('.check-duplicate.is-invalid');
            const currentURL = window.location.href;

            // Check if we are NOT in "edit" page
            if (invalidInputs.length > 0 && !currentURL.includes('option=edit')) {
                e.preventDefault();
                showGenericModal({
                    title: "Errors Found!",
                    message: "Please fix the issues before submitting.",
                    buttonText: "OK",
                    buttonAction: null
                });
            }
        });
    }
}


// 6. Validate numeric key
function isNumberKey(evt) {
	const charCode = evt.which ? evt.which : evt.keyCode;
	return (charCode === 46 || (charCode >= 48 && charCode <= 57));
}


// 7. Validate text key
function isTextKey(evt) {
	const charCode = evt.which ? evt.which : evt.keyCode;
	return ((charCode >= 65 && charCode <= 90) ||
		(charCode >= 97 && charCode <= 122) ||
		charCode === 8 || charCode === 127 || charCode === 32 || charCode === 46);
}


// 8. Validate mobile number
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


// 9. Validate NIC
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


// 10. Validate Email
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


// 11. Password Visibility, Strength, Mismatch model show, and show inline.
function handlePasswordFeatures() {
	// setupPasswordToggle();
    const passwordInput = document.getElementById("txt_password");
    const confirmInput = document.getElementById("txt_confirm_password");
    const toggleButton = document.getElementById("togglePassword");
    const passwordIcon = document.getElementById("password-icon");
    const form = document.querySelector("form");

    if (!passwordInput) return; // <-- If password input not found, just stop the function safely.

    let strengthDisplay = document.getElementById("password-strength");
    
    if (!strengthDisplay) {
        strengthDisplay = document.createElement("div");
        strengthDisplay.id = "password-strength";
        strengthDisplay.className = "mt-1 fw-semibold";
        passwordInput.closest('.input-group').after(strengthDisplay);
    }

    // Handle password visibility toggle
    if (toggleButton && passwordIcon) {
        toggleButton.addEventListener("click", () => {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            confirmInput.type = isPassword ? "text" : "password";

            passwordIcon.classList.toggle("bi-eye");
            passwordIcon.classList.toggle("bi-eye-slash");
        });
    }
	

    // Password strength checker (shared function)
    function checkPasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[\W_]/.test(password)) score++;

        if (score <= 2) return "Weak";
        if (score <= 4) return "Medium";
        return "Strong";
    }

    function updateStrengthDisplay(strength) {
        const colors = {
            Weak: "red",
            Medium: "orange",
            Strong: "green"
        };
        strengthDisplay.textContent = `Password Strength: ${strength}`;
        strengthDisplay.style.color = colors[strength] || "black";
    }

    // On password input
    passwordInput.addEventListener("input", () => {
        const strength = checkPasswordStrength(passwordInput.value);
        updateStrengthDisplay(strength);

        passwordInput.classList.toggle("is-valid", strength === "Strong");
        passwordInput.classList.toggle("is-invalid", strength !== "Strong");
    });

    // On confirm input
    confirmInput.addEventListener("input", () => {
        const match = confirmInput.value === passwordInput.value;
        confirmInput.classList.toggle("is-valid", match);
        confirmInput.classList.toggle("is-invalid", !match);
    });

    // Handle password validation on form submit
    form.addEventListener("submit", function (e) {
        const password = passwordInput.value.trim();
        const confirm = confirmInput.value.trim();
        const strength = checkPasswordStrength(password);

        updateStrengthDisplay(strength);

        passwordInput.classList.remove("is-invalid");
        confirmInput.classList.remove("is-invalid");

        if (password !== confirm) {
            e.preventDefault();
            confirmInput.classList.add("is-invalid");

            showGenericModal({
                title: "Password Mismatch",
                message: "The passwords you entered do not match.",
                buttonText: "Try Again",
                buttonAction: () => confirmInput.focus()
            });
            return;
        }

        if (strength !== "Strong") {
            e.preventDefault();
            passwordInput.classList.add("is-invalid");

            showGenericModal({
                title: "Weak Password",
                message: "Your password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character.",
                buttonText: "Try Again",
                buttonAction: () => passwordInput.focus()
            });
        }
    });	
}


// 12. Function to show Terms & Conditions modal and handle checkbox interaction
function setupTermsModal() {
    const acceptTermsCheckbox = document.getElementById("acceptTerms");
    const termsModalElement = document.getElementById("termsModal");
    
    if (!termsModalElement || !acceptTermsCheckbox) return;  // Exit if modal or checkbox is not found

    const termsModal = new bootstrap.Modal(termsModalElement, {
        backdrop: 'static',  // Prevent closing the modal by clicking outside
        keyboard: false      // Disable closing with the escape key
    });

    let isModalConfirmed = false;

    // Trigger modal when checkbox is clicked, but prevent checking until the user agrees
    acceptTermsCheckbox.addEventListener("click", function (e) {
        if (!isModalConfirmed) {
            e.preventDefault();  // Prevent checkbox from being checked
            termsModal.show();   // Show the modal
        }
    });

    // "I do agree" button inside modal
    const agreeButton = termsModalElement.querySelector(".btn-secondary");
    
    if (agreeButton) {
        agreeButton.addEventListener("click", function () {
            isModalConfirmed = true;
            acceptTermsCheckbox.checked = true;  // Set the checkbox to checked
            termsModal.hide();  // Hide the modal after agreement
        });
    }
}

// 13. Function to show Delete Confirmation Modal and trigger form submission on confirmation
function deleteConfirmModal(callback) {
    const deleteModalElement = document.getElementById('deleteConfirmModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Exit if modal or confirm delete button is not found
    if (!deleteModalElement || !confirmDeleteBtn) return;

    const deleteModal = new bootstrap.Modal(deleteModalElement);

    // Show the modal
    deleteModal.show();

    // Handle confirmation button click
    confirmDeleteBtn.onclick = function() {
        // Execute the callback function (which submits the form)
        callback();
    };
}

// 14. Function to show Reactive Confirmation Modal and trigger form submission on confirmation
function reactivateConfirmModal(callback) {
    const reactivateModalElement = document.getElementById('reactivateConfirmModal');
    const confirmReactivateBtn = document.getElementById('reactivateConfirmBtn');
    
    // Exit if modal or confirm reactivate button is not found
    if (!reactivateModalElement || !confirmReactivateBtn) return;

    const reactivateModal = new bootstrap.Modal(reactivateModalElement);

    // Show the modal
    reactivateModal.show();

    // Handle confirmation button click
    confirmReactivateBtn.onclick = function() {
        // Execute the callback function (which submits the form)
        callback();
    };
}


// 15. Password visibility toggle
function setupPasswordToggle() {
    const passwordInput = document.getElementById("txt_password");
    const confirmInput = document.getElementById("txt_confirm_password");
    const toggleButton = document.getElementById("togglePassword");
    const passwordIcon = document.getElementById("password-icon");

    // Ensure the required elements exist before adding event listeners
    if (!passwordInput || !confirmInput) return;

    // Add event listener for the toggle button
    toggleButton.addEventListener("click", () => {
        // Toggle password visibility
        const isPassword = passwordInput.type === "password";
        passwordInput.type = isPassword ? "text" : "password"; // Toggle password field
        confirmInput.type = isPassword ? "text" : "password"; // Toggle confirm password field

        // Toggle the visibility icon
        passwordIcon.classList.toggle("bi-eye");
        passwordIcon.classList.toggle("bi-eye-slash");
    });
}


// document.addEventListener("DOMContentLoaded", setupPasswordToggle);


function initializeFormScripts() {
    const clearButton = document.getElementById("btn_clear");
    if (clearButton) clearButton.addEventListener("click", clearFormInputs);

    setTodayDate("date_joined_date");
    setupNICListenerWithGender("txt_nic_number", "date_date_of_birth", "select_gender");
    DuplicateCheck();
    //  DuplicateCheck(staffId);
    // if (staffId) {
    //     DuplicateCheck(staffId); // For Edit: pass the staffId
    // } else {
    //     DuplicateCheck(); // For Add: don't pass staffId
    // }
    handlePasswordFeatures();
    setupTermsModal();
    // setupPasswordToggle();  // Remove this line as it's now handled on DOMContentLoaded
}


// Run initializer on DOM ready
document.addEventListener("DOMContentLoaded", initializeFormScripts);  // Call the initializer once DOM is ready