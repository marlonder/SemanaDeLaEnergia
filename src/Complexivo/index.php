
<?php

$var = 1;
$var2 = -1 ;

$resultado = $var <=> $var2;

echo $resultado;


$a[0] = "MA";
$a[1] = "lon";


if($a[0] == "MA"){
    echo("es el dato querido");
}else{
    echo ("no lo es");
}


$i = 0;

while($i + 1){
    if($i == 11){
        break;
    }else{
        echo $i, $a[0] ,"<br>";
        $i++;
    }
}



?>