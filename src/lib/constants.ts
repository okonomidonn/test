export const PLATFORMS = ["X", "INSTAGRAM", "FACEBOOK", "TIKTOK", "YOUTUBE", "THREADS", "LINE"] as const;
export type PlatformKey = (typeof PLATFORMS)[number];

export const PLATFORM_LABELS: Record<PlatformKey, string> = {
  X: "X (Twitter)",
  INSTAGRAM: "Instagram",
  FACEBOOK: "Facebook",
  TIKTOK: "TikTok",
  YOUTUBE: "YouTube",
  THREADS: "Threads",
  LINE: "LINE公式",
};

export const PLATFORM_STYLES: Record<PlatformKey, string> = {
  X: "bg-slate-900 text-white",
  INSTAGRAM: "bg-pink-100 text-pink-800",
  FACEBOOK: "bg-blue-100 text-blue-800",
  TIKTOK: "bg-cyan-100 text-cyan-800",
  YOUTUBE: "bg-red-100 text-red-700",
  THREADS: "bg-zinc-200 text-zinc-800",
  LINE: "bg-green-100 text-green-800",
};

export const PLATFORM_CHAR_LIMITS: Partial<Record<PlatformKey, number>> = {
  X: 280,
  INSTAGRAM: 2200,
  THREADS: 500,
  TIKTOK: 2200,
};

export const POST_STATUSES = ["DRAFT", "SCHEDULED", "PUBLISHED"] as const;
export type PostStatusKey = (typeof POST_STATUSES)[number];

export const POST_STATUS_LABELS: Record<PostStatusKey, string> = {
  DRAFT: "下書き",
  SCHEDULED: "予約済み",
  PUBLISHED: "公開済み",
};

export const POST_STATUS_STYLES: Record<PostStatusKey, string> = {
  DRAFT: "bg-slate-100 text-slate-700",
  SCHEDULED: "bg-amber-100 text-amber-800",
  PUBLISHED: "bg-emerald-100 text-emerald-800",
};

export type Metrics = {
  impressions: number;
  likes: number;
  comments: number;
  shares: number;
  saves: number;
};

export function engagementCount(m: Metrics): number {
  return m.likes + m.comments + m.shares + m.saves;
}

export function engagementRate(m: Metrics): number | null {
  if (m.impressions <= 0) return null;
  return engagementCount(m) / m.impressions;
}

export function formatPercent(v: number | null): string {
  return v == null ? "—" : `${(v * 100).toFixed(2)}%`;
}

export function formatNumber(n: number): string {
  return n.toLocaleString("ja-JP");
}

const dateTimeFormatter = new Intl.DateTimeFormat("ja-JP", {
  timeZone: "Asia/Tokyo",
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
});

export function formatDateTime(d: Date | null): string {
  return d ? dateTimeFormatter.format(d) : "—";
}

// <input type="datetime-local"> has no timezone; the app treats it as JST.
export function toJstInputValue(d: Date | null): string {
  if (!d) return "";
  const jst = new Date(d.getTime() + 9 * 60 * 60 * 1000);
  return jst.toISOString().slice(0, 16);
}

export function fromJstInputValue(v: string): Date | null {
  if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(v)) return null;
  const d = new Date(`${v}:00+09:00`);
  return Number.isNaN(d.getTime()) ? null : d;
}
