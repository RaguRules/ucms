<script>
	//  1. JS clear the form button
		document.addEventListener("DOMContentLoaded", function () {
			// Clear Inputs Button
			const clearButton = document.getElementById("btn_clear");
	
			// Add event listener to clear the form inputs
			clearButton.addEventListener("click", function () {
				// Select all input fields and clear them
				const inputs = document.querySelectorAll("input[type='text'], input[type='password'], input[type='email'], input[type='number'], textarea");
				inputs.forEach(input => {
					input.value = ""; // Clear the value of each input
					input.classList.remove("is-invalid", "is-valid"); // Remove validation classes if any
				});
	
				//clear checkboxes or radio buttons
				const checkboxes = document.querySelectorAll("input[type='checkbox'], input[type='radio']");
				checkboxes.forEach(checkbox => {
					checkbox.checked = false; // Uncheck all checkboxes and radio buttons
				});
			});
		});
</script>
<script>
	//2. JS Set joined date to today
	const joinedDateField = document.getElementById('date_joined_date');
		const today = new Date();
		const formattedDate = today.toISOString().split('T')[0]; // Format as YYYY-MM-DD
		joinedDateField.value = formattedDate;
</script>
<script>
	// 3. Set date of birth from NIC
	const nicInput = document.getElementById('txt_nic_number');
	const dobInput = document.getElementById('date_date_of_birth');
	
	nicInput.addEventListener('input', function () {
		const nic = nicInput.value.trim();
		let year = '';
		let dayOfYear = '';
	
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
	
		if (dayOfYear > 500) {
			dayOfYear -= 500;
		}
	
		const date = new Date(year, 0, dayOfYear - 1);
		const yyyy = date.getFullYear();
		const mm = String(date.getMonth() + 1).padStart(2, '0');
		const dd = String(date.getDate()).padStart(2, '0');
	
		dobInput.value = `${yyyy}-${mm}-${dd}`; // Format required for input[type="date"]
	});
</script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
	//4. JS to confirm msg to multipurpose model
	const actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
	const modalTitle = document.getElementById('actionModalLabel');
	const modalBody = document.getElementById('actionModalBody');
	const modalConfirmBtn = document.getElementById('actionModalConfirmBtn');
	
	// 4.2 For duplicate check error
	function showDuplicateModal(message) {
		const modalElement = document.getElementById('actionModal');
		const modal = new bootstrap.Modal(modalElement);
	
		const modalTitle = document.getElementById('actionModalLabel');
		const modalBody = document.getElementById('actionModalBody');
		const modalConfirmBtn = document.getElementById('actionModalConfirmBtn');
	
		modalTitle.textContent = "Duplicate Detected";
		modalBody.textContent = message || "This value is already used.";
		modalConfirmBtn.textContent = "OK";
		modalConfirmBtn.className = "btn btn-primary btn-sm";
	
		// Close modal on click
		modalConfirmBtn.onclick = () => modal.hide();
	
		modal.show();
	}
</script>
<script>
	// 5.1 Duplicate check on blur
	document.querySelectorAll('.check-duplicate').forEach(input => {
		input.addEventListener('blur', function () {
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
					// feedback.textContent = "This name is already taken. Choose another";
					// input.classList.add("is-invalid");
				} else {
					feedback.textContent = "";
					input.classList.remove("is-invalid");
				}
			});
		});
	});
	
	// 5.2 Block form submission if any field is invalid
	document.querySelector('form').addEventListener('submit', function (e) {
		const invalidInputs = document.querySelectorAll('.check-duplicate.is-invalid');
		if (invalidInputs.length > 0) {
			e.preventDefault();
			showDuplicateModal("Please fix the issues before submitting.");
		}
	});
	
</script>
<script>
	// 6. Allow only numbers (e.g., for mobile/landline)
	function isNumberKey(evt) {
		const charCode = evt.which ? evt.which : evt.keyCode;
		return (charCode === 46 || (charCode >= 48 && charCode <= 57));
	}
</script>
<script>
	// 7. Allow only text (letters, space, delete, dot)
	function isTextKey(evt) {
		const charCode = evt.which ? evt.which : evt.keyCode;
		return (
			(charCode >= 65 && charCode <= 90) || // uppercase
			(charCode >= 97 && charCode <= 122) || // lowercase
			charCode === 8 || charCode === 127 || charCode === 32 || charCode === 46 // delete, backspace, space, dot
		);
	}
</script>
<script>
	//  8. Validate mobile number (starts with 07 and has 10 digits)
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
</script>
<script>
	// 9. Validate NIC and extract DOB
		function validateNIC(id) {
			const input = document.getElementById(id);
			const nic = input.value.trim();
	
			if (nic.length === 0) return;
	
			// 10-character NIC: 9 digits + V/v/X/x
			if (nic.length === 10) {
				if (!/^[0-9]{9}[vVxX]$/.test(nic)) {
					alert("NIC must be 9 digits followed by V/v/X/x");
					input.value = "";
					input.focus();
					return false;
				}
			}
			// 12-character NIC: all digits
			else if (nic.length === 12) {
				if (!/^[0-9]{12}$/.test(nic)) {
					alert("NIC must be exactly 12 digits");
					input.value = "";
					input.focus();
					return false;
				}
			}
			else {
				alert("NIC must be either 10 or 12 characters");
				input.value = "";
				input.focus();
				return false;
			}
	
			return true;
		}
</script>
<script>
	// 10. Validate Email
	function validateEmail(id) {
		const email = document.getElementById(id).value.trim();
		const regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
	
		if (email === "") return;
	
		if (!regex.test(email)) {
			alert("Invalid Email Address");
			document.getElementById(id).value = "";
			document.getElementById(id).focus();
			return false;
		}
	}
</script>
<script>
	// 11. Password visibility, Strength, Mismatch model show, and show inline.
	document.addEventListener("DOMContentLoaded", function () {
		const passwordInput = document.getElementById("txt_password");
		const confirmInput = document.getElementById("txt_confirm_password");
		const toggleButton = document.getElementById("togglePassword");
		const passwordIcon = document.getElementById("password-icon");
		const form = document.querySelector("form");
	
		const modal = new bootstrap.Modal(document.getElementById('passwordMismatchModal'));
		const modalBody = document.getElementById("passwordModalBody");
		const modalTitle = document.getElementById("passwordMismatchModalLabel");
	
		// Add strength display element
		const strengthDisplay = document.createElement("div");
		strengthDisplay.id = "password-strength";
		strengthDisplay.className = "mt-1 fw-semibold";
		passwordInput.closest('.input-group').after(strengthDisplay);
	
		// Toggle visibility
		toggleButton.addEventListener("click", function () {
			const type = passwordInput.type === "password" ? "text" : "password";
			passwordInput.type = type;
			confirmInput.type = type;
			passwordIcon.classList.toggle("bi-eye");
			passwordIcon.classList.toggle("bi-eye-slash");
		});
	
		// Password strength meter
		passwordInput.addEventListener("input", () => {
			const password = passwordInput.value;
			const strength = getPasswordStrength(password);
			updateStrengthDisplay(strength);
	
			// Visual feedback
			if (strength === "Strong") {
				passwordInput.classList.remove("is-invalid");
				passwordInput.classList.add("is-valid");
			} else {
				passwordInput.classList.remove("is-valid");
				passwordInput.classList.add("is-invalid");
			}
		});
	
		// Match check
		confirmInput.addEventListener("input", () => {
			if (confirmInput.value !== passwordInput.value) {
				confirmInput.classList.add("is-invalid");
			} else {
				confirmInput.classList.remove("is-invalid");
				confirmInput.classList.add("is-valid");
			}
		});
	
		//  Block form submit if invalid
		form.addEventListener("submit", function (e) {
			const password = passwordInput.value;
			const confirm = confirmInput.value;
			const strength = getPasswordStrength(password);
	
			if (password !== confirm) {
				e.preventDefault();
				confirmInput.classList.add("is-invalid");
				modalTitle.textContent = "Password Mismatch";
				modalBody.textContent = "The passwords you entered do not match. Please try again.";
				modal.show();
				return;
			}
	
			if (strength !== "Strong") {
				e.preventDefault();
				passwordInput.focus();
				modalTitle.textContent = "Weak Password";
				modalBody.textContent = "Password must be Strong: 8+ characters, with uppercase, lowercase, number, and symbol.";
				modal.show();
				return;
			}
		});
	
		// Strength checker
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
			const colors = {
				Weak: "red",
				Medium: "orange",
				Strong: "green"
			};
			strengthDisplay.textContent = `Password Strength: ${strength}`;
			strengthDisplay.style.color = colors[strength] || "black";
		}
	});
</script>
<script>
	// 12. Show T&C model
	document.addEventListener("DOMContentLoaded", function () {
	const acceptTermsCheckbox = document.getElementById("acceptTerms");
	const termsModal = new bootstrap.Modal(document.getElementById("termsModal"));
	let isModalConfirmed = false;
	
	// Trigger modal when checkbox is clicked
	acceptTermsCheckbox.addEventListener("click", function (e) {
		if (!isModalConfirmed) {
			e.preventDefault(); // Prevent checkbox from being checked
			termsModal.show();
		}
	});
	
	// "I do agree" button inside modal
	const agreeButton = document.querySelector("#termsModal .btn-secondary");
	agreeButton.addEventListener("click", function () {
		isModalConfirmed = true;
		acceptTermsCheckbox.checked = true;
		termsModal.hide();
	});
	});
</script>

<script>
	// 13. Login page Bootstrap modal with custom messages
	function showCustomAlert(message, title = 'Notice') {
		document.getElementById('customAlertLabel').innerText = title;
		document.getElementById('customAlertBody').innerHTML = message;
		
		var myModal = new bootstrap.Modal(document.getElementById('customAlertModal'));
		myModal.show();
</script>