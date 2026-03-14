export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    roles?: string[];
    status?: string;
}

export interface Semester {
    id: number;
    semester_name: string;
    session_id: number;
    start_date: string;
    end_date: string;
}

export interface Course {
    id: number;
    course_name: string;
    course_type: string;
    semester_id: number;
    class_id: number;
}

export interface FinalMark {
    id: number;
    student_id: number;
    course_id: number;
    semester_id: number;
    session_id: number;
    final_mark: number;
    created_at: string;
    updated_at: string;
}

export interface Promotion {
    id: number;
    student_id: number;
    class_id: number;
    section_id: number;
    session_id: number;
    status: string;
    schoolClass: {
        id: number;
        class_name: string;
        courses: Course[];
    };
    session: {
        id: number;
        session_name: string;
    };
}

export interface Mark {
    id: number;
    student_id: number;
    course_id: number;
    exam_id: number;
    mark: number;
    exam: {
        id: number;
        exam_name: string;
        weight: number;
        semester_id: number;
    };
}

export interface BreakdownData {
    success: boolean;
    assessments: Mark[];
    summary: FinalMark | null;
}
