<?php namespace App\Http\Requests;

class AnalyzeRequest extends Request
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
			'project' => 'required|integer',
			'from' => 'required|date_format:d-m-Y',
			'to' => 'required|date_format:d-m-Y',
		];

		if ($this->route()->hasParameter('id')) {
			unset($rules['project']);
		}

		return $rules;
	}

}
