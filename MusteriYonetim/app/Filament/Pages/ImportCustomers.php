<?php

namespace App\Filament\Pages;

use App\Enums\OdemeDurumu;
use App\Exports\ImportFailuresExport;
use App\Imports\CustomerRowMapper;
use App\Imports\CustomersImport;
use App\Imports\CustomersPreviewReader;
use App\Models\Customer;
use App\Models\CustomerAudit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\Failure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Üç adımlı içe aktarım: dosya seçimi → önizleme → sonuç.
 *
 * @property-read Schema $form
 */
class ImportCustomers extends Page
{
    public const PREVIEW_ROWS = 20;

    public const MAX_KB = 10240;

    /** Yüklenen dosyalar yalnız bu klasörde tutulur; başka yol okunmaz, silinmez. */
    public const DIRECTORY = 'imports';

    protected static ?string $slug = 'ice-aktar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'İçe aktar';

    protected static ?string $title = 'Müşteri listesini içe aktar';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.import-customers';

    /** 1: dosya, 2: önizleme, 3: sonuç */
    public int $step = 1;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    // İstemci değiştiremez: dosya yolu yalnız sunucuda, form durumundan atanır.
    #[Locked]
    public ?string $storedPath = null;

    #[Locked]
    public ?string $originalName = null;

    /** @var array{total: int, valid: int, invalid: int, rows: list<array{line: int, unvan: ?string, firma_no: ?string, il: ?string, tutar: mixed, odeme: ?string, errors: list<string>}>} */
    public array $preview = ['total' => 0, 'valid' => 0, 'invalid' => 0, 'rows' => []];

    /** @var array{inserted: int, updated: int, skipped: int, failures: list<array{line: int, field: string, message: string}>} */
    public array $result = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'failures' => []];

    public static function canAccess(): bool
    {
        return Gate::allows('import', Customer::class);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                FileUpload::make('file')
                    ->label('Excel dosyası')
                    ->helperText('XLSX, XLS ya da CSV, en fazla 10 MB. Başlıklar 2. satırda olmalı.')
                    ->disk('local')
                    ->directory(self::DIRECTORY)
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->rules(['extensions:xlsx,xls,csv'])
                    ->maxSize(self::MAX_KB)
                    ->storeFileNamesIn('original_name')
                    ->required(),
            ]);
    }

    public function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Önizle')
            ->icon(Heroicon::OutlinedEye)
            ->action(fn () => $this->buildPreview());
    }

    public function importAction(): Action
    {
        return Action::make('import')
            ->label(fn () => $this->preview['valid'] > 0 ? "{$this->preview['valid']} satırı içe aktar" : 'İçe aktar')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->disabled(fn () => $this->preview['valid'] === 0)
            ->requiresConfirmation(fn () => $this->preview['invalid'] > 0)
            ->modalHeading('Hatalı satırlar atlanacak')
            ->modalDescription(fn () => "{$this->preview['invalid']} satır hatalı ve içe aktarılmayacak. Geçerli satırlar eklenir ya da güncellenir.")
            ->modalSubmitActionLabel('Devam et')
            ->action(fn () => $this->runImport());
    }

    public function restartAction(): Action
    {
        return Action::make('restart')
            ->label(fn () => $this->step === 3 ? 'Yeni dosya yükle' : 'Başka dosya seç')
            ->color('gray')
            ->action(fn () => $this->restart());
    }

    public function templateAction(): Action
    {
        return Action::make('template')
            ->label('Örnek şablonu indir')
            ->link()
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->url(route('customers.import.template'));
    }

    public function downloadFailuresAction(): Action
    {
        return Action::make('downloadFailures')
            ->label('Hatalı satırları indir')
            ->color('gray')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->visible(fn () => $this->result['failures'] !== [])
            ->action(fn () => $this->downloadFailures());
    }

    public function buildPreview(): void
    {
        $state = $this->form->getState();

        if (! self::isImportPath($state['file'] ?? null)) {
            $this->failAndReset('Dosya bulunamadı', 'Dosyayı yeniden seçin.');

            return;
        }

        $this->storedPath = $state['file'];
        $this->originalName = $state['original_name'] ?? basename((string) $state['file']);

        try {
            $sheets = Excel::toArray(new CustomersPreviewReader, $this->storedPath, 'local');
        } catch (Throwable $e) {
            Log::warning('İçe aktarım önizlemesi okunamadı.', ['dosya' => $this->originalName, 'hata' => $e->getMessage()]);
            $this->failAndReset('Dosya okunamadı', 'Dosya bozuk ya da desteklenmeyen biçimde. Örnek şablonla karşılaştırın.');

            return;
        }

        // Satır numarası korunsun diye boş satırlar okunur, burada atlanır.
        $rows = array_filter($sheets[0] ?? [], fn (array $row) => ! self::isEmptyRow($row));

        if ($rows === []) {
            $this->failAndReset('Dosyada veri yok', 'Başlıklar 2. satırda, veri 3. satırdan başlamalı.');

            return;
        }

        if (! $this->hasKnownHeadings(array_keys(reset($rows)))) {
            $this->failAndReset('Başlıklar tanınmadı', '2. satırda "Hizmet Alan İşyeri Unvanı" başlığı bulunamadı. Örnek şablonu kullanın.');

            return;
        }

        $preview = ['total' => count($rows), 'valid' => 0, 'invalid' => 0, 'rows' => []];

        foreach ($rows as $index => $row) {
            $data = CustomerRowMapper::map($row);
            $errors = CustomerRowMapper::errors($data);
            $errors === [] ? $preview['valid']++ : $preview['invalid']++;

            if (count($preview['rows']) < self::PREVIEW_ROWS) {
                $preview['rows'][] = [
                    'line' => $index + 3,
                    'unvan' => $data['hizmet_alan_isyeri_unvani'],
                    'firma_no' => $data['firma_no'],
                    'il' => $data['il'],
                    'tutar' => $data['tutar'],
                    'odeme' => OdemeDurumu::tryFrom((string) $data['odeme_durumu'])?->getLabel(),
                    'errors' => $errors,
                ];
            }
        }

        $this->preview = $preview;
        $this->step = 2;
    }

    public function runImport(): void
    {
        if (! self::isImportPath($this->storedPath) || ! Storage::disk('local')->exists($this->storedPath)) {
            $this->failAndReset('Dosya bulunamadı', 'Dosyayı yeniden seçin.');

            return;
        }

        $import = new CustomersImport;

        try {
            // Dosya ortasında hata olursa önceki parçalar da geri alınır: ya hepsi ya hiçbiri.
            DB::transaction(fn () => Excel::import($import, $this->storedPath, 'local'));
        } catch (Throwable $e) {
            Log::error('Müşteri içe aktarımı başarısız.', ['dosya' => $this->originalName, 'kullanici' => auth()->id(), 'exception' => $e]);
            Notification::make()
                ->title('İçe aktarım tamamlanmadı')
                ->body('Beklenmeyen bir hata oluştu. Hiçbir değişiklik kaydedilmedi. Sistem günlüğüne bakın.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $this->result = [
            'inserted' => $import->inserted,
            'updated' => $import->updated,
            'skipped' => $import->skipped(),
            'failures' => $import->failures()
                ->map(fn (Failure $failure) => [
                    'line' => $failure->row(),
                    'field' => (string) $failure->attribute(),
                    'message' => implode(' ', $failure->errors()),
                ])
                ->sortBy('line')
                ->values()
                ->all(),
        ];

        CustomerAudit::record('imported', null, [
            'dosya' => $this->originalName,
            'eklenen' => $this->result['inserted'],
            'guncellenen' => $this->result['updated'],
            'atlanan' => $this->result['skipped'],
            // Upsert observer'ı atlar; etkilenen kayıtlar burada izlenir.
            'firma_no' => $import->firmaNumbers(),
        ]);

        Storage::disk('local')->delete($this->storedPath);
        $this->storedPath = null;
        $this->step = 3;

        Notification::make()
            ->title('İçe aktarım tamamlandı')
            ->body("{$this->result['inserted']} eklendi, {$this->result['updated']} güncellendi, {$this->result['skipped']} satır atlandı.")
            ->success()
            ->send();
    }

    public function downloadFailures(): BinaryFileResponse
    {
        $rows = collect($this->result['failures'])
            ->groupBy('line')
            ->map(fn ($items, $line) => ['line' => $line, 'messages' => $items->pluck('message')->unique()->join(' ')])
            ->values();

        return Excel::download(new ImportFailuresExport($rows->all()), 'hatali-satirlar.xlsx');
    }

    public function restart(): void
    {
        if (self::isImportPath($this->storedPath)) {
            Storage::disk('local')->delete($this->storedPath);
        }

        $this->reset(['step', 'storedPath', 'originalName', 'preview', 'result']);
        $this->form->fill();
    }

    /** Yol yalnız içe aktarım klasöründe bir dosyayı gösterebilir. */
    private static function isImportPath(mixed $path): bool
    {
        return is_string($path)
            && str_starts_with($path, self::DIRECTORY.'/')
            && ! str_contains($path, '..')
            && ! str_contains($path, '\\');
    }

    /** @param  list<int|string>  $headings */
    private function hasKnownHeadings(array $headings): bool
    {
        return in_array('hizmet_alan_isyeri_unvani', $headings, true);
    }

    private function failAndReset(string $title, string $body): void
    {
        Notification::make()->title($title)->body($body)->danger()->send();

        if (self::isImportPath($this->storedPath)) {
            Storage::disk('local')->delete($this->storedPath);
        }

        $this->storedPath = null;
        $this->step = 1;
        $this->form->fill();
    }

    /** @param  array<string, mixed>  $row */
    private static function isEmptyRow(array $row): bool
    {
        return array_filter($row, fn ($value) => $value !== null && $value !== '') === [];
    }
}
