// AIススメルのロゴ：吹き出し（AIの答え）の中に、前へ進む矢印（ススメル）
export function LogoMark({ className = "h-8 w-8" }: { className?: string }) {
  return (
    <svg viewBox="0 0 32 32" className={className} aria-hidden>
      <defs>
        <linearGradient id="aisusumeru-mark" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#3B82F6" />
          <stop offset="1" stopColor="#1D4ED8" />
        </linearGradient>
      </defs>
      <path d="M7 2.5h18A4.5 4.5 0 0 1 29.5 7v13a4.5 4.5 0 0 1-4.5 4.5H14l-6.5 5v-5H7A4.5 4.5 0 0 1 2.5 20V7A4.5 4.5 0 0 1 7 2.5z" fill="url(#aisusumeru-mark)" />
      <path d="M11 18.5l9-9M13 9.5h7v7" fill="none" stroke="#fff" strokeWidth="3.2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function Logo({ light, size = "md" }: { light?: boolean; size?: "md" | "sm" }) {
  return (
    <span className="inline-flex items-center gap-2">
      <LogoMark className={size === "sm" ? "h-7 w-7" : "h-8 w-8"} />
      <span className={`palt whitespace-nowrap font-black leading-none ${size === "sm" ? "text-lg" : "text-[1.3rem]"} ${light ? "text-white" : "text-slate-900"}`}>
        <span className={`font-num font-black ${light ? "text-blue-300" : "text-brand"}`}>AI</span>ススメル
      </span>
    </span>
  );
}
