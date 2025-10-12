<?php

namespace App\Features;

class Employee
{
    public $name = 'employee';

    public function resolve(): mixed
    {
        return true;
    }
}
