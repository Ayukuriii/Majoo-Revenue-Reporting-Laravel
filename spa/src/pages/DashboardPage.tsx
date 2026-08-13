import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { apiErrorMessage } from '../api/client';
import { clearAuth } from '../auth/storage';
import { DailyOmzetChart } from '../components/DailyOmzetChart';
import { DailyOmzetTable, type DailyOmzetRow } from '../components/DailyOmzetTable';

type MeResponse = {
    data: {
        email: string;
        name: string;
        merchant_id: number | null;
        merchant_name: string | null;
    };
};

type Outlet = {
    id: number;
    outlet_name: string;
};

type OutletsResponse = {
    data: Outlet[];
};

type PaginatedReport = {
    data: DailyOmzetRow[];
};

type ReportState = {
    rows: DailyOmzetRow[];
    loading: boolean;
    error: string;
};

const MONTHS = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const DEFAULT_YEAR = 2026;
const DEFAULT_MONTH = 8;
const PER_PAGE = 31;

const emptyReport: ReportState = { rows: [], loading: true, error: '' };

export function DashboardPage() {
    const navigate = useNavigate();
    const [merchantName, setMerchantName] = useState<string | null>(null);
    const [profileError, setProfileError] = useState('');
    const [year, setYear] = useState(DEFAULT_YEAR);
    const [month, setMonth] = useState(DEFAULT_MONTH);
    const [outlets, setOutlets] = useState<Outlet[]>([]);
    const [outletId, setOutletId] = useState<number | null>(null);
    const [outletsError, setOutletsError] = useState('');
    const [merchantReport, setMerchantReport] = useState<ReportState>(emptyReport);
    const [outletReport, setOutletReport] = useState<ReportState>(emptyReport);

    useEffect(() => {
        let cancelled = false;

        api.get<MeResponse>('/auth/me')
            .then((response) => {
                if (!cancelled) {
                    setMerchantName(response.data.data.merchant_name);
                }
            })
            .catch((err) => {
                if (!cancelled) {
                    setProfileError(apiErrorMessage(err, 'Failed to load profile'));
                }
            });

        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        let cancelled = false;

        api.get<OutletsResponse>('/outlets')
            .then((response) => {
                if (cancelled) {
                    return;
                }

                const list = response.data.data;
                setOutlets(list);
                setOutletsError('');

                if (list.length > 0) {
                    setOutletId((current) => current ?? list[0].id);
                }
            })
            .catch((err) => {
                if (!cancelled) {
                    setOutletsError(apiErrorMessage(err, 'Failed to load outlets'));
                }
            });

        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        let cancelled = false;

        setMerchantReport(emptyReport);

        api.get<PaginatedReport>('/reports/merchant', {
            params: { year, month, per_page: PER_PAGE },
        })
            .then((response) => {
                if (cancelled) {
                    return;
                }

                const rows = response.data.data;

                setMerchantReport({ rows, loading: false, error: '' });
                setMerchantName((current) => rows[0]?.merchant_name ?? current);
            })
            .catch((err) => {
                if (!cancelled) {
                    setMerchantReport({
                        rows: [],
                        loading: false,
                        error: apiErrorMessage(err, 'Failed to load merchant report'),
                    });
                }
            });

        return () => {
            cancelled = true;
        };
    }, [year, month]);

    useEffect(() => {
        if (outletId === null) {
            return;
        }

        let cancelled = false;

        setOutletReport(emptyReport);

        api.get<PaginatedReport>('/reports/outlet', {
            params: { outlet_id: outletId, year, month, per_page: PER_PAGE },
        })
            .then((response) => {
                if (!cancelled) {
                    setOutletReport({ rows: response.data.data, loading: false, error: '' });
                }
            })
            .catch((err) => {
                if (!cancelled) {
                    setOutletReport({
                        rows: [],
                        loading: false,
                        error: apiErrorMessage(err, 'Failed to load outlet report'),
                    });
                }
            });

        return () => {
            cancelled = true;
        };
    }, [outletId, year, month]);

    async function logout(): Promise<void> {
        try {
            await api.post('/auth/logout');
        } catch {
            // Token is cleared regardless so the session cannot linger in the browser.
        } finally {
            clearAuth();
            navigate('/login', { replace: true });
        }
    }

    const selectedOutlet = outlets.find((outlet) => outlet.id === outletId);
    const outletTitle = outletReport.rows[0]?.outlet_name ?? selectedOutlet?.outlet_name ?? 'Outlet';

    return (
        <div className="mx-auto max-w-6xl p-6">
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <h1 className="text-2xl font-semibold">Dashboard</h1>
                <div className="flex flex-wrap items-end gap-3">
                    <label className="text-sm">
                        Year
                        <input
                            type="number"
                            className="mt-1 block w-24 rounded border border-slate-300 bg-white px-3 py-2"
                            value={year}
                            min={2000}
                            max={2100}
                            onChange={(event) => setYear(Number(event.target.value))}
                        />
                    </label>
                    <label className="text-sm">
                        Month
                        <select
                            className="mt-1 block w-40 rounded border border-slate-300 bg-white px-3 py-2"
                            value={month}
                            onChange={(event) => setMonth(Number(event.target.value))}
                        >
                            {MONTHS.map((item) => (
                                <option key={item.value} value={item.value}>
                                    {item.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <button
                        type="button"
                        onClick={() => {
                            void logout();
                        }}
                        className="rounded border border-slate-300 px-3 py-2 text-sm"
                    >
                        Logout
                    </button>
                </div>
            </div>
            {profileError && <p className="mb-4 text-sm text-red-700">{profileError}</p>}
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="mb-1 text-lg font-medium">Merchant statistics</h2>
                    <p className="mb-4 text-sm text-slate-500">{merchantName ?? '…'}</p>
                    {merchantReport.loading && <p className="text-sm text-slate-500">Loading…</p>}
                    {merchantReport.error && (
                        <p className="text-sm text-red-700">{merchantReport.error}</p>
                    )}
                    {!merchantReport.loading && !merchantReport.error && (
                        <DailyOmzetChart rows={merchantReport.rows} />
                    )}
                </section>
                <div className="flex flex-col gap-4">
                    <label className="text-sm">
                        Outlet
                        <select
                            className="mt-1 block w-full rounded border border-slate-300 bg-white px-3 py-2"
                            value={outletId ?? ''}
                            onChange={(event) => setOutletId(Number(event.target.value))}
                            disabled={outlets.length === 0}
                        >
                            {outlets.map((outlet) => (
                                <option key={outlet.id} value={outlet.id}>
                                    {outlet.outlet_name}
                                </option>
                            ))}
                        </select>
                    </label>
                    {outletsError && <p className="text-sm text-red-700">{outletsError}</p>}
                    <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 className="mb-1 text-lg font-medium">Outlet statistics</h2>
                        <p className="mb-4 text-sm text-slate-500">{outletTitle}</p>
                        {outletReport.loading && <p className="text-sm text-slate-500">Loading…</p>}
                        {outletReport.error && (
                            <p className="text-sm text-red-700">{outletReport.error}</p>
                        )}
                        {!outletReport.loading && !outletReport.error && (
                            <DailyOmzetChart rows={outletReport.rows} />
                        )}
                    </section>
                </div>
                <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="mb-4 text-lg font-medium">Merchant table daily</h2>
                    {merchantReport.loading && <p className="text-sm text-slate-500">Loading…</p>}
                    {merchantReport.error && (
                        <p className="text-sm text-red-700">{merchantReport.error}</p>
                    )}
                    {!merchantReport.loading && !merchantReport.error && (
                        <DailyOmzetTable rows={merchantReport.rows} />
                    )}
                </section>
                <section className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="mb-4 text-lg font-medium">Outlet table daily</h2>
                    {outletReport.loading && <p className="text-sm text-slate-500">Loading…</p>}
                    {outletReport.error && (
                        <p className="text-sm text-red-700">{outletReport.error}</p>
                    )}
                    {!outletReport.loading && !outletReport.error && (
                        <DailyOmzetTable rows={outletReport.rows} />
                    )}
                </section>
            </div>
        </div>
    );
}
