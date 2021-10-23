<?php
/**
 * ExcelExport Export
 *
 * @author: tuanha
 * @last-mod: 23-Jan-2021
 */
namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExcelExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $headinga;

    /**
     * Create instance
     *
     * @param $data $array
     */
    public function __construct($data, $headings)
    {
        $this->data = $data;
        $this->headings = $headings;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:J1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
            },
        ];
    }
}
