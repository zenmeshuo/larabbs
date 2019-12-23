<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    public function created(Reply $reply)
    {
        $topic = $reply->topic;
        $topic->reply_count = $topic->replies->count();
        $topic->save();

        // 通知话题作者有新的评论
        $topic->user->topicNotify(new TopicReplied($reply));
    }
}
