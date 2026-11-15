# STAGE 01 FINAL REPORT: DATABASE ARCHITECTURE, INJECTION & INJECTION ANTIBIOTICS
*(Comprehensive Technical Audit & Deep Verification Report with Live Hosted Evidence)*

**Project:** Hospital Ward Inventory Management System  
**Scope:** Injection & Injection Antibiotics Integration  
**Stage:** Stage 01 — Read-Only Database Inspection, Field Mapping, Risk Validation & Isolated Testing  
**Status:** COMPLETE & VERIFIED (Stage 01 Scope Only — Stopped for Independent Review)  
**Security Notice:** All database passwords, API tokens, and sensitive infrastructure secrets have been strictly omitted.

---

# PART I: BASELINE DATABASE ARCHITECTURE & LIVE SQL EVIDENCE

## 1. Existing Database Analysis
- **Hosted Production Database:** MySQL 9.7.2 (Hosted on Railway Cloud: `altaria.proxy.rlwy.net:13742`)
- **Local Isolated Testing Database:** MySQL Community Server 26.10.0 (`127.0.0.1:3307`, Database: `test_ward_inventory`)
- **Compatibility:** Full MySQL InnoDB engine compatibility, supporting foreign keys, transactional triggers, and ACID compliance.

### Verification of Dropped Legacy Injection Tables
A live read-only inspection confirmed that the three legacy standalone tables do **NOT** exist in the database:
- `Schema::hasTable('injection_medicines')` ➡️ **`false`**
- `Schema::hasTable('injection_requisitions')` ➡️ **`false`**
- `Schema::hasTable('injection_antibiotic_requisitions')` ➡️ **`false`**

*Migration History Note:* Legacy migration records (IDs 36, 37, 38) remain in the hosted `migrations` tracking table. Per instructions, these historical tracking rows were preserved without alteration.

---

## 2. Hosted Database vs. Proposed Columns (Raw SQL Proof)

Inspected directly from the hosted Railway database on **2026-11-08T00:36:51Z**:

### Raw SQL: `SHOW FULL COLUMNS FROM orders`
```text
Field                  Type                                              Null     Key    Default      Extra
-------------------------------------------------------------------------------------------------------------------------
id                     int                                               NO       PRI    NULL         auto_increment
ward_id                int                                               NO       MUL    NULL         
req_no                 varchar(50)                                       NO       UNI    NULL         
date                   timestamp                                         NO              CURRENT_TIMESTAMP DEFAULT_GENERATED
requested_by           bigint unsigned                                   NO       MUL    NULL         
ms_approval_status     enum('Pending','Approved','Rejected','Cancelled') NO              Pending      
approved_by            bigint unsigned                                   YES      MUL    NULL         
remark                 text                                              YES             NULL         
```

### Raw SQL: `SHOW FULL COLUMNS FROM order_details`
```text
Field                  Type                      Null     Key    Default      Extra
-----------------------------------------------------------------------------------------
id                     int                       NO       PRI    NULL         auto_increment
order_id               int                       NO       MUL    NULL         
medicine_id            int                       NO       MUL    NULL         
qty_requested          int                       NO              NULL         
qty_issued             int                       NO              0            
remark                 text                      YES             NULL         
```

### Verification of Proposed Additive Columns in Hosted Database:
- `orders.issued_by` ➡️ **NO (GENUINELY MISSING)**
- `orders.issued_date` ➡️ **NO (GENUINELY MISSING)**
- `orders.received_by` ➡️ **NO (GENUINELY MISSING)**
- `orders.received_date` ➡️ **NO (GENUINELY MISSING)**
- `order_details.qty_received` ➡️ **NO (GENUINELY MISSING)**
- `order_details.balance_before_order` ➡️ **NO (GENUINELY MISSING)**

All six proposed columns are genuinely missing from the hosted database.

---

# PART II: STAGE 01 – FINAL ARCHITECTURE CLARIFICATION & RISK VALIDATION

This section contains the in-depth technical investigations into stock receipts, batch relationships, duplicate prevention, partial receipts, one-medicine-per-order verification, routes, and risk validations.

---

## 1. STOCK RECEIPT – ORDER RELATIONSHIP INVESTIGATION

### A. How `MedicineController::receiveOrder()` Operates
- **Source File:** [`app/Http/Controllers/MedicineController.php`](file:///c:/Users/ASUS/Desktop/new_p2/Ward-Inventory-System/app/Http/Controllers/MedicineController.php#L737-L805)
- **Method Signature:** `public function receiveOrder(Request $request, $category, $id)` (line 737)
- **Identification:** The parameter `$id` passed in the URL route (`/inventory/medicines/{category}/orders/{id}/receive`) is the primary key `id` of the `order_details` table:
  - Line 739: `$detail = OrderDetail::findOrFail($id);`
  - The parent order is retrieved via Eloquent relationship: `$detail->order`.
- **Batch Processing:**
  - If `batch_type === 'new'` (lines 751–765): Creates a new `MedicineBatch` record with `medicine_id => $detail->medicine_id`, `batch_no`, `expiry_date`, and optional `supplier_id`.
  - If `batch_type !== 'new'` (lines 766–778): Validates `batch_id` via `'batch_id' => 'required|integer|exists:medicine_batches,id'`.
- **Stock Receipt Insertion:**
  - Lines 783–791:
    ```php
    if ($detail->qty_issued > 0) {
        StockReceipt::create([
            'batch_id' => $batchId,
            'ward_id' => $wardId,
            'quantity_received' => $detail->qty_issued,
            'received_by' => $userId,
            'date' => now(),
        ]);
    }
    ```
- **Receipt Marker:**
  - Line 802: `$detail->update(['remark' => trim('[RECEIVED_BY:' . (Auth::user()->name ?? 'Nurse') . '] ' . $remarkStr)]);`

### B. Batch Association & Medicine Verification Risk
- **Source File:** `app/Http/Controllers/MedicineController.php` (lines 768–770)
- **Finding:** The controller validates that the selected `batch_id` exists in `medicine_batches`:
  `'batch_id' => 'required|integer|exists:medicine_batches,id'`
- **Identified Risk:** The validator does **not** enforce at the database query level that `medicine_batches.medicine_id == $detail->medicine_id`. Although the Blade view modal dropdown filters by `$selectedMedicine->batches`, a manipulated HTTP POST request could submit a `batch_id` belonging to a different medicine.
- **Recommendation for Stage 02:** Add an explicit validator rule:
  ```php
  'batch_id' => [
      'required',
      'integer',
      Rule::exists('medicine_batches', 'id')->where('medicine_id', $detail->medicine_id)
  ]
  ```

### C. Traceability: Can a `StockReceipt` be Linked Back to its Order?
- **Inspection of `stock_receipts` Table:**
  ```sql
  CREATE TABLE `stock_receipts` (
    `id` int NOT NULL AUTO_INCREMENT,
    `batch_id` int NOT NULL,
    `ward_id` int NOT NULL,
    `quantity_received` int NOT NULL,
    `received_by` bigint unsigned NOT NULL,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`), ...
  );
  ```
- **Fact:** `stock_receipts` contains **no** `order_id` or `order_detail_id` column.
- **Traceability Analysis:**
  1. **From Requisition to Physical Stock (Forward):** The requisition audit trail on `orders` and `order_details` records the receiving nurse (`orders.received_by`), the receipt timestamp (`orders.received_date`), and the verified received quantity (`order_details.qty_received`).
  2. **From StockReceipt to Requisition (Reverse):** A row in `stock_receipts` cannot directly join to `orders` via a foreign key. The connection is decoupled: `orders` tracks clinical procurement, while `stock_receipts` tracks warehouse/ward inventory batches.
  3. **Distinguishing Multiple Orders for the Same Medicine:** If two separate requisitions for Adrenaline are received on the same day into the same batch, each requisition clearly maintains its own unique `orders.req_no`, `requested_by`, and `received_by`. However, the two corresponding rows in `stock_receipts` do not carry an `order_detail_id` pointer.
- **Architectural Conclusion:**
  - For displaying the Injection Requisition tables and satisfying all clinical columns: **Extending `orders` and `order_details` is 100% sufficient.**
  - If formal bi-directional database referential integrity from `stock_receipts` back to `order_details` is desired by hospital auditors, the minimum change would be an optional column: `stock_receipts.order_detail_id` (nullable foreign key). However, because the project scope strictly restricts modifications to `orders` and `order_details`, this is documented as an **architectural option** requiring explicit authorization.

---

## 2. DUPLICATE RECEIPT PREVENTION: LEDGER vs. RECEIPT

### Situation A – Duplicate Ledger Entry from a Single StockReceipt Record
- **Can a single `StockReceipt` create duplicate ledger entries?** **NO.**
- **Database Trigger Definition (`trg_receipt_ledger`):**
  ```sql
  -- Hosted Railway MySQL Database: SHOW CREATE TRIGGER trg_receipt_ledger
  CREATE DEFINER=`root`@`%` TRIGGER `trg_receipt_ledger` AFTER INSERT ON `stock_receipts` FOR EACH ROW BEGIN
      INSERT INTO stock_ledger 
          (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
      VALUES 
          (NEW.batch_id, NEW.ward_id, 'RECEIPT', NEW.quantity_received, 'stock_receipts', NEW.id, NEW.received_by);
  END;
  ```
- **Constraint Proof:**
  `stock_ledger` possesses a composite unique index:
  `UNIQUE KEY uk_ledger_source (source_table, source_id)`
- **Observer Deduplication (`StockReceiptObserver.php`):**
  The observer calls `StockLedger::firstOrCreate(['source_table' => 'stock_receipts', 'source_id' => $stockReceipt->id], ...)`. Because the trigger already inserted the row, `firstOrCreate` finds the existing entry and does nothing. Any concurrent duplicate insert is rejected with MySQL Error 1062.

### Situation B – Duplicate Stock Receipt from Repeated Submission (Identified Risk)
- **What happens if a nurse submits the "Confirm Receipt" form twice rapidly?**
- **Analysis:**
  - If two separate `StockReceipt` records are created (e.g., ID 10 and ID 11), `uk_ledger_source` sees `source_id = 10` and `source_id = 11`.
  - Both records are distinct primary keys. **The unique constraint `uk_ledger_source` WILL NOT prevent the second `StockReceipt`!** Both will trigger ledger entries, adding double the stock to the ward ledger.
- **Existing Application Check (`MedicineController.php` lines 745–747):**
  ```php
  if (str_contains($detail->remark ?? '', '[RECEIVED')) {
      return back()->with('error', 'This order has already been received.');
  }
  ```
- **Identified Concurrency Weaknesses in Existing Code:**
  1. The string check on `remark` is non-atomic and executes outside a database transaction (`DB::transaction`).
  2. No pessimistic locking (`lockForUpdate()`) is used.
  3. The update setting `[RECEIVED_BY:...]` occurs at line 802 — **after** `StockReceipt::create` at line 784! Under simultaneous requests, both requests pass line 745 before either reaches line 802.
- **Required Recommendation for Stage 02:**
  Implement atomic locking and column-level verification:
  ```php
  DB::transaction(function () use ($id, $validated, $userId, $wardId) {
      $detail = OrderDetail::where('id', $id)->lockForUpdate()->firstOrFail();
      $order = $detail->order()->lockForUpdate()->firstOrFail();

      if ($detail->qty_received !== null || $order->received_by !== null) {
          throw new \Exception('This order requisition has already been received.');
      }

      // Record receipt atomically
      $order->update([
          'received_by' => $userId,
          'received_date' => now(),
      ]);
      $detail->update([
          'qty_received' => $validated['qty_received'],
      ]);

      StockReceipt::create([ ... ]);
  });
  ```

---

## 3. PARTIAL RECEIPTS AND MULTIPLE BATCHES

### A. Example A – Single Delivery Partial Receipt (Discrepancy)
- **Scenario:** 100 ordered, 100 issued, 95 physically arrive intact.
- **Capability:** **Fully supported** by the proposed schema:
  - `order_details.qty_requested = 100`
  - `order_details.qty_issued = 100`
  - `order_details.qty_received = 95`
  - `StockReceipt::create(['quantity_received' => 95])` increases the ward ledger by exactly 95.
  - The order log permanently displays: Requested: 100 | Issued: 100 | Received: 95.

### B. Example B – Split Deliveries (95 Delivered First, 5 Delivered Later)
- **Can the existing application code support two separate receive actions on the same order detail?**
  **NO, not in the current code.**
  - As soon as the first delivery is received, the order is locked against further receipts (`str_contains($detail->remark, '[RECEIVED')`).
  - In our proposed schema, `orders.received_by` and `orders.received_date` are single scalar values on `orders`.
- **Clinical Practice in Hospital Wards:**
  In Sri Lankan government hospital ward workflows, pharmacy issues are discrete delivery events. If a pharmacy only supplies a partial quantity (95 of 100), the requisition is closed at 95, and remaining requirements are requested via a supplementary requisition.
- **Recommendation:** Keep the single-receipt per requisition model for Stage 02. If cumulative split receipts are mandated in the future, a dedicated `order_receipt_items` pivot table would be required.

### C. Example C – Multiple Batches for One Order (e.g. 60 from Batch A, 40 from Batch B)
- **Can `stock_receipts` record multiple batches?**
  `stock_receipts` has `batch_id int NOT NULL`. To record two batches, **two separate rows in `stock_receipts` must be inserted** (Row 1: Batch A, Qty 60; Row 2: Batch B, Qty 40).
- **Existing UI Limitation:** The current Blade receive modal provides only a single batch selection dropdown.
- **Stage 02 Recommendation:** For standard injection requisitions, one batch per order is the norm due to the one-medicine-per-order rule. If multi-batch receipts are needed, the modal can accept an array of batch/quantity pairs in Stage 02 without requiring any database schema alterations.

---

## 4. ONE MEDICINE PER ORDER – CODEBASE VERIFICATION

### Code Evidence from Existing Application
- **Source File:** [`app/Http/Controllers/MedicineController.php`](file:///c:/Users/ASUS/Desktop/new_p2/Ward-Inventory-System/app/Http/Controllers/MedicineController.php#L597-L652)
- **Method:** `public function storeOrder(Request $request, $category, $id)` (line 597)
- **Findings with Line Numbers:**
  - Line 609: `$medicine = Medicine::findOrFail($id);` — The order is initiated from a single selected medicine.
  - Line 616: `$order = Order::create([...]);` — A single `Order` record is generated.
  - Line 645: `OrderDetail::create([...]);` — Exactly **one** `OrderDetail` record is created for `$medicine->id`.
- **Verification:**
  1. In `MedicineController`, **one-medicine-per-order is ALREADY the inherent application pattern** because orders are submitted from the medicine details screen (`/inventory/{category}/{id}/details`).
  2. **Ordinary Inventory Categories:** The database schema retains a standard `1:N` foreign key (`order_details.order_id REFERENCES orders(id)`). Other procurement modules (e.g., Bulk or General Items) can insert multiple line items under one order without database constraint conflicts.
  3. **No Global Database Constraint:** No unique index on `order_details.order_id` is required or recommended.

---

## 5. EXISTING ROUTES, CATEGORY RESOLUTION & ACCESS CONTROL

### A. Existing Route Definitions (`routes/web.php`)
- **Source File:** [`routes/web.php`](file:///c:/Users/ASUS/Desktop/new_p2/Ward-Inventory-System/routes/web.php#L740-L758)
- Lines 740–758:
  ```php
  Route::prefix('inventory/{category}')
      ->name('inventory.')
      ->group(function () {
          Route::get('/', [MedicineController::class, 'index'])->name('index');
          Route::post('/store', [MedicineController::class, 'storeMedicine'])->name('store');
          Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
      });
  ```
- **Preservation:** All routes are dynamic and **MUST NOT be changed or renamed**.

### B. Live Categories in Hosted Database
```text
ID    Name                      Description                              Created At
------------------------------------------------------------------------------------------
1     Narcotics                 (null)                                   2026-10-20 03:23:42
2     Bulk                      (null)                                   2026-10-20 03:23:42
3     Syrups                    (null)                                   2026-10-23 07:00:26
4     Antibiotics               (null)                                   2026-10-29 06:35:08
5     Oral-antibiotics          (null)                                   2026-10-29 19:58:36
6     Injection                 Injection medicines for ward inventory   2026-10-31 08:16:12
7     Injection Antibiotics     Antibiotic injections                    2026-11-01 04:02:44
8     OralCountable             (null)                                   2026-11-03 04:38:57
9     General Items             (null)                                   2026-11-04 02:51:01
10    injections                (null)                                   2026-11-06 20:43:10
```

### C. URL Resolution Audit & Cross-Contamination Findings
Tested via SQL script against the hosted database:
1. **URL `/inventory/injections`:**
   - Matches Category ID 10 (`injections`, lowercase plural), which contains **0 medicines**.
   - URL `/inventory/injection` (singular) matches Category ID 6 (`Injection`), which contains **Medicine ID 5 (`Adirenalin`)**.
2. **URL `/inventory/injectionantibiotics`:**
   - In `MedicineController.php` line 48:
     `if (str_contains($categoryClean, 'antibiotic')) { ... LOWER(name) LIKE '%antibiotic%' ... }`
   - Because `injectionantibiotics` contains `antibiotic`, the query matches **both** Category ID 4 (`Antibiotics`) and Category ID 7 (`Injection Antibiotics`).
   - Conversely, accessing `/inventory/antibiotics` also matches Category ID 7.

### D. Proposed Minimal Controller Correction for Stage 02 (Zero Route Changes)
In `app/Http/Controllers/MedicineController.php` (line 35), refine `resolveCategoryAndMedicines()`:
```php
// 1. Alias 'injections' and 'injection' to Category ID 6
if (in_array($categoryClean, ['injection', 'injections'])) {
    $q->orWhere('id', 6)->orWhere('name', 'Injection');
}

// 2. Specific matching for Injection Antibiotics (ID 7)
if (str_contains($categoryClean, 'injection') && str_contains($categoryClean, 'antibiotic')) {
    $q->orWhere('id', 7)->orWhere('name', 'Injection Antibiotics');
}

// 3. Exclude Injection Antibiotics from general Antibiotics (ID 4)
if (str_contains($categoryClean, 'antibiotic') && !str_contains($categoryClean, 'injection') && !str_contains($categoryClean, 'oral')) {
    $q->orWhere(function ($sub) {
        $sub->whereRaw('LOWER(name) LIKE ?', ['%antibiotic%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%injection%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%oral%']);
    });
}
```
This ensures:
- `/inventory/injections` and `/inventory/injection` cleanly display Category ID 6.
- `/inventory/injectionantibiotics` cleanly displays Category ID 7.
- `/inventory/antibiotics` displays Category ID 4 without cross-contamination.

### E. User Access Control Verification
- Auth middleware on group: `auth` (all logged in users).
- Order Request: Nurse, Head Nurse, Admin (`medicine-dashboard.blade.php` line 624).
- MS Approval: Doctor / MS (`medicine-dashboard.blade.php` line 520, line 547).
- Pharmacy Issue: Pharmacist (`medicine-dashboard.blade.php` line 556, line 591).
- Ward Receipt: Nurse, Head Nurse (`medicine-dashboard.blade.php` line 615).
All existing permissions and role-based actions are fully preserved.

---

## 6. MIGRATION COMPATIBILITY REVIEW

### File: `database/migrations/2026_11_07_000010_add_injection_fields_to_orders_and_order_details_tables.php`

1. **Foreign Key Data Type Match:**
   - `users.id`: `bigint unsigned NOT NULL AUTO_INCREMENT`
   - `orders.issued_by`: `unsignedBigInteger` (`bigint unsigned NULL`), `FOREIGN KEY REFERENCES users(id) ON DELETE SET NULL`
   - `orders.received_by`: `unsignedBigInteger` (`bigint unsigned NULL`), `FOREIGN KEY REFERENCES users(id) ON DELETE SET NULL`
   - **Verification:** 100% data type, unsigned flag, and collation match with `users.id` and existing `orders.approved_by`.
2. **Backward Compatibility:** All 6 columns are `nullable()`. Existing records will have `NULL` without constraint violations.
3. **Rollback Safety:** `down()` drops foreign keys first, then drops columns.

---

## 7. ARCHITECTURAL STATUS & ACTION TABLE

| Item | Status | Evidence | Required Action |
|:---|:---:|:---|:---|
| **StockReceipt relationship** | **DECOUPLED** | `stock_receipts` table has no `order_id` or `order_detail_id` column. Orders track requisition lifecycle; `stock_receipts` tracks warehouse batches. | Maintain decoupled architecture for Stage 02; audit trail is preserved on `orders`/`order_details`. Optional FK on `stock_receipts` documented for future enhancement. |
| **Duplicate receipt prevention** | **IDENTIFIED RISK** | `uk_ledger_source` prevents duplicate ledger rows from one receipt, but does NOT prevent duplicate receipt submissions if a user clicks twice. | In Stage 02, wrap `receiveOrder` in `DB::transaction()` with `lockForUpdate()` and an atomic check on `detail.qty_received !== null`. |
| **Partial receipts** | **SUPPORTED** | `order_details.qty_received` allows recording physical delivered quantity (e.g., 95 of 100). | In Stage 02, validate `qty_received <= qty_issued` and pass `qty_received` to `StockReceipt::create()`. |
| **Multiple batches** | **SINGLE-BATCH CURRENTLY** | `stock_receipts.batch_id` holds 1 batch per row. Modal accepts 1 batch per receipt. | Maintain 1-batch-per-receive for Stage 02; if multi-batch receipt is needed, allow submitting array of batches in modal. |
| **One medicine per order** | **VERIFIED IN CODE** | `MedicineController::storeOrder()` (lines 616, 645) already creates 1 `Order` and 1 `OrderDetail` per selected medicine. | Preserve this behavior in Stage 02 for `/inventory/injections` and `/inventory/injectionantibiotics`. |
| **Category resolution** | **NEEDS MINIMAL FIX** | Test script proved `/inventory/injections` matches ID 10 (empty) and `/inventory/injectionantibiotics` cross-matches ID 4 and 7. | In Stage 02, refine `resolveCategoryAndMedicines()` with alias logic without changing any routes. |
| **Route preservation** | **VERIFIED 100%** | `routes/web.php` line 740 uses dynamic `inventory/{category}`. | Do not modify `routes/web.php`. |
| **Migration compatibility** | **VERIFIED 100%** | `SHOW CREATE TABLE users` matches `bigint unsigned`. Isolated MySQL migration passed up/down tests. | Migration is ready to be applied whenever explicitly authorized. |

---

## 8. REMAINING DECISIONS REQUIRING APPROVAL

1. **Authorization to Apply Migration on Railway:** Confirm whether to apply `2026_11_07_000010_add_injection_fields_to_orders_and_order_details_tables.php` to the hosted Railway MySQL database.
2. **Reverse Traceability Decision:** Confirm whether the decoupled relationship (requisition audit on `orders`, warehouse receipt on `stock_receipts`) is acceptable, or if you wish to add a nullable `stock_receipts.order_detail_id` foreign key.
3. **Approval for Stage 02 Code Implementation:** Confirm authorization to implement the controller refinements, model relations, and Blade views.

---

## STOP NOTICE
**Investigation complete. All implementation work remains completely halted.**  
No application code modified. No migrations run on Railway. No Git commits or pushes made.  
Awaiting your independent review and instructions.
