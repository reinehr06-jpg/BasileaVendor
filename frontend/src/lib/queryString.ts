export function qs(params?: Record<string, unknown>): string {
  if (!params) return "";
  const cleaned = Object.entries(params)
    .filter(([, v]) => v !== undefined && v !== null && v !== "")
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`);
  return cleaned.length ? `?${cleaned.join("&")}` : "";
}
