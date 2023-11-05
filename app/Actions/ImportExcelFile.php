<?php

namespace App\Actions;

use Spatie\SimpleExcel\SimpleExcelReader;

class ImportExcelFile {

}


$pathToCsv =  storage_path('app/test.xlsx');
return $pathToCsv ;
// $rows = SimpleExcelReader::create($pathToCsv)->getRows();
/*
$rows->each(function(array $rowProperties) {
    // in the first pass $rowProperties will contain
    // ['email' => 'john@example.com', 'first_name' => 'john']
 });
 */