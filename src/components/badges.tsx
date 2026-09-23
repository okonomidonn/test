import {
  PLATFORM_LABELS,
  PLATFORM_STYLES,
  POST_STATUS_LABELS,
  POST_STATUS_STYLES,
  type PlatformKey,
  type PostStatusKey,
} from "@/lib/constants";

export function PlatformBadge({ platform }: { platform: PlatformKey }) {
  return (
    <span className={`inline-block rounded px-2 py-0.5 text-xs font-semibold ${PLATFORM_STYLES[platform]}`}>
      {PLATFORM_LABELS[platform]}
    </span>
  );
}

export function StatusBadge({ status, overdue }: { status: PostStatusKey; overdue?: boolean }) {
  if (overdue) {
    return (
      <span className="inline-block rounded px-2 py-0.5 text-xs font-semibold bg-rose-100 text-rose-700">
        予約時刻超過
      </span>
    );
  }
  return (
    <span className={`inline-block rounded px-2 py-0.5 text-xs font-semibold ${POST_STATUS_STYLES[status]}`}>
      {POST_STATUS_LABELS[status]}
    </span>
  );
}
