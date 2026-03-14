/**
 * Class Fee Management System
 * Handles AJAX definitions, Master Table expansions, and Multi-step Bulk Billing Wizard.
 */

const ClassFeeManager = {
    // State
    currentClassId: null,
    currentSemesterId: null,
    currentSessionId: null,
    wizardStep: 1,

    init() {
        this.bindEvents();
        this.initTooltips();
        this.setupKeyboardShortcuts();
    },

    bindEvents() {
        // Master Table Expand/Collapse
        document.querySelectorAll('.toggle-expand').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleRowExpansion(e));
        });

        // Global Expand/Collapse
        const expandAllBtn = document.getElementById('expandAll');
        if(expandAllBtn) expandAllBtn.addEventListener('click', () => this.toggleAllRows(true));
        
        const collapseAllBtn = document.getElementById('collapseAll');
        if(collapseAllBtn) collapseAllBtn.addEventListener('click', () => this.toggleAllRows(false));

        // Manage Modal Launch
        document.querySelectorAll('.manage-term-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.launchManageModal(e.currentTarget));
        });

        // AJAX Form Submission
        const manageForm = document.getElementById('manageFeeForm');
        if(manageForm) {
            manageForm.addEventListener('submit', (e) => this.handleFeeSubmission(e));
        }

        // Wizard Navigation
        document.getElementById('nextStep')?.addEventListener('click', () => this.navigateWizard(1));
        document.getElementById('prevStep')?.addEventListener('click', () => this.navigateWizard(-1));
        
        // Search Filter
        document.getElementById('classSearch')?.addEventListener('input', (e) => this.filterClasses(e.target.value));
    },

    // --- Master Table Logic ---
    toggleRowExpansion(e) {
        const btn = e.currentTarget;
        const targetId = btn.dataset.target;
        const container = document.getElementById(targetId);
        
        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', !isExpanded);
        container.classList.toggle('expanded');
        
        const icon = btn.querySelector('.bi');
        if(icon) {
            icon.classList.toggle('bi-plus-circle', isExpanded);
            icon.classList.toggle('bi-dash-circle', !isExpanded);
        }
    },

    toggleAllRows(expand) {
        document.querySelectorAll('.fee-details-container').forEach(c => {
            if(expand) c.classList.add('expanded');
            else c.classList.remove('expanded');
        });
        document.querySelectorAll('.toggle-expand').forEach(btn => {
            btn.setAttribute('aria-expanded', expand);
            const icon = btn.querySelector('.bi');
            if(icon) {
                icon.classList.toggle('bi-plus-circle', !expand);
                icon.classList.toggle('bi-dash-circle', expand);
            }
        });
    },

    filterClasses(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.class-row').forEach(row => {
            const name = row.dataset.className.toLowerCase();
            row.style.display = name.includes(q) ? '' : 'none';
        });
    },

    // --- AJAX Fee Management ---
    async launchManageModal(btn) {
        this.currentClassId = btn.dataset.classId;
        this.currentSemesterId = btn.dataset.semesterId;
        this.currentSessionId = btn.dataset.sessionId;

        document.getElementById('modal_class_id').value = this.currentClassId;
        document.getElementById('modal_semester_id').value = this.currentSemesterId;
        document.getElementById('modal_session_id').value = this.currentSessionId;
        
        document.getElementById('target-class').textContent = btn.dataset.className;
        document.getElementById('target-term').textContent = btn.dataset.semesterName;

        this.loadFeeList();
    },

    async loadFeeList() {
        const listContainer = document.getElementById('modalFeeList');
        listContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

        try {
            const response = await axios.get('/ajax/accounting/fees/list', {
                params: {
                    class_id: this.currentClassId,
                    semester_id: this.currentSemesterId,
                    session_id: this.currentSessionId
                }
            });

            if(response.data.success) {
                this.renderFeeList(response.data.fees, response.data.total);
            }
        } catch (error) {
            this.showToast('Error loading fees', 'danger');
        }
    },

    renderFeeList(fees, total) {
        const listContainer = document.getElementById('modalFeeList');
        if(fees.length === 0) {
            listContainer.innerHTML = '<div class="alert alert-light text-center border">No fees defined for this term.</div>';
            return;
        }

        let html = '<ul class="list-group list-group-flush border rounded">';
        fees.forEach(fee => {
            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center fee-item-row">
                    <div>
                        <span class="fw-bold">${fee.fee_head.name}</span>
                        <div class="small text-muted">${fee.description || ''}</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold">₦${Number(fee.amount).toLocaleString()}</span>
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="ClassFeeManager.deleteFee(${fee.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </li>
            `;
        });
        html += `
            <li class="list-group-item list-group-item-light d-flex justify-content-between align-items-center fw-bold">
                <span>Total Definition</span>
                <span class="text-primary fs-5">₦${Number(total).toLocaleString()}</span>
            </li>
        </ul>`;
        listContainer.innerHTML = html;
    },

    async handleFeeSubmission(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        try {
            const formData = new FormData(form);
            const response = await axios.post('/ajax/accounting/fees/store', formData);

            if(response.data.success) {
                this.showToast('Fee added successfully', 'success');
                form.reset();
                this.loadFeeList();
                // Optionally update master table inline if we wanted "Optimistic UI"
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Error saving fee';
            this.showToast(msg, 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    },

    async deleteFee(id) {
        if(!confirm('Delete this fee definition?')) return;

        try {
            const response = await axios.delete(`/ajax/accounting/fees/destroy/${id}`);
            if(response.data.success) {
                this.showToast('Fee removed', 'success');
                this.loadFeeList();
            }
        } catch (error) {
            this.showToast('Error deleting fee', 'danger');
        }
    },

    // --- Bulk Billing Wizard ---
    async navigateWizard(delta) {
        const nextStep = this.wizardStep + delta;
        if(nextStep === 2 && delta > 0) {
            await this.loadBulkPreview();
        }
        
        this.wizardStep = nextStep;
        this.updateWizardUI();
    },

    async loadBulkPreview() {
        const semesterId = document.getElementById('bulk_semester_id').value;
        const sessionId = document.querySelector('input[name="session_id"]').value;

        if(!semesterId) {
            this.showToast('Please select a term first', 'warning');
            this.wizardStep = 1;
            return;
        }

        const previewContainer = document.getElementById('wizard-preview-content');
        previewContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-grow text-warning"></div><p>Calculating financial impact...</p></div>';

        try {
            const response = await axios.get('/ajax/accounting/fees/bulk-preview', {
                params: { semester_id: semesterId, session_id: sessionId }
            });

            if(response.data.success) {
                this.renderBulkPreview(response.data);
            }
        } catch (error) {
            this.showToast('Error generating preview', 'danger');
            this.navigateWizard(-1);
        }
    },

    renderBulkPreview(data) {
        const s = data.summary;
        const container = document.getElementById('wizard-preview-content');
        
        let html = `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="text-muted small uppercase fw-bold">Active Students</div>
                            <div class="h4 mb-0 fw-bold">${s.total_students.toLocaleString()}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-primary" style="background: #f0f7ff">
                        <div class="card-body">
                            <div class="text-primary small uppercase fw-bold">Estimated Total Billing</div>
                            <div class="h4 mb-0 fw-bold text-primary">₦${s.total_estimated.toLocaleString()}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h6 class="fw-bold mb-3">Readiness Audit</h6>
            <div class="list-group list-group-flush border rounded mb-3" style="max-height: 200px; overflow-y: auto;">
        `;

        data.details.forEach(item => {
            const icon = item.is_ready ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span><i class="bi ${icon} me-2"></i> ${item.class_name}</span>
                    <span class="text-muted small">₦${item.term_total.toLocaleString()}/stud</span>
                </div>
            `;
        });

        html += '</div>';

        if(s.incomplete_count > 0) {
            html += `
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>${s.incomplete_count} classes</strong> have no fees defined. These students will NOT be billed.
                </div>
            `;
        } else {
            html += `
                <div class="alert alert-success py-2 small mb-0">
                    <i class="bi bi-check-circle"></i> All classes have fee definitions. System is 100% ready.
                </div>
            `;
        }

        container.innerHTML = html;
        
        // Final Authorization Logic
        const authBtn = document.getElementById('authorize-btn');
        if(authBtn) authBtn.disabled = false;
    },

    updateWizardUI() {
        document.querySelectorAll('.wizard-step').forEach((el, idx) => {
            el.classList.toggle('active', (idx + 1) === this.wizardStep);
        });

        // Indicators
        document.querySelectorAll('.step-indicator').forEach((el, idx) => {
            const step = idx + 1;
            el.classList.remove('active', 'complete');
            if(step === this.wizardStep) el.classList.add('active');
            if(step < this.wizardStep) el.classList.add('complete');
        });

        // Buttons
        document.getElementById('prevStep').style.display = this.wizardStep === 1 ? 'none' : 'block';
        document.getElementById('nextStep').style.display = this.wizardStep === 3 ? 'none' : 'block';
    },

    // --- Utilities ---
    showToast(message, type = 'primary') {
        const container = document.getElementById('toastWrapper') || this.createToastContainer();
        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById(id);
        const toast = new bootstrap.Toast(el, { delay: 5000 });
        toast.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    },

    createToastContainer() {
        const div = document.createElement('div');
        div.id = 'toastWrapper';
        div.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        div.style.zIndex = '1100';
        document.body.appendChild(div);
        return div;
    },

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+B for Bulk Bill
            if(e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('bulkBillingModal'));
                modal.show();
            }
            // ? for shortcuts help
            if(e.key === '?') {
                alert('Keyboard Shortcuts:\nCtrl+B: Execute Bulk Billing\nEsc: Close Modals\n?: Show this help');
            }
        });
    }
};

window.addEventListener('DOMContentLoaded', () => ClassFeeManager.init());
