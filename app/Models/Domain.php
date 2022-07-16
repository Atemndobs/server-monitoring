<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Domain
 * 
 * @property int $id
 * @property string $domain
 * @property string $tenant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class Domain extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
	protected $table = 'domains';

	protected $fillable = [
		'domain',
		'tenant_id'
	];

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}
}
