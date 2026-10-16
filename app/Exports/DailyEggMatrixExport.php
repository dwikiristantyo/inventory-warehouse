<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DailyEggMatrixExport implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(private array $dailyData) {}

    public function array(): array
    {
        $mainHeader = array_fill(0, 31, '');
        $mainHeader[0] = 'Hari / Tgl';
        foreach ([1 => 'Saldo Awal', 5 => 'Produksi Kandang', 11 => 'Total Stok', 15 => 'Susut', 17 => 'Buang', 19 => 'Penjualan', 23 => 'Saldo Akhir', 27 => 'Adjustment'] as $column => $label) {
            $mainHeader[$column] = $label;
        }

        $subHeader = array_fill(0, 31, '');
        foreach ([
            1 => 'SALABLE EGGS', 3 => 'CULL EGGS', 5 => 'SALABLE EGGS', 7 => 'CULL EGGS',
            11 => 'SALABLE EGGS', 13 => 'CULL EGGS', 15 => 'SALABLE EGGS', 17 => 'CULL EGGS',
            19 => 'SALABLE EGGS', 21 => 'CULL EGGS', 23 => 'SALABLE EGGS', 25 => 'CULL EGGS',
            27 => 'SALABLE EGGS', 29 => 'CULL EGGS',
        ] as $column => $label) {
            $subHeader[$column] = $label;
        }

        $units = ['Hari / Tgl'];
        $numbers = ['-'];
        for ($column = 1; $column <= 30; $column++) {
            $units[] = $column % 2 === 1 ? 'Kg' : 'Butir';
            $numbers[] = (string) $column;
        }
        $numbers[11] = '11 (1+5)';
        $numbers[12] = '12 (2+6)';
        $numbers[13] = '13 (3+7+9)';
        $numbers[14] = '14 (4+8+10)';
        $numbers[23] = '23 (11-15-19+27)';
        $numbers[24] = '24 (12-16-20+28)';
        $numbers[25] = '25 (13-17-21+29)';
        $numbers[26] = '26 (14-18-22+30)';

        $rows = [$mainHeader, $subHeader, $units, $numbers];

        foreach ($this->dailyData as $row) {
            $values = ["{$row['dayName']}\n{$row['formattedDate']}"];
            for ($column = 1; $column <= 30; $column++) {
                $values[] = (float) $row["c{$column}"];
            }
            $rows[] = $values;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 14];
        for ($column = 2; $column <= 31; $column++) {
            $widths[Coordinate::stringFromColumnIndex($column)] = 10;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(4, count($this->array()));

                $sheet->mergeCells('A1:A4');
                foreach (['B1:E1', 'F1:K1', 'L1:O1', 'P1:Q1', 'R1:S1', 'T1:W1', 'X1:AA1', 'AB1:AE1'] as $range) {
                    $sheet->mergeCells($range);
                }
                foreach (['B2:C2', 'D2:E2', 'F2:G2', 'H2:K2', 'L2:M2', 'N2:O2', 'P2:Q2', 'R2:S2', 'T2:U2', 'V2:W2', 'X2:Y2', 'Z2:AA2', 'AB2:AC2', 'AD2:AE2'] as $range) {
                    $sheet->mergeCells($range);
                }

                $sheet->getStyle("A1:AE{$lastRow}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 9],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '94A3B8'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A1:AE1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C6E7');
                $sheet->getStyle('A3:AE3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C6E7');
                $sheet->getStyle('A4:AE4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $sheet->getStyle('A1:AE4')->getFont()->setBold(true);
                $sheet->getStyle('A1:AE4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                foreach ([['B2:C2', 'A9D08E'], ['F2:G2', 'A9D08E'], ['L2:M2', 'A9D08E'], ['P2:Q2', 'A9D08E'], ['T2:U2', 'A9D08E'], ['X2:Y2', 'A9D08E'], ['AB2:AC2', 'A9D08E'], ['D2:E2', 'FFF2CC'], ['H2:K2', 'FFF2CC'], ['N2:O2', 'FFF2CC'], ['R2:S2', 'FFF2CC'], ['V2:W2', 'FFF2CC'], ['Z2:AA2', 'FFF2CC'], ['AD2:AE2', 'FFF2CC']] as [$range, $color]) {
                    $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
                }

                if ($lastRow > 4) {
                    $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                    $sheet->getStyle("B5:AE{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    foreach (range(2, 30, 2) as $column) {
                        $letter = Coordinate::stringFromColumnIndex($column);
                        $sheet->getStyle("{$letter}5:{$letter}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                    foreach (range(3, 31, 2) as $column) {
                        $letter = Coordinate::stringFromColumnIndex($column);
                        $sheet->getStyle("{$letter}5:{$letter}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                }

                $sheet->freezePane('B5');
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageSetup()->setFitToPage(true);
            },
        ];
    }

    public function title(): string
    {
        return 'Daily Egg Matrix';
    }
}