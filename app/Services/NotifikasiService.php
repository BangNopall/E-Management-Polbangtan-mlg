<?php

namespace App\Services;

use App\Models\Notifikasi;

class NotifikasiService
{
    /**
     * Kirim notifikasi in-app ke pengguna tertentu.
     */
    public function kirim(int $userId, string $judul, string $pesan, ?string $link = null): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Hitung jumlah notifikasi belum dibaca untuk user.
     */
    public function countUnread(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)->unread()->count();
    }
}
