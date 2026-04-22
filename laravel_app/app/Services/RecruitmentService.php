<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RecruitmentService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.recruitment.connection', config('database.default'));
    }

    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    private function query(): Builder
    {
        return DB::connection($this->connection())
            ->table('dottuyendung as dt')
            ->leftJoin('hosoungtuyen as hs', 'hs.MaDTD', '=', 'dt.MaDTD')
            ->select([
                'dt.MaDTD',
                'dt.TenDotTuyenDung',
                'dt.ViTriTuyenDung',
                'dt.SoLuong',
                'dt.TuNgay',
                'dt.DenNgay',
                'dt.TrangThai',
                'dt.MoTa',
                DB::raw('COUNT(hs.MaHS) as SoHoSo'),
            ])
            ->groupBy(
                'dt.MaDTD',
                'dt.TenDotTuyenDung',
                'dt.ViTriTuyenDung',
                'dt.SoLuong',
                'dt.TuNgay',
                'dt.DenNgay',
                'dt.TrangThai',
                'dt.MoTa'
            );
    }

    private function candidateQuery(): Builder
    {
        return DB::connection($this->connection())
            ->table('ungvien as uv')
            ->select([
                'uv.MaUV',
                'uv.HoTen',
                'uv.NgaySinh',
                'uv.GioiTinh',
                'uv.Email',
                'uv.DienThoai',
                'uv.TrinhDo',
                'uv.KinhNghiem',
                'uv.FileCV',
                'uv.DiemCV',
                DB::raw('(SELECT COUNT(*) FROM hosoungtuyen hs WHERE hs.MaUV = uv.MaUV) as SoHoSo'),
            ]);
    }

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('dt.TenDotTuyenDung', 'like', "%{$keyword}%")
                        ->orWhere('dt.ViTriTuyenDung', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('dt.TrangThai', (string) $filters['status']);
            })
            ->orderByDesc('dt.MaDTD')
            ->paginate($perPage);
    }

    public function find(int $campaignId): ?array
    {
        $item = DB::connection($this->connection())
            ->table('dottuyendung')
            ->where('MaDTD', $campaignId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function campaignOptions(bool $openOnly = false): Collection
    {
        return DB::connection($this->connection())
            ->table('dottuyendung')
            ->when($openOnly, fn (Builder $query) => $query->where('TrangThai', 'Đang tuyển'))
            ->orderByDesc('TuNgay')
            ->get();
    }

    public function paginateCandidates(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->candidateQuery()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('uv.HoTen', 'like', "%{$keyword}%")
                        ->orWhere('uv.Email', 'like', "%{$keyword}%")
                        ->orWhere('uv.DienThoai', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['score']), function (Builder $query) use ($filters) {
                $query->where('uv.DiemCV', '>=', (int) $filters['score']);
            })
            ->orderByDesc('uv.MaUV')
            ->paginate($perPage);
    }

    public function findCandidate(int $candidateId): ?array
    {
        $item = $this->candidateQuery()
            ->where('uv.MaUV', $candidateId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function createCandidate(array $payload, ?UploadedFile $cvFile = null): int
    {
        $storedFile = $cvFile ? $this->storeCv($cvFile) : ($payload['FileCV'] ?? null);

        return (int) DB::connection($this->connection())
            ->table('ungvien')
            ->insertGetId([
                'HoTen' => $payload['HoTen'],
                'NgaySinh' => $payload['NgaySinh'] ?? null,
                'GioiTinh' => $payload['GioiTinh'] ?? null,
                'Email' => $payload['Email'] ?? null,
                'DienThoai' => $payload['DienThoai'] ?? null,
                'TrinhDo' => $payload['TrinhDo'] ?? null,
                'KinhNghiem' => $payload['KinhNghiem'] ?? null,
                'FileCV' => $storedFile,
                'DiemCV' => $this->scoreCv($storedFile),
            ], 'MaUV');
    }

    public function paginateApplications(int $campaignId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return DB::connection($this->connection())
            ->table('hosoungtuyen as hs')
            ->join('ungvien as uv', 'uv.MaUV', '=', 'hs.MaUV')
            ->join('dottuyendung as dt', 'dt.MaDTD', '=', 'hs.MaDTD')
            ->select([
                'hs.MaHS',
                'hs.MaUV',
                'hs.MaDTD',
                'hs.TrangThai',
                'hs.NgayNop',
                'hs.GhiChu',
                'uv.HoTen',
                'uv.Email',
                'uv.DienThoai',
                'uv.FileCV',
                'uv.DiemCV',
                'dt.TenDotTuyenDung',
                'dt.ViTriTuyenDung',
                DB::raw('(SELECT COUNT(*) FROM lichphongvan lpv WHERE lpv.MaHS = hs.MaHS) as SoLichPhongVan'),
            ])
            ->where('hs.MaDTD', $campaignId)
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('uv.HoTen', 'like', "%{$keyword}%")
                        ->orWhere('uv.Email', 'like', "%{$keyword}%")
                        ->orWhere('uv.DienThoai', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('hs.TrangThai', (string) $filters['status']);
            })
            ->orderByDesc('hs.MaHS')
            ->paginate($perPage);
    }

    public function attachCandidateToCampaign(int $campaignId, int $candidateId, ?string $note = null): int
    {
        $connection = DB::connection($this->connection());
        $existingId = $connection->table('hosoungtuyen')
            ->where('MaDTD', $campaignId)
            ->where('MaUV', $candidateId)
            ->value('MaHS');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) $connection->table('hosoungtuyen')->insertGetId([
            'MaUV' => $candidateId,
            'MaDTD' => $campaignId,
            'TrangThai' => 'Nộp hồ sơ',
            'NgayNop' => now()->toDateString(),
            'GhiChu' => $note,
        ], 'MaHS');
    }

    public function findApplication(int $applicationId): ?array
    {
        $item = DB::connection($this->connection())
            ->table('hosoungtuyen as hs')
            ->join('ungvien as uv', 'uv.MaUV', '=', 'hs.MaUV')
            ->join('dottuyendung as dt', 'dt.MaDTD', '=', 'hs.MaDTD')
            ->select([
                'hs.MaHS',
                'hs.MaUV',
                'hs.MaDTD',
                'hs.TrangThai',
                'hs.NgayNop',
                'hs.GhiChu',
                'uv.HoTen',
                'uv.Email',
                'uv.DienThoai',
                'uv.FileCV',
                'uv.DiemCV',
                'dt.TenDotTuyenDung',
                'dt.ViTriTuyenDung',
            ])
            ->where('hs.MaHS', $applicationId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function updateApplicationStatus(int $applicationId, array $payload): void
    {
        DB::connection($this->connection())->transaction(function () use ($applicationId, $payload) {
            DB::connection($this->connection())
                ->table('hosoungtuyen')
                ->where('MaHS', $applicationId)
                ->update([
                    'TrangThai' => $payload['TrangThai'],
                    'GhiChu' => $payload['GhiChu'] ?? null,
                ]);

            if (($payload['TrangThai'] ?? null) === 'Nhận việc') {
                $this->ensureEmployeeCreatedFromApplication($applicationId);
            }
        });
    }

    public function listInterviews(int $applicationId): Collection
    {
        return DB::connection($this->connection())
            ->table('lichphongvan')
            ->where('MaHS', $applicationId)
            ->orderByDesc('NgayPhongVan')
            ->orderByDesc('GioPhongVan')
            ->get();
    }

    public function createInterview(int $applicationId, array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('lichphongvan')
            ->insertGetId([
                'MaHS' => $applicationId,
                'NgayPhongVan' => $payload['NgayPhongVan'],
                'GioPhongVan' => $payload['GioPhongVan'],
                'DiaDiem' => $payload['DiaDiem'] ?? null,
                'GhiChu' => $payload['GhiChu'] ?? null,
                'KetQua' => $payload['KetQua'] ?? null,
            ], 'MaPV');
    }

    public function listReviews(int $applicationId): Collection
    {
        return DB::connection($this->connection())
            ->table('danhgiaphongvan')
            ->where('MaHS', $applicationId)
            ->orderByDesc('MaDG')
            ->get();
    }

    public function createReview(int $applicationId, array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('danhgiaphongvan')
            ->insertGetId([
                'MaHS' => $applicationId,
                'DiemKyNang' => $payload['DiemKyNang'],
                'DiemKinhNghiem' => $payload['DiemKinhNghiem'],
                'DiemThaiDo' => $payload['DiemThaiDo'],
                'NhanXet' => $payload['NhanXet'] ?? null,
            ], 'MaDG');
    }

    public function create(array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('dottuyendung')
            ->insertGetId($payload, 'MaDTD');
    }

    public function update(int $campaignId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('dottuyendung')
            ->where('MaDTD', $campaignId)
            ->update($payload);
    }

    public function delete(int $campaignId): void
    {
        DB::connection($this->connection())
            ->table('dottuyendung')
            ->where('MaDTD', $campaignId)
            ->delete();
    }

    private function ensureEmployeeCreatedFromApplication(int $applicationId): void
    {
        $hrConnection = DB::connection($this->hrConnection());

        if ($hrConnection->table('nhanvien')->where('MaHS', $applicationId)->exists()) {
            return;
        }

        $candidate = DB::connection($this->connection())
            ->table('hosoungtuyen as hs')
            ->join('ungvien as uv', 'uv.MaUV', '=', 'hs.MaUV')
            ->select([
                'uv.HoTen',
                'uv.GioiTinh',
                'uv.NgaySinh',
                'uv.Email',
                'uv.DienThoai',
            ])
            ->where('hs.MaHS', $applicationId)
            ->first();

        if (!$candidate) {
            return;
        }

        $hrConnection->table('nhanvien')->insert([
            'HoTen' => $candidate->HoTen,
            'GioiTinh' => $candidate->GioiTinh,
            'NgaySinh' => $candidate->NgaySinh,
            'Email' => $candidate->Email,
            'DienThoai' => $candidate->DienThoai,
            'TrangThai' => 'Đang làm',
            'MaBac' => null,
            'MaHS' => $applicationId,
        ]);
    }

    private function storeCv(UploadedFile $cvFile): string
    {
        $directory = dirname(base_path()) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv';
        File::ensureDirectoryExists($directory);

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $cvFile->getClientOriginalName()) ?: ('cv_' . time() . '.pdf');
        $fileName = time() . '_' . $safeName;
        $cvFile->move($directory, $fileName);

        return $fileName;
    }

    private function scoreCv(?string $fileName): int
    {
        if (!$fileName) {
            return 0;
        }

        $fullPath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv' . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($fullPath) || !class_exists('Smalot\\PdfParser\\Parser')) {
            return 0;
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $text = mb_strtolower($parser->parseFile($fullPath)->getText());
        } catch (\Throwable) {
            return 0;
        }

        $score = 0;
        $skills = [
            'php' => 3,
            'laravel' => 3,
            'mysql' => 2,
            'javascript' => 2,
            'html' => 1,
            'css' => 1,
            'react' => 2,
            'node' => 2,
            'git' => 1,
            'docker' => 2,
        ];

        foreach ($skills as $keyword => $value) {
            if (str_contains($text, $keyword)) {
                $score += $value;
            }
        }

        if (str_contains($text, 'đại học') || str_contains($text, 'university')) {
            $score += 2;
        }

        if (mb_strlen($text) > 1000) {
            $score += 1;
        }

        return min($score, 10);
    }
}