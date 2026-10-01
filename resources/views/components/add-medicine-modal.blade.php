<!-- Add Medicine Modal -->
<div id="addMedicineModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus-circle" style="color: var(--primary-blue); margin-right: 8px;"></i> Add New Medicine Item</h3>
            <button class="btn-close-modal" onclick="closeModal('addMedicineModal')">&times;</button>
        </div>
        <form action="{{ route('medicines.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_code">Item Code *</label>
                        <input type="text" id="item_code" name="item_code" class="form-control" placeholder="e.g. MED-INJ-004" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Medicine Name *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Adrenalin" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ isset($currentCategory) && $currentCategory->id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="unit_id">Unit *</label>
                        <select id="unit_id" name="unit_id" class="form-control" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="form_id">Dosage Form *</label>
                        <select id="form_id" name="form_id" class="form-control" required>
                            @foreach($forms as $form)
                                <option value="{{ $form->id }}">{{ $form->form_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="strength">Strength</label>
                        <input type="text" id="strength" name="strength" class="form-control" placeholder="e.g. 1 mg/ml">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="min_level">Minimum Stock Level *</label>
                        <input type="number" id="min_level" name="min_level" class="form-control" value="30" required>
                    </div>
                    <div class="form-group">
                        <label for="warning_limit">Warning Stock Limit *</label>
                        <input type="number" id="warning_limit" name="warning_limit" class="form-control" value="50" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addMedicineModal')">Cancel</button>
                <button type="submit" class="btn-add-primary">Save Medicine</button>
            </div>
        </form>
    </div>
</div>
