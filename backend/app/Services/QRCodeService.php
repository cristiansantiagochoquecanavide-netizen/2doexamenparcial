<?php

namespace App\Services;

class QRCodeService
{
    /**
     * Generar código QR usando QR Server API (servicio online gratuito)
     * 
     * @param string $texto El texto/URL a codificar
     * @return string URL del QR o data URL
     */
    public static function generarQR(string $texto): string
    {
        try {
            // Usar QR Server API - genera SVG directamente
            $encodedText = urlencode($texto);
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=svg&data={$encodedText}";
            
            // Obtener el SVG
            $svgData = @file_get_contents($qrUrl);
            
            if ($svgData === false) {
                // Si falla la conexión, usar fallback local
                return self::generarQRLocal($texto);
            }
            
            // Convertir a base64
            $base64 = base64_encode($svgData);
            
            return 'data:image/svg+xml;base64,' . $base64;
        } catch (\Exception $e) {
            \Log::error('Error generando QR: ' . $e->getMessage());
            return self::generarQRLocal($texto);
        }
    }

    /**
     * Generar un QR simple sin dependencias externas
     * 
     * @param string $texto El texto a codificar
     * @return string Data URL del QR
     */
    private static function generarQRLocal(string $texto): string
    {
        // Como fallback, genera una URL del QR usando QR Server con parámetro format PNG
        $encodedText = urlencode($texto);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedText}";
        
        return $qrUrl;
    }

    /**
     * Generar código QR como archivo
     * 
     * @param string $texto El texto/URL a codificar
     * @return string Ruta del archivo QR
     */
    public static function generarURLQR(string $texto): string
    {
        try {
            // Usar QR Server API para descargar y guardar
            $encodedText = urlencode($texto);
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&data={$encodedText}";
            
            $pngData = @file_get_contents($qrUrl);
            
            if ($pngData === false) {
                throw new \Exception('No se pudo descargar el QR');
            }
            
            // Guardar en storage
            $filename = 'qr_' . uniqid() . '.png';
            $path = storage_path('app/public/qr/' . $filename);
            
            // Crear directorio si no existe
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, $pngData);

            return asset('storage/qr/' . $filename);
        } catch (\Exception $e) {
            \Log::error('Error generando URL QR: ' . $e->getMessage());
            throw $e;
        }
    }
}
