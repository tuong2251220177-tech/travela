<?php
namespace Tests\Feature;

use App\Services\BangTinhHanhKhach;
use Illuminate\Http\UploadedFile;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Tests\TestCase;

class PassengerSpreadsheetTest extends TestCase
{
    private function rows(): array
    {
        return [
            ['STT', 'Loại khách', 'Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ'],
            [1, 'Người lớn', 'Nguyễn Văn An', 'an@example.com', "\0" . '0356077270', 'Đà Nẵng'],
            [2, 'Trẻ em', 'Nguyễn Bé Một', 'an@example.com', "\0" . '0356077270', 'Đà Nẵng'],
            [3, 'Trẻ em', 'Nguyễn Bé Hai', 'an@example.com', "\0" . '0356077270', 'Đà Nẵng'],
        ];
    }

    private function upload(array $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('passengers.xlsx', (string) SimpleXLSXGen::fromArray($rows));
    }

    public function test_download_is_a_real_excel_workbook_with_requested_passenger_types(): void
    {
        $response = $this->withSession(['username' => 'test'])->get('/booking/passenger-template?adults=1&children=2');
        $response->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="travela-passengers.xlsx"');
        $xlsx = SimpleXLSX::parseData($response->getContent());
        $this->assertNotFalse($xlsx);
        $this->assertCount(4, $xlsx->rows());
        $this->assertSame('Trẻ em', $xlsx->rows()[3][1]);
        $file = UploadedFile::fake()->createWithContent('template.xlsx', $response->getContent());
        $zip = new \ZipArchive();
        $zip->open($file->getRealPath());
        $styles = $zip->getFromName('xl/styles.xml');
        $zip->close();
        $this->assertStringContainsString('formatCode="@"', $styles);
    }

    public function test_preview_preserves_vietnamese_and_leading_zero_in_phone(): void
    {
        $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 2, 'passenger_file' => $this->upload($this->rows()),
        ])->assertOk()->assertJsonCount(3, 'passengers')
            ->assertJsonPath('passengers.0.fullName', 'Nguyễn Văn An')
            ->assertJsonPath('passengers.0.tel', '0356077270');
    }

    public function test_invalid_rows_and_counts_are_rejected_with_file_error(): void
    {
        $missing = $this->rows(); $missing[2][2] = '';
        $badEmail = $this->rows(); $badEmail[1][3] = 'invalid';
        $wrongType = $this->rows(); $wrongType[2][1] = 'Người lớn';
        $wrongNumber = $this->rows(); $wrongNumber[3][0] = 2;
        foreach ([$missing, $badEmail, $wrongType, $wrongNumber, array_slice($this->rows(), 0, 3)] as $rows) {
            $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
                'adults' => 1, 'children' => 2, 'passenger_file' => $this->upload($rows),
            ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
        }
    }

    public function test_corrupt_and_oversized_files_are_rejected(): void
    {
        foreach ([UploadedFile::fake()->createWithContent('broken.xlsx', 'not a workbook'), UploadedFile::fake()->create('large.xlsx', 2049)] as $file) {
            $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
                'adults' => 1, 'children' => 2, 'passenger_file' => $file,
            ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
        }
    }

    public function test_booking_cannot_replace_missing_upload_with_old_browser_json(): void
    {
        $this->withSession(['username' => 'test', 'userId' => 1])->postJson('/create-booking', [
            'tourId' => 1, 'numAdults' => 1, 'numChildren' => 2,
            'bookingToken' => (string) \Illuminate\Support\Str::uuid(), 'payment' => 'bank-transfer', 'agree' => 1,
            'passenger_import_json' => json_encode([['fullName' => 'Stale browser data']]),
        ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
    }

    public function test_word_and_renamed_word_are_rejected(): void
    {
        foreach (['list.docx', 'list.xlsx', 'list.csv'] as $name) {
            $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
                'adults' => 1, 'children' => 2,
                'passenger_file' => UploadedFile::fake()->createWithContent($name, 'This is not a passenger spreadsheet'),
            ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
        }
    }

    public function test_wrong_headers_and_extra_columns_are_rejected(): void
    {
        $renamed = $this->rows(); $renamed[0][2] = 'Tên khác';
        $reordered = $this->rows(); [$reordered[0][3], $reordered[0][4]] = [$reordered[0][4], $reordered[0][3]];
        $extra = $this->rows(); $extra[0][] = 'Ghi chú';
        $missing = $this->rows(); array_pop($missing[0]);
        foreach ([$renamed, $reordered, $extra, $missing] as $rows) {
            $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
                'adults' => 1, 'children' => 2, 'passenger_file' => $this->upload($rows),
            ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
        }
    }

    public function test_csv_accepts_bom_and_rejects_extra_data_columns(): void
    {
        $header = "\xEF\xBB\xBFSTT,Loại khách,Họ và tên,Email,Số điện thoại,Địa chỉ\n";
        $row = '1,Người lớn,Nguyễn An,an@example.com,0356077270,Đà Nẵng';
        $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 0, 'passenger_file' => UploadedFile::fake()->createWithContent('list.csv', $header.$row),
        ])->assertOk()->assertJsonPath('passengers.0.tel', '0356077270');
        $this->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 0, 'passenger_file' => UploadedFile::fake()->createWithContent('list.csv', $header.$row.',extra'),
        ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
    }

    public function test_header_error_identifies_the_exact_cell_and_expected_name(): void
    {
        $rows = $this->rows();
        $rows[0][2] = 'Tên khách';
        $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 2, 'passenger_file' => $this->upload($rows),
        ])->assertUnprocessable()->assertJsonPath('errors.passenger_file.0',
            'File sai cấu trúc: ô C1 phải là "Họ và tên". Giữ nguyên tên và thứ tự cột trong mẫu.');
    }

    public function test_multiple_sheets_are_rejected_instead_of_silently_ignoring_passengers(): void
    {
        $workbook = SimpleXLSXGen::fromArray($this->rows())->addSheet($this->rows(), 'Danh sách khác');
        $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 2,
            'passenger_file' => UploadedFile::fake()->createWithContent('list.xlsx', (string) $workbook),
        ])->assertUnprocessable()->assertJsonPath('errors.passenger_file.0',
            'File phải có đúng 1 sheet danh sách hành khách theo mẫu. Vui lòng xóa các sheet thừa trước khi tải lên.');
    }

    public function test_invalid_template_is_rejected_again_when_submitting_booking(): void
    {
        $rows = $this->rows();
        $rows[0][0] = 'Mã khách';
        $this->withSession(['username' => 'test', 'userId' => 1])->postJson('/create-booking', [
            'tourId' => 1, 'numAdults' => 1, 'numChildren' => 2,
            'bookingToken' => (string) \Illuminate\Support\Str::uuid(),
            'payment' => 'bank-transfer', 'agree' => 1,
            'passenger_file' => $this->upload($rows),
            'passenger_import_json' => json_encode($this->rows()),
        ])->assertUnprocessable()->assertJsonPath('errors.passenger_file.0',
            'File sai cấu trúc: ô A1 phải là "STT". Giữ nguyên tên và thứ tự cột trong mẫu.');
    }

    public function test_empty_and_wrongly_encoded_csv_are_rejected(): void
    {
        foreach (['', "STT,Lo\xE1i khach\n"] as $contents) {
            $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
                'adults' => 1, 'children' => 2,
                'passenger_file' => UploadedFile::fake()->createWithContent('list.csv', $contents),
            ])->assertUnprocessable()->assertJsonValidationErrors('passenger_file');
        }
    }

    public function test_excessive_blank_rows_are_rejected_before_expanding_workbook(): void
    {
        $file = $this->upload($this->rows());
        $path = tempnam(sys_get_temp_dir(), 'passenger-test-');
        copy($file->getRealPath(), $path);
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $xml = preg_replace('/<dimension ref="[^"]+"\s*\/>/', '<dimension ref="A1:F1048576"/>', $xml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();
        $file = UploadedFile::fake()->createWithContent('list.xlsx', file_get_contents($path));
        unlink($path);
        $this->withSession(['username' => 'test'])->postJson('/booking/passenger-preview', [
            'adults' => 1, 'children' => 2, 'passenger_file' => $file,
        ])->assertUnprocessable()->assertJsonPath('errors.passenger_file.0',
            'File có quá nhiều dòng, kể cả dòng trống. Vui lòng tải mẫu mới và chỉ điền danh sách hành khách.');
    }
}
