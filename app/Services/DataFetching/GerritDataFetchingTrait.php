<?php

namespace App\Services\DataFetching;

use App\Project;
use App\Commit;
use App\Person;
use App\Avatar;
use App\Comment;
use App\Revision;
use App\CodeReview;
use Cache;
use DateTime;
use DateTimeZone;

trait GerritDataFetchingTrait
{
	private static $cache = [];

	protected function fetch(Project $project, $uri)
	{
        if (isset(self::$cache[$project->getAttribute('name')][$uri])) {
            return self::$cache[$project->getAttribute('name')][$uri];
        }

		$ch = curl_init();

		//CURLAUTH_BASIC  -> for review.gerrithub
		//print_r(str_replace(' ', '%20', $project->getAttribute('url').$uri));exit;
		//print_r( $project->getAttribute('username').':'.$project->getAttribute('password'));exit;

        echo "Fetching URL: ", str_replace(' ', '%20', $project->getAttribute('url').$uri;

		curl_setopt_array($ch, [
			CURLOPT_URL => str_replace(' ', '%20', $project->getAttribute('url').$uri),
			CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
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

		//print_r(curl_getinfo($ch));
		if ($result === null) {
                    return [];
		}

		self::$cache[$project->getAttribute('name')][$uri] = $result;

                 //print_r($uri);exit;

		return $result;
	}

	protected function collectDataForReview(Project $project, $from, $to)
	{
            //temporary
            /*$commit = \App\Comment::all();
            foreach($commit as $val){
                $val->delete();
            }

            $commit = \App\Commit::all();
            foreach($commit as $val){
                $val->delete();
            }

			$commit = \App\Verified::all();
            foreach($commit as $val){
                $val->delete();
            }

			$commit = \App\Revision::all();
            foreach($commit as $val){
                $val->delete();
            }

			$commit = \App\CodeReview::all();
            foreach($commit as $val){
                $val->delete();
            }

			$commit = \App\Avatar::all();
            foreach($commit as $val){
                $val->delete();
            }

			$commit = \App\Person::all();
            foreach($commit as $val){
                $val->delete();
            }*/

            //end temporary

			//Cache::put('emails-to-update', [], 8);
			//Cache::put('emails-to-update-from-badges', [], 8);
			echo "1 ";
            $result = $this->fetch($project, $this->buildUriElement($project, $from, $to));
            $results = [];
            //print_r($result);
			//echo "<br/><br/>aaaaaaaaaa";
echo "2 ";
            foreach ($result as $commit_item) {
			//print_r($commit_item);
			echo "3 ";
                    $commitId = $this->createOrUpdateCommit($commit_item);
					echo "4 ";
					$uri = '/a/changes/'.$commit_item->id.'/detail/';
					$detail_data = (array)$this->fetch($project, $uri);
					echo "5 ";
					$this->createCodeReviewsAndVerifiedForCommit($detail_data, $commitId);
echo "6 ";
                    foreach ($commit_item->revisions as $revision => $data) {echo "7 ";
						$marked = $this->markRebasedCodeNumbers($detail_data);
                        $revisionId = $this->createOrUpdateRevision($data, $revision, $commitId, $marked);
                        echo "8 ";
                        $uri = '/a/changes/'.$commit_item->id.'/revisions/'.$revision.'/comments/';
                        $comments = (array)$this->fetch($project, $uri);echo "9 ";
						//print_r($uri);exit;
                        foreach ($comments as $filename => $comment_item) {
                            foreach ($comment_item as $message) {echo "10 ";
                                $this->createOrUpdateComment($message, $revisionId, $filename);
                            }
                        }
                    }
            }
			echo "Emails to update from GerritDataFetchingTrait: ";
			print_r(Cache::get('emails-to-update'));

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
            $uri .= ' -is:draft ((status:merged)OR(status:open)OR(status:ABANDONED))';
            $uri .= $dateUriElement;
            $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=DETAILED_LABELS';

            //print_r($uri);exit;

            return $uri;
        }


		protected function markRebasedCodeNumbers($data){

			$messages = $data['messages'];
			$marked = [];

			foreach($messages as $msg){

				$rebased = strpos($msg->message, 'rebased');
				if ($rebased !== false) {
					array_push($marked, $msg->_revision_number);
				}
			}

			return $marked;
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

				$this->addEmailToCache($email);

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

		protected function addEmailToCache($email){
			$arr = Cache::get('emails-to-update');

			$inArray = false;
			foreach($arr as $item){
				if($item == $email)
					$inArray = true;
			}

			if(!$inArray)
				array_push($arr, $email);

			Cache::forever('emails-to-update', $arr);
		}

		protected function changeDateToWarsawTimeZone($date){
			//print_r($commit_item->created);echo "<br/>";
			$dateexploded = explode(".", $date);

			$date = new DateTime($dateexploded[0].' +00');
			$date->setTimezone(new DateTimeZone('Europe/Warsaw')); // +02

			return $date->format('Y-m-d H:i:s'); // 2012-07-15 05:00:00
		}

        protected function createOrUpdateCommit($commit_item){
            $commit = \App\Commit::where('commit_id', $commit_item->id)->first();

            if (!$commit) {
                $commit = new Commit;

				$this->addEmailToCache($commit_item->owner->email);
                echo "COMMIT DOESNT EXIST <br/>".$commit_item->subject;
            } else {
                //echo "COMMIT EXISTS WITH ID: ". $commit->id . "<br/>";
            }

            $commit->commit_id = $commit_item->id;
            $commit->project = $commit_item->project;
            $commit->branch = $commit_item->branch;
            $commit->change_id = $commit_item->change_id;
            $commit->subject = $commit_item->subject;
            $commit->status = $commit_item->status;
            $commit->created = $this->changeDateToWarsawTimeZone($commit_item->created);
            $commit->updated = $this->changeDateToWarsawTimeZone($commit_item->updated);
            $commit->submittable = $commit_item->submittable;
            $commit->insertions = $commit_item->insertions;
            $commit->deletions = $commit_item->deletions;
            $commit->_number = $commit_item->_number;

            $commit->owner_id = $this->createPersonIfNotExists($commit_item->owner->_account_id, $commit_item->owner->name,
                    $commit_item->owner->email, $commit_item->owner->username, $commit_item->owner->avatars);


            //print_r($commit);echo "<br/><br/><br/>";

            $commit->save();


            /*if(isset($commit_item->labels) && isset($commit_item->labels->{'Code-Review'}))
            {
                if(isset($commit_item->labels->{'Code-Review'}->{'all'}))
                {
                    $codeReview = $commit_item->labels->{'Code-Review'}->{'all'};
                    foreach ($codeReview as $reviewer) {
                        if ($reviewer instanceof \stdClass) {
                            $commit->approved_by_id = $this->createCodeReviewIfNotExists($reviewer, $commit->id);
                        }
                    }
                }

                if(isset($commit_item->labels->{'Verified'}->{'all'}))
                {
                    $verified = $commit_item->labels->{'Verified'}->{'all'};
                        foreach ($verified as $ver) {
                            if ($ver instanceof \stdClass) {
                                $this->createVerifiedIfNotExists($ver, $commit->id);
                        }
                    }
                }
            }*/

            //echo $commit->id;
            return $commit->id;
        }

		protected function createCodeReviewsAndVerifiedForCommit($data, $commitId){
			$messages = $data['messages'];
			echo "5a ";
			foreach($messages as $msg){
echo "5b ";
					//print_r($msg->message);echo "<br/>";
				$codeRevPos = strpos($msg->message, 'Code-Review');
				if ($codeRevPos !== false) {
					/*print_r($msg->message);echo "<br/>";echo $codeRevPos;
					echo "<Br/>";
					echo $msg->message[$codeRevPos+11];
					exit;*/

					$value = 0;

					if(isset($msg->message[$codeRevPos+11]) && ($msg->message[$codeRevPos+11] == "-" || $msg->message[$codeRevPos+11] == "+"))
					{
						$value = $msg->message[$codeRevPos+11].$msg->message[$codeRevPos+12];
					}
					//print_r($msg);exit;
					$reviewer['value'] = $value;
					$reviewer['_account_id'] = $msg->author->_account_id;
					$reviewer['name'] = $msg->author->name;
					$reviewer['email'] = $msg->author->email;
					$reviewer['username'] = $msg->author->username;
					$reviewer['avatars'] = $msg->author->avatars;
					$reviewer['_revision_number'] = $msg->_revision_number;
					$reviewer['date'] = $this->changeDateToWarsawTimeZone($msg->date);
					//echo $data['id'] . " " . $msg->message. " .... ".$reviewer['_revision_number']." <br/>";
					echo "5c ";
					$this->createCodeReviewIfNotExists($reviewer, $commitId);
					echo "5d ";
				}
				echo "5e";
				$verifPos = strpos($msg->message, 'Verified');
				echo "5f ";



				if ($verifPos !== false) {
					/*print_r($msg->message);echo "<br/>";echo $verifPos;
					echo "<Br/>";
					echo $msg->message[$verifPos+8];
					exit;*/
					echo "5g ";
					$value = 0;

					if(isset($msg->message[$verifPos+8]) && ($msg->message[$verifPos+8] == "-" || $msg->message[$verifPos+8] == "+"))
					{
						$value = $msg->message[$verifPos+8].$msg->message[$verifPos+9];
					}
					echo "5h ";
					if(isset($msg->author->email)){
						$ver['value'] = $value;
						$ver['_account_id'] = $msg->author->_account_id;
						$ver['name'] = $msg->author->name;
						$ver['email'] = $msg->author->email;
						$ver['username'] = $msg->author->username;
						$ver['avatars'] = $msg->author->avatars;
						$ver['_revision_number'] = $msg->_revision_number;
						$ver['date'] = $this->changeDateToWarsawTimeZone($msg->date);
						echo "5i ";
						$this->createVerifiedIfNotExists($ver, $commitId);
						echo "5j ";
					}
					echo "5k ";
				}
			}
		}


        protected function createVerifiedIfNotExists($ver, $commitId){


            if(isset($ver['value']) && isset($ver['date']))
            {
                $val = $ver['value'];
                $date = $ver['date'];
				$rev = $ver['_revision_number'];

                $verifierId = $this->createPersonIfNotExists($ver['_account_id'], $ver['name'],
                        $ver['email'], $ver['username'], $ver['avatars']);

                $verified = \App\Verified::where('commit_id', $commitId)->where('verifier_id', $verifierId)->where('verified_date', $date)->first();

                if(!$verified  && isset($val)){
                    $verified = new \App\Verified;
                    $verified->commit_id = $commitId;
                    $verified->verifier_id = $verifierId;
                    $verified->verified_value = $val;
                    $verified->verified_date = $date;
					$verified->_revision_number = $rev;

                    $verified->save();
					//echo "CREATING VER";
					$this->addEmailToCache($ver['email']);
                }
            }
        }

        protected function createCodeReviewIfNotExists($reviewer, $commitId){
            //print_r($reviewer->date);exit;

            $reviewerId = $this->createPersonIfNotExists($reviewer['_account_id'], $reviewer['name'],
                    $reviewer['email'], $reviewer['username'], $reviewer['avatars']);

            $codeReview = \App\CodeReview::where('commit_id', $commitId)->where('reviewer_id', $reviewerId)->where('_revision_number', $reviewer['_revision_number'])->where('review_value', $reviewer['value'])->where('review_date', $reviewer['date'])->first();
			//print_r($codeReview);echo "<br/><Br/>";

            if(!$codeReview && isset($reviewer['value'])){
                $codeReview = new CodeReview;
                $codeReview->commit_id = $commitId;
                $codeReview->reviewer_id = $reviewerId;
                $codeReview->review_value = $reviewer['value'];
				$codeReview->_revision_number = $reviewer['_revision_number'];
				$codeReview->review_date = $reviewer['date'];

                $codeReview->save();

				$this->addEmailToCache($reviewer['email']);
            }
        }

        protected function createOrUpdateComment($message, $revisionId, $filename){
            $comment = \App\Comment::where('comment_id', $message->id)->first();

            if(!$comment){
                $comment = new Comment;
				$this->addEmailToCache($message->author->email);
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

            if(isset($message->in_reply_to)){
                $comment->in_reply_to =  $message->in_reply_to;
            }

            $comment->filename = $filename;
            $comment->updated =  $this->changeDateToWarsawTimeZone($message->updated);
            $comment->message = $message->message;
            $comment->revision_id = $revisionId;

            $comment->author_id = $this->createPersonIfNotExists($message->author->_account_id, $message->author->name,
                $message->author->email, $message->author->username, $message->author->avatars);

            //print_r($comment);echo "<Br/><Br/>";
            $comment->save();
        }

        protected function createOrUpdateRevision($revisionData, $revisionId, $commit_id, $marked){
            $revision = \App\Revision::where('revision_id', $revisionId)->first();
            if(!$revision){
                $revision = new Revision;
				$this->addEmailToCache($revisionData->uploader->email);
            }

            $revision->revision_id = $revisionId;
            $revision->commit_id = $commit_id;

            $revision->created =  $this->changeDateToWarsawTimeZone($revisionData->created);
            $revision->_number = $revisionData->_number;
            $revision->ref = $revisionData->ref;

            $revision->uploader_id = $this->createPersonIfNotExists($revisionData->uploader->_account_id, $revisionData->uploader->name,
                $revisionData->uploader->email, $revisionData->uploader->username, $revisionData->uploader->avatars);

			if(in_array($revision->_number, $marked))
				$revision->rebased = 1;
			else
				$revision->rebased = 0;

            $revision->save();

            return $revision->id;
        }

	/**
	 * @param string $data Data to decode.
	 * @return mixed Decoded results.
	 */
	protected abstract function decode($data);
}
