<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherTracking;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->select('id', 'name', 'email')->get()->toArray();
        return response()->json([
            'status' => 200,
            'users' => $users,
            'message' => 'Users fetched successfully'
        ], 200);
    }

    public function issueVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
        ]);

        $voucherExists = Voucher::where('id', $request->voucher_id)
            ->where('status', 1)
            ->exists();

        if (!$voucherExists) {
            return response()->json([
                'status' => 404,
                'message' => 'Voucher not found or inactive'
            ], 404);
        }
        $existingUserIds = VoucherTracking::where('voucher_id', $request->voucher_id)
            ->pluck('user_id')
            ->toArray();
        $toRemove = array_diff($existingUserIds, $request->user_id);
        if ($toRemove) {
            VoucherTracking::where('voucher_id', $request->voucher_id)
                ->whereIn('user_id', $toRemove)
                ->delete();
        }
        foreach ($request->user_id as $userId) {
            $userExists = User::where('id', $userId)->where('role', 'user')->exists();
            $voucherUserExists = VoucherTracking::where('voucher_id', $request->voucher_id)->where('user_id', $userId)->exists();
            if ($userExists && !$voucherUserExists) {
                VoucherTracking::create([
                    'user_id'    => $userId,
                    'voucher_id' => $request->voucher_id,
                    'status'     => 1
                ]);
            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Voucher issued to selected users successfully'
        ], 201);
    }

    public function getVoucherUsers($voucherId)
    {
        $users = VoucherTracking::where('voucher_id', $voucherId)->where('status', 1)->pluck('user_id')->toArray();
        return response()->json([
            'status' => 200,
            'users' => $users,
            'message' => 'Users with the specified voucher fetched successfully'
        ], 200);
    }
}
