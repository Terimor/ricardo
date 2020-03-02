<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use Illuminate\Http\Request;

class OdinCustomerController extends Controller
{
    public function addOrUpdate(Request $request) {
        $data = $request->input();
        $model = OdinCustomer::query()->FirstOrNew(['email' => strtolower($data['email'])]);
        $model->ip = array_unique(array_merge($model->ip, $request->ips()));

        if (!$model->exists) {
            $model->last_viewed_sku_code = $data['sku'];
            $model->last_page_checkout = $data['page'];
            $model->language = substr(app()->getLocale(), 0, 2);
        }
        if ($model->type == 'lead') {
            $model->first_name = $data['first_name'];
            $model->last_name = $data['last_name'];
        }
        if (isset($data['fingerprint'])  && is_string($data['fingerprint'])) {
            $model->fingerprint = $data['fingerprint'];
        }
        if (isset($data['phone']) && is_array($data['phone']) && array_filter($data['phone'])) {
            $model->phones = array_unique(array_merge($data['phone'], $model->phones));
        }

        $validator = $model->validate();
        if ($validator->fails()) {
            return response()->json([
                'errors'=>$validator->errors()->messages(),
                'success' => false
            ]);
        }
       $model->save();
       return response()->json([
           'success' => true,
           'customer' => $model->attributesToArray()
       ]);
    }
}
