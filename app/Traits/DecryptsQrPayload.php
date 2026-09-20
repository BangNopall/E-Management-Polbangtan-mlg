<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Exception;

trait DecryptsQrPayload
{
    /**
     * Mengekstrak dan mendekripsi payload dari QR Code yang disubmit (jika ada).
     * Hasilnya akan dimerge kembali ke Request agar divalidasi oleh controller.
     */
    protected function decryptAndMergePayload(Request $request): void
    {
        if ($request->filled('payload')) {

        try {
            $decryptedJson = Crypt::decryptString($request->input('payload'));
            $payloadData = json_decode($decryptedJson, true);

            if (is_array($payloadData)) {
                // Anti-replay check via nonce with 60-second TTL
                if (! empty($payloadData['nonce'])) {
                    $nonce = $payloadData['nonce'];
                    $cacheKey = "qr_nonce:{$nonce}";
                    if (! \Illuminate\Support\Facades\Cache::add($cacheKey, true, 60)) {
                        throw new Exception('Kode QR ini sudah pernah digunakan.');
                    }
                }

                $request->merge($payloadData);
            } else {
                throw new Exception('Format QR Code terenkripsi tidak valid.');
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'Kode QR ini sudah pernah digunakan.') {
                throw $e;
            }
            throw new Exception('Kode QR tidak valid atau telah kadaluarsa (Gagal Dekripsi).');
        }
        }
    }
}
