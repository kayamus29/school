import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppShell from '@/Layouts/AppShell';
import Breadcrumbs from '@/Components/Layout/Breadcrumbs';
import { Card, CardHeader, CardContent, CardTitle, CardDescription } from '@/Components/UI/Card';
import Badge from '@/Components/UI/Badge';
import BreakdownModal from '@/Components/Results/BreakdownModal';
import { Printer, AlertOctagon } from 'lucide-react';
import type { User, Semester, Course, FinalMark, Promotion } from '@/types/results';

interface StudentDashboardProps {
    student: User;
    semesters: Semester[];
    courses: Course[];
    results: Record<number, FinalMark[]>;
    promotion: Promotion | null;
    withheld: boolean;
    error?: string;
}

export default function StudentDashboard({
    student,
    semesters,
    courses,
    results,
    promotion,
    withheld,
    error,
}: StudentDashboardProps) {
    const [selectedBreakdown, setSelectedBreakdown] = useState<{
        courseId: number;
        semesterId: number;
        courseName: string;
        semesterName: string;
    } | null>(null);

    const calculateAnnualTotal = (courseId: number): number => {
        const courseResults = results[courseId] || [];
        return courseResults.reduce((sum, mark) => sum + (mark.final_mark || 0), 0);
    };

    const getPassStatus = (courseId: number): 'Pass' | 'Fail' | 'Pending' => {
        const total = calculateAnnualTotal(courseId);
        const requiredSemesters = semesters.length;
        const courseResults = results[courseId] || [];

        if (courseResults.length < requiredSemesters) return 'Pending';
        return total / requiredSemesters >= 50 ? 'Pass' : 'Fail';
    };

    const getMarkColor = (mark: number): string => {
        if (mark >= 80) return 'bg-emerald-50 text-emerald-700 font-semibold';
        if (mark >= 60) return 'bg-sky-50 text-sky-700 font-medium';
        if (mark >= 40) return 'bg-amber-50 text-amber-700';
        return 'bg-rose-50 text-rose-700';
    };

    const handlePrint = () => {
        window.print();
    };

    // Financial Withholding Notice
    if (withheld) {
        return (
            <AppShell>
                <Head title="My Academic Performance" />
                <Breadcrumbs items={[{ label: 'Results' }]} />

                <div className="bg-rose-50 border-2 border-rose-200 rounded-xl p-8 shadow-sm">
                    <div className="flex items-start gap-4">
                        <div className="p-3 bg-rose-100 rounded-full">
                            <AlertOctagon className="w-8 h-8 text-rose-600" />
                        </div>
                        <div className="flex-1">
                            <h3 className="text-xl font-bold text-rose-900 mb-2">Academic Records Withheld</h3>
                            <p className="text-rose-700 text-base mb-4">
                                Detailed results and performance records are temporarily withheld due to an outstanding financial balance.
                            </p>
                            <hr className="border-rose-200 my-4" />
                            <p className="text-rose-600 text-sm">
                                Please visit the Accountant's office or settle your balance to regain access.
                            </p>
                        </div>
                    </div>
                </div>
            </AppShell>
        );
    }

    // Error State
    if (error) {
        return (
            <AppShell>
                <Head title="My Academic Performance" />
                <Breadcrumbs items={[{ label: 'Results' }]} />

                <Card>
                    <CardContent className="py-12 text-center">
                        <p className="text-zinc-500">{error}</p>
                    </CardContent>
                </Card>
            </AppShell>
        );
    }

    return (
        <AppShell>
            <Head title="My Academic Performance" />

            <div className="flex items-center justify-between mb-6">
                <div>
                    <Breadcrumbs items={[{ label: 'Results' }]} />
                    <h1 className="text-2xl font-bold text-zinc-900 mt-2">My Academic Performance</h1>
                    <p className="text-sm text-zinc-500 mt-1">
                        {promotion?.schoolClass.class_name} | {promotion?.session.session_name}
                    </p>
                </div>
                <button
                    onClick={handlePrint}
                    className="flex items-center gap-2 px-4 py-2 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition-colors print:hidden"
                >
                    <Printer size={16} />
                    <span className="text-sm font-medium">Download Transcript</span>
                </button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Course-wise Performance Summary</CardTitle>
                    <CardDescription>Academic performance across all terms</CardDescription>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse">
                            <thead className="bg-zinc-50 border-b border-zinc-200">
                                <tr>
                                    <th className="text-left px-6 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider">
                                        Course Name
                                    </th>
                                    {semesters.map((semester) => (
                                        <th key={semester.id} className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider">
                                            {semester.semester_name}
                                        </th>
                                    ))}
                                    <th className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider bg-zinc-100">
                                        Annual Total
                                    </th>
                                    <th className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider bg-zinc-100">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {courses.map((course) => {
                                    const courseResults = results[course.id] || [];
                                    const annualTotal = calculateAnnualTotal(course.id);
                                    const status = getPassStatus(course.id);

                                    return (
                                        <tr key={course.id} className="hover:bg-zinc-50 transition-colors">
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-zinc-900">{course.course_name}</div>
                                                <div className="text-xs text-zinc-500 mt-0.5">{course.course_type}</div>
                                            </td>
                                            {semesters.map((semester) => {
                                                const mark = courseResults.find((m) => m.semester_id === semester.id);
                                                return (
                                                    <td key={semester.id} className="px-4 py-4 text-center">
                                                        {mark ? (
                                                            <button
                                                                onClick={() => setSelectedBreakdown({
                                                                    courseId: course.id,
                                                                    semesterId: semester.id,
                                                                    courseName: course.course_name,
                                                                    semesterName: semester.semester_name,
                                                                })}
                                                                className={`px-3 py-1.5 rounded-md text-sm transition-all hover:ring-2 hover:ring-offset-1 ${getMarkColor(mark.final_mark)}`}
                                                            >
                                                                {mark.final_mark.toFixed(2)}
                                                            </button>
                                                        ) : (
                                                            <span className="text-zinc-300 text-sm">—</span>
                                                        )}
                                                    </td>
                                                );
                                            })}
                                            <td className="px-4 py-4 text-center bg-zinc-50">
                                                <span className="font-bold text-base text-zinc-900">
                                                    {annualTotal.toFixed(2)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4 text-center bg-zinc-50">
                                                <Badge
                                                    variant={
                                                        status === 'Pass' ? 'success' : status === 'Fail' ? 'error' : 'neutral'
                                                    }
                                                >
                                                    {status}
                                                </Badge>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            {/* Summary Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 print:hidden">
                <Card>
                    <CardContent className="py-4">
                        <div className="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">
                            Total Courses
                        </div>
                        <div className="text-2xl font-bold text-zinc-900">{courses.length}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="py-4">
                        <div className="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">
                            Passed Courses
                        </div>
                        <div className="text-2xl font-bold text-emerald-600">
                            {courses.filter((c) => getPassStatus(c.id) === 'Pass').length}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="py-4">
                        <div className="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">
                            Average Performance
                        </div>
                        <div className="text-2xl font-bold text-zinc-900">
                            {courses.length > 0
                                ? (
                                    courses.reduce((sum, c) => sum + calculateAnnualTotal(c.id), 0) /
                                    (courses.length * semesters.length)
                                ).toFixed(1)
                                : '0.0'}
                            %
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Breakdown Modal */}
            {selectedBreakdown && (
                <BreakdownModal
                    isOpen={!!selectedBreakdown}
                    onClose={() => setSelectedBreakdown(null)}
                    studentId={student.id}
                    courseId={selectedBreakdown.courseId}
                    semesterId={selectedBreakdown.semesterId}
                    courseName={selectedBreakdown.courseName}
                    semesterName={selectedBreakdown.semesterName}
                />
            )}
        </AppShell>
    );
}
