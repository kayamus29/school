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

        if (!data.assessments || data.assessments.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted">No assessment data found.</div>';
            return;
        }

        // 1. Determine Dynamic Headers from the first assessment's exam rule
        // Fallback to standard 3 columns if not defined
        const representativeMark = data.assessments[0];
        const breakdownConfig = representativeMark.exam && representativeMark.exam.exam_rule
            ? representativeMark.exam.exam_rule.marks_breakdown
            : null;

        let headers = [];
        if (breakdownConfig && Array.isArray(breakdownConfig)) {
            headers = breakdownConfig.map(b => b.name);
        } else {
            // Default fallback if no rule found
            headers = ['CA 1', 'CA 2', 'Exam'];
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Assessment</th>
                            ${headers.map(h => `<th class="text-center">${h}</th>`).join('')}
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.assessments.forEach(m => {
            html += `<tr><td class="fw-bold text-nowrap">${m.exam.exam_name}</td>`;

            if (breakdownConfig) {
                // Dynamic rendering based on config keys
                breakdownConfig.forEach(item => {
                    // Slugify logic similar to PHP: 'Final Exam' -> 'final_exam'
                    const key = item.name.toLowerCase().replace(/ /g, '_');

                    let val = 0;
                    if (m.breakdown_marks && m.breakdown_marks[key] !== undefined) {
                        val = m.breakdown_marks[key];
                    } else if (m.breakdown_marks && m.breakdown_marks[item.name] !== undefined) {
                        val = m.breakdown_marks[item.name];
                    } else {
                        // Semantic Fallbacks for old data
                        if (key.includes('final') || key.includes('exam')) val = m.exam_mark || 0;
                        else if (key.includes('ca_1') || key.includes('ca1')) val = m.ca1_mark || 0;
                        else if (key.includes('ca_2') || key.includes('ca2')) val = m.ca2_mark || 0;
                    }

                    html += `<td class="text-center">${val}</td>`;
                });
            } else {
                // Fallback rendering
                html += `
                    <td class="text-center">${m.ca1_mark || 0}</td>
                    <td class="text-center">${m.ca2_mark || 0}</td>
                    <td class="text-center">${m.exam_mark || 0}</td>
                `;
            }

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

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.key === '?') {
                alert('Results Dashboard Shortcuts:\nEsc: Close Modal\n?: Show help');
            }
        });
    }
};

window.addEventListener('DOMContentLoaded', () => ResultsDashboard.init());
