<?php

namespace App\Features;

class Expense
{
    public $name = 'expense';

    public function resolve(): mixed
    {
        return true;
    }
}
