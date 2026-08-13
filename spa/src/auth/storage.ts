const TOKEN_KEY = 'token';
const TOKEN_TYPE_KEY = 'token_type';

export function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
}

export function setAuth(token: string, tokenType: string): void {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(TOKEN_TYPE_KEY, tokenType);
}

export function clearAuth(): void {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(TOKEN_TYPE_KEY);
}

export function isAuthenticated(): boolean {
    return getToken() !== null;
}
