<html>
    <head>
    <!-- <link href="assets/css/bootstrap.min.css" rel="stylesheet"> -->
    <script type="assets/js/javascript" src="js/jquery.min.js"></script>
</head>

<?php
	include_once('menu.php');
?>

<!-- <html>
    <div class="alert alert-info" role="alert" align-items-center style="
        border-left-width: 2px;
        margin-left: 500px;
        margin-right: 500px;
    ">
        <strong><centre>Staff Page<centre></strong>
    </div> -->

<?php
	if(isset($_GET['option'])){
        $option = $_GET['option'];
		if($option == "view"){
            echo "<script>alert('Hey')</script>";

        }elseif($option == "add"){
        ?>

<form>
  <div class="mb-3">
    <label for="exampleInputEmail1" class="form-label">Email address</label>
    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
  </div>
  <div class="mb-3">
    <label for="exampleInputPassword1" class="form-label">Password</label>
    <input type="password" class="form-control" id="exampleInputPassword1">
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="exampleCheck1">
    <label class="form-check-label" for="exampleCheck1">Check me out</label>
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
</form>



            
        <?php
        }elseif($option == "edit"){

        }elseif($option == "fullview"){

        }elseif($option == "delete"){

        }else{
            header("Location: localhost/icms/index.php", true, 301);
        }

	}else{
        // header("Location: /icms/index.php");
        echo "<script> location.href='index.php'; </script>";
        exit; 
	}
?>

</html>