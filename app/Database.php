<?php

namespace App;

class Database {
    /**
     * Select medicine_outgoings columns
     */
    function medicine_outgoing() {
        return [
            "medicine_outgoing.id",
            'id_medicine',
            'id_unit',
            "medicines.name as medicine_name",
            "units.name as unit_name",
            "medicines.id_clinic",
            "medicine_outgoing.batch_no",
            "medicine_outgoing.exp_date",
            "medicine_outgoing.quantity",
            "medicine_outgoing.date",
            "medicine_outgoing.created_at",
            "medicine_outgoing.updated_at",
        ];
    }

    /**
     * Select medicine sub columns
     */
    function sub_medicine() {
        return [
            'medicines.id',
            'medicines.name'
        ];
    }
}
