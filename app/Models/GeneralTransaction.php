namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralTransaction extends Model
{
    protected $table = 'general_transactions';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'ward_id',
        'quantity_received',
        'quantity_issued',
        'date',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'quantity_received' => 'integer',
            'quantity_issued' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GeneralItem::class, 'item_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }


    public function recordedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
