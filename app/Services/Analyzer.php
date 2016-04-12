<?php

namespace App\Services;

use App\Project;
use App\Services\Analyzer\AnalyzerInterface as CodeAnalyzer;
use App\Services\Ranking\RankerInterface;

class Analyzer implements AnalyzerInterface
{
	private $analyzers = [];
	private $ranking = [];

	function __construct()
	{
		$this->analyzers = [
			'gerrit' => [
				'changes' => [
					'commits_per_user' => new Analyzer\Gerrit\CommitsPerUser(),
					'all_commits_per_user' => new Analyzer\Gerrit\AllCommitsPerUser(),
					'reviews_per_user' => new Analyzer\Gerrit\ReviewsPerUser(),
					'reviews_per_commit' => new Analyzer\Gerrit\ReviewsPerCommit(),
					'patchsets_per_user' => new Analyzer\Gerrit\PatchsetsPerUser(),
					'commit_without_corrections' => new Analyzer\Gerrit\CommitsWithoutCorrections(),
					'nt_changes' => new Analyzer\Gerrit\NoTaskChanges(),
				],
				'comments' => [
					'comments_received' => new Analyzer\Gerrit\CommentsReceived(),
					'comments_given' => new Analyzer\Gerrit\CommentsGiven(),
				],
				'statistics' => [
					'average_comment_length' => new Analyzer\Gerrit\AverageCommentLength(),
					'average_comment_length_chart' => new Analyzer\Gerrit\AverageCommentLengthChart(),
					'changes_per_review' => new Analyzer\Gerrit\ChangesPerReview(),
					'changes_per_review_chart' => new Analyzer\Gerrit\ChangesPerReviewChart(),
				],
				'topics' => [
					'hot_topics' => new Analyzer\Gerrit\HotTopics(),
					'discussions' => new Analyzer\Gerrit\Discussions(),
				],
				'pairs' => [
					'review_pairs' => new Analyzer\Gerrit\ReviewPairs(),
					'review_pairs_graph' => new Analyzer\Gerrit\ReviewPairsGraph(),
				],
//				'badges' => [
//					'project_badges' => new Analyzer\Gerrit\ProjectBadges(),
//				],
			],
			'stash' => [
				'changes' => [
					'commits_per_user' => new Analyzer\Stash\CommitsPerUser(),
					'reviews_per_user' => new Analyzer\Stash\ReviewsPerUser(),
					'nt_changes' => new Analyzer\Stash\NoTaskChanges(),
				],
				'comments' => [
					'comments_received' => new Analyzer\Stash\CommentsReceived(),
					'comments_given' => new Analyzer\Stash\CommentsGiven(),
				],
				'statistics' => [
					'average_comment_length' => new Analyzer\Stash\AverageCommentLength(),
					'average_comment_length_chart' => new Analyzer\Stash\AverageCommentLengthChart(),
					'changes_per_review' => new Analyzer\Stash\ChangesPerReview(),
					'changes_per_review_chart' => new Analyzer\Stash\ChangesPerReviewChart(),
				],
				'topics' => [
					'hot_topics' => new Analyzer\Stash\HotTopics(),
					'discussions' => new Analyzer\Stash\Discussions(),
				],
				'pairs' => [
					'review_pairs' => new Analyzer\Stash\ReviewPairs(),
					'review_pairs_graph' => new Analyzer\Stash\ReviewPairsGraph(),
				],
			]
		];

		$this->ranking = [
			'gerrit' => [
				'overall' => new Ranking\Gerrit\OverallRanking(),
				'overall_chart' => new Ranking\Gerrit\OverallRankingChart(),
			],
			'stash' => [
				'overall' => new Ranking\Stash\OverallRanking(),
				'overall_chart' => new Ranking\Stash\OverallRankingChart(),
			],
		];
	}
        
        public function reBuildAnalyzerForApi(){
            $this->analyzers = [
			'gerrit' => [
				'changes' => [
					'commits_per_user' => new Analyzer\Gerrit\CommitsPerUser(),
					'reviews_per_user' => new Analyzer\Gerrit\ReviewsPerUser(),
                                        'patchsets_per_user' => new Analyzer\Gerrit\PatchsetsPerUser(),
                                        'reviews_per_commit' => new Analyzer\Gerrit\ReviewsPerCommit(),
                                        'commit_without_corrections' => new Analyzer\Gerrit\CommitsWithoutCorrections(),
					'nt_changes' => new Analyzer\Gerrit\NoTaskChanges(),
				],
				'comments' => [
					'comments_received' => new Analyzer\Gerrit\CommentsReceived(),
					'comments_given' => new Analyzer\Gerrit\CommentsGiven(),
				],
				'statistics' => [
					'average_comment_length' => new Analyzer\Gerrit\AverageCommentLength(),
					'average_comment_length_chart' => new Analyzer\Gerrit\AverageCommentLengthChart(),
					'changes_per_review' => new Analyzer\Gerrit\ChangesPerReview(),
					'changes_per_review_chart' => new Analyzer\Gerrit\ChangesPerReviewChart(),
				],
				'topics' => [
					'hot_topics' => new Analyzer\Gerrit\HotTopics(),
					'discussions' => new Analyzer\Gerrit\Discussions(),
				],
				'pairs' => [
					'review_pairs' => new Analyzer\Gerrit\ReviewPairs(),
					'review_pairs_graph' => new Analyzer\Gerrit\ReviewPairsGraph(),
				],
//				'badges' => [
//					'project_badges' => new Analyzer\Gerrit\ProjectBadges(),
//				],
			],
			'stash' => [
				'changes' => [
					'commits_per_user' => new Analyzer\Stash\CommitsPerUser(),
					'reviews_per_user' => new Analyzer\Stash\ReviewsPerUser(),
					'nt_changes' => new Analyzer\Stash\NoTaskChanges(),
				],
				'comments' => [
					'comments_received' => new Analyzer\Stash\CommentsReceived(),
					'comments_given' => new Analyzer\Stash\CommentsGiven(),
				],
				'statistics' => [
					'average_comment_length' => new Analyzer\Stash\AverageCommentLength(),
					'average_comment_length_chart' => new Analyzer\Stash\AverageCommentLengthChart(),
					'changes_per_review' => new Analyzer\Stash\ChangesPerReview(),
					'changes_per_review_chart' => new Analyzer\Stash\ChangesPerReviewChart(),
				],
				'topics' => [
					'hot_topics' => new Analyzer\Stash\HotTopics(),
					'discussions' => new Analyzer\Stash\Discussions(),
				],
				'pairs' => [
					'review_pairs' => new Analyzer\Stash\ReviewPairs(),
					'review_pairs_graph' => new Analyzer\Stash\ReviewPairsGraph(),
				],
			]
		];

		$this->ranking = [
			'gerrit' => [
				'overall' => new Ranking\Gerrit\OverallRanking(),
				'overall_chart' => new Ranking\Gerrit\OverallRankingChart(),
			],
			'stash' => [
				'overall' => new Ranking\Stash\OverallRanking(),
				'overall_chart' => new Ranking\Stash\OverallRankingChart(),
			],
		];
        }

	public function getList()
	{
		return $this->analyzers;
	}

	public function getRankers()
	{
		return $this->ranking;
	}

	/**
	 * @param Project $project
	 * @param $from
	 * @param $to
	 * @return array
	 */
	public function analyze($project, $from, $to)
	{
		$results = [];

		foreach ($this->analyzers[$project->getType()] as $analyzers) {
			/** @var CodeAnalyzer[] $analyzers */
			foreach ($analyzers as $type => $analyzer) {
				$results[$type] = $analyzer->analyze($project, $from, $to);
 			}
		}

		foreach ($this->ranking[$project->getType()] as $type => $ranker) {
			/** @var RankerInterface $ranker */
			$results['ranking_'.$type] = $ranker->createRanking($project, $results);
		}

		return $results;
	}
}
