<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class HotTopics extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Hot topics';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from HotTopics";exit;
        //$this->collectDataForReview($project, $from, $to);
        
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();

        $results = [];

        foreach ($result as $commit) {
            if (!isset($results[$commit->commit_id])) {
                    $results[$commit->commit_id] = [
                            'id' => $commit->_number,
                            'subject' => $commit->subject,
                            'username' => $commit->owner->username,
                            'name' => $commit->owner->name,
                            'email' => $commit->owner->email,
                            'avatar' => (object) ['url' => $commit->owner->avatars->first()->url, 
                                            'height' => $commit->owner->avatars->first()->height],
                            'messages' => [],
                            'count' => 0,
                    ];
            }

            foreach ($commit->revisions as $revision) {
                $files = $revision->comments;
                
                $bare = array_filter($files->all(), function($item){
                    return $item->in_reply_to == null;
                });
                    
                $replies = array_filter($files->all(), function($item){
                    return $item->in_reply_to != null;
                });

                foreach ($bare as $message) {
                    $results[$commit->commit_id]['messages'][$message->comment_id] = [
                        'from' => [
                            'name' => $message->author->name,
                            'username' => $message->author->username,
                            'email' => $message->author->email,
                        ],
                        'file' => $message->filename,
                        'revision' => $revision->revision_id,  
                        'change' => $commit->commit_id,
                        'line' => isset($message->line) ? $message->line : 'false',
                        'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $message->updated),
                        'text' => $message->message,
                        'replies' => [],
                    ];
                    

                    $results[$commit->commit_id]['messages'] = $this->addReplies($results[$commit->commit_id]['messages'], $replies);
                }
            }
        }

        foreach ($results as &$result) {
            $result['count'] = count($result['messages']);
        }

        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        if (empty($results)) {
            return [];
        }

        $average = array_sum(array_map(function($item){ return $item['count']; }, $results))/count($results);

        $results = array_filter($results, function($item) use ($average) {
            return $item['count'] > $average;
        });

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });

        //print_r($results);exit;
        return $results;
    }
    
    private function addReplies($messages, $replies)
    {
        if (empty($messages)) {
            return [];
        }

        $current = array_filter($replies, function($item) use ($messages) {
            return isset($messages[$item->in_reply_to]);
        });

        $deep = array_filter($replies, function($item) use ($messages) {
            return !isset($messages[$item->in_reply_to]);
        });

        foreach ($current as $reply) {
            $messages[$reply->in_reply_to]['replies'][$reply->comment_id] = [
                'from' => [
                    'name' => $reply->author->name,
                    'username' => $reply->author->username,
                ],
                'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $reply->updated),
                'text' => $reply->message,
                'replies' => [],
            ];
        }

        if (!empty($deep)) {
            foreach ($messages as $key => $message) {
                if (!empty($message['replies'])) {
                    $messages[$key]['replies'] = $this->addReplies($message['replies'], $deep);
                }
            }
        }

        return $messages;
    }

    public function analyze2(Project $project, $from, $to)
    {
        $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
        $uri .= ' -is:draft ((status:merged)OR(status:open))';
        $uri .= ' after:'.$from.' before:'.$to;
        $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';
        
        $result = $this->fetch($project, $uri);
        $results = [];

        foreach ($result as $commit) {
            if (!isset($results[$commit->id])) {
                    $results[$commit->id] = [
                            'id' => $commit->_number,
                            'subject' => $commit->subject,
                            'username' => $commit->owner->username,
                            'name' => $commit->owner->name,
                            'avatar' => current($commit->owner->avatars),
                            'messages' => [],
                            'count' => 0,
                    ];
            }

            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                //sprint_r($uri);exit;
                $files = (array)$this->fetch($project, $uri);
                
                foreach ($files as $filename => $file) {
                    //print_r($revision);exit;
                    $bare = array_filter($file, function($item){
                        return !isset($item->in_reply_to);
                    });
                    
                    $replies = array_filter($file, function($item){
                        return isset($item->in_reply_to);
                    });

                    foreach ($bare as $message) {
                        print_r($message);exit;
                        $results[$commit->id]['messages'][$message->id] = [
                            'from' => [
                                'name' => $message->author->name,
                                'username' => $message->author->username,
                            ],
                            'file' => $filename,
                            'revision' => $revision,
                            'change' => $commit->id,
                            'line' => isset($message->line) ? $message->line : false,
                            'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $message->updated),
                            'text' => $message->message,
                            'replies' => [],
                        ];
                    }

                    $results[$commit->id]['messages'] = $this->addReplies_old($results[$commit->id]['messages'], $replies);
                }
            }
        }

        foreach ($results as &$result) {
            $result['count'] = count($result['messages']);
        }

        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        if (empty($results)) {
            return [];
        }

        $average = array_sum(array_map(function($item){ return $item['count']; }, $results))/count($results);

        $results = array_filter($results, function($item) use ($average) {
            return $item['count'] > $average;
        });

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });

        print_r($results);exit;
        return $results;
    }

    private function addReplies_old($messages, $replies)
    {
        if (empty($messages)) {
            return [];
        }

        $current = array_filter($replies, function($item) use ($messages) {
            return isset($messages[$item->in_reply_to]);
        });

        $deep = array_filter($replies, function($item) use ($messages) {
            return !isset($messages[$item->in_reply_to]);
        });

        foreach ($current as $reply) {
            $messages[$reply->in_reply_to]['replies'][$reply->id] = [
                'from' => [
                    'name' => $reply->author->name,
                    'username' => $reply->author->username,
                ],
                'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $reply->updated),
                'text' => $reply->message,
                'replies' => [],
            ];
        }

        if (!empty($deep)) {
            foreach ($messages as $key => $message) {
                if (!empty($message['replies'])) {
                    $messages[$key]['replies'] = $this->addReplies_old($message['replies'], $deep);
                }
            }
        }

        return $messages;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.hot_topics._results', ['result' => $result, 'project' => $project]);
    }
}
