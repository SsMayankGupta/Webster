<?php

namespace App\Services;

use App\Exceptions\RedemptionRejectedException;
use App\Models\MealBooking;
use App\Models\QrToken;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrTokenService
{
    private ?string $privateKey = null;

    private ?string $publicKey = null;

    public function __construct(private readonly MealLifecycleService $lifecycle) {}

    /**
     * Issue a short-lived, single-use, lane-pinned ES256 check-in token.
     *
     * @return array{jti: string, jwt: string, expires_at: string, lane: ?string, svg: string}
     */
    public function issue(MealBooking $booking): array
    {
        $booking->loadMissing('menu');
        $this->lifecycle->sync($booking->menu);

        if (! in_array($booking->menu->status, ['locked', 'serving'], true)) {
            throw new RedemptionRejectedException(
                'Check-in opens when the meal locks, four hours before serving.',
                409,
            );
        }

        if ($booking->assigned_lane === null) {
            $this->lifecycle->assignLanes($booking->menu);
            $booking->refresh();
        }

        $jti = (string) Str::uuid();
        $expiresAt = CarbonImmutable::now()->addSeconds((int) config('mess.qr.ttl_seconds'));

        $claims = [
            'iss' => (string) config('mess.qr.issuer'),
            'aud' => (string) config('mess.qr.audience'),
            'sub' => (string) $booking->student_id,
            'jti' => $jti,
            'booking_id' => $booking->id,
            'meal_id' => $booking->menu_id,
            'lane' => $booking->assigned_lane,
            'typ' => 'meal-checkin',
            'iat' => CarbonImmutable::now()->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ];

        $jwt = $this->sign($claims);

        QrToken::query()->create([
            'jti' => $jti,
            'booking_id' => $booking->id,
            'expires_at' => $expiresAt,
        ]);

        return [
            'jti' => $jti,
            'jwt' => $jwt,
            'expires_at' => $expiresAt->toIso8601String(),
            'lane' => $booking->assigned_lane,
            'svg' => $this->svg($jwt),
        ];
    }

    /**
     * Verify a token offline-style: the public key alone is enough.
     *
     * @return array<string, mixed>
     */
    public function verify(string $jwt): array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new RedemptionRejectedException('Malformed check-in token.', 422);
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = json_decode($this->base64UrlDecode($encodedHeader), true);

        if (! is_array($header) || ($header['alg'] ?? null) !== 'ES256') {
            throw new RedemptionRejectedException('Unsupported token algorithm.', 422);
        }

        $signature = $this->base64UrlDecode($encodedSignature);
        $verified = openssl_verify(
            $encodedHeader.'.'.$encodedPayload,
            $this->joseToDer($signature),
            $this->publicKey(),
            OPENSSL_ALGO_SHA256,
        );

        if ($verified !== 1) {
            throw new RedemptionRejectedException('Invalid token signature.', 422);
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (! is_array($payload)) {
            throw new RedemptionRejectedException('Malformed token payload.', 422);
        }

        if (($payload['iss'] ?? null) !== config('mess.qr.issuer')
            || ($payload['aud'] ?? null) !== config('mess.qr.audience')) {
            throw new RedemptionRejectedException('Token was issued for another system.', 422);
        }

        if ((int) ($payload['exp'] ?? 0) < CarbonImmutable::now()->getTimestamp()) {
            throw new RedemptionRejectedException('This token has expired. Refresh the QR and try again.', 422);
        }

        return $payload;
    }

    public function publicKeyPem(): string
    {
        return $this->publicKey();
    }

    public function publicKeyFingerprint(): string
    {
        $hash = hash('sha256', $this->publicKey());

        return implode(':', str_split(strtoupper(substr($hash, 0, 16)), 4));
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function sign(array $claims): string
    {
        $header = $this->base64UrlEncode(json_encode(
            ['alg' => 'ES256', 'typ' => 'JWT'],
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
        $payload = $this->base64UrlEncode(json_encode(
            $claims,
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));

        $signature = '';
        $key = openssl_pkey_get_private($this->privateKey());

        if ($key === false || ! openssl_sign($header.'.'.$payload, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the check-in token.');
        }

        return $header.'.'.$payload.'.'.$this->base64UrlEncode($this->derToJose($signature));
    }

    private function svg(string $jwt): string
    {
        return (string) QrCode::format('svg')
            ->size(260)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($jwt);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padded = str_pad(strtr($value, '-_', '+/'), (int) (ceil(strlen($value) / 4) * 4), '=');

        $decoded = base64_decode($padded, true);

        if ($decoded === false) {
            throw new RedemptionRejectedException('Malformed token encoding.', 422);
        }

        return $decoded;
    }

    /**
     * Convert an OpenSSL DER ECDSA signature to the fixed-width JOSE format.
     */
    private function derToJose(string $der): string
    {
        $offset = 0;

        if (($der[$offset++] ?? null) !== "\x30") {
            throw new RuntimeException('Invalid DER signature.');
        }

        $lengthByte = ord($der[$offset++]);

        if (($lengthByte & 0x80) !== 0) {
            $lengthBytes = $lengthByte & 0x7F;
            $offset += $lengthBytes;
        }

        if (($der[$offset++] ?? null) !== "\x02") {
            throw new RuntimeException('Invalid DER signature.');
        }

        $rLength = ord($der[$offset++]);
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;

        if (($der[$offset++] ?? null) !== "\x02") {
            throw new RuntimeException('Invalid DER signature.');
        }

        $sLength = ord($der[$offset++]);
        $s = substr($der, $offset, $sLength);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        $signature = str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);

        if (strlen($signature) !== 64) {
            throw new RuntimeException('Invalid ECDSA signature length.');
        }

        return $signature;
    }

    /**
     * Convert a JOSE fixed-width signature back to DER for OpenSSL verification.
     */
    private function joseToDer(string $signature): string
    {
        if (strlen($signature) !== 64) {
            throw new RedemptionRejectedException('Invalid token signature length.', 422);
        }

        $r = ltrim(substr($signature, 0, 32), "\x00") ?: "\x00";
        $s = ltrim(substr($signature, 32, 32), "\x00") ?: "\x00";

        if ((ord($r[0]) & 0x80) !== 0) {
            $r = "\x00".$r;
        }

        if ((ord($s[0]) & 0x80) !== 0) {
            $s = "\x00".$s;
        }

        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    private function privateKey(): string
    {
        if ($this->privateKey !== null) {
            return $this->privateKey;
        }

        $this->ensureKeys();

        $pem = file_get_contents((string) config('mess.qr.private_key_path'));

        if ($pem === false) {
            throw new RuntimeException('Unable to read the QR private key.');
        }

        return $this->privateKey = $pem;
    }

    private function publicKey(): string
    {
        if ($this->publicKey !== null) {
            return $this->publicKey;
        }

        $this->ensureKeys();

        $pem = file_get_contents((string) config('mess.qr.public_key_path'));

        if ($pem === false) {
            throw new RuntimeException('Unable to read the QR public key.');
        }

        return $this->publicKey = $pem;
    }

    private function ensureKeys(): void
    {
        $privatePath = (string) config('mess.qr.private_key_path');
        $publicPath = (string) config('mess.qr.public_key_path');

        if (is_file($privatePath) && is_file($publicPath)) {
            return;
        }

        $directory = dirname($privatePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create the QR key directory.');
        }

        $options = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ];

        $configFile = $this->opensslConfig();

        if ($configFile !== null) {
            $options['config'] = $configFile;
        }

        $key = openssl_pkey_new($options);

        if ($key === false) {
            throw new RuntimeException('Unable to generate the QR signing key: '.openssl_error_string());
        }

        $privatePem = '';
        openssl_pkey_export($key, $privatePem, null, $configFile !== null ? ['config' => $configFile] : []);
        $details = openssl_pkey_get_details($key);

        if ($privatePem === '' || ! is_array($details) || ! isset($details['key'])) {
            throw new RuntimeException('Unable to export the QR signing key.');
        }

        file_put_contents($privatePath, $privatePem, LOCK_EX);
        file_put_contents($publicPath, $details['key'], LOCK_EX);
        @chmod($privatePath, 0600);
    }

    private function opensslConfig(): ?string
    {
        $configured = config('mess.qr.openssl_config');

        if (is_string($configured) && is_file($configured)) {
            return $configured;
        }

        $environment = getenv('OPENSSL_CONF');

        if (is_string($environment) && is_file($environment)) {
            return $environment;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidate = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
