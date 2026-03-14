import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    Users,
    ClipboardList,
    GraduationCap,
    Wallet,
    Settings,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Bell,
    User,
    Menu
} from 'lucide-react';
import { cn } from '@/lib/utils';

interface NavItem {
    label: string;
    href: string;
    icon: React.ElementType;
    roles?: string[];
}

const navItems: NavItem[] = [
    { label: 'Dashboard', href: '/home', icon: LayoutDashboard },
    { label: 'Results', href: '/results', icon: ClipboardList },
    { label: 'Students', href: '/students', icon: Users },
    { label: 'Promotions', href: '/promotions', icon: ClipboardList },
    { label: 'Graduation', href: '/graduation', icon: GraduationCap },
    { label: 'Accounting', href: '/accounting', icon: Wallet },
    { label: 'Settings', href: '/settings', icon: Settings },
];

export default function AppShell({ children }: { children: React.ReactNode }) {
    const { auth } = usePage().props as any;
    const [isSidebarOpen, setIsSidebarOpen] = useState(true);

    return (
        <div className="min-h-screen bg-zinc-50 flex">
            {/* Sidebar */}
            <aside className={cn(
                "bg-zinc-900 text-zinc-400 flex flex-col transition-all duration-300 border-r border-zinc-800",
                isSidebarOpen ? "w-64" : "w-20"
            )}>
                <div className="p-6 flex items-center justify-between">
                    {isSidebarOpen && (
                        <span className="text-white font-bold text-lg tracking-tight">Unified<span className="text-zinc-500">Transform</span></span>
                    )}
                    <button
                        onClick={() => setIsSidebarOpen(!isSidebarOpen)}
                        className="p-1 hover:bg-zinc-800 rounded-md transition-colors"
                    >
                        {isSidebarOpen ? <ChevronLeft size={20} /> : <ChevronRight size={20} className="mx-auto" />}
                    </button>
                </div>

                <nav className="flex-1 px-3 space-y-1">
                    {navItems.map((item) => (
                        <Link
                            key={item.label}
                            href={item.href}
                            className={cn(
                                "flex items-center gap-3 px-3 py-2 rounded-lg transition-colors hover:text-white hover:bg-zinc-800",
                                isSidebarOpen ? "" : "justify-center px-0"
                            )}
                        >
                            <item.icon size={20} />
                            {isSidebarOpen && <span className="text-sm font-medium">{item.label}</span>}
                        </Link>
                    ))}
                </nav>

                <div className="p-4 border-t border-zinc-800">
                    <div className={cn(
                        "flex items-center gap-3 px-2 py-3 rounded-xl bg-zinc-800/50",
                        isSidebarOpen ? "" : "justify-center"
                    )}>
                        <div className="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-white shrink-0">
                            <User size={16} />
                        </div>
                        {isSidebarOpen && (
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-white truncate">{auth.user?.first_name}</p>
                                <p className="text-xs text-zinc-500 truncate">{auth.user?.roles?.[0]}</p>
                            </div>
                        )}
                    </div>
                </div>
            </aside>

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col min-w-0 max-h-screen overflow-hidden">
                {/* Header */}
                <header className="h-16 bg-white border-b border-zinc-200 flex items-center justify-between px-8 shrink-0">
                    <div className="flex items-center gap-4">
                        <button className="lg:hidden p-2 -ml-2 text-zinc-500">
                            <Menu size={20} />
                        </button>
                        <h2 className="text-sm font-medium text-zinc-500">Academic Management System</h2>
                    </div>

                    <div className="flex items-center gap-4">
                        <button className="p-2 text-zinc-400 hover:text-zinc-600 border border-zinc-200 rounded-lg transition-colors">
                            <Bell size={18} />
                        </button>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors"
                        >
                            <LogOut size={16} />
                            <span>Sign out</span>
                        </Link>
                    </div>
                </header>

                {/* Content */}
                <main className="flex-1 overflow-y-auto p-8">
                    <div className="max-w-7xl mx-auto">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
