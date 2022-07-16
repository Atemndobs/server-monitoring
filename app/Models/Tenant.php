<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tenant
 * 
 * @property string $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $data
 * 
 * @property Collection|Domain[] $domains
 *
 * @package App\Models
 */
class Tenant extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
	protected $table = 'tenants';
	public $incrementing = false;

	protected $casts = [
		'data' => 'json'
	];

	protected $fillable = [
		'data'
	];

	public function domains()
	{
		return $this->hasMany(Domain::class);
	}
}
