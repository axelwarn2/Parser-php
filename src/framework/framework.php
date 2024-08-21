<?php

include $_SERVER["DOCUMENT_ROOT"] . "/../vendor/autoload.php";

use Framework\CProject;

$parser = new CProject();

$file = fopen("projects.txt", "r");

while(($line = fgets($file)) !== false) {
    $fields = explode("| ", $line);

    $partner_id = $fields[0];
    $project_url = $fields[1];
    $product_version = $fields[2];
    $description = $fields[3];

    $projectData = [
        'partner_id' => $partner_id,
        'project_url' => $project_url,
        'product_version' => $product_version,
        'description' => $description
    ];

    CProject::create($projectData);
}
fclose($file);
