<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $action_performed
 * @property string|null $ip_address
 * @property string $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereActionPerformed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $patient_id
 * @property string $bht_no
 * @property int $ward_id
 * @property \Illuminate\Support\Carbon $admit_date
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispensation> $dispensations
 * @property-read int|null $dispensations_count
 * @property-read \App\Models\Patient $patient
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission whereAdmitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission whereBhtNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admission whereWardId($value)
 */
	class Admission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralItem> $generalItems
 * @property-read int|null $general_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $admission_id
 * @property int $batch_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $qty_given
 * @property string $dosage
 * @property string|null $usage_time
 * @property int $issued_by
 * @property int|null $witnessed_by
 * @property-read \App\Models\Admission $admission
 * @property-read \App\Models\MedicineBatch $batch
 * @property-read \App\Models\User $issuedBy
 * @property-read \App\Models\User|null $witnessedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereAdmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereQtyGiven($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereUsageTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispensation whereWitnessedBy($value)
 */
	class Dispensation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $report_title
 * @property string $start_date
 * @property string $end_date
 * @property int $generated_by
 * @property string $file_path
 * @property string $created_at
 * @property-read \App\Models\User $generatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereGeneratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereReportTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispensingReport whereStartDate($value)
 */
	class DispensingReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $item_code
 * @property string $name
 * @property int $category_id
 * @property-read \App\Models\Category $category
 * @property-read int $current_balance
 * @property-read bool $is_low_stock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralStockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralItem whereName($value)
 */
	class GeneralItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $item_id
 * @property int $ward_id
 * @property string $adjustment_type
 * @property int $quantity
 * @property string $reason
 * @property int $adjusted_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User $adjustedBy
 * @property-read \App\Models\User $adjuster
 * @property-read \App\Models\GeneralItem $item
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereAdjustedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereAdjustmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralStockAdjustment whereWardId($value)
 */
	class GeneralStockAdjustment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $item_id
 * @property int $ward_id
 * @property int $quantity_received
 * @property int $quantity_issued
 * @property \Illuminate\Support\Carbon $date
 * @property int $recorded_by
 * @property-read \App\Models\GeneralItem $item
 * @property-read \App\Models\User $recordedBy
 * @property-read \App\Models\User $recorder
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereQuantityIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereQuantityReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralTransaction whereWardId($value)
 */
	class GeneralTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $item_code
 * @property string $name
 * @property int $category_id
 * @property int $unit_id
 * @property int $form_id
 * @property string|null $strength
 * @property bool $is_controlled
 * @property int $min_level
 * @property int $warning_limit
 * @property int $units_per_pack
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineBatch> $batches
 * @property-read int|null $batches_count
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\MedicineForm $form
 * @property-read mixed $stock
 * @property-read \App\Models\Unit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine controlled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereIsControlled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereMinLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereStrength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereUnitsPerPack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereWarningLimit($value)
 */
	class Medicine extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $medicine_id
 * @property int|null $supplier_id
 * @property string $batch_no
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineStockAdjustment> $adjustments
 * @property-read int|null $adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispensation> $dispensations
 * @property-read int|null $dispensations_count
 * @property-read int $current_balance
 * @property-read bool $is_expired
 * @property int $quantity
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockLedger> $ledgerEntries
 * @property-read int|null $ledger_entries_count
 * @property-read \App\Models\Medicine $medicine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockReceipt> $receipts
 * @property-read int|null $receipts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockLedger> $stockLedger
 * @property-read int|null $stock_ledger_count
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineBatch whereSupplierId($value)
 */
	class MedicineBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $form_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineForm query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineForm whereFormName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineForm whereId($value)
 */
	class MedicineForm extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property int $ward_id
 * @property string $adjustment_type
 * @property int $quantity
 * @property string $reason
 * @property int $adjusted_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User $adjustedBy
 * @property-read \App\Models\MedicineBatch $batch
 * @property string $type
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereAdjustedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereAdjustmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineStockAdjustment whereWardId($value)
 */
	class MedicineStockAdjustment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property int $is_read
 * @property string $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ward_id
 * @property string $req_no
 * @property \Illuminate\Support\Carbon $date
 * @property int $requested_by
 * @property string $ms_approval_status
 * @property int|null $approved_by
 * @property string|null $remark
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $approver
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderDetail> $details
 * @property-read int|null $details_count
 * @property-read \App\Models\User $requestedBy
 * @property-read \App\Models\User $requester
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereMsApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereReqNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereWardId($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $medicine_id
 * @property int $qty_requested
 * @property int $qty_issued
 * @property string|null $remark
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereQtyIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereQtyRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderDetail whereRemark($value)
 */
	class OrderDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $patient_name
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admission> $admissions
 * @property-read int|null $admissions_count
 * @property-read \App\Models\Admission|null $currentAdmission
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispensation> $dispensations
 * @property-read int|null $dispensations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient wherePatientName($value)
 */
	class Patient extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $permission_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission wherePermissionName($value)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $role_name
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereRoleName($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property int $ward_id
 * @property string $adjustment_type
 * @property int $quantity
 * @property string $reason
 * @property int $adjusted_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User $adjustedBy
 * @property-read \App\Models\MedicineBatch $batch
 * @property string $type
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereAdjustedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereAdjustmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereWardId($value)
 */
	class StockAdjustment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property int $ward_id
 * @property string $transaction_type
 * @property int $quantity
 * @property string $source_table
 * @property int $source_id
 * @property int $recorded_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\MedicineBatch $batch
 * @property-read \App\Models\User $recorder
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereSourceTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockLedger whereWardId($value)
 */
	class StockLedger extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property int $ward_id
 * @property int $quantity_received
 * @property int $received_by
 * @property \Illuminate\Support\Carbon $date
 * @property-read \App\Models\MedicineBatch $batch
 * @property-read \App\Models\User $receivedBy
 * @property-read \App\Models\User $receiver
 * @property-read \App\Models\Ward $ward
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereQuantityReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockReceipt whereWardId($value)
 */
	class StockReceipt extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $supplier_name
 * @property string|null $contact_info
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineBatch> $medicineBatches
 * @property-read int|null $medicine_batches_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereContactInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereSupplierName($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $unit_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUnitName($value)
 */
	class Unit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property \App\Models\Role|null $role
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispensation> $dispensationsIssued
 * @property-read int|null $dispensations_issued_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispensation> $dispensationsWitnessed
 * @property-read int|null $dispensations_witnessed_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralStockAdjustment> $generalStockAdjustments
 * @property-read int|null $general_stock_adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralTransaction> $generalTransactions
 * @property-read int|null $general_transactions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Ward|null $ward
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ward_number
 * @property string $ward_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admission> $admissions
 * @property-read int|null $admissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralStockAdjustment> $generalStockAdjustments
 * @property-read int|null $general_stock_adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralTransaction> $generalTransactions
 * @property-read int|null $general_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockReceipt> $stockReceipts
 * @property-read int|null $stock_receipts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward whereWardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ward whereWardNumber($value)
 */
	class Ward extends \Eloquent {}
}

