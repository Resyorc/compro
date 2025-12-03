@extends('components.main')
@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Dashboard</a>
        </li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page"></li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Dashboard</h6>
@endsection
@section('content')
    <!-- End Navbar -->
    @include('components.dashboard.statistic')

    @if (auth()->user()->hasRole('guru'))
        @if ($myData == null)
            <div class="row">
                <div class="col-lg-12 col-md-6 mb-4">
                    <div class="card z-index-2 ">
                        <h4 style="text-align: center; width: 100%; padding: 40px 10px">Anda Tidak
                            memiliki informasi pribadi
                        </h4>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-12 col-md-6 mb-4">
                    <div class="card z-index-2 ">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                <div class="chart">
                                    {{-- <canvas id="chart-bars" class="chart-canvas" height="170"></canvas> --}}
                                    <h6 class="text-white text-capitalize ps-3">Data Guru
                                        {{-- {{ $kelas->guru_id ? 'ada' : 'tidak' }} --}}
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="foto">
                                        <img src="{{ asset('storage/guru/img/' . $myData?->foto) ?? asset('storage/guru/img/default_img.png') }}"
                                            alt="" width="100%" height="auto">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">NIP</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->nip ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Nama</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->nama ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Jenis
                                                        Kelamin</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->jenis_kelamin ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Tempat,
                                                        tanggal
                                                        lahir</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->tempat_lahir ?? '' }}
                                                    @if ($myData->tanggal_lahir)
                                                        {{ \Carbon\Carbon::parse($myData->tanggal_lahir)->format('d-m-Y') ?? '' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">No Telepon
                                                    </span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->no_telp ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Agama</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->agama ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Alamat</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->alamat ?? '' }}
                                                </div>
                                            </div>
                                        </li>
                                        @if ($myData)
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <span class="float-start fw-bold">Wali Kelas</span>
                                                        <div class="float-end">:</div>
                                                    </div>
                                                    <div class="col-md-7">
                                                        {{ $myData->kelas->nama_kelas ?? '' }}
                                                    </div>
                                                </div>
                                            </li>
                                        @else
                                        @endif
                                    </ul>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4 class="card-title">Pengumuman</h4>
                                    </div>
                                    <div class="card-body">
                                        @if ($pengumumans->isEmpty())
                                            <p class="text-muted">Tidak ada pengumuman saat ini.</p>
                                        @else
                                            <ul class="list-group">
                                                @foreach ($pengumumans as $pengumuman)
                                                    <li class="list-group-item">
                                                        <h5>{{ $pengumuman->title }}</h5>
                                                        <p>{{ $pengumuman->message }}</p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="card-header">
                                        <h4 class="card-title">Pengumuman Tamu</h4>
                                    </div>
                                    <div class="card-body">

                                        @if ($tamu_pesans->where('status', '!=', 'pesan_telah_selesai')->isEmpty())

                                            <p class="text-muted">Tidak ada tamu saat ini.</p>
                                        @else
                                            <ul class="list-group">
                                                @foreach ($tamu_pesans as $tamu_pesan)
                                                    @if ($tamu_pesan->status !== 'pesan_telah_selesai')
                                                            <div class="row">
                                                                <div class="col-md-8">  <!-- Kolom untuk data -->
                                                                    <li class="list-group-item">
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <span class="float-start">Nama Tamu </span>
                                                                                    <div class="float-end">:</div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    {{ $tamu_pesan->nama }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <span class="float-start">Alamat </span>
                                                                                    <div class="float-end">:</div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    {{ $tamu_pesan->alamat }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <span class="float-start">Keperluan </span>
                                                                                    <div class="float-end">: </div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    {{ $tamu_pesan->Keterangan }}
                                                                                </div>
                                                                            </div>
                                                                    </li>
                                                                </div>
                                                                <div class="col-md-4"> <!-- Kolom untuk tombol -->
                                                                    <div class="col">
                                                                        <div class="col-md-6">
                                                                            <form action="{{ route('dashboard.terimaPesan', ['id' => $tamu_pesan->id]) }}" method="POST">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-success">Pesan Diterima</button>
                                                                            </form>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <form action="{{ route('dashboard.hapusPesan', ['id' => $tamu_pesan->id]) }}" method="POST">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-danger">Hapus Pesan</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @elseif(auth()->user()->hasRole('siswa'))
        @if ($myData == null)
            <div class="row">
                <div class="col-lg-12 col-md-6 mb-4">
                    <div class="card z-index-2 ">
                        <h4 style="text-align: center; width: 100%; padding: 40px 10px">Anda Tidak
                            memiliki informasi pribadi
                        </h4>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-12 col-md-6 mb-4">
                    <div class="card z-index-2 ">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                <div class="chart">
                                    <h6 class="text-white text-capitalize ps-3">Data Siswa</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="foto">
                                        <img src="{{ $myData->foto ? asset('storage/murid/img/' . $myData->foto) : asset('storage/murid/img/default_img.png') }}"
                                            alt="" width="100%" height="auto">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">NISN</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->nisn }}
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Nama</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->nama }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Kelas</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->kelas->nama_kelas }}

                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Jenis
                                                        Kelamin</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7 text-capitalize">
                                                    {{ $myData->jenis_kelamin }}
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">No Telepon
                                                    </span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->no_telp }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Agama</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->agama }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <span class="float-start fw-bold">Alamat</span>
                                                    <div class="float-end">:</div>
                                                </div>
                                                <div class="col-md-7">
                                                    {{ $myData->alamat }}

                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="card mt-4" id="ai-profiling-card" data-endpoint="{{ $aiProfilingEndpoint }}">
                                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                            <div>
                                                <h4 class="card-title mb-0">AI Profil Siswa</h4>
                                                <small class="text-muted" id="ai-profiling-subtitle">
                                                    Profil belum digenerate. Klik tombol untuk memulai.
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="ai-profiling-trigger" {{ $aiProfilingEndpoint ? '' : 'disabled' }}>
                                                <span class="spinner-border spinner-border-sm me-2 d-none" id="ai-profiling-spinner" role="status" aria-hidden="true"></span>
                                                <span id="ai-profiling-trigger-text">Generate Profil AI</span>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            @if (!$aiProfilingEndpoint)
                                                <div class="alert alert-warning mb-0">
                                                    Profil AI belum dapat digunakan karena endpoint tidak tersedia.
                                                </div>
                                            @else
                                                <div id="ai-profiling-status" class="alert alert-info mb-3">
                                                    Profil AI belum diminta. Tekan tombol untuk mengirim data ke layanan AI.
                                                </div>
                                                <div id="ai-profiling-error" class="alert alert-warning d-none"></div>
                                                <div id="ai-profiling-content" class="d-none">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                                        <div>
                                                            <small class="text-muted">Segmen</small>
                                                            <div class="fw-bold text-uppercase" id="ai-profile-segmentation">-</div>
                                                        </div>
                                                        <span class="badge bg-gradient-secondary text-uppercase" id="ai-profile-risk">Risiko -</span>
                                                    </div>
                                                    <p class="mb-4" id="ai-profile-summary">Tidak ada ringkasan tersedia.</p>
                                                    <div class="row text-center">
                                                        <div class="col-md-4 mb-3">
                                                            <small class="text-uppercase text-muted">Indeks Akademik</small>
                                                            <h5 class="mb-0" id="ai-profile-academic">-</h5>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <small class="text-uppercase text-muted">Kehadiran</small>
                                                            <h5 class="mb-0" id="ai-profile-attendance">-</h5>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <small class="text-uppercase text-muted">Indeks Keterlibatan</small>
                                                            <h5 class="mb-0" id="ai-profile-engagement">-</h5>
                                                        </div>
                                                    </div>
                                                    <div id="ai-profile-recommendations" class="d-none">
                                                        <h6 class="mt-4 mb-2">Rekomendasi Tindakan</h6>
                                                        <ul class="list-group list-group-flush" id="ai-profile-recommendations-list"></ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4 class="card-title">Pengumuman</h4>
                                    </div>
                                    <div class="card-body">
                                        @if ($pengumumans->isEmpty())
                                            <p class="text-muted">Tidak ada pengumuman saat ini.</p>
                                        @else
                                            <ul class="list-group">
                                                @foreach ($pengumumans as $pengumuman)
                                                    <li class="list-group-item">
                                                        <h5>{{ $pengumuman->title }}</h5>
                                                        <p>{{ $pengumuman->message }}</p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endif
    @endif

    {{-- Tamu status --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        function hapusPesan(id) {
            // Lakukan permintaan Ajax untuk menghapus pesan
            $.ajax({
                url: '/dashboard/' + id,
                type: 'DELETE',
                success: function(response) {
                    // Sembunyikan elemen dengan ID yang sesuai
                    $('#pesan-' + id).hide();
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>  --}}

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const card = document.getElementById('ai-profiling-card');
        if (!card) {
            return;
        }

        const endpoint = card.dataset.endpoint;
        const trigger = document.getElementById('ai-profiling-trigger');
        const triggerText = document.getElementById('ai-profiling-trigger-text');
        const spinner = document.getElementById('ai-profiling-spinner');
        const statusBox = document.getElementById('ai-profiling-status');
        const errorBox = document.getElementById('ai-profiling-error');
        const contentBox = document.getElementById('ai-profiling-content');
        const subtitle = document.getElementById('ai-profiling-subtitle');
        const summary = document.getElementById('ai-profile-summary');
        const segmentation = document.getElementById('ai-profile-segmentation');
        const riskBadge = document.getElementById('ai-profile-risk');
        const academic = document.getElementById('ai-profile-academic');
        const attendance = document.getElementById('ai-profile-attendance');
        const engagement = document.getElementById('ai-profile-engagement');
        const recWrapper = document.getElementById('ai-profile-recommendations');
        const recList = document.getElementById('ai-profile-recommendations-list');

        const riskClasses = {
            high: 'bg-gradient-danger',
            moderate: 'bg-gradient-warning',
            low: 'bg-gradient-success',
        };

        const formatNumber = (value, suffix = '') => {
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            const number = Number(value);
            if (Number.isFinite(number)) {
                return `${Number(number.toFixed(2))}${suffix}`;
            }
            return `${value}${suffix}`;
        };

        function setLoading(isLoading) {
            if (!trigger) {
                return;
            }
            trigger.disabled = isLoading;
            if (spinner) {
                spinner.classList.toggle('d-none', !isLoading);
            }
            if (triggerText) {
                triggerText.textContent = isLoading ? 'Memproses...' : 'Generate Profil AI';
            }
        }

        function resetContent() {
            if (summary) summary.textContent = 'Tidak ada ringkasan tersedia.';
            if (segmentation) segmentation.textContent = '-';
            if (riskBadge) {
                riskBadge.className = 'badge bg-gradient-secondary text-uppercase';
                riskBadge.textContent = 'Risiko -';
            }
            if (academic) academic.textContent = '-';
            if (attendance) attendance.textContent = '-';
            if (engagement) engagement.textContent = '-';
            if (recList) recList.innerHTML = '';
            if (recWrapper) recWrapper.classList.add('d-none');
        }

        async function ensureCsrfCookie() {
            // Sanctum butuh cookie XSRF untuk permintaan API yang memakai auth:sanctum.
            await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        }

        function getCookie(name) {
            return document.cookie.split(';').map(c => c.trim()).find(c => c.startsWith(name + '='))?.split('=')[1] ?? '';
        }

        async function fetchProfiling() {
            if (!endpoint) {
                return;
            }

            setLoading(true);
            resetContent();

            if (errorBox) {
                errorBox.classList.add('d-none');
                errorBox.textContent = '';
            }
            if (contentBox) {
                contentBox.classList.add('d-none');
            }
            if (statusBox) {
                statusBox.classList.remove('d-none');
                statusBox.textContent = 'Mengirim data ke layanan AI...';
            }
            if (subtitle) {
                subtitle.textContent = 'Permintaan sedang diproses. Mohon tunggu...';
            }

            try {
                await ensureCsrfCookie();
                const xsrfToken = decodeURIComponent(getCookie('XSRF-TOKEN') || '');

                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': xsrfToken,
                    },
                    credentials: 'include',
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Profil AI tidak dapat dimuat.');
                }

                renderProfiling(payload.data);
            } catch (error) {
                if (errorBox) {
                    errorBox.textContent = error?.message || 'Profil AI tidak dapat dimuat.';
                    errorBox.classList.remove('d-none');
                }
                if (statusBox) {
                    statusBox.classList.add('d-none');
                }
                if (subtitle) {
                    subtitle.textContent = 'Gagal memuat profil AI. Coba lagi.';
                }
            } finally {
                setLoading(false);
            }
        }

        function renderProfiling(data) {
            if (!data) {
                return;
            }

            if (statusBox) {
                statusBox.classList.add('d-none');
            }
            if (subtitle) {
                subtitle.textContent = 'Profil berhasil dibuat dari layanan AI.';
            }
            if (contentBox) {
                contentBox.classList.remove('d-none');
            }

            if (summary) summary.textContent = data.summary || 'Tidak ada ringkasan tersedia.';
            if (segmentation) segmentation.textContent = data.segmentation || '-';

            const riskLevel = String(data.risk_level || '').toLowerCase();
            const riskClass = riskClasses[riskLevel] || 'bg-gradient-secondary';
            if (riskBadge) {
                riskBadge.className = `badge ${riskClass} text-uppercase`;
                riskBadge.textContent = `Risiko ${data.risk_level || '-'}`;
            }

            if (academic) academic.textContent = formatNumber(data.academic_index);
            if (attendance) attendance.textContent = formatNumber(data.attendance_rate, '%');
            if (engagement) engagement.textContent = formatNumber(data.engagement_index);

            const recommendations = Array.isArray(data.recommendations) ? data.recommendations : [];
            if (recList) {
                recList.innerHTML = '';
                recommendations.forEach((rec) => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item';
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-gradient-info text-uppercase me-2';
                    badge.textContent = rec.category || 'Rekomendasi';
                    item.appendChild(badge);
                    item.appendChild(document.createTextNode(rec.message || '-'));
                    recList.appendChild(item);
                });
            }
            if (recWrapper) {
                recWrapper.classList.toggle('d-none', recommendations.length === 0);
            }
        }

        if (trigger && endpoint) {
            trigger.addEventListener('click', fetchProfiling);
        }
    });
</script>
@endsection
