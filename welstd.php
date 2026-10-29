<html>
<head>
<title>User Registration</title>
</head>
<div class="main">
<h2>Student submission form</h2>
<?php
if(isset($_POST['submit'])){
$fn=$_POST['fname'];
$ln=$_POST['lname'];
$ad=$_POST['add'];
$em=$_POST['email'];
$bg=$_POST['bggroup'];
$mb=$_POST['mob'];
$cty=$_POST['city'];
$st=$_POST['state'];
$gn=$_POST['gender'];
$hb=$_POST['hobbies'];
echo "First name:$fn<br>";
echo "Last name:$ln<br>";
echo "Address:$ad<br>";
echo "Email:$em<br>";
echo "Blood group:$bg<br>";
echo "Mobile:$mb<br>";
echo "City:$st<br>";
echo "State:$st<br>";
echo "Gender:$gn<br>";
echo "<h3>Hobbies</h3>";
$i=0;
while($i<sizeof($hb))
{
echo $hb[$i]."<br>";
$i++;
}
}
?>
</div>
</html>











