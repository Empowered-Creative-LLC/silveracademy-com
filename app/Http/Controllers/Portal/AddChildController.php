<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\ParentCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddChildController extends Controller
{
    /**
     * Add a child to the current account using a parent code.
     * Parents and staff (including staff who are also parents) can link children.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isParent() && !$user->isStaff()) {
            abort(403, 'Only parent or staff accounts can link students.');
        }

        $request->validate([
            'code' => 'required|string|max:32',
        ]);

        $result = ParentCodeService::validateCode($request->input('code'));
        if ($result === null) {
            return response()->json([
                'message' => 'Invalid code or this student has reached the maximum number of linked accounts. Contact the school office.',
            ], 422);
        }

        $student = $result['student'];
        $accessCode = $result['access_code'];

        if (!$accessCode->isValid()) {
            return response()->json([
                'message' => 'This student already has the maximum number of linked accounts. Contact the school office.',
            ], 422);
        }

        if ($user->children()->where('students.id', $student->id)->exists()) {
            return response()->json([
                'message' => 'You have already linked this student.',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'grade' => $student->grade ? ['name' => $student->grade->name] : null,
                ],
            ]);
        }

        $user->children()->attach($student->id);

        return response()->json([
            'message' => 'Child added successfully.',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => $student->grade ? ['name' => $student->grade->name] : null,
            ],
        ]);
    }
}
