<?php

namespace PierreMiniggio\YoutubeToSkyrock\Repository;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseFetcher $fetcher)
    {}

    public function findBySkyrockAndYoutubeChannelIds(int $skyrockAccountId, int $youtubeChannelId): array
    {
        $postedSkyrockPostIds = $this->fetcher->query(
            $this->fetcher
                ->createQuery('skyrock_post_youtube_video as qpyv')
                ->leftJoin('skyrock_post as q', 'q.id = qpyv.skyrock_id')
                ->select('q.id')
                ->where('q.account_id = :account_id')
            ,
            ['account_id' => $skyrockAccountId]
        );
        $postedSkyrockPostIds = array_map(fn ($entry) => (int) $entry['id'], $postedSkyrockPostIds);

        $query = $this->fetcher
            ->createQuery('youtube_video as y')
            ->select('y.id, y.title, y.url')
            ->where('y.channel_id = :channel_id' . (
                $postedSkyrockPostIds ? ' AND qpyv.id IS NULL' : ''
            ))
            ->limit(1)
        ;

        if ($postedSkyrockPostIds) {
            $query->leftJoin(
                'skyrock_post_youtube_video as qpyv',
                'y.id = qpyv.youtube_id AND qpyv.skyrock_id IN (' . implode(', ', $postedSkyrockPostIds) . ')'
            );
        }
        $postsToPost = $this->fetcher->query($query, ['channel_id' => $youtubeChannelId]);
        
        return $postsToPost;
    }
}
