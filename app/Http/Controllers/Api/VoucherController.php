<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::where('status', 1)->select('id', 'name', 'expiry_date')->orderBy('id', 'desc')->get()->toArray();
        return response()->json([
            'status' => 200,
            'vouchers' => $vouchers
        ], 200);
    }

    public function dashboard()
    {
        $stats = DB::table('vouchers')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN 1 ELSE 0 END) as total')
            ->selectRaw("SUM(CASE WHEN expiry_date > NOW() THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN expiry_date < NOW() THEN 1 ELSE 0 END) as expired")
            ->first();
        $issued = VoucherTracking::where('status', 1)->count();
        $voucherData = (object) array_merge((array) $stats, [
            'issued' => $issued
        ]);
        return response()->json([
            'message' => 'Stats fetched successfully',
            'status' => 200,
            'voucherData' => $voucherData,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'code' => ['required', 'string', Rule::unique('vouchers', 'code')->where('status', 1)],
            'currency' => 'required|string|max:255',
            'expiry_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        Voucher::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'currency' => $request->currency,
            'expiry_date' => $request->expiry_date
        ]);
        return response()->json([
            'status' => 201,
            'message' => 'Voucher Created Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $voucher = Voucher::find($id);
        return response()->json([
            'status' => 200,
            'voucher' => $voucher
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'code' => ['required', 'string'],
            'currency' => 'required|string|max:255',
            'expiry_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        $voucher = Voucher::find($id);
        $voucher->name = $request->name;
        $voucher->description = $request->description;
        $voucher->code = $request->code;
        $voucher->currency = $request->currency;
        $voucher->expiry_date = $request->expiry_date;
        $voucher->update();
        return response()->json([
            'status' => 200,
            'message' => 'Voucher Updated Successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $voucher = Voucher::find($id);
        if ($voucher) {
            $voucher->status = 0;
            $voucher->update();
            return response()->json([
                'status' => 200,
                'message' => 'Voucher Deleted Successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Voucher Found'
            ], 404);
        }
    }

    public function getActiveVouchers()
    {
        $vouchers = VoucherTracking::with(['user', 'voucher'])->where('status', 1)->get()->toArray();
        return response()->json([
            'status' => 200,
            'vouchers' => $vouchers
        ], 200);
    }
}
