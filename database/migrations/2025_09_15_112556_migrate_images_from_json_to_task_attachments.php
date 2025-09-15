<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migracja danych z kolumny images (JSON) do tabeli task_attachments
        $tasks = DB::table('tasks')->whereNotNull('images')->get();
        
        foreach ($tasks as $task) {
            $images = json_decode($task->images, true);
            
            if (is_array($images)) {
                foreach ($images as $image) {
                    // Sprawdź czy plik istnieje fizycznie
                    $filePath = $image['path'] ?? null;
                    if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                        continue; // Pomiń nieistniejące pliki
                    }
                    
                    // Pobierz informacje o pliku
                    $fullPath = Storage::disk('public')->path($filePath);
                    $fileSize = file_exists($fullPath) ? filesize($fullPath) : null;
                    $mimeType = file_exists($fullPath) ? mime_content_type($fullPath) : null;
                    
                    // Ustawienia domyślne
                    $originalName = $image['original_name'] ?? basename($filePath);
                    $uploadedAt = $image['uploaded_at'] ?? $task->created_at;
                    
                    // Znajdź użytkownika - domyślnie lider zadania lub pierwszy admin
                    $uploadedBy = $task->leader_id;
                    if (!$uploadedBy) {
                        $adminUser = DB::table('users')->where('role', 'admin')->first();
                        $uploadedBy = $adminUser ? $adminUser->id : 1; // fallback na ID 1
                    }
                    
                    // Wstaw do nowej tabeli
                    DB::table('task_attachments')->insert([
                        'task_id' => $task->id,
                        'file_path' => $filePath,
                        'original_name' => $originalName,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'uploaded_by' => $uploadedBy,
                        'created_at' => $uploadedAt,
                        'updated_at' => $uploadedAt,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Przywrócenie danych z tabeli task_attachments do kolumny images (JSON)
        $tasks = DB::table('tasks')->get();
        
        foreach ($tasks as $task) {
            $attachments = DB::table('task_attachments')
                ->where('task_id', $task->id)
                ->get();
            
            if ($attachments->count() > 0) {
                $images = [];
                foreach ($attachments as $attachment) {
                    $images[] = [
                        'path' => $attachment->file_path,
                        'original_name' => $attachment->original_name,
                        'uploaded_at' => $attachment->created_at,
                    ];
                }
                
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['images' => json_encode($images)]);
            }
        }
        
        // Wyczyść tabelę załączników
        DB::table('task_attachments')->truncate();
    }
};
