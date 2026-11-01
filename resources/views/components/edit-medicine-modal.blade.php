<!-- Edit Medicine Modal -->
<div id="editMedicineModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color: var(--primary-blue); margin-right: 8px;"></i> Edit Medicine Item</h3>
            <button class="btn-close-modal" type="button" onclick="closeModal('editMedicineModal')">&times;</button>
        </div>
        <form id="editMedicineForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_med_item_code">Item Code *</label>
                        <input type="text" id="edit_med_item_code" name="item_code" class="form-control" placeholder="e.g. MED-INJ-004" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_med_name">Medicine Name *</label>
                        <input type="text" id="edit_med_name" name="name" class="form-control" placeholder="e.g. Adrenalin" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_med_category_id">Category *</label>
                        <select id="edit_med_category_id" name="category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_med_unit_id">Unit *</label>
                        <select id="edit_med_unit_id" name="unit_id" class="form-control" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_med_form_id">Dosage Form *</label>
                        <select id="edit_med_form_id" name="form_id" class="form-control" required>
                            @foreach($forms as $form)
                                <option value="{{ $form->id }}">{{ $form->form_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_med_strength">Strength</label>
                        <input type="text" id="edit_med_strength" name="strength" class="form-control" placeholder="e.g. 1 mg/ml">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_med_min_level">Minimum Stock Level *</label>
                        <input type="number" id="edit_med_min_level" name="min_level" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_med_warning_limit">Warning Stock Limit *</label>
                        <input type="number" id="edit_med_warning_limit" name="warning_limit" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editMedicineModal')">Cancel</button>
                <button type="submit" class="btn-add-primary">Update Medicine</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditMedicineModal(data) {
    const form = document.getElementById('editMedicineForm');
    form.action = "{{ url('/medicines') }}/" + data.id;

    if (document.getElementById('edit_med_item_code')) {
        document.getElementById('edit_med_item_code').value = data.item_code || '';
    }
    if (document.getElementById('edit_med_name')) {
        document.getElementById('edit_med_name').value = data.name || '';
    }
    if (document.getElementById('edit_med_category_id')) {
        document.getElementById('edit_med_category_id').value = data.category_id || '';
    }
    if (document.getElementById('edit_med_unit_id')) {
        document.getElementById('edit_med_unit_id').value = data.unit_id || '';
    }
    if (document.getElementById('edit_med_form_id')) {
        document.getElementById('edit_med_form_id').value = data.form_id || '';
    }
    if (document.getElementById('edit_med_strength')) {
        document.getElementById('edit_med_strength').value = data.strength || '';
    }
    if (document.getElementById('edit_med_min_level')) {
        document.getElementById('edit_med_min_level').value = data.min_level || 30;
    }
    if (document.getElementById('edit_med_warning_limit')) {
        document.getElementById('edit_med_warning_limit').value = data.warning_limit || 50;
    }

    openModal('editMedicineModal');
}
</script>

