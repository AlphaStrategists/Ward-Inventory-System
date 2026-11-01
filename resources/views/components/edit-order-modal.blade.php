<!-- Edit Requisition Order Modal -->
<div id="editOrderModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary-blue); font-size: 18px;"></i>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Edit Pharmacy Requisition Order</h3>
                <span id="edit_order_req_no" style="font-size: 13px; font-weight: 600; color: var(--primary-blue); background: #eff6ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #bfdbfe;"></span>
            </div>
            <button class="btn-close-modal" type="button" onclick="closeModal('editOrderModal')">&times;</button>
        </div>
        <form id="editOrderForm" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_detail_id" name="detail_id">

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_medicine_id">Drug / Medicine *</label>
                        <select id="edit_medicine_id" name="medicine_id" class="form-control" required>
                            @foreach(($allCategoryMedicines ?? $medicines) as $med)
                                <option value="{{ $med->id }}">
                                    {{ $med->name }} ({{ $med->item_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_date">Order Date *</label>
                        <input type="date" id="edit_date" name="date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_qty_requested">Requested Quantity *</label>
                        <input type="number" id="edit_qty_requested" name="qty_requested" class="form-control" placeholder="e.g. 30" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_qty_issued">Received Quantity</label>
                        <input type="number" id="edit_qty_issued" name="qty_issued" class="form-control" placeholder="e.g. 0" min="0" value="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_ward_id">Ward *</label>
                        <select id="edit_ward_id" name="ward_id" class="form-control" required>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->id }}">{{ $ward->ward_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_ms_approval_status">MS Approval Status *</label>
                        <select id="edit_ms_approval_status" name="ms_approval_status" class="form-control" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_requested_by">Confirm Signature For Request (Requested By) *</label>
                        <select id="edit_requested_by" name="requested_by" class="form-control" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->role_name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_approved_by">Signature of Issued Officer / Approved By</label>
                        <select id="edit_approved_by" name="approved_by" class="form-control">
                            <option value="">-- None / Pending --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->role_name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_remark">Remark / Notes</label>
                    <textarea id="edit_remark" name="remark" class="form-control" rows="2" placeholder="e.g. Urgent stock requisition for ward balance"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editOrderModal')">Cancel</button>
                <button type="submit" class="btn-add-primary">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditOrderModal(data) {
    const form = document.getElementById('editOrderForm');
    form.action = "{{ url('/orders') }}/" + data.id;

    const reqBadge = document.getElementById('edit_order_req_no');
    if (reqBadge) {
        reqBadge.textContent = data.req_no ? data.req_no : '';
        reqBadge.style.display = data.req_no ? 'inline-block' : 'none';
    }

    if (document.getElementById('edit_detail_id')) {
        document.getElementById('edit_detail_id').value = data.detail_id || '';
    }
    if (document.getElementById('edit_medicine_id')) {
        document.getElementById('edit_medicine_id').value = data.medicine_id || '';
    }
    if (document.getElementById('edit_date')) {
        document.getElementById('edit_date').value = data.date || '';
    }
    if (document.getElementById('edit_qty_requested')) {
        document.getElementById('edit_qty_requested').value = data.qty_requested || '';
    }
    if (document.getElementById('edit_qty_issued')) {
        document.getElementById('edit_qty_issued').value = (data.qty_issued !== undefined && data.qty_issued !== null) ? data.qty_issued : 0;
    }
    if (document.getElementById('edit_ward_id')) {
        document.getElementById('edit_ward_id').value = data.ward_id || '';
    }
    if (document.getElementById('edit_ms_approval_status')) {
        document.getElementById('edit_ms_approval_status').value = data.ms_approval_status || 'Pending';
    }
    if (document.getElementById('edit_requested_by')) {
        document.getElementById('edit_requested_by').value = data.requested_by || '';
    }
    if (document.getElementById('edit_approved_by')) {
        document.getElementById('edit_approved_by').value = data.approved_by || '';
    }
    if (document.getElementById('edit_remark')) {
        document.getElementById('edit_remark').value = data.remark || '';
    }

    openModal('editOrderModal');
}
</script>

