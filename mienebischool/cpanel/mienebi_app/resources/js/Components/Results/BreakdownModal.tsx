import React, { useEffect, useState } from 'react';
import { X, Loader2 } from 'lucide-react';
import axios from 'axios';
import type { BreakdownData } from '@/types/results';

interface BreakdownModalProps {
    isOpen: boolean;
    onClose: () => void;
    studentId: number;
    courseId: number;
    semesterId: number;
    courseName: string;
    semesterName: string;
}

export default function BreakdownModal({
    isOpen,
    onClose,
    studentId,
    courseId,
    semesterId,
    courseName,
    semesterName,
}: BreakdownModalProps) {
    const [data, setData] = useState<BreakdownData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (isOpen) {
            fetchBreakdown();
        }
    }, [isOpen, studentId, courseId, semesterId]);

    const fetchBreakdown = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/ajax/results/breakdown', {
                params: {
                    student_id: studentId,
                    course_id: courseId,
                    semester_id: semesterId,
                },
            });

            setData(response.data);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Failed to load breakdown details');
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 print:hidden">
            <div className="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                {/* Header */}
                <div className="p-6 border-b border-zinc-200 flex items-start justify-between">
                    <div>
                        <h3 className="text-lg font-bold text-zinc-900">Assessment Breakdown</h3>
                        <p className="text-sm text-zinc-500 mt-1">
                            {courseName} - {semesterName}
                        </p>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 hover:bg-zinc-100 rounded-lg transition-colors"
                    >
                        <X size={20} className="text-zinc-500" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto p-6">
                    {loading && (
                        <div className="flex items-center justify-center py-12">
                            <Loader2 className="w-8 h-8 text-zinc-400 animate-spin" />
                        </div>
                    )}

                    {error && (
                        <div className="bg-rose-50 border border-rose-200 rounded-lg p-4 text-rose-700 text-sm">
                            {error}
                        </div>
                    )}

                    {data && !loading && !error && (
                        <div className="space-y-6">
                            {/* Assessments Table */}
                            <div>
                                <h4 className="text-sm font-bold text-zinc-700 uppercase tracking-wider mb-3">
                                    Individual Assessments
                                </h4>
                                <div className="overflow-x-auto">
                                    <table className="w-full border-collapse border border-zinc-200 rounded-lg overflow-hidden">
                                        <thead className="bg-zinc-50">
                                            <tr>
                                                <th className="text-left px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider border-b border-zinc-200">
                                                    Assessment Name
                                                </th>
                                                <th className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider border-b border-zinc-200">
                                                    Weight (%)
                                                </th>
                                                <th className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider border-b border-zinc-200">
                                                    Raw Score
                                                </th>
                                                <th className="text-center px-4 py-3 text-xs font-bold text-zinc-700 uppercase tracking-wider border-b border-zinc-200">
                                                    Weighted Score
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-zinc-100">
                                            {data.assessments.length > 0 ? (
                                                data.assessments.map((mark) => {
                                                    const weighted = (mark.mark * mark.exam.weight) / 100;
                                                    return (
                                                        <tr key={mark.id} className="hover:bg-zinc-50">
                                                            <td className="px-4 py-3 text-sm text-zinc-900">
                                                                {mark.exam.exam_name}
                                                            </td>
                                                            <td className="px-4 py-3 text-sm text-center text-zinc-600">
                                                                {mark.exam.weight}%
                                                            </td>
                                                            <td className="px-4 py-3 text-sm text-center font-medium text-zinc-900">
                                                                {mark.mark.toFixed(2)}
                                                            </td>
                                                            <td className="px-4 py-3 text-sm text-center font-semibold text-zinc-900">
                                                                {weighted.toFixed(2)}
                                                            </td>
                                                        </tr>
                                                    );
                                                })
                                            ) : (
                                                <tr>
                                                    <td colSpan={4} className="px-4 py-8 text-center text-sm text-zinc-500">
                                                        No assessment data available
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Summary */}
                            {data.summary && (
                                <div className="bg-zinc-50 border border-zinc-200 rounded-lg p-4">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-bold text-zinc-700 uppercase tracking-wider">
                                            Final Mark
                                        </span>
                                        <span className="text-2xl font-bold text-zinc-900">
                                            {data.summary.final_mark.toFixed(2)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-zinc-500 mt-2">
                                        Calculated from weighted assessments
                                    </p>
                                </div>
                            )}

                            {/* No Summary Warning */}
                            {!data.summary && data.assessments.length > 0 && (
                                <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-700 text-sm">
                                    Final mark has not been submitted for this semester.
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-zinc-200 flex justify-end">
                    <button
                        onClick={onClose}
                        className="px-4 py-2 bg-zinc-100 text-zinc-700 rounded-lg hover:bg-zinc-200 transition-colors font-medium text-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    );
}
