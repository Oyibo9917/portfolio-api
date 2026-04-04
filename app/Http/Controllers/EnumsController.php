<?php

namespace App\Http\Controllers;

use App\Enums\InventoryStatus;
use App\Enums\ProductCategories;


class EnumsController extends Controller
{
    public function getAllEnums()
    {
        $enums = [
            'inventory_status' => InventoryStatus::values(),
            'product_categories' => ProductCategories::values(),
        ];

        return response()->json(['data' => $enums]);
    }
}