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
        Employee Code: <?php echo  $emp_id ?><br />
        <br />
        You have been Assigned as admin for the portals below
        <br /><br />

        Portal Names : @foreach ($portal_names as $element)
        {{ $element }}<br>
        @endforeach<br />



    </p><br />
    <br />

    <p>
        Thanks & Regards <br />
        Frontiers

    </p>

</body>

</html>