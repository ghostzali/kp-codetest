<?php

namespace App\Repositories;

interface MedicineOutgoingRepository
{
    public function index($lang, $request);
    public function show($lang, $id, $request);
    public function test($lang, $request);
}
