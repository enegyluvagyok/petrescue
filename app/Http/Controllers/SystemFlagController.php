<?php

namespace App\Http\Controllers;

use App\Models\SystemFlag;
use Illuminate\Http\Request;

class SystemFlagController extends Controller
{
    public function setRestart($value)
    {
        $flag = SystemFlag::first();
        $flag->update(['restart' => (bool)$value]);

        return response()->json([
            'success' => true,
            'restart' => (int)$value
        ]);
    }

    public function getRestart()
    {
        $flag = SystemFlag::first();

        return response()->json([
            'restart' => (int)$flag->restart
        ]);
    }
}
