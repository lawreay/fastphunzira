<?php

return [
    'storage_path' => getenv('MEDIA_STORAGE_PATH') ?: dirname(__DIR__) . '/storage/learning-media',
    'max_upload_bytes' => (int) (getenv('MEDIA_MAX_UPLOAD_BYTES') ?: 10485760),
    'video_mimes' => ['video/mp4', 'video/webm', 'video/ogg'],
    'material_mimes' => [
        'application/pdf', 'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4',
        'video/mp4', 'video/webm', 'text/html', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain',
    ],
];
