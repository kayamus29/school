import React from 'react';
import { cn } from '@/lib/utils';

export function Card({ className, children }: { className?: string, children: React.ReactNode }) {
    return (
        <div className={cn("bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden", className)}>
            {children}
        </div>
    );
}

export function CardHeader({ className, children }: { className?: string, children: React.ReactNode }) {
    return (
        <div className={cn("px-6 py-4 border-b border-zinc-200 bg-zinc-50/50", className)}>
            {children}
        </div>
    );
}

export function CardContent({ className, children }: { className?: string, children: React.ReactNode }) {
    return (
        <div className={cn("px-6 py-6", className)}>
            {children}
        </div>
    );
}

export function CardTitle({ className, children }: { className?: string, children: React.ReactNode }) {
    return (
        <h3 className={cn("text-base font-semibold text-zinc-900", className)}>
            {children}
        </h3>
    );
}

export function CardDescription({ className, children }: { className?: string, children: React.ReactNode }) {
    return (
        <p className={cn("text-xs text-zinc-500 mt-1", className)}>
            {children}
        </p>
    );
}
