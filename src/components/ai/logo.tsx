// AIススメルのロゴ「ダブルス」：「ス」と矢じり「›」を並べ、薦める・進めるの動きを表す
export function LogoMark({ className = "h-8 w-8", light }: { className?: string; light?: boolean }) {
  const c = light ? "#FFFFFF" : "#2563EB";
  return (
    <svg viewBox="0 0 32 32" className={className} aria-hidden>
      <g transform="translate(16 16) skewX(-10) translate(-16 -16)" fill="none" stroke={c}>
        <path d="M3.8 10H13.6L7.4 22.6" strokeWidth="4" strokeLinejoin="miter" />
        <path d="M10.2 16.6L13.6 20" strokeWidth="3.6" />
        <path d="M17.4 10L25.4 16.3L17.4 22.6" strokeWidth="4" strokeLinejoin="miter" />
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
