<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' =>  'required|max:100',
            'table_no'      =>  'required|max:5',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only('customer_name', 'table_no');
            $data['order_date'] = date('Y-m-d');
            $data['order_time'] = date('H:i:s');
            $data['status'] = "ordered";
            $data['total'] = 15000;
            $data['waiters_id'] = Auth::user()->id;

            $order = Order::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th);
        }



        return response()->json(['data' => $order], 200);
    }
}
