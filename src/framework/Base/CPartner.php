<?php

namespace Framework;

use Framework\Models\Model;

class CPartner extends Model
{
    protected static string $table = "partners";
    protected array $fillable = ["name", "detal_page", "adress_partner"];
}