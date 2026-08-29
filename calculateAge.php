<?php
//Ths function will be used to calcualte the age using birthDate
//Include_once this then call the function
function calculateAge($birthDate)
{
    $birthDateObj = new DateTime($birthDate);
    $today = new DateTime('today');
    $age = $birthDateObj->diff($today)->y;
    return $age;
}
