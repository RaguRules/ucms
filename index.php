<?php
	include_once('menu.php');
	include_once('db.php');
	// require_once ('security.php');

?>


<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title>Kilinochchi Courts Management System</title>
		<meta name="description" content="Kilinochchi Courts Management System for efficient case management, scheduling, and document handling.">
		<meta name="keywords" content="Kilinochchi Courts, Case Management, Court System, Legal System">
		<link href="assets/img/favicon.png" rel="icon">
		<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
		<link href="https://fonts.googleapis.com" rel="preconnect">
		<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
		<!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> -->
		<link rel="stylesheet" href="assets/css/font.css">
		<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
		<link href="assets/vendor/aos/aos.css" rel="stylesheet">
		<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
		<link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
		<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
		<link href="assets/css/main.css" rel="stylesheet">
		<link href="assets/css/ragu.css" rel="stylesheet">
	</head>
	<body>

	<script>
	function isNumberKey(evt) // only numbers to allow the input field
   	{
      var charCode = (evt.which) ? evt.which : event.keyCode;
      if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
         return false;

      	 return true;
   	}
</script>
<script>
	 //text validation
	function isTextKey(evt) // only text to allow the input field
   	{
      var charCode = (evt.which) ? evt.which : event.keyCode;
      if (((charCode >64 && charCode < 91)||(charCode >96 && charCode < 123)||charCode ==08 || charCode ==127||charCode ==32||charCode ==46)&&(!(evt.ctrlKey&&(charCode==118||charCode==86))))
         return true;
		
      	 return false;
   	}
</script>
<script>
    //mobile number validation
   function phonenumber(mobile_text_box_name) // Mobile No 
	{
		var phoneno = /^\d{10}$/;
		if(document.getElementById(mobile_text_box_name).value=="")
		{
		}
		else
		{
			if( document.getElementById(mobile_text_box_name).value.match(phoneno))
			{
				hand(mobile_text_box_name);
			}
			else
			{
				alert("Enter 10 digit Mobile Number");
				document.getElementById(mobile_text_box_name).value="";
				document.getElementById(mobile_text_box_name).focus()=true;		
				return false;
			}
		}	 
	}
	function hand(mobile_text_box_name)
	{
		var str = document.getElementById(mobile_text_box_name).value;
		var res = str.substring(0, 2);
		if(res=="07")
		{
			return true;
		}
		else
		{
				alert("Enter 10 digit of Mobile Number start with 07xxxxxxxx");
				document.getElementById(mobile_text_box_name).value="";
				document.getElementById(mobile_text_box_name).focus()=true;			
				return false;
		}
		
	}
</script>
<script>
//nic validation
function nicnumber(txt_nic_number)
	{
		var nic=document.getElementById(txt_nic_number).value;
		if(nic.length==10)
		{
			var nicformat1=/^[0-9]{9}[a-zA-Z0-9]{1}$/;
			if(nic.match(nicformat1))
			{
				var nicformat2=/^[0-9]{9}[vVxX]{1}$/;
				if(nic.match(nicformat2))
				{
					//calculatedob(nic);
				}
				else
				{
					alert("Last character must be V/v/X/x");
					document.getElementById(txt_nic_number).value="";
					document.getElementById(txt_nic_number).focus();
				}
			}
			else
			{
				alert("First 9 characters must be Numbers");
				document.getElementById(txt_nic_number).value="";	
				document.getElementById(txt_nic_number).focus();
			}	
		}
		else if(nic.length==12)
		{		
			var nicformat3=/^[0-9]{12}$/;
			if(nic.match(nicformat3))
			{
				//calculatedob(nic);
			}
			else
			{
				alert("All 12 characters must be Number");
				document.getElementById(txt_nic_number).value="";
				document.getElementById(txt_nic_number).focus();
			}
		}
		else if(nic.length==0)
		{
			//document.getElementById("txt_dob").value="";
			//document.getElementById("txt_gender").value ="NO";
		}
		else
		{
			alert("NIC No must be 10 or 12 Characters");
			document.getElementById(txt_nic_number).value="";
			document.getElementById(txt_nic_number).focus();
		}
	}
</script>
<script>
function emailvalidation(email_text_box_name)
	{
		var email=document.getElementById(email_text_box_name).value;
		var emailformat=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		// var get_page_name=<?php //echo json_encode($page_name); ?>;
		if (email.match(emailformat))
		{
			//if(get_page_name=="Student"|| get_page_name=="Register" || get_page_name=="Staff" || get_page_name=="Inquire")
			//{
				//check_email_username(email);//call validate check_email_username function
			//}			
		}
		else if(email.length==0)
		{
			
		}
		else
		{
			alert("Email Address is Invalid");
			document.getElementById(email_text_box_name).value="";
			document.getElementById(email_text_box_name).focus();
		}		
	}

</script>








		<?php
			if(isset($_GET['pg'])){
			    $page = $_GET['pg'];
			    include_once("$page");
			}else{
			    include_once("body.php");
			    include_once("footer.php");
			}
			?>
		<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/php-email-form/validate.js"></script>
		<script src="assets/vendor/aos/aos.js"></script>
		<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
		<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
		<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
		<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
		<script src="assets/js/flatpickr.js"></script>
		<!-- <script src="assets/js/main.js"></script> -->
	</body>







	<!-- <script>
  const emailInput = document.getElementById("txt_email");
  const errorSpan = document.getElementById("emailError");
  const form = document.getElementById("staffForm");

  // Validate on blur
  emailInput.addEventListener("blur", function () {
    validateEmail();
  });

  // Prevent form submit if invalid
  form.addEventListener("submit", function (e) {
    if (!validateEmail()) {
      e.preventDefault(); // Prevents form submission
    }
  });

  function validateEmail() {
    const email = emailInput.value.trim();
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!regex.test(email)) {
      errorSpan.textContent = "Invalid email address";
      return false;
    } else {
      errorSpan.textContent = "";
      return true;
    }
  }
</script> -->
</html>