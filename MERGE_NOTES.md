# Merge notes: `Ward` project merged into `Ward-Inventory-System - C2`

C2 is the base. Everything that only exists in `Ward` was added. Where both have the same file, the
Ward functions were added to the C2 file.

## Functions / files renamed because the name clashed
| What | Was (Ward) | Now | References updated |
|---|---|---|---|
| `MedicineController::destroy()` | `destroy` | `destroyMedicine` | route `DELETE /inventory/medicines/{id}` |
| `DatabaseSeeder::run()` | `run` | `runWardInventorySeeders` | not called automatically (see below) |
| `layouts/app.blade.php` | `layouts.app` | `layouts.ward-app` | 12 Ward views `@extends`, `WardInventoryTest` |
| Route `/` | Ward home | `/ward` (`ward.root`) | |
| Route `/home`, name `home` | | `/ward/home`, `ward.home` | views, `RouteServiceProvider::HOME`, `LoginController`, tests |
| Route name `login` | `GET /login` | `ward.login` | views, controllers, guests redirect in `bootstrap/app.php` |

C2 keeps `/` (login), `/home` and the `login` / `home` route names.

## Same name AND same meaning (kept once, not duplicated)
Eloquent relations such as `Medicine::category/unit/form/batches`, `Order::ward/details/requester/approver`,
`OrderDetail::order/medicine`, `StockReceipt::batch/ward/receiver`, `Category::medicines`, `Unit::medicines`,
`MedicineForm::medicines`, `MedicineBatch::medicine/supplier`, `AppServiceProvider::register`.
`User::casts()` was combined (adds `is_admin`), `AppServiceProvider::boot()` uses Ward's body (C2's was empty).

## Database
* Both projects create `roles, wards, categories, units, medicine_forms, suppliers, medicines, medicine_batches,
  stock_receipts, stock_ledger, orders, order_details, patients`. C2 creates them first; Ward's create-migrations
  now skip a table that already exists.
* `users`: one migration with C2's `name` (now nullable) plus Ward's `first_name, last_name, is_admin, role`.
* New `2026_11_01_000000_reconcile_ward_columns_on_c2_tables.php` adds the columns Ward expects (`created_at`, stock_ledger `source_table/source_id/recorded_by`).
* C2's old unused `patients` table (patient_id/name/nic/bht) is renamed `patients_legacy`; Ward's `patients` is created.
* Ward FK columns that point at C2's bigint ids were changed to `unsignedBigInteger`.
* The `ms_approval_status` ENUM migration now also keeps C2's `Completed` value; MySQL-only statements skip on SQLite.

## Not carried over
* `2026_10_24_000000_add_daily_count_details_to_general_transactions.php` is an **empty file** in Ward and would make `php artisan migrate` crash. Skipped.
* Ward's `ExampleTest` (expects `/` -> `route('dashboard')`) and `setUp()` of `WardInventorySystemTest`; C2's versions kept.
* `phpunit.xml`: C2's (SQLite in-memory) kept; Ward's pointed at MySQL `ward_inventory`.

## After unzipping
```
composer install     # Ward added livewire, laravel-ide-helper, dump-server
npm install && npm run build
php artisan migrate
```
`DatabaseSeeder::run()` still seeds C2's data. Ward's seeders are in `runWardInventorySeeders()`; its `RoleSeeder`
deletes any role other than Admin/Doctor/Nurse/Pharmacist (that would remove C2's "MS Officer"), so run it only if you want that.
