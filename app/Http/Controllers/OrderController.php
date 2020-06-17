<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\Mail\OrderMail;
use Mail;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer.district.city.province'])
            ->withCount('return')
            ->orderBy('created_at', 'DESC');
        
        if (request()->q != '') {
            $orders = $orders->where(function($q) {
                $q->where('customer_name', 'LIKE', '%' . request()->q . '%')
                ->orWhere('invoice', 'LIKE', '%' . request()->q . '%')
                ->orWhere('customer_address', 'LIKE', '%' . request()->q . '%');
            });
        }

        if (request()->status != '') {
            $orders = $orders->where('status', request()->status);
        }
        $orders = $orders->paginate(10);

        return response()->json([
            'data' => $orders,
            'status' => true,
            'message' => 'Order show all successfully'
        ]);        
    }

    public function view($invoice)
    {
        $order = Order::with(['customer.district.city.province', 'payment', 'details.product'])->where('invoice', $invoice)->first();

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show order invoice successfully'
        ]);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        $order->details()->delete();
        $order->payment()->delete();
        $order->delete();
        
        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show order delete successfully'
        ]);
    }

    public function acceptPayment($invoice)
    {
        $order = Order::with(['payment'])->where('invoice', $invoice)->first();
        $order->payment()->update(['status' => 1]);
        $order->update(['status' => 2]);

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show payment invoice successfully'
        ]);
    }

    public function shippingOrder(Request $request)
    {
        $order = Order::with(['customer'])->find($request->order_id);
        $order->update(['tracking_number' => $request->tracking_number, 'status' => 3]);
        Mail::to($order->customer->email)->send(new OrderMail($order));

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show shipping order successfully'
        ]);
    }

    public function return($invoice)
    {
        $order = Order::with(['return', 'customer'])->where('invoice', $invoice)->first();

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show return order successfully'
        ]);
    }

    public function approveReturn(Request $request)
    {
        $this->validate($request, ['status' => 'required']);
        $order = Order::find($request->order_id);
        $order->return()->update(['status' => $request->status]);
        $order->update(['status' => 4]);

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Show return approval order successfully'
        ]);
    }
}
