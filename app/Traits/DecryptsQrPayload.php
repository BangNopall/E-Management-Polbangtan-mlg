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
                    $request->merge($payloadData);
                } else {
                    throw new Exception('Format QR Code terenkripsi tidak valid.');
                }
            } catch (Exception $e) {
                // Lempar ke pemanggil untuk dihandle return redirect()->back()->with('error',...)
                throw new Exception('Kode QR tidak valid atau telah kadaluarsa (Gagal Dekripsi).');
            }
        }
    }
}
