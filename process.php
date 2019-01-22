<?php 
$config = include('config.php');
$sKey = $config->stripe_secret_key;

require_once('vendor/autoload.php');
session_start();
\Stripe\Stripe::setApiKey($sKey);
if ($_POST['action'] == "verification") {

	$bankAccountID = $_POST['bankAccountID'];
	$token = $_POST['token'];
	$accName = $_POST['accName'];
	try {
		$customer = \Stripe\Customer::create([
			"source" => $token,
			"description" => $accName
		]);
		$_SESSION['customerID'] = $customer->id;
		# get the existing bank account
		$customer = \Stripe\Customer::retrieve($customer->id);
		$bankAccount = $customer->sources->retrieve($bankAccountID);

		# verify the account
		$bankAccount->verify(['amounts' => [32, 45]]);

		if ($bankAccount) {
			# status (error: 0, success: 1)
			echo json_encode(['status' => 1]);
			exit();
		}
		echo json_encode(['status' => 0]);
		exit();
	} catch (Exception $e) {
		echo $e;
		exit();
	}
}
else
{
	$amount = $_POST['amount'];
	try {

		$transaction = \Stripe\Charge::create([
			"amount" => $amount,
			"currency" => "usd",
			"customer" => $_SESSION['customerID']
		]);

		if ($transaction) {
			# status (error: 0, success: 1)
			echo json_encode(['status' => 1]);
			exit();
		}
		else
		{
			echo json_encode(['status' => 0]);
			exit();
		}
	} catch (Exception $e) {
		echo $e;
		exit();
	}
	session_destroy();
}
?>