<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Project;
use App\Services\Analyzer\Gerrit\Badges\AbstractBadge;
use App\Services\AnalyzerInterface;

class ReviewController extends Controller
{
    private $analyzerService;

    function __construct(AnalyzerInterface $analyzerService)
    {
        $this->analyzerService = $analyzerService;
        //$this->middleware('auth');
    }

    public function index()
    {
        $projects = array_map(function ($item) {
            return $item->label;
        }, Project::all()->getDictionary());

        return view('review.index', [
            'projects' => $projects,
            'from' => date('d-m-Y'),
            'to' => date('d-m-Y'),
        ]);
    }
	
	public function getAllProjects()
	{
		$projects = Project::all();
		$results = [];
		
		foreach($projects as $project){
			array_push($results, $project['name']);
		}	
		
		return $results;
	}

    public function generate(Requests\AnalyzeRequest $request, $id = null)
    {
        $id = $id ?: $request->get('project');
        /** @var Project $project */
        $project = Project::findOrFail($id);

        $from = date('Y-m-d', strtotime($request->get('from')));
        $to = date('Y-m-d', strtotime('+1 day', strtotime($request->get('to'))));

        $results = $this->analyzerService->analyze($project, $from, $to);

        \Session::set('results.' . $project->getAttribute('id'), [
            'results' => $results,
            'from' => $request->get('from'),
            'to' => $request->get('to'),
        ]);
        flash()->success('Dane projektu zostały poprawnie przetworzone.');

        return redirect()->route('review.results', $project->getAttribute('id'));
    }

    public function generateApi($name, $from, $to)
    {
        //proper format 2015-01-16
        //echo str_replace('&2F;', '/', $name);exit;
		session_write_close();
        $project = Project::where('name', str_replace('&2F;', '/', $name))->firstOrFail();

        $results = $this->analyzerService->analyze($project, $from, $to);

        return $results;
    }

    public function generateApiForLastPeriod($name){
        session_write_close();
        $project = Project::where('name', str_replace('&2F;', '/', $name))->firstOrFail();
        if (!$project)
            return null;

        $to = date('Y-m-d', strtotime(' +1 day'));
        $noOfDays = $project->badges_period;
        $from = date('Y-m-d', strtotime(' -'.$noOfDays.' day'));
        return $this->generateApi($name, $from, $to);
    }

    public function analyze($id)
    {
        /** @var Project $project */
		session_write_close();
        $project = Project::findOrFail($id);
        $analyzers = $this->analyzerService->getList()[$project->getType()];

        return view('review.analyze', [
            'project' => $project,
            'analyzers' => $analyzers,
            'from' => date('d-m-Y'),
            'to' => date('d-m-Y'),
        ]);
    }
	
	public function runCron(){
		
	}

    public function results($id)
    {
        /** @var Project $project */
        $project = Project::findOrFail($id);
        $analyzers = $this->analyzerService->getList()[$project->getType()];

		unset($analyzers['changes']['reviews_per_commit']);
		unset($analyzers['changes']['patchsets_per_user']);
		unset($analyzers['changes']['commit_without_corrections']);
		unset($analyzers['changes']['all_commits_per_user']);
		
		
        $rankers = $this->analyzerService->getRankers()[$project->getType()];
        $results = \Session::get('results.' . $project->getAttribute('id'), [
            'from' => date('d-m-Y'),
            'to' => date('d-m-Y'),
            'results' => [],
        ]);
        $tabs = [
            'changes' => 'Zmiany',
            'comments' => 'Komentarze',
            'statistics' => 'Statystyki',
            'topics' => 'Tematy',
            'pairs' => 'Pary',
        ];

        if (empty($results['results'])) {
            flash()->error('Brak wyników. Spróbuj ponownie.');

            return redirect()->route('review.analyze', $project->getAttribute('id'));
        }

        return view('review.results', [
            'project' => $project,
            'tabs' => $tabs,
            'analyzers' => $analyzers,
            'rankers' => $rankers,
            'from' => $results['from'],
            'to' => $results['to'],
            'results' => $results['results'],
        ]);
    }
}
