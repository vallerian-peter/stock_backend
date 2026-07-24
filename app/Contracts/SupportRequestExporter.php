<?php

namespace App\Contracts;

use App\Models\SupportRequest;

interface SupportRequestExporter
{
    public function export(SupportRequest $supportRequest): void;
}
