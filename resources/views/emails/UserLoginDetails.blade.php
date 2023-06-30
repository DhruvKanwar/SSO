<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
</head>

<body>
    <?php     //dd($employee_id) 
    ?>

    <p>
        Dear <?php echo $name ?>,<br />
        Employee Code: <?php echo  $employee_id ?><br />
        <br />
        Your Credentials for Login to SSO portal has been Created Successfully...
        <br /><br />
        Your Credentials for Login are Below : <br />
        Unique ID : <?php echo  $sso_unid ?><br />
        Employee ID : <?php echo  $employee_id ?><br />
        Phone No. :<?php echo  $phone ?><br />
        Email :<?php echo  $email ?><br /><br />

        Note: You can use above Mentioned Login for authorization and Your Password will be your
        Date of Birth. <br />

        Password :<?php echo  $login_password ?><br />


    </p><br />
    <br />

    <p>
        Thanks & Regards <br />
        Frontiers

    </p>

</body>

</html>