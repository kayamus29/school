/**
 * Results Dashboard Manager
 * Handles AJAX breakdowns and interactive grid features.
 */

const ResultsDashboard = {
    init() {
        this.bindEvents();
        this.setupKeyboardShortcuts();
    },

    bindEvents() {
        document.querySelectorAll('.clickable-mark').forEach(el => {
            el.addEventListener('click', (e) => {
                const data = e.currentTarget.dataset;
                this.loadBreakdown(data.studentId, data.courseId, data.semesterId, data.sessionId);
            });
        });
    },

    async loadBreakdown(studentId, courseId, semesterId, sessionId) {
        const modal = new bootstrap.Modal(document.getElementById('breakdownModal'));
        const container = document.getElementById('breakdownContent');

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        modal.show();

        try {
            const response = await axios.get('/ajax/results/breakdown', {
                params: { student_id: studentId, course_id: courseId, semester_id: semesterId, session_id: sessionId }
            });

            if (response.data.success) {
                this.renderBreakdown(response.data);
            }
        } catch (error) {
            container.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
        }
    },

    renderBreakdown(data) {
        const container = document.getElementById('breakdownContent');

        if (!data.assessments || data.assessments.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted">No assessment data found.</div>';
            return;
        }

        const representativeMark = data.assessments[0];
        const examRule = representativeMark.exam
            ? (representativeMark.exam.exam_rule || representativeMark.exam.examRule)
            : null;
        const breakdownConfig = examRule && examRule.marks_breakdown
            ? examRule.marks_breakdown
            : null;

        let headers = [];
        if (breakdownConfig && Array.isArray(breakdownConfig)) {
            headers = breakdownConfig.map((item) => ({
                key: this.normalizeBreakdownKey(item.name),
                label: item.name
            }));
        } else if (representativeMark.breakdown_marks && Object.keys(representativeMark.breakdown_marks).length > 0) {
            headers = Object.keys(representativeMark.breakdown_marks).map((key) => ({
                key,
                label: this.humanizeBreakdownKey(key)
            }));
        } else {
            headers = [
                { key: 'ca_1', label: 'CA 1' },
                { key: 'ca_2', label: 'CA 2' },
                { key: 'final_exam', label: 'Exam' }
            ];
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Assessment</th>
                            ${headers.map(h => `<th class="text-center">${h.label}</th>`).join('')}
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.assessments.forEach(m => {
            html += `<tr><td class="fw-bold text-nowrap">${m.exam.exam_name}</td>`;

            headers.forEach((header) => {
                const val = this.resolveBreakdownValue(m, header.key);
                html += `<td class="text-center">${val}</td>`;
            });

            html += `<td class="text-center fw-bold">${m.marks}</td></tr>`;
        });

        if (data.summary) {
            // Calculate colspan based on dynamic headers + 1 (Assessment col)
            // Actually it's just Headers count + 1 (Assessment) - 1 (Total cell itself) -> Headers count
            const colspan = headers.length + 1;

            html += `
                <tr class="table-primary fw-bold">
                    <td>Term Summary</td>
                    <td colspan="${colspan}" class="text-end">Final Calculated:</td>
                    <td class="text-center">${data.summary.final_marks}</td>
                </tr>
            `;
        }

        html += '</tbody></table></div>';

        if (data.summary && data.summary.note) {
            html += `<div class="mt-3 small text-muted italic"><strong>Note:</strong> ${data.summary.note}</div>`;
        }

        container.innerHTML = html;
    },

    normalizeBreakdownKey(label) {
        return String(label || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    },

    humanizeBreakdownKey(key) {
        return String(key || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase());
    },

    resolveBreakdownValue(mark, key) {
        if (mark.breakdown_marks && mark.breakdown_marks[key] !== undefined) {
            return mark.breakdown_marks[key];
        }

        const normalizedKey = this.normalizeBreakdownKey(key);
        if (mark.breakdown_marks && mark.breakdown_marks[normalizedKey] !== undefined) {
            return mark.breakdown_marks[normalizedKey];
        }

        if (normalizedKey.includes('final') || normalizedKey.includes('exam')) return mark.exam_mark || 0;
        if (normalizedKey === 'ca_1' || normalizedKey === 'ca1' || normalizedKey.includes('first_ca')) return mark.ca1_mark || 0;
        if (normalizedKey === 'ca_2' || normalizedKey === 'ca2' || normalizedKey.includes('second_ca')) return mark.ca2_mark || 0;

        return 0;
    },

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.key === '?') {
                alert('Results Dashboard Shortcuts:\nEsc: Close Modal\n?: Show help');
            }
        });
    }
};

window.addEventListener('DOMContentLoaded', () => ResultsDashboard.init());
