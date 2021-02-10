<?php

namespace PierreMiniggio\YoutubeToSkyrock\Repository;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoToUploadRepository
{
    public function __construct(private DatabaseFetcher $fetcher)
    {}

    public function insertVideoIfNeeded(
        string $skyrockId,
        int $skyrockAccountId,
        int $youtubeVideoId
    ): void
    {
        $postQueryParams = [
            'account_id' => $skyrockAccountId,
            'skyrock_id' => $skyrockId
        ];
        $findPostIdQuery = [
            $this->fetcher
                ->createQuery('skyrock_post')
                ->select('id')
                ->where('account_id = :account_id AND skyrock_id = :skyrock_id')
            ,
            $postQueryParams
        ];
        $queriedIds = $this->fetcher->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->fetcher->exec(
                $this->fetcher
                    ->createQuery('skyrock_post')
                    ->insertInto('account_id, skyrock_id', ':account_id, :skyrock_id')
                ,
                $postQueryParams
            );
            $queriedIds = $this->fetcher->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'skyrock_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->fetcher->query(
            $this->fetcher
                ->createQuery('skyrock_post_youtube_video')
                ->select('id')
                ->where('skyrock_id = :skyrock_id AND youtube_id = :youtube_id')
            ,
            $pivotQueryParams
        );
        
        if (! $queriedPivotIds) {
            $this->fetcher->exec(
                $this->fetcher
                    ->createQuery('skyrock_post_youtube_video')
                    ->insertInto('skyrock_id, youtube_id', ':skyrock_id, :youtube_id')
                ,
                $pivotQueryParams
            );
        }
    }
}
