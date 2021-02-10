<?php

namespace PierreMiniggio\YoutubeToSkyrock\Repository;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class LinkedChannelRepository
{
    public function __construct(private DatabaseFetcher $fetcher)
    {}

    public function findAll(): array
    {
        return $this->fetcher->query(
            $this->fetcher
                ->createQuery('skyrock_account_youtube_channel as qayc')
                ->leftJoin(
                    'skyrock_account as q',
                    'q.id = qayc.skyrock_id'
                )
                ->select('
                    qayc.youtube_id as y_id,
                    q.id as s_id,
                    q.api_url,
                    q.api_token
                ')
        );
    }
}
