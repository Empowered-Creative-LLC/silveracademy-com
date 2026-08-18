<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParentCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentCodeController extends Controller
{
    /**
     * Validate a parent code (public). Returns minimal student hint if valid.
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:32',
        ]);

        $result = ParentCodeService::validateCode($request->input('code'));
        if ($result === null) {
            return response()->json(['valid' => false]);
        }

        $student = $result['student'];
        $accessCode = $result['access_code'];

        if (! $accessCode->isValid()) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'student_hint' => ParentCodeService::studentHint($student),
        ]);
    }

    /**
     * First-time signup with email + code.
     */
    public function signup(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255',
            'code' => 'required|string|max:32',
        ]);

        $result = ParentCodeService::signup($request->input('email'), $request->input('code'));
        if (! $result['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $result['message'],
            ], 422);
        }

        $payload = [
            'ok' => true,
            'message' => $result['message'],
        ];

        if ($result['password'] !== null) {
            $payload['sandbox_password'] = $result['password'];
            $payload['sandbox_email'] = $result['email'];
        }

        return response()->json($payload);
    }
}
