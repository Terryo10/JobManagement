<?php

namespace App\Support;

class DocumentFileTypes
{
    const DOCUMENTS = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
    ];

    const IMAGES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
        'image/bmp',
        'image/heic',
    ];

    const DESIGN = [
        'image/svg+xml',
        'application/postscript', // .ai, .eps
        'image/vnd.adobe.photoshop', // .psd
        '.ai',
        '.psd',
        '.indd',
        '.eps',
        '.cdr',
        '.afdesign',
        '.fig',
        '.sketch',
        '.xd',
        '.svgz',
    ];

    const VIDEO = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'video/webm',
        'video/mxf',
        'video/x-ms-wmv',
    ];

    const AUDIO = [
        'audio/mpeg',
        'audio/wav',
        'audio/aac',
        'audio/flac',
        'audio/mp4',
    ];

    const ARCHIVES = [
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',
    ];

    // Size constants in KB
    const SIZE_20MB  = 20480;
    const SIZE_50MB  = 51200;
    const SIZE_100MB = 102400;
    const SIZE_200MB = 204800;
    const SIZE_1GB   = 1048576;

    public static function only(string ...$groups): array
    {
        $map = [
            'documents' => self::DOCUMENTS,
            'images'    => self::IMAGES,
            'design'    => self::DESIGN,
            'video'     => self::VIDEO,
            'audio'     => self::AUDIO,
            'archives'  => self::ARCHIVES,
        ];

        return array_merge(...array_map(fn ($g) => $map[$g] ?? [], $groups));
    }

    public static function all(): array
    {
        return array_merge(
            self::DOCUMENTS,
            self::IMAGES,
            self::DESIGN,
            self::VIDEO,
            self::AUDIO,
            self::ARCHIVES
        );
    }
}
