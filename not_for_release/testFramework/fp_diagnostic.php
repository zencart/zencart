<?php
error_reporting(E_ALL & ~E_DEPRECATED);

echo "PHP: " . PHP_VERSION . "  (int-size=" . PHP_INT_SIZE . ")\n";
echo "precision=" . ini_get("precision") .
    "  serialize_precision=" .
    ini_get("serialize_precision") .
    "\n";

$a = 69.99;
$b = 0.175;
$p = $a * $b;

printf("a=%.17f\n", $a);
printf("b=%.17f\n", $b);
printf("a*b=%.17f\n", $p);
echo "round(a*b,4) = " . round($p, 4) . "\n";

