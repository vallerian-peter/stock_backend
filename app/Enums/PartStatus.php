<?php

namespace App\Enums;

enum PartStatus: string
{
    case IN_STOCK = "in_stock";
    case LOW_STOCK = "low_stock";
    case OUT_OF_STOCK = "out_of_stock";
}
