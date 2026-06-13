<?php

namespace App\Enum;

enum FinancialCategoryEnum: string
{
    case Exam = 'exam';
    case Food = 'food';
    case Transport = 'transport';
    case Supply = 'supply';
    case Other = 'other';
}
