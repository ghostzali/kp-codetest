<?php

namespace App\Http\Controllers\V1;

use App\Database;
use App\Helpers\PaginationHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineOutgoing;
use App\Repositories\MedicineOutgoingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MedicineOutgoingController extends Controller
{
    protected MedicineOutgoingRepository $medicineOutgoingRepository;

    public function __construct(MedicineOutgoingRepository $medicineOutgoingRepository)
    {
        $this->medicineOutgoingRepository = $medicineOutgoingRepository;
    }

    public function index (string $lang, Request $request) {
        try {
            App::setLocale($lang);

            $id_user = $request->user()->id;
            $level_user = $request->user()->level;
            $access = (new \App\Http\Controllers\FunctionController)->checkACL($level_user, $id_user, 'medicine_outgoing', 'read');
            if ($access == false) {
                return ResponseHelper::error(trans('validation.unauthorizad'));
            }

            $id_clinic = (new \App\Http\Controllers\FunctionController)->getIDClinicUser($id_user);

            $q = $request->input('q');
            $per_page = $request->input('per_page');
            $is_dpho = false;

            if (empty($request->input('is_dpho'))) {
                $is_dpho = null;
            } else if ($request->input('is_dpho') == 'true') {
                $is_dpho = true;
            }

            if (config('app.paginate')) {
                $per_page = config('app.paginate_per_page');
            }

            $orderby = 'date';
            $ordertype = 'desc';

            $query = MedicineOutgoing::select((new Database)->medicine_outgoing())
                ->join('medicine', 'medicine.id', '=', 'medicine_outgoing.id_medicine')
                ->join('units', 'units.id', '=', 'medicine_outgoing.id_unit')
                ->where('medicine.id_clinic', '' . $id_clinic . '')
                ->where('medicine.name', 'LIKE', '%' . $q . '%');

            // filter medicine dpho
            // filter is dpho or not, or null
            if (is_null($is_dpho)) {
                $query = $query->orderBy('' . $orderby . '', '' . $ordertype . '');
            } else if ($is_dpho == true) {
                $query = $query->whereNotNull('medicine.kode_dpho')->orderBy('' . $orderby . '', '' . $ordertype . '');
            } else if ($is_dpho == false) {
                $query = $query->whereNull('medicine.kode_dpho')->orderBy('' . $orderby . '', '' . $ordertype . '');
            }

            $from_date = $request->input('from_date');
            if ($from_date == "") {
                $from_date = "0001-01-01";
            }

            $to_date = $request->input('to_date');
            if ($to_date == "") {
                $to_date = "9999-12-31";
            }

            $query->where('medicine_outgoing.date', '>=', '' . $from_date . '')->where('medicine_outgoing.date', '<=', '' . $to_date . '');
            $query->where('medicine_outgoing.quantity','<>', 0);

            $mapItem = function (MedicineOutgoing $value) {
                return [
                    'id' => $value['id'],
                    'id_medicine' => $value['id_medicine'],
                    'batch_no' => $value['batch_no'],
                    'exp_date' => $value['exp_date'],
                    'quantity' => $value['quantity'],
                    'date' => $value['date'],
                    'unit' => $value->unit(),
                    'medicine' => $value->medicine()
                ];
            };
            $response = PaginationHelper::pagination($query, $per_page, $mapItem);
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
        } catch (\Exception $error_log) { }

        return ResponseHelper::success($response);
    }

    public function show($lang, $id = 1, Request $request)
    {
        $params = [$id];
        try {
            App::setLocale($lang);

            $id_user = $request->user()->id;
            $level_user = $request->user()->level;
            $access = (new \App\Http\Controllers\FunctionController)->checkACL($level_user, $id_user, 'medicine_outgoing', 'read');
            if ($access == false) {
                return ResponseHelper::error(trans('validation.unauthorized'));
            }

            $id_clinic = (new \App\Http\Controllers\FunctionController)->getIDClinicUser($id_user);

            $medicine_outgoing = $this->getData($lang, $id);
            if ($medicine_outgoing == false) {
                return ResponseHelper::error(trans('validation.data_not_found'));
            } else {
                $medicine = Medicine::withTrashed()->findOrFail($medicine_outgoing->id_medicine);
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
        } catch (\Exception $error_log) { }

        return ResponseHelper::success(array($response));
    }

    function getData($lang, $id)
    {
        $data = false;
        try {
            App::setLocale($lang);

            $medicine_outgoing = MedicineOutgoing::withTrashed()
                ->select((new Database)->medicine_outgoing())
                ->join('units', 'units.id', '=', 'medicine_outgoing.id_unit')
                ->where('medicine_outgoing.id', $id)
                ->first();

            if (empty($medicine_outgoing)) return false;

            $medicine = Medicine::withTrashed()->select((new Database)->sub_medicine())->findOrFail($medicine_outgoing->id_medicine);
            $medicine_outgoing['medicine'] = $medicine;
            $data = $medicine_outgoing;
        } catch (\Exception $error) {
            $data = false;
        }

        return $data;
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
            $data['errors']  = $errors;
            $data['message']  = (new \App\Functions)->error($errors);
        } else {
            $data['success'] = true;
            $data['error'] = false;
        }

        $validate = $request->input('validate');

        if ($validate != "true" && $data['success'] == true) {
            $input['date'] = Date("Y-m-d");

            $id_medicine = $input['id_medicine'];
            $quantity = $input['quantity'];
            $date = Date("Y-m-d");

            $data_outgoing['id_medicine'] =  $id_medicine;

            $query = DB::select("SELECT
                medicine_incoming.*,
                IF(
                    (medicine_incoming.quantity-medicine_outgoing.quantity) is NULL,
                    medicine_incoming.quantity,
                    (medicine_incoming.quantity-medicine_outgoing.quantity)
                )
                AS stock
                    FROM `medicine_incoming`
                    LEFT JOIN
                        (SELECT
                        medicine_outgoing.id,
                        medicine_outgoing.id_medicine,
                        medicine_outgoing.batch_no,
                        sum(medicine_outgoing.quantity) as quantity
                            FROM `medicine_outgoing`
                            WHERE medicine_outgoing.id_medicine='" . $id_medicine . "' AND medicine_outgoing.deleted_at is NULL
                            GROUP BY medicine_outgoing.batch_no) AS medicine_outgoing
                     ON medicine_incoming.batch_no = medicine_outgoing.batch_no
                     WHERE
                        medicine_incoming.id_medicine = '" . $id_medicine . "' AND
                        (medicine_incoming.quantity>medicine_outgoing.quantity OR medicine_outgoing.batch_no is NULL) AND medicine_incoming.deleted_at is NULL
                        GROUP BY medicine_incoming.batch_no
                        ORDER BY medicine_incoming.date ASC");

            $check_stock = true;

            $stock = 0;
            foreach ($query as $key => $value) {
                $stock = $stock + $value->stock;
            }

            if ($stock < $quantity) {
                $check_stock = false;
            }

            $data = $check_stock;

            if ($check_stock == true) {

                foreach ($query as $key => $value) {
                    if ($quantity >= $value->stock) {
                        $quantity_outgoing = $value->stock;
                    } else {
                        $quantity_outgoing = $quantity;
                    }

                    $data_outgoing['id_medicine'] = $id_medicine;
                    $data_outgoing['batch_no'] = $value->batch_no;
                    $data_outgoing['exp_date'] = $value->exp_date;
                    $data_outgoing['quantity'] = $quantity_outgoing;
                    $data_outgoing['date'] = $date;

                    $medicine_outgoing = MedicineOutgoing::firstOrCreate((new \App\Functions)->firstCreate($data_outgoing, [
                        'id_medicine',
                        'batch_no',
                        'exp_date',
                        'quantity',
                        'date'
                    ]));

                    $quantity = $quantity - $quantity_outgoing;

                    if ($quantity == 0) {
                        break;
                    }
                }
            } else {

                $medicine = Medicine::findOrFail($id_medicine);

                $data = trans('validation.medicine_stock_not_enough', ['name' => $medicine['name']]);
            }
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
        } catch (\Exception $error_log) { }

        return response()->json($response);
    }
}
