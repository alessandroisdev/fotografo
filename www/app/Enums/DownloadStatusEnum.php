<?php

namespace App\Enums;

enum DownloadStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case READY = 'ready';
    case ARCHIVED_IN_CLOUD = 'archived_in_cloud';
}
