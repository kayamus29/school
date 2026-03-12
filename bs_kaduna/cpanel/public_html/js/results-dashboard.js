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
                this.loadBreakdown(data.studentId, data.courseId, data.semesterId);
            });
        });
    },

    async loadBreakdown(studentId, courseId, semesterId) {
        const modal = new bootstrap.Modal(document.getElementById('breakdownModal'));
        const container = document.getElementById('breakdownContent');

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        modal.show();

        try {
            const response = await axios.get('/ajax/results/breakdown', {
                params: { student_id: studentId, course_id: courseId, semester_id: semesterId }
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
        let html = `
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Assessment</th>
                            <th class="text-center">CA 1</th>
                            <th class="text-center">CA 2</th>
                            <th class="text-center">Exam</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.assessments.forEach(m => {
            html += `
                <tr>
                    <td class="fw-bold">${m.exam.exam_name}</td>
                    <td class="text-center">${m.ca1_mark || 0}</td>
                    <td class="text-center">${m.ca2_mark || 0}</td>
                    <td class="text-center">${m.exam_mark || 0}</td>
                    <td class="text-center fw-bold">${m.marks}</td>
                </tr>
            `;
        });

        if (data.summary) {
            html += `
                <tr class="table-primary fw-bold">
                    <td>Term Summary</td>
                    <td colspan="3" class="text-end">Final Calculated:</td>
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

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.key === '?') {
                alert('Results Dashboard Shortcuts:\nEsc: Close Modal\n?: Show help');
            }
        });
    }
};

window.addEventListener('DOMContentLoaded', () => ResultsDashboard.init());
