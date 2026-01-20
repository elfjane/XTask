<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TaskImportService
{
    /**
     * Import tasks from an Excel file.
     *
     * @param UploadedFile $file
     * @param User $admin
     * @return int Count of imported tasks
     */
    public function import(UploadedFile $file, User $admin): int
    {
        $path = $file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Get headers from first row
        $headers = [];
        $colCount = Coordinate::columnIndexFromString($highestColumn);
        for ($col = 1; $col <= $colCount; $col++) {
            $headers[$col] = $sheet->getCell([$col, 1])->getValue();
        }

        $importedCount = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $this->getRowData($sheet, $headers, $row);

            if (empty($rowData['has_data'])) {
                continue;
            }

            $task = $this->mapRowToTask($rowData, $admin);
            $task->save();
            $importedCount++;
        }

        return $importedCount;
    }

    /**
     * Extract data and hyperlinks from a row.
     */
    private function getRowData($sheet, $headers, $row): array
    {
        $hasData = false;
        $rowData = ['has_data' => false];

        foreach ($headers as $col => $header) {
            if (!$header)
                continue;

            $cell = $sheet->getCell([$col, $row]);
            $val = $cell->getValue();
            $rowData[$header] = $val;

            // Extract hyperlink
            $hyperlink = $cell->getHyperlink()->getUrl();
            if ($hyperlink) {
                $rowData[$header . '_link'] = $hyperlink;
            }

            if ($val !== null && $val !== '') {
                $hasData = true;
            }
        }

        $rowData['has_data'] = $hasData;
        return $rowData;
    }

    /**
     * Map row data to a Task model.
     */
    private function mapRowToTask(array $rowData, User $admin): Task
    {
        $task = new Task();
        $task->level = $rowData['級別'] ?? 1;

        // Status mapping
        $excelStatus = $rowData['狀態'] ?? '已完成';
        $statusMap = [
            '已完成' => 'finished',
            'finished' => 'finished',
            '執行中' => 'working',
            'working' => 'working',
            '未執行' => 'in_progress',
            'in_progress' => 'in_progress',
            '閒置' => 'idle',
            'idle' => 'idle',
            '待測試' => 'waiting_qa',
            'waiting_qa' => 'waiting_qa',
            '未達成' => 'miss',
            'miss' => 'miss',
            '已取消' => 'cancelled',
            'cancelled' => 'cancelled',
        ];
        $task->status = $statusMap[$excelStatus] ?? 'in_progress';

        // Assignee mapping
        $assigneeName = $rowData['執行人員'] ?? null;
        if ($assigneeName) {
            $user = User::where('name', $assigneeName)->first();
            if ($user) {
                $task->user_id = $user->id;
                $task->department = $user->department?->name ?: 'Unknown';
            } else {
                $task->user_id = $admin->id;
                $task->department = $admin->department?->name ?: 'Admin Dept';
            }
        } else {
            $task->user_id = $admin->id;
            $task->department = 'Unknown';
        }

        $task->related_personnel = $rowData['相關人員'] ?? null;
        $task->project = $rowData['專案'] ?? 'Imported';
        $task->item = $rowData['項目'] ?? null;
        // The original code had a strange double mapping for department/category
        $task->department = $rowData['類別'] ?? $task->department;

        $workText = $rowData['工作'] ?? '';
        $embeddedLink = $rowData['工作_link'] ?? null;
        $task->work = $workText;
        $task->points = $rowData['點數'] ?? 0;

        $task->release_date = $this->parseExcelValueAsDate($rowData['發佈日'] ?? null);
        $task->start_date = $this->parseExcelValueAsDate($rowData['起始日'] ?? null);
        $task->expected_finish_date = $this->parseExcelValueAsDate($rowData['預計完成日'] ?? null);
        $task->actual_finish_date = $this->parseExcelValueAsDate($rowData['完成日'] ?? null);

        // Memo processing
        $originalMemo = $rowData['備註說明'] ?? '';
        $memoLink = $rowData['備註說明_link'] ?? null;
        if ($memoLink && $originalMemo) {
            $originalMemo = "[" . $originalMemo . "](" . $memoLink . ")";
        }

        $memoContent = $originalMemo;
        if ($embeddedLink) {
            $workTitle = $workText ?: '工作連結';
            $markdownLink = "[" . $workTitle . "](" . $embeddedLink . ")";
            $memoContent = $markdownLink . ($originalMemo ? "\n" . $originalMemo : "");
        }
        $task->memo = $this->autoConvertLinksToMarkdown($memoContent);

        // Output URL processing
        $outputUrlRaw = $rowData['產出的文件或建檔(詳細說明)'] ?? '';
        $outputUrlLink = $rowData['產出的文件或建檔(詳細說明)_link'] ?? null;
        if ($outputUrlLink && $outputUrlRaw) {
            $outputUrlRaw = "[" . $outputUrlRaw . "](" . $outputUrlLink . ")";
        }
        $task->output_url = $this->autoConvertLinksToMarkdown($outputUrlRaw);

        // Review Status automation
        if ($task->status === 'finished') {
            $task->review_status = 'approved';
            $task->reviewed_by = $admin->id;
            $finishDate = $task->actual_finish_date ?: now();
            $task->reviewed_at = $finishDate;
            $task->approved_at = $finishDate;
        } else {
            $task->review_status = 'unsubmitted';
        }

        return $task;
    }

    /**
     * Helper to linkify raw text URLs.
     */
    private function autoConvertLinksToMarkdown($text)
    {
        if (!$text)
            return $text;

        // Pattern to find URLs that are not already part of a markdown link [title](url)
        $pattern = '/(?<!\()https?:\/\/[^\s\)]+/i';

        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[0];
            return "[$url]($url)";
        }, $text);
    }

    /**
     * Parse excel date value.
     */
    private function parseExcelValueAsDate($value)
    {
        if (!$value)
            return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
