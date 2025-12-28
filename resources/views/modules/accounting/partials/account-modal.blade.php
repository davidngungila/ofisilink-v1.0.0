<!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="accountModalTitle">New Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="accountForm">
                <div class="modal-body" style="max-height: calc(90vh - 160px); overflow-y: auto; overflow-x: hidden;">
                    <input type="hidden" id="accountId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Account Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountCode" name="code" required>
                            <small class="text-muted">Unique account identifier</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountType" name="type" required onchange="updateCategoryOptions(this.value)">
                                <option value="">Select Type</option>
                                <option value="Asset">Asset</option>
                                <option value="Liability">Liability</option>
                                <option value="Equity">Equity</option>
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="accountCategory" name="category">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parent Account</label>
                            <select class="form-select" id="accountParent" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($allAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" step="0.01" class="form-control" id="accountOpeningBalance" name="opening_balance" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opening Balance Date</label>
                            <input type="date" class="form-control" id="accountOpeningDate" name="opening_balance_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="accountSortOrder" name="sort_order" value="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="accountIsActive" name="is_active" checked>
                                <label class="form-check-label" for="accountIsActive">
                                    Active Account
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="accountDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const categoryOptions = {
    'Asset': ['Current Asset', 'Fixed Asset', 'Non-Current Asset'],
    'Liability': ['Current Liability', 'Non-Current Liability'],
    'Equity': ['Equity', 'Retained Earnings'],
    'Income': ['Operating Income', 'Non-Operating Income'],
    'Expense': ['Operating Expense', 'Non-Operating Expense', 'Cost of Goods Sold']
};

function updateCategoryOptions(type) {
    const categorySelect = document.getElementById('accountCategory');
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    if (type && categoryOptions[type]) {
        categoryOptions[type].forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            categorySelect.appendChild(option);
        });
    }
}
</script>






