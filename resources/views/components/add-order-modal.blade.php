<!-- Add Requisition Order Modal -->
<div id="addOrderModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-cart-plus" style="color: var(--primary-blue); margin-right: 8px;"></i> Create Pharmacy Requisition Order</h3>
            <button class="btn-close-modal" onclick="closeModal('addOrderModal')">&times;</button>
        </div>
        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="medicine_id">Drug / Medicine *</label>
                    <select id="medicine_id" name="medicine_id" class="form-control" required>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" {{ isset($selectedMedicine) && $selectedMedicine->id == $med->id ? 'selected' : '' }}>
                                {{ $med->name }} ({{ $med->item_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="qty_requested">Requested Quantity *</label>
                        <input type="number" id="qty_requested" name="qty_requested" class="form-control" placeholder="e.g. 30" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="ward_id">Ward *</label>
                        <select id="ward_id" name="ward_id" class="form-control" required>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->id }}">{{ $ward->ward_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="requested_by">Confirm Signature For Request (Requested By) *</label>
                    <select id="requested_by" name="requested_by" class="form-control" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->role_name ?? 'Staff' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="remark">Remark / Notes</label>
                    <textarea id="remark" name="remark" class="form-control" rows="2" placeholder="e.g. Urgent stock requisition for ward balance"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addOrderModal')">Cancel</button>
                <button type="submit" class="btn-add-primary">Submit Requisition Order</button>
            </div>
        </form>
    </div>
</div>
