<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\MedicineOutgoingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class MedicineOutgoingController extends Controller
{
    protected MedicineOutgoingRepository $medicineOutgoingRepository;

    public function __construct(MedicineOutgoingRepository $medicineOutgoingRepository)
    {
        $this->medicineOutgoingRepository = $medicineOutgoingRepository;
    }

    public function index(string $lang, Request $request)
    {
        App::setLocale($lang);
        try {
            $id_user = $request->user()->id;
            $level_user = $request->user()->level;
            $access = (new \App\Http\Controllers\FunctionController)->checkACL($level_user, $id_user, 'medicine_outgoing', 'read');
            if ($access == false) {
                return ResponseHelper::error(trans('validation.unauthorizad'));
            }

            $response = $this->medicineOutgoingRepository->index($request, $id_user);
        } catch (\Exception $error) {
            return ResponseHelper::error($error->getMessage());
        }

        try {
            $params = [];
            $log = (new \App\Http\Controllers\FunctionController)->log(
                $request->user()->level,
                $request->user()->id,
                'list',
                'medicine_outgoing',
                $request->path(),
                $params,
                app('Illuminate\Http\Response')->status()
            );
        } catch (\Exception $error_log) {
        }

        return ResponseHelper::success($response);
    }

    public function show($lang, $id = 1, Request $request)
    {
        App::setLocale($lang);
        $params = [$id];
        try {
            $id_user = $request->user()->id;
            $level_user = $request->user()->level;
            $access = (new \App\Http\Controllers\FunctionController)->checkACL($level_user, $id_user, 'medicine_outgoing', 'read');
            if ($access == false) {
                return ResponseHelper::error(trans('validation.unauthorized'));
            }

            $data = $this->medicineOutgoingRepository->show($id, $request, $id_user);
            $id_clinic = $data["id_clinic"];
            $medicine_outgoing = $data["medicine_outgoing"];

            if ($medicine_outgoing == false) {
                return ResponseHelper::error(trans('validation.data_not_found'));
            } else {
                $medicine = $data["medicine"];
                if ($medicine->id_clinic != $id_clinic) {
                    return ResponseHelper::error(trans('validation.data_not_found'));
                } else {
                    $medicine_outgoing['error'] = false;
                    $response = $medicine_outgoing;
                }
            }
        } catch (\Exception $error) {
            return ResponseHelper::error($error->getMessage());
        }

        try {
            $log = (new \App\Http\Controllers\FunctionController)->log(
                $request->user()->level,
                $request->user()->id,
                'get',
                'medicine_outgoing',
                $request->path(),
                $params,
                app('Illuminate\Http\Response')->status()
            );
        } catch (\Exception $error_log) {
        }

        return ResponseHelper::success(array($response));
    }

    public function test($lang, Request $request)
    {
        //try{
        App::setLocale($lang);

        $id_user = $request->user()->id;
        $level_user = $request->user()->level;
        $access = (new \App\Http\Controllers\FunctionController)->checkACL($level_user, $id_user, 'medicine_outgoing', 'create');
        if ($access == false) {
            return ResponseHelper::error(trans('validation.unauthorized'));
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'id_medicine' => 'required',
            'quantity' => 'required'
        ]);

        $errors = array();
        $data = array();

        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                foreach ($messages as $message) {
                    $errors[$field_name] = $message;
                }
            }
        }

        if (!empty($errors)) {
            $data['success'] = false;
            $data['error'] = false;
            $data['errors'] = $errors;
            $data['message'] = (new \App\Functions)->error($errors);
        } else {
            $data['success'] = true;
            $data['error'] = false;
        }

        $validate = $request->input('validate');

        if ($validate != "true" && $data['success'] == true) {
            $result = $this->medicineOutgoingRepository->test($data, $input);
            $data = $result['data'];
            $data_outgoing = $result['data_outgoing'];
            $data['data'] = $data_outgoing;
        }

        $response = $data;

        try {
            $params = [];
            $log = (new \App\Http\Controllers\FunctionController)->log(
                $request->user()->level,
                $request->user()->id,
                'create',
                'medicine_incoming',
                $request->path(),
                $params,
                app('Illuminate\Http\Response')->status()
            );
        } catch (\Exception $error_log) {
        }

        return response()->json($response);
    }
}
