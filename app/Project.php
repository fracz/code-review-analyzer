<?php namespace App;

class Project extends Model {

	protected $table = 'projects';

	protected $fillable = ['label', 'name', 'type', 'url', 'repository', 'username', 'password', 'badges_period'];

	protected $hidden = ['password'];

	public static function getTypes()
	{
		return [
			'gerrit' => 'Gerrit',
			'stash' => 'Stash',
		];
	}

	public function getChangeUrl($changeId, $revision = '') {
		$uri = '';

		switch ($this->getType()) {
			case 'gerrit':
				if (!empty($revision)) {
					$revision = '/'.$revision;
				}

				$uri = '#/c/'.$changeId.$revision;
				break;
			case 'stash':
				$uri = '/projects/'.$this->getAttribute('name').
					'/repos/'.$this->getAttribute('repository').
					'/pull-requests/'.$changeId.'/overview';
		}

		return $this->getAttribute('url').$uri;
	}

	/**
	 * Returns type name of the project.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->getAttribute('type');
	}
}
