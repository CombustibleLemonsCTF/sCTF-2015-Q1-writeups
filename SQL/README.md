# sCTF 2015 Q1: SQL

**Points:** 60
**Description:**

> http://192.99.244.134:9898/login.php

## Write-up

Here is the PHP source provided: 

```php
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
```

The random credentials generation at the top and the HTML at the bottom can be ignored. Basically, this problem is looking for users with the correct username and password. The username is provided, but the password is redacted.

It turns out that we don't need either because our input is not filtered, leaving the application vulnerable to SQL injection. This means that our input is interpreted as part of the SQL query; we can manipulate this query so that the condition is always true and that it returns every user, giving us the flag.

You don't need to know much about SQL, just boolean logic. First, we will need a `"` in `$_POST['user']` so that that part of the condition reads like `user=""`. To guaruntee that the condition is true, we can add ` OR 1=1` (an `OR` with anything true will be true). To ignore the rest of the query, we can use ` -- `, an SQL comment (note that there should be a space at the end of the two dashes). Putting this altogether, we get `" OR 1=1 -- ` for `user` and nothing for `pass`, which will be commented out anyways.

The message that we get is `flag: must_secure_sql`.

## Other write-ups and resources

* https://docs.google.com/presentation/d/1Zj3qMvDRBEnnh9EFyEf0bUgSTc6g2o0ZxGm6QdHTE4o/edit#slide=id.p
