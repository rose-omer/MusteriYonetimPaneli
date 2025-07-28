<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\FaaliyetManager; // FaaliyetManager'ı import edin
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// use App\Imports\CustomersImport; // Eğer kullanmıyorsanız bu satırı silebilirsiniz
// use Maatwebsite\Excel\Facades\Excel; // Eğer kullanmıyorsanız bu satırı silebilirsiniz
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $faaliyetManager;

    // Constructor ile FaaliyetManager'ı otomatik olarak enjekte edin
    public function __construct(FaaliyetManager $faaliyetManager)
    {
        $this->faaliyetManager = $faaliyetManager;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::paginate(10); // Müşterileri sayfalayarak getir
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Faaliyetleri FaaliyetManager servisinden çekiyoruz
        $faaliyetler = $this->faaliyetManager->getFaaliyetler();

        $tehlike_siniflari = ['Tehlikeli', 'Az Tehlikeli', 'Çok Tehlikeli'];
        $fatura_tipleri = ['AYLIK', 'YILLIK', 'HAFTALIK'];
        $fatura_durumlari = ['KESİLDİ', 'KESİLMEDİ', 'İPTAL'];
        $odeme_durumlari = ['ÖDENMİŞ', 'ÖDENMEMİŞ', 'KISMİ ÖDENMİŞ'];

        return view('customers.create', compact('faaliyetler', 'tehlike_siniflari', 'fatura_tipleri', 'fatura_durumlari', 'odeme_durumlari'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firma_no' => 'nullable|string|max:255',
            'hizmet_alan_isyeri_sgk_detsis_no' => 'nullable|string|max:255|unique:customers',
            'hizmet_alan_isyeri_unvani' => 'required|string|max:255',
            'gorunur_unvani' => 'nullable|string|max:255',
            'faaliyet' => 'nullable|string|max:255', // Faaliyetin buradan gelmesi beklenecek
            'hizmet_alan_isyeri_calisan_sayisi' => 'nullable|integer',
            'hizmet_alan_isyeri_tehlike_sinifi' => 'nullable|string|max:255',
            'firma_sorumlusu' => 'nullable|string|max:255',
            'yetkili_kisi_telefon_numarasi' => 'nullable|string|max:255',
            'hizmet_alan_isyeri_ili' => 'nullable|string|max:255',
            'mahalle' => 'nullable|string|max:255',
            'konum' => 'nullable|string|max:255',
            'fatura_tipi' => 'nullable|string|max:255',
            'fatura_durumu' => 'nullable|string|max:255',
            'tutar' => 'nullable|numeric',
            'odeme' => 'nullable|string|max:255',
            'sozlesme_onay_durumu' => 'nullable|boolean',
            'igu' => 'nullable|string|max:255',
            'ih' => 'nullable|string|max:255',
            'firma_ziyareti' => 'nullable|date',
            'defter' => 'nullable|string|max:255',
            'ra' => 'nullable|string|max:255',
            // Diğer alanlar
        ]);

        Customer::create($validatedData);

        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla eklendi.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        // Faaliyetleri FaaliyetManager servisinden çekiyoruz
        $faaliyetler = $this->faaliyetManager->getFaaliyetler();

        $tehlike_siniflari = ['Tehlikeli', 'Az Tehlikeli', 'Çok Tehlikeli'];
        $fatura_tipleri = ['W30', 'W45', 'HAZIRLANACAK']; // Fatura tiplerini kontrol edin
        $fatura_durumlari = ['TESLİM EDİLECEK', 'TESLİM EDİLDİ', 'İPTAL']; // Fatura durumlarını kontrol edin
        $odeme_durumlari = ['ÖDENMİŞ', 'ÖDENMEMİŞ', 'KISMİ ÖDENMİŞ'];

        return view('customers.edit', compact('customer', 'faaliyetler', 'tehlike_siniflari', 'fatura_tipleri', 'fatura_durumlari', 'odeme_durumlari'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'firma_no' => 'nullable|string|max:255',
            'hizmet_alan_isyeri_sgk_detsis_no' => ['nullable', 'string', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'hizmet_alan_isyeri_unvani' => 'required|string|max:255',
            'gorunur_unvani' => 'nullable|string|max:255',
            'faaliyet' => 'nullable|string|max:255',
            'hizmet_alan_isyeri_calisan_sayisi' => 'nullable|integer',
            'hizmet_alan_isyeri_tehlike_sinifi' => 'nullable|string|max:255',
            'firma_sorumlusu' => 'nullable|string|max:255',
            'yetkili_kisi_telefon_numarasi' => 'nullable|string|max:255',
            'hizmet_alan_isyeri_ili' => 'nullable|string|max:255',
            'mahalle' => 'nullable|string|max:255',
            'konum' => 'nullable|string|max:255',
            'fatura_tipi' => 'nullable|string|max:255',
            'fatura_durumu' => 'nullable|string|max:255',
            'tutar' => 'nullable|numeric',
            'odeme' => 'nullable|string|max:255',
            'sozlesme_onay_durumu' => 'nullable|boolean',
            'igu' => 'nullable|string|max:255',
            'ih' => 'nullable|string|max:255',
            'firma_ziyareti' => 'nullable|date',
            'defter' => 'nullable|string|max:255',
            'ra' => 'nullable|string|max:255',
            // Diğer alanlar
        ]);

        $customer->update($validatedData);

        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla silindi.');
    }

    // YENİ EKLENEN METOD: Faaliyet Yönetimi sayfasını gösterir
    public function manageActivities()
    {
        // Bu sayfada sadece Livewire bileşeni yüklenecek.
        // Livewire bileşeni kendi FaaliyetManager'ını enjekte edecektir.
        return view('activities.manage');
    }

    // Müşteri import formu için metod (Eğer kullanıyorsanız)
    public function createImportForm()
    {
        return view('customers.import');
    }

    // Müşteri import işlemi için metod (Eğer kullanıyorsanız)
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
        ]);

        try {
            // Excel::import(new CustomersImport, $request->file('file')); // CustomersImport'u kullanıyorsanız yorumu kaldırın

            return redirect()->back()->with('success', 'Müşteri listesi başarıyla yüklendi ve entegre edildi!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Satır ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            Log::error('Excel Import Validation Failed: ' . json_encode($errorMessages));
            return redirect()->back()->withErrors($errorMessages)->withInput();
        } catch (\Exception $e) {
            Log::error('Excel Import Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Dosya yüklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }
}