<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@Symfony' => true,
];

$finder = (new Finder())
    ->in(__DIR__);

return (new Config())
    ->setRules($rules)
    ->setFinder($finder);
