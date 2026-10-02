import React from 'react';
import { useSelector } from 'react-redux';
import { Doughnut, Line, Bar } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Tooltip, Legend, Filler);

const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const peso = (n) => '₱' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });

// Small inline Heroicon (outline) wrapper — keeps icons consistent without
// pulling in an image/AI-generated asset.
function Icon({ path, className = 'h-5 w-5' }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" d={path} />
        </svg>
    );
}

const ICONS = {
    users: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
    check: 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    clock: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    banknotes: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.625c.621 0 1.125.504 1.125 1.125v.375M3.75 6h16.5M3.75 6v14.25m16.5-14.25v14.25m0-14.25h.75m-.75 0v.375c0 .621.504 1.125 1.125 1.125h.375m-1.5-1.5v.375c0 .621.504 1.125 1.125 1.125h.375M4.5 18.75h15V19.5a1.5 1.5 0 01-1.5 1.5h-12a1.5 1.5 0 01-1.5-1.5v-.75z',
    pie: 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
    trend: 'M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941',
    bars: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    breakdown: 'M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5',
};

function StatCard({ label, value, sub, icon, bg, text }) {
    return (
        <div className="rounded-xl bg-white p-6 ring-1 ring-gray-950/5 shadow-sm">
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-500">{label}</p>
                    <h2 className="mt-2 text-3xl font-semibold tracking-tight text-gray-950">{value}</h2>
                </div>
                <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${bg} ${text}`}>
                    <Icon path={icon} className="h-6 w-6" />
                </div>
            </div>
            <p className="mt-4 text-xs text-gray-400">{sub}</p>
        </div>
    );
}

function SectionHeader({ icon, iconClass, title, sub }) {
    return (
        <div className="pb-4 mb-5 border-b border-gray-200 flex items-center gap-3">
            <Icon path={icon} className={`h-5 w-5 ${iconClass}`} />
            <div>
                <h2 className="text-base font-semibold text-gray-950">{title}</h2>
                <p className="text-sm text-gray-500">{sub}</p>
            </div>
        </div>
    );
}

export default function App() {
    const { stats, status, thisMonth, monthlyApplications, monthlySubsidies, subsidyBreakdown } = useSelector(
        (state) => state.analytics
    );

    const statusTotal = Math.max(1, status.pending + status.verification + status.approved + status.rejected);

    const statusRows = [
        { label: 'Pending', value: status.pending, dot: 'bg-amber-500' },
        { label: 'For Verification', value: status.verification, dot: 'bg-orange-500' },
        { label: 'Approved', value: status.approved, dot: 'bg-emerald-500' },
        { label: 'Rejected', value: status.rejected, dot: 'bg-rose-500' },
    ];

    const avgPerBeneficiary = thisMonth.releases > 0 ? thisMonth.subsidy / thisMonth.releases : 0;

    return (
        <div>
            {/* STAT CARDS */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
                <StatCard
                    label="Total Senior Citizens"
                    value={Number(stats.totalSeniorCitizens).toLocaleString()}
                    sub={`${Number(stats.activeBeneficiaries).toLocaleString()} active of ${Number(stats.totalSeniorCitizens).toLocaleString()} total`}
                    icon={ICONS.users}
                    bg="bg-rose-50"
                    text="text-rose-600"
                />
                <StatCard
                    label="Approved Beneficiaries"
                    value={Number(stats.approvedBeneficiaries).toLocaleString()}
                    sub="Approved subsidy beneficiaries"
                    icon={ICONS.check}
                    bg="bg-emerald-50"
                    text="text-emerald-600"
                />
                <StatCard
                    label="Pending Applications"
                    value={Number(stats.pendingApplications).toLocaleString()}
                    sub="Applications waiting for action"
                    icon={ICONS.clock}
                    bg="bg-amber-50"
                    text="text-amber-600"
                />
                <StatCard
                    label="Total Subsidy Released"
                    value={peso(stats.totalSubsidyDistributed)}
                    sub="Total released subsidy amount"
                    icon={ICONS.banknotes}
                    bg="bg-indigo-50"
                    text="text-indigo-600"
                />
            </div>

            {/* ROW 1 — Status donut + This Month */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <SectionHeader
                        icon={ICONS.pie}
                        iconClass="text-indigo-600"
                        title="Application Status"
                        sub="Current subsidy application overview"
                    />
                    <div className="flex flex-col sm:flex-row items-center gap-6">
                        <div className="relative h-44 w-44 shrink-0">
                            <Doughnut
                                data={{
                                    labels: ['Pending', 'For Verification', 'Approved', 'Rejected'],
                                    datasets: [
                                        {
                                            data: [status.pending, status.verification, status.approved, status.rejected],
                                            backgroundColor: ['#f59e0b', '#f97316', '#10b981', '#f43f5e'],
                                            borderWidth: 0,
                                            hoverOffset: 4,
                                        },
                                    ],
                                }}
                                options={{
                                    cutout: '72%',
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: { callbacks: { label: (item) => ` ${item.label}: ${item.formattedValue}` } },
                                    },
                                }}
                            />
                            <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span className="text-2xl font-semibold text-gray-950">{statusTotal}</span>
                                <span className="text-xs text-gray-400">Total</span>
                            </div>
                        </div>
                        <div className="w-full space-y-3">
                            {statusRows.map((row) => (
                                <div key={row.label} className="flex items-center justify-between text-sm">
                                    <div className="flex items-center gap-2">
                                        <span className={`h-2.5 w-2.5 rounded-full ${row.dot}`} />
                                        <span className="text-gray-600">{row.label}</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-semibold text-gray-950">{row.value}</span>
                                        <span className="text-xs text-gray-400 w-10 text-right">
                                            {Math.round((row.value / statusTotal) * 100)}%
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div>
                    <SectionHeader
                        icon={ICONS.banknotes}
                        iconClass="text-rose-600"
                        title="This Month"
                        sub={new Date().toLocaleDateString(undefined, { month: 'long', year: 'numeric' })}
                    />
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <p className="text-sm text-gray-500">Subsidy Released</p>
                            <p className="mt-2 text-2xl font-semibold text-rose-700">{peso(thisMonth.subsidy)}</p>
                        </div>
                        <div>
                            <p className="text-sm text-gray-500">Releases</p>
                            <p className="mt-2 text-2xl font-semibold text-gray-950">{Number(thisMonth.releases).toLocaleString()}</p>
                        </div>
                    </div>
                    <div className="mt-5 pt-4 border-t border-gray-100">
                        <p className="text-xs text-gray-500 leading-relaxed flex items-start gap-2">
                            <Icon
                                path="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
                                className="h-4 w-4 shrink-0 text-gray-400 mt-0.5"
                            />
                            Average release this month is {peso(avgPerBeneficiary)} per beneficiary.
                        </p>
                    </div>
                </div>
            </div>

            {/* ROW 2 — Trend charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <SectionHeader
                        icon={ICONS.trend}
                        iconClass="text-sky-600"
                        title="Applications Submitted"
                        sub={`Monthly volume, ${new Date().getFullYear()}`}
                    />
                    <div className="h-56">
                        <Line
                            data={{
                                labels: MONTH_LABELS,
                                datasets: [
                                    {
                                        label: 'Applications',
                                        data: monthlyApplications,
                                        borderColor: '#0ea5e9',
                                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                                        tension: 0.35,
                                        fill: true,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#0ea5e9',
                                    },
                                ],
                            }}
                            options={{
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' } },
                                    x: { grid: { display: false } },
                                },
                            }}
                        />
                    </div>
                </div>

                <div>
                    <SectionHeader
                        icon={ICONS.bars}
                        iconClass="text-emerald-600"
                        title="Subsidy Released"
                        sub={`Monthly amount, ${new Date().getFullYear()}`}
                    />
                    <div className="h-56">
                        <Bar
                            data={{
                                labels: MONTH_LABELS,
                                datasets: [
                                    {
                                        label: 'Amount Released',
                                        data: monthlySubsidies,
                                        backgroundColor: '#10b981',
                                        borderRadius: 6,
                                        maxBarThickness: 28,
                                    },
                                ],
                            }}
                            options={{
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: (item) => ' ' + peso(item.raw) } },
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { callback: (v) => '₱' + Number(v).toLocaleString() }, grid: { color: '#f3f4f6' } },
                                    x: { grid: { display: false } },
                                },
                            }}
                        />
                    </div>
                </div>
            </div>

            {/* ROW 3 — Breakdown by program */}
            {subsidyBreakdown.length > 0 && (
                <div className="mb-8">
                    <SectionHeader
                        icon={ICONS.breakdown}
                        iconClass="text-violet-600"
                        title="Subsidy Breakdown by Program"
                        sub="Total amount released per subsidy program"
                    />
                    <div style={{ height: Math.max(180, subsidyBreakdown.length * 48) }}>
                        <Bar
                            data={{
                                labels: subsidyBreakdown.map((row) => row.name),
                                datasets: [
                                    {
                                        label: 'Total Released',
                                        data: subsidyBreakdown.map((row) => row.total_amount),
                                        backgroundColor: '#8b5cf6',
                                        borderRadius: 6,
                                        maxBarThickness: 24,
                                    },
                                ],
                            }}
                            options={{
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: (item) => ' ' + peso(item.raw) } },
                                },
                                scales: {
                                    x: { beginAtZero: true, ticks: { callback: (v) => '₱' + Number(v).toLocaleString() }, grid: { color: '#f3f4f6' } },
                                    y: { grid: { display: false } },
                                },
                            }}
                        />
                    </div>
                </div>
            )}
        </div>
    );
}