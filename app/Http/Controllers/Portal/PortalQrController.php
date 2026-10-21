<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\QrTokenService;
use Symfony\Component\HttpFoundation\Response;

class PortalQrController extends Controller
{
    public function publicKey(QrTokenService $tokens): Response
    {
        return response($tokens->publicKeyPem(), 200, [
            'Content-Type' => 'application/x-pem-file',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
