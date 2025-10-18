<?php

namespace App\Http\Controllers;

use App\Models\Tenants\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PrinterController extends Controller
{
    public function index()
    {
        return $this->buildResponse()
            ->setData(Printer::all())
            ->setMessage('Data retrieved successfully')
            ->present();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'ip_address' => 'required',
            'port' => 'nullable',
            'driver' => 'required',
        ]);

        Printer::create($request->all());

        return $this->buildResponse()
            ->setMessage('Data saved successfully')
            ->present();
    }

    public function update(Request $request, Printer $printer)
    {
        $request->validate([
            'name' => 'required',
            'ip_address' => 'required',
            'port' => 'nullable',
            'driver' => 'required',
        ]);

        $printer->update($request->all());

        return $this->buildResponse()
            ->setMessage('Data updated successfully')
            ->present();
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();

        return $this->buildResponse()
            ->setMessage('Data deleted successfully')
            ->present();
    }

    public function signing(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'request' => 'required|string',
        ]);

        $payload = $validated['request'];

        // Get key path and optional passphrase from env
        $keyPath = config('setting.printer_private_key'); // full system path recommended
        $passphrase = env('SIGNING_KEY_PASSPHRASE', null);

        if (empty($keyPath) || !Storage::disk('local')->exists($keyPath)) {
            Log::error('Signing key not found or SIGNING_KEY_PATH not set', ['path' => $keyPath]);
            return Response::make('Signing key not found', 404);
        }

        // Read private key contents
        $privateKeyContents = Storage::disk('local')->get($keyPath);
        if ($privateKeyContents === false) {
            Log::error('Failed to read signing key file', ['path' => $keyPath]);
            return Response::make('Failed to read signing key', 400);
        }

        // Get private key resource / handle
        if ($passphrase) {
            $privateKey = openssl_pkey_get_private($privateKeyContents, $passphrase);
        } else {
            $privateKey = openssl_pkey_get_private($privateKeyContents);
        }

        if ($privateKey === false) {
            Log::error('Failed to load private key for signing', ['path' => $keyPath]);
            return Response::make('Failed to load private key', 500);
        }

        $signature = null;

        // Use SHA-512 by default (use OPENSSL_ALGO_SHA1 if you need older compatibility)
        $ok = openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA512);

        // Free key
        openssl_free_key($privateKey);

        if (!$ok || $signature === null) {
            Log::error('OpenSSL signing failed');
            return Response::make('Error signing message', 500);
        }

        // Return base64-encoded signature as plain text
        $b64 = base64_encode($signature);
        return Response::make($b64, 200, ['Content-Type' => 'text/plain']);
    }
}
