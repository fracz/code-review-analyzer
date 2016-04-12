<?php namespace App\Http\Requests;

class BadgeRequest extends Request
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
			'badges_period' => 'required|integer',
		];

		if ($this->route()->hasParameter('id')) {
			unset($rules['password']);
		}

		return $rules;
	}
}
