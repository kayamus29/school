import React from 'react';
import { Head } from '@inertiajs/react';

export default function Welcome({ auth, app }: { auth: any, app: { name: string } }) {
    return (
        <div className="min-h-screen flex items-center justify-center bg-zinc-50">
            <Head title="Welcome" />
            <div className="p-8 bg-white shadow-xl rounded-2xl border border-zinc-200 max-w-md w-full text-center">
                <h1 className="text-3xl font-bold text-zinc-900 mb-2">{app.name}</h1>
                <p className="text-zinc-500 mb-6">Technical Foundation: SUCCESS</p>
                <div className="p-4 bg-zinc-100 rounded-lg text-sm text-left font-mono">
                    <p className="text-zinc-600 mb-2">// Shared State</p>
                    <pre className="overflow-x-auto">
                        {JSON.stringify({ auth: !!auth.user }, null, 2)}
                    </pre>
                </div>
                <div className="mt-8 text-xs text-zinc-400">
                    Laravel 8 + Vite + React + TypeScript + Inertia
                </div>
            </div>
        </div>
    );
}
