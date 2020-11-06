<?php

namespace App\Http\Controllers;

use App\Http\Resources\SerialNumberResource;
use Illuminate\Http\Request;
use App\SerialNumber;
use Carbon\Carbon;

class SerialNumberController extends Controller
{
    //list serial number
    public function index(): SerialNumberResource
    {
        $serial_number = SerialNumber::where('deleted', null)->paginate();
        return new SerialNumberResource($serial_number);
    }

    //add serial number
    public function store(Request $request): SerialNumberResource
    {
        $request->validate([
            'purchase_order_id' => 'required',
            'serial_number' => 'required',
            'number_of_items' => 'required',
        ]);

        $purchase_order_id = $request->purchase_order_id;
        $number_of_items = $request->number_of_items;
        $serial_number_new = $request->serial_number;
        $s = array();
        for ($i = 1; $i <= $number_of_items; $i++) {

            $s[$i] = $serial_number_new . $i;
            $request->merge([
                'serial_number' => $serial_number_new . $i,
            ]);
            $serial_number = SerialNumber::create($request->all());
        };
        $serial_number = SerialNumber::where(['deleted' => null, 'purchase_order_id' => $purchase_order_id])->paginate();
        return new SerialNumberResource($serial_number);
    }

    //list serial number
    public function showForPurchaseOrder($id): SerialNumberResource
    {
        $serial_number = SerialNumber::where(['deleted' => null, 'purchase_order_id' => $id])->orderBy('created_at', 'desc')->paginate(200);
        return new SerialNumberResource($serial_number);
    }

    public function delete($id)
    {
        $serial_number = SerialNumber::findOrFail($id);
        $purchase_order_id = $serial_number->purchase_order_id;
        $serial_number->deleted = \Carbon\Carbon::now();
        $serial_number->save();
        return response()->json(['data' => ['message' => 'Deleted', 'id' => $purchase_order_id]]);
    }


    /** API to activate serial by a user
     * 
     */
    public function activate(Request $request, $serial_number)
    {
        $serial_number_object = SerialNumber::where('serial_number', $serial_number)->firstOrFail();
        if ($serial_number_object->activated_by == NULL) {
            $serial_number_object->activated_by = auth()->user()->id;
            $serial_number_object->longitude = $request->longitude;
            $serial_number_object->latitude = $request->latitude;
            $serial_number_object->warranty_started =
                \Carbon\Carbon::now();

            $serial_number_object->save();

            $message = 'Serial Number Is Activated';
        } else {
            $message = 'Cannot be activated. Contact info@ledinaction.com';
        }


        return response()->json(['message' => $message]);
    }
}