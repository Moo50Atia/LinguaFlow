<?php

namespace App\Services;

use App\Models\Moment;
use App\Models\MomentLike;
use App\Models\MomentComment;
use Illuminate\Support\Facades\DB;

class MomentService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function create(int $userId, array $data): Moment
    {
        return DB::transaction(function () use ($userId, $data) {
            $moment = Moment::create([
                'user_id'        => $userId,
                'content'        => $data['content'],
                'category'       => $data['category'],
                'likes_count'    => 0,
                'comments_count' => 0,
            ]);

            if (isset($data['images']) && is_array($data['images'])) {
                $imageUrls = [];
                foreach ($data['images'] as $image) {
                    $imageUrls[] = $this->fileUploadService->store($image, 'moments');
                }
                $moment->update(['images' => $imageUrls]);
            }

            return $moment;
        });
    }

    public function delete(Moment $moment): void
    {
        if (is_array($moment->images)) {
            foreach ($moment->images as $imageUrl) {
                $this->fileUploadService->delete($imageUrl);
            }
        }
        $moment->delete();
    }

    public function toggleLike(int $userId, int $momentId): array
    {
        $like = MomentLike::where('user_id', $userId)->where('moment_id', $momentId)->first();
        
        $moment = Moment::findOrFail($momentId);

        if ($like) {
            $like->delete();
            $moment->decrement('likes_count');
            return ['status' => 'unliked', 'likes_count' => $moment->likes_count];
        } else {
            MomentLike::create(['user_id' => $userId, 'moment_id' => $momentId]);
            $moment->increment('likes_count');
            return ['status' => 'liked', 'likes_count' => $moment->likes_count];
        }
    }

    public function addComment(int $userId, int $momentId, array $data): MomentComment
    {
        return DB::transaction(function () use ($userId, $momentId, $data) {
            $comment = MomentComment::create([
                'moment_id' => $momentId,
                'user_id'   => $userId,
                'content'   => $data['content'],
            ]);

            Moment::where('id', $momentId)->increment('comments_count');

            return $comment;
        });
    }
}
