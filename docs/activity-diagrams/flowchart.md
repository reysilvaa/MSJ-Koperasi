# Flowchart Global Sistem Koperasi

```mermaid
flowchart TD
    Start(["Start"]) --> InputLogin[/"User Login Input:<br/>username & password"/]
    InputLogin --> Login{"Login<br/>berhasil?"}
    Login -->|No| OutputError[/"Output message:<br/>password atau username salah"/]
    Login -->|Yes| PICArea(["PIC Area Koperasi"])
    
    %% Area Utama Sistem - Global View
    PICArea -->|Anggota| ModulAnggota["Modul Anggota<br/>• Pengajuan Pinjaman<br/>• Kelola Iuran"]
    PICArea -->|Admin| ModulAdmin["Modul Admin<br/>• Verifikasi & Approval<br/>• Monitor Sistem"]
    PICArea -->|Management| ModulManagement["Modul Management<br/>• Dashboard Eksekutif<br/>• Laporan Global"]
    
    %% Proses Global Pinjaman
    ModulAnggota --> ProsePinjaman["Proses Global<br/>Pengajuan Pinjaman"]
    ProsePinjaman --> VerifikasiGlobal["Verifikasi & Approval<br/>Multi Level"]
    ModulAdmin --> VerifikasiGlobal
    
    VerifikasiGlobal --> HasilVerifikasi{"Hasil<br/>Verifikasi?"}
    HasilVerifikasi -->|Ditolak| NotifikasiTolak[/"Notifikasi<br/>Pengajuan Ditolak"/]
    HasilVerifikasi -->|Disetujui| PencairanDana[/"Pencairan Dana<br/>ke Anggota"/]
    
    %% Siklus Global Pembayaran
    PencairanDana --> SiklusGlobal["Siklus Global<br/>Pembayaran & Monitoring"]
    SiklusGlobal --> StatusLunas{"Status<br/>Lunas?"}
    StatusLunas -->|Belum| SiklusGlobal
    StatusLunas -->|Ya| SertifikatLunas[/"Sertifikat<br/>Pelunasan"/]
    
    %% Manajemen Global Iuran
    ModulAnggota --> ManajemenIuran["Manajemen Global<br/>Iuran Anggota"]
    ModulAdmin --> ManajemenIuran
    ManajemenIuran --> MonitoringIuran["Monitoring &<br/>Reminder Iuran"]
    
    %% Proses Global SHU (Tahunan)
    SertifikatLunas --> PeriodeGlobal["Periode Global<br/>Tahunan"]
    MonitoringIuran --> PeriodeGlobal
    ModulManagement --> PeriodeGlobal
    
    PeriodeGlobal --> TutupBukuGlobal["Tutup Buku Global<br/>& Hitung SHU"]
    TutupBukuGlobal --> DistribusiSHU[/"Distribusi SHU<br/>Global ke Semua Anggota"/]
    
    %% End Points
    NotifikasiTolak --> End(["End"])
    DistribusiSHU --> End
    OutputError --> End
    
    %% Styling
    classDef startEndBox fill:#e8f5e8,stroke:#2e7d32,stroke-width:3px
    classDef inputOutputBox fill:#fce4ec,stroke:#c2185b,stroke-width:2px
    classDef decisionBox fill:#fff3e0,stroke:#ef6c00,stroke-width:2px
    classDef picAreaBox fill:#f3e5f5,stroke:#7b1fa2,stroke-width:4px
    classDef modulBox fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef prosesGlobalBox fill:#f1f8e9,stroke:#388e3c,stroke-width:2px
    
    class Start,End startEndBox
    class InputLogin,OutputError,NotifikasiTolak,PencairanDana,SertifikatLunas,DistribusiSHU inputOutputBox
    class Login,HasilVerifikasi,StatusLunas decisionBox
    class PICArea picAreaBox
    class ModulAnggota,ModulAdmin,ModulManagement modulBox
    class ProsePinjaman,VerifikasiGlobal,SiklusGlobal,ManajemenIuran,MonitoringIuran,PeriodeGlobal,TutupBukuGlobal prosesGlobalBox
```

## Keterangan Flowchart Global Sistem Koperasi

### Konsep Global System:

#### 1. **PIC Area Koperasi (Central Hub)**
- Titik pusat distribusi akses berdasarkan role user
- Mengatur alur kerja global seluruh sistem

#### 2. **3 Modul Utama (High-Level)**
- **Modul Anggota**: Pengajuan pinjaman & kelola iuran
- **Modul Admin**: Verifikasi, approval, & monitoring sistem
- **Modul Management**: Dashboard eksekutif & laporan global

#### 3. **Proses Global Terintegrasi**
- **Proses Pinjaman**: Dari pengajuan hingga pencairan (global process)
- **Verifikasi Multi-Level**: Sistem approval terintegrasi
- **Siklus Pembayaran**: Monitoring global semua anggota
- **Manajemen Iuran**: Sistem iuran global seluruh anggota

#### 4. **Periode Global Tahunan**
- Tutup buku sistem secara global
- Distribusi SHU untuk seluruh anggota
- Reset sistem untuk periode baru

### Karakteristik Global:
- **Terintegrasi**: Semua proses saling terhubung
- **Terpusat**: PIC Area sebagai koordinator utama
- **Modular**: 3 modul utama yang fokus pada fungsi masing-masing
- **Scalable**: Dapat menangani banyak anggota secara bersamaan
- **Comprehensive**: Mencakup seluruh siklus hidup koperasi

### Alur Kerja Global:
1. **Login** → **PIC Area** → **Role-based Module**
2. **Global Process** → **Multi-Level Verification**
3. **Global Monitoring** → **Periodic Global Closure**
4. **Global Distribution** → **System Reset**

Flowchart ini memberikan pandangan global (high-level) dari sistem koperasi yang kompleks, mirip dengan struktur pada gambar referensi yang Anda berikan.
