import { useEffect, useMemo, useState } from 'react';

export type DailyOmzetRow = {
    date: string;
    omzet: string;
    merchant_name?: string | null;
    outlet_name?: string | null;
};

type DailyOmzetTableProps = {
    rows: DailyOmzetRow[];
    perPage?: number;
};

const DEFAULT_PER_PAGE = 10;

export function DailyOmzetTable({ rows, perPage = DEFAULT_PER_PAGE }: DailyOmzetTableProps) {
    const [page, setPage] = useState(1);
    const lastPage = Math.max(1, Math.ceil(rows.length / perPage));

    useEffect(() => {
        setPage(1);
    }, [rows]);

    const pageRows = useMemo(() => {
        const currentPage = Math.min(page, lastPage);
        const start = (currentPage - 1) * perPage;

        return rows.slice(start, start + perPage);
    }, [lastPage, page, perPage, rows]);

    const currentPage = Math.min(page, lastPage);

    return (
        <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table className="min-w-full text-left text-sm">
                <thead className="bg-slate-50 text-slate-600">
                    <tr>
                        <th className="px-4 py-3 font-medium">Date</th>
                        <th className="px-4 py-3 font-medium">Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    {pageRows.map((row) => (
                        <tr key={row.date} className="border-t border-slate-100">
                            <td className="px-4 py-2 font-mono">{row.date}</td>
                            <td className="px-4 py-2 font-mono">{row.omzet}</td>
                        </tr>
                    ))}
                    {pageRows.length === 0 && (
                        <tr>
                            <td className="px-4 py-6 text-slate-500" colSpan={2}>
                                No rows
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
            <div className="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-sm">
                <span className="text-slate-600">
                    Page {currentPage} of {lastPage}
                </span>
                <div className="flex gap-2">
                    <button
                        type="button"
                        className="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
                        disabled={currentPage <= 1}
                        onClick={() => setPage(currentPage - 1)}
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        className="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
                        disabled={currentPage >= lastPage}
                        onClick={() => setPage(currentPage + 1)}
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    );
}
