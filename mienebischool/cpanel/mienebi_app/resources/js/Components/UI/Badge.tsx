import React from 'react';
import { cn } from '@/lib/utils';

type BadgeVariant = 'default' | 'success' | 'warning' | 'error' | 'info' | 'neutral';

interface BadgeProps {
    children: React.ReactNode;
    variant?: BadgeVariant;
    className?: string;
}

const variants: Record<BadgeVariant, string> = {
    default: "bg-zinc-100 text-zinc-800 border-zinc-200",
    success: "bg-emerald-50 text-emerald-700 border-emerald-200",
    warning: "bg-amber-50 text-amber-700 border-amber-200",
    error: "bg-rose-50 text-rose-700 border-rose-200",
    info: "bg-sky-50 text-sky-700 border-sky-200",
    neutral: "bg-zinc-50 text-zinc-500 border-zinc-100",
};

export default function Badge({ children, variant = 'default', className }: BadgeProps) {
    return (
        <span className={cn(
            "inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border tracking-wider uppercase",
            variants[variant],
            className
        )}>
            {children}
        </span>
    );
}
