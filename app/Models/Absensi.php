<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;
    protected $table = 'absensis';

    protected $fillable = [
        'detail_jadwal_id',
        'kelas_id',
        'mapel_id',
        'guru_id',
        'siswa_id',
        'tanggal_pertemuan',
        'pertemuan_ke',
        'status_absen',
        'catatan',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function detailJadwal()
    {
        return $this->belongsTo(Detail_jadwal::class, 'detail_jadwal_id');
    }
}
