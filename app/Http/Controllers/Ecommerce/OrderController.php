<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use App\Payment;
use Carbon\Carbon;
use App\OrderReturn;
use Illuminate\Support\Str;
use DB;
use PDF;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::withCount(['return'])->where('customer_id', auth()->guard('customer')->user()->id)
            ->orderBy('created_at', 'DESC')->paginate(10);

        return response()->json([
            'data' => $orders,
            'status' => true,
            'message' => 'Order show all successfully'
        ]);        
    }

    public function view($invoice)
    {
        $order = Order::with(['district.city.province', 'details', 'details.product', 'payment'])
            ->where('invoice', $invoice)->first();
        
        if (\Gate::forUser(auth()->guard('customer')->user())->allows('order-view', $order)) {
            return view('ecommerce.orders.view', compact('order'));
        }
        
        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Order show all successfully'
        ]);
    }

    public function paymentForm()
    {
        return response()->json([
            'message' => 'Payment Form'
        ]);
    }

    public function storePayment(Request $request)
    {
        $this->validate($request, [
            'invoice' => 'required|exists:orders,invoice',
            'name' => 'required|string',
            'transfer_to' => 'required|string',
            'transfer_date' => 'required',
            'amount' => 'required|integer',
            'proof' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::where('invoice', $request->invoice)->first();
            if ($order->subtotal != $request->amount) return redirect()->back()->with(['error' => 'Error, Pembayaran Harus Sama Dengan Tagihan']);

            if ($order->status == 0 && $request->hasFile('proof')) {
                $file = $request->file('proof');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/payment', $filename);

                Payment::create([
                    'order_id' => $order->id,
                    'name' => $request->name,
                    'transfer_to' => $request->transfer_to,
                    'transfer_date' => Carbon::parse($request->transfer_date)->format('Y-m-d'),
                    'amount' => $request->amount,
                    'proof' => $filename,
                    'status' => false
                ]);
                $order->update(['status' => 1]);
                DB::commit();

                return response()->json([
                    'data' => $order,
                    'status' => true,
                    'message' => 'Pesanan dikonfirmasi'
                ]);
            }
            return response()->json([
                'data' => $order,
                'status' => false,
                'message' => 'Error upload bukti transfer'
            ]);
        } catch(\Exception $e) {
            DB::rollback();

            return response()->json([
                'data' => $e,
                'status' => false
            ]);
        }
    }

    public function acceptOrder(Request $request)
    {
        $order = Order::find($request->order_id);
        if (!\Gate::forUser(auth()->guard('customer')->user())->allows('order-view', $order)) {
            return redirect()->back()->with(['error' => 'Bukan Pesanan Kamu']);
        }

        $order->update(['status' => 4]);

        return response()->json([
            'data' => $order,
            'status' => true,
            'message' => 'Pesanan telah dikonfirmasi'
        ]);
    }

    public function returnForm($invoice)
    {
        $order = Order::where('invoice', $invoice)->first();

        return response()->json([
            'data' => $order,
            'status' => true
        ]);
    }

    public function processReturn(Request $request, $id)
    {
        $this->validate($request, [
            'reason' => 'required|string',
            'refund_transfer' => 'required|string',
            'photo' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        $return = OrderReturn::where('order_id', $id)->first();
        if ($return) return redirect()->back()->with(['error' => 'Permintaan Refund Dalam Proses']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/return', $filename);

            OrderReturn::create([
                'order_id' => $id,
                'photo' => $filename,
                'reason' => $request->reason,
                'refund_transfer' => $request->refund_transfer,
                'status' => 0
            ]);
            $order = Order::find($id);
            $this->sendMessage($order->invoice, $request->reason);

            return response()->json([
                'data' => $order,
                'status' => true,
                'message' => 'Pesanan refund dikirim'
            ]);
        }
    }
}
