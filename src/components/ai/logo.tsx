// AIススメルのロゴ：線の太さをそろえた、シンプルな「ス」
export function LogoMark({ className = "h-8 w-8", light }: { className?: string; light?: boolean }) {
  const c = light ? "#FFFFFF" : "#2563EB";
  return (
    <svg viewBox="0 0 32 32" className={className} aria-hidden>
      <g fill="none" stroke={c} strokeWidth="3.6">
        <path d="M7 9.6H24.6C20.6 16.6 14.6 21.8 7.2 25.6" strokeLinejoin="miter" />
        <path d="M17.2 18.4L24.8 25.8" />
      </g>
    </svg>
  );
}

export function Logo({ light, size = "md" }: { light?: boolean; size?: "md" | "sm" }) {
  return (
    <span className="inline-flex items-center gap-2">
      <LogoMark light={light} className={size === "sm" ? "h-7 w-7" : "h-8 w-8"} />
      <span className={`palt whitespace-nowrap font-black leading-none ${size === "sm" ? "text-lg" : "text-[1.3rem]"} ${light ? "text-white" : "text-slate-900"}`}>
        <span className={`font-num font-black ${light ? "text-blue-300" : "text-brand"}`}>AI</span>ススメル
      </span>
    </span>
  );
}
