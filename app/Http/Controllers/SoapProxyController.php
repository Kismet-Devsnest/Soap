<?php

namespace App\Http\Controllers;

use CodeDredd\Soap\Facades\Soap;
use CodeDredd\Soap\SoapFactory;
use App\Soap\CustomSoapClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
//

class SoapProxyController extends Controller
{
    public function wsdl(): JsonResponse
    {
        return response()->json([
            'wsdl' => env('SPG_WSDL', 'https://27.147.153.82:6332/SpgService.asmx?wsdl'),
        ]);
    }

    public function call(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'params' => ['nullable', 'array'],
            'headers' => ['nullable', 'array'],
            'auth' => ['nullable', 'array'],
            'auth.username' => ['sometimes', 'string'],
            'auth.password' => ['sometimes', 'string'],
        ]);

        $wsdl = $this->resolveWsdl($this->normalizeWsdl(env('SPG_WSDL', 'https://27.147.153.82:6332/SpgService.asmx?wsdl')));

        $client = (new CustomSoapClient(app(SoapFactory::class)))
            ->baseWsdl($wsdl)
            ->withGuzzleClientOptions([
                'verify' => filter_var(env('SPG_SSL_VERIFY', false), FILTER_VALIDATE_BOOL),
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

        if (!empty($validated['headers'])) {
            $client->withHeaders($validated['headers']);
        }

        $username = $validated['auth']['username'] ?? env('SPG_USER');
        $password = $validated['auth']['password'] ?? env('SPG_PASSWORD');
        if (!empty($username) || !empty($password)) {
            $client->withSoapHeader('http://tempuri.org/', 'SpgUserCredentials', [
                'userName' => $username ?? '',
                'password' => $password ?? '',
            ]);
        }

        $params = $validated['params'] ?? [];
        $operationRules = config('spg_validation.operations.'.($validated['action'] ?? ''), []);
        if (!empty($operationRules)) {
            $params = validator($params, $operationRules)->validate();
        }

        $response = $client->call($validated['action'], $params);
        dd($response);
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'status' => $response->status(),
            'data' => $response->json(),
        ]);
    }

    public function callOperation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'params' => ['nullable', 'array'],
            'headers' => ['nullable', 'array'],
            'auth' => ['nullable', 'array'],
            'auth.username' => ['sometimes', 'string'],
            'auth.password' => ['sometimes', 'string'],
        ]);

        $wsdl = $this->resolveWsdl($this->normalizeWsdl(env('SPG_WSDL', 'https://27.147.153.82:6332/SpgService.asmx?wsdl')));

        $client = (new CustomSoapClient(app(SoapFactory::class)))
            ->baseWsdl($wsdl)
            ->withGuzzleClientOptions([
                'verify' => filter_var(env('SPG_SSL_VERIFY', false), FILTER_VALIDATE_BOOL),
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

        if (!empty($validated['headers'])) {
            $client->withHeaders($validated['headers']);
        }


        $username = $validated['auth']['username'] ?? env('SPG_USER');
        $password = $validated['auth']['password'] ?? env('SPG_PASSWORD');
        if (!empty($username) || !empty($password)) {
            $client->withSoapHeader('http://tempuri.org/', 'SpgUserCredentials', [
                'userName' => $username ?? '',
                'password' => $password ?? '',
            ]);
        }

        $params = $validated['params'] ?? [];
        $action = (string) $request->route('action');
        $operationRules = config('spg_validation.operations.'.$action, []);
        if (!empty($operationRules)) {
            $params = validator($params, $operationRules)->validate();
        }

        $response = $client->call($action, $params);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'status' => $response->status(),
            'data' => $response->json(),
        ]);
    }

    private function normalizeWsdl(string $wsdl): string
    {
        if ($wsdl && !str_contains($wsdl, '?') && str_ends_with(strtolower($wsdl), '.asmx')) {
            return $wsdl.'?wsdl';
        }
        return $wsdl;
    }

    private function resolveWsdl(string $wsdl): string
    {
        // If it's already a local path, return
        if (!str_starts_with($wsdl, 'http')) {
            return $wsdl;
        }

        $dir = storage_path('app/wsdls');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }
        $filename = $dir.'/'.md5($wsdl).'.wsdl';

        // Try to download with relaxed SSL
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'timeout' => 20,
            ],
        ]);
        $xml = @file_get_contents($wsdl, false, $context);
        if ($xml !== false && str_contains($xml, '<wsdl:definitions')) {
            File::put($filename, $xml);
            return $filename;
        }

        // Fallback to original URL if download failed; client may still handle it
        return $wsdl;
    }
}

