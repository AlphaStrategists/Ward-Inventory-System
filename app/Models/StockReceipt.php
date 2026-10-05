namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceipt extends Model
{
    protected $table = 'stock_receipts';
    public $timestamps = false;

    protected $fillable = [
        'batch_id',
        'ward_id',
        'quantity_received',
        'received_by',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'quantity_received' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }


    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
