<?php

namespace App\Repositories;

use App\Models\Podcast;
use App\Repositories\Interfaces\PodcastRepositoryInterface;

class PodcastRepository extends BaseRepository implements PodcastRepositoryInterface
{
    public function __construct(Podcast $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
