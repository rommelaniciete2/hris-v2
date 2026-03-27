type JsonValue = Record<string, unknown>;

async function parseError(response: Response): Promise<string> {
    try {
        const payload = (await response.json()) as {
            message?: string;
            errors?: Record<string, string[]>;
        };

        const firstError = payload.errors
            ? Object.values(payload.errors)[0]?.[0]
            : null;

        return firstError ?? payload.message ?? 'Request failed.';
    } catch {
        return 'Request failed.';
    }
}

export async function postJson<T>(
    url: string,
    data: JsonValue,
    method: 'POST' | 'PUT' | 'PATCH' | 'DELETE' = 'POST',
): Promise<T> {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(data),
    });

    if (!response.ok) {
        throw new Error(await parseError(response));
    }

    return (await response.json()) as T;
}
