<?php

namespace App\Helpers\Koperasi\Pengajuan;

use Illuminate\Support\Facades\DB;
use App\Models\PengajuanPinjaman;

/**
 * Helper class untuk fungsi-fungsi authorization dan user management
 * terkait Pengajuan Pinjaman
 */
class PengajuanPinjamanAuthHelper
{
    /**
     * Get current username from session or user login data
     */
    public static function getCurrentUsername($data)
    {
        return session('username') ?? $data['user_login']->username;
    }

    /**
     * Get user role from login data
     */
    public static function getUserRole($data)
    {
        return $data['user_login']->idroles;
    }

    /**
     * Check if user has admin role that can bypass approval workflow
     */
    public static function isAdminUser($data)
    {
        $userRole = self::getUserRole($data);
        $adminRoles = ['kadmin', 'akredt', 'atrans', 'ketuum'];
        return in_array($userRole, $adminRoles);
    }

    /**
     * Filter pengajuan based on user role and approval status
     */
    public static function filterByRole($role, $username)
    {
        $query = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
            ->where('isactive', '1');

        switch ($role) {
            case 'kadmin': // Ketua Admin - review pertama
                return $query->where('status_pengajuan', 'diajukan')
                    ->whereNotExists(function($subquery) use ($username) {
                        $subquery->select(DB::raw(1))
                                 ->from('approval_history')
                                 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
                                 ->where('approval_history.user_create', $username)
                                 ->where('approval_history.isactive', '1');
                    });

            case 'akredt': // Admin Kredit - review kedua
                return $query->where('status_pengajuan', 'review_admin')
                    ->whereNotExists(function($subquery) use ($username) {
                        $subquery->select(DB::raw(1))
                                 ->from('approval_history')
                                 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
                                 ->where('approval_history.user_create', $username)
                                 ->where('approval_history.isactive', '1');
                    });

            case 'ketuum': // Ketua Umum - final approval
                return $query->where('status_pengajuan', 'review_panitia')
                    ->whereNotExists(function($subquery) use ($username) {
                        $subquery->select(DB::raw(1))
                                 ->from('approval_history')
                                 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
                                 ->where('approval_history.user_create', $username)
                                 ->where('approval_history.isactive', '1');
                    });

            default: // Default: show all pending approvals (for super admin)
                return $query->whereIn('status_pengajuan', ['diajukan', 'review_admin', 'review_panitia', 'review_ketua']);
        }
    }

    /**
     * Apply search filters to query
     */
    public static function applySearchFilter($query, $search)
    {
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('anggota', function($qa) use ($search) {
                      $qa->where('nama_lengkap', 'like', "%$search%")
                        ->orWhere('nomor_anggota', 'like', "%$search%");
                  });
            });
        }
        return $query;
    }

    /**
     * Apply authorization rules to query
     */
    public static function applyAuthorizationRules($query, $authorize, $userRoles)
    {
        if ($authorize->rules == '1') {
            $query->where(function ($q) use ($userRoles) {
                foreach ($userRoles as $role) {
                    $q->orWhereRaw("FIND_IN_SET(?, REPLACE(rules, ' ', ''))", [$role]);
                }
            });
        }
        return $query;
    }
}
