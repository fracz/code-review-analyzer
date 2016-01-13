<?php

namespace App;

class Model extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * Find a model by its primary key or fails with exception.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return \Illuminate\Support\Collection|static|null
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public static function findOrFail($id, $columns = array('*'))
	{
		return static::query()->findOrFail($id, $columns);
	}
}
