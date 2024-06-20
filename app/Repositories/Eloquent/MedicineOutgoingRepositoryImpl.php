<?php

namespace App\Repositories\Eloquent;

use App\Database;
use App\Helpers\PaginationHelper;
use App\Models\Medicine;
use App\Models\MedicineOutgoing;
use App\Repositories\MedicineOutgoingRepository;
use Illuminate\Support\Facades\DB;

class MedicineOutgoingRepositoryImpl implements MedicineOutgoingRepository
{
    public function index($request, $id_user)
    {
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
        return $response;
    }

    function getData($id)
    {
        $data = false;
        try {
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

    public function show($id, $request, $id_user)
    {
        $id_clinic = (new \App\Http\Controllers\FunctionController)->getIDClinicUser($id_user);

        $medicine_outgoing = $this->getData($id);
        $medicine = Medicine::withTrashed()->findOrFail($medicine_outgoing->id_medicine);

        return [
            "id_clinic" => $id_clinic,
            "medicine_outgoing" => $medicine_outgoing,
            "medicine" => $medicine
        ];
    }

    public function test($data, $input)
    {
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
        return [
            "data" => $data,
            "data_outgoing" => $data_outgoing
        ];
    }
}
