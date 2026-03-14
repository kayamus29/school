import React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, Home } from 'lucide-react';

interface BreadcrumbItem {
    label: string;
    href?: string;
}

export default function Breadcrumbs({ items }: { items: BreadcrumbItem[] }) {
    return (
        <nav className="flex items-center space-x-2 text-xs font-medium text-zinc-500 mb-6">
            <Link href="/home" className="hover:text-zinc-900 transition-colors">
                <Home size={14} />
            </Link>
            {items.map((item, index) => (
                <React.Fragment key={index}>
                    <ChevronRight size={14} className="text-zinc-300" />
                    {item.href ? (
                        <Link href={item.href} className="hover:text-zinc-900 transition-colors">
                            {item.label}
                        </Link>
                    ) : (
                        <span className="text-zinc-900">{item.label}</span>
                    )}
                </React.Fragment>
            ))}
        </nav>
    );
}
