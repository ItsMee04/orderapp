<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $order = Order::select('id', 'customer_name', 'table_no', 'order_date', 'order_time', 'status', 'total')->get();
        return response()->json(['data' => $order, 'detail' => "Data Ditemukan"]);
    }

    public function show($id)
    {
        $order = Order::select('id', 'customer_name', 'table_no', 'order_date', 'order_time', 'status', 'total', 'waiters_id', 'cashier_id')->where('id', $id)->get();
        $detailOrder = $order->loadMissing('orderDetail:order_id,item_id,price', 'orderDetail.item:name,id', 'waiters:id,name', 'cashier:id,name');
        return response()->json(['data' => $detailOrder, 'detail' => 'Data Ditemukan']);
    }

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
            $data['total'] = 0;
            $data['waiters_id'] = Auth::user()->id;
            $data['items'] = $request->items;

            $order = Order::create($data);

            collect($data['items'])->map(function ($item) use ($order) {
                $foodDrink = Item::where('id', $item)->first();

                OrderDetail::create([
                    'order_id'  =>  $order->id,
                    'item_id'   =>  $item,
                    'price'     =>  $foodDrink->price
                ]);
            });

            $order->total = $order->sumOrderPrice();
            $order->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th);
        }

        return response()->json(['data' => $data], 200);
        // return response()->json(['data' => $order], 200);
    }

    public function doneOrder($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status != "ordered") {
            return response()->json('Order cannot accept to Done because status is not ordered', 403);
        }

        $order->status = "Done";
        $order->save();

        return response()->json(['Data' => $order, 'detail' => 'Status Diubah']);
    }

    public function payment($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status != "Done") {
            return response()->json('Payment cannot be done because status is not done or order payment has been received', 403);
        }

        $order->status = "Paid";
        $order->save();

        return response()->json(['Data' => $order, 'detail' => 'Status Diubah']);
    }
}
