<?php namespace App\Http\Requests;

class ProjectRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [
			'label' => 'required|string|min:3',
			'name' => 'required|string',
			'type' => 'required|string',
			'url' => 'required|url',
			'repository' => 'string',
			'username' => 'required|string',
			'password' => 'required|string',
		];

		if ($this->route()->hasParameter('id')) {
			unset($rules['password']);
		}

		return $rules;
	}

	public function all()
	{
		$items = parent::all();

		if (empty($items['password']))
		{
			unset($items['password']);
		}

		return $items;
	}
}
