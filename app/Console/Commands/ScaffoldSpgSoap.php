<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScaffoldSpgSoap extends Command
{
    protected $signature = 'soap:scaffold-spg {wsdl?}';

    protected $description = 'Introspect the SPG WSDL and scaffold fixed API endpoints and validation rules.';

    public function handle(): int
    {
        $wsdl = $this->argument('wsdl') ?: env('SPG_WSDL', 'https://27.147.153.82:6332/SpgService.asmx?wsdl');

        $this->info("Fetching WSDL: {$wsdl}");
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 20,
                ],
            ]);
            $xmlString = @file_get_contents($wsdl, false, $context);
            if ($xmlString === false) {
                $this->error('Failed to fetch WSDL.');
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Error fetching WSDL: '.$e->getMessage());
            return self::FAILURE;
        }

        try {
            $xml = new \SimpleXMLElement($xmlString);
        } catch (\Throwable $e) {
            $this->error('Invalid WSDL XML: '.$e->getMessage());
            return self::FAILURE;
        }

        // Register common WSDL namespaces
        $namespaces = [
            'wsdl' => 'http://schemas.xmlsoap.org/wsdl/',
            's' => 'http://www.w3.org/2001/XMLSchema',
            'tns' => 'http://tempuri.org/',
        ];
        foreach ($namespaces as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }

        $operations = [];
        $opNodes = $xml->xpath('//wsdl:portType/wsdl:operation');
        foreach ($opNodes as $node) {
            $name = (string)($node['name'] ?? '');
            if ($name !== '') {
                $operations[] = $name;
            }
        }

        $operations = array_values(array_unique($operations));
        if (empty($operations)) {
            $this->warn('No operations found in WSDL.');
        } else {
            $this->info('Found operations: '.implode(', ', $operations));
        }

        $routesPath = base_path('routes/spg.php');
        $routesContent = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse App\\Http\\Controllers\\SoapProxyController;\nuse App\\Http\\Middleware\\VerifyCsrfToken;\n\nRoute::prefix('api/spg')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {\n";
        foreach ($operations as $op) {
            $uri = addslashes($op);
            $routesContent .= "    Route::post('{$uri}', [SoapProxyController::class, 'callAction'])->defaults('action', '{$uri}');\n";
        }
        $routesContent .= "});\n";

        File::put($routesPath, $routesContent);
        $this->info("Generated routes: {$routesPath}");

        // Build validation rules per operation based on schema elements
        $validation = [];
        foreach ($operations as $op) {
            $paramElements = $xml->xpath("//s:element[@name='{$op}']/s:complexType/s:sequence/s:element");
            if (!is_array($paramElements)) {
                continue;
            }
            $rules = [];
            foreach ($paramElements as $param) {
                $paramName = (string) ($param['name'] ?? '');
                if ($paramName === '') {
                    continue;
                }
                $type = (string) ($param['type'] ?? 's:string');
                $minOccurs = (string) ($param['minOccurs'] ?? '1');
                $required = ($minOccurs === '1') ? 'required' : 'nullable';
                $mapped = $this->mapXsdTypeToLaravelRule($type);
                $rules[$paramName] = trim($required.'|'.$mapped, '|');
            }
            if (!empty($rules)) {
                $validation[$op] = $rules;
            }
        }

        $configContent = "<?php\n\nreturn ".var_export(['operations' => $validation], true).";\n";
        $configPath = base_path('config/spg_validation.php');
        File::put($configPath, $configContent);
        $this->info("Generated validation config: {$configPath}");

        $this->line('Ensure routes/spg.php is included from routes/web.php (already added).');

        return self::SUCCESS;
    }

    protected function mapXsdTypeToLaravelRule(string $xsdType): string
    {
        // strip namespace prefix like s:string
        $base = str_contains($xsdType, ':') ? explode(':', $xsdType, 2)[1] : $xsdType;
        return match ($base) {
            'string' => 'string',
            'boolean' => 'boolean',
            'int', 'integer' => 'integer',
            'decimal', 'float', 'double' => 'numeric',
            default => 'string',
        };
    }
}


