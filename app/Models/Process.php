<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Process
 * 
 * @property int $pid
 * @property string|null $name
 * @property Carbon|null $started_at
 * @property string|null $status
 * @property int|null $port
 * @property string|null $command
 * @property Carbon|null $last_checked_at
 * @property string|null $url
 *
 * @package App\Models
 */
class Process extends Model
{
	protected $table = 'processes';
	protected $primaryKey = 'pid';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'pid' => 'int',
		'port' => 'int'
	];

	protected $dates = [
		'started_at',
		'last_checked_at'
	];

	protected $fillable = [
		'name',
		'started_at',
		'status',
		'port',
		'command',
		'last_checked_at',
		'url'
	];
}
