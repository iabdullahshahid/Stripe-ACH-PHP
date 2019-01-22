<?php 
$config = include('config.php');
$pKey = $config->stripe_public_key;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Stripe ACH</title>
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/custom.css">
	<link rel="icon" type="image/png" sizes="104x80" href="assets/img/logo.png">
</head>
<body>
	<div class="head-wrapper">
		<div class="container">
			<nav class="navbar navbar-light">
				<a class="navbar-brand" href="#">
					<img src="assets/img/logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
					Stripe ACH Payment
				</a>
			</nav>
		</div>
	</div>
	<section id="form">
		<div class="row">
			<div class="col-sm-6 offset-sm-3">
				<form id="acc_info_form">
					<fieldset>
						<legend class="text-center">Verification Form</legend>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="accHolderName">Acc Holder Name</label>
									<input type="text" name="acc_holder_name" class="form-control" id="accHolderName" placeholder="Jenny Rosen" required>
									<small>Account Holder Name: Jenny Rosen</small>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="accType">Acc Holder Type</label>
									<select class="form-control" name="acc_type" id="accType" required>
										<option value="">Select type</option>
										<option value="individual">Individual</option>
										<option value="company">Company</option>
									</select>
									<small>Account Type: Individual</small>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="accNo">Acc Number</label>
									<input type="text" name="acc_number" class="form-control" id="accNumber" placeholder="xxxxxxxxxxxxxx" required>
									<small>Account Number: 000123456789</small>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="accRoutingNumber">Routing Number</label>
									<input type="text" name="acc_routing_number" class="form-control" id="accRoutingNumber" placeholder="xxxxxxxxxxxxxx" required>
									<small>Account Routing Number: 110000000</small>
								</div>
							</div>
							<div class="col-sm-12">
								<a href="javascript:;" type="submit" id="sbmtBtn" class="btn btn-success pull-right">Verify</a>
							</div>
						</div>						
					</fieldset>
				</form>
			</div>
		</div>
	</section>
	<section id="payButton" style="">
		<div class="row">
			<div class="col-sm-6 offset-sm-3">
				<input type="hidden" name="payment" id="payment" value="2500">
				<button id="pay" class="button">Pay $25</button>
			</div>
		</div>
	</section>
	<footer class="footer">
		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<div class="text-center">Design and develop by <a href="https://github.com/iabdullahshahid/Stripe-ACH-PHP" class="text-primary"><b>ABDULLAH SHAHID</b></a></div>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
<script
src="assets/js/jquery.min.js"
integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
crossorigin="anonymous"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="assets/js/sweetalert.min.js"></script>
<script>
	$(document).ready(function(){
		
		$('#sbmtBtn').click(function(){
			var stripe = Stripe('<?php echo $pKey; ?>');
			var accName = $('#accHolderName').val();
			var accType = $('#accType').val();
			var accNumber = $('#accNumber').val();
			var accRoutingNumber = $('#accRoutingNumber').val();
			if (accName == '' || accType == '' || accNumber == '' || accRoutingNumber == '') {
				swal("Oops!", "Please fill all the fields.", "error");
				return false;
			}
			stripe.createToken('bank_account', {
				country: 'US',
				currency: 'usd',
				routing_number: accRoutingNumber,
				account_number: accNumber,
				account_holder_name: accName,
				account_holder_type: accType,
			}).then(function(result) {

				if (result.token) {

					var bankAccountID = result.token.bank_account.id;
					var token = result.token.id;
					swal({
						title: "Please Wait",
						text: "Your request is in process...",
						icon: "info",
						buttons: false
					});
					$.ajax({
						type: 'post',
						url: 'process.php',
						data: {bankAccountID: bankAccountID, token: token, accName: accName, action: "verification"},
						success:function(response)
						{
							response = JSON.parse(response);
							if (response.status == 1) {
								swal("Success!", "Bank verified please click the pay button.", "success");
								$('#form').hide();
								$('#payButton').show();
							}
							else
							{
								swal("Oops!", "Something went wrong please check your provided information.", "error");
							}
						}
					});

				}
				else
				{
					swal("Oops!", result.error.message, "error");
				}
			});
		});

		$('#pay').click(function(){
			var amount = $('#payment').val();
			swal({
				title: "Please Wait",
				text: "Payment is in process...",
				icon: "info",
				buttons: false
			});
			$.ajax({
				type: 'post',
				url: 'process.php',
				data: {amount: amount, action: "payment"},
				success:function(response)
				{
					response = JSON.parse(response);
					console.log(response);
					if (response.status == 1) {
						swal("Success!", "Payment has been done successfully.", "success");
						$('#payButton').hide();
						$('#acc_info_form')[0].reset();
						$('#form').show();
					}
					else
					{
						swal("Oops!", "Something went wrong in payment processing.", "error");
					}
				}
			});
		});
	});
</script>