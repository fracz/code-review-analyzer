<?php namespace App\Http\Requests;

class FetchCodeRequest extends Request
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
			'change' => 'required|string',
			'revision' => 'required|string',
			'filename' => 'required|string'
		];

		return $rules;
	}
}
