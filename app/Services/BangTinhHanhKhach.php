<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class BangTinhHanhKhach
{
    public const FILE_MESSAGES = [
        'passenger_file.required' => 'Vui lòng chọn file danh sách hành khách đã điền.',
        'passenger_file.file' => 'Không nhận được file hợp lệ. Vui lòng chọn lại file .xlsx hoặc CSV UTF-8.',
        'passenger_file.uploaded' => 'Tải file thất bại. Vui lòng chọn file không quá 2 MB và thử lại.',
        'passenger_file.max' => 'File danh sách quá lớn. Dung lượng tối đa là 2 MB.',
    ];
    public function template(int $adults, int $children): string
    {
        $rows = [['STT', 'Loại khách', 'Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ']];
        for ($i = 0; $i < $adults + $children; $i++) {
            $rows[] = [$i + 1, $i < $adults ? 'Người lớn' : 'Trẻ em', '', '', '<style nf="@"></style>', ''];
        }
        return (string) SimpleXLSXGen::fromArray($rows)->setColWidth(1, 8)->setColWidth(2, 18)
            ->setColWidth(3, 30)->setColWidth(4, 32)->setColWidth(5, 22)->setColWidth(6, 45);
    }

    public function read(UploadedFile $file, int $adults, int $children): array
    {
        $fail = fn ($message) => throw ValidationException::withMessages(['passenger_file' => $message]);
        if (!$file->isValid() || $file->getSize() > 2 * 1024 * 1024) {
            $fail('File không hợp lệ hoặc lớn hơn 2 MB.');
        }
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];
        if ($extension === 'xlsx') {
            // Bound decompressed data before handing the workbook to the reader.
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) $fail('File Excel bị hỏng. Vui lòng tải mẫu mới.');
            $size = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) $size += $zip->statIndex($i)['size'];
            $zip->close();
            if ($size > 20 * 1024 * 1024) $fail('Nội dung Excel quá lớn. Vui lòng dùng mẫu của hệ thống.');
            try {
                $xlsx = SimpleXLSX::parse($file->getRealPath());
            } catch (\Throwable $e) {
                $fail('Không đọc được nội dung Excel. File có thể bị hỏng hoặc không phải .xlsx. Vui lòng tải mẫu mới.');
            }
            if (!$xlsx) $fail('Không đọc được Excel. Vui lòng lưu file ở định dạng .xlsx.');
            if ($xlsx->sheetsCount() !== 1) $fail('File phải có đúng 1 sheet danh sách hành khách theo mẫu. Vui lòng xóa các sheet thừa trước khi tải lên.');
            // Check dimensions before rows() allocates padding for every cell.
            [$columns, $rowCount] = $xlsx->dimension();
            if ($columns !== 6) $fail('File sai cấu trúc: phải có đúng 6 cột từ A đến F theo mẫu. Vui lòng xóa cột thừa hoặc tải lại mẫu.');
            if ($rowCount > 1000) $fail('File có quá nhiều dòng, kể cả dòng trống. Vui lòng tải mẫu mới và chỉ điền danh sách hành khách.');
            $rows = $xlsx->rows();
        } elseif ($extension === 'csv') {
            if (!mb_check_encoding(file_get_contents($file->getRealPath()), 'UTF-8')) $fail('CSV phải dùng mã hóa UTF-8. Trong Excel, chọn Save As → CSV UTF-8 rồi tải lại.');
            $handle = fopen($file->getRealPath(), 'r');
            $line = fgets($handle) ?: '';
            $delimiter = substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
            rewind($handle);
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) $rows[] = $row;
            fclose($handle);
        } else {
            $fail('Sai định dạng file: chỉ nhận .xlsx hoặc .csv (UTF-8), không nhận Word, PDF, ảnh hoặc .xls/.htm. Hãy tải mẫu và lưu đúng định dạng; không chỉ đổi tên đuôi file.');
        }
        $header = array_shift($rows) ?? [];
        $header = array_map(fn ($value) => trim((string) $value), $header);
        if (isset($header[0])) $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        $expected = ['STT', 'Loại khách', 'Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ'];
        if (count($header) !== count($expected)) {
            $fail('File sai cấu trúc: dòng đầu phải có đúng 6 cột theo thứ tự STT, Loại khách, Họ và tên, Email, Số điện thoại, Địa chỉ. Không thêm, xóa, đổi tên hoặc đổi thứ tự cột. Vui lòng tải lại mẫu.');
        }
        foreach ($expected as $column => $name) {
            if ($header[$column] !== $name) {
                $letter = chr(65 + $column);
                $fail("File sai cấu trúc: ô {$letter}1 phải là \"{$name}\". Giữ nguyên tên và thứ tự cột trong mẫu.");
            }
        }
        $passengers = [];
        foreach ($rows as $index => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row);
            if (!array_filter($row, fn ($value) => $value !== '')) continue;
            $line = $index + 2;
            if (count($row) !== 6) $fail("Dòng $line: phải có đúng 6 cột theo mẫu. Nếu dùng CSV, hãy đặt nội dung có dấu phẩy trong dấu nháy kép hoặc lưu lại bằng Excel.");
            $position = count($passengers);
            if (($row[0] ?? '') !== (string) ($position + 1)) $fail("Dòng $line: STT phải liên tiếp từ 1.");
            $type = $position < $adults ? 'Người lớn' : 'Trẻ em';
            if (($row[1] ?? '') !== $type) $fail("Dòng $line: loại khách phải là $type theo số lượng đã chọn.");
            $passenger = array_combine(['fullName', 'email', 'tel', 'address'], array_pad(array_slice($row, 2, 4), 4, ''));
            $validator = Validator::make($passenger, [
                'fullName' => 'required|string|max:255', 'email' => 'required|email|max:255',
                'tel' => ['required', 'regex:/^[0-9+ ()-]{9,20}$/'], 'address' => 'required|string|max:255',
            ], ['required' => ':attribute chưa được điền.', 'email' => 'Email không hợp lệ.', 'regex' => 'Số điện thoại không hợp lệ.', 'max' => ':attribute quá dài.'],
                ['fullName' => 'Họ và tên', 'email' => 'Email', 'tel' => 'Số điện thoại', 'address' => 'Địa chỉ']);
            if ($validator->fails()) $fail("Dòng $line: " . $validator->errors()->first());
            $passengers[] = $passenger;
            if (count($passengers) > 200) $fail('File có quá nhiều hành khách.');
        }
        if (count($passengers) !== $adults + $children) $fail('File có ' . count($passengers) . ' hành khách; cần đúng ' . ($adults + $children) . ' hành khách theo số lượng đã chọn.');
        return $passengers;
    }
}
