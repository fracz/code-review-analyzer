<?php

namespace App\Services\DataFetching;

use App\Project;
use App\Commit;
use App\Person;
use App\Avatar;
use App\Comment;

trait GerritDataFetchingTrait
{
	private static $cache = [];

	protected function fetch(Project $project, $uri)
	{
		if (isset(self::$cache[$project->getAttribute('name')][$uri])) {
			return self::$cache[$project->getAttribute('name')][$uri];
		}
                
		//print_r(str_replace(' ', '%20', $project->getAttribute('url').$uri));exit;
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => str_replace(' ', '%20', $project->getAttribute('url').$uri),
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERPWD => $project->getAttribute('username').':'.$project->getAttribute('password'),
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
		]);
			
		$result = curl_exec($ch);

		if ($result === false) {
			return [];
		}

		$result = $this->decode($result);

		if ($result === null) {
			return [];
		}

		self::$cache[$project->getAttribute('name')][$uri] = $result;

		return $result;
	}

	protected function collectDataForReview(Project $project, $from, $to)
	{
		$result = $this->fetch($project, $this->buildUriElement($project, $from, $to));
		$results = [];
                
                //temporary
                /*$commit = \App\Comment::all();
                print_r(count($commit));

                foreach($commit as $val){
                    $val->delete();
                }
                $commit = \App\Commit::all();
                foreach($commit as $val){
                    $val->delete();
                }
                print_r(count($commit));*/
                //end temporary
                
               
                //print_r(count($result));exit;
		foreach ($result as $commit_item) {
			$commitId = $this->createOrUpdateCommit($commit_item);
                        
                        foreach ($commit_item->revisions as $revision => $data) {
                            $uri = '/a/changes/'.$commit_item->id.'/revisions/'.$revision.'/comments/';
                            $comments = (array)$this->fetch($project, $uri);

                            foreach ($comments as $comment_item) {
                                foreach ($comment_item as $message) {
                                    $this->createOrUpdateComment($message, $commitId);
                                }
                            }
                        }
		}
                
                echo "AA";exit;
	}
        
        protected function buildUriElement(Project $project, $from, $to){
            $dateUriElement = "";
            
            $any_commit_found = \App\Commit::where('project', $project->getAttribute('name'))->orderBy('created', 'DESC')->first();

            if(!$any_commit_found){
                $dateUriElement .= ' before:'.$to;
            } else {
                $fromExploded = explode(" ", $any_commit_found->created);
                $dateUriElement = ' after:'.$fromExploded[0].' before:'.$to;
            }

            $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
            $uri .= ' -is:draft ((status:merged)OR(status:open))';
            $uri .= $dateUriElement;
            $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';
            
            return $uri;
        }
        
        protected function createPersonIfNotExists($acc_id, $name, $email, $username, $avatars){
            $person = \App\Person::where('_account_id', $acc_id)->first();
            
            if (!$person) {
                $person = new Person;

                $person->_account_id = $acc_id;
                $person->name =  $name;
                $person->email =  $email;
                $person->username =  $username;
                $person->save();

                foreach ($avatars as $avatar_item) {
                    $avatar = new Avatar;
                    $avatar->url = $avatar_item->url;
                    $avatar->height = $avatar_item->height;
                    $avatar->person_id = $person->id;
                    $avatar->save();
                }  
            }
            
            return $person->id;
        }
        
        protected function createOrUpdateCommit($commit_item){
            $commit = \App\Commit::where('commit_id', $commit_item->id)->first();
            
            if (!$commit) {
                $commit = new Commit;
                echo "COMMIT DOESNT EXIST <br/>";
            } else {
                echo "COMMIT EXISTS WITH ID: ". $commit->id . "<br/>";
            }
            
            $commit->commit_id = $commit_item->id;
            $commit->project = $commit_item->project;
            $commit->branch = $commit_item->branch;
            $commit->change_id = $commit_item->change_id;
            $commit->subject = $commit_item->subject;
            $commit->status = $commit_item->status;
            $commit->created = $commit_item->created;
            $commit->updated = $commit_item->updated;
            $commit->submittable = $commit_item->submittable;
            $commit->insertions = $commit_item->insertions;
            $commit->deletions = $commit_item->deletions;
            $commit->_number = $commit_item->_number;

            $commit->owner_id = $this->createPersonIfNotExists($commit_item->owner->_account_id, $commit_item->owner->name,
                    $commit_item->owner->email, $commit_item->owner->username, $commit_item->owner->avatars);

            $commit->save();
            
            return $commit->id;
        }
        
        protected function createOrUpdateComment($message, $commitId){
            $comment = \App\Comment::where('comment_id', $message->id)->first();
            
            if(!$comment){
                $comment = new Comment;
            }
            
            $comment->comment_id = $message->id; 

            if(isset($message->line))
                $comment->line = $message->line;

            if(isset($message->range)){
                $comment->start_line = $message->range->start_line;
                $comment->start_character = $message->range->start_character;
                $comment->end_line = $message->range->end_line;
                $comment->end_character = $message->range->end_character;  
            }

            $comment->updated =  $message->updated;
            $comment->message = $message->message;
            $comment->commit_id = $commitId;

            $comment->author_id = $this->createPersonIfNotExists($message->author->_account_id, $message->author->name,
                $message->author->email, $message->author->username, $message->author->avatars);

            $comment->save();          
        }

	/**
	 * @param string $data Data to decode.
	 * @return mixed Decoded results.
	 */
	protected abstract function decode($data);
}
