<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMutationExport implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    private array $rows = [];
    private array $titleRows = [];
    private array $headerRows = [];
    private array $subtotalRows = [];
    private array $totalRows = [];

    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->titleRows[] = count($this->rows) + 1;
            $this->rows[] = ["{$item['icode']} - {$item['desc1']} (UOM: {$item['uom1']} & {$item['uom2']})"];

            $headerRow = count($this->rows) + 1;
            $this->headerRows[] = $headerRow;
            $this->rows[] = ['Date', 'Remark', 'Opening Balance', '', 'Incoming', '', 'Outgoing', '', 'Adjustment', '', 'Closing Balance', ''];
            $this->rows[] = ['', '', $item['uom1'] ?: 'Qty 1', $item['uom2'] ?: 'Qty 2', $item['uom1'] ?: 'Qty 1', $item['uom2'] ?: 'Qty 2', $item['uom1'] ?: 'Qty 1', $item['uom2'] ?: 'Qty 2', $item['uom1'] ?: 'Qty 1', $item['uom2'] ?: 'Qty 2', $item['uom1'] ?: 'Qty 1', $item['uom2'] ?: 'Qty 2'];

            foreach ($item['grouped'] as $date => $movements) {
                foreach ($movements as $movement) {
                    $this->rows[] = [
                        date('d-m-Y', strtotime($date)),
                        $movement['remark'],
                        (float) ($movement['bal_kg'] - ($movement['in_kg'] - $movement['out_kg'] + $movement['adj_kg'])),
                        (float) ($movement['bal_pcs'] - ($movement['in_pcs'] - $movement['out_pcs'] + $movement['adj_pcs'])),
                        (float) $movement['in_kg'], (float) $movement['in_pcs'],
                        (float) $movement['out_kg'], (float) $movement['out_pcs'],
                        (float) $movement['adj_kg'], (float) $movement['adj_pcs'],
                        (float) $movement['bal_kg'], (float) $movement['bal_pcs'],
                    ];
                }

                if (count($movements) > 0 && ($movements[0]['type'] ?? '') !== 'INIT') {
                    $lastMovement = $movements[array_key_last($movements)];
                    $this->subtotalRows[] = count($this->rows) + 1;
                    $this->rows[] = [date('d-m-Y', strtotime($date)), 'DAILY CLOSING BALANCE', '', '', '', '', '', '', '', '', (float) $lastMovement['bal_kg'], (float) $lastMovement['bal_pcs']];
                }
            }

            $this->totalRows[] = count($this->rows) + 1;
            $this->rows[] = ['TOTAL CLOSING BALANCE', '', '', '', '', '', '', '', '', '', (float) $item['final_kg'], (float) $item['final_pcs']];
            $this->rows[] = [];
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 13, 'B' => 30, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 12, 'G' => 12, 'H' => 12, 'I' => 12, 'J' => 12, 'K' => 12, 'L' => 12];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(1, count($this->rows));

                $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 9],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                foreach ($this->titleRows as $row) {
                    $sheet->mergeCells("A{$row}:L{$row}");
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF3F8']],
                    ]);
                }

                foreach ($this->headerRows as $row) {
                    $subHeaderRow = $row + 1;
                    foreach (["A{$row}:A{$subHeaderRow}", "B{$row}:B{$subHeaderRow}", "C{$row}:D{$row}", "E{$row}:F{$row}", "G{$row}:H{$row}", "I{$row}:J{$row}", "K{$row}:L{$row}"] as $range) {
                        $sheet->mergeCells($range);
                    }
                    $sheet->getStyle("A{$row}:L{$subHeaderRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8EA9DB']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                    $sheet->getStyle("C{$subHeaderRow}:L{$subHeaderRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
                }

                foreach ($this->subtotalRows as $row) {
                    $sheet->mergeCells("B{$row}:J{$row}");
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    ]);
                }

                foreach ($this->totalRows as $row) {
                    $sheet->mergeCells("A{$row}:J{$row}");
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2F7']],
                    ]);
                }

                if ($lastRow > 0) {
                    foreach (['C', 'E', 'G', 'I', 'K'] as $column) {
                        $sheet->getStyle("{$column}1:{$column}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                    foreach (['D', 'F', 'H', 'J', 'L'] as $column) {
                        $sheet->getStyle("{$column}1:{$column}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                }

                $sheet->getStyle("C1:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageSetup()->setFitToPage(true);
            },
        ];
    }

    public function title(): string
    {
        return 'Stock Mutation';
    }
}