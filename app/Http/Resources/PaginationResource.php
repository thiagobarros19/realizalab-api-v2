<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginationResource extends ResourceCollection
{
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        unset($default['links']);
        return $default;
    }
}
