<?php namespace App\Http\Controllers;

use App\Http\Requests\FetchCodeRequest;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\BadgeRequest;
use App\Project;
use app\Services\CodeFetcherInterface;
use Request;

class ProjectsController extends Controller
{
	/** @var CodeFetcherInterface[] */
	private $codeFetchers;

	function __construct($codeFetchers)
	{
		$this->codeFetchers = $codeFetchers;
		$this->middleware('auth');
	}

	public function index()
	{
		/** @var Project[] $projects */
		$projects = Project::all();

		return view('projects.list', ['projects' => $projects]);
	}

	public function show($id)
	{
		/** @var Project $project */
		$project = Project::findOrFail($id);

		return view('projects.show', ['project' => $project]);
	}
	
	public function badges($id){
		$project = Project::findOrFail($id);
		$types = Project::getTypes();

		return view('projects.badges', ['project' => $project, 'types' => $types]);
	}

	public function create()
	{
		$types = Project::getTypes();

		return view('projects.create', ['types' => $types]);
	}

	public function store(ProjectRequest $request)
	{
		Project::create($request->all());

		flash()->success('Nowy projekt został dodany.');

		return redirect()->route('projects');
	}

	public function edit($id)
	{
		/** @var Project $project */
		$project = Project::findOrFail($id);
		$types = Project::getTypes();

		return view('projects.edit', ['project' => $project, 'types' => $types]);
	}

	public function update($id, ProjectRequest $request)
	{
		/** @var Project $project */
		$project = Project::findOrFail($id);
		$project->update($request->all());

		flash()->success('Projekt został zaktualizowany.');

		return redirect()->route('projects.show', $id);
	}
	
	public function updateBadge($id, BadgeRequest $request)
	{
		/** @var Project $project */
		$project = Project::findOrFail($id);
		$project->update($request->all());

		flash()->success('Projekt został zaktualizowany.');

		return redirect()->route('projects', $id);
	}

	public function delete($id)
	{
		Project::destroy($id);

		flash()->success('Projekt został usunięty.');

		return redirect()->route('projects');
	}

	public function getCode(FetchCodeRequest $request, $id)
	{
		/** @var Project $project */
		$project = Project::findOrFail($id);

		$codeFetcher = $this->getCodeFetcher($project);
		$code = $codeFetcher->getCode($project, $request->get('change'), $request->get('revision'), $request->get('filename'));

		return response()->json([
			'success' => true,
			'language' => $code['type'],
			'code' => $code,
		]);
	}

	private function getCodeFetcher(Project $project)
	{
		foreach ($this->codeFetchers as $codeFetcher) {
			if ($codeFetcher->getType() == $project->getType()) {
				return $codeFetcher;
			}
		}

		throw new \Exception(sprintf('Code fetcher not found for project "%s" with type "%s"',
			$project->getAttribute('label'), $project->getType()));
	}
}
