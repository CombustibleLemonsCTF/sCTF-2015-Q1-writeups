<?

$randuser = bin2hex(mcrypt_create_iv(40, MCRYPT_DEV_URANDOM));
$randpass = bin2hex(mcrypt_create_iv(40, MCRYPT_DEV_URANDOM));

$flag = "HIDDEN";
$antiflag = "FAILED LOGIN";

//HIDDEN CONNECTION CODE//
$res = $connection->query("INSERT INTO temp_info (`user`,`pass`) VALUES (\"" . $randuser . "\",\"" . $randpass . "\");");

echo "YOUR RANDOM USER: " . $randuser . "; YOUR RANDOM PASS: *HIDDEN*<br><br>";

if(isset($_POST['user']) && isset($_POST['pass'])) {
	//HIDDEN CONNECTION CODE//
	if($connection->connect_error) {
		die("Connection failed: " . $connection->connect_error);
	}
	$res = $connection->query("SELECT `user`,`pass` FROM temp_info WHERE `user`=\"" . $_POST['user'] . "\" AND `pass`=\"" . $_POST['pass'] . "\"");
	if($res->num_rows > 0) {
		echo $flag;
	} else {
		echo $antiflag;
	}
}

?>
<form method="post" action="">
<input type="text" name="user" placeholder="User">
<br>
<input type="password" name="pass" placeholder="Pass">
<br>
<input type="submit" value="Login">
</form>
<br>
<a href="source.txt">Source</a>