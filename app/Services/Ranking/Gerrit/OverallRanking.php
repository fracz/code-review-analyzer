<?php

namespace App\Services\Ranking\Gerrit;

use App\Project;
use App\Services\Ranking\RankerInterface;

class OverallRanking implements RankerInterface
{
    private $weights = [
        'changes' => [
            'commits_per_user' => [
                'weight' => 1.0,
                'field' => 'count',
            ],
            'reviews_per_user' => [
                'weight' => 2.0,
                'field' => 'count',
            ],
            'patchsets_per_user' => [
                'weight' => 0.1,
                'field' => 'count_without_first_patchset',
            ],
            'commit_without_corrections' => [
                'weight' => 1.0,
                'field' => 'commit_without_corrections',
            ],
            'nt_changes' => [
                'weight' => -0.5,
                'field' => 'count',
            ],
        ],
        'comments' => [
            'comments_received' => [
                'weight' => -0.1,
                'field' => 'count',
            ],
            'comments_given' => [
                'weight' => 0.1,
                'field' => 'count',
            ],
            'comments_given' => [
                'weight' => 0.00,
                'field' => 'rank',
            ],
        ],
        'statistics' => [
            'average_comment_length' => [
                'weight' => 0.0,
                'field' => 'rank',
            ],
            'changes_per_review' => [
                'weight' => 0.0,
                'field' => 'average',
            ],
        ],
        'topics' => [
            'hot_topics' => [
                'weight' => 0.0,
                'field' => 'count',
            ],
            'discussions' => [
                'weight' => 0.0,
                'field' => 'count',
            ],
        ],
        'pairs' => [
            'review_pairs' => [
                'weight' => 0.0,
                'field' => 'count',
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
                foreach ($results[$type] as $user) {
                    //print_r($type);exit;
                    if (!isset($result[$user['username']])) {
                        //print_r($user);exit;
                        $result[$user['username']] = [
                            'value' => 0.0,
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'avatar' => $user['avatar'],
                        ];
                    }

                    $result[$user['username']]['value'] += $weight['weight'] * $user[$weight['field']];
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

