<?php

namespace App\Http\Controllers;

use App\Models\PipeWeight;
use App\Models\PipeSize;
use Illuminate\Http\Request;

class PipeDataController extends Controller
{
    public function getInfo($sizeId)
    {
        $size = PipeSize::find($sizeId);

        return response()->json([
            'pcs_per_bundle' => $size ? $size->pcs_per_bundle : 0,
        ]);
    }
}
