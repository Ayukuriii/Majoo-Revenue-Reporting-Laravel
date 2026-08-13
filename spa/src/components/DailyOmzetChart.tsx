import { CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import type { DailyOmzetRow } from './DailyOmzetTable';

type ChartPoint = {
    date: string;
    day: string;
    omzet: number;
};

type DailyOmzetChartProps = {
    rows: DailyOmzetRow[];
};

function toChartPoints(rows: DailyOmzetRow[]): ChartPoint[] {
    return rows.map((row) => ({
        date: row.date,
        day: row.date.slice(-2),
        omzet: Number.parseFloat(row.omzet) || 0,
    }));
}

export function DailyOmzetChart({ rows }: DailyOmzetChartProps) {
    const data = toChartPoints(rows);

    return (
        <div className="h-64 w-full">
            <ResponsiveContainer width="100%" height="100%">
                <LineChart data={data} margin={{ top: 8, right: 12, left: 8, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                    <XAxis dataKey="day" tick={{ fontSize: 12 }} stroke="#64748b" />
                    <YAxis tick={{ fontSize: 12 }} stroke="#64748b" width={56} />
                    <Tooltip
                        formatter={(value) =>
                            typeof value === 'number' ? value.toFixed(2) : String(value)
                        }
                        labelFormatter={(_, payload) => {
                            const point = payload?.[0]?.payload as ChartPoint | undefined;

                            return point?.date ?? '';
                        }}
                    />
                    <Line type="monotone" dataKey="omzet" stroke="#0f172a" strokeWidth={2} dot={false} />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
}
