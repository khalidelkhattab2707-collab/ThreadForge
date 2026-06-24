<?php
namespace App\Enums;
enum RawContentStatusEnum :string 
{
    case Pending ='pending';
    case Analyzing ='analyzing';
    case Done ='done';
    case Failed ='failed';
}