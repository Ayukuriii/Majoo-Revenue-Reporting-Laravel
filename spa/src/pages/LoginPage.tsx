import { type FormEvent, useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import api, { apiErrorMessage } from '../api/client';
import { isAuthenticated, setAuth } from '../auth/storage';

type LoginResponse = {
    data: {
        token: string;
        token_type: string;
        expires_in: number;
    };
};

export function LoginPage() {
    const navigate = useNavigate();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);

    if (isAuthenticated()) {
        return <Navigate to="/dashboard" replace />;
    }

    async function onSubmit(event: FormEvent): Promise<void> {
        event.preventDefault();
        setError('');
        setSubmitting(true);

        try {
            const response = await api.post<LoginResponse>('/auth/login', { email, password });
            setAuth(response.data.data.token, response.data.data.token_type);
            navigate('/dashboard', { replace: true });
        } catch (err) {
            setError(apiErrorMessage(err, 'Login failed'));
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div className="flex min-h-screen items-center justify-center p-4">
            <form
                onSubmit={onSubmit}
                className="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h1 className="mb-1 text-xl font-semibold">Sign in</h1>
                <p className="mb-6 text-sm text-slate-500">Majoo revenue reporting</p>
                {error && (
                    <p className="mb-4 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
                )}
                <label className="mb-3 block text-sm">
                    Email
                    <input
                        type="email"
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        className="mt-1 w-full rounded border border-slate-300 px-3 py-2"
                    />
                </label>
                <label className="mb-4 block text-sm">
                    Password
                    <input
                        type="password"
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="mt-1 w-full rounded border border-slate-300 px-3 py-2"
                    />
                </label>
                <button
                    type="submit"
                    disabled={submitting}
                    className="w-full rounded bg-slate-900 px-3 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    {submitting ? 'Signing in…' : 'Sign in'}
                </button>
            </form>
        </div>
    );
}
