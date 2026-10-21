<?php

namespace App\Console\Commands;

use App\Services\QrTokenService;
use Illuminate\Console\Command;

class GenerateQrKeys extends Command
{
    protected $signature = 'qr:keys';

    protected $description = 'Generate the ES256 key pair used to sign student check-in tokens';

    public function handle(QrTokenService $qr): int
    {
        $qr->publicKeyPem();

        $this->info('QR signing keys are ready.');
        $this->line('Private key: '.config('mess.qr.private_key_path'));
        $this->line('Public key:  '.config('mess.qr.public_key_path'));
        $this->line('Fingerprint: '.$qr->publicKeyFingerprint());

        return self::SUCCESS;
    }
}
