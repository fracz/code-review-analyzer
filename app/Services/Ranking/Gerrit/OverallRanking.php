<?php

namespace App\Services\Ranking\Gerrit;

use App\Project;
use App\Services\Ranking\RankerInterface;

class OverallRanking implements RankerInterface
{
	//kazda liczba mnoga powinna wygladac tak samo tyko miec na koncu s (dla badge controller ->getUserBadges)
    private $weights = [
        'changes' => [
            'commits_per_user' => [
                'weight' => 1.0,
                'field' => 'count',
				'desc' => 'new change|new changes',
            ],
			'nt_changes' => [
                'weight' => 0.5,
                'field' => 'count',
				'desc' => 'new NT change|new NT changes',
            ],
            'reviews_per_user' => [
                'weight' => 1.0,
                'field' => 'count',
				'desc' => 'review|reviews',
            ],
			 'reviews_per_user_repeat' => [
                'weight' => 0.5,
                'field' => 'minor_count',
				'desc' => 'repeated review|repeated reviews',
            ],
            'patchsets_per_user' => [
                'weight' => 0.1,
                'field' => 'count_without_first_patchset',
				'desc' => 'patchset|patchsets',
            ],
            'commit_without_corrections' => [
                'weight' => 1.0,
                'field' => 'commit_without_corrections',
				'desc' => 'flawless change|flawless changes',
            ],
        ],
        'comments' => [
            'comments_received' => [
                'weight' => -0.1,
                'field' => 'count',
				'desc' => 'received comment|received comments',
            ],
            'comments_given' => [
                'weight' => 0.1,
                'field' => 'count',
				'desc' => 'given comment|given comments',
            ],
        ],
        'statistics' => [
            'average_comment_length' => [
                'weight' => 0.0,
                'field' => 'rank',
				'desc' => 'avarage comment length|avarage comment length',
            ],
            'changes_per_review' => [
                'weight' => 0.0,
                'field' => 'average',
				'desc' => 'changes per review|changes per review',
            ],
        ],
        'topics' => [
            'hot_topics' => [
                'weight' => 0.0,
                'field' => 'count',
				'desc' => 'hot topics|hot topics',
            ],
            'discussions' => [
                'weight' => 0.0,
                'field' => 'count',
				'desc' => 'discussions|discussions',
            ],
        ],
        'pairs' => [
            'review_pairs' => [
                'weight' => 0.0,
                'field' => 'count',
				'desc' => 'pairs|pairs',
            ],
        ],
    ];

    /**
     * Creates ranking for all generated results.
     *
     * @param Project $project Project which needs ranking.
     * @param array $results List of results to process.
     * @return array
     */
    public function createRanking(Project $project, array $results)
    {
        $result = [];

        foreach ($this->weights as $weights) {
            foreach ($weights as $type => $weight) {
				
				if($type == 'reviews_per_user_repeat')
					$type = 'reviews_per_user';
				
                foreach ($results[$type] as $user) {
                    //print_r($type);exit;
                    if (!isset($result[$user['username']])) {
                        //print_r($user);exit;
                        $result[$user['username']] = [
                            'value' => 0.0,
							'achievements' => [],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'avatar' => $user['avatar'],
                        ];
                    }

                    $result[$user['username']]['value'] += $weight['weight'] * $user[$weight['field']];
					
					if($user[$weight['field']] != 0 && $weight['weight'] != 0)  {
					
						$desc = $weight['desc'];
						$desc_exploded = explode("|", $weight['desc']);
						if($user[$weight['field']] == 1){
							$desc = $desc_exploded[0];
						} else {
							$desc = $desc_exploded[1];
						}
						
						$result[$user['username']]['achievements'][$desc] = [];
						$result[$user['username']]['achievements'][$desc]['weight'] = $weight['weight'];
						$result[$user['username']]['achievements'][$desc]['times'] = $user[$weight['field']];
					}
                }
            }
        }

        usort($result, function ($a, $b) {
            $isMore = $b['value'] > $a['value'];
            $isEqual = $b['value'] == $a['value'];
            return $isMore ? 1 : ($isEqual ? 0 : -1);
        });

        foreach ($result as &$item) {
            $item['value'] = round($item['value'], 2);
        }

        return $result;
    }

    public function getLabel()
    {
        return 'Ogólny <button class="btn btn-sm btn-default pull-right overall-rank">Wzór</button>';
    }

    public function getResults(array $results, Project $project)
    {
        return view('review.gerrit.ranking._overall', ['results' => $results, 'ranker' => $this, 'project' => $project]);
    }
}

