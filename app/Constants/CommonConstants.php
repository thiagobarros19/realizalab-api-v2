<?php

namespace App\Constants;

use App\Enum\OrderDirectionEnum;

class CommonConstants
{
    public static string $defaultOrderField = 'id';
    public static int $defaultPageSize = 10;
    public static string $defaultOrderBy = OrderDirectionEnum::Desc->value;
}
