<?php

namespace App\Exports;

use App\Model\BaseProductStore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseProductsStoreExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BaseProductStore::select('*')
        ->where('deleted','=',0)
        ->get();
    }

    public function headings(): array
    {
        return array_keys($this->collection()->first()->toArray());
    }
}
