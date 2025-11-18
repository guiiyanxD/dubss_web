<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Subir y procesar archivo
     */
    public function upload(UploadedFile $file, string $carpeta = 'documentos'): array
    {
        $extension = $file->getClientOriginalExtension();
        $nombreOriginal = $file->getClientOriginalName();
        $hash = hash_file('sha256', $file->getRealPath());

        // Generar nombre único
        $nombreUnico = time() . '_' . uniqid() . '.' . $extension;

        // Guardar archivo
        $ruta = $file->storeAs($carpeta, $nombreUnico, 'public');

        // Comprimir imágenes si es necesario
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $this->compressImage(storage_path('app/public/' . $ruta));
        }

        return [
            'nombre_original' => $nombreOriginal,
            'nombre_guardado' => $nombreUnico,
            'ruta' => $ruta,
            'hash' => $hash,
            'tamano' => $file->getSize(),
            'extension' => $extension,
            'url' => Storage::url($ruta),
        ];
    }

    /**
     * Comprimir imagen
     */
    protected function compressImage(string $path): void
    {
        try {
            $image = Image::make($path);

            // Redimensionar si es muy grande
            if ($image->width() > 1920 || $image->height() > 1920) {
                $image->resize(1920, 1920, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Guardar con calidad 85%
            $image->save($path, 85);
        } catch (\Exception $e) {
            // Si falla, mantener imagen original
            logger()->warning('Error al comprimir imagen: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar archivo
     */
    public function delete(string $ruta): bool
    {
        return Storage::disk('public')->delete($ruta);
    }

    /**
     * Verificar si archivo existe
     */
    public function exists(string $ruta): bool
    {
        return Storage::disk('public')->exists($ruta);
    }

    /**
     * Obtener URL pública
     */
    public function url(string $ruta): string
    {
        return Storage::url($ruta);
    }
}
