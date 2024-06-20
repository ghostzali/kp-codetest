<?php

namespace App\Repositories;

interface MedicineOutgoingRepository
{
    public function index($request, $id_user);
    public function show($id, $id_user);
    public function test($data, $input);
}
