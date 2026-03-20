<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Professional;

class AdminController extends Controller
{
    public function approveProfessional($id)
    {
        $professional = Professional::find($id);

        if (!$professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'approved';
        $professional->save();

        return response()->json([
            'message' => 'Professional approved'
        ]);
    }

    public function rejectProfessional($id)
    {
        $professional = Professional::find($id);

        if (!$professional) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $professional->status = 'rejected';
        $professional->save();

        return response()->json([
            'message' => 'Professional rejected'
        ]);
    }
}