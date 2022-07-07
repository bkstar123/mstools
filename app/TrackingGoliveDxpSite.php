<?php
/**
 * TrackingGoliveDxpSite model
 *
 * @author: tuanha
 * @date: 07-July-2022
 */
namespace App;

use Bkstar123\BksCMS\AdminPanel\Admin;
use Illuminate\Database\Eloquent\Model;
use Bkstar123\MySqlSearch\Traits\MySqlSearch;

class TrackingGoliveDxpSite extends Model
{
    use MySqlSearch;

    const ON = true;

    const OFF = false;

    /**
     * List of columns for search enabling
     *
     * @var array
     */
    public static $mysqlSearchable = ['sites'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sites', 'admin_id'
    ];

    /**
     * A report belongs to an admin
     *
     * @return @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
