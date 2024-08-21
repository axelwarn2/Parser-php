<?php

namespace Framework;

use Framework\Models\Model;

class CProject extends Model
{
    protected static string $table = "projects";
    protected array $fillable = ["partner_id", "project_url", "product_version", "description"];
}
